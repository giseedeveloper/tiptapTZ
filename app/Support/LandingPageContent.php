<?php

namespace App\Support;

use App\Models\Setting;

class LandingPageContent
{
    private static ?array $storedValues = null;

    private static ?array $viewData = null;

    public static function value(string $key): string
    {
        $storedValues = self::storedValues();
        $storedKey = self::storageKey($key);
        $stored = $storedValues[$storedKey] ?? null;

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
     *     trust: array{
     *         headline: string,
     *         metrics: array<int, array{value: string, label: string, icon?: string}>,
     *         partners_label: string,
     *         partners: array<int, array{name: string, logo: string, width?: int}>,
     *         featured_in_label: string,
     *         featured_in: array<int, array{name: string, logo: string, width?: int}>,
     *         stats_headline: string,
     *         stats: array<int, array{value: string, label: string}>,
     *     },
     *     faq: array{
     *         chat_label: string,
     *         chat_cta: string,
     *         items: array<int, array{question: string, answer: string}>,
     *     },
     *     testimonials: array{
     *         title: string,
     *         items: array<int, array{
     *             quote: string,
     *             highlight: string|null,
     *             name: string,
     *             role: string,
     *             restaurant: string,
     *             city: string,
     *             photo: string|null,
     *         }>,
     *     },
     *     pricing: array<string, string>,
     * }
     */
    public static function viewData(): array
    {
        if (self::$viewData !== null) {
            return self::$viewData;
        }

        return self::$viewData = [
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
                'rafiki_intro' => self::value('hero_rafiki_intro'),
                'rafiki_meaning' => self::value('hero_rafiki_meaning'),
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
            'trust' => self::trust(),
            'faq' => self::faq(),
            'testimonials' => self::testimonials(),
            'pricing' => self::pricing(),
            'features' => self::features(),
            'demo' => self::demo(),
            'partners' => self::partners(),
            'nurture' => self::nurture(),
            'seo' => self::seo(),
        ];
    }

    public static function logoUrl(): string
    {
        if (function_exists('public_asset')) {
            return public_asset('images/logo.png');
        }

        return asset('images/logo.png');
    }

