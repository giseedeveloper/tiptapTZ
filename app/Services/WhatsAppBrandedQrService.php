<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class WhatsAppBrandedQrService
{
    public function generate(string $waMeUrl, int $size = 400): string
    {
        $this->assertValidWaMeUrl($waMeUrl);
        $size = max(100, min(2000, $size));

        $cachePath = $this->cachePath($waMeUrl, $size);
        $disk = Storage::disk('local');

        if ($disk->exists($cachePath)) {
            $cached = $disk->get($cachePath);

            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        }

        $png = $this->buildPng($waMeUrl, $size);
        $disk->put($cachePath, $png);

        return $png;
    }

    public function assertValidWaMeUrl(string $url): void
    {
        if (! preg_match('#^https://wa\.me/[0-9]{6,20}(\?text=.*)?$#', $url)) {
            throw new InvalidArgumentException('QR data must be a https://wa.me/ link.');
        }
    }

    private function cachePath(string $waMeUrl, int $size): string
    {
        return 'qr-cache/'.hash('sha256', $waMeUrl.'|'.$size).'.png';
    }

    private function buildPng(string $waMeUrl, int $size): string
    {
        $response = Http::timeout(20)->get('https://api.qrserver.com/v1/create-qr-code/', [
            'size' => "{$size}x{$size}",
            'data' => $waMeUrl,
            'ecc' => 'H',
            'margin' => 1,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to generate base QR code.');
        }

        $qr = @imagecreatefromstring($response->body());

        if ($qr === false) {
            throw new RuntimeException('Invalid QR image data.');
        }

        $this->applyCenterLogo($qr, $size);

        ob_start();
        imagepng($qr);
        $png = ob_get_clean();

        if ($png === false || $png === '') {
            throw new RuntimeException('Failed to encode branded QR image.');
        }

        return $png;
    }

    /**
     * @param  \GdImage  $qr
     */
    private function applyCenterLogo($qr, int $size): void
    {
        $logoPath = public_path('images/whatsapp-icon-center.png');

        if (! is_file($logoPath)) {
            return;
        }

        $logo = @imagecreatefrompng($logoPath);

        if ($logo === false) {
            return;
        }

        $logoSize = (int) round($size * 0.22);
        $plateSize = (int) round($size * 0.26);
        $logoResized = imagecreatetruecolor($logoSize, $logoSize);
        imagealphablending($logoResized, false);
        imagesavealpha($logoResized, true);
        $transparent = imagecolorallocatealpha($logoResized, 0, 0, 0, 127);
        imagefilledrectangle($logoResized, 0, 0, $logoSize, $logoSize, $transparent);
        imagecopyresampled(
            $logoResized,
            $logo,
            0,
            0,
            0,
            0,
            $logoSize,
            $logoSize,
            imagesx($logo),
            imagesy($logo)
        );

        $plate = imagecreatetruecolor($plateSize, $plateSize);
        $white = imagecolorallocate($plate, 255, 255, 255);
        imagefilledellipse($plate, (int) ($plateSize / 2), (int) ($plateSize / 2), $plateSize, $plateSize, $white);

        $cx = (int) (($size - $plateSize) / 2);
        $cy = (int) (($size - $plateSize) / 2);
        imagecopy($qr, $plate, $cx, $cy, 0, 0, $plateSize, $plateSize);

        $lx = (int) (($size - $logoSize) / 2);
        $ly = (int) (($size - $logoSize) / 2);
        imagecopy($qr, $logoResized, $lx, $ly, 0, 0, $logoSize, $logoSize);
    }
}
