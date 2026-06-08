<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SendBillImageToCustomer implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $backoff = 30;

    public function __construct(
        public int $orderId,
        public bool $force = false
    ) {}

    public function handle(): void
    {
        $order = Order::withoutGlobalScopes()
            ->with('restaurant')
            ->find($this->orderId);

        if (! $order) {
            if ($this->force) {
                throw new RuntimeException('Order not found for bill image push.');
            }

            return;
        }

        if ($order->status !== 'served') {
            Log::warning('Bill image skipped: order not served.', [
                'order_id' => $order->id,
            ]);
            if ($this->force) {
                throw new RuntimeException('Bill image can only be sent when the order is in Served status.');
            }

            return;
        }

        $jid = Order::normalizeWhatsAppJid($order->whatsapp_jid, $order->customer_phone);
        $recipientId = Order::whatsAppRecipientId($order->whatsapp_jid, $order->customer_phone);
        if ($recipientId === null || $recipientId === '') {
            Log::warning('Bill image skipped: no WhatsApp JID and no usable customer phone.', [
                'order_id' => $order->id,
            ]);
            if ($this->force) {
                throw new RuntimeException('Cannot send bill image: save the customer WhatsApp JID on the order or a valid customer phone.');
            }

            return;
        }

        if (! filled($order->whatsapp_jid) && filled($jid)) {
            $order->forceFill(['whatsapp_jid' => $jid])->saveQuietly();
        }

        if (! $this->force && ! is_null($order->bill_image_pushed_at)) {
            return;
        }

        $url = config('whatsapp.bot_notify_url');
        $secret = config('whatsapp.bot_notify_secret');

        if (empty($url) || empty($secret)) {
            Log::warning('WhatsApp bot notify URL/secret missing; cannot push bill image.', [
                'order_id' => $order->id,
            ]);
            if ($this->force) {
                throw new RuntimeException('WhatsApp bot notify URL or secret is not configured.');
            }

            return;
        }

        $payload = [
            'event' => 'bill_image',
            'order_id' => $order->id,
            'jid' => $recipientId,
            'force' => $this->force,
            'bill_image_url' => $order->billImageUrl(),
            'restaurant_name' => $order->restaurant?->name,
            'total_amount' => (float) $order->total_amount,
            'caption' => $this->buildCaption($order),
        ];

        $timeout = max(15, (int) config('whatsapp.bot_notify_timeout', 60));

        $response = Http::timeout($timeout)
            ->connectTimeout(min(25, $timeout))
            ->withHeaders(['X-Bot-Secret' => $secret])
            ->acceptJson()
            ->asJson()
            ->post($url, $payload);

        if (! $response->successful()) {
            Log::warning('Bill image push to bot failed.', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $response->throw();
        }

        $body = $response->json();
        if (is_array($body) && ($body['deduped'] ?? false) === true && ! $this->force) {
            Log::info('Bill image push skipped by bot dedupe.', [
                'order_id' => $order->id,
                'recipient' => $body['recipient'] ?? $recipientId,
            ]);

            return;
        }

        if (is_array($body) && ($body['deduped'] ?? false) === true && $this->force) {
            throw new RuntimeException('WhatsApp bot skipped resending the bill (deduped). Restart the bot or retry shortly.');
        }

        Log::info('Bill image push accepted by WhatsApp bot.', [
            'order_id' => $order->id,
            'recipient' => is_array($body) ? ($body['recipient'] ?? $recipientId) : $recipientId,
            'message_id' => is_array($body) ? ($body['message_id'] ?? null) : null,
        ]);

        $order->markBillImagePushed();
    }

    protected function buildCaption(Order $order): string
    {
        $restaurant = $order->restaurant?->name ?? 'TipTap';
        $total = number_format((float) $order->total_amount, 0);

        return "🧾 *Your bill from {$restaurant} is ready.*\n"
            ."Order #{$order->id} | Total: {$total}/=\n"
            .'Please review and proceed to payment when ready.';
    }
}
