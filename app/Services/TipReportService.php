<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\Tip;
use App\Models\TipPoolAllocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Tip reporting across waiter / barista / kitchen, by branch, shift and date range.
 *
 * Direct tips (waiter/barista) come from Tip rows that are NOT part of a kitchen
 * pool allocation. Kitchen tips come from TipPoolAllocation rows.
 */
class TipReportService
{
    /** @var array<string, array{start: int, end: int}> hour windows [start, end) */
    public const SHIFTS = [
        'morning' => ['start' => 5, 'end' => 12],
        'afternoon' => ['start' => 12, 'end' => 17],
        'evening' => ['start' => 17, 'end' => 24],
        'night' => ['start' => 0, 'end' => 5],
    ];

    /**
     * @param  list<int>  $restaurantIds
     * @return array<string, mixed>
     */
    public function build(array $restaurantIds, Carbon $from, Carbon $to, string $shift = 'all'): array
    {
        $restaurantIds = array_values(array_unique(array_map('intval', $restaurantIds)));
        if ($restaurantIds === []) {
            return $this->empty($from, $to, $shift);
        }

        $shift = array_key_exists($shift, self::SHIFTS) ? $shift : 'all';

        // Direct tips (exclude kitchen pool allocations to avoid double counting).
        $allocationTipIds = TipPoolAllocation::query()
            ->whereHas('contribution', fn ($q) => $q->whereIn('restaurant_id', $restaurantIds))
            ->whereNotNull('tip_id')
            ->pluck('tip_id')
            ->filter()
            ->unique()
            ->all();

        $directTips = Tip::withoutGlobalScopes()
            ->whereIn('restaurant_id', $restaurantIds)
            ->whereBetween('created_at', [$from, $to])
            ->when($allocationTipIds !== [], fn ($q) => $q->whereNotIn('id', $allocationTipIds))
            ->get(['id', 'restaurant_id', 'waiter_id', 'amount', 'created_at']);

        $allocations = TipPoolAllocation::query()
            ->with('contribution:id,restaurant_id')
            ->whereHas('contribution', fn ($q) => $q->whereIn('restaurant_id', $restaurantIds))
            ->whereBetween('created_at', [$from, $to])
            ->get(['id', 'user_id', 'amount', 'created_at', 'tip_pool_contribution_id']);

        if ($shift !== 'all') {
            $window = self::SHIFTS[$shift];
            $inShift = fn ($row) => $this->inShift($row->created_at, $window);
            $directTips = $directTips->filter($inShift)->values();
            $allocations = $allocations->filter($inShift)->values();
        }

        // Classify direct tips by staff role.
        $staffIds = $directTips->pluck('waiter_id')->merge($allocations->pluck('user_id'))->filter()->unique()->values();
        $staff = User::query()->whereIn('id', $staffIds)->get(['id', 'name']);
        $names = $staff->pluck('name', 'id');
        $baristaIds = User::query()->role('barista')->whereIn('id', $staffIds)->pluck('id')->all();

        $waiterRows = [];
        $baristaRows = [];

        foreach ($directTips->groupBy('waiter_id') as $waiterId => $rows) {
            if (! $waiterId) {
                continue;
            }
            $bucket = [
                'id' => (int) $waiterId,
                'name' => (string) ($names[$waiterId] ?? 'Staff #'.$waiterId),
                'count' => $rows->count(),
                'total' => round((float) $rows->sum('amount'), 2),
            ];
            if (in_array((int) $waiterId, array_map('intval', $baristaIds), true)) {
                $baristaRows[] = $bucket;
            } else {
                $waiterRows[] = $bucket;
            }
        }

        $kitchenRows = [];
        foreach ($allocations->groupBy('user_id') as $userId => $rows) {
            if (! $userId) {
                continue;
            }
            $kitchenRows[] = [
                'id' => (int) $userId,
                'name' => (string) ($names[$userId] ?? 'Staff #'.$userId),
                'count' => $rows->count(),
                'total' => round((float) $rows->sum('amount'), 2),
            ];
        }

        usort($waiterRows, fn ($a, $b) => $b['total'] <=> $a['total']);
        usort($baristaRows, fn ($a, $b) => $b['total'] <=> $a['total']);
        usort($kitchenRows, fn ($a, $b) => $b['total'] <=> $a['total']);

        $waiterTotal = array_sum(array_column($waiterRows, 'total'));
        $baristaTotal = array_sum(array_column($baristaRows, 'total'));
        $kitchenTotal = array_sum(array_column($kitchenRows, 'total'));

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'shift' => $shift,
            'waiters' => $waiterRows,
            'baristas' => $baristaRows,
            'kitchen' => $kitchenRows,
            'totals' => [
                'waiter' => round($waiterTotal, 2),
                'barista' => round($baristaTotal, 2),
                'kitchen' => round($kitchenTotal, 2),
                'grand' => round($waiterTotal + $baristaTotal + $kitchenTotal, 2),
                'count' => $directTips->count() + $allocations->count(),
            ],
            'by_branch' => $this->byBranch($restaurantIds, $directTips, $allocations),
            'by_shift' => $this->byShift($directTips, $allocations),
        ];
    }

    /**
     * @param  list<int>  $restaurantIds
     * @param  Collection<int, \App\Models\Tip>  $directTips
     * @param  Collection<int, TipPoolAllocation>  $allocations
     * @return list<array{restaurant_id: int, name: string, total: float, count: int}>
     */
    private function byBranch(array $restaurantIds, Collection $directTips, Collection $allocations): array
    {
        if (count($restaurantIds) < 2) {
            return [];
        }

        $names = Restaurant::query()->whereIn('id', $restaurantIds)->pluck('name', 'id');
        $rows = [];

        foreach ($restaurantIds as $rid) {
            $direct = $directTips->where('restaurant_id', $rid);
            $alloc = $allocations->filter(fn ($a) => (int) ($a->contribution->restaurant_id ?? 0) === $rid);
            $total = (float) $direct->sum('amount') + (float) $alloc->sum('amount');
            $count = $direct->count() + $alloc->count();

            if ($count === 0) {
                continue;
            }

            $rows[] = [
                'restaurant_id' => (int) $rid,
                'name' => (string) ($names[$rid] ?? 'Branch #'.$rid),
                'total' => round($total, 2),
                'count' => $count,
            ];
        }

        usort($rows, fn ($a, $b) => $b['total'] <=> $a['total']);

        return $rows;
    }

    /**
     * @param  Collection<int, \App\Models\Tip>  $directTips
     * @param  Collection<int, TipPoolAllocation>  $allocations
     * @return list<array{shift: string, label: string, total: float, count: int}>
     */
    private function byShift(Collection $directTips, Collection $allocations): array
    {
        $rows = [];
        foreach (self::SHIFTS as $key => $window) {
            $direct = $directTips->filter(fn ($t) => $this->inShift($t->created_at, $window));
            $alloc = $allocations->filter(fn ($a) => $this->inShift($a->created_at, $window));
            $total = (float) $direct->sum('amount') + (float) $alloc->sum('amount');
            $count = $direct->count() + $alloc->count();

            $rows[] = [
                'shift' => $key,
                'label' => ucfirst($key),
                'total' => round($total, 2),
                'count' => $count,
            ];
        }

        return $rows;
    }

    private function inShift(?Carbon $at, array $window): bool
    {
        if (! $at) {
            return false;
        }
        $hour = (int) $at->format('G');

        return $hour >= $window['start'] && $hour < $window['end'];
    }

    /**
     * @return array<string, mixed>
     */
    private function empty(Carbon $from, Carbon $to, string $shift): array
    {
        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'shift' => array_key_exists($shift, self::SHIFTS) ? $shift : 'all',
            'waiters' => [],
            'baristas' => [],
            'kitchen' => [],
            'totals' => ['waiter' => 0.0, 'barista' => 0.0, 'kitchen' => 0.0, 'grand' => 0.0, 'count' => 0],
            'by_branch' => [],
            'by_shift' => [],
        ];
    }
}
