<?php

use Illuminate\Support\Facades\Http;

it('verifies meta webhook handshake', function () {
    config(['services.whatsapp.verify_token' => 'test-verify-token']);

    $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=test-verify-token&hub_challenge=12345')
        ->assertOk()
        ->assertSee('12345');
});

it('forwards signed webhook payload to bot inbound url', function () {
    config([
        'services.whatsapp.app_secret' => '',
        'whatsapp.bot_notify_url' => 'https://bot.example.com/notify',
        'whatsapp.bot_notify_secret' => 'shared-secret',
        'whatsapp.sa_phone_number_id' => '',
        'whatsapp.sa_bot_inbound_url' => '',
        'whatsapp.sa_bot_notify_secret' => '',
    ]);

    Http::fake();

    $payload = [
        'object' => 'whatsapp_business_account',
        'entry' => [],
    ];

    $this->postJson('/api/whatsapp/webhook', $payload)
        ->assertOk()
        ->assertJson(['status' => 'received']);

    Http::assertSent(function ($request) use ($payload) {
        return $request->url() === 'https://bot.example.com/inbound'
            && $request->hasHeader('X-Bot-Secret', 'shared-secret')
            && $request['object'] === $payload['object'];
    });
});

it('forwards sa phone number webhooks to the sa bot inbound url', function () {
    config([
        'services.whatsapp.app_secret' => '',
        'whatsapp.bot_notify_url' => 'https://tz-bot.example.com/notify',
        'whatsapp.bot_notify_secret' => 'tz-secret',
        'whatsapp.sa_phone_number_id' => '1083173148222454',
        'whatsapp.sa_bot_inbound_url' => 'https://sa-bot.example.com/inbound',
        'whatsapp.sa_bot_notify_secret' => 'sa-secret',
    ]);

    Http::fake();

    $payload = [
        'object' => 'whatsapp_business_account',
        'entry' => [[
            'changes' => [[
                'value' => [
                    // Meta often sends this as a JSON number, not a string.
                    'metadata' => ['phone_number_id' => 1083173148222454],
                    'messages' => [[
                        'from' => '27753228505',
                        'type' => 'text',
                        'text' => ['body' => 'Hi'],
                    ]],
                ],
            ]],
        ]],
    ];

    $this->postJson('/api/whatsapp/webhook', $payload)
        ->assertOk()
        ->assertJson(['status' => 'received']);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://sa-bot.example.com/inbound'
            && $request->hasHeader('X-Bot-Secret', 'sa-secret');
    });
});
