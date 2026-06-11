<?php

use App\Models\CustomerRequest;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::create([
        'name' => 'Test Grill',
        'location' => 'Dar',
        'phone' => '0800000000',
        'is_active' => true,
    ]);

    $this->waiter = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'is_online' => true,
    ]);
    $this->waiter->assignRole('waiter');

    $this->botUser = User::factory()->create([
        'email' => 'bot-table-call@taptap.test',
    ]);

    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }

    $this->botUser->assignRole('bot_service');

    Sanctum::actingAs($this->botUser);
});

test('call waiter from table uses waiter on active order', function (): void {
    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T5',
        'waiter_id' => $this->waiter->id,
        'status' => 'preparing',
        'total_amount' => 12000,
    ]);

    $response = $this->postJson('/api/bot/call-waiter', [
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T5',
        'type' => 'call_waiter',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true);

    $request = CustomerRequest::withoutGlobalScopes()->first();
    expect($request->waiter_id)->toBe($this->waiter->id);
    expect($request->table_number)->toBe('T5');
});

test('call waiter from table without active order returns 422', function (): void {
    $response = $this->postJson('/api/bot/call-waiter', [
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T9',
        'type' => 'call_waiter',
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false);

    expect(CustomerRequest::withoutGlobalScopes()->count())->toBe(0);
});

test('active order endpoint returns waiter on table order', function (): void {
    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T3',
        'waiter_id' => $this->waiter->id,
        'status' => 'served',
        'total_amount' => 8000,
    ]);

    $response = $this->getJson('/api/bot/active-order?'.http_build_query([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T3',
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('order.waiter_id', $this->waiter->id);
});
