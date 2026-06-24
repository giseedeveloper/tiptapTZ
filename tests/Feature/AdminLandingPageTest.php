<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');
});

function validLandingPagePayload(): array
{
    return [
        'hero_live_badge' => 'Live',
        'hero_badge_text' => 'TipTap Rafiki · WhatsApp',
        'hero_title_line1' => 'Order, pay, and tip',
        'hero_title_line2' => 'from a WhatsApp chat',
        'hero_rafiki_intro' => 'Meet Rafiki — your restaurant\'s WhatsApp friend, always on, always ready.',
        'hero_rafiki_meaning' => 'Rafiki means "friend" in Swahili — TipTap\'s always-on assistant for orders, payments, and guest care.',
        'hero_description' => 'Updated hero description for tests.',
        'hero_cta_primary' => 'Start free trial',
        'hero_cta_secondary' => 'See demo',
        'demo_title' => 'Custom demo title',
        'demo_subtitle' => 'Custom demo subtitle for tests.',
        'demo_video_url' => '',
        'demo_try_rafiki_label' => 'Try Rafiki now',
        'demo_try_rafiki_message' => 'Hi',
        'nurture_book_demo_label' => 'Book a 20-minute demo',
        'nurture_book_demo_calendly_url' => '',
        'nurture_book_demo_whatsapp_message' => 'Hi, I would like to book a 20-minute TipTap demo.',
        'nurture_chat_with_us_label' => 'Chat with us',
        'nurture_lead_magnet_title' => 'Get our restaurant efficiency guide — free',
        'nurture_lead_magnet_subtitle' => 'Guide subtitle for tests.',
        'nurture_lead_magnet_button' => 'Send me the guide',
        'nurture_lead_magnet_success' => 'Thanks! Guide on the way.',
        'seo_title' => 'TipTap — QR & WhatsApp Restaurant Ordering in Tanzania | M-Pesa & Selcom',
        'seo_description' => 'SEO description for tests.',
        'seo_keywords' => 'TipTap, Tanzania, WhatsApp ordering',
        'contact_label' => 'TipTap Africa',
        'contact_title' => 'Where we operate',
        'contact_description' => 'Contact section description.',
        'contact_social_title' => 'Follow us',
        'contact_social_description' => 'Social description.',
        'office_tz_name' => 'Tanzania',
        'office_tz_city' => 'Dar es Salaam, Kinondoni',
        'office_tz_line1' => 'Tanzanite Park',
        'office_tz_line2' => '14th Floor',
        'office_za_name' => 'South Africa',
        'office_za_city' => 'Lonehill, Gauteng',
        'office_za_line1' => '16 Capricorn Road',
        'office_za_line2' => 'Lonehill, 2062',
        'social_facebook' => 'https://facebook.com/tiptapafrica',
        'social_instagram' => 'https://instagram.com/tiptapafrica',
        'social_x' => '',
        'social_linkedin' => '',
        'social_tiktok' => '',
        'social_youtube' => '',
        'whatsapp_url' => 'https://wa.me/255791070771',
        'cta_title' => 'Upgrade today',
        'cta_description' => 'CTA description.',
        'cta_button' => 'Register now',
        'footer_tagline' => 'Built with care in Tanzania.',
    ];
}

test('guest cannot access landing page manager', function () {
    $this->get(route('admin.landing-page.index'))->assertRedirect(route('login'));
});

test('super admin can view landing page manager', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.landing-page.index'))
        ->assertOk()
        ->assertSee('Landing Page Manager', false)
        ->assertSee('Tanzanite Park', false);
});

test('super admin can update landing page content', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.landing-page.update'), validLandingPagePayload())
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(Setting::get('landing_office_tz_line2'))->toBe('14th Floor');
    expect(Setting::get('landing_social_instagram'))->toBe('https://instagram.com/tiptapafrica');
});

test('landing page manager rejects invalid social url', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.landing-page.update'), [
            ...validLandingPagePayload(),
            'social_instagram' => 'not-a-url',
        ])
        ->assertSessionHasErrors('social_instagram');
});

test('homepage reflects landing page manager updates', function () {
    Setting::set('landing_office_tz_line2', '20th Floor', 'landing');
    Setting::set('landing_hero_title_line1', 'Custom headline', 'landing');

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('20th Floor', false);
    $response->assertSee('Custom headline', false);
});

test('homepage whatsapp links use the configured bot number', function () {
    Setting::set('whatsapp_bot_number', '255791070771', 'api');
    Setting::set('landing_whatsapp_url', 'https://wa.me/255620366103', 'landing');

    $this->get('/')
        ->assertOk()
        ->assertSee('https://wa.me/255791070771', false)
        ->assertDontSee('https://wa.me/255620366103', false);
});
