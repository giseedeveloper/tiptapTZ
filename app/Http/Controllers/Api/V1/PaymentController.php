<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Concerns\AuthorizesRestaurantAccess;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderWorkflowService;
use App\Services\SelcomService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    use AuthorizesRestaurantAccess;

    protected SelcomService $selcom;

    public function __construct(SelcomService $selcom, private OrderWorkflowService $workflow)
    {
        $this->selcom = $selcom;
    }

    /**
     * Initiate USSD Push Payment for an Order
     */
    public function ussdRequest(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'phone_number' => 'required|string|max:30',
        ]);

        $order = Order::with('restaurant')->find($validated['order_id']);
        $this->authorizeOrderAccess($request, $order);
        $idempotencyKey = trim((string) $request->header('Idempotency-Key', ''));
        if (strlen($idempotencyKey) > 100) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'The idempotency key may not be greater than 100 characters.',
            ]);
        }
        $idempotencyKey = $idempotencyKey !== '' ? $idempotencyKey : null;

        $paymentLock = Cache::lock('payment-initiation:order:'.$order->id, 45);
        if (! $paymentLock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'A payment request is already being processed for this order.',
            ], 409);
        }

        try {
            $order->refresh();
            $restaurant = $order->restaurant;

            if ($idempotencyKey) {
                $existing = Payment::query()->where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    abort_unless((int) $existing->order_id === (int) $order->id, 409);

                    return response()->json([
                        'success' => in_array($existing->status, ['pending', 'paid', 'completed'], true),
                        'payment_id' => $existing->id,
                        'status' => $existing->status,
                        'transaction_reference' => $existing->transaction_reference,
                        'reused' => true,
                    ]);
                }
            }

            $activePayment = $order->payments()
                ->where('method', 'ussd')
                ->whereIn('status', ['pending', 'paid', 'completed'])
                ->latest('id')
                ->first();

            if ($activePayment) {
                return response()->json([
                    'success' => true,
                    'payment_id' => $activePayment->id,
                    'status' => $activePayment->status,
                    'transaction_reference' => $activePayment->transaction_reference,
                    'reused' => true,
                ]);
            }

            if (! $restaurant || ! $restaurant->canAcceptMobilePayments()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mobile money payments are not available for this venue right now.',
                ], 400);
            }

            $transactionRef = 'TXN-'.Str::uuid();
            $result = $this->selcom->initiatePayment($restaurant->getSelcomCredentials(), [
                'order_id' => $transactionRef,
                'email' => 'customer@taptap.co.tz',
                'name' => 'Customer',
                'phone' => $validated['phone_number'],
                'amount' => $order->total_amount,
                'description' => 'Order #'.$order->id,
            ]);

            if (isset($result['status']) && $result['status'] === 'success') {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'restaurant_id' => $restaurant->id,
                    'waiter_id' => $order->waiter_id,
                    'customer_phone' => $validated['phone_number'],
                    'amount' => $order->total_amount,
                    'method' => 'ussd',
                    'status' => 'pending',
                    'transaction_reference' => $transactionRef,
                    'idempotency_key' => $idempotencyKey,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'USSD push sent to '.$validated['phone_number'],
                    'transaction_reference' => $transactionRef,
                    'payment_id' => $payment->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to initiate payment',
            ], 400);
        } finally {
            $paymentLock->release();
        }
    }

    /**
     * Change notification before payment: get change to give when customer pays cash.
     * Call this before confirming cash payment so the app can show "Change to give: X {{ currency }}".
     */
    public function cashChangeNotification(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount_received' => 'required|numeric|min:0',
        ]);

        $order = Order::find($validated['order_id']);
        $this->authorizeOrderAccess($request, $order);
        $orderTotal = (float) $order->total_amount;
        $amountReceived = (float) $validated['amount_received'];
        $changeToGive = max(0, $amountReceived - $orderTotal);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_total' => $orderTotal,
            'amount_received' => $amountReceived,
            'change_to_give' => $changeToGive,
            'message' => $changeToGive > 0
                ? 'Change to give to customer: '.Money::format($changeToGive)
                : ($amountReceived >= $orderTotal ? 'Exact amount or no change needed.' : 'Amount received is less than order total.'),
        ]);
    }

    /**
     * Record Cash Payment for an Order.
     * Optional amount_received: when provided, response includes change_to_give for notification/receipt.
     */
    public function cashPayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount_received' => 'nullable|numeric|min:0',
        ]);

        $order = Order::with('restaurant')->find($validated['order_id']);
        $this->authorizeOrderAccess($request, $order);

        [$payment, $orderTotal, $amountReceived, $changeToGive] = DB::transaction(function () use ($order, $validated): array {
            $order = Order::withoutGlobalScopes()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();
            $orderTotal = (float) $order->total_amount;
            $amountReceived = isset($validated['amount_received']) ? (float) $validated['amount_received'] : null;

            if ($amountReceived !== null && $amountReceived + 0.00001 < $orderTotal) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Amount received cannot be less than the order total.',
                ]);
            }

            $changeToGive = $amountReceived !== null ? max(0, $amountReceived - $orderTotal) : null;
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->where('method', 'cash')
                ->whereIn('status', ['paid', 'completed'])
                ->first();

            if (! $payment) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'restaurant_id' => $order->restaurant_id,
                    'waiter_id' => $order->waiter_id,
                    'amount' => $order->total_amount,
                    'method' => 'cash',
                    'status' => 'paid',
                    'settled_at' => now(),
                    'transaction_reference' => 'CASH-'.Str::uuid(),
                ]);
            }

            $this->workflow->completeFromPayment($order, 'cash');

            return [$payment, $orderTotal, $amountReceived, $changeToGive];
        });

        $response = [
            'success' => true,
            'payment' => $payment,
        ];
        if ($changeToGive !== null) {
            $response['change_to_give'] = $changeToGive;
            $response['order_total'] = $orderTotal;
            $response['amount_received'] = $amountReceived;
            $response['message'] = $changeToGive > 0
                ? 'Change to give to customer: '.Money::format($changeToGive)
                : 'Payment recorded. No change needed.';
        }

        return response()->json($response);
    }

    /**
     * Check Payment Status (Polling)
     * This endpoint is called repeatedly by clients to check payment status
     */
    public function status(Request $request, Order $order)
    {
        $this->authorizeOrderAccess($request, $order);
        $order->load('restaurant');
        $payment = $order->payments()->where('method', 'ussd')->latest()->first();

        if ($payment && $payment->status === 'pending') {
            $restaurant = $order->restaurant;

            if ($restaurant && $restaurant->hasSelcomConfigured()) {
                $result = $this->selcom->checkOrderStatus(
                    $restaurant->getSelcomCredentials(),
                    $payment->transaction_reference
                );
                $paymentStatus = $this->selcom->parsePaymentStatus($result);

                if ($paymentStatus === 'paid') {
                    $payment->update(['status' => 'paid', 'settled_at' => now()]);
                    $this->workflow->completeFromPayment($order, 'ussd');
                } elseif ($paymentStatus === 'failed') {
                    $payment->update(['status' => 'failed']);
                }
            }
        }

        return response()->json([
            'success' => true,
            'status' => $payment ? $payment->status : 'unpaid',
            'payment' => $payment,
            'order_status' => $order->fresh()->status,
        ]);
    }
}
