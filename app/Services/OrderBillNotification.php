<?php

namespace App\Services;

use App\Jobs\SendBillImageToCustomer;
use App\Models\Order;
use Throwable;

class OrderBillNotification
{
    public static function maybePushBillImage(Order $order): void
    {
        $order->refresh();

        if (empty($order->whatsapp_jid)) {
            $derived = Order::normalizeWhatsAppJid(null, $order->customer_phone);
            if (filled($derived)) {
                $order->forceFill(['whatsapp_jid' => $derived])->saveQuietly();
                $order->refresh();
            }
        }

        if (! $order->shouldPushBillImage()) {
            return;
        }

        try {
            SendBillImageToCustomer::dispatchSync($order->id, false);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
