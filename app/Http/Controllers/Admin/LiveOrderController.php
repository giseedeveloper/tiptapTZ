<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Support\Money;
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

        $pending = (clone $baseQuery)->where('status', 'pending')->latest()->limit(50)->get();
        $preparing = (clone $baseQuery)->where('status', 'preparing')->latest()->limit(50)->get();
        $ready = (clone $baseQuery)->where('status', 'ready')->latest()->limit(50)->get();
        $served = (clone $baseQuery)->where('status', 'served')->latest()->limit(50)->get();

        $counts = [
            'pending' => Order::query()->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))->where('status', 'pending')->count(),
            'preparing' => Order::query()->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))->where('status', 'preparing')->count(),
            'ready' => Order::query()->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))->where('status', 'ready')->count(),
            'served' => Order::query()->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))->where('status', 'served')->count(),
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
