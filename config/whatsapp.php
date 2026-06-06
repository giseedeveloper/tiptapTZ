<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Bot Notification Endpoint
    |--------------------------------------------------------------------------
    |
    | The URL of the Node.js bot's notify HTTP server (Cloud API v2). When server-side
    | events occur (e.g. an order reaches the "served" stage), Laravel POSTs here
    | so the bot can deliver the bill image to the customer over WhatsApp.
    |
    | Production: many PHP hosts block outbound traffic to non-standard ports. If
    | http://VPS_IP:3001 fails with a connection error, terminate TLS on the VPS
    | (e.g. Nginx on 443) and proxy to 127.0.0.1:3001, then set this URL to https://...
    |
    */

    'bot_notify_url' => env('WHATSAPP_BOT_NOTIFY_URL'),

    'bot_notify_secret' => env('WHATSAPP_BOT_NOTIFY_SECRET'),

    /*
    | Seconds to wait for the bot to fetch the bill PNG and send it on WhatsApp.
    | 8s is often too short; use 45–90 on production if you see "Could not connect"
    | while bot logs show the message was sent (Laravel gave up waiting first).
    */
    'bot_notify_timeout' => (int) env('WHATSAPP_BOT_NOTIFY_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Public base URL for bill images (optional)
    |--------------------------------------------------------------------------
    |
    | The WhatsApp bot downloads the PNG from this URL when sending the bill.
    | It must be reachable from your bot VPS (curl/open HTTPS). If your Laravel
    | app lives under /public on shared hosting, set this to that full base, e.g.
    | https://tiptapafrica.co.za — otherwise route() may generate
    | https://tiptapafrica.co.za/bill-image/... which returns 404
    |
    | Bill URLs use /bill-image/{id}/{signature} (path) so shared-host WAFs that
    | block ?signature=... still allow the bot to download the PNG.
    |
    | Leave empty to use APP_URL + route (default).
    |
    */

    'bill_image_base_url' => env('WHATSAPP_BILL_IMAGE_BASE_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Global bot welcome card (customer says "hi" before choosing a restaurant)
    |--------------------------------------------------------------------------
    |
    | Override per deployment in Admin → Bots. image_url must be HTTPS and
    | reachable by Meta when downloading media for WhatsApp.
    |
    */

    'welcome_title' => env('WHATSAPP_WELCOME_TITLE', 'TipTap'),

    'welcome_body' => env('WHATSAPP_WELCOME_BODY'),

    'welcome_image_url' => env('WHATSAPP_WELCOME_IMAGE_URL'),

];
