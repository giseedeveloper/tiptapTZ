<?php

namespace App\Support;

use App\Models\Setting;

class LandingPageContent
{
    public static function value(string $key): string
    {
        $stored = Setting::get(self::storageKey($key));

        if (filled($stored)) {
            return (string) $stored;
        }

        $defaults = config('tiptap.landing.defaults', []);

        if (array_key_exists($key, $defaults)) {
            return (string) $defaults[$key];
        }

        return '';
    }

    /**
     * @return array<string, string>
     */
    public static function forAdmin(): array
    {
        $values = [];

        foreach (array_keys(config('tiptap.landing.fields', [])) as $key) {
            $values[$key] = self::value($key);
        }

        return $values;
    }

    /**
     * @return array{
     *     market: string,
     *     offices: array<string, array{name: string, flag: string, city: string, lines: array<int, string>}>,
     *     social: array<string, string|null>,
     *     whatsapp_url: string,
     *     hero: array<string, string>,
     *     contact: array<string, string>,
     *     cta: array<string, string>,
     *     footer_tagline: string,
     * }
     */
    public static function viewData(): array
    {
        return [
            'market' => (string) config('tiptap.market', 'tz'),
            'offices' => self::offices(),
            'social' => self::social(),
            'whatsapp_url' => self::whatsappUrl(),
            'hero' => [
                'live_badge' => self::value('hero_live_badge'),
                'badge_text' => self::value('hero_badge_text'),
                'title_line1' => self::value('hero_title_line1'),
                'title_line2' => self::value('hero_title_line2'),
                'description' => self::value('hero_description'),
                'cta_primary' => self::value('hero_cta_primary'),
                'cta_secondary' => self::value('hero_cta_secondary'),
            ],
            'contact' => [
                'label' => self::value('contact_label'),
                'title' => self::value('contact_title'),
                'description' => self::value('contact_description'),
                'social_title' => self::value('contact_social_title'),
                'social_description' => self::value('contact_social_description'),
            ],
            'cta' => [
                'title' => self::value('cta_title'),
                'description' => self::value('cta_description'),
                'button' => self::value('cta_button'),
            ],
            'footer_tagline' => self::value('footer_tagline'),
        ];
    }

    /**
     * @return array<string, array{name: string, flag: string, city: string, lines: array<int, string>}>
     */
    public static function offices(): array
    {
        return [
            'tz' => [
                'name' => self::value('office_tz_name') ?: 'Tanzania',
                'flag' => 'tz',
                'city' => self::value('office_tz_city'),
                'lines' => array_values(array_filter([
                    self::value('office_tz_line1'),
                    self::value('office_tz_line2'),
                ])),
            ],
            'za' => [
                'name' => self::value('office_za_name') ?: 'South Africa',
                'flag' => 'za',
                'city' => self::value('office_za_city'),
                'lines' => array_values(array_filter([
                    self::value('office_za_line1'),
                    self::value('office_za_line2'),
                ])),
            ],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function social(): array
    {
        $platforms = ['facebook', 'instagram', 'x', 'linkedin', 'tiktok', 'youtube'];
        $social = [];

        foreach ($platforms as $platform) {
            $url = self::value('social_'.$platform);
            $social[$platform] = filled($url) ? $url : null;
        }

        return $social;
    }

    public static function whatsappUrl(): string
    {
        return self::value('whatsapp_url');
    }

    public static function storageKey(string $key): string
    {
        return 'landing_'.$key;
    }
}
