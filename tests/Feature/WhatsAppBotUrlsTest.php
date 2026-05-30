<?php

use App\Support\WhatsAppBotUrls;

it('derives bot base and inbound forward urls from notify url', function () {
    config([
        'whatsapp.bot_notify_url' => 'https://wa-notify.example.com/notify',
        'app.url' => 'https://app.example.com',
    ]);

    expect(WhatsAppBotUrls::botBaseUrl())->toBe('https://wa-notify.example.com')
        ->and(WhatsAppBotUrls::inboundForwardUrl())->toBe('https://wa-notify.example.com/inbound')
        ->and(WhatsAppBotUrls::botWebhookUrl())->toBe('https://wa-notify.example.com/webhook')
        ->and(WhatsAppBotUrls::laravelWebhookUrl())->toBe('https://app.example.com/api/whatsapp/webhook');
});

it('returns null inbound url when notify url is empty', function () {
    config(['whatsapp.bot_notify_url' => '']);

    expect(WhatsAppBotUrls::inboundForwardUrl())->toBeNull();
});
