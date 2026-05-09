<?php

namespace App\Services;

use App\Models\Order;
use InvalidArgumentException;

class BillImageService
{
    private const WIDTH = 900;

    private const HEADER_BOTTOM = 192;

    private const PAD_X = 48;

    /** @return array{0: \GdImage, 1: int} image and total pixel height */
    private function createCanvas(int $height): array
    {
        $im = imagecreatetruecolor(self::WIDTH, $height);
        if ($im === false) {
            throw new InvalidArgumentException('Could not create image canvas.');
        }
        imagealphablending($im, true);
        imagesavealpha($im, true);

        return [$im, $height];
    }

    public function renderPng(Order $order): string
    {
        $order->loadMissing(['restaurant', 'items']);

        $itemCount = max(1, $order->items->count());
        $itemsBlockH = 56 + ($itemCount * 44) + 24;
        $height = self::HEADER_BOTTOM + 48 + 200 + $itemsBlockH + 200 + 120 + 80;

        [$im, $h] = $this->createCanvas($height);

        $fonts = $this->fontPaths();
        $bodyBg = $this->allocate($im, 243, 244, 246);
        imagefilledrectangle($im, 0, 0, self::WIDTH, $h, $bodyBg);

        $this->drawHeaderGradient($im);
        $this->drawTornNotches($im, $bodyBg);
        $this->drawHeaderContent($im, $order, $fonts);
        $y = self::HEADER_BOTTOM + 36;
        $this->drawCardShadow($im, $y, $h - $y - 40);
        $y += 8;
        $y = $this->drawCustomerSection($im, $order, $fonts, $y);
        $y = $this->drawItemsTable($im, $order, $fonts, $y);
        $y = $this->drawGrandTotalSection($im, $order, $fonts, $y);
        $y = $this->drawStatusBar($im, $order, $fonts, $y);
        $this->drawFooter($im, $fonts, $y + 16, $h);

        ob_start();
        imagepng($im);
        $binary = (string) ob_get_clean();
        imagedestroy($im);

        return $binary;
    }

    /**
     * @return array{regular: string, bold: string}
     */
    private function fontPaths(): array
    {
        $base = base_path('vendor/dompdf/dompdf/lib/fonts');
        $regular = $base.'/DejaVuSans.ttf';
        $bold = $base.'/DejaVuSans-Bold.ttf';
        if (! is_readable($regular) || ! is_readable($bold)) {
            throw new InvalidArgumentException(
                'DejaVu fonts not found. Run composer install so dompdf fonts exist under vendor/dompdf/dompdf/lib/fonts.'
            );
        }

        return ['regular' => $regular, 'bold' => $bold];
    }

    private function allocate(\GdImage $im, int $r, int $g, int $b): int
    {
        return imagecolorallocate($im, $r, $g, $b);
    }

