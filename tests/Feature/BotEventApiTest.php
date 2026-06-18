<?php

use App\Enums\BotEngagementEvent;
use App\Enums\BotFunnelStep;
use App\Enums\BotQrEntryType;
use App\Models\BotEvent;
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
        'is_online' => true,
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
        'email' => 'bot-events@taptap.test',
    ]);

    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }

    $this->botUser->assignRole('bot_service');

    Sanctum::actingAs($this->botUser);
});

test('bot service can store a bot engagement event', function (): void {
    $response = $this->postJson('/api/bot/events', [
        'event_type' => BotEngagementEvent::ViewMenu->value,
        'restaurant_id' => $this->restaurant->id,
        'wa_id' => '255712345678',
        'metadata' => ['menu_type' => 'image'],
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.event_type', 'view_menu')
        ->assertJsonPath('data.restaurant_id', $this->restaurant->id);

    $event = BotEvent::query()->first();
    expect($event->wa_id)->toBe('255712345678');
    expect($event->metadata)->toBe(['menu_type' => 'image']);
});

test('bot events endpoint rejects invalid event types', function (): void {
    $this->postJson('/api/bot/events', [
        'event_type' => 'invalid_action',
        'restaurant_id' => $this->restaurant->id,
    ])->assertUnprocessable();
});

test('submitting feedback records rate service engagement event', function (): void {
    $this->postJson('/api/bot/feedback', [
        'restaurant_id' => $this->restaurant->id,
        'type' => 'waiter',
        'customer_phone' => '255712345678',
        'waiter_id' => $this->waiter->id,
        'order_id' => $this->order->id,
        'rating' => 5,
        'comment' => 'Great service',
    ])->assertSuccessful();

    expect(BotEvent::query()->where('event_type', BotEngagementEvent::RateService->value)->count())->toBe(1);
    expect(Feedback::withoutGlobalScopes()->count())->toBe(1);
});

test('call waiter records call waiter engagement event', function (): void {
    $this->postJson('/api/bot/call-waiter', [
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T3',
        'waiter_id' => $this->waiter->id,
        'type' => 'call_waiter',
        'customer_phone' => '255712345678',
    ])->assertSuccessful();

    expect(BotEvent::query()->where('event_type', BotEngagementEvent::CallWaiter->value)->count())->toBe(1);
});

test('unauthenticated clients cannot store bot events', function (): void {
    $this->app['auth']->forgetGuards();

    $this->withHeaders(['Accept' => 'application/json'])
        ->postJson('/api/bot/events', [
            'event_type' => BotEngagementEvent::ExitBot->value,
            'restaurant_id' => $this->restaurant->id,
        ])
        ->assertUnauthorized();
});

test('bot service can store funnel and qr analytics events', function (): void {
    $this->postJson('/api/bot/events', [
        'event_type' => BotFunnelStep::ViewMenu->value,
        'restaurant_id' => $this->restaurant->id,
        'wa_id' => '255712345678',
    ])->assertCreated();

    $this->postJson('/api/bot/events', [
        'event_type' => BotQrEntryType::Waiter->value,
        'restaurant_id' => $this->restaurant->id,
        'wa_id' => '255712345678',
    ])->assertCreated();

    expect(BotEvent::query()->count())->toBe(2);
});

test('parse entry logs qr waiter and bot home funnel events', function (): void {
    $waiter = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'waiter_code' => 'TST-W01',
    ]);
    $waiter->assignRole('waiter');

    $this->postJson('/api/bot/parse-entry', [
        'input' => 'START_'.$this->restaurant->id.'_W'.$waiter->id,
        'wa_id' => '255712345678',
    ])->assertSuccessful();

    expect(BotEvent::query()->where('event_type', BotQrEntryType::Waiter->value)->count())->toBe(1);
    expect(BotEvent::query()->where('event_type', BotFunnelStep::BotHome->value)->count())->toBe(1);
});
