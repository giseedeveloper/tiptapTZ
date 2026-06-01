<?php

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');

    $this->restaurant = Restaurant::create([
        'name' => 'Live Bistro',
        'location' => 'Dar',
        'phone' => '0800000099',
        'is_active' => true,
    ]);
});

test('admin dashboard analytics api returns charts and stats', function () {
    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => '7',
        'status' => 'preparing',
        'total_amount' => 12000,
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.dashboard.analytics'));

    $response->assertOk()
        ->assertJsonStructure([
            'analytics' => [
                'revenue_trend',
                'orders_trend',
                'orders_by_status',
                'restaurant_split',
                'payment_methods',
                'rating_distribution',
                'top_restaurants',
                'week_comparison',
            ],
            'stats' => ['active_orders', 'total_restaurants'],
            'currency_symbol',
        ])
        ->assertJsonPath('stats.active_orders', 1);
});

test('admin live orders feed returns kanban columns', function () {
    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => '3',
        'status' => 'pending',
        'total_amount' => 8000,
    ]);

    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => '4',
        'status' => 'ready',
        'total_amount' => 15000,
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.live-orders.feed'));

    $response->assertOk()
        ->assertJsonPath('total_live', 2)
        ->assertJsonPath('counts.pending', 1)
        ->assertJsonPath('counts.ready', 1)
        ->assertJsonCount(1, 'columns.pending')
        ->assertJsonCount(1, 'columns.ready');
});

test('live orders feed respects restaurant filter', function () {
    $other = Restaurant::create(['name' => 'Other', 'location' => 'Arusha', 'phone' => '0800000002', 'is_active' => true]);

    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => '1',
        'status' => 'pending',
        'total_amount' => 5000,
    ]);

    Order::create([
        'restaurant_id' => $other->id,
        'table_number' => '2',
        'status' => 'pending',
        'total_amount' => 6000,
    ]);

    $this->actingAs($this->admin)
        ->getJson(route('admin.live-orders.feed', ['restaurant_id' => $this->restaurant->id]))
        ->assertOk()
        ->assertJsonPath('total_live', 1)
        ->assertJsonCount(1, 'columns.pending');
});

test('manager cannot access admin live dashboard apis', function () {
    $manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $manager->assignRole('manager');

    $this->actingAs($manager)->getJson(route('admin.dashboard.analytics'))->assertForbidden();
    $this->actingAs($manager)->getJson(route('admin.live-orders.feed'))->assertForbidden();
});
