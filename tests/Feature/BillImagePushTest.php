<?php

use App\Jobs\SendBillImageToCustomer;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config()->set('whatsapp.bot_notify_url', 'http://bot.test/notify');
    config()->set('whatsapp.bot_notify_secret', 'test-secret');
    config()->set('whatsapp.bill_image_base_url', '');
});

it('uses bill_image_base_url for signed bill link when configured', function () {
    config()->set('whatsapp.bill_image_base_url', 'https://example.test/public');

    $restaurant = Restaurant::create([
        'name' => 'Base URL Cafe',
        'is_active' => true,
    ]);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '1',
        'customer_phone' => '255700000099',
        'whatsapp_jid' => '255700000099@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 100,
    ]);

    $url = $order->billImageUrl();
    expect($url)->toStartWith('https://example.test/public/bill-image/'.$order->id.'/');
    expect($url)->toMatch('/^https:\/\/example\.test\/public\/bill-image\/\d+\/[a-f0-9]{64}$/');
});

it('posts the bill image payload to the bot notify endpoint', function () {
    Http::fake([
        'http://bot.test/notify' => Http::response(['ok' => true], 200),
    ]);

    $restaurant = Restaurant::create([
        'name' => 'Push Cafe',
        'is_active' => true,
    ]);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '9',
        'customer_phone' => '255700000020',
        'whatsapp_jid' => '255700000020@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 8500,
    ]);

    (new SendBillImageToCustomer($order->id, false))->handle();

    Http::assertSent(function ($request) use ($order) {
        return $request->url() === 'http://bot.test/notify'
            && $request->header('X-Bot-Secret')[0] === 'test-secret'
            && $request['event'] === 'bill_image'
            && $request['order_id'] === $order->id
            && $request['jid'] === '255700000020@s.whatsapp.net'
            && preg_match('#/bill-image/'.$order->id.'/([a-f0-9]{64})$#', (string) $request['bill_image_url']);
    });

    expect($order->fresh()->bill_image_pushed_at)->not->toBeNull();
});

it('skips pushing when the bill has already been pushed without force flag', function () {
    Http::fake();

    $restaurant = Restaurant::create([
        'name' => 'Idempotent Cafe',
        'is_active' => true,
    ]);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '4',
        'customer_phone' => '255700000030',
        'whatsapp_jid' => '255700000030@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 3000,
        'bill_image_pushed_at' => now(),
    ]);

    (new SendBillImageToCustomer($order->id, false))->handle();

    Http::assertNothingSent();
});

it('sends again when force flag is true even if already pushed', function () {
    Http::fake([
        'http://bot.test/notify' => Http::response(['ok' => true], 200),
    ]);

    $restaurant = Restaurant::create([
        'name' => 'Resend Cafe',
        'is_active' => true,
    ]);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '8',
        'customer_phone' => '255711111112',
        'whatsapp_jid' => '255711111112@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 4400,
        'bill_image_pushed_at' => now()->subHour(),
    ]);

    (new SendBillImageToCustomer($order->id, true))->handle();

    Http::assertSentCount(1);
});

it('manager can sync-send WhatsApp bill for a served WhatsApp order', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    Http::fake([
        'http://bot.test/notify' => Http::response(['ok' => true], 200),
    ]);

    $restaurant = Restaurant::create([
        'name' => 'Manager Bill Test',
        'is_active' => true,
    ]);

    $manager = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $manager->assignRole('manager');

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '3',
        'customer_phone' => '255722222223',
        'whatsapp_jid' => '165515876151525@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 12000,
    ]);

    $this->actingAs($manager)
        ->post(route('manager.orders.whatsapp-bill', $order))
        ->assertRedirect();

    Http::assertSentCount(1);
    expect($order->fresh()->bill_image_pushed_at)->not->toBeNull();
});

it('manager sees actionable message when bot returns 502 for bill push', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    Http::fake([
        'http://bot.test/notify' => Http::response([
            'ok' => false,
            'error' => 'send_failed',
            'detail' => 'fetch failed for test',
            'hint' => 'Check jid.',
        ], 502),
    ]);

    $restaurant = Restaurant::create([
        'name' => '502 Cafe',
        'is_active' => true,
    ]);

    $manager = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $manager->assignRole('manager');

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '3',
        'customer_phone' => '255700000001',
        'whatsapp_jid' => '255700000001@s.whatsapp.net',
        'status' => 'served',
        'total_amount' => 1000,
    ]);

    $this->actingAs($manager)
        ->post(route('manager.orders.whatsapp-bill', $order))
        ->assertRedirect()
        ->assertSessionHas('error', function (string $message): bool {
            return str_contains($message, '502')
                && str_contains($message, 'fetch failed for test')
                && str_contains($message, 'Check jid.');
        });

    expect($order->fresh()->bill_image_pushed_at)->toBeNull();
});

it('manager send bill derives whatsapp jid from customer phone when missing', function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    Http::fake([
        'http://bot.test/notify' => Http::response(['ok' => true], 200),
    ]);

    $restaurant = Restaurant::create([
        'name' => 'Fallback JID Test',
        'is_active' => true,
    ]);

    $manager = User::factory()->create(['restaurant_id' => $restaurant->id]);
    $manager->assignRole('manager');

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '6',
        'customer_phone' => '+255 711 222 333',
        'whatsapp_jid' => null,
        'status' => 'served',
        'total_amount' => 9800,
    ]);

    $this->actingAs($manager)
        ->post(route('manager.orders.whatsapp-bill', $order))
        ->assertRedirect();

    Http::assertSent(function ($request) {
        return $request->url() === 'http://bot.test/notify'
            && $request['jid'] === '255711222333@s.whatsapp.net';
    });

    expect($order->fresh()->whatsapp_jid)->toBe('255711222333@s.whatsapp.net');
});

it('forced bill push throws when there is no jid and no customer phone', function () {
    Http::fake();

    $restaurant = Restaurant::create([
        'name' => 'No Phone Cafe',
        'is_active' => true,
    ]);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '1',
        'customer_phone' => null,
        'whatsapp_jid' => null,
        'status' => 'served',
        'total_amount' => 1000,
    ]);

    expect(fn () => (new SendBillImageToCustomer($order->id, true))->handle())
        ->toThrow(RuntimeException::class);

    Http::assertNothingSent();
});

it('does not throw when not forced and whatsapp jid is missing', function () {
    Http::fake();

    $restaurant = Restaurant::create([
        'name' => 'Quiet Skip Cafe',
        'is_active' => true,
    ]);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '2',
        'customer_phone' => null,
        'whatsapp_jid' => null,
        'status' => 'served',
        'total_amount' => 500,
    ]);

    (new SendBillImageToCustomer($order->id, false))->handle();

    Http::assertNothingSent();
});
