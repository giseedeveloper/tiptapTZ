<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Support\OrderWorkflow;

class FreeWaiterService
{
    /**
     * Waiters currently tied to an open order (received through served).
     *
     * @return list<int>
     */
    public function busyWaiterIds(int $restaurantId): array
    {
        return Order::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', OrderWorkflow::activeTableStatuses())
            ->whereNotNull('waiter_id')
            ->pluck('waiter_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Next online waiter with no active order assigned.
     */
    public function findAvailable(int $restaurantId): ?User
    {
        $busyIds = $this->busyWaiterIds($restaurantId);

        return User::role('waiter')
            ->where('restaurant_id', $restaurantId)
            ->where('is_online', true)
            ->when($busyIds !== [], fn ($query) => $query->whereNotIn('id', $busyIds))
            ->orderBy('id')
            ->first();
    }
}
