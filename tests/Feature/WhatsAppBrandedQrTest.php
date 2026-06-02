<?php

use Illuminate\Support\Facades\Http;

it('returns branded whatsapp qr png for valid wa.me url', function () {
    $png = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
        true
    );

    Http::fake([
        'api.qrserver.com/*' => Http::response($png, 200, ['Content-Type' => 'image/png']),
    ]);

    $url = 'https://wa.me/255712345678?text='.urlencode('START_1');

    $this->get(route('qr.whatsapp', ['data' => $url, 'size' => 200]))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

it('rejects non wa.me qr data', function () {
    $this->get(route('qr.whatsapp', ['data' => 'https://evil.example/', 'size' => 200]))
        ->assertBadRequest();
});

it('builds branded qr url helper', function () {
    $wa = 'https://wa.me/255712345678?text=hi';

    expect(whatsapp_branded_qr_url($wa, 300))
        ->toContain('/qr/whatsapp')
        ->toContain(urlencode($wa));
});