    /**
     * @return array{
     *     title: string,
     *     description: string,
     *     keywords: string,
     *     html_lang: string,
     *     locale: string,
     *     geo_region: string,
     *     geo_place_name: string,
     *     canonical_url: string,
     *     og_type: string,
     *     og_image: string,
     *     twitter_card: string,
     *     structured_data: array<string, mixed>,
     * }
     */
    public static function seo(): array
    {
        /** @var array<string, mixed> $seoConfig */
        $seoConfig = config('tiptap.landing.seo', []);

        $title = self::value('seo_title') ?: (string) config('tiptap.landing.defaults.seo_title', 'TipTap');
        $description = self::value('seo_description') ?: (string) config('tiptap.landing.defaults.seo_description', '');
        $keywords = self::value('seo_keywords') ?: (string) config('tiptap.landing.defaults.seo_keywords', '');
        $canonical = url('/');

        $ogImagePath = (string) ($seoConfig['og_image'] ?? 'images/logo.png');
        $ogImage = str_starts_with($ogImagePath, 'http')
            ? $ogImagePath
            : (function_exists('public_asset') ? public_asset($ogImagePath) : asset($ogImagePath));

        $context = [
            'market' => (string) config('tiptap.market', 'tz'),
            'title' => $title,
            'description' => $description,
            'canonical_url' => $canonical,
            'logo_url' => self::logoUrl(),
            'locale' => (string) ($seoConfig['locale'] ?? 'en'),
            'geo_region' => (string) ($seoConfig['geo_region'] ?? ''),
            'geo_place_name' => (string) ($seoConfig['geo_place_name'] ?? ''),
            'whatsapp_url' => self::whatsappUrl(),
            'social' => self::social(),
            'offices' => self::offices(),
            'faq' => self::faq(),
            'currency_code' => (string) config('tiptap.currency_code', 'TZS'),
        ];

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'html_lang' => (string) ($seoConfig['html_lang'] ?? 'en'),
            'locale' => (string) ($seoConfig['locale'] ?? 'en'),
            'geo_region' => (string) ($seoConfig['geo_region'] ?? ''),
            'geo_place_name' => (string) ($seoConfig['geo_place_name'] ?? ''),
            'canonical_url' => $canonical,
            'og_type' => (string) ($seoConfig['og_type'] ?? 'website'),
            'og_image' => $ogImage,
            'twitter_card' => (string) ($seoConfig['twitter_card'] ?? 'summary_large_image'),
            'structured_data' => LandingSeo::structuredData($context),
        ];
    }

    /**
     * @return array{
     *     book_demo_label: string,
     *     book_demo_url: string,
     *     book_demo_opens_new_tab: bool,
     *     chat_with_us_label: string,
     *     chat_with_us_url: string,
     *     cta_secondary_hint: string,
     *     lead_magnet_eyebrow: string,
     *     lead_magnet_title: string,
     *     lead_magnet_subtitle: string,
     *     lead_magnet_button: string,
     *     lead_magnet_success: string,
     *     lead_magnet_privacy: string,
     * }
     */
    public static function nurture(): array
    {
        /** @var array<string, mixed> $nurtureConfig */
        $nurtureConfig = config('tiptap.landing.nurture', []);

        $bookDemoUrl = self::bookDemoUrl();

        return [
            'book_demo_label' => self::value('nurture_book_demo_label') ?: (string) ($nurtureConfig['book_demo_label'] ?? 'Book a 20-minute demo'),
            'book_demo_url' => $bookDemoUrl,
            'book_demo_opens_new_tab' => self::bookDemoOpensNewTab($bookDemoUrl),
            'chat_with_us_label' => self::value('nurture_chat_with_us_label') ?: (string) ($nurtureConfig['chat_with_us_label'] ?? 'Chat with us'),
            'chat_with_us_url' => self::whatsappUrl(),
            'cta_secondary_hint' => (string) ($nurtureConfig['cta_secondary_hint'] ?? ''),
            'lead_magnet_eyebrow' => (string) ($nurtureConfig['lead_magnet_eyebrow'] ?? 'Free guide'),
            'lead_magnet_title' => self::value('nurture_lead_magnet_title') ?: (string) ($nurtureConfig['lead_magnet_title'] ?? ''),
            'lead_magnet_subtitle' => self::value('nurture_lead_magnet_subtitle') ?: (string) ($nurtureConfig['lead_magnet_subtitle'] ?? ''),
            'lead_magnet_button' => self::value('nurture_lead_magnet_button') ?: (string) ($nurtureConfig['lead_magnet_button'] ?? 'Send me the guide'),
            'lead_magnet_success' => self::value('nurture_lead_magnet_success') ?: (string) ($nurtureConfig['lead_magnet_success'] ?? 'Thanks! We will email your guide shortly.'),
            'lead_magnet_privacy' => (string) ($nurtureConfig['lead_magnet_privacy'] ?? ''),
        ];
    }

    public static function bookDemoUrl(): string
    {
        $calendly = self::value('nurture_book_demo_calendly_url');
        if (filled($calendly)) {
            return $calendly;
        }

        $message = self::value('nurture_book_demo_whatsapp_message');
        if (! filled($message)) {
            $message = (string) config('tiptap.landing.nurture.book_demo_whatsapp_message', 'Hi, I would like to book a 20-minute TipTap demo.');
        }

        $base = self::whatsappUrl();
        $separator = str_contains($base, '?') ? '&' : '?';

        return $base.$separator.'text='.rawurlencode($message);
    }

    protected static function bookDemoOpensNewTab(string $url): bool
    {
        return str_contains($url, 'calendly.com')
            || str_contains($url, 'cal.com')
            || str_contains($url, 'hubspot.com');
    }

    /**
     * @return array{
     *     eyebrow: string,
     *     title: string,
     *     subtitle: string,
     *     footnote: string,
     *     groups: array<int, array{
     *         label: string,
     *         items: array<int, array{name: string, logo: string, width?: int}>,
     *     }>,
     * }
     */
    public static function partners(): array
    {
        /** @var array<string, mixed> $partners */
        $partners = config('tiptap.landing.partners', []);

        return [
            'eyebrow' => (string) ($partners['eyebrow'] ?? 'Payment integrations'),
            'title' => (string) ($partners['title'] ?? 'Powered by trusted payment partners'),
            'subtitle' => (string) ($partners['subtitle'] ?? ''),
            'footnote' => (string) ($partners['footnote'] ?? ''),
            'groups' => is_array($partners['groups'] ?? null) ? $partners['groups'] : [],
        ];
    }

    /**
     * @return array{
     *     title: string,
     *     subtitle: string,
     *     walkthrough_label: string,
     *     video_url: string,
     *     video_poster: string,
     *     video_embed: array{provider: string, embed_url: string, poster: string|null}|null,
     *     try_rafiki_label: string,
     *     try_rafiki_message: string,
     *     try_rafiki_hint: string,
     *     try_rafiki_url: string,
     *     steps: array<int, array{icon: string, title: string, caption: string}>,
     * }
     */
    public static function demo(): array
    {
        /** @var array<string, mixed> $demoConfig */
        $demoConfig = config('tiptap.landing.demo', []);

        $videoUrl = self::value('demo_video_url');
        $poster = (string) ($demoConfig['video_poster'] ?? 'images/demo/rafiki-demo-poster.svg');

        return [
            'title' => self::value('demo_title') ?: (string) ($demoConfig['title'] ?? 'Watch Rafiki in action'),
            'subtitle' => self::value('demo_subtitle') ?: (string) ($demoConfig['subtitle'] ?? ''),
            'walkthrough_label' => (string) ($demoConfig['walkthrough_label'] ?? 'Interactive preview'),
            'video_url' => $videoUrl,
            'video_poster' => $poster,
            'video_embed' => LandingDemoVideo::embed($videoUrl),
            'try_rafiki_label' => self::value('demo_try_rafiki_label') ?: (string) ($demoConfig['try_rafiki_label'] ?? 'Try Rafiki now'),
            'try_rafiki_message' => self::value('demo_try_rafiki_message') ?: (string) ($demoConfig['try_rafiki_message'] ?? 'Hi'),
            'try_rafiki_hint' => (string) ($demoConfig['try_rafiki_hint'] ?? ''),
            'try_rafiki_url' => self::demoWhatsAppUrl(),
            'steps' => is_array($demoConfig['steps'] ?? null) ? $demoConfig['steps'] : [],
        ];
    }

    public static function demoWhatsAppUrl(): string
    {
        $message = self::value('demo_try_rafiki_message');
        if (! filled($message)) {
            $message = (string) config('tiptap.landing.demo.try_rafiki_message', 'Hi');
        }

        $base = self::whatsappUrl();
        $separator = str_contains($base, '?') ? '&' : '?';

        return $base.$separator.'text='.rawurlencode($message);
    }

    /**
     * @return array{
     *     headline: string,
     *     metrics: array<int, array{value: string, label: string, icon?: string}>,
     *     partners_label: string,
     *     partners: array<int, array{name: string, logo: string, width?: int}>,
     *     featured_in_label: string,
     *     featured_in: array<int, array{name: string, logo: string, width?: int}>,
     *     stats_headline: string,
     *     stats: array<int, array{value: string, label: string}>,
     * }
     */
    public static function trust(): array
    {
        /** @var array<string, mixed> $trust */
        $trust = config('tiptap.landing.trust', []);

        return [
            'headline' => (string) ($trust['headline'] ?? ''),
            'metrics' => is_array($trust['metrics'] ?? null) ? $trust['metrics'] : [],
            'partners_label' => (string) ($trust['partners_label'] ?? 'Secured payments via'),
            'partners' => is_array($trust['partners'] ?? null) ? $trust['partners'] : [],
            'featured_in_label' => (string) ($trust['featured_in_label'] ?? 'As featured in'),
            'featured_in' => is_array($trust['featured_in'] ?? null) ? $trust['featured_in'] : [],
            'stats_headline' => (string) ($trust['stats_headline'] ?? ''),
            'stats' => is_array($trust['stats'] ?? null) ? $trust['stats'] : [],
        ];
    }

    /**
     * @return array{
     *     chat_label: string,
     *     chat_cta: string,
     *     items: array<int, array{question: string, answer: string}>,
     * }
     */
    public static function faq(): array
    {
        /** @var array<string, mixed> $faq */
        $faq = config('tiptap.landing.faq', []);

        return [
            'chat_label' => (string) ($faq['chat_label'] ?? 'Still have questions?'),
            'chat_cta' => (string) ($faq['chat_cta'] ?? 'Chat with us on WhatsApp'),
            'items' => is_array($faq['items'] ?? null) ? $faq['items'] : [],
        ];
    }

    /**
     * @return array{
     *     title: string,
     *     items: array<int, array{
     *         quote: string,
     *         highlight: string|null,
     *         name: string,
     *         role: string,
     *         restaurant: string,
     *         city: string,
     *         photo: string|null,
     *     }>,
     * }
     */
    public static function testimonials(): array
    {
        /** @var array<string, mixed> $testimonials */
        $testimonials = config('tiptap.landing.testimonials', []);

        return [
            'title' => (string) ($testimonials['title'] ?? 'What managers are saying'),
            'items' => is_array($testimonials['items'] ?? null) ? $testimonials['items'] : [],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function pricing(): array
    {
        /** @var array<string, string> $pricing */
        $pricing = config('tiptap.landing.pricing', []);

        return $pricing;
    }

    /**
     * @return array{subtitle: string, rafiki_card_body: string}
     */
    public static function features(): array
    {
        /** @var array<string, string> $features */
        $features = config('tiptap.landing.features', []);

        return [
            'subtitle' => (string) ($features['subtitle'] ?? ''),
            'rafiki_card_body' => (string) ($features['rafiki_card_body'] ?? ''),
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
        $botNumber = Setting::get('whatsapp_bot_number');
        if (filled($botNumber)) {
            $clean = preg_replace('/[^0-9]/', '', (string) $botNumber);

            if (strlen($clean) >= 9) {
                return 'https://wa.me/'.$clean;
            }
        }

        $stored = self::value('whatsapp_url');
        if (filled($stored)) {
            return $stored;
        }

        $defaultNumber = (string) config('tiptap.default_whatsapp_bot_number', '255791070771');
        $cleanDefault = preg_replace('/[^0-9]/', '', $defaultNumber);

        return 'https://wa.me/'.$cleanDefault;
    }

    public static function storageKey(string $key): string
    {
        return 'landing_'.$key;
    }

    /**
     * @return array<string, string|null>
     */
    private static function storedValues(): array
    {
        if (self::$storedValues !== null) {
            return self::$storedValues;
        }

        $keys = array_map(
            fn (string $key): string => self::storageKey($key),
            array_keys(config('tiptap.landing.fields', [])),
        );

        if ($keys === []) {
            return self::$storedValues = [];
        }

        return self::$storedValues = Setting::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->all();
    }
}
