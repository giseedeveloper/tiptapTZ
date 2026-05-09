<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('returns bill image for a valid signed order url', function () {
    config()->set('whatsapp.bill_image_base_url', '');

    $restaurant = Restaurant::create([
        'name' => 'Samaki Grill',
        'is_active' => true,
    ]);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '7',
        'customer_phone' => '255700000001',
        'customer_name' => 'Mteja',
        'status' => 'served',
        'total_amount' => 12000,
    ]);

    $category = Category::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'name' => 'Main',
    ]);

    $menuItem = MenuItem::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'category_id' => $category->id,
        'name' => 'Wali Samaki',
        'price' => 12000,
        'is_available' => true,
    ]);

    OrderItem::withoutGlobalScopes()->create([
        'order_id' => $order->id,
        'menu_item_id' => $menuItem->id,
        'name' => 'Wali Samaki',
        'quantity' => 1,
        'price' => 12000,
        'total' => 12000,
    ]);

    $url = $order->billImageUrl();
    expect($url)->toContain('/bill-image/'.$order->id.'/');

    $response = $this->get($url);

    $response->assertOk();
    $response->assertHeader('content-type', 'image/png');
    expect(substr($response->getContent(), 0, 8))->toBe(chr(0x89).'PNG'.chr(0x0D).chr(0x0A).chr(0x1A).chr(0x0A));
    expect(strlen($response->getContent()))->toBeGreaterThan(5000);
});

it('accepts legacy bill image URL with signature query parameter', function () {
    $restaurant = Restaurant::create([
        'name' => 'Legacy Bill',
        'is_active' => true,
    ]);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '1',
        'customer_phone' => '255700000011',
        'status' => 'served',
        'total_amount' => 500,
    ]);

    $sig = $order->billImageSignature();
    $response = $this->get('/bill-image/'.$order->id.'?signature='.$sig);

    $response->assertOk();
    $response->assertHeader('content-type', 'image/png');
});

it('includes bill image url in bot order status when order is served', function () {
    $restaurant = Restaurant::create([
        'name' => 'Taptap Cafe',
        'is_active' => true,
    ]);

    $apiUser = User::factory()->create([
        'restaurant_id' => $restaurant->id,
    ]);

    Sanctum::actingAs($apiUser);

    $order = Order::withoutGlobalScopes()->create([
        'restaurant_id' => $restaurant->id,
        'table_number' => '3',
        'customer_phone' => '255700000002',
        'status' => 'served',
        'total_amount' => 7000,
    ]);

    $response = $this->getJson('/api/bot/order/'.$order->id.'/status');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('is_bill_ready', true);

    expect($response->json('bill_image_url'))->toBeString()->not->toBe('');
});
