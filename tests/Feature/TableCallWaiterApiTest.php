<?php

use App\Models\CustomerRequest;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Table;
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

    $this->busyWaiter = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'is_online' => true,
    ]);
    $this->busyWaiter->assignRole('waiter');

    $this->freeWaiter = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'is_online' => true,
    ]);
    $this->freeWaiter->assignRole('waiter');

    $this->botUser = User::factory()->create([
        'email' => 'bot-table-call@taptap.test',
    ]);

    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }

    $this->botUser->assignRole('bot_service');

    Sanctum::actingAs($this->botUser);
});

test('call waiter from table assigns a free waiter not busy on another order', function (): void {
    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T5',
        'waiter_id' => $this->busyWaiter->id,
        'status' => 'preparing',
        'total_amount' => 12000,
    ]);

    $response = $this->postJson('/api/bot/call-waiter', [
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T9',
        'type' => 'call_waiter',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true);

    $request = CustomerRequest::withoutGlobalScopes()->first();
    expect($request->waiter_id)->toBe($this->freeWaiter->id);
    expect($request->table_number)->toBe('T9');
});

test('call waiter from table returns 422 when every online waiter is busy', function (): void {
    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T1',
        'waiter_id' => $this->busyWaiter->id,
        'status' => 'preparing',
        'total_amount' => 5000,
    ]);

    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T2',
        'waiter_id' => $this->freeWaiter->id,
        'status' => 'served',
        'total_amount' => 7000,
    ]);

    $response = $this->postJson('/api/bot/call-waiter', [
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T9',
        'type' => 'call_waiter',
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false);

    expect(CustomerRequest::withoutGlobalScopes()->count())->toBe(0);
});

test('explicit waiter id from waiter qr is still used directly', function (): void {
    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T5',
        'waiter_id' => $this->busyWaiter->id,
        'status' => 'preparing',
        'total_amount' => 12000,
    ]);

    $response = $this->postJson('/api/bot/call-waiter', [
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T5',
        'waiter_id' => $this->busyWaiter->id,
        'type' => 'call_waiter',
    ]);

    $response->assertSuccessful();

    expect(CustomerRequest::withoutGlobalScopes()->first()->waiter_id)->toBe($this->busyWaiter->id);
});

test('active order endpoint returns waiter on table order', function (): void {
    Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T3',
        'waiter_id' => $this->busyWaiter->id,
        'status' => 'served',
        'total_amount' => 8000,
    ]);

    $response = $this->getJson('/api/bot/active-order?'.http_build_query([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T3',
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('order.waiter_id', $this->busyWaiter->id);
});

test('call waiter ignores bot menu ids sent as table number and resolves table name from table id', function (): void {
    $table = Table::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'T12',
        'is_active' => true,
    ]);

    $response = $this->postJson('/api/bot/call-waiter', [
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'change_language',
        'table_id' => $table->id,
        'type' => 'call_waiter',
    ]);

    $response->assertSuccessful();

    $request = CustomerRequest::withoutGlobalScopes()->first();
    expect($request->table_number)->toBe('T12');
});

test('waiter pending requests api returns resolved table label', function (): void {
    Sanctum::actingAs($this->freeWaiter);

    CustomerRequest::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'change_language',
        'waiter_id' => $this->freeWaiter->id,
        'type' => 'call_waiter',
        'status' => 'pending',
    ]);

    $response = $this->getJson('/api/waiter/requests');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.table_number', null);
});
