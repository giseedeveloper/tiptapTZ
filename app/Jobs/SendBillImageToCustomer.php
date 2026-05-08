<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            return;
        }

        if ($order->status !== 'served' || empty($order->whatsapp_jid)) {
            Log::warning('Bill image skipped: order not served or no WhatsApp JID.', [
                'order_id' => $order->id ?? null,
            ]);

            return;
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

            return;
        }

        $payload = [
            'event' => 'bill_image',
            'order_id' => $order->id,
            'jid' => $order->whatsapp_jid,
            'bill_image_url' => $order->billImageUrl(),
            'restaurant_name' => $order->restaurant?->name,
            'total_amount' => (float) $order->total_amount,
            'caption' => $this->buildCaption($order),
        ];

        $response = Http::timeout((int) config('whatsapp.bot_notify_timeout', 8))
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
