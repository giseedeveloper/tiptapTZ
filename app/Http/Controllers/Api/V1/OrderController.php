<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderWorkflowService;
use App\Support\OrderWorkflow;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderWorkflowService $workflow)
    {
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_number' => 'nullable|string',
            'items' => 'required|array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $order = Order::create([
            'restaurant_id' => $validated['restaurant_id'],
            'table_number' => $validated['table_number'],
            'notes' => $validated['notes'] ?? null,
            'status' => OrderWorkflow::RECEIVED,
            'total_amount' => 0,
        ]);

        $totalAmount = 0;

        foreach ($validated['items'] as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            $price = $menuItem->price;
            $total = $price * $item['quantity'];

            OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'name' => $menuItem->name,
                'quantity' => $item['quantity'],
                'price' => $price,
                'total' => $total,
            ]);

            $totalAmount += $total;
        }

        $order->update(['total_amount' => $totalAmount]);
        $this->workflow->markReceived($order, $request->user(), 'api_v1');

        return response()->json($order->load('orderItems.menuItem'), 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load('orderItems.menuItem', 'payments'));
    }

    public function status(Order $order)
    {
        return response()->json([
            'status' => $order->status,
            'workflow_label' => $order->workflowLabel(),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => OrderWorkflow::validationRule(),
        ]);

        $target = OrderWorkflow::normalize($validated['status']);

        $order = $this->workflow->transition(
            $order,
            $target,
            $request->user(),
            'api_v1',
            [],
            ensurePaymentOnComplete: $target === OrderWorkflow::COMPLETED,
        );

        return response()->json([
            'status' => $order->status,
            'workflow_label' => $order->workflowLabel(),
            'order' => $order,
        ]);
    }
}
