<?php

use App\Models\Order;
use App\Models\OrderPortalPassword;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config()->set('whatsapp.bot_notify_url', 'http://bot.test/notify');
    config()->set('whatsapp.bot_notify_secret', 'test-secret');
    config()->set('whatsapp.bill_image_base_url', '');
});

function createOrderPortalToken(Restaurant $restaurant, User $waiter, string $password = 'PORTAL99'): string
{
    test()->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    $waiter->update(['restaurant_id' => $restaurant->id]);
    $waiter->assignRole('waiter');

    OrderPortalPassword::query()->create([
        'restaurant_id' => $restaurant->id,
        'user_id' => $waiter->id,
        'password' => $password,
        'generated_at' => now(),
    ]);

    $response = test()->postJson('/api/order-portal/login', [
        'password' => $password,
    ]);

    $response->assertSuccessful();

    return (string) $response->json('data.token');
}

it('order portal can confirm and send WhatsApp bill for a served order', function () {
    Http::fake([
        'http://bot.test/notify' => Http::response(['ok' => true], 200),
    ]);

    $restaurant = Restaurant::create([
        'name' => 'Portal Bill Cafe',
        'is_active' => true,
    ]);

    $waiter = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $token = createOrderPortalToken($restaurant, $waiter);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'waiter_id' => $waiter->id,
        'table_number' => '7',
        'customer_phone' => '255700000040',
        'whatsapp_jid' => '255700000040@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 5600,
    ]);

    $response = test()->withToken($token)
        ->postJson("/api/order-portal/orders/{$order->id}/whatsapp-bill");

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.bill_already_sent', true)
        ->assertJsonPath('data.can_send_whatsapp_bill', true)
        ->assertJsonPath('meta.recipient', '255700000040');

    Http::assertSentCount(1);
    expect($order->fresh()->bill_image_pushed_at)->not->toBeNull();
});

it('order portal whatsapp bill derives jid from customer phone when missing', function () {
    Http::fake([
        'http://bot.test/notify' => Http::response(['ok' => true], 200),
    ]);

    $restaurant = Restaurant::create([
        'name' => 'Portal Fallback Cafe',
        'is_active' => true,
    ]);

    $waiter = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $token = createOrderPortalToken($restaurant, $waiter);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'waiter_id' => $waiter->id,
        'table_number' => '2',
        'customer_phone' => '+255 711 333 444',
        'whatsapp_jid' => null,
        'status' => 'served',
        'total_amount' => 3200,
    ]);

    test()->withToken($token)
        ->postJson("/api/order-portal/orders/{$order->id}/whatsapp-bill")
        ->assertSuccessful()
        ->assertJsonPath('data.whatsapp_jid', '255711333444@s.whatsapp.net');

    Http::assertSent(function ($request) {
        return $request['jid'] === '255711333444' && $request['force'] === true;
    });
});

it('order portal whatsapp bill rejects non served orders', function () {
    Http::fake();

    $restaurant = Restaurant::create([
        'name' => 'Portal Status Cafe',
        'is_active' => true,
    ]);

    $waiter = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $token = createOrderPortalToken($restaurant, $waiter);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'waiter_id' => $waiter->id,
        'table_number' => '1',
        'customer_phone' => '255700000050',
        'whatsapp_jid' => '255700000050@s.whatsapp.net',
        'status' => 'preparing',
        'total_amount' => 1000,
    ]);

    test()->withToken($token)
        ->postJson("/api/order-portal/orders/{$order->id}/whatsapp-bill")
        ->assertUnprocessable()
        ->assertJsonPath('error_code', 'invalid_status');

    Http::assertNothingSent();
});

it('order portal whatsapp bill cannot access another waiters order', function () {
    Http::fake();

    $restaurant = Restaurant::create([
        'name' => 'Portal Scope Cafe',
        'is_active' => true,
    ]);

    $waiter = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $otherWaiter = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $token = createOrderPortalToken($restaurant, $waiter);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'waiter_id' => $otherWaiter->id,
        'table_number' => '5',
        'customer_phone' => '255700000060',
        'whatsapp_jid' => '255700000060@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 1500,
    ]);

    test()->withToken($token)
        ->postJson("/api/order-portal/orders/{$order->id}/whatsapp-bill")
        ->assertNotFound();

    Http::assertNothingSent();
});

it('order portal orders index includes whatsapp bill metadata', function () {
    $restaurant = Restaurant::create([
        'name' => 'Portal Meta Cafe',
        'is_active' => true,
    ]);

    $waiter = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $token = createOrderPortalToken($restaurant, $waiter);

    Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'waiter_id' => $waiter->id,
        'table_number' => '9',
        'customer_phone' => '255700000070',
        'whatsapp_jid' => '255700000070@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 4400,
        'bill_image_pushed_at' => now(),
    ]);

    test()->withToken($token)
        ->getJson('/api/order-portal/orders')
        ->assertSuccessful()
        ->assertJsonPath('data.served.0.bill_already_sent', true)
        ->assertJsonPath('data.served.0.can_resend_whatsapp_bill', true)
        ->assertJsonPath('data.served.0.is_whatsapp_order', true);
});

it('order portal whatsapp bill returns bot error details as json', function () {
    Http::fake([
        'http://bot.test/notify' => Http::response([
            'ok' => false,
            'error' => 'send_failed',
            'detail' => 'fetch failed for test',
            'hint' => 'Check jid.',
        ], 502),
    ]);

    $restaurant = Restaurant::create([
        'name' => 'Portal 502 Cafe',
        'is_active' => true,
    ]);

    $waiter = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $token = createOrderPortalToken($restaurant, $waiter);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'waiter_id' => $waiter->id,
        'table_number' => '3',
        'customer_phone' => '255700000080',
        'whatsapp_jid' => '255700000080@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 2100,
    ]);

    test()->withToken($token)
        ->postJson("/api/order-portal/orders/{$order->id}/whatsapp-bill")
        ->assertStatus(502)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error_code', 'send_failed');

    expect($order->fresh()->bill_image_pushed_at)->toBeNull();
});
