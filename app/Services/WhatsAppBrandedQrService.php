<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;

class WhatsAppBrandedQrService
{
    private const CACHE_VERSION = 'v2';

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
        return 'qr-cache/'.self::CACHE_VERSION.'/'.hash('sha256', $waMeUrl.'|'.$size).'.png';
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

        if (! imageistruecolor($qr)) {
            imagepalettetotruecolor($qr);
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

        $this->assertCenterLogoAsset($logo);

        $plateSize = (int) round($size * 0.28);
        $logoSize = (int) round($size * 0.20);

        $cx = (int) (($size - $plateSize) / 2);
        $cy = (int) (($size - $plateSize) / 2);
        $this->drawWhiteCircle($qr, $cx, $cy, $plateSize);

        $logoResized = $this->resizeLogoWithAlpha($logo, $logoSize);
        $lx = (int) (($size - $logoSize) / 2);
        $ly = (int) (($size - $logoSize) / 2);
        $this->copyWithAlpha($qr, $logoResized, $lx, $ly);
    }

    /**
     * Reject assets that look like QR codes (prevents nested-QR branding bug).
     *
     * @param  \GdImage  $logo
     */
    private function assertCenterLogoAsset($logo): void
    {
        $width = imagesx($logo);
        $height = imagesy($logo);
        $darkPixels = 0;
        $samples = 0;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = imagecolorat($logo, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;

                if ($alpha >= 100) {
                    continue;
                }

                $samples++;
                $red = ($rgba >> 16) & 0xFF;
                $green = ($rgba >> 8) & 0xFF;
                $blue = $rgba & 0xFF;

                if ($red < 48 && $green < 48 && $blue < 48) {
                    $darkPixels++;
                }
            }
        }

        if ($samples === 0) {
            throw new RuntimeException('WhatsApp center logo asset is empty.');
        }

        if (($darkPixels / $samples) > 0.34) {
            throw new RuntimeException('WhatsApp center logo asset looks like a QR code, not an icon.');
        }
    }

    /**
     * @param  \GdImage  $qr
     */
    private function drawWhiteCircle($qr, int $x, int $y, int $diameter): void
    {
        $plate = imagecreatetruecolor($diameter, $diameter);
        $white = imagecolorallocate($plate, 255, 255, 255);
        imagefilledellipse($plate, (int) ($diameter / 2), (int) ($diameter / 2), $diameter, $diameter, $white);
        imagecopy($qr, $plate, $x, $y, 0, 0, $diameter, $diameter);
    }

    /**
     * @param  \GdImage  $logo
     * @return \GdImage
     */
    private function resizeLogoWithAlpha($logo, int $logoSize)
    {
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

        return $logoResized;
    }

    /**
     * @param  \GdImage  $destination
     * @param  \GdImage  $source
     */
    private function copyWithAlpha($destination, $source, int $dstX, int $dstY): void
    {
        imagealphablending($destination, true);

        imagecopy(
            $destination,
            $source,
            $dstX,
            $dstY,
            0,
            0,
            imagesx($source),
            imagesy($source)
        );
    }
}
