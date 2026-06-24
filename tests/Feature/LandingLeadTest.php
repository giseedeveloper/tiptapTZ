<?php

use App\Models\LandingLead;

test('lead magnet form stores email and redirects with success', function () {
    $response = $this->from('/')
        ->post(route('landing.lead-magnet'), ['email' => 'manager@example.com']);

    $response->assertRedirect(url('/').'#lead-magnet');
    $response->assertSessionHas('lead_magnet_success', true);

    $this->assertDatabaseHas('landing_leads', [
        'email' => 'manager@example.com',
        'market' => config('tiptap.market', 'tz'),
        'source' => 'efficiency_guide',
    ]);
});

test('lead magnet rejects invalid email', function () {
    $this->from('/')
        ->post(route('landing.lead-magnet'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');

    expect(LandingLead::query()->count())->toBe(0);
});

test('lead magnet updates existing email for same market', function () {
    LandingLead::factory()->create([
        'email' => 'manager@example.com',
        'market' => config('tiptap.market', 'tz'),
        'ip_address' => '127.0.0.1',
    ]);

    $this->post(route('landing.lead-magnet'), ['email' => 'manager@example.com'])
        ->assertRedirect(url('/').'#lead-magnet');

    expect(LandingLead::query()->count())->toBe(1);
});
