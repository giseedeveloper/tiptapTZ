<?php

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::create([
        'name' => 'Tips Cafe',
        'location' => 'Dar',
        'phone' => '0700111001',
        'tag_prefix' => 'TIP',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->tippable = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Amina Barista',
        'digital_tips_enabled' => true,
        'employment_type' => 'permanent',
    ]);
    $this->tippable->assignRole('waiter');

    $this->blocked = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Blocked Staff',
        'digital_tips_enabled' => false,
        'employment_type' => 'permanent',
    ]);
    $this->blocked->assignRole('waiter');

    $this->bot = User::factory()->create(['email' => 'tips-bot@test']);
    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }
    $this->bot->assignRole('bot_service');
});

it('lets manager enable and disable digital tipping for specific staff', function (): void {
    $this->actingAs($this->manager)
        ->get(route('manager.waiters.index'))
        ->assertOk()
        ->assertSee('Digital tipping', false)
        ->assertSee('Amina Barista', false);

    $this->actingAs($this->manager)
        ->post(route('manager.waiters.digital-tips', $this->tippable), [
            'digital_tips_enabled' => 0,
        ])
        ->assertRedirect();

    expect($this->tippable->fresh()->digital_tips_enabled)->toBeFalse();

    $this->actingAs($this->manager)
        ->post(route('manager.waiters.digital-tips', $this->blocked), [
            'digital_tips_enabled' => 1,
        ])
        ->assertRedirect();

    expect($this->blocked->fresh()->digital_tips_enabled)->toBeTrue();
});

it('bot tippable waiters list excludes staff without digital tips enabled', function (): void {
    Sanctum::actingAs($this->bot);

    $all = $this->getJson("/api/bot/restaurant/{$this->restaurant->id}/waiters")
        ->assertOk()
        ->json('data');

    expect(collect($all)->pluck('id')->all())
        ->toContain($this->tippable->id)
        ->toContain($this->blocked->id);

    $tippable = $this->getJson("/api/bot/restaurant/{$this->restaurant->id}/waiters?tippable_only=1")
        ->assertOk()
        ->json('data');

    $ids = collect($tippable)->pluck('id')->all();

    expect($ids)->toContain($this->tippable->id)
        ->and($ids)->not->toContain($this->blocked->id);
});

it('rejects quick tip payments to staff without digital tips enabled', function (): void {
    Sanctum::actingAs($this->bot);

    $this->postJson('/api/bot/payment/quick', [
        'restaurant_id' => $this->restaurant->id,
        'phone_number' => '255700000111',
        'amount' => 1000,
        'description' => 'Tip for Blocked Staff',
        'waiter_id' => $this->blocked->id,
    ])->assertStatus(422)
        ->assertJsonFragment(['success' => false]);

    \App\Models\Setting::set('demo_push', '1');

    $this->postJson('/api/bot/payment/quick', [
        'restaurant_id' => $this->restaurant->id,
        'phone_number' => '255700000111',
        'amount' => 1000,
        'description' => 'Tip for Amina Barista',
        'waiter_id' => $this->tippable->id,
    ])->assertOk()
        ->assertJsonPath('success', true);
});

it('starts newly linked waiters with digital tips disabled', function (): void {
    $fresh = User::factory()->create([
        'restaurant_id' => null,
        'name' => 'New Hire',
        'global_waiter_number' => 'TIPTAP-W-99991',
        'digital_tips_enabled' => true,
    ]);
    $fresh->assignRole('waiter');

    $this->actingAs($this->manager)
        ->post(route('manager.waiters.link', $fresh), [
            'employment_type' => 'permanent',
        ])
        ->assertRedirect();

    expect($fresh->fresh()->restaurant_id)->toBe($this->restaurant->id)
        ->and($fresh->fresh()->digital_tips_enabled)->toBeFalse();
});
