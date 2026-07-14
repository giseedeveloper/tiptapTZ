<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Jobs\SendBillImageToCustomer;
use App\Models\Order;
use App\Services\OrderWorkflowService;
use App\Support\OrderWorkflow;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Throwable;

class LiveOrderController extends Controller
{
    public function __construct(private OrderWorkflowService $workflow)
    {
    }

    public function index()
    {
        $restaurantId = auth()->user()->restaurant_id;
        $board = $this->workflow->boardForRestaurant($restaurantId);

        $receivedOrders = $board[OrderWorkflow::RECEIVED];
        $acceptedOrders = $board[OrderWorkflow::ACCEPTED];
        $preparingOrders = $board[OrderWorkflow::PREPARING];
        $readyOrders = $board[OrderWorkflow::READY];
        $servedOrders = $board[OrderWorkflow::SERVED];
        $completedOrders = $board[OrderWorkflow::COMPLETED];

        // Backward-compatible aliases for any older partials.
        $pendingOrders = $receivedOrders;
        $paidOrders = $completedOrders;

        $tables = \App\Models\Table::where('restaurant_id', $restaurantId)->get();
        $menuItems = \App\Models\MenuItem::where('restaurant_id', $restaurantId)->where('is_available', true)->get();
        $pipeline = OrderWorkflow::PIPELINE;
        $workflowMeta = OrderWorkflow::META;

        return view('manager.orders.live', compact(
            'receivedOrders',
            'acceptedOrders',
            'preparingOrders',
            'readyOrders',
            'servedOrders',
            'completedOrders',
            'pendingOrders',
            'paidOrders',
            'tables',
            'menuItems',
            'pipeline',
            'workflowMeta',
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'table_number' => 'required',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;
        $orderItems = [];

        foreach ($request->items as $itemData) {
            $menuItem = \App\Models\MenuItem::find($itemData['id']);
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

        $order = Order::create([
            'restaurant_id' => auth()->user()->restaurant_id,
            'table_number' => $request->table_number,
            'customer_phone' => $request->customer_phone,
            'customer_name' => $request->customer_name,
            'total_amount' => $totalAmount,
            'status' => OrderWorkflow::RECEIVED,
        ]);

        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        $this->workflow->markReceived($order, auth()->user(), 'manager_live');

        return redirect()->back()->with('success', 'Order created successfully');
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($request->has('status')) {
            $request->validate(['status' => OrderWorkflow::validationRule()]);

            $target = OrderWorkflow::normalize($request->status);
            $this->workflow->transition(
                $order,
                $target,
                auth()->user(),
                'manager_live',
                [],
                ensurePaymentOnComplete: $target === OrderWorkflow::COMPLETED,
            );
        }

        if ($request->has('table_number')) {
            $order->update([
                'table_number' => $request->table_number,
                'customer_phone' => $request->customer_phone,
                'customer_name' => $request->customer_name,
            ]);
        }

        return redirect()->back()->with('success', 'Order updated successfully');
    }

    /**
     * Send the WhatsApp-customer bill image notification (explicit waiter/manager step).
     * Runs synchronously against the notify endpoint so no queue worker is required.
     */
    public function sendWhatsAppBill(Order $order)
    {
        if ($order->restaurant_id !== auth()->user()->restaurant_id) {
            abort(403);
        }

        if (! OrderWorkflow::isBillStage($order->status)) {
            return redirect()->back()->with('error', 'Bill can only be sent when the order is in Served status.');
        }

        if (empty($order->whatsapp_jid)) {
            $derived = Order::normalizeWhatsAppJid(null, $order->customer_phone);
            if (filled($derived)) {
                $order->forceFill(['whatsapp_jid' => $derived])->saveQuietly();
                $order->refresh();
            }
        }

        if (empty($order->whatsapp_jid)) {
            return redirect()->back()->with('error', 'Add a customer phone number on this order (or open the chat from the WhatsApp bot) so we can deliver the bill.');
        }

        try {
            SendBillImageToCustomer::dispatchSync($order->id, true);
        } catch (Throwable $e) {
            report($e);

            if ($e instanceof RequestException) {
                $response = $e->response;
                $status = $response->status();
                $json = $response->json();
                $errorCode = is_array($json) ? ($json['error'] ?? null) : null;
                $detail = is_array($json) ? ($json['detail'] ?? null) : null;
                $hint = is_array($json) ? ($json['hint'] ?? null) : null;

                if ($status === 503 || $errorCode === 'whatsapp_not_connected') {
                    return redirect()->back()->with(
                        'error',
                        'WhatsApp is not connected on the bot (HTTP 503). Open the bot session on the VPS until it is online, then try Confirm order again.'
                    );
                }

                if ($status === 502 || $errorCode === 'send_failed') {
                    $tail = '';
                    if (is_string($detail) && $detail !== '') {
                        $tail = ' Technical: '.$detail;
                    }
                    if (is_string($hint) && $hint !== '') {
                        $tail .= ' '.$hint;
                    }

                    return redirect()->back()->with(
                        'error',
                        'WhatsApp could not deliver the bill image (HTTP 502). The notify service ran, but sending the picture failed. Typical causes: wrong chat JID for this customer (compare order `whatsapp_jid` with the address shown in bot logs, e.g. …@lid), or the bill image URL cannot be downloaded from the VPS (SSL error, 403, or firewall). Check `docker compose logs bot` on the server.'.$tail
                    );
                }

                if ($status === 401) {
                    return redirect()->back()->with(
                        'error',
                        'Notify secret mismatch (HTTP 401). Set WHATSAPP_BOT_NOTIFY_SECRET on Laravel to match NOTIFY_SECRET on the bot `.env`, then run php artisan config:clear.'
                    );
                }

                return redirect()->back()->with(
                    'error',
                    'WhatsApp notify request failed (HTTP '.$status.'). Check WHATSAPP_BOT_NOTIFY_URL, bot health, and Laravel logs.'.(is_string($detail) && $detail !== '' ? ' '.$detail : '')
                );
            }

            if ($e instanceof ConnectionException) {
                return redirect()->back()->with(
                    'error',
                    'Could not reach the WhatsApp notify URL in time. Try increasing WHATSAPP_BOT_NOTIFY_TIMEOUT (e.g. 90) in .env, then php artisan config:clear. If bot logs already show “Pushed bill image”, the message may have been sent—Laravel often stops waiting before the bot finishes fetching the bill and uploading to WhatsApp. Also verify WHATSAPP_BOT_NOTIFY_URL and that your host allows outbound HTTPS to that address.'
                );
            }

            return redirect()->back()->with(
                'error',
                'Could not send the bill via WhatsApp. Check Laravel logs and bot logs for details.'
            );
        }

        $recipient = Order::whatsAppRecipientId($order->whatsapp_jid, $order->customer_phone);

        return redirect()->back()->with(
            'success',
            'Bill image was sent to WhatsApp '.($recipient ?? 'customer').'. Ask them to open the chat with the TipTap business number ('.config('tiptap.phone_international_prefix').' …) and scroll to the latest message.'
        );
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->back()->with('success', 'Order deleted successfully');
    }
}
