<?php

return [
    'market' => env('TIPTAP_MARKET', 'tz'),

    'currency_symbol' => env('TIPTAP_CURRENCY_SYMBOL', 'Tsh'),

    'currency_code' => env('TIPTAP_CURRENCY_CODE', 'TZS'),

    'country_code' => env('TIPTAP_COUNTRY_CODE', '255'),

    'payment_gateway' => env('TIPTAP_PAYMENT_GATEWAY', 'Selcom'),

    'default_whatsapp_bot_number' => env('TIPTAP_WHATSAPP_BOT_NUMBER', '255791070771'),

    'admin_live_poll_seconds' => (int) env('ADMIN_LIVE_POLL_SECONDS', 30),

    /*
    |--------------------------------------------------------------------------
    | Admin settings (whitelist — keys editable via admin/settings)
    |--------------------------------------------------------------------------
    */
    'admin_setting_groups' => [
        'system_name' => 'general',
        'support_email' => 'general',
        'commission_rate' => 'financial',
        'min_withdrawal' => 'financial',
        'demo_push' => 'payments',
        'whatsapp_bot_number' => 'api',
        'webhook_secret' => 'api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Public landing page — offices & social (TipTap Africa)
    |--------------------------------------------------------------------------
    */
    'company' => [
        'offices' => [
            'tz' => [
                'name' => 'Tanzania',
                'flag' => 'tz',
                'city' => 'Dar es Salaam, Kinondoni',
                'lines' => [
                    'Tanzanite Park',
                    '13th Floor',
                ],
            ],
            'za' => [
                'name' => 'South Africa',
                'flag' => 'za',
                'city' => 'Lonehill, Gauteng',
                'lines' => [
                    '16 Capricorn Road',
                    'Lonehill, 2062',
                ],
            ],
        ],
        'whatsapp_url' => env('TIPTAP_WHATSAPP_URL', 'https://wa.me/255791070771'),
        'social' => [
            'facebook' => env('TIPTAP_SOCIAL_FACEBOOK'),
            'instagram' => env('TIPTAP_SOCIAL_INSTAGRAM'),
            'x' => env('TIPTAP_SOCIAL_X'),
            'linkedin' => env('TIPTAP_SOCIAL_LINKEDIN'),
            'tiktok' => env('TIPTAP_SOCIAL_TIKTOK'),
            'youtube' => env('TIPTAP_SOCIAL_YOUTUBE'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Landing page manager (admin-editable; falls back to defaults below)
    |--------------------------------------------------------------------------
    */
    'landing' => [
        'sections' => [
            'hero' => 'Hero section',
            'contact' => 'Contact & offices',
            'social' => 'Social media & WhatsApp',
            'cta' => 'Bottom call to action',
            'footer' => 'Footer',
        ],
        'fields' => [
            'hero_live_badge' => ['nullable', 'string', 'max:50'],
            'hero_badge_text' => ['nullable', 'string', 'max:200'],
            'hero_title_line1' => ['nullable', 'string', 'max:120'],
            'hero_title_line2' => ['nullable', 'string', 'max:120'],
            'hero_description' => ['nullable', 'string', 'max:1000'],
            'hero_cta_primary' => ['nullable', 'string', 'max:80'],
            'hero_cta_secondary' => ['nullable', 'string', 'max:80'],
            'contact_label' => ['nullable', 'string', 'max:80'],
            'contact_title' => ['nullable', 'string', 'max:160'],
            'contact_description' => ['nullable', 'string', 'max:500'],
            'contact_social_title' => ['nullable', 'string', 'max:120'],
            'contact_social_description' => ['nullable', 'string', 'max:500'],
            'office_tz_name' => ['nullable', 'string', 'max:80'],
            'office_tz_city' => ['nullable', 'string', 'max:160'],
            'office_tz_line1' => ['nullable', 'string', 'max:160'],
            'office_tz_line2' => ['nullable', 'string', 'max:160'],
            'office_za_name' => ['nullable', 'string', 'max:80'],
            'office_za_city' => ['nullable', 'string', 'max:160'],
            'office_za_line1' => ['nullable', 'string', 'max:160'],
            'office_za_line2' => ['nullable', 'string', 'max:160'],
            'social_facebook' => ['nullable', 'url', 'max:500'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_x' => ['nullable', 'url', 'max:500'],
            'social_linkedin' => ['nullable', 'url', 'max:500'],
            'social_tiktok' => ['nullable', 'url', 'max:500'],
            'social_youtube' => ['nullable', 'url', 'max:500'],
            'whatsapp_url' => ['nullable', 'url', 'max:500'],
            'cta_title' => ['nullable', 'string', 'max:160'],
            'cta_description' => ['nullable', 'string', 'max:500'],
            'cta_button' => ['nullable', 'string', 'max:80'],
            'footer_tagline' => ['nullable', 'string', 'max:300'],
        ],
        'defaults' => [
            'hero_live_badge' => 'Live',
            'hero_badge_text' => 'TipTap Rafiki · WhatsApp · QR · M-Pesa',
            'hero_title_line1' => 'Review, pay and tip',
            'hero_title_line2' => 'in one platform',
            'hero_description' => 'Guests scan a QR code, chat with TipTap Rafiki, place orders, leave reviews, and pay with mobile payment or bank — no app, no queues, no hassle.',
            'hero_cta_primary' => 'Start free trial',
            'hero_cta_secondary' => 'See the conversation',
            'contact_label' => 'TipTap Africa',
            'contact_title' => 'Where we operate',
            'contact_description' => 'TipTap is managed from our offices in Tanzania and South Africa. Reach us at either location or connect on social media.',
            'contact_social_title' => 'Follow TipTap Africa',
            'contact_social_description' => 'Connect with us on social media for product updates, restaurant success stories, and support.',
            'office_tz_name' => 'Tanzania',
            'office_tz_city' => 'Dar es Salaam, Kinondoni',
            'office_tz_line1' => 'Tanzanite Park',
            'office_tz_line2' => '13th Floor',
            'office_za_name' => 'South Africa',
            'office_za_city' => 'Lonehill, Gauteng',
            'office_za_line1' => '16 Capricorn Road',
            'office_za_line2' => 'Lonehill, 2062',
            'social_facebook' => env('TIPTAP_SOCIAL_FACEBOOK', ''),
            'social_instagram' => env('TIPTAP_SOCIAL_INSTAGRAM', ''),
            'social_x' => env('TIPTAP_SOCIAL_X', ''),
            'social_linkedin' => env('TIPTAP_SOCIAL_LINKEDIN', ''),
            'social_tiktok' => env('TIPTAP_SOCIAL_TIKTOK', ''),
            'social_youtube' => env('TIPTAP_SOCIAL_YOUTUBE', ''),
            'whatsapp_url' => env('TIPTAP_WHATSAPP_URL', 'https://wa.me/255791070771'),
            'cta_title' => 'Upgrade your restaurant today',
            'cta_description' => 'Join venues already using TipTap Rafiki, QR ordering, and instant mobile payments.',
            'cta_button' => 'Create free account',
            'footer_tagline' => 'The operating system for modern dining. Built with care in Tanzania.',
        ],
    ],
];
