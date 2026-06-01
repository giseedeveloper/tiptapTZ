<?php

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');

    $this->restaurant = Restaurant::create([
        'name' => 'Test Venue',
        'location' => 'Johannesburg',
        'phone' => '0800000000',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->waiter = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->waiter->assignRole('waiter');
});

test('super admin can impersonate manager and return to admin', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.impersonate.start', $this->manager))
        ->assertRedirect(route('manager.dashboard'));

    $this->assertAuthenticatedAs($this->manager);
    expect(session('impersonator_id'))->toBe($this->admin->id);

    $this->post(route('impersonate.stop'))
        ->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($this->admin);
    expect(session('impersonator_id'))->toBeNull();
});

test('manager portal shows impersonation banner while impersonating', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.impersonate.start', $this->manager));

    $this->actingAs($this->manager)
        ->withSession(['impersonator_id' => $this->admin->id])
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSee('Impersonation mode', false)
        ->assertSee('Exit impersonation', false);
});

test('cannot impersonate waiter', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.impersonate.start', $this->waiter))
        ->assertForbidden();
});

test('manager cannot start impersonation', function () {
    $otherManager = User::factory()->create();
    $otherManager->assignRole('manager');

    $this->actingAs($this->manager)
        ->post(route('admin.impersonate.start', $otherManager))
        ->assertForbidden();
});
