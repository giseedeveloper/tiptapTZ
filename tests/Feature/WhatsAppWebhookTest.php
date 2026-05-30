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
