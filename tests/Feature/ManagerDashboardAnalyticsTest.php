<?php

use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\ManagerDashboardAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $this->restaurant = Restaurant::create(['name' => 'Analytics Bistro', 'location' => 'Dar', 'is_active' => true]);
    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');
});

test('manager dashboard includes analytics section', function () {
    $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));

    $response->assertOk();
    $response->assertSee('Smart Analytics');
    $response->assertSee('Revenue & Orders Cycle', false);
    $response->assertViewHas('analytics');
});

test('manager dashboard analytics service returns seven day trend', function () {
    $analytics = app(ManagerDashboardAnalytics::class)->forRestaurant($this->restaurant->id);

    expect($analytics['weekly_trend'])->toHaveCount(7);
    expect($analytics['hourly_activity'])->toHaveCount(16);
    expect($analytics['status_cycle']['segments'])->toHaveCount(4);
});

test('analytics reflect restaurant orders and payments', function () {
    $order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => '5',
        'status' => 'paid',
        'total_amount' => 25000,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'restaurant_id' => $this->restaurant->id,
        'amount' => 25000,
        'method' => 'mobile',
        'status' => 'paid',
    ]);

    $analytics = app(ManagerDashboardAnalytics::class)->forRestaurant($this->restaurant->id);

    expect(collect($analytics['weekly_trend'])->sum('revenue'))->toBe(25000.0);
    expect($analytics['status_cycle']['total'])->toBeGreaterThan(0);
});
