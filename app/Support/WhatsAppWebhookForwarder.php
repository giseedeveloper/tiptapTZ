<?php

namespace App\Support;

/**
 * Routes Meta webhook payloads to the correct regional bot when multiple
 * WhatsApp phone numbers share one Meta app callback (TZ Laravel URL).
 */
class WhatsAppWebhookForwarder
{
    /**
     * @return array{url: string, secret: string}|null
     */
    public static function resolve(array $payload): ?array
    {
        $phoneId = self::extractPhoneNumberId($payload);

        if ($phoneId !== null) {
            foreach (self::phoneRoutes() as $routePhoneId => $route) {
                if ($phoneId === (string) $routePhoneId) {
                    return [
                        'url' => rtrim((string) $route['inbound_url'], '/'),
                        'secret' => (string) ($route['secret'] ?? ''),
                    ];
                }
            }
        }

        $defaultUrl = WhatsAppBotUrls::inboundForwardUrl();

        if ($defaultUrl === null) {
            return null;
        }

        return [
            'url' => $defaultUrl,
            'secret' => (string) config('whatsapp.bot_notify_secret', ''),
        ];
    }

    public static function extractPhoneNumberId(array $payload): ?string
    {
        foreach ($payload['entry'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            foreach ($entry['changes'] ?? [] as $change) {
                if (! is_array($change)) {
                    continue;
                }

                $id = $change['value']['metadata']['phone_number_id'] ?? null;

                // Meta may send phone_number_id as a JSON number; cast so SA routing works.
                if (is_int($id) || is_float($id) || (is_string($id) && $id !== '')) {
                    return (string) $id;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, array{inbound_url: string, secret: string}>
     */
    private static function phoneRoutes(): array
    {
        $routes = [];

        $saPhoneId = trim((string) config('whatsapp.sa_phone_number_id', ''));
        $saInbound = trim((string) config('whatsapp.sa_bot_inbound_url', ''));
        $saSecret = trim((string) config('whatsapp.sa_bot_notify_secret', ''));

        if ($saPhoneId !== '' && $saInbound !== '') {
            $routes[$saPhoneId] = [
                'inbound_url' => $saInbound,
                'secret' => $saSecret,
            ];
        }

        return $routes;
    }
}
