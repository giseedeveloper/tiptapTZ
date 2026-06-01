<?php

use App\Models\Activity;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('super_admin');

    $this->manager = User::factory()->create();
    $this->manager->assignRole('manager');

    $this->restaurant = Restaurant::create([
        'name' => 'Test Grill',
        'location' => 'Dar es Salaam',
        'phone' => '0800000000',
        'is_active' => true,
    ]);

    $this->manager->update(['restaurant_id' => $this->restaurant->id]);
});

test('super admin can view admin dashboard', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertViewIs('admin.dashboard');
    $response->assertViewHas('stats');
    $response->assertViewHas('recent_restaurants');
    $response->assertViewHas('recent_activities');
    $response->assertSee('Platform Overview');
    $response->assertViewHas('analytics');
    $response->assertSee('Revenue histogram');
    $response->assertSee('Order pipeline');
});

test('admin dashboard stats api returns accurate counts', function () {
    $waiter = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $waiter->assignRole('waiter');

    $order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T1',
        'status' => 'preparing',
        'total_amount' => 50000,
    ]);

    Payment::create([
        'order_id' => $order->id,
        'amount' => 25000,
        'method' => 'cash',
        'status' => 'paid',
    ]);

    Payment::create([
        'order_id' => $order->id,
        'amount' => 10000,
        'method' => 'ussd',
        'status' => 'completed',
    ]);

    Withdrawal::create([
        'restaurant_id' => $this->restaurant->id,
        'amount' => 5000,
        'status' => 'pending',
    ]);

    Activity::create([
        'user_id' => $this->admin->id,
        'description' => 'Test activity logged',
        'type' => 'test',
    ]);

    $response = $this->actingAs($this->admin)->getJson(route('admin.dashboard.stats'));

    $response->assertOk();
    $response->assertJson([
        'total_restaurants' => 1,
        'total_waiters' => 1,
        'active_orders' => 1,
        'total_revenue' => 35000,
        'pending_withdrawals' => 1,
    ]);
});

test('manager cannot access admin dashboard or stats', function () {
    $this->actingAs($this->manager)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($this->manager)
        ->getJson(route('admin.dashboard.stats'))
        ->assertForbidden();
});

test('guest is redirected from admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});
