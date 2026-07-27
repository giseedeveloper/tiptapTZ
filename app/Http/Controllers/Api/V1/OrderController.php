<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\AuthorizesRestaurantAccess;
use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderWorkflowService;
use App\Support\OrderWorkflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    use AuthorizesRestaurantAccess;

    public function __construct(private OrderWorkflowService $workflow) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_number' => 'nullable|string|max:50',
            'items' => 'required|array|min:1|max:100',
            'items.*.menu_item_id' => [
                'required',
                'integer',
                Rule::exists('menu_items', 'id')->where(
                    fn ($query) => $query->where('restaurant_id', $request->input('restaurant_id'))
                ),
            ],
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->authorizeRestaurantAccess($request, (int) $validated['restaurant_id']);

        $order = DB::transaction(function () use ($request, $validated): Order {
            $order = Order::withoutGlobalScopes()->create([
                'restaurant_id' => $validated['restaurant_id'],
                'waiter_id' => $request->user()->hasRole('waiter') ? $request->user()->id : null,
                'table_number' => $validated['table_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => OrderWorkflow::RECEIVED,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::withoutGlobalScopes()
                    ->where('restaurant_id', $validated['restaurant_id'])
                    ->findOrFail($item['menu_item_id']);
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

            return $this->workflow->markReceived($order, $request->user(), 'api_v1');
        });

        return response()->json($order->load('orderItems.menuItem'), 201);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeOrderAccess($request, $order);

        return response()->json($order->load('orderItems.menuItem', 'payments'));
    }

    public function status(Request $request, Order $order)
    {
        $this->authorizeOrderAccess($request, $order);

        return response()->json([
            'status' => $order->status,
            'workflow_label' => $order->workflowLabel(),
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeOrderAccess($request, $order);

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
