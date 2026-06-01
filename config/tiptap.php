<?php

return [
    'market' => env('TIPTAP_MARKET', 'tz'),

    'currency_symbol' => env('TIPTAP_CURRENCY_SYMBOL', 'Tsh'),

    'currency_code' => env('TIPTAP_CURRENCY_CODE', 'TZS'),

    'country_code' => env('TIPTAP_COUNTRY_CODE', '255'),

    'payment_gateway' => env('TIPTAP_PAYMENT_GATEWAY', 'Selcom'),

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
];
