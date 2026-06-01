<?php

use App\Models\Restaurant;
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

test('super admin can access all new admin portal pages', function (string $route) {
    $this->actingAs($this->admin)
        ->get(route($route))
        ->assertOk();
})->with([
    'search' => 'admin.search.index',
    'live orders' => 'admin.live-orders.index',
    'customer requests' => 'admin.customer-requests.index',
    'tips' => 'admin.tips.index',
    'payroll' => 'admin.payroll.index',
    'reports' => 'admin.reports.index',
    'feedback' => 'admin.feedback.index',
    'menus' => 'admin.menus.index',
    'activity log' => 'admin.activity-log.index',
]);

test('super admin can view restaurant menu read-only', function () {
    $restaurant = Restaurant::create([
        'name' => 'Portal Grill',
        'location' => 'Dar es Salaam',
        'phone' => '0800000001',
        'is_active' => true,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.menus.show', $restaurant))
        ->assertOk()
        ->assertSee('read-only', false);
});

test('manager cannot access new admin portal pages', function (string $route) {
    $this->actingAs($this->manager)
        ->get(route($route))
        ->assertForbidden();
})->with([
    'search' => 'admin.search.index',
    'live orders' => 'admin.live-orders.index',
    'reports' => 'admin.reports.index',
]);

test('global search requires at least two characters', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.search.index', ['q' => 'a']))
        ->assertOk()
        ->assertSee('at least 2 characters', false);
});
