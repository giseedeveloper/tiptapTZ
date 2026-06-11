<?php

use App\Models\Feedback;
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
    ]);
    $this->waiter->assignRole('waiter');

    $this->order = Order::create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T1',
        'customer_phone' => '255712345678',
        'waiter_id' => $this->waiter->id,
        'status' => 'served',
        'total_amount' => 15000,
    ]);

    $this->botUser = User::factory()->create([
        'email' => 'bot-feedback@taptap.test',
    ]);

    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }

    $this->botUser->assignRole('bot_service');

    Sanctum::actingAs($this->botUser);
});

test('bot can submit waiter feedback', function (): void {
    $response = $this->postJson('/api/bot/feedback', [
        'restaurant_id' => $this->restaurant->id,
        'type' => 'waiter',
        'customer_phone' => '255712345678',
        'waiter_id' => $this->waiter->id,
        'order_id' => $this->order->id,
        'rating' => 5,
        'comment' => 'Great service',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true);

    $feedback = Feedback::withoutGlobalScopes()->first();
    expect($feedback->type->value)->toBe('waiter');
    expect($feedback->waiter_id)->toBe($this->waiter->id);
    expect($feedback->rating)->toBe(5);
});

test('bot can submit food feedback linked to latest order', function (): void {
    $response = $this->postJson('/api/bot/feedback', [
        'restaurant_id' => $this->restaurant->id,
        'type' => 'food',
        'customer_phone' => '255712345678',
        'rating' => 4,
        'comment' => 'Tasty meal',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true);

    $feedback = Feedback::withoutGlobalScopes()->first();
    expect($feedback->type->value)->toBe('food');
    expect($feedback->order_id)->toBe($this->order->id);
    expect($feedback->waiter_id)->toBeNull();
});

test('food feedback without order returns 422', function (): void {
    $response = $this->postJson('/api/bot/feedback', [
        'restaurant_id' => $this->restaurant->id,
        'type' => 'food',
        'customer_phone' => '255799999999',
        'rating' => 3,
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false);
});

test('bot latest-order endpoint returns customer order', function (): void {
    $response = $this->getJson('/api/bot/latest-order?'.http_build_query([
        'restaurant_id' => $this->restaurant->id,
        'customer_phone' => '255712345678',
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('order.id', $this->order->id);
});

test('manager can view food ratings page', function (): void {
    Feedback::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'order_id' => $this->order->id,
        'type' => 'food',
        'rating' => 5,
        'comment' => 'Excellent',
    ]);

    $manager = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
    ]);
    $manager->assignRole('manager');

    $this->actingAs($manager)
        ->get(route('manager.food-ratings.index'))
        ->assertOk()
        ->assertSee('Food Ratings')
        ->assertSee('Excellent');
});
