<?php

use App\Services\WhatsAppBrandedQrService;
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

it('places whatsapp icon in center without nested qr modules', function () {
    Http::fake([
        'api.qrserver.com/*' => Http::response(
            file_get_contents(base_path('tests/Fixtures/sample-qr-200.png')),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    $url = 'https://wa.me/255712345678?text='.urlencode('START_99');
    $png = app(WhatsAppBrandedQrService::class)->generate($url, 200);

    $image = imagecreatefromstring($png);
    expect($image)->not->toBeFalse();

    $size = imagesx($image);
    $radius = (int) round($size * 0.12);
    $center = (int) round($size / 2);
    $dark = 0;
    $samples = 0;

    for ($x = $center - $radius; $x <= $center + $radius; $x++) {
        for ($y = $center - $radius; $y <= $center + $radius; $y++) {
            if (($x - $center) ** 2 + ($y - $center) ** 2 > $radius ** 2) {
                continue;
            }

            $samples++;
            $rgb = imagecolorat($image, $x, $y);
            $red = ($rgb >> 16) & 0xFF;
            $green = ($rgb >> 8) & 0xFF;
            $blue = $rgb & 0xFF;

            if ($red < 48 && $green < 48 && $blue < 48) {
                $dark++;
            }
        }
    }

    imagedestroy($image);

    expect($samples)->toBeGreaterThan(0);
    expect($dark / $samples)->toBeLessThan(0.12);
});

it('rejects qr-like center logo assets', function () {
    $logoPath = public_path('images/whatsapp-icon-center.png');
    $backup = file_get_contents($logoPath);

    $qrLike = imagecreatetruecolor(64, 64);
    $black = imagecolorallocate($qrLike, 0, 0, 0);
    $white = imagecolorallocate($qrLike, 255, 255, 255);

    for ($x = 0; $x < 64; $x++) {
        for ($y = 0; $y < 64; $y++) {
            $color = (($x + $y) % 2 === 0) ? $black : $white;
            imagesetpixel($qrLike, $x, $y, $color);
        }
    }

    imagepng($qrLike, $logoPath);
    imagedestroy($qrLike);

    Http::fake([
        'api.qrserver.com/*' => Http::response(
            file_get_contents(base_path('tests/Fixtures/sample-qr-200.png')),
            200,
            ['Content-Type' => 'image/png'],
        ),
    ]);

    try {
        expect(fn () => app(WhatsAppBrandedQrService::class)->generate('https://wa.me/255712345678?text=bad', 200))
            ->toThrow(RuntimeException::class);
    } finally {
        file_put_contents($logoPath, $backup);
    }
});
