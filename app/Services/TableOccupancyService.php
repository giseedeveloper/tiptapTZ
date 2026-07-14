<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Table;
use App\Support\OrderWorkflow;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Live floor occupancy from tables + open orders (received → served).
 */
class TableOccupancyService
{
    /**
     * @return array{
     *     summary: array{
     *         total_tables: int,
     *         active_tables: int,
     *         occupied: int,
     *         free: int,
     *         inactive_tables: int,
     *         occupancy_percent: float,
     *         capacity_total: int,
     *         capacity_occupied: int,
     *         live_orders: int,
     *         overdue: int
     *     },
     *     tables: list<array<string, mixed>>,
     *     zones: list<array{id: int|null, name: string, occupied: int, free: int, tables: list<array<string, mixed>>}>,
     *     generated_at: string
     * }
     */
    public function snapshot(int $restaurantId, int $overdueAfterMinutes = 45): array
    {
        $tables = Table::withoutGlobalScopes()
            ->with(['waiter:id,name', 'zone:id,name'])
            ->where('restaurant_id', $restaurantId)
            ->orderBy('name')
            ->get();

        $activeOrders = Order::withoutGlobalScopes()
            ->with(['waiter:id,name'])
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', OrderWorkflow::activeTableStatuses())
            ->latest('id')
            ->get();

        /** @var Collection<string, Order> $orderByTable */
        $orderByTable = collect();
        foreach ($activeOrders as $order) {
            $key = $this->normalizeTableKey($order->table_number);
            if ($key === '' || $orderByTable->has($key)) {
                continue;
            }
            $orderByTable->put($key, $order);
        }

        $now = now();
        $mapped = [];
        $occupied = 0;
        $free = 0;
        $inactive = 0;
        $capacityTotal = 0;
        $capacityOccupied = 0;
        $overdue = 0;

        foreach ($tables as $table) {
            $isActive = (bool) $table->is_active;
            $key = $this->normalizeTableKey($table->name);
            $order = $key !== '' ? $orderByTable->get($key) : null;
            $isOccupied = $isActive && $order !== null;

            $elapsedMinutes = null;
            $isOverdue = false;
            if ($order) {
                $started = $order->received_at ?? $order->created_at;
                $elapsedMinutes = $started ? $started->diffInMinutes($now) : null;
                $isOverdue = $elapsedMinutes !== null && $elapsedMinutes >= $overdueAfterMinutes;
                if ($isOverdue) {
                    $overdue++;
                }
            }

            $capacity = max(0, (int) ($table->capacity ?? 0));
            if ($isActive) {
                $capacityTotal += $capacity;
                if ($isOccupied) {
                    $occupied++;
                    $capacityOccupied += $capacity;
                } else {
                    $free++;
                }
            } else {
                $inactive++;
            }

            $row = [
                'id' => (int) $table->id,
                'name' => (string) $table->name,
                'capacity' => $capacity,
                'is_active' => $isActive,
                'status' => ! $isActive ? 'inactive' : ($isOccupied ? 'occupied' : 'free'),
                'zone_id' => $table->zone_id ? (int) $table->zone_id : null,
                'zone_name' => $table->zone?->name,
                'assigned_waiter' => $table->waiter ? [
                    'id' => (int) $table->waiter->id,
                    'name' => (string) $table->waiter->name,
                ] : null,
                'order' => $order ? [
                    'id' => (int) $order->id,
                    'status' => OrderWorkflow::normalize($order->status),
                    'status_label' => OrderWorkflow::label($order->status),
                    'status_color' => OrderWorkflow::color($order->status),
                    'total_amount' => round((float) $order->total_amount, 2),
                    'payment_status' => $order->payment_status ?? null,
                    'waiter_name' => $order->waiter?->name,
                    'customer_name' => $order->customer_name,
                    'elapsed_minutes' => $elapsedMinutes,
                    'is_overdue' => $isOverdue,
                    'started_at' => ($order->received_at ?? $order->created_at)?->toIso8601String(),
                ] : null,
            ];

            $mapped[] = $row;
        }

        $activeTables = $occupied + $free;
        $occupancyPercent = $activeTables > 0
            ? round(($occupied / $activeTables) * 100, 1)
            : 0.0;

        $zones = collect($mapped)
            ->groupBy(fn (array $t) => $t['zone_id'] ?? 0)
            ->map(function (Collection $rows) {
                $first = $rows->first();
                $activeRows = $rows->where('is_active', true);

                return [
                    'id' => $first['zone_id'],
                    'name' => $first['zone_name'] ?: 'Unassigned',
                    'occupied' => $activeRows->where('status', 'occupied')->count(),
                    'free' => $activeRows->where('status', 'free')->count(),
                    'tables' => $rows->values()->all(),
                ];
            })
            ->sortBy(fn (array $z) => $z['name'] === 'Unassigned' ? 'zzz' : strtolower($z['name']))
            ->values()
            ->all();

        return [
            'summary' => [
                'total_tables' => $tables->count(),
                'active_tables' => $activeTables,
                'occupied' => $occupied,
                'free' => $free,
                'inactive_tables' => $inactive,
                'occupancy_percent' => $occupancyPercent,
                'capacity_total' => $capacityTotal,
                'capacity_occupied' => $capacityOccupied,
                'live_orders' => $activeOrders->count(),
                'overdue' => $overdue,
            ],
            'tables' => $mapped,
            'zones' => $zones,
            'generated_at' => Carbon::now()->toIso8601String(),
        ];
    }

    private function normalizeTableKey(?string $value): string
    {
        return strtolower(trim((string) $value));
    }
}
