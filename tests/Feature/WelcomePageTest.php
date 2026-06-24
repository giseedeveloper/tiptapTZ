<?php

use App\Models\Setting;
use App\Models\SubscriptionPackage;

test('homepage renders welcome landing page', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('TipTap Rafiki', false);
    $response->assertSee('Order, pay, and tip', false);
    $response->assertSee('from a WhatsApp chat', false);
});

test('homepage includes geo targeted seo meta tags and schema.org json-ld', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('<title>TipTap — QR &amp; WhatsApp Restaurant Ordering in Tanzania | M-Pesa &amp; Selcom</title>', false);
    $response->assertSee('meta name="description" content="TipTap helps restaurants in Tanzania', false);
    $response->assertSee('meta name="geo.region" content="TZ"', false);
    $response->assertSee('meta name="geo.placename" content="Tanzania"', false);
    $response->assertSee('rel="canonical"', false);
    $response->assertSee('property="og:title"', false);
    $response->assertSee('property="og:locale" content="en_TZ"', false);
    $response->assertSee('application/ld+json', false);
    $response->assertSee('"@type":"FAQPage"', false);
    $response->assertSee('"@type":"SoftwareApplication"', false);
    $response->assertSee('"@type":"Organization"', false);
});

test('homepage shows hero trust bar with metrics and payment partners', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Trusted by restaurants across Tanzania', false);
    $response->assertSee('50+', false);
    $response->assertSee('Live restaurants', false);
    $response->assertSee('10k+', false);
    $response->assertSee('Orders processed', false);
    $response->assertSee('Secured payments via', false);
    $response->assertSee('alt="Selcom"', false);
    $response->assertSee('alt="M-Pesa"', false);
    $response->assertSee('alt="Tigo Pesa"', false);
    $response->assertSeeInOrder([
        'Trusted by restaurants across Tanzania',
        'Start free trial',
    ], false);
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

test('homepage introduces TipTap Rafiki in the hero section', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('hero-rafiki-intro', false);
    $response->assertSee('Meet', false);
    $response->assertSee('<span class="text-fin-primary-dark font-semibold">Rafiki</span>', false);
    $response->assertSee('WhatsApp friend, always on, always ready', false);
    $response->assertSee('Rafiki means &quot;friend&quot; in Swahili', false);
    $response->assertSee('See Rafiki in action', false);
    $response->assertSeeInOrder([
        'hero-rafiki-intro',
        'Guests scan a QR code',
    ], false);
});

test('homepage shows rafiki demo section with interactive walkthrough and try rafiki link', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('id="demo"', false);
    $response->assertSee('Watch Rafiki handle a full guest journey', false);
    $response->assertSee('Interactive preview', false);
    $response->assertSee('data-demo-walkthrough', false);
    $response->assertSee('Try Rafiki now', false);
    $response->assertSee('wa.me/', false);
    $response->assertSee('text=Hi', false);
    $response->assertSee('Pay with mobile money', false);
});

test('homepage embeds configured demo video url', function () {
    Setting::set('landing_demo_video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'landing');

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('youtube-nocookie.com/embed/dQw4w9WgXcQ', false);
    $response->assertDontSee('data-demo-walkthrough', false);
});

test('homepage shows dedicated payment partners section with mno and gateway logos', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('id="partners"', false);
    $response->assertSee('Powered by trusted payment partners', false);
    $response->assertSee('Payment gateway', false);
    $response->assertSee('Mobile money', false);
    $response->assertSee('Banking partner', false);
    $response->assertSee('alt="Selcom"', false);
    $response->assertSee('alt="M-Pesa"', false);
    $response->assertSee('alt="Tigo Pesa"', false);
    $response->assertSee('alt="Airtel Money"', false);
    $response->assertSee('alt="Stanbic Bank"', false);
});

test('homepage shows nurture ctas for book demo chat and lead magnet', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Book a 20-minute demo', false);
    $response->assertSee('Chat with us', false);
    $response->assertSee('id="lead-magnet"', false);
    $response->assertSee('Get our restaurant efficiency guide', false);
    $response->assertSee('Send me the guide', false);
    $response->assertSee('wa.me/', false);
    $response->assertSee(route('landing.lead-magnet'), false);
});

test('homepage shows named testimonials with restaurants and metrics', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Amina Hassan', false);
    $response->assertSee('Khari Grill House', false);
    $response->assertSee('Dar es Salaam', false);
    $response->assertSee('40% faster orders', false);
    $response->assertSee('cut order time by 40%', false);
    $response->assertSee('Juma Makame', false);
    $response->assertSee('Forodhani Spice Kitchen', false);
    $response->assertDontSee('Manager, Dar es Salaam', false);
});

test('homepage shows expanded faq section with whatsapp chat link', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('What happens if the internet goes down?', false);
    $response->assertSee('How do I train my staff?', false);
    $response->assertSee('Is my restaurant data safe?', false);
    $response->assertSee('Can I use TipTap for multiple branches?', false);
    $response->assertSee('Still have questions?', false);
    $response->assertSee('Chat with us on WhatsApp', false);
    $response->assertSee('M-Pesa, Tigo Pesa, and Airtel Money through Selcom', false);
    expect(substr_count($response->getContent(), '<details'))->toBe(12);
});

test('homepage shows pricing with annual billing toggle and plan anchors', function () {
    SubscriptionPackage::factory()->free()->create([
        'slug' => 'starter',
        'name' => 'Starter',
        'sort_order' => 1,
    ]);

    SubscriptionPackage::factory()->featured()->create([
        'slug' => 'business',
        'name' => 'Business',
        'price' => 50000,
        'currency' => 'TZS',
        'billing_period' => 'monthly',
        'sort_order' => 2,
        'features' => ['QR ordering', 'WhatsApp bot (TipTap Rafiki)'],
    ]);

    SubscriptionPackage::factory()->create([
        'slug' => 'enterprise',
        'name' => 'Enterprise',
        'price' => 150000,
        'currency' => 'TZS',
        'billing_period' => 'monthly',
        'sort_order' => 3,
        'features' => ['Multi-branch support'],
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('Pay 10 months, get 2 free', false);
    $response->assertSee('Save ~17%', false);
    $response->assertSee('Less than the cost of one hardware POS terminal per year.', false);
    $response->assertSee('Starts from', false);
    $response->assertSee('TZS 500,000', false);
    $response->assertSee('Contact sales', false);
});

test('homepage shows configured social media links', function () {
    Setting::set('landing_social_instagram', 'https://instagram.com/tiptapafrica', 'landing');
    Setting::set('landing_social_x', 'https://x.com/tiptapafrica', 'landing');

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('https://instagram.com/tiptapafrica', false);
    $response->assertSee('https://x.com/tiptapafrica', false);
});
