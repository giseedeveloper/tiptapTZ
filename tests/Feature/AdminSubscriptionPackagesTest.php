<?php

use App\Models\Restaurant;
use App\Models\SubscriptionPackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager');
});

test('super admin can view the plans index', function () {
    SubscriptionPackage::factory()->create(['name' => 'Business']);

    $this->actingAs($this->admin)
        ->get(route('admin.plans.index'))
        ->assertOk()
        ->assertSee('Business');
});

test('non admin cannot view the plans index', function () {
    $this->actingAs($this->manager)
        ->get(route('admin.plans.index'))
        ->assertForbidden();
});

test('super admin can create a plan', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.plans.store'), [
            'name' => 'Pro Plan',
            'tagline' => 'For growing venues',
            'price' => 50000,
            'currency' => 'TZS',
            'billing_period' => 'monthly',
            'features' => ['Unlimited tables', '', 'Kitchen display'],
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.plans.index'));

    $package = SubscriptionPackage::where('name', 'Pro Plan')->first();
    expect($package)->not->toBeNull();
    expect($package->slug)->toBe('pro-plan');
    expect($package->features)->toBe(['Unlimited tables', 'Kitchen display']);
});

test('super admin can update a plan', function () {
    $package = SubscriptionPackage::factory()->create(['name' => 'Old', 'price' => 100]);

    $this->actingAs($this->admin)
        ->put(route('admin.plans.update', $package), [
            'name' => 'New Name',
            'price' => 999,
            'billing_period' => 'monthly',
        ])
        ->assertRedirect(route('admin.plans.index'));

    expect($package->fresh()->name)->toBe('New Name');
    expect((float) $package->fresh()->price)->toBe(999.0);
});

test('plan with restaurants cannot be deleted', function () {
    $package = SubscriptionPackage::factory()->create();
    Restaurant::create(['name' => 'Linked', 'subscription_package_id' => $package->id]);

    $this->actingAs($this->admin)
        ->delete(route('admin.plans.destroy', $package))
        ->assertRedirect(route('admin.plans.index'));

    expect(SubscriptionPackage::find($package->id))->not->toBeNull();
});

test('unused plan can be deleted', function () {
    $package = SubscriptionPackage::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('admin.plans.destroy', $package))
        ->assertRedirect(route('admin.plans.index'));

    expect(SubscriptionPackage::find($package->id))->toBeNull();
});

test('plan creation validates required fields', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.plans.store'), [
            'name' => '',
            'price' => -5,
            'billing_period' => 'invalid',
        ])
        ->assertSessionHasErrors(['name', 'price', 'billing_period']);
});
