<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Tip;
use App\Models\TipPool;
use App\Models\TipPoolAllocation;
use App\Models\TipPoolContribution;
use App\Models\TipPoolMember;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TipPoolService
{
    /**
     * Ensure the restaurant has a kitchen tip pool row (disabled by default).
     */
    public function ensureKitchenPool(int $restaurantId): TipPool
    {
        return TipPool::query()->firstOrCreate(
            [
                'restaurant_id' => $restaurantId,
                'code' => TipPool::CODE_KITCHEN,
            ],
            [
                'name' => 'Kitchen staff tip pool',
                'is_enabled' => false,
                'distribution_method' => TipPool::METHOD_EQUAL,
            ]
        );
    }

    public function findTippableKitchenPool(int $restaurantId): ?TipPool
    {
        $pool = $this->ensureKitchenPool($restaurantId);
        $pool->load(['activeMembers.user']);

        return $pool->isTippable() ? $pool : null;
    }

    /**
     * Split amount (in major units) into integer cents for each member.
     *
     * @param  Collection<int, TipPoolMember>  $members
     * @return list<array{user_id: int, weight: int, cents: int}>
     */
    public function calculateShares(Collection $members, float $amount, string $method): array
    {
        $active = $members->filter(fn (TipPoolMember $m) => $m->is_active && $m->weight > 0)->values();
        if ($active->isEmpty()) {
            throw new InvalidArgumentException('Tip pool has no active members.');
        }

        $totalCents = (int) round($amount * 100);
        if ($totalCents < 1) {
            throw new InvalidArgumentException('Tip amount must be at least 0.01.');
        }

        $method = $method === TipPool::METHOD_WEIGHTED
            ? TipPool::METHOD_WEIGHTED
            : TipPool::METHOD_EQUAL;

        if ($method === TipPool::METHOD_EQUAL) {
            $n = $active->count();
            $base = intdiv($totalCents, $n);
            $remainder = $totalCents % $n;

            return $active->values()->map(function (TipPoolMember $member, int $index) use ($base, $remainder) {
                return [
                    'user_id' => (int) $member->user_id,
                    'weight' => 1,
                    'cents' => $base + ($index < $remainder ? 1 : 0),
                ];
            })->all();
        }

        $totalWeight = (int) $active->sum('weight');
        $shares = [];
        $assigned = 0;

        foreach ($active as $member) {
            $raw = ($totalCents * (int) $member->weight) / $totalWeight;
            $floor = (int) floor($raw);
            $shares[] = [
                'user_id' => (int) $member->user_id,
                'weight' => (int) $member->weight,
                'cents' => $floor,
                'fraction' => $raw - $floor,
            ];
            $assigned += $floor;
        }

        $remainder = $totalCents - $assigned;
        usort($shares, function (array $a, array $b) {
            if ($a['fraction'] === $b['fraction']) {
                return $b['weight'] <=> $a['weight'];
            }

            return $b['fraction'] <=> $a['fraction'];
        });

        for ($i = 0; $i < $remainder; $i++) {
            $shares[$i % count($shares)]['cents']++;
        }

        return array_map(fn (array $row) => [
            'user_id' => $row['user_id'],
            'weight' => $row['weight'],
            'cents' => $row['cents'],
        ], $shares);
    }

    /**
     * Record a pool tip and create individual Tip rows for each member.
     */
    public function distributeFromPayment(Payment $payment): ?TipPoolContribution
    {
        if (! $payment->tip_pool_id) {
            return null;
        }

        if ($payment->status !== 'paid') {
            return null;
        }

        $existing = TipPoolContribution::query()
            ->where('payment_id', $payment->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $pool = TipPool::query()->with('activeMembers')->find($payment->tip_pool_id);
        if (! $pool || ! $pool->is_enabled) {
            return null;
        }

        return $this->distribute(
            $pool,
            (float) $payment->amount,
            $payment->id,
            null,
            ['source' => 'payment']
        );
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function distribute(
        TipPool $pool,
        float $amount,
        ?int $paymentId = null,
        ?int $orderId = null,
        array $meta = [],
    ): TipPoolContribution {
        $pool->loadMissing('activeMembers');

        $method = $pool->distribution_method === TipPool::METHOD_WEIGHTED
            ? TipPool::METHOD_WEIGHTED
            : TipPool::METHOD_EQUAL;

        $shares = $this->calculateShares($pool->activeMembers, $amount, $method);

        return DB::transaction(function () use ($pool, $amount, $paymentId, $orderId, $meta, $method, $shares) {
            if ($paymentId) {
                $locked = TipPoolContribution::query()
                    ->where('payment_id', $paymentId)
                    ->lockForUpdate()
                    ->first();
                if ($locked) {
                    return $locked;
                }
            }

            $contribution = TipPoolContribution::create([
                'tip_pool_id' => $pool->id,
                'restaurant_id' => $pool->restaurant_id,
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'amount' => round($amount, 2),
                'distribution_method' => $method,
                'meta' => $meta ?: null,
            ]);

            foreach ($shares as $share) {
                if ($share['cents'] <= 0) {
                    continue;
                }

                $shareAmount = round($share['cents'] / 100, 2);

                $tip = Tip::withoutGlobalScopes()->create([
                    'restaurant_id' => $pool->restaurant_id,
                    'waiter_id' => $share['user_id'],
                    'order_id' => $orderId,
                    'payment_id' => $paymentId,
                    'amount' => $shareAmount,
                ]);

                TipPoolAllocation::create([
                    'tip_pool_contribution_id' => $contribution->id,
                    'user_id' => $share['user_id'],
                    'tip_id' => $tip->id,
                    'amount' => $shareAmount,
                    'weight_used' => $share['weight'],
                ]);
            }

            return $contribution->load('allocations');
        });
    }

    /**
     * Settle a paid quick-tip payment: waiter-only, kitchen pool, or 50/50 split.
     */
    public function settleQuickTipPayment(Payment $payment): void
    {
        if ($payment->status !== 'paid') {
            return;
        }

        $hasPool = ! empty($payment->tip_pool_id);
        $hasWaiter = ! empty($payment->waiter_id);

        if ($hasPool && $hasWaiter) {
            $this->distributeSplitFromPayment($payment);

            return;
        }

        if ($hasPool) {
            $this->distributeFromPayment($payment);

            return;
        }

        if ($hasWaiter) {
            Tip::withoutGlobalScopes()->firstOrCreate(
                [
                    'payment_id' => $payment->id,
                    'waiter_id' => $payment->waiter_id,
                ],
                [
                    'restaurant_id' => $payment->restaurant_id,
                    'order_id' => null,
                    'amount' => $payment->amount,
                ]
            );
        }
    }

    /**
     * Split a tip between one front-of-house staff member and the kitchen pool.
     * Default: 50% waiter / 50% kitchen (remainder cents go to kitchen).
     */
    public function distributeSplitFromPayment(Payment $payment, float $waiterPercent = 50.0): ?TipPoolContribution
    {
        if ($payment->status !== 'paid' || ! $payment->tip_pool_id || ! $payment->waiter_id) {
            return null;
        }

        $existing = TipPoolContribution::query()
            ->where('payment_id', $payment->id)
            ->first();
        if ($existing) {
            return $existing;
        }

        $pool = TipPool::query()->with('activeMembers')->find($payment->tip_pool_id);
        if (! $pool || ! $pool->is_enabled) {
            return null;
        }

        $waiterPercent = max(1.0, min(99.0, $waiterPercent));
        $totalCents = (int) round(((float) $payment->amount) * 100);
        $waiterCents = (int) round($totalCents * ($waiterPercent / 100));
        $poolCents = $totalCents - $waiterCents;

        return DB::transaction(function () use ($payment, $pool, $waiterCents, $poolCents, $waiterPercent) {
            $locked = TipPoolContribution::query()
                ->where('payment_id', $payment->id)
                ->lockForUpdate()
                ->first();
            if ($locked) {
                return $locked;
            }

            Tip::withoutGlobalScopes()->firstOrCreate(
                [
                    'payment_id' => $payment->id,
                    'waiter_id' => $payment->waiter_id,
                ],
                [
                    'restaurant_id' => $payment->restaurant_id,
                    'order_id' => null,
                    'amount' => round($waiterCents / 100, 2),
                ]
            );

            if ($poolCents <= 0) {
                return TipPoolContribution::create([
                    'tip_pool_id' => $pool->id,
                    'restaurant_id' => $pool->restaurant_id,
                    'payment_id' => $payment->id,
                    'order_id' => null,
                    'amount' => 0,
                    'distribution_method' => $pool->distribution_method,
                    'meta' => [
                        'source' => 'split',
                        'waiter_percent' => $waiterPercent,
                        'waiter_amount' => round($waiterCents / 100, 2),
                        'pool_amount' => 0,
                    ],
                ]);
            }

            return $this->distribute(
                $pool,
                round($poolCents / 100, 2),
                $payment->id,
                null,
                [
                    'source' => 'split',
                    'waiter_percent' => $waiterPercent,
                    'waiter_amount' => round($waiterCents / 100, 2),
                    'pool_amount' => round($poolCents / 100, 2),
                ]
            );
        });
    }

    /**
     * Options for the optional post-payment tipping screen.
     *
     * @return array<string, mixed>
     */
    public function postPaymentTipOptions(int $restaurantId, ?int $preferredWaiterId = null): array
    {
        $restaurant = \App\Models\Restaurant::find($restaurantId);
        $settings = $restaurant
            ? $restaurant->tipSettings()
            : \App\Models\Restaurant::TIP_SETTINGS_DEFAULTS;

        $catWaiter = (bool) ($settings['categories']['waiter'] ?? true);
        $catBarista = (bool) ($settings['categories']['barista'] ?? true);
        $catKitchen = (bool) ($settings['categories']['kitchen'] ?? true);

        $waiters = User::role('waiter')
            ->activeAtRestaurant($restaurantId)
            ->digitalTipsEnabled()
            ->orderBy('name')
            ->get(['id', 'name']);

        $baristas = User::role('barista')
            ->activeAtRestaurant($restaurantId)
            ->digitalTipsEnabled()
            ->orderBy('name')
            ->get(['id', 'name']);

        $kitchen = $this->findTippableKitchenPool($restaurantId);

        $defaultWaiter = null;
        if ($preferredWaiterId) {
            $defaultWaiter = $waiters->firstWhere('id', $preferredWaiterId)
                ?? $baristas->firstWhere('id', $preferredWaiterId);
        }
        if (! $defaultWaiter) {
            $defaultWaiter = $waiters->first();
        }

        $mapStaff = fn ($u) => ['id' => (int) $u->id, 'name' => (string) $u->name];

        $waiterAvailable = $catWaiter && $waiters->isNotEmpty();
        $baristaAvailable = $catBarista && $baristas->isNotEmpty();
        $kitchenAvailable = $catKitchen && $kitchen !== null;
        $splitPartner = $defaultWaiter ?? $waiters->first() ?? $baristas->first();
        $splitAvailable = $kitchenAvailable && ($waiterAvailable || $baristaAvailable) && $splitPartner !== null;

        return [
            'amounts' => $settings['fixed_amounts'] ?? [500, 1000, 2000, 5000],
            'currency_hint' => 'local',
            'suggestions' => [
                'mode' => $settings['suggestion_mode'] ?? 'percent',
                'percentages' => $settings['percentages'] ?? [5, 10, 15],
                'fixed_amounts' => $settings['fixed_amounts'] ?? [500, 1000, 2000, 5000],
            ],
            'options' => [
                'waiter' => [
                    'available' => $waiterAvailable,
                    'label' => 'Waiter',
                    'default' => $defaultWaiter && $waiters->contains('id', $defaultWaiter->id)
                        ? $mapStaff($defaultWaiter)
                        : ($waiters->first() ? $mapStaff($waiters->first()) : null),
                    'staff' => $waiters->map($mapStaff)->values()->all(),
                ],
                'barista' => [
                    'available' => $baristaAvailable,
                    'label' => 'Barista',
                    'default' => $baristas->first() ? $mapStaff($baristas->first()) : null,
                    'staff' => $baristas->map($mapStaff)->values()->all(),
                ],
                'kitchen' => [
                    'available' => $kitchenAvailable,
                    'label' => 'Kitchen',
                    'pool' => $kitchen ? [
                        'id' => $kitchen->id,
                        'name' => $kitchen->name,
                        'code' => $kitchen->code,
                        'member_count' => $kitchen->activeMembers->count(),
                    ] : null,
                ],
                'split' => [
                    'available' => $splitAvailable,
                    'label' => 'Split',
                    'description' => '50% to staff · 50% to kitchen pool',
                    'waiter_percent' => 50,
                    'staff' => $splitPartner ? $mapStaff($splitPartner) : null,
                    'pool' => $kitchen ? [
                        'id' => $kitchen->id,
                        'name' => $kitchen->name,
                        'code' => $kitchen->code,
                    ] : null,
                ],
            ],
        ];
    }
}