    private function drawHeaderGradient(\GdImage $im): void
    {
        $top = $this->allocate($im, 124, 58, 237);
        $bottom = $this->allocate($im, 91, 33, 182);
        for ($y = 0; $y < self::HEADER_BOTTOM; $y++) {
            $t = $y / max(1, self::HEADER_BOTTOM - 1);
            $r = (int) round(124 + (91 - 124) * $t);
            $g = (int) round(58 + (33 - 58) * $t);
            $b = (int) round(237 + (182 - 237) * $t);
            $c = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, self::WIDTH, $y, $c);
        }
    }

    private function drawTornNotches(\GdImage $im, int $bodyBg): void
    {
        $hb = self::HEADER_BOTTOM;
        $depth = 12;
        for ($x = -24; $x < self::WIDTH + 24; $x += 36) {
            $points = [$x, $hb, $x + 18, $hb + $depth, $x + 36, $hb];
            imagefilledpolygon($im, $points, 3, $bodyBg);
        }
    }

    /**
     * @param  array{regular: string, bold: string}  $fonts
     */
    private function drawHeaderContent(\GdImage $im, Order $order, array $fonts): void
    {
        $white = $this->allocate($im, 255, 255, 255);
        $soft = $this->allocate($im, 221, 214, 254);

        $this->drawClocheIcon($im, self::PAD_X, 36, $white);

        $this->ttf($im, $fonts['bold'], 26, self::PAD_X + 56, 72, $white, 'TIPTAP BILL');
        $restaurant = $this->cleanText($order->restaurant?->name ?? 'Restaurant');
        $this->ttf($im, $fonts['regular'], 15, self::PAD_X + 56, 102, $soft, strtoupper($restaurant));

        $now = now();
        $dateLine = $now->format('Y-m-d');
        $timeLine = $now->format('h:i A');
        $dateBlockW = 200;
        $dx = self::WIDTH - self::PAD_X - $dateBlockW;
        $this->ttf($im, $fonts['regular'], 12, $dx, 48, $soft, $dateLine);
        $this->ttf($im, $fonts['regular'], 12, $dx, 68, $soft, $timeLine);

        $badgeY = 128;
        $this->drawPill($im, self::PAD_X, $badgeY, 'Order #'.$order->id, $fonts, $white, $soft);
        $tableLabel = 'Table '.$this->cleanText((string) ($order->table_number ?? '—'));
        $this->drawPill($im, self::PAD_X + 200, $badgeY, $tableLabel, $fonts, $white, $soft);
    }

    private function drawClocheIcon(\GdImage $im, int $x, int $y, int $color): void
    {
        imagearc($im, $x + 22, $y + 18, 36, 32, 180, 360, $color);
        imagerectangle($im, $x + 6, $y + 26, $x + 38, $y + 30, $color);
    }

    /**
     * @param  array{regular: string, bold: string}  $fonts
     */
    private function drawPill(\GdImage $im, int $x, int $y, string $text, array $fonts, int $fg, int $borderSoft): void
    {
        $pillBg = $this->allocateAlpha($im, 67, 56, 202, 40);
        $w = (int) max(140, strlen($text) * 9 + 36);
        $h = 34;
        $this->fillRoundRect($im, $x, $y, $w, $h, 10, $pillBg);
        imagerectangle($im, $x, $y, $x + $w, $y + $h, $borderSoft);
        $this->ttf($im, $fonts['bold'], 11, $x + 14, $y + 24, $fg, $text);
    }

    private function fillRoundRect(\GdImage $im, int $x, int $y, int $w, int $h, int $r, int $color): void
    {
        imagefilledrectangle($im, $x + $r, $y, $x + $w - $r, $y + $h, $color);
        imagefilledrectangle($im, $x, $y + $r, $x + $w, $y + $h - $r, $color);
        imagefilledellipse($im, $x + $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x + $w - $r, $y + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x + $r, $y + $h - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($im, $x + $w - $r, $y + $h - $r, $r * 2, $r * 2, $color);
    }

    private function allocateAlpha(\GdImage $im, int $r, int $g, int $b, int $a): int
    {
        return imagecolorallocatealpha($im, $r, $g, $b, $a);
    }

    private function drawCardShadow(\GdImage $im, int $top, int $tall): void
    {
        $shadow = $this->allocate($im, 209, 213, 219);
        $card = $this->allocate($im, 255, 255, 255);
        $x0 = self::PAD_X - 8;
        $x1 = self::WIDTH - self::PAD_X + 8;
        $y0 = $top;
        $y1 = $top + $tall;
        $this->fillRoundRect($im, $x0 + 4, $y0 + 4, $x1 - $x0, $y1 - $y0, 18, $shadow);
        $this->fillRoundRect($im, $x0, $y0, $x1 - $x0, $y1 - $y0, 18, $card);
    }

    /**
     * @param  array{regular: string, bold: string}  $fonts
     */
    private function drawCustomerSection(\GdImage $im, Order $order, array $fonts, int $y): int
    {
        $muted = $this->allocate($im, 107, 114, 128);
        $dark = $this->allocate($im, 17, 24, 39);
        $accent = $this->allocate($im, 124, 58, 237);
        $line = $this->allocate($im, 229, 231, 235);

        $x = self::PAD_X + 8;
        $yy = $y + 28;
        $this->dashedLine($im, $x, $yy, self::WIDTH - $x, $yy, $line);
        $yy += 28;

        imagefilledellipse($im, $x + 12, $yy - 4, 28, 28, $accent);
        $this->ttf($im, $fonts['regular'], 9, $x + 52, $yy - 8, $muted, 'CUSTOMER');
        $name = $this->cleanText($order->customer_name ?: 'Guest');
        $this->ttf($im, $fonts['bold'], 15, $x + 52, $yy + 14, $dark, $name);
        $yy += 48;

        $this->dashedLine($im, $x, $yy, self::WIDTH - $x, $yy, $line);
        $yy += 28;

        imagefilledellipse($im, $x + 12, $yy - 4, 28, 28, $accent);
        $this->ttf($im, $fonts['regular'], 9, $x + 52, $yy - 8, $muted, 'PHONE');
        $phone = $this->cleanText((string) ($order->customer_phone ?: '—'));
        $this->ttf($im, $fonts['bold'], 14, $x + 52, $yy + 14, $dark, $phone);
        $yy += 40;

        $this->dashedLine($im, $x, $yy, self::WIDTH - $x, $yy, $line);

        return $yy + 20;
    }

    private function dashedLine(\GdImage $im, int $x1, int $y, int $x2, int $y2, int $color): void
    {
        for ($x = $x1; $x < $x2; $x += 10) {
            imageline($im, $x, $y, min($x + 5, $x2), $y2, $color);
        }
    }

    /**
     * @param  array{regular: string, bold: string}  $fonts
     */
    private function drawItemsTable(\GdImage $im, Order $order, array $fonts, int $y): int
    {
        $x = self::PAD_X + 8;
        $w = self::WIDTH - 2 * self::PAD_X - 16;
        $headerBg = $this->allocate($im, 237, 233, 254);
        $headerFg = $this->allocate($im, 91, 33, 182);
        $dark = $this->allocate($im, 17, 24, 39);
        $muted = $this->allocate($im, 100, 116, 139);

        $rowH = 40;
        $headerH = 36;
        imagefilledrectangle($im, $x, $y, $x + $w, $y + $headerH, $headerBg);
        $this->ttf($im, $fonts['bold'], 10, $x + 12, $y + 25, $headerFg, 'ITEM');
        $this->ttf($im, $fonts['bold'], 10, $x + 420, $y + 25, $headerFg, 'QTY');
        $this->ttf($im, $fonts['bold'], 10, $x + 520, $y + 25, $headerFg, 'PRICE');
        $this->ttf($im, $fonts['bold'], 10, $x + 660, $y + 25, $headerFg, 'TOTAL');

        $yy = $y + $headerH + 8;
        if ($order->items->isEmpty()) {
            $this->ttf($im, $fonts['bold'], 12, $x + 12, $yy + 22, $dark, '—');
            $this->ttf($im, $fonts['regular'], 12, $x + 420, $yy + 22, $dark, '0');
            $this->ttf($im, $fonts['regular'], 12, $x + 520, $yy + 22, $muted, 'TZS 0');
            $this->ttf($im, $fonts['regular'], 12, $x + 660, $yy + 22, $muted, 'TZS 0');
            $yy += $rowH;
        } else {
            foreach ($order->items as $item) {
                $name = $this->cleanText((string) ($item->name ?: 'Item'));
                if (mb_strlen($name) > 42) {
                    $name = mb_substr($name, 0, 41).'…';
                }
                $this->ttf($im, $fonts['bold'], 12, $x + 12, $yy + 22, $dark, $name);
                $this->ttf($im, $fonts['regular'], 12, $x + 420, $yy + 22, $dark, (string) $item->quantity);
                $this->ttf($im, $fonts['regular'], 12, $x + 520, $yy + 22, $muted, 'TZS '.$this->money((float) $item->price));
                $this->ttf($im, $fonts['regular'], 12, $x + 660, $yy + 22, $muted, 'TZS '.$this->money((float) $item->total));
                $yy += $rowH;
            }
        }

        return $yy + 16;
    }

    /**
     * @param  array{regular: string, bold: string}  $fonts
     */
    private function drawGrandTotalSection(\GdImage $im, Order $order, array $fonts, int $y): int
    {
        $x = self::PAD_X + 8;
        $w = self::WIDTH - 2 * self::PAD_X - 16;
        $border = $this->allocate($im, 229, 231, 235);
        $muted = $this->allocate($im, 107, 114, 128);
        $purple = $this->allocate($im, 124, 58, 237);
        $boxH = 120;

        imagerectangle($im, $x, $y, $x + $w, $y + $boxH, $border);
        imagefilledrectangle($im, $x + 1, $y + 1, $x + $w - 1, $y + $boxH - 1, $this->allocate($im, 255, 255, 255));

        $this->ttf($im, $fonts['regular'], 11, $x + 20, $y + 32, $muted, 'GRAND TOTAL');
        $this->ttf($im, $fonts['bold'], 36, $x + 20, $y + 82, $purple, 'TZS '.$this->money((float) $order->total_amount));

        $this->drawWalletGraphic($im, $x + $w - 140, $y + 24);

        return $y + $boxH + 24;
    }

    private function drawWalletGraphic(\GdImage $im, int $x, int $y): void
    {
        $p1 = $this->allocate($im, 167, 139, 250);
        $p2 = $this->allocate($im, 124, 58, 237);
        $p3 = $this->allocate($im, 91, 33, 182);
        $this->fillRoundRect($im, $x, $y + 20, 88, 52, 8, $p3);
        $this->fillRoundRect($im, $x + 6, $y + 10, 76, 44, 8, $p2);
        $this->fillRoundRect($im, $x + 12, $y, 64, 36, 6, $p1);
        imagefilledrectangle($im, $x + 18, $y + 8, $x + 70, $y + 14, $this->allocate($im, 254, 249, 231));
    }

    /**
     * @param  array{regular: string, bold: string}  $fonts
     */
    private function drawStatusBar(\GdImage $im, Order $order, array $fonts, int $y): int
    {
        $x = self::PAD_X + 8;
        $w = self::WIDTH - 2 * self::PAD_X - 16;
        $h = 56;
        $greenBg = $this->allocate($im, 209, 250, 229);
        $greenFg = $this->allocate($im, 5, 122, 85);
        $greenMuted = $this->allocate($im, 16, 185, 129);

        $this->fillRoundRect($im, $x, $y, $w, $h, 12, $greenBg);

        $tickWhite = $this->allocate($im, 255, 255, 255);
        imagefilledellipse($im, $x + 28, $y + 28, 22, 22, $greenFg);
        imageline($im, $x + 22, $y + 28, $x + 26, $y + 33, $tickWhite);
        imageline($im, $x + 26, $y + 33, $x + 36, $y + 21, $tickWhite);

        $this->ttf($im, $fonts['regular'], 9, $x + 48, $y + 22, $greenMuted, 'STATUS');
        $this->ttf($im, $fonts['bold'], 13, $x + 48, $y + 42, $greenFg, strtoupper($this->cleanText((string) $order->status)));

        $mid = $x + (int) ($w / 2);
        imageline($im, $mid, $y + 12, $mid, $y + $h - 12, $this->allocate($im, 167, 243, 208));

        $gen = now()->format('Y-m-d H:i:s');
        $this->ttf($im, $fonts['regular'], 9, $mid + 16, $y + 22, $greenMuted, 'GENERATED');
        $this->ttf($im, $fonts['bold'], 12, $mid + 16, $y + 42, $greenFg, $gen);

        return $y + $h;
    }

    /**
     * @param  array{regular: string, bold: string}  $fonts
     */
    private function drawFooter(\GdImage $im, array $fonts, int $y, int $canvasH): void
    {
        $purple = $this->allocate($im, 124, 58, 237);
        $muted = $this->allocate($im, 100, 116, 139);
        $cx = (int) (self::WIDTH / 2);
        imageline($im, self::PAD_X, $y, $cx - 24, $y, $purple);
        imageline($im, $cx + 24, $y, self::WIDTH - self::PAD_X, $y, $purple);
        imagefilledellipse($im, $cx, $y, 18, 18, $purple);
        imagearc($im, $cx, $y - 2, 8, 8, 200, 340, $this->allocate($im, 255, 255, 255));

        $this->ttfCenter($im, $fonts['bold'], 14, $y + 36, $purple, 'Thank you for choosing TIPTAP!');
        $this->ttfCenter($im, $fonts['regular'], 11, $y + 62, $muted, 'We appreciate your visit and hope to serve you again.');

        $tearY = $canvasH - 14;
        $bodyBg = $this->allocate($im, 243, 244, 246);
        for ($tx = -24; $tx < self::WIDTH + 24; $tx += 36) {
            $points = [$tx, $tearY, $tx + 18, $tearY - 12, $tx + 36, $tearY];
            imagefilledpolygon($im, $points, 3, $bodyBg);
        }
    }

    private function ttf(\GdImage $im, string $font, float $size, int $x, int $y, int $color, string $text): void
    {
        imagettftext($im, $size, 0, $x, $y, $color, $font, $text);
    }

    private function ttfCenter(\GdImage $im, string $font, float $size, int $y, int $color, string $text): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        if ($box === false) {
            return;
        }
        $tw = (int) abs($box[2] - $box[0]);
        $x = (int) ((self::WIDTH - $tw) / 2);
        $this->ttf($im, $font, $size, $x, $y, $color, $text);
    }

    private function cleanText(string $value): string
    {
        $t = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value));

        return $t !== '' ? $t : '—';
    }

    private function money(float $amount): string
    {
        return number_format($amount, 0, '.', ',');
    }
}
