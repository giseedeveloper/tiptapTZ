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
            'demo' => 'Video demo & live Rafiki',
            'nurture' => 'Nurture CTAs & lead magnet',
            'contact' => 'Contact & offices',
            'social' => 'Social media & WhatsApp',
            'cta' => 'Bottom call to action',
            'seo' => 'SEO & schema markup',
            'footer' => 'Footer',
        ],
        'fields' => [
            'hero_live_badge' => ['nullable', 'string', 'max:50'],
            'hero_badge_text' => ['nullable', 'string', 'max:200'],
            'hero_title_line1' => ['nullable', 'string', 'max:120'],
            'hero_title_line2' => ['nullable', 'string', 'max:120'],
            'hero_description' => ['nullable', 'string', 'max:1000'],
            'hero_rafiki_intro' => ['nullable', 'string', 'max:300'],
            'hero_rafiki_meaning' => ['nullable', 'string', 'max:300'],
            'hero_cta_primary' => ['nullable', 'string', 'max:80'],
            'hero_cta_secondary' => ['nullable', 'string', 'max:80'],
            'demo_title' => ['nullable', 'string', 'max:160'],
            'demo_subtitle' => ['nullable', 'string', 'max:300'],
            'demo_video_url' => ['nullable', 'url', 'max:500'],
            'demo_try_rafiki_label' => ['nullable', 'string', 'max:80'],
            'demo_try_rafiki_message' => ['nullable', 'string', 'max:120'],
            'nurture_book_demo_label' => ['nullable', 'string', 'max:80'],
            'nurture_book_demo_calendly_url' => ['nullable', 'url', 'max:500'],
            'nurture_book_demo_whatsapp_message' => ['nullable', 'string', 'max:300'],
            'nurture_chat_with_us_label' => ['nullable', 'string', 'max:80'],
            'nurture_lead_magnet_title' => ['nullable', 'string', 'max:160'],
            'nurture_lead_magnet_subtitle' => ['nullable', 'string', 'max:500'],
            'nurture_lead_magnet_button' => ['nullable', 'string', 'max:80'],
            'nurture_lead_magnet_success' => ['nullable', 'string', 'max:300'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
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
        'trust' => [
            'headline' => 'Trusted by restaurants across Tanzania',
            'metrics' => [
                ['value' => '50+', 'label' => 'Live restaurants', 'icon' => 'store'],
                ['value' => '10k+', 'label' => 'Orders processed', 'icon' => 'receipt'],
            ],
            'partners_label' => 'Secured payments via',
            'partners' => [
                ['name' => 'Selcom', 'logo' => 'images/partners/selcom.svg', 'width' => 88],
                ['name' => 'M-Pesa', 'logo' => 'images/partners/mpesa.svg', 'width' => 88],
                ['name' => 'Tigo Pesa', 'logo' => 'images/partners/tigopesa.svg', 'width' => 96],
            ],
            'featured_in_label' => 'As featured in',
            'featured_in' => [],
            'stats_headline' => 'Trusted by leading restaurants across Tanzania for faster ordering and payments',
            'stats' => [
                ['value' => '24/7', 'label' => 'Bot always on'],
                ['value' => '4.9★', 'label' => 'Manager rating'],
                ['value' => 'Instant', 'label' => 'Payment settlement'],
                ['value' => 'Zero', 'label' => 'Apps for guests'],
            ],
        ],
        'partners' => [
            'eyebrow' => 'Payment integrations',
            'title' => 'Powered by trusted payment partners',
            'subtitle' => 'Restaurant payments run through Selcom — a regulated Tanzanian gateway — with M-Pesa, Tigo Pesa, Airtel Money, and bank settlement where configured.',
            'footnote' => 'Seeing these logos means your guests pay through recognised, regulated rails — not informal workarounds.',
            'groups' => [
                [
                    'label' => 'Payment gateway',
                    'items' => [
                        ['name' => 'Selcom', 'logo' => 'images/partners/selcom.svg', 'width' => 108],
                    ],
                ],
                [
                    'label' => 'Mobile money',
                    'items' => [
                        ['name' => 'M-Pesa', 'logo' => 'images/partners/mpesa.svg', 'width' => 96],
                        ['name' => 'Tigo Pesa', 'logo' => 'images/partners/tigopesa.svg', 'width' => 104],
                        ['name' => 'Airtel Money', 'logo' => 'images/partners/airtelmoney.svg', 'width' => 112],
                    ],
                ],
                [
                    'label' => 'Banking partner',
                    'items' => [
                        ['name' => 'Stanbic Bank', 'logo' => 'images/partners/stanbic.svg', 'width' => 120],
                    ],
                ],
            ],
        ],
        'faq' => [
            'chat_label' => 'Still have questions?',
            'chat_cta' => 'Chat with us on WhatsApp',
            'items' => [
                [
                    'question' => 'What is TipTap?',
                    'answer' => 'TipTap is a restaurant operating system built for Tanzania: QR table ordering, TipTap Rafiki on WhatsApp, mobile money and bank payments, a kitchen display, and live manager dashboards — all in one platform.',
                ],
                [
                    'question' => 'Do customers need to download an app?',
                    'answer' => 'No. Guests scan a QR code at the table and order through WhatsApp — the app they already use every day. No downloads, no new accounts, no friction.',
                ],
                [
                    'question' => 'Which payment methods are supported?',
                    'answer' => 'M-Pesa, Tigo Pesa, and Airtel Money through Selcom, plus bank options where configured. Bills, tips, and settlements are tracked in real time on your dashboard.',
                ],
                [
                    'question' => 'What happens if the internet goes down?',
                    'answer' => 'Orders already sent to the kitchen display stay visible on screen. If connectivity drops briefly, TipTap queues messages and syncs when the connection returns. Your team can also take manual orders and reconcile once back online.',
                ],
                [
                    'question' => 'How long does setup take?',
                    'answer' => 'Most restaurants go live in under a day. Upload your menu, assign table QR codes, connect payments, and invite your team — our onboarding guide walks you through each step.',
                ],
                [
                    'question' => 'How do I train my staff?',
                    'answer' => 'Waiters need only their TIPTAP-W code and a two-minute walkthrough. Kitchen staff use a simple display screen. We provide quick-start guides and WhatsApp support during your first week.',
                ],
                [
                    'question' => 'Is my restaurant data safe?',
                    'answer' => 'Yes. Data is encrypted in transit, hosted on secure infrastructure, and access is role-based — managers, waiters, and admins only see what they need. Payment processing is handled by regulated partners such as Selcom.',
                ],
                [
                    'question' => 'Can I use TipTap for multiple branches?',
                    'answer' => 'Yes. Enterprise plans support multiple venues under one account with separate menus, staff, and reporting per branch — ideal for groups and franchises.',
                ],
                [
                    'question' => 'Can waiters register separately?',
                    'answer' => 'Yes. Each waiter receives a unique TIPTAP-W registration code and links to your venue through the manager dashboard. They can track their own tips and orders.',
                ],
                [
                    'question' => 'How does the kitchen receive orders?',
                    'answer' => 'Confirmed orders appear instantly on the kitchen display screen, grouped by table and course. Staff mark items as prepared so front-of-house knows when to serve.',
                ],
                [
                    'question' => 'How does tipping work?',
                    'answer' => 'Guests can add a tip when paying through WhatsApp. Tips are attributed to the assigned waiter and visible on manager and waiter dashboards for transparent payout tracking.',
                ],
                [
                    'question' => 'Is there a free trial?',
                    'answer' => 'Yes. Start with a 14-day free trial — no card required. Test QR ordering, TipTap Rafiki, and payments with real tables before choosing a plan.',
                ],
            ],
        ],
        'testimonials' => [
            'title' => 'What managers are saying',
            'items' => [
                [
                    'quote' => 'We cut order time by 40% in the first week. Guests now browse the menu and pay on WhatsApp without waiting for a waiter.',
                    'highlight' => '40% faster orders',
                    'name' => 'Amina Hassan',
                    'role' => 'General Manager',
                    'restaurant' => 'Khari Grill House',
                    'city' => 'Dar es Salaam',
                    'photo' => null,
                ],
                [
                    'quote' => 'M-Pesa payments land instantly and our waiters finally trust the tip tracking. No more lost bills at the end of service.',
                    'highlight' => null,
                    'name' => 'Juma Makame',
                    'role' => 'Owner',
                    'restaurant' => 'Forodhani Spice Kitchen',
                    'city' => 'Zanzibar',
                    'photo' => null,
                ],
                [
                    'quote' => 'The kitchen display changed how we run peak hour. Orders are clearer, fewer mistakes, and the team stays calm.',
                    'highlight' => null,
                    'name' => 'Grace Mrema',
                    'role' => 'Head Chef',
                    'restaurant' => 'Meru View Bistro',
                    'city' => 'Arusha',
                    'photo' => null,
                ],
            ],
        ],
        'features' => [
            'subtitle' => 'QR tables, TipTap Rafiki, mobile payments, kitchen screen, and manager dashboard — one beautiful platform.',
            'rafiki_card_body' => 'Rafiki means "friend" in Swahili. Guests browse your menu, order, request the bill, and pay — all inside one WhatsApp chat, in English or Swahili.',
        ],
        'demo' => [
            'title' => 'Watch Rafiki handle a full guest journey',
            'subtitle' => 'Scan QR, chat with Rafiki, place an order, and pay with mobile money — all in under 90 seconds.',
            'walkthrough_label' => 'Interactive preview',
            'video_poster' => 'images/demo/rafiki-demo-poster.svg',
            'try_rafiki_label' => 'Try Rafiki now',
            'try_rafiki_message' => 'Hi',
            'try_rafiki_hint' => 'Opens WhatsApp with our live demo bot. Say Hi to explore the menu — no signup required.',
            'steps' => [
                ['icon' => 'qr-code', 'title' => 'Scan table QR', 'caption' => 'Guest scans the code on the table — no app download.'],
                ['icon' => 'messages-square', 'title' => 'Chat with Rafiki', 'caption' => 'Browse the menu and build an order inside WhatsApp.'],
                ['icon' => 'utensils-crossed', 'title' => 'Order hits the kitchen', 'caption' => 'Staff see the order instantly on kitchen display.'],
                ['icon' => 'banknote', 'title' => 'Pay with mobile money', 'caption' => 'M-Pesa, Tigo Pesa, or Airtel Money — bill and tip in one tap.'],
            ],
        ],
        'nurture' => [
            'book_demo_label' => 'Book a 20-minute demo',
            'book_demo_whatsapp_message' => 'Hi, I would like to book a 20-minute TipTap demo.',
            'chat_with_us_label' => 'Chat with us',
            'cta_secondary_hint' => 'Not ready to sign up? Book a quick walkthrough or chat with our team on WhatsApp.',
            'lead_magnet_eyebrow' => 'Free guide',
            'lead_magnet_title' => 'Get our restaurant efficiency guide — free',
            'lead_magnet_subtitle' => 'Practical tips on QR ordering, WhatsApp guest flows, and faster table turns — written for restaurants in Tanzania.',
            'lead_magnet_button' => 'Send me the guide',
            'lead_magnet_success' => 'Thanks! We will email your guide shortly.',
            'lead_magnet_privacy' => 'No spam. Unsubscribe anytime.',
        ],
        'seo' => [
            'html_lang' => 'en-TZ',
            'locale' => 'en_TZ',
            'geo_region' => 'TZ',
            'geo_place_name' => 'Tanzania',
            'og_type' => 'website',
            'twitter_card' => 'summary_large_image',
            'og_image' => 'images/logo.png',
        ],
        'pricing' => [
            'title' => 'Simple plans, clear value',
            'billing_monthly' => 'Monthly',
            'billing_annual' => 'Annual',
            'annual_promo' => 'Pay 10 months, get 2 free',
            'annual_savings' => 'Save ~17%',
            'business_slug' => 'business',
            'enterprise_slug' => 'enterprise',
            'business_anchor' => 'Less than the cost of one hardware POS terminal per year.',
            'enterprise_from_label' => 'Starts from',
            'enterprise_note' => 'Custom onboarding for groups and franchises',
        ],
        'defaults' => [
            'hero_live_badge' => 'Live',
            'hero_badge_text' => 'TipTap Rafiki · WhatsApp · QR · M-Pesa',
            'hero_title_line1' => 'Order, pay, and tip',
            'hero_title_line2' => 'from a WhatsApp chat',
            'hero_description' => 'Guests scan a QR code, place orders, leave reviews, and pay with mobile money or bank — no app, no queues, no hassle.',
            'hero_rafiki_intro' => 'Meet Rafiki — your restaurant\'s WhatsApp friend, always on, always ready.',
            'hero_rafiki_meaning' => 'Rafiki means "friend" in Swahili — TipTap\'s always-on assistant for orders, payments, and guest care.',
            'hero_cta_primary' => 'Start free trial',
            'hero_cta_secondary' => 'See Rafiki in action',
            'demo_title' => 'Watch Rafiki handle a full guest journey',
            'demo_subtitle' => 'Scan QR, chat with Rafiki, place an order, and pay with mobile money — all in under 90 seconds.',
            'demo_video_url' => env('TIPTAP_DEMO_VIDEO_URL', ''),
            'demo_try_rafiki_label' => 'Try Rafiki now',
            'demo_try_rafiki_message' => 'Hi',
            'nurture_book_demo_label' => 'Book a 20-minute demo',
            'nurture_book_demo_calendly_url' => env('TIPTAP_BOOK_DEMO_CALENDLY_URL', ''),
            'nurture_book_demo_whatsapp_message' => 'Hi, I would like to book a 20-minute TipTap demo.',
            'nurture_chat_with_us_label' => 'Chat with us',
            'nurture_lead_magnet_title' => 'Get our restaurant efficiency guide — free',
            'nurture_lead_magnet_subtitle' => 'Practical tips on QR ordering, WhatsApp guest flows, and faster table turns — written for restaurants in Tanzania.',
            'nurture_lead_magnet_button' => 'Send me the guide',
            'nurture_lead_magnet_success' => 'Thanks! We will email your guide shortly.',
            'seo_title' => 'TipTap — QR & WhatsApp Restaurant Ordering in Tanzania | M-Pesa & Selcom',
            'seo_description' => 'TipTap helps restaurants in Tanzania accept QR and WhatsApp orders, settle M-Pesa and mobile money via Selcom, and run kitchen display plus live analytics. Meet TipTap Rafiki — free trial.',
            'seo_keywords' => 'restaurant ordering Tanzania, WhatsApp menu, QR ordering Dar es Salaam, M-Pesa restaurant payments, TipTap Rafiki, Selcom, kitchen display',
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
