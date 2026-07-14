<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Restaurant;
use App\Services\OrderWorkflowService;
use App\Support\OrderWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class KitchenController extends Controller
{
    /**
     * Display the kitchen display system
     */
    public function display($token)
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();
        
        return view('kitchen.display', compact('restaurant'));
    }

    /**
     * Get orders for kitchen display (API endpoint for real-time updates)
     */
    public function getOrders($token)
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();
        
        $receivedVariants = OrderWorkflow::storageVariants(OrderWorkflow::RECEIVED);
        $acceptedVariants = OrderWorkflow::storageVariants(OrderWorkflow::ACCEPTED);
        $preparingVariants = OrderWorkflow::storageVariants(OrderWorkflow::PREPARING);

        $orders = Order::with(['items.menuItem', 'waiter'])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', OrderWorkflow::kitchenActiveStatuses())
            ->orderByRaw(
                "CASE
                WHEN status IN ('".implode("','", $receivedVariants)."') THEN 1
                WHEN status IN ('".implode("','", $acceptedVariants)."') THEN 2
                WHEN status IN ('".implode("','", $preparingVariants)."') THEN 3
                ELSE 4
            END"
            )
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($order) {
                $createdAt = $order->created_at;
                $now = now();
                $elapsedMinutes = $createdAt->diffInMinutes($now);
                
                // SLA: 15 min = green, 25 min = yellow, 30+ = red
                $slaStatus = 'green';
                if ($elapsedMinutes > 25) {
                    $slaStatus = 'red';
                } elseif ($elapsedMinutes > 15) {
                    $slaStatus = 'yellow';
                }
                
                return [
                    'id' => $order->id,
                    'table_number' => $order->table_number,
                    'status' => OrderWorkflow::normalize($order->status),
                    'workflow_label' => OrderWorkflow::label($order->status),
                    'is_vip' => $order->is_vip ?? false,
                    'waiter_name' => $order->waiter?->name ?? 'Unassigned',
                    'elapsed_minutes' => $elapsedMinutes,
                    'elapsed_time' => $this->formatElapsedTime($elapsedMinutes),
                    'sla_status' => $slaStatus,
                    'created_at' => $createdAt->format('H:i'),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name ?? ($item->menuItem ? $item->menuItem->name : 'Custom Order'),
                            'quantity' => $item->quantity,
                            'notes' => $item->notes ?? '',
                            'status' => $item->status ?? 'pending'
                        ];
                    })
                ];
            });

        $pendingCount = $orders->whereIn('status', [OrderWorkflow::RECEIVED, OrderWorkflow::ACCEPTED])->count();
        $preparingCount = $orders->where('status', OrderWorkflow::PREPARING)->count();

        return response()->json([
            'success' => true,
            'orders' => $orders,
            'stats' => [
                'pending' => $pendingCount,
                'preparing' => $preparingCount,
                'total' => $orders->count(),
                'overdue' => $orders->where('sla_status', 'red')->count()
            ],
            'timestamp' => now()->format('H:i:s')
        ]);
    }

    /**
     * Update order status from kitchen
     */
    public function updateStatus(Request $request, $token, OrderWorkflowService $workflow)
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            // Kitchen-specific subset: only forward stages it can drive, plus cancel.
            'status' => ['required', Rule::in([
                OrderWorkflow::ACCEPTED,
                OrderWorkflow::PREPARING,
                OrderWorkflow::READY,
                OrderWorkflow::SERVED,
                OrderWorkflow::COMPLETED,
                OrderWorkflow::CANCELLED,
            ])],
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('restaurant_id', $restaurant->id)
            ->with('waiter')
            ->firstOrFail();

        $target = OrderWorkflow::normalize($request->status);
        $order = $workflow->transition($order, $target, null, 'kitchen_display');

        return response()->json([
            'success' => true,
            'message' => 'Order status updated',
            'order_id' => $order->id,
            'new_status' => $order->status,
            'workflow_label' => $order->workflowLabel(),
        ]);
    }

    /**
     * Mark individual item as cooking/ready
     */
    public function updateItemStatus(Request $request, $token, OrderWorkflowService $workflow)
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();
        
        $request->validate([
            'item_id' => 'required|exists:order_items,id',
            'status' => 'required|in:pending,cooking,ready'
        ]);
        
        $item = \App\Models\OrderItem::where('id', $request->item_id)
            ->whereHas('order', function ($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id);
            })
            ->firstOrFail();
        
        $item->update(['status' => $request->status]);
        
        // If all items are ready, mark order as ready (waiter notification handled by the workflow service).
        $order = $item->order()->with('waiter')->first();
        $allReady = $order->items()->where('status', '!=', 'ready')->count() === 0;
        $preReadyStatuses = [OrderWorkflow::RECEIVED, OrderWorkflow::ACCEPTED, OrderWorkflow::PREPARING, OrderWorkflow::READY];
        if ($allReady && in_array(OrderWorkflow::normalize($order->status), $preReadyStatuses, true)) {
            $order = $workflow->transition($order, OrderWorkflow::READY, null, 'kitchen_display_item');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Item status updated',
            'item_id' => $item->id,
            'new_status' => $request->status,
            'order_ready' => $allReady
        ]);
    }

    /**
     * Generate new kitchen token (Manager only)
     */
    public function generateToken(Request $request)
    {
        $user = Auth::user();
        $restaurant = Restaurant::findOrFail($user->restaurant_id);
        
        $restaurant->update([
            'kitchen_token' => Str::random(32),
            'kitchen_token_generated_at' => now()
        ]);
        
        return back()->with('success', 'Kitchen display link generated successfully!');
    }

    /**
     * Revoke kitchen token (Manager only)
     */
    public function revokeToken(Request $request)
    {
        $user = Auth::user();
        $restaurant = Restaurant::findOrFail($user->restaurant_id);
        
        $restaurant->update([
            'kitchen_token' => null,
            'kitchen_token_generated_at' => null
        ]);
        
        return back()->with('success', 'Kitchen display link revoked!');
    }

    /**
     * Get order history for kitchen display (completed/served orders)
     */
    public function getOrderHistory(Request $request, $token)
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();
        
        // 'ready' orders should appear in BOTH active orders (ready sidebar) AND history
        // so chefs can see orders waiting to be served
        $historyStatuses = array_merge(
            OrderWorkflow::storageVariants(OrderWorkflow::READY),
            OrderWorkflow::storageVariants(OrderWorkflow::SERVED),
            OrderWorkflow::terminalStatuses(),
        );

        $query = Order::with(['items.menuItem', 'waiter'])
            ->where('restaurant_id', $restaurant->id)
            ->whereIn('status', $historyStatuses)
            ->orderBy('updated_at', 'desc');
        
        // Filter by date - show all by default, or filter if date provided
        if ($request->has('date') && $request->date) {
            $query->whereDate('updated_at', $request->date);
        }
        // If no date provided, show last 7 days by default (not just today)
        else {
            $query->where('updated_at', '>=', now()->subDays(7));
        }
        
        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->whereIn('status', OrderWorkflow::storageVariants($request->status));
        }
        
        // Filter by table
        if ($request->has('table')) {
            $query->where('table_number', $request->table);
        }
        
        $orders = $query->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'table_number' => $order->table_number,
                'status' => OrderWorkflow::normalize($order->status),
                'workflow_label' => OrderWorkflow::label($order->status),
                'is_vip' => $order->is_vip ?? false,
                'waiter_name' => $order->waiter?->name ?? 'Unassigned',
                'total_amount' => $order->total_amount,
                'completed_at' => $order->updated_at->format('H:i'),
                'completed_time' => $order->updated_at->diffForHumans(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name ?? ($item->menuItem ? $item->menuItem->name : 'Custom Order'),
                        'quantity' => $item->quantity,
                        'status' => $item->status ?? 'pending'
                    ];
                })
            ];
        });
        
        // Get unique tables for filter - use same date logic
        $tablesQuery = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', $historyStatuses);
            
        if ($request->has('date') && $request->date) {
            $tablesQuery->whereDate('updated_at', $request->date);
        } else {
            $tablesQuery->where('updated_at', '>=', now()->subDays(7));
        }
        
        $tables = $tablesQuery->distinct()
            ->pluck('table_number')
            ->sort()
            ->values()
            ->toArray();
        
        return response()->json([
            'success' => true,
            'orders' => $orders,
            'tables' => $tables,
            'stats' => [
                'total' => $orders->count(),
                'ready' => $orders->where('status', OrderWorkflow::READY)->count(),
                'served' => $orders->where('status', OrderWorkflow::SERVED)->count(),
                'completed' => $orders->where('status', OrderWorkflow::COMPLETED)->count()
            ],
            'date' => $request->date ?? today()->toDateString()
        ]);
    }

    /**
     * Format elapsed time for display
     */
    private function formatElapsedTime($minutes)
    {
        if ($minutes < 1) {
            return 'Just now';
        } elseif ($minutes < 60) {
            return $minutes . 'm';
        } else {
            $hours = floor($minutes / 60);
            $mins = $minutes % 60;
            return $hours . 'h ' . $mins . 'm';
        }
    }
}
