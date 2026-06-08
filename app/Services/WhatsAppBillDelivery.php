<?php

namespace App\Services;

use App\Jobs\SendBillImageToCustomer;
use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Throwable;

class WhatsAppBillDelivery
{
    /**
     * Explicitly send (or resend) the WhatsApp bill image for a served order.
     *
     * @return array{
     *     ok: bool,
     *     message: string,
     *     recipient: ?string,
     *     http_status: int,
     *     error_code: ?string
     * }
     */
    public function sendExplicit(Order $order, bool $force = true): array
    {
        if ($order->status !== 'served') {
            return [
                'ok' => false,
                'message' => 'Bill can only be sent when the order is in Served status.',
                'recipient' => null,
                'http_status' => 422,
                'error_code' => 'invalid_status',
            ];
        }

        if (empty($order->whatsapp_jid)) {
            $derived = Order::normalizeWhatsAppJid(null, $order->customer_phone);
            if (filled($derived)) {
                $order->forceFill(['whatsapp_jid' => $derived])->saveQuietly();
                $order->refresh();
            }
        }

        if (empty($order->whatsapp_jid)) {
            return [
                'ok' => false,
                'message' => 'Add a customer phone number on this order (or open the chat from the WhatsApp bot) so we can deliver the bill.',
                'recipient' => null,
                'http_status' => 422,
                'error_code' => 'missing_whatsapp_target',
            ];
        }

        try {
            SendBillImageToCustomer::dispatchSync($order->id, $force);
        } catch (Throwable $e) {
            report($e);

            if ($e instanceof RequestException) {
                return $this->requestExceptionResult($e);
            }

            if ($e instanceof ConnectionException) {
                return [
                    'ok' => false,
                    'message' => 'Could not reach the WhatsApp notify URL in time. Try increasing WHATSAPP_BOT_NOTIFY_TIMEOUT (e.g. 90) in .env, then php artisan config:clear. If bot logs already show “Pushed bill image”, the message may have been sent—Laravel often stops waiting before the bot finishes fetching the bill and uploading to WhatsApp. Also verify WHATSAPP_BOT_NOTIFY_URL and that your host allows outbound HTTPS to that address.',
                    'recipient' => null,
                    'http_status' => 504,
                    'error_code' => 'notify_unreachable',
                ];
            }

            return [
                'ok' => false,
                'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Could not send the bill via WhatsApp. Check Laravel logs and bot logs for details.',
                'recipient' => null,
                'http_status' => 502,
                'error_code' => 'send_failed',
            ];
        }

        $order->refresh();
        $recipient = Order::whatsAppRecipientId($order->whatsapp_jid, $order->customer_phone);

        return [
            'ok' => true,
            'message' => 'Bill image was sent to WhatsApp '.($recipient ?? 'customer').'. Ask them to open the chat with the TipTap business number ('.config('tiptap.phone_international_prefix').' …) and scroll to the latest message.',
            'recipient' => $recipient,
            'http_status' => 200,
            'error_code' => null,
        ];
    }

    /**
     * @return array{
     *     ok: bool,
     *     message: string,
     *     recipient: ?string,
     *     http_status: int,
     *     error_code: ?string
     * }
     */
    private function requestExceptionResult(RequestException $e): array
    {
        $response = $e->response;
        $status = $response->status();
        $json = $response->json();
        $errorCode = is_array($json) ? ($json['error'] ?? null) : null;
        $detail = is_array($json) ? ($json['detail'] ?? null) : null;
        $hint = is_array($json) ? ($json['hint'] ?? null) : null;

        if ($status === 503 || $errorCode === 'whatsapp_not_connected') {
            return [
                'ok' => false,
                'message' => 'WhatsApp is not connected on the bot (HTTP 503). Open the bot session on the VPS until it is online, then try Confirm order again.',
                'recipient' => null,
                'http_status' => 503,
                'error_code' => 'whatsapp_not_connected',
            ];
        }

        if ($status === 502 || $errorCode === 'send_failed') {
            $tail = '';
            if (is_string($detail) && $detail !== '') {
                $tail = ' Technical: '.$detail;
            }
            if (is_string($hint) && $hint !== '') {
                $tail .= ' '.$hint;
            }

            return [
                'ok' => false,
                'message' => 'WhatsApp could not deliver the bill image (HTTP 502). The notify service ran, but sending the picture failed. Typical causes: wrong chat JID for this customer (compare order `whatsapp_jid` with the address shown in bot logs, e.g. …@lid), or the bill image URL cannot be downloaded from the VPS (SSL error, 403, or firewall). Check `docker compose logs bot` on the server.'.$tail,
                'recipient' => null,
                'http_status' => 502,
                'error_code' => 'send_failed',
            ];
        }

        if ($status === 401) {
            return [
                'ok' => false,
                'message' => 'Notify secret mismatch (HTTP 401). Set WHATSAPP_BOT_NOTIFY_SECRET on Laravel to match NOTIFY_SECRET on the bot `.env`, then run php artisan config:clear.',
                'recipient' => null,
                'http_status' => 502,
                'error_code' => 'notify_unauthorized',
            ];
        }

        return [
            'ok' => false,
            'message' => 'WhatsApp notify request failed (HTTP '.$status.'). Check WHATSAPP_BOT_NOTIFY_URL, bot health, and Laravel logs.'.(is_string($detail) && $detail !== '' ? ' '.$detail : ''),
            'recipient' => null,
            'http_status' => $status >= 400 && $status < 600 ? $status : 502,
            'error_code' => is_string($errorCode) ? $errorCode : 'notify_failed',
        ];
    }
}
