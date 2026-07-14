<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Support\Money;
use App\Support\OrderWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveOrderController extends Controller
{
    public function index(Request $request): View
    {
        $restaurantId = $request->integer('restaurant_id') ?: null;
        $payload = $this->buildFeed($restaurantId);

        return view('admin.live-orders.index', [
            'pendingOrders' => $payload['collections']['pending'],
            'preparingOrders' => $payload['collections']['preparing'],
            'readyOrders' => $payload['collections']['ready'],
            'servedOrders' => $payload['collections']['served'],
            'restaurants' => Restaurant::query()->orderBy('name')->get(['id', 'name']),
            'restaurantId' => $restaurantId,
            'counts' => $payload['counts'],
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $restaurantId = $request->integer('restaurant_id') ?: null;

        return response()->json($this->buildFeed($restaurantId));
    }

    /**
     * @return array{
     *     counts: array<string, int>,
     *     total_live: int,
     *     columns: array<string, list<array<string, mixed>>>,
     *     collections: array<string, \Illuminate\Support\Collection<int, Order>>,
     *     refreshed_at: string
     * }
     */
    private function buildFeed(?int $restaurantId): array
    {
        $baseQuery = Order::query()
            ->with(['restaurant', 'waiter'])
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId));

        // Received + accepted orders are grouped under "Pending" (kitchen hasn't started/queued yet).
        $pendingStatuses = array_merge(
            OrderWorkflow::storageVariants(OrderWorkflow::RECEIVED),
            OrderWorkflow::storageVariants(OrderWorkflow::ACCEPTED),
        );
        $preparingStatuses = OrderWorkflow::storageVariants(OrderWorkflow::PREPARING);
        $readyStatuses = OrderWorkflow::storageVariants(OrderWorkflow::READY);
        $servedStatuses = OrderWorkflow::storageVariants(OrderWorkflow::SERVED);

        $pending = (clone $baseQuery)->whereIn('status', $pendingStatuses)->latest()->limit(50)->get();
        $preparing = (clone $baseQuery)->whereIn('status', $preparingStatuses)->latest()->limit(50)->get();
        $ready = (clone $baseQuery)->whereIn('status', $readyStatuses)->latest()->limit(50)->get();
        $served = (clone $baseQuery)->whereIn('status', $servedStatuses)->latest()->limit(50)->get();

        $counts = [
            'pending' => Order::query()->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))->whereIn('status', $pendingStatuses)->count(),
            'preparing' => Order::query()->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))->whereIn('status', $preparingStatuses)->count(),
            'ready' => Order::query()->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))->whereIn('status', $readyStatuses)->count(),
            'served' => Order::query()->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))->whereIn('status', $servedStatuses)->count(),
        ];

        $columns = [
            'pending' => $pending->map(fn (Order $order) => $this->serializeOrder($order))->values()->all(),
            'preparing' => $preparing->map(fn (Order $order) => $this->serializeOrder($order))->values()->all(),
            'ready' => $ready->map(fn (Order $order) => $this->serializeOrder($order))->values()->all(),
            'served' => $served->map(fn (Order $order) => $this->serializeOrder($order))->values()->all(),
        ];

        return [
            'counts' => $counts,
            'total_live' => array_sum($counts),
            'columns' => $columns,
            'collections' => [
                'pending' => $pending,
                'preparing' => $preparing,
                'ready' => $ready,
                'served' => $served,
            ],
            'refreshed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'number' => str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            'table' => $order->table_number,
            'restaurant' => $order->restaurant?->name,
            'amount' => (float) $order->total_amount,
            'amount_formatted' => Money::format($order->total_amount),
            'waiter' => $order->waiter?->name,
            'url' => route('admin.orders.show', $order),
        ];
    }
}
