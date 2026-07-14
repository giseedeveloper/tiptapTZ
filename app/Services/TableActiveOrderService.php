<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Table;
use App\Support\OrderWorkflow;

class TableActiveOrderService
{
    /**
     * Latest open order for a table (received through served).
     */
    public function findForTable(int $restaurantId, ?string $tableNumber = null, ?int $tableId = null): ?Order
    {
        if (($tableNumber === null || trim($tableNumber) === '') && $tableId === null) {
            return null;
        }

        if (($tableNumber === null || trim($tableNumber) === '') && $tableId !== null) {
            $table = Table::withoutGlobalScopes()->find($tableId);
            $tableNumber = $table?->name;
        }

        if ($tableNumber === null || trim($tableNumber) === '') {
            return null;
        }

        return Order::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->where('table_number', $tableNumber)
            ->whereIn('status', OrderWorkflow::activeTableStatuses())
            ->latest('id')
            ->first();
    }
}
