<?php

namespace App\Support;

class WhatsAppBotUrls
{
    public static function botBaseUrl(): ?string
    {
        $notify = rtrim((string) config('whatsapp.bot_notify_url', ''), '/');

        if ($notify === '') {
            return null;
        }

        $base = preg_replace('#/notify$#', '', $notify);

        return $base !== '' ? $base : null;
    }

    public static function inboundForwardUrl(): ?string
    {
        $base = self::botBaseUrl();

        return $base !== null ? $base.'/inbound' : null;
    }

    public static function laravelWebhookUrl(): string
    {
        return rtrim((string) config('app.url'), '/').'/api/whatsapp/webhook';
    }

    public static function botWebhookUrl(): ?string
    {
        $base = self::botBaseUrl();

        return $base !== null ? $base.'/webhook' : null;
    }
}
