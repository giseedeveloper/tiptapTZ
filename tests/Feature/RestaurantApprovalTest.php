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
});

function makeManagerWithRestaurant(string $status = Restaurant::STATUS_PENDING): array
{
    $restaurant = Restaurant::create([
        'name' => 'Test Venue',
        'location' => 'Cape Town',
        'phone' => '0820000000',
        'is_active' => $status === Restaurant::STATUS_ACTIVE,
        'approval_status' => $status,
    ]);

    $manager = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $manager->assignRole('manager');

    return [$restaurant, $manager];
}

test('pending manager is redirected from dashboard to waiting page', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant();

    $this->actingAs($manager)
        ->get(route('manager.dashboard'))
        ->assertRedirect(route('manager.onboarding.waiting'));
});

test('pending manager can view the waiting page', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant();

    $this->actingAs($manager)
        ->get(route('manager.onboarding.waiting'))
        ->assertOk()
        ->assertSee('awaiting approval');
});

test('status endpoint returns pending with no redirect', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant();

    $this->actingAs($manager)
        ->getJson(route('manager.onboarding.status'))
        ->assertOk()
        ->assertJson(['status' => 'pending', 'redirect' => null]);
});

test('admin can approve a pending restaurant', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant();

    $this->actingAs($this->admin)
        ->post(route('admin.restaurant-requests.approve', $restaurant))
        ->assertRedirect(route('admin.restaurant-requests.index'));

    $restaurant->refresh();
    expect($restaurant->approval_status)->toBe(Restaurant::STATUS_APPROVED);
    expect($restaurant->approved_by)->toBe($this->admin->id);
});

test('approved manager without plan is redirected to plan selection', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant(Restaurant::STATUS_APPROVED);

    $this->actingAs($manager)
        ->get(route('manager.dashboard'))
        ->assertRedirect(route('manager.onboarding.plan'));
});

test('approved manager can view plan selection page', function () {
    SubscriptionPackage::factory()->create(['name' => 'Business', 'is_active' => true]);
    [$restaurant, $manager] = makeManagerWithRestaurant(Restaurant::STATUS_APPROVED);

    $this->actingAs($manager)
        ->get(route('manager.onboarding.plan'))
        ->assertOk()
        ->assertSee('Business');
});

test('manager can select a plan and activate the restaurant', function () {
    $package = SubscriptionPackage::factory()->create(['is_active' => true]);
    [$restaurant, $manager] = makeManagerWithRestaurant(Restaurant::STATUS_APPROVED);

    $this->actingAs($manager)
        ->post(route('manager.onboarding.plan.store'), [
            'subscription_package_id' => $package->id,
        ])
        ->assertRedirect(route('manager.dashboard'));

    $restaurant->refresh();
    expect($restaurant->approval_status)->toBe(Restaurant::STATUS_ACTIVE);
    expect($restaurant->subscription_package_id)->toBe($package->id);
    expect($restaurant->plan_selected_at)->not->toBeNull();
});

test('fully onboarded manager reaches the dashboard', function () {
    $package = SubscriptionPackage::factory()->create(['is_active' => true]);
    [$restaurant, $manager] = makeManagerWithRestaurant(Restaurant::STATUS_ACTIVE);
    $restaurant->update(['subscription_package_id' => $package->id, 'plan_selected_at' => now()]);

    $this->actingAs($manager)
        ->get(route('manager.dashboard'))
        ->assertOk();
});

test('admin can reject a restaurant with a reason', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant();

    $this->actingAs($this->admin)
        ->post(route('admin.restaurant-requests.reject', $restaurant), [
            'rejection_reason' => 'Could not verify your details.',
        ])
        ->assertRedirect(route('admin.restaurant-requests.index'));

    $restaurant->refresh();
    expect($restaurant->approval_status)->toBe(Restaurant::STATUS_REJECTED);
    expect($restaurant->rejection_reason)->toBe('Could not verify your details.');
});

test('rejection requires a reason', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant();

    $this->actingAs($this->admin)
        ->post(route('admin.restaurant-requests.reject', $restaurant), [])
        ->assertSessionHasErrors('rejection_reason');
});

test('rejected manager sees the reason on the waiting page', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant(Restaurant::STATUS_REJECTED);
    $restaurant->update(['rejection_reason' => 'Please update your phone number.']);

    $this->actingAs($manager)
        ->get(route('manager.onboarding.waiting'))
        ->assertOk()
        ->assertSee('Please update your phone number.');
});

test('admin can bulk approve restaurants', function () {
    [$r1] = makeManagerWithRestaurant();
    [$r2] = makeManagerWithRestaurant();

    $this->actingAs($this->admin)
        ->post(route('admin.restaurant-requests.bulk-approve'), [
            'ids' => [$r1->id, $r2->id],
        ])
        ->assertRedirect(route('admin.restaurant-requests.index'));

    expect($r1->fresh()->approval_status)->toBe(Restaurant::STATUS_APPROVED);
    expect($r2->fresh()->approval_status)->toBe(Restaurant::STATUS_APPROVED);
});

test('manager cannot access admin restaurant requests', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant();

    $this->actingAs($manager)
        ->get(route('admin.restaurant-requests.index'))
        ->assertForbidden();
});

test('admin can view the restaurant requests index', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant();

    $this->actingAs($this->admin)
        ->get(route('admin.restaurant-requests.index'))
        ->assertOk()
        ->assertSee('Test Venue');
});

test('admin can view a restaurant request detail page', function () {
    [$restaurant, $manager] = makeManagerWithRestaurant();

    $this->actingAs($this->admin)
        ->get(route('admin.restaurant-requests.show', $restaurant))
        ->assertOk()
        ->assertSee('Test Venue')
        ->assertSee('Approve restaurant');
});
