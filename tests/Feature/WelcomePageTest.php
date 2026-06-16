<?php

use App\Models\Setting;

test('homepage renders welcome landing page', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('TipTap Rafiki', false);
    $response->assertSee('Review, pay and tip', false);
    $response->assertSee('in one platform', false);
});

test('homepage shows TipTap Africa office addresses', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Where we operate', false);
    $response->assertSee('Tanzanite Park', false);
    $response->assertSee('13th Floor', false);
    $response->assertSee('16 Capricorn Road', false);
    $response->assertSee('Lonehill, 2062', false);
});

test('homepage shows configured social media links', function () {
    Setting::set('landing_social_instagram', 'https://instagram.com/tiptapafrica', 'landing');
    Setting::set('landing_social_x', 'https://x.com/tiptapafrica', 'landing');

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('https://instagram.com/tiptapafrica', false);
    $response->assertSee('https://x.com/tiptapafrica', false);
});
