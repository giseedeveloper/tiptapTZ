<?php

namespace App\Services;

use App\Enums\BotEngagementEvent;
use App\Enums\BotFunnelStep;
use App\Models\BotSession;
use App\Models\MenuEngagementSession;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\CustomerMenuEngagementNotification;
use Illuminate\Support\Carbon;

class MenuEngagementService
{
    public function __construct(
        private readonly BotEventService $botEventService,
    ) {}

    public function recordMenuView(
        int $restaurantId,
        ?string $waId = null,
        ?string $customerPhone = null,
        ?int $tableId = null,
        ?string $tableNumber = null,
    ): ?MenuEngagementSession {
        $normalizedWaId = $this->normalizeWaId($waId, $customerPhone);

        $metadata = array_filter([
            'table_id' => $tableId,
            'table_number' => $tableNumber,
        ], fn ($value) => $value !== null && $value !== '');

        $this->botEventService->recordFunnelStep(
            step: BotFunnelStep::ViewMenu,
            restaurantId: $restaurantId,
            waId: $normalizedWaId,
            customerPhone: $customerPhone,
            metadata: $metadata,
        );

        $this->botEventService->record(
            event: BotEngagementEvent::ViewMenu,
            restaurantId: $restaurantId,
            waId: $normalizedWaId,
            customerPhone: $customerPhone,
            metadata: $metadata,
        );

        $restaurant = Restaurant::query()->find($restaurantId);
        if (! $restaurant || ! $restaurant->menu_engagement_alerts_enabled) {
            return null;
        }

        $pending = MenuEngagementSession::query()
            ->where('restaurant_id', $restaurantId)
            ->where('status', MenuEngagementSession::STATUS_PENDING)
            ->when(
                $normalizedWaId,
                fn ($query) => $query->where('wa_id', $normalizedWaId),
                fn ($query) => $query->whereNull('wa_id'),
            )
            ->first();

        if ($pending) {
            $pending->update([
                'menu_viewed_at' => now(),
                'table_id' => $tableId ?? $pending->table_id,
                'table_number' => $tableNumber ?? $pending->table_number,
            ]);

            return $pending->fresh();
        }

        return MenuEngagementSession::query()->create([
            'restaurant_id' => $restaurantId,
            'table_id' => $tableId,
            'table_number' => $tableNumber,
            'wa_id' => $normalizedWaId,
            'menu_viewed_at' => now(),
            'status' => MenuEngagementSession::STATUS_PENDING,
        ]);
    }

    public function markConvertedForCustomer(
        int $restaurantId,
        ?string $waId = null,
        ?string $customerPhone = null,
    ): void {
        $normalizedWaId = $this->normalizeWaId($waId, $customerPhone);

        if ($normalizedWaId === null) {
            return;
        }

        MenuEngagementSession::query()
            ->where('restaurant_id', $restaurantId)
            ->where('status', MenuEngagementSession::STATUS_PENDING)
            ->where('wa_id', $normalizedWaId)
            ->update([
                'status' => MenuEngagementSession::STATUS_CONVERTED,
                'converted_at' => now(),
            ]);
    }

    public function checkAndNotify(): int
    {
        $notifiedCount = 0;

        Restaurant::query()
            ->where('menu_engagement_alerts_enabled', true)
            ->where('is_active', true)
            ->select(['id', 'menu_engagement_timeout_minutes'])
            ->chunkById(50, function ($restaurants) use (&$notifiedCount): void {
                foreach ($restaurants as $restaurant) {
                    $notifiedCount += $this->processRestaurant($restaurant);
                }
            });

        return $notifiedCount;
    }

    /**
     * @return array{
     *     pending: int,
     *     notified_today: int,
     *     converted_today: int,
     *     views_today: int
     * }
     */
    public function statsForRestaurant(int $restaurantId): array
    {
        $today = Carbon::today();

        return [
            'pending' => MenuEngagementSession::query()
                ->where('restaurant_id', $restaurantId)
                ->where('status', MenuEngagementSession::STATUS_PENDING)
                ->count(),
            'notified_today' => MenuEngagementSession::query()
                ->where('restaurant_id', $restaurantId)
                ->where('status', MenuEngagementSession::STATUS_NOTIFIED)
                ->whereDate('notified_at', $today)
                ->count(),
            'converted_today' => MenuEngagementSession::query()
                ->where('restaurant_id', $restaurantId)
                ->where('status', MenuEngagementSession::STATUS_CONVERTED)
                ->whereDate('converted_at', $today)
                ->count(),
            'views_today' => MenuEngagementSession::query()
                ->where('restaurant_id', $restaurantId)
                ->whereDate('menu_viewed_at', $today)
                ->count(),
        ];
    }

    public function dismissSession(MenuEngagementSession $session): void
    {
        $session->update([
            'status' => MenuEngagementSession::STATUS_DISMISSED,
            'dismissed_at' => now(),
        ]);
    }

    private function processRestaurant(Restaurant $restaurant): int
    {
        $timeout = $this->timeoutMinutesFor($restaurant);
        $notifiedCount = 0;

        $sessions = MenuEngagementSession::query()
            ->where('restaurant_id', $restaurant->id)
            ->where('status', MenuEngagementSession::STATUS_PENDING)
            ->where('menu_viewed_at', '<=', now()->subMinutes($timeout))
            ->get();

        foreach ($sessions as $session) {
            if ($this->hasOrderSinceView($session)) {
                $session->update([
                    'status' => MenuEngagementSession::STATUS_CONVERTED,
                    'converted_at' => now(),
                ]);

                continue;
            }

            if ($session->wa_id === null) {
                continue;
            }

            $session->update([
                'status' => MenuEngagementSession::STATUS_NOTIFIED,
                'notified_at' => now(),
            ]);

            $this->notifyManagers($session, $timeout);
            $notifiedCount++;
        }

        return $notifiedCount;
    }

    private function notifyManagers(MenuEngagementSession $session, int $timeoutMinutes): void
    {
        User::query()
            ->role('manager')
            ->where('restaurant_id', $session->restaurant_id)
            ->get()
            ->each(fn (User $manager) => $manager->notify(
                new CustomerMenuEngagementNotification($session, $timeoutMinutes)
            ));
    }

    private function hasOrderSinceView(MenuEngagementSession $session): bool
    {
        if ($session->wa_id === null) {
            return false;
        }

        $needle = $session->wa_id;

        return Order::withoutGlobalScopes()
            ->where('restaurant_id', $session->restaurant_id)
            ->where('created_at', '>=', $session->menu_viewed_at)
            ->where(function ($query) use ($needle): void {
                $query->whereRaw(
                    "REPLACE(REPLACE(REPLACE(customer_phone, '+', ''), ' ', ''), '-', '') LIKE ?",
                    ['%'.$needle.'%'],
                )->orWhereRaw(
                    "REPLACE(REPLACE(whatsapp_jid, '@s.whatsapp.net', ''), '+', '') LIKE ?",
                    ['%'.$needle.'%'],
                );
            })
            ->exists();
    }

    private function timeoutMinutesFor(Restaurant $restaurant): int
    {
        return max(5, min(60, (int) ($restaurant->menu_engagement_timeout_minutes ?? 10)));
    }

    private function normalizeWaId(?string $waId, ?string $customerPhone): ?string
    {
        if (is_string($waId) && trim($waId) !== '') {
            return BotSession::normalizeWaId($waId);
        }

        if (is_string($customerPhone) && trim($customerPhone) !== '') {
            return BotSession::normalizeWaId($customerPhone);
        }

        return null;
    }
}
