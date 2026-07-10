<?php

use App\Models\MenuEngagementSession;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use App\Notifications\CustomerMenuEngagementNotification;
use App\Services\MenuEngagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::create([
        'name' => 'Engagement Test',
        'location' => 'Dar',
        'phone' => '0700000001',
        'is_active' => true,
        'menu_engagement_alerts_enabled' => true,
        'menu_engagement_timeout_minutes' => 10,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->table = Table::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => '8',
        'is_active' => true,
    ]);

    $this->botUser = User::factory()->create(['email' => 'menu-engagement-bot@test']);

    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }

    $this->botUser->assignRole('bot_service');
    Sanctum::actingAs($this->botUser);
});

test('menu pdf request records engagement session and view menu event', function (): void {
    $this->restaurant->update(['menu_pdf' => 'menus/test.pdf']);

    $response = $this->getJson('/api/bot/restaurant/'.$this->restaurant->id.'/menu-pdf?'.http_build_query([
        'wa_id' => '255712345678',
        'customer_phone' => '255712345678',
        'table_id' => $this->table->id,
        'table_number' => '8',
    ]));

    $response->assertOk()->assertJsonPath('success', true);

    $this->assertDatabaseHas('menu_engagement_sessions', [
        'restaurant_id' => $this->restaurant->id,
        'table_id' => $this->table->id,
        'wa_id' => '255712345678',
        'status' => MenuEngagementSession::STATUS_PENDING,
    ]);

    $this->assertDatabaseHas('bot_events', [
        'restaurant_id' => $this->restaurant->id,
        'event_type' => 'view_menu',
        'wa_id' => '255712345678',
    ]);
});

test('stale menu engagement session notifies manager', function (): void {
    Notification::fake();

    MenuEngagementSession::query()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id' => $this->table->id,
        'table_number' => '8',
        'wa_id' => '255712345678',
        'menu_viewed_at' => now()->subMinutes(11),
        'status' => MenuEngagementSession::STATUS_PENDING,
    ]);

    Artisan::call('menu-engagement:check');

    Notification::assertSentTo($this->manager, CustomerMenuEngagementNotification::class);

    $this->assertDatabaseHas('menu_engagement_sessions', [
        'restaurant_id' => $this->restaurant->id,
        'wa_id' => '255712345678',
        'status' => MenuEngagementSession::STATUS_NOTIFIED,
    ]);
});

test('order placement converts pending menu engagement session', function (): void {
    $category = \App\Models\Category::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Mains',
    ]);

    $menuItem = MenuItem::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'category_id' => $category->id,
        'name' => 'Chips',
        'price' => 5000,
        'is_available' => true,
    ]);

    MenuEngagementSession::query()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id' => $this->table->id,
        'table_number' => '8',
        'wa_id' => '255712345678',
        'menu_viewed_at' => now()->subMinutes(2),
        'status' => MenuEngagementSession::STATUS_PENDING,
    ]);

    $this->postJson('/api/bot/order', [
        'restaurant_id' => $this->restaurant->id,
        'table_id' => $this->table->id,
        'customer_phone' => '255712345678',
        'items' => [
            ['menu_item_id' => $menuItem->id, 'quantity' => 1],
        ],
    ])->assertOk();

    $this->assertDatabaseHas('menu_engagement_sessions', [
        'restaurant_id' => $this->restaurant->id,
        'wa_id' => '255712345678',
        'status' => MenuEngagementSession::STATUS_CONVERTED,
    ]);
});

test('manager can view engagement dashboard and dismiss alert', function (): void {
    $session = MenuEngagementSession::query()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id' => $this->table->id,
        'table_number' => '8',
        'wa_id' => '255799999999',
        'menu_viewed_at' => now()->subMinutes(3),
        'status' => MenuEngagementSession::STATUS_NOTIFIED,
        'notified_at' => now(),
    ]);

    $this->actingAs($this->manager)
        ->get(route('manager.menu-engagement.index'))
        ->assertOk()
        ->assertSee('Customer Engagement Alerts')
        ->assertSee('Table 8');

    $this->actingAs($this->manager)
        ->post(route('manager.menu-engagement.dismiss', $session))
        ->assertRedirect();

    $this->assertDatabaseHas('menu_engagement_sessions', [
        'id' => $session->id,
        'status' => MenuEngagementSession::STATUS_DISMISSED,
    ]);
});

test('menu engagement service skips notify when order exists', function (): void {
    Notification::fake();

    $viewedAt = now()->subMinutes(12);

    MenuEngagementSession::query()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_id' => $this->table->id,
        'table_number' => '8',
        'wa_id' => '255712345678',
        'menu_viewed_at' => $viewedAt,
        'status' => MenuEngagementSession::STATUS_PENDING,
    ]);

    Order::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => '8',
        'customer_phone' => '255712345678',
        'total_amount' => 5000,
        'status' => 'pending',
        'created_at' => $viewedAt->copy()->addMinute(),
        'updated_at' => $viewedAt->copy()->addMinute(),
    ]);

    app(MenuEngagementService::class)->checkAndNotify();

    Notification::assertNothingSent();

    $this->assertDatabaseHas('menu_engagement_sessions', [
        'wa_id' => '255712345678',
        'status' => MenuEngagementSession::STATUS_CONVERTED,
    ]);
});
