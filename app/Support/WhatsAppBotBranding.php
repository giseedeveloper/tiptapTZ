<?php

namespace App\Support;

use App\Models\Bot;
use Illuminate\Support\Facades\Storage;

class WhatsAppBotBranding
{
    /**
     * @return array{title: string, body: ?string, image_url: string}
     */
    public static function resolve(): array
    {
        $bot = Bot::query()->orderBy('id')->first();
        $settings = is_array($bot?->settings) ? $bot->settings : [];

        $baseUrl = rtrim((string) config('app.url'), '/');
        $configuredImage = config('whatsapp.welcome_image_url');
        $defaultImage = filled($configuredImage)
            ? (string) $configuredImage
            : $baseUrl.'/images/icon-512.png';

        $imageUrl = self::resolveImageUrl($settings, $baseUrl, $defaultImage);

        return [
            'title' => (string) ($settings['welcome_title'] ?? config('whatsapp.welcome_title', 'TipTap')),
            'body' => filled($settings['welcome_body'] ?? null)
                ? (string) $settings['welcome_body']
                : null,
            'image_url' => $imageUrl,
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private static function resolveImageUrl(array $settings, string $baseUrl, string $defaultImage): string
    {
        if (filled($settings['welcome_image_path'] ?? null)) {
            $path = (string) $settings['welcome_image_path'];
            $url = Storage::disk('public')->url($path);

            if (str_starts_with($url, 'http')) {
                return $url;
            }

            return $baseUrl.'/'.ltrim($url, '/');
        }

        if (filled($settings['welcome_image_url'] ?? null)) {
            return self::absoluteUrl((string) $settings['welcome_image_url'], $baseUrl);
        }

        return self::absoluteUrl($defaultImage, $baseUrl);
    }

    private static function absoluteUrl(string $url, string $baseUrl): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return $baseUrl.'/'.ltrim($url, '/');
    }
}
