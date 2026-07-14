<?php

namespace App\Http\Controllers\Api;

use App\Enums\BotEngagementEvent;
use App\Enums\BotFunnelStep;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitBotFeedbackRequest;
use App\Models\Activity;
use App\Models\Category;
use App\Models\CustomerRequest;
use App\Models\Feedback;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\Table;
use App\Models\Tip;
use App\Models\TipPool;
use App\Models\User;
use App\Services\BotEventService;
use App\Services\BotFeedbackService;
use App\Services\MenuEngagementService;
use App\Services\FreeWaiterService;
use App\Services\OrderWorkflowService;
use App\Services\TableActiveOrderService;
use App\Services\TipPoolService;
use App\Support\Money;
use App\Support\OrderWorkflow;
use App\Support\WhatsAppBotBranding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WhatsAppBotController extends Controller
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    private function recordBotEngagement(
        Request $request,
        BotEngagementEvent $event,
        ?int $restaurantId = null,
        array $metadata = [],
    ): void {
        app(BotEventService::class)->record(
            event: $event,
            restaurantId: $restaurantId ?? ($request->filled('restaurant_id') ? (int) $request->input('restaurant_id') : null),
            waId: $request->input('wa_id'),
            customerPhone: $request->input('customer_phone') ?? $request->input('phone_number'),
            metadata: $metadata,
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function recordFunnelStep(
        Request $request,
        BotFunnelStep $step,
        int $restaurantId,
        array $metadata = [],
    ): void {
        app(BotEventService::class)->recordFunnelStep(
            step: $step,
            restaurantId: $restaurantId,
            waId: $request->input('wa_id'),
            customerPhone: $request->input('customer_phone') ?? $request->input('phone_number'),
            metadata: $metadata,
        );
    }

    private function markMenuEngagementConverted(Request $request, int $restaurantId): void
    {
        app(MenuEngagementService::class)->markConvertedForCustomer(
            restaurantId: $restaurantId,
            waId: $request->input('wa_id'),
            customerPhone: $request->input('customer_phone') ?? $request->input('phone_number'),
        );
    }

    private function recordPaymentSuccess(Order $order, Payment $payment): void
    {
        app(BotEventService::class)->recordFunnelStep(
            step: BotFunnelStep::PaymentSuccess,
            restaurantId: (int) $order->restaurant_id,
            customerPhone: $order->customer_phone,
            metadata: [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'method' => $payment->method,
            ],
        );
    }

    /**
     * Search for restaurants (Optimized for WhatsApp Buttons - Max 3 results)
     */
    public function searchRestaurant(Request $request)
    {
        $query = $request->input('query');

        $restaurants = Restaurant::where('name', 'like', "%{$query}%")
            ->where('is_active', true)
            ->limit(3)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'location' => $r->location,
                'support_phone' => $r->getCustomerSupportPhone(),
            ]);

        return response()->json([
            'success' => true,
            'count' => $restaurants->count(),
            'data' => $restaurants->values(),
        ]);
    }

    /**
     * Verify Restaurant and Table (For QR Scan Entry)
     */
    public function verifyRestaurant(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);

        $restaurant = Restaurant::find($request->restaurant_id);

        if (! $restaurant || ! $restaurant->is_active) {
            return response()->json(['success' => false, 'message' => 'Restaurant not found or inactive'], 404);
        }

        // Check if waiter_id or table_id is provided
        $waiterId = $request->input('waiter_id');
        $tableId = $request->input('table_id');
        $waiter = null;
        $table = null;

        if ($waiterId) {
            $waiter = User::where('id', $waiterId)
                ->where('restaurant_id', $restaurant->id)
                ->first();
        }

        if ($tableId) {
            $table = Table::withoutGlobalScopes()
                ->where('id', $tableId)
                ->where('restaurant_id', $restaurant->id)
                ->first();
        }

        return response()->json([
            'success' => true,
            'skip_standalone_welcome' => true,
            'data' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'location' => $restaurant->location,
                'support_phone' => $restaurant->getCustomerSupportPhone(),
                'table_number' => $table ? $table->name : $request->input('table_number'),
                'table_id' => $table ? $table->id : null,
                'table_tag' => $table ? $table->table_tag : null,
                'waiter_id' => $waiter ? $waiter->id : null,
                'waiter_name' => $waiter ? $waiter->name : null,
                'waiter_code' => $waiter ? $waiter->waiter_code : null,
            ],
        ]);
    }

    /**
     * Verify Service Tag (SMK-T01, SMK-W01, etc.)
     * Supports both table tags and waiter codes
     */
    public function verifyTag(Request $request)
    {
        $request->validate([
            'tag' => 'required|string|max:20',
        ]);

        $tag = strtoupper(trim($request->input('tag')));

        // Determine if it's a table tag or waiter code
        // Format: PREFIX-T## for tables, PREFIX-W## for waiters
        if (preg_match('/^([A-Z0-9]+)-T(\d+)$/i', $tag, $matches)) {
            // Table tag
            $prefix = $matches[1];

            $table = Table::withoutGlobalScopes()
                ->where('table_tag', $tag)
                ->with('restaurant')
                ->first();

            if (! $table || ! $table->restaurant || ! $table->restaurant->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid table tag or restaurant inactive',
                ], 404);
            }

            $data = [
                'restaurant_id' => $table->restaurant->id,
                'restaurant_name' => $table->restaurant->name,
                'restaurant_location' => $table->restaurant->location,
                'support_phone' => $table->restaurant->getCustomerSupportPhone(),
                'table_id' => $table->id,
                'table_name' => $table->name,
                'table_tag' => $table->table_tag,
                'waiter_id' => null,
                'waiter_name' => null,
            ];
            app(BotEventService::class)->recordQrEntryFromRequest($request, 'table', $data);

            return response()->json([
                'success' => true,
                'type' => 'table',
                'skip_standalone_welcome' => true,
                'data' => $data,
            ]);

        } elseif (preg_match('/^([A-Z0-9]+)-W(\d+)$/i', $tag, $matches)) {
            // Waiter code
            $prefix = $matches[1];

            $waiter = User::where('waiter_code', $tag)
                ->with('restaurant')
                ->first();

            if (! $waiter || ! $waiter->restaurant || ! $waiter->restaurant->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid waiter code or restaurant inactive',
                ], 404);
            }

            $data = [
                'restaurant_id' => $waiter->restaurant->id,
                'restaurant_name' => $waiter->restaurant->name,
                'restaurant_location' => $waiter->restaurant->location,
                'support_phone' => $waiter->restaurant->getCustomerSupportPhone(),
                'table_id' => null,
                'table_name' => null,
                'table_tag' => null,
                'waiter_id' => $waiter->id,
                'waiter_name' => $waiter->name,
                'waiter_code' => $waiter->waiter_code,
            ];
            app(BotEventService::class)->recordQrEntryFromRequest($request, 'waiter', $data);

            return response()->json([
                'success' => true,
                'type' => 'waiter',
                'skip_standalone_welcome' => true,
                'data' => $data,
            ]);

        } else {
            // Try to find by prefix only (might be a restaurant tag)
            $restaurant = Restaurant::where('tag_prefix', $tag)
                ->where('is_active', true)
                ->first();

            if ($restaurant) {
                $data = [
                    'restaurant_id' => $restaurant->id,
                    'restaurant_name' => $restaurant->name,
                    'restaurant_location' => $restaurant->location,
                    'support_phone' => $restaurant->getCustomerSupportPhone(),
                ];
                app(BotEventService::class)->recordQrEntryFromRequest($request, 'restaurant', $data);

                return response()->json([
                    'success' => true,
                    'type' => 'restaurant',
                    'skip_standalone_welcome' => true,
                    'data' => $data,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid tag format. Use PREFIX-T## for tables or PREFIX-W## for waiters.',
            ], 400);
        }
    }

    /**
     * Parse QR code or tag input and return appropriate data
     * Handles: START_2, START_2_T5, START_2_W3, SMK-T01, SMK-W01
     */
    public function parseEntry(Request $request)
    {
        // Support both POST body and query parameter for flexibility
        $inputValue = $request->input('input') ?? $request->query('input') ?? $request->query('text');

        if (empty($inputValue)) {
            return response()->json([
                'success' => false,
                'message' => 'The input field is required.',
                'hint' => 'Send input as POST body: {"input": "START_2_W12"} or as query: ?input=START_2_W12',
            ], 422);
        }

        $input = strtoupper(trim($inputValue));

        // Pattern 1: START_{restaurant_id}
        if (preg_match('/^START_(\d+)$/i', $input, $matches)) {
            $restaurant = Restaurant::find($matches[1]);
            if (! $restaurant || ! $restaurant->is_active) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found'], 404);
            }

            $data = [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'support_phone' => $restaurant->getCustomerSupportPhone(),
            ];
            app(BotEventService::class)->recordQrEntryFromRequest($request, 'restaurant', $data);

            return response()->json([
                'success' => true,
                'type' => 'restaurant',
                'skip_standalone_welcome' => true,
                'data' => $data,
            ]);
        }

        // Pattern 2: START_{restaurant_id}_T{table_id}
        if (preg_match('/^START_(\d+)_T(\d+)$/i', $input, $matches)) {
            $restaurant = Restaurant::find($matches[1]);
            $table = Table::withoutGlobalScopes()->find($matches[2]);

            if (! $restaurant || ! $restaurant->is_active) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found'], 404);
            }

            $data = [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'support_phone' => $restaurant->getCustomerSupportPhone(),
                'table_id' => $table ? $table->id : null,
                'table_name' => $table ? $table->name : null,
                'table_tag' => $table ? $table->table_tag : null,
            ];
            app(BotEventService::class)->recordQrEntryFromRequest($request, 'table', $data);

            return response()->json([
                'success' => true,
                'type' => 'table',
                'skip_standalone_welcome' => true,
                'data' => $data,
            ]);
        }

        // Pattern 3: START_{restaurant_id}_W{waiter_id}
        if (preg_match('/^START_(\d+)_W(\d+)$/i', $input, $matches)) {
            $restaurant = Restaurant::find($matches[1]);
            $waiter = User::find($matches[2]);

            if (! $restaurant || ! $restaurant->is_active) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found'], 404);
            }

            $data = [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'support_phone' => $restaurant->getCustomerSupportPhone(),
                'waiter_id' => $waiter ? $waiter->id : null,
                'waiter_name' => $waiter ? $waiter->name : null,
                'waiter_code' => $waiter ? $waiter->waiter_code : null,
            ];
            app(BotEventService::class)->recordQrEntryFromRequest($request, 'waiter', $data);

            return response()->json([
                'success' => true,
                'type' => 'waiter',
                'skip_standalone_welcome' => true,
                'data' => $data,
            ]);
        }

        // Pattern 4: Service tags (SMK-T01, SMK-W01)
        if (preg_match('/^([A-Z0-9]+)-(T|W)(\d+)$/i', $input)) {
            return $this->verifyTag(new Request(['tag' => $input]));
        }

        // Pattern 5: Old format START_{restaurant_id}_{table_number} (backward compatibility)
        if (preg_match('/^START_(\d+)_(\d+)$/i', $input, $matches)) {
            $restaurant = Restaurant::find($matches[1]);
            if (! $restaurant || ! $restaurant->is_active) {
                return response()->json(['success' => false, 'message' => 'Restaurant not found'], 404);
            }

            $data = [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'support_phone' => $restaurant->getCustomerSupportPhone(),
                'table_number' => $matches[2],
            ];
            app(BotEventService::class)->recordQrEntryFromRequest($request, 'table', $data);

            return response()->json([
                'success' => true,
                'type' => 'table',
                'skip_standalone_welcome' => true,
                'data' => $data,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid input format',
        ], 400);
    }

    /**
     * Get Categories for a Restaurant
     */
    public function getCategories($restaurantId)
    {
        $categories = Category::withoutGlobalScopes()->where('restaurant_id', $restaurantId)
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get Menu Items for a Category
     */
    public function getCategoryItems($categoryId)
    {
        $category = Category::withoutGlobalScopes()->find($categoryId);
        $restaurant = $category ? Restaurant::find($category->restaurant_id) : null;

        $items = MenuItem::withoutGlobalScopes()->where('category_id', $categoryId)
            ->where('is_available', true)
            ->get(['id', 'name', 'price', 'description', 'image', 'preparation_time', 'restaurant_id'])
            ->map(function ($item) use ($restaurant) {
                $item->imageUrl = $item->imageUrl();
                $item->withEta($restaurant);

                return $item;
            });

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Get Single Item Details
     */
    public function getItemDetails($itemId)
    {
        $item = MenuItem::withoutGlobalScopes()->with(['category' => function ($query) {
            $query->withoutGlobalScopes();
        }])->find($itemId);

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $item->imageUrl = $item->imageUrl();
        $item->withEta(Restaurant::find($item->restaurant_id));

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * Get Full Menu (Categories + Items) for a Restaurant
     */
    public function getFullMenu($restaurantId)
    {
        $restaurant = Restaurant::find($restaurantId);

        $categories = Category::withoutGlobalScopes()->with(['menuItems' => function ($query) {
            $query->withoutGlobalScopes()->where('is_available', true);
        }])->where('restaurant_id', $restaurantId)->get()->map(function ($category) use ($restaurant) {
            $category->imageUrl = $category->imageUrl();
            $category->menuItems->map(function ($item) use ($restaurant) {
                $item->imageUrl = $item->imageUrl();
                $item->withEta($restaurant);

                return $item;
            });

            return $category;
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Create Order from Bot
     */
    public function createOrder(Request $request, OrderWorkflowService $workflow)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_id' => 'nullable|exists:tables,id',
            'table_number' => 'nullable|string',
            'waiter_id' => 'nullable|exists:users,id',
            'customer_phone' => 'required',
            'customer_name' => 'nullable|string',
            'whatsapp_jid' => 'nullable|string|max:191',
            'items' => 'required|array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Get table number from table_id if provided
        $tableNumber = $request->table_number;
        if (! $tableNumber && $request->table_id) {
            $table = Table::withoutGlobalScopes()->find($request->table_id);
            $tableNumber = $table ? $table->name : null;
        }

        try {
            return DB::transaction(function () use ($request, $tableNumber, $workflow) {
                $totalAmount = 0;
                $orderItems = [];

                foreach ($request->items as $itemData) {
                    $menuItem = MenuItem::withoutGlobalScopes()->find($itemData['menu_item_id']);
                    $subtotal = $menuItem->price * $itemData['quantity'];
                    $totalAmount += $subtotal;

                    $orderItems[] = [
                        'menu_item_id' => $menuItem->id,
                        'name' => $menuItem->name,
                        'quantity' => $itemData['quantity'],
                        'price' => $menuItem->price,
                        'total' => $subtotal,
                    ];
                }

                $order = Order::withoutGlobalScopes()->create([
                    'restaurant_id' => $request->restaurant_id,
                    'waiter_id' => $request->waiter_id,
                    'table_number' => $tableNumber,
                    'customer_phone' => $request->customer_phone,
                    'customer_name' => $request->customer_name,
                    'whatsapp_jid' => Order::normalizeWhatsAppJid($request->whatsapp_jid, $request->customer_phone),
                    'total_amount' => $totalAmount,
                    'status' => OrderWorkflow::RECEIVED,
                ]);

                foreach ($orderItems as $item) {
                    $order->items()->create($item);
                }

                $workflow->markReceived($order, null, 'whatsapp_bot');

                Activity::create([
                    'description' => "New WhatsApp order #{$order->id} from {$request->customer_phone}",
                    'type' => 'order_created',
                    'properties' => [
                        'order_id' => $order->id,
                        'source' => 'whatsapp',
                        'waiter_id' => $request->waiter_id,
                    ],
                ]);

                $this->recordFunnelStep(
                    $request,
                    BotFunnelStep::AddToCart,
                    (int) $request->restaurant_id,
                    ['order_id' => $order->id, 'item_count' => count($orderItems)],
                );
                $this->recordFunnelStep(
                    $request,
                    BotFunnelStep::ConfirmOrder,
                    (int) $request->restaurant_id,
                    ['order_id' => $order->id],
                );

                $this->markMenuEngagementConverted($request, (int) $request->restaurant_id);

                $order->load('items');

                return response()->json([
                    'success' => true,
                    'order_id' => $order->id,
                    'total' => $totalAmount,
                    'waiter_id' => $request->waiter_id,
                    'table_number' => $tableNumber,
                    'message' => 'Order created successfully',
                    'order' => $this->formatOrderForBot($order),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Check Order & Payment Status (Polling)
     * Bot calls this repeatedly to check if payment is complete
     */
    public function getOrderStatus($orderId, OrderWorkflowService $workflow)
    {
        $order = Order::withoutGlobalScopes()->with(['restaurant', 'items.menuItem' => function ($query) {
            $query->withoutGlobalScopes();
        }, 'payments'])->find($orderId);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $payment = $order->payments()->where('method', 'ussd')->latest()->first();

        $billData = [
            'is_bill_ready' => $order->isBillStage(),
            'bill_image_url' => $order->isBillStage() ? $order->billImageUrl() : null,
        ];

        // If already completed or failed, return immediately
        if ($payment && in_array($payment->status, ['paid', 'failed', 'cancelled'])) {
            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_status' => $order->status,
                'workflow_label' => $order->workflowLabel(),
                'payment_status' => $payment->status,
                'is_paid' => $payment->status === 'paid',
                'is_failed' => in_array($payment->status, ['failed', 'cancelled']),
                'total' => $order->total_amount,
                'items' => $order->items,
                ...$billData,
            ]);
        }

        // Polling logic for Selcom - check with Selcom API
        if ($payment && $payment->status === 'pending') {
            $restaurant = $order->restaurant;

            if ($restaurant && $restaurant->hasSelcomConfigured()) {
                $selcom = new \App\Services\SelcomService;
                $result = $selcom->checkOrderStatus(
                    $restaurant->getSelcomCredentials(),
                    $payment->transaction_reference
                );
                $paymentStatus = $selcom->parsePaymentStatus($result);

                if ($paymentStatus === 'paid') {
                    $payment->update(['status' => 'paid']);
                    $workflow->completeFromPayment($order, 'whatsapp_ussd');

                    // Log successful payment
                    Activity::create([
                        'description' => 'Order #'.$order->id.' payment completed: '.Money::format($order->total_amount),
                        'type' => 'order_payment_success',
                        'properties' => [
                            'order_id' => $order->id,
                            'payment_id' => $payment->id,
                            'amount' => $order->total_amount,
                        ],
                    ]);

                    $this->recordPaymentSuccess($order, $payment);

                    // Refresh to get updated status
                    $payment->refresh();
                    $order->refresh();
                } elseif ($paymentStatus === 'failed') {
                    $payment->update(['status' => 'failed']);
                    $payment->refresh();
                }
            }
        }

        // Check if payment has expired (older than 10 minutes)
        if ($payment && $payment->status === 'pending' && $payment->created_at->diffInMinutes(now()) > 10) {
            $payment->update(['status' => 'failed']);
            $payment->refresh();
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_status' => $order->status,
            'workflow_label' => $order->workflowLabel(),
            'payment_status' => $payment ? $payment->status : 'unpaid',
            'is_paid' => $payment && $payment->status === 'paid',
            'is_failed' => $payment && in_array($payment->status, ['failed', 'cancelled']),
            'is_pending' => $payment && $payment->status === 'pending',
            'total' => $order->total_amount,
            'items' => $order->items,
            'transaction_reference' => $payment?->transaction_reference,
            ...$billData,
        ]);
    }

    /**
     * Submit Feedback from Bot
     */
    public function submitFeedback(SubmitBotFeedbackRequest $request, BotFeedbackService $botFeedbackService): JsonResponse
    {
        try {
            $payload = $botFeedbackService->buildPayload($request->validated());
        } catch (\InvalidArgumentException $exception) {
            if ($exception->getMessage() === 'no_order_for_food_rating') {
                return response()->json([
                    'success' => false,
                    'message' => 'Place an order first before rating food.',
                ], 422);
            }

            throw $exception;
        }

        Feedback::withoutGlobalScopes()->create($payload);

        $this->recordBotEngagement(
            $request,
            BotEngagementEvent::RateService,
            (int) $payload['restaurant_id'],
            ['rating' => $payload['rating'], 'type' => $payload['type'] ?? null],
        );

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully',
        ]);
    }

    /**
     * Submit Tip from Bot
     */
    public function submitTip(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            // Get waiter_id from order if exists
            $order = Order::withoutGlobalScopes()->find($request->order_id);
            $waiterId = $order ? $order->waiter_id : null;

            $tip = Tip::withoutGlobalScopes()->create([
                'restaurant_id' => $request->restaurant_id,
                'order_id' => $request->order_id,
                'waiter_id' => $waiterId,
                'amount' => $request->amount,
            ]);

            $this->recordBotEngagement(
                $request,
                BotEngagementEvent::GiveTips,
                (int) $request->restaurant_id,
                ['amount' => (float) $request->amount, 'order_id' => (int) $request->order_id],
            );

            return response()->json([
                'success' => true,
                'message' => 'Tip submitted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit tip: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initiate USSD Payment
     */
    public function initiatePayment(Request $request, OrderWorkflowService $workflow)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'phone_number' => 'required',
            'amount' => 'required|numeric',
            'network' => 'nullable|string',
        ]);

        $order = Order::withoutGlobalScopes()->with('restaurant')->find($request->order_id);
        $restaurant = $order->restaurant;

        $transactionId = 'BOT-'.$order->id.'-'.time();

        if (Setting::get('demo_push', '0') === '1') {
            $payment = Payment::create([
                'order_id' => $order->id,
                'restaurant_id' => $order->restaurant_id,
                'waiter_id' => $order->waiter_id,
                'amount' => $request->amount,
                'method' => 'ussd',
                'status' => 'paid',
                'transaction_reference' => $transactionId,
            ]);
            $workflow->completeFromPayment($order, 'whatsapp_demo');

            $this->recordBotEngagement(
                $request,
                BotEngagementEvent::PayBill,
                (int) $order->restaurant_id,
                ['order_id' => $order->id, 'method' => 'ussd', 'demo' => true],
            );
            $this->recordPaymentSuccess($order, $payment);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'message' => 'Demo: Payment marked successful (no push sent)',
            ]);
        }

        if (! $restaurant || ! $restaurant->canAcceptMobilePayments()) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile money payments are not available for this venue right now.',
            ], 400);
        }

        $selcom = new \App\Services\SelcomService;
        $result = $selcom->initiatePayment($restaurant->getSelcomCredentials(), [
            'order_id' => $transactionId,
            'email' => $order->customer_phone.'@taptap.com',
            'name' => 'WhatsApp Customer',
            'phone' => $request->phone_number,
            'amount' => $request->amount,
            'description' => 'Order #'.$order->id,
        ]);

        if (isset($result['status']) && $result['status'] === 'success') {
            $payment = Payment::create([
                'order_id' => $order->id,
                'restaurant_id' => $order->restaurant_id,
                'waiter_id' => $order->waiter_id,
                'amount' => $request->amount,
                'method' => 'ussd',
                'status' => 'pending',
                'transaction_reference' => $transactionId,
            ]);

            $this->recordBotEngagement(
                $request,
                BotEngagementEvent::PayBill,
                (int) $order->restaurant_id,
                ['order_id' => $order->id, 'method' => 'ussd', 'payment_id' => $payment->id],
            );

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'message' => 'USSD Prompt sent to '.$request->phone_number,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to initiate payment with '.config('tiptap.payment_gateway'),
            'debug' => $result,
        ], 400);
    }

    /**
     * Initiate Quick Payment (without order)
     * Allows payment of any amount with a description
     */
    public function initiateQuickPayment(Request $request, TipPoolService $tipPools)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'phone_number' => 'required',
            'amount' => 'required|numeric|min:100',
            'description' => 'required|string|max:500',
            'network' => 'nullable|string',
            'waiter_id' => 'nullable|exists:users,id',
            'tip_pool_id' => 'nullable|exists:tip_pools,id',
        ]);

        if ($request->filled('waiter_id')) {
            $tipStaff = User::query()->find($request->waiter_id);
            if (! $tipStaff || ! $tipStaff->canReceiveDigitalTips() || (int) $tipStaff->restaurant_id !== (int) $request->restaurant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Digital tipping is not enabled for this staff member. Ask the restaurant manager to enable tips for them.',
                ], 422);
            }
        }

        if ($request->filled('tip_pool_id')) {
            $pool = TipPool::query()
                ->whereKey($request->tip_pool_id)
                ->where('restaurant_id', $request->restaurant_id)
                ->first();
            if (! $pool || ! $pool->isTippable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This tip pool is not available. Ask the restaurant to enable the kitchen tip pool and add staff.',
                ], 422);
            }
        }

        $restaurant = Restaurant::find($request->restaurant_id);

        if (! $restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found',
            ], 404);
        }

        $transactionId = 'QUICK-'.$restaurant->id.'-'.time();

        if (Setting::get('demo_push', '0') === '1') {
            $paymentData = [
                'restaurant_id' => $restaurant->id,
                'customer_phone' => $request->phone_number,
                'amount' => $request->amount,
                'method' => 'ussd',
                'payment_type' => 'quick',
                'status' => 'paid',
                'transaction_reference' => $transactionId,
                'description' => $request->description,
            ];
            if (Schema::hasColumn('payments', 'waiter_id') && $request->waiter_id) {
                $paymentData['waiter_id'] = $request->waiter_id;
            }
            if (Schema::hasColumn('payments', 'tip_pool_id') && $request->tip_pool_id) {
                $paymentData['tip_pool_id'] = $request->tip_pool_id;
            }
            $payment = Payment::create($paymentData);

            if ($payment->waiter_id || $payment->tip_pool_id) {
                $tipPools->settleQuickTipPayment($payment);
            }

            Activity::create([
                'description' => 'Demo: Quick payment completed: '.Money::format($request->amount),
                'type' => 'payment_success',
                'properties' => [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'phone' => $request->phone_number,
                ],
            ]);

            $this->recordBotEngagement(
                $request,
                BotEngagementEvent::PayBill,
                (int) $restaurant->id,
                ['payment_id' => $payment->id, 'method' => 'ussd', 'quick' => true, 'demo' => true],
            );

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'message' => 'Demo: Payment marked successful (no push sent)',
                'description' => $request->description,
            ]);
        }

        if (! $restaurant->canAcceptMobilePayments()) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile money payments are not available for this venue right now.',
            ], 400);
        }

        $selcom = new \App\Services\SelcomService;
        $result = $selcom->initiatePayment($restaurant->getSelcomCredentials(), [
            'order_id' => $transactionId,
            'email' => $request->phone_number.'@taptap.com',
            'name' => 'WhatsApp Customer',
            'phone' => $request->phone_number,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        if (isset($result['status']) && $result['status'] === 'success') {
            $paymentData = [
                'restaurant_id' => $restaurant->id,
                'customer_phone' => $request->phone_number,
                'amount' => $request->amount,
                'method' => 'ussd',
                'payment_type' => 'quick',
                'status' => 'pending',
                'transaction_reference' => $transactionId,
                'description' => $request->description,
            ];
            if (Schema::hasColumn('payments', 'waiter_id') && $request->waiter_id) {
                $paymentData['waiter_id'] = $request->waiter_id;
            }
            if (Schema::hasColumn('payments', 'tip_pool_id') && $request->tip_pool_id) {
                $paymentData['tip_pool_id'] = $request->tip_pool_id;
            }
            $payment = Payment::create($paymentData);

            Activity::create([
                'description' => 'Quick payment initiated: '.Money::format($request->amount)." from {$request->phone_number}",
                'type' => 'quick_payment',
                'properties' => [
                    'payment_id' => $payment->id,
                    'source' => 'whatsapp',
                    'description' => $request->description,
                ],
            ]);

            $this->recordBotEngagement(
                $request,
                BotEngagementEvent::PayBill,
                (int) $restaurant->id,
                ['payment_id' => $payment->id, 'method' => 'ussd', 'quick' => true],
            );

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'message' => 'USSD Prompt sent to '.$request->phone_number,
                'description' => $request->description,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to initiate payment with '.config('tiptap.payment_gateway'),
            'debug' => $result,
        ], 400);
    }

    /**
     * Check Quick Payment Status (Polling)
     * Bot calls this repeatedly to check if payment is complete
     */
    public function getQuickPaymentStatus($paymentId)
    {
        $payment = Payment::where('id', $paymentId)
            ->where('payment_type', 'quick')
            ->with('restaurant')
            ->first();

        if (! $payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        // If already completed or failed, return immediately
        if (in_array($payment->status, ['paid', 'failed', 'cancelled'])) {
            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'is_complete' => $payment->status === 'paid',
                'is_failed' => in_array($payment->status, ['failed', 'cancelled']),
                'amount' => $payment->amount,
                'description' => $payment->description,
                'customer_phone' => $payment->customer_phone,
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        // Polling logic for Selcom - check with Selcom API
        if ($payment->status === 'pending' && $payment->restaurant) {
            $restaurant = $payment->restaurant;

            if ($restaurant->hasSelcomConfigured()) {
                $selcom = new \App\Services\SelcomService;
                $result = $selcom->checkOrderStatus(
                    $restaurant->getSelcomCredentials(),
                    $payment->transaction_reference
                );
                $paymentStatus = $selcom->parsePaymentStatus($result);

                // Update payment status based on Selcom response
                if ($paymentStatus === 'paid') {
                    $payment->update(['status' => 'paid']);

                    if ($payment->waiter_id || $payment->tip_pool_id) {
                        app(TipPoolService::class)->settleQuickTipPayment($payment->fresh());
                    }

                    // Log successful payment
                    Activity::create([
                        'description' => 'Quick payment completed: '.Money::format($payment->amount),
                        'type' => 'payment_success',
                        'properties' => [
                            'payment_id' => $payment->id,
                            'amount' => $payment->amount,
                            'phone' => $payment->customer_phone,
                        ],
                    ]);
                } elseif ($paymentStatus === 'failed') {
                    $payment->update(['status' => 'failed']);
                }

                // Refresh payment to get updated status
                $payment->refresh();
            }
        }

        // Check if payment has expired (older than 10 minutes)
        $isExpired = $payment->status === 'pending'
            && $payment->created_at->diffInMinutes(now()) > 10;

        if ($isExpired) {
            $payment->update(['status' => 'failed']);
            $payment->refresh();
        }

        return response()->json([
            'success' => true,
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'is_complete' => $payment->status === 'paid',
            'is_failed' => in_array($payment->status, ['failed', 'cancelled']),
            'is_pending' => $payment->status === 'pending',
            'amount' => $payment->amount,
            'description' => $payment->description,
            'customer_phone' => $payment->customer_phone,
            'transaction_reference' => $payment->transaction_reference,
            'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get Tables for a Restaurant
     */
    public function getTables($restaurantId)
    {
        $tables = Table::withoutGlobalScopes()->where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->get(['id', 'name', 'capacity']);

        return response()->json([
            'success' => true,
            'data' => $tables,
        ]);
    }

    /**
     * Check if a waiter is online (for bot: before showing "call waiter" or sending request).
     * GET /api/bot/waiter/{waiterId}/status
     */
    public function waiterStatus(string $waiterId)
    {
        $waiter = User::role('waiter')->find($waiterId);

        if (! $waiter) {
            return response()->json([
                'success' => false,
                'message' => 'Waiter not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'waiter_id' => (int) $waiter->id,
                'name' => $waiter->name,
                'is_online' => (bool) $waiter->is_online,
                'last_online_at' => $waiter->last_online_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Call Waiter from Bot
     */
    public function callWaiter(Request $request, FreeWaiterService $freeWaiterService, \App\Services\WaiterRosterService $rosterService)
    {
        // Handle both 'type' and 'request_type' (from bot)
        $type = $request->input('type') ?? $request->input('request_type');

        // Map bot values to DB values
        if ($type === 'Call Waiter') {
            $type = 'call_waiter';
        }
        if ($type === 'Request Bill') {
            $type = 'request_bill';
        }

        $request->merge(['type' => $type]);

        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_number' => 'nullable|string|max:50',
            'table_id' => 'nullable|exists:tables,id',
            'waiter_id' => 'nullable|exists:users,id',
            'type' => 'required|in:call_waiter,request_bill',
        ]);

        $table = $request->table_id ? Table::withoutGlobalScopes()->find($request->table_id) : null;
        $tableNumber = CustomerRequest::sanitizeTableNumber($request->table_number);
        if (($tableNumber === null || $tableNumber === '') && $table) {
            $tableNumber = $table->name;
        }

        // Table QR: assign a free online waiter (not busy with another open order).
        $waiterId = $request->waiter_id;
        $hasTableContext = ! empty($tableNumber) || $request->table_id;

        if (! $waiterId && $hasTableContext) {
            $freeWaiter = $rosterService->resolveWaiterForTable((int) $request->restaurant_id, $table, $freeWaiterService);

            if ($freeWaiter === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'No free waiter available right now.',
                ], 422);
            }

            $waiterId = $freeWaiter->id;
        }

        if ($waiterId) {
            $waiterForRequest = User::find($waiterId);
            if (! $waiterForRequest || ! $waiterForRequest->is_online) {
                $waiterId = null;
            }
        }

        // Get waiter info if waiter_id is set
        $waiterName = null;
        if ($waiterId) {
            $waiter = User::find($waiterId);
            $waiterName = $waiter ? $waiter->name : null;
        }

        $customerRequest = CustomerRequest::withoutGlobalScopes()->create([
            'restaurant_id' => $request->restaurant_id,
            'table_number' => $tableNumber,
            'table_id' => $request->table_id,
            'waiter_id' => $waiterId,
            'type' => $request->type,
            'status' => 'pending',
        ]);

        $message = $request->type === 'request_bill' ? 'Bill request sent' : 'Waiter called successfully';
        if ($waiterName) {
            $message = $request->type === 'request_bill'
                ? "Bill request sent to {$waiterName}"
                : "{$waiterName} has been called";
        }

        if ($request->type === 'call_waiter') {
            $this->recordBotEngagement(
                $request,
                BotEngagementEvent::CallWaiter,
                (int) $request->restaurant_id,
                [
                    'request_id' => $customerRequest->id,
                    'table_number' => $tableNumber,
                    'waiter_id' => $waiterId,
                ],
            );
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'request_id' => $customerRequest->id,
                'table_number' => $tableNumber,
                'waiter_name' => $waiterName,
            ],
        ]);
    }

    /**
     * Get Waiters for a Restaurant
     */
    public function getWaiters(Request $request, $restaurantId)
    {
        $tippableOnly = $request->boolean('tippable_only');
        $staffRole = strtolower(trim((string) $request->query('role', '')));

        $roles = match ($staffRole) {
            'waiter' => ['waiter'],
            'barista' => ['barista'],
            default => ['waiter', 'barista'],
        };

        $query = User::role($roles)
            ->activeAtRestaurant((int) $restaurantId);

        if ($tippableOnly) {
            $query->digitalTipsEnabled();
        }

        $waiters = $query->orderBy('name')->get(['id', 'name', 'is_online', 'digital_tips_enabled']);

        return response()->json([
            'success' => true,
            'data' => $waiters->map(fn ($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'is_online' => (bool) $w->is_online,
                'digital_tips_enabled' => (bool) $w->digital_tips_enabled,
                'can_receive_tips' => $w->canReceiveDigitalTips(),
                'roles' => $w->getRoleNames()->values()->all(),
            ]),
        ]);
    }

    /**
     * Options for optional post-payment tipping screen.
     */
    public function getPostPaymentTipOptions(Request $request, $restaurantId, TipPoolService $tipPools)
    {
        $preferredWaiterId = $request->filled('waiter_id') ? (int) $request->waiter_id : null;

        return response()->json([
            'success' => true,
            'data' => $tipPools->postPaymentTipOptions((int) $restaurantId, $preferredWaiterId),
        ]);
    }

    /**
     * Active tip pools customers can tip (e.g. kitchen pool).
     */
    public function getTipPools($restaurantId, TipPoolService $tipPools)
    {
        $kitchen = $tipPools->findTippableKitchenPool((int) $restaurantId);
        $data = [];
        if ($kitchen) {
            $data[] = [
                'id' => $kitchen->id,
                'name' => $kitchen->name,
                'code' => $kitchen->code,
                'distribution_method' => $kitchen->distribution_method,
                'member_count' => $kitchen->activeMembers->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get Active Order (Bill) for a Table
     */
    public function getActiveOrder(Request $request, TableActiveOrderService $tableActiveOrderService)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_number' => 'nullable|string',
            'table_id' => 'nullable|exists:tables,id',
        ]);

        if (empty($request->table_number) && empty($request->table_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Table number or table id is required.',
            ], 422);
        }

        $order = $tableActiveOrderService->findForTable(
            (int) $request->restaurant_id,
            $request->table_number,
            $request->table_id ? (int) $request->table_id : null,
        );

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'No active order found for this table',
            ], 404);
        }

        $order->load(['items.menuItem' => function ($query) {
            $query->withoutGlobalScopes();
        }, 'payments', 'waiter']);

        $latestPayment = $order->payments()->latest()->first();

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'total' => $order->total_amount,
                'status' => $order->status,
                'workflow_label' => $order->workflowLabel(),
                'waiter_id' => $order->waiter_id,
                'waiter_name' => $order->waiter?->name,
                'payment_status' => $latestPayment?->status ?? 'unpaid',
                'bill_image_url' => $order->isBillStage() ? $order->billImageUrl() : null,
                'is_bill_ready' => $order->isBillStage(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'name' => $item->name ?? ($item->menuItem ? $item->menuItem->name : 'Custom Order'),
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->total,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Latest order for a customer (used before food rating).
     */
    public function getLatestCustomerOrder(Request $request, BotFeedbackService $botFeedbackService): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'customer_phone' => 'required|string|max:30',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $orderId = $botFeedbackService->resolveLatestOrderId(
            (int) $validated['restaurant_id'],
            $validated['customer_phone'],
            isset($validated['order_id']) ? (int) $validated['order_id'] : null,
        );

        if ($orderId === null) {
            return response()->json([
                'success' => false,
                'message' => 'No order found for this customer.',
            ], 404);
        }

        $order = Order::withoutGlobalScopes()
            ->with(['items'])
            ->find($orderId);

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'table_number' => $order->table_number,
                'customer_name' => $order->customer_name,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                ])->values(),
            ],
        ]);
    }

    /**
     * Get Menu PDF for a Restaurant (WhatsApp document).
     */
    public function getMenuPdf(Request $request, $restaurantId)
    {
        $restaurant = Restaurant::find($restaurantId);

        if (! $restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found',
            ], 404);
        }

        $tableId = $request->filled('table_id') ? (int) $request->input('table_id') : null;
        $tableNumber = $request->input('table_number');

        if (! $tableNumber && $tableId) {
            $table = Table::withoutGlobalScopes()->find($tableId);
            $tableNumber = $table?->name;
        }

        app(MenuEngagementService::class)->recordMenuView(
            restaurantId: (int) $restaurant->id,
            waId: $request->input('wa_id'),
            customerPhone: $request->input('customer_phone') ?? $request->input('phone_number'),
            tableId: $tableId,
            tableNumber: is_string($tableNumber) ? $tableNumber : null,
        );

        if (! $restaurant->menu_pdf) {
            return response()->json([
                'success' => false,
                'message' => 'No menu PDF available for this restaurant',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'restaurant_id' => $restaurant->id,
                'restaurant_name' => $restaurant->name,
                'menu_pdf_url' => $restaurant->menuPdfUrl(),
                'filename' => $restaurant->menuPdfFilename(),
            ],
        ]);
    }

    /**
     * @deprecated Use getMenuPdf. Kept for older bot builds.
     */
    public function getMenuImage($restaurantId)
    {
        return $this->getMenuPdf($restaurantId);
    }

    /**
     * Create Order from Text (Natural Language)
     * table_id/table_number is optional - order can be created via waiter QR scan
     */
    public function createOrderByText(Request $request, OrderWorkflowService $workflow)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
            'table_id' => 'nullable|exists:tables,id',
            'table_number' => 'nullable|string',
            'waiter_id' => 'nullable|exists:users,id',
            'customer_phone' => 'required',
            'customer_name' => 'nullable|string',
            'whatsapp_jid' => 'nullable|string|max:191',
            'order_text' => 'required|string',
        ]);

        $text = $request->order_text;
        $restaurantId = $request->restaurant_id;

        // Get table number from table_id if provided
        $tableNumber = $request->table_number;
        if (! $tableNumber && $request->table_id) {
            $table = Table::withoutGlobalScopes()->find($request->table_id);
            $tableNumber = $table ? $table->name : null;
        }

        // Fetch all available items for this restaurant
        $menuItems = MenuItem::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->where('is_available', true)
            ->get();

        // Sort by name length descending to match longest names first (e.g. "Chips Mayai" before "Chips")
        $sortedItems = $menuItems->sortByDesc(function ($item) {
            return strlen($item->name);
        });

        $matchedItems = [];
        $totalAmount = 0;

        foreach ($sortedItems as $item) {
            // Escape special characters in item name for regex
            $escapedName = preg_quote($item->name, '/');

            // Regex to find item name, optionally preceded OR followed by a number (quantity)
            // Matches: "2 Chips", "Chips 2", "2x Chips", "Chips x2"
            $regex = '/(?:(\d+)\s*[xX]?\s*)?\b'.$escapedName.'\b(?:\s*[xX]?\s*(\d+))?/i';

            if (preg_match($regex, $text, $matches)) {
                $q1 = isset($matches[1]) && $matches[1] !== '' ? (int) $matches[1] : 0;
                $q2 = isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : 0;
                $quantity = max($q1, $q2, 1);

                $matchedItems[] = [
                    'menu_item_id' => $item->id,
                    'quantity' => $quantity,
                    'price' => $item->price,
                    'name' => $item->name, // For response and DB
                    'subtotal' => $item->price * $quantity,
                ];

                $totalAmount += $item->price * $quantity;

                // Remove the matched part from text to prevent double matching
                // We replace with spaces to preserve word boundaries for other matches
                $text = str_replace($matches[0], str_repeat(' ', strlen($matches[0])), $text);
            }
        }

        // If no items matched, create a custom order with the raw text
        if (empty($matchedItems)) {
            try {
                return DB::transaction(function () use ($request, $tableNumber, $workflow) {
                    $order = Order::withoutGlobalScopes()->create([
                        'restaurant_id' => $request->restaurant_id,
                        'waiter_id' => $request->waiter_id,
                        'table_number' => $tableNumber,
                        'customer_phone' => $request->customer_phone,
                        'customer_name' => $request->customer_name,
                        'whatsapp_jid' => Order::normalizeWhatsAppJid($request->whatsapp_jid, $request->customer_phone),
                        'total_amount' => 0,
                        'status' => OrderWorkflow::RECEIVED,
                        'notes' => 'Order from text: '.$request->order_text,
                    ]);

                    $order->items()->create([
                        'menu_item_id' => null,
                        'name' => $request->order_text,
                        'quantity' => 1,
                        'price' => 0,
                        'total' => 0,
                    ]);

                    $workflow->markReceived($order, null, 'whatsapp_bot_text');

                    Activity::create([
                        'description' => "New WhatsApp text order #{$order->id} from {$request->customer_phone}: \"{$request->order_text}\" (Unmatched)",
                        'type' => 'order_created',
                        'properties' => [
                            'order_id' => $order->id,
                            'source' => 'whatsapp_text_unmatched',
                            'waiter_id' => $request->waiter_id,
                        ],
                    ]);

                    $this->recordFunnelStep(
                        $request,
                        BotFunnelStep::AddToCart,
                        (int) $request->restaurant_id,
                        ['order_id' => $order->id, 'item_count' => 1, 'unmatched' => true],
                    );
                    $this->recordFunnelStep(
                        $request,
                        BotFunnelStep::ConfirmOrder,
                        (int) $request->restaurant_id,
                        ['order_id' => $order->id, 'unmatched' => true],
                    );

                    $order->load('items');

                    return response()->json([
                        'success' => true,
                        'order_id' => $order->id,
                        'total' => 0,
                        'items' => [['name' => $request->order_text, 'quantity' => 1, 'price' => 0]],
                        'message' => 'Order created successfully. Waiter will confirm the price.',
                        'order' => $this->formatOrderForBot($order),
                    ]);
                });
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
        }

        // Create Order with matched items
        try {
            return DB::transaction(function () use ($request, $matchedItems, $totalAmount, $tableNumber, $workflow) {
                $order = Order::withoutGlobalScopes()->create([
                    'restaurant_id' => $request->restaurant_id,
                    'waiter_id' => $request->waiter_id,
                    'table_number' => $tableNumber,
                    'customer_phone' => $request->customer_phone,
                    'customer_name' => $request->customer_name,
                    'whatsapp_jid' => Order::normalizeWhatsAppJid($request->whatsapp_jid, $request->customer_phone),
                    'total_amount' => $totalAmount,
                    'status' => OrderWorkflow::RECEIVED,
                ]);

                foreach ($matchedItems as $item) {
                    $order->items()->create([
                        'menu_item_id' => $item['menu_item_id'],
                        'name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['subtotal'],
                    ]);
                }

                $workflow->markReceived($order, null, 'whatsapp_bot_text');

                Activity::create([
                    'description' => "New WhatsApp text order #{$order->id} from {$request->customer_phone}: \"{$request->order_text}\"",
                    'type' => 'order_created',
                    'properties' => [
                        'order_id' => $order->id,
                        'source' => 'whatsapp_text',
                        'waiter_id' => $request->waiter_id,
                    ],
                ]);

                $this->recordFunnelStep(
                    $request,
                    BotFunnelStep::AddToCart,
                    (int) $request->restaurant_id,
                    ['order_id' => $order->id, 'item_count' => count($matchedItems)],
                );
                $this->recordFunnelStep(
                    $request,
                    BotFunnelStep::ConfirmOrder,
                    (int) $request->restaurant_id,
                    ['order_id' => $order->id],
                );

                $this->markMenuEngagementConverted($request, (int) $request->restaurant_id);

                $order->load('items');

                return response()->json([
                    'success' => true,
                    'order_id' => $order->id,
                    'total' => $totalAmount,
                    'items' => $matchedItems,
                    'waiter_id' => $request->waiter_id,
                    'table_number' => $tableNumber,
                    'message' => 'Order created successfully',
                    'order' => $this->formatOrderForBot($order),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Build the nested `order` payload the WhatsApp bot expects.
     *
     * @return array{id:int, total:float, status:string, workflow_label:string, table_number:?string, whatsapp_jid:?string, items:array<int,array{name:string,quantity:int,price:float,total:float}>}
     */
    protected function formatOrderForBot(Order $order): array
    {
        return [
            'id' => $order->id,
            'total' => (float) $order->total_amount,
            'status' => $order->status,
            'workflow_label' => $order->workflowLabel(),
            'table_number' => $order->table_number,
            'whatsapp_jid' => $order->whatsapp_jid,
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->name,
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
                'total' => (float) $item->total,
            ])->values()->all(),
        ];
    }

    /**
     * Branding for the global "hi" welcome card (logo image + title + body).
     */
    public function branding(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => WhatsAppBotBranding::resolve(),
        ]);
    }
}
