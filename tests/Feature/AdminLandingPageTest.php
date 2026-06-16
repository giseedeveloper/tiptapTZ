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
        'hero_title_line1' => 'Review, pay and tip',
        'hero_title_line2' => 'in one platform',
        'hero_description' => 'Updated hero description for tests.',
        'hero_cta_primary' => 'Start free trial',
        'hero_cta_secondary' => 'See demo',
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
        'whatsapp_url' => 'https://wa.me/255620366103',
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
