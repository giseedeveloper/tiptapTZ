<?php

use App\Models\Restaurant;
use App\Models\SubscriptionPackage;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

/**
 * @return array{0: Restaurant, 1: User}
 */
function onboardedManager(?SubscriptionPackage $package = null): array
{
    $restaurant = Restaurant::create([
        'name' => 'Plan Venue',
        'location' => 'Dar',
        'phone' => '0700000000',
        'is_active' => true,
        'approval_status' => Restaurant::STATUS_ACTIVE,
        'subscription_package_id' => $package?->id,
        'plan_selected_at' => now(),
    ]);

    $manager = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $manager->assignRole('manager');

    return [$restaurant, $manager];
}

test('restaurant without a plan is granted all capabilities and unlimited resources', function () {
    [$restaurant] = onboardedManager(null);

    expect($restaurant->planAllows('kitchen_display'))->toBeTrue();
    expect($restaurant->planAllows('mobile_payments'))->toBeTrue();
    expect($restaurant->withinLimit('tables', 9999))->toBeTrue();
    expect($restaurant->planLimit('waiters'))->toBeNull();
});

test('plan capabilities and limits are enforced by the model helpers', function () {
    $package = SubscriptionPackage::factory()->create([
        'table_limit' => 10,
        'waiter_limit' => 3,
        'capabilities' => ['kitchen_display'],
    ]);
    [$restaurant] = onboardedManager($package);

    expect($restaurant->planAllows('kitchen_display'))->toBeTrue();
    expect($restaurant->planAllows('mobile_payments'))->toBeFalse();
    expect($restaurant->planAllows('advanced_analytics'))->toBeFalse();
    expect($restaurant->withinLimit('tables', 9))->toBeTrue();
    expect($restaurant->withinLimit('tables', 10))->toBeFalse();
    expect($restaurant->planLimit('waiters'))->toBe(3);
});

test('null capabilities means unconfigured plan grants full access', function () {
    $package = SubscriptionPackage::factory()->create(['capabilities' => null]);
    [$restaurant] = onboardedManager($package);

    expect($restaurant->planAllows('kitchen_display'))->toBeTrue();
});

test('table creation is blocked once the plan limit is reached', function () {
    $package = SubscriptionPackage::factory()->create(['table_limit' => 1, 'capabilities' => []]);
    [$restaurant, $manager] = onboardedManager($package);

    Table::create(['restaurant_id' => $restaurant->id, 'name' => 'T1', 'capacity' => 4, 'is_active' => true]);

    $this->actingAs($manager)
        ->post(route('manager.tables.store'), ['name' => 'T2', 'capacity' => 4])
        ->assertSessionHas('error');

    expect(Table::where('restaurant_id', $restaurant->id)->count())->toBe(1);
});

test('table creation is allowed when under the plan limit', function () {
    $package = SubscriptionPackage::factory()->create(['table_limit' => 5, 'capabilities' => []]);
    [$restaurant, $manager] = onboardedManager($package);

    $this->actingAs($manager)
        ->post(route('manager.tables.store'), ['name' => 'T1', 'capacity' => 4])
        ->assertSessionHas('success');

    expect(Table::where('restaurant_id', $restaurant->id)->count())->toBe(1);
});

test('manager without advanced analytics capability is blocked from the analytics endpoint', function () {
    $package = SubscriptionPackage::factory()->create(['capabilities' => []]);
    [$restaurant, $manager] = onboardedManager($package);

    $this->actingAs($manager)
        ->getJson(route('manager.dashboard.analytics'))
        ->assertForbidden();
});

test('manager with advanced analytics capability is not blocked by the capability gate', function () {
    $package = SubscriptionPackage::factory()->create(['capabilities' => ['advanced_analytics']]);
    [$restaurant, $manager] = onboardedManager($package);

    $response = $this->actingAs($manager)->getJson(route('manager.dashboard.analytics'));

    // The capability middleware must allow the request through (not 403).
    expect($response->status())->not->toBe(403);
});

test('waiter limit helper blocks linking beyond the plan allowance', function () {
    $package = SubscriptionPackage::factory()->create(['waiter_limit' => 1, 'capabilities' => []]);
    [$restaurant] = onboardedManager($package);

    User::factory()->create(['restaurant_id' => $restaurant->id])->assignRole('waiter');

    $current = User::role('waiter')->where('restaurant_id', $restaurant->id)->count();
    expect($restaurant->withinLimit('waiters', $current))->toBeFalse();
});
