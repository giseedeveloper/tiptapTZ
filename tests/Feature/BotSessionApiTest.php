<?php

use App\Models\BotSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->botUser = User::factory()->create([
        'email' => 'bot@taptap.test',
    ]);

    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }

    $this->botUser->assignRole('bot_service');

    Sanctum::actingAs($this->botUser);
});

test('show returns defaults when no session exists yet', function (): void {
    $response = $this->getJson('/api/bot/session?wa_id=255712345678');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('exists', false)
        ->assertJsonPath('data.state', 'START')
        ->assertJsonPath('data.lang', 'en')
        ->assertJsonPath('data.wa_id', '255712345678');
});

test('upsert creates a new persistent session', function (): void {
    $response = $this->putJson('/api/bot/session', [
        'wa_id' => '255712345678',
        'state' => 'HOME',
        'lang' => 'sw',
        'data' => [
            'restaurant_id' => 42,
            'cart' => [
                ['menu_id' => 1, 'qty' => 2, 'price' => 5000],
            ],
        ],
    ]);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.state', 'HOME')
        ->assertJsonPath('data.lang', 'sw')
        ->assertJsonPath('data.data.restaurant_id', 42);

    expect(BotSession::query()->where('wa_id', '255712345678')->exists())->toBeTrue();
});

test('upsert overwrites an existing session', function (): void {
    BotSession::create([
        'wa_id' => '255712345678',
        'state' => 'HOME',
        'lang' => 'en',
        'data' => ['cart' => []],
    ]);

    $this->putJson('/api/bot/session', [
        'wa_id' => '255712345678',
        'state' => 'PAY_NOW',
        'lang' => 'en',
        'data' => ['active_order_id' => 7],
    ])->assertOk();

    expect(BotSession::query()->where('wa_id', '255712345678')->count())->toBe(1);

    $session = BotSession::query()->where('wa_id', '255712345678')->first();
    expect($session->state)->toBe('PAY_NOW');
    expect($session->data['active_order_id'])->toBe(7);
});

test('show returns the stored payload when session exists', function (): void {
    BotSession::create([
        'wa_id' => '255712345678',
        'state' => 'MENU_HUB',
        'lang' => 'sw',
        'data' => ['table_number' => '7'],
    ]);

    $this->getJson('/api/bot/session?wa_id=255712345678')
        ->assertOk()
        ->assertJsonPath('exists', true)
        ->assertJsonPath('data.state', 'MENU_HUB')
        ->assertJsonPath('data.lang', 'sw')
        ->assertJsonPath('data.data.table_number', '7');
});

test('destroy removes the session row', function (): void {
    BotSession::create([
        'wa_id' => '255712345678',
        'state' => 'HOME',
        'lang' => 'en',
        'data' => [],
    ]);

    $this->deleteJson('/api/bot/session?wa_id=255712345678')
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(BotSession::query()->where('wa_id', '255712345678')->exists())->toBeFalse();
});

test('upsert validates wa_id is required', function (): void {
    $this->putJson('/api/bot/session', [
        'state' => 'HOME',
    ])->assertUnprocessable();
});

test('show requires wa_id query parameter', function (): void {
    $this->getJson('/api/bot/session')
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

test('show normalizes a legacy baileys jid to digits only', function (): void {
    BotSession::create([
        'wa_id' => '255712345678',
        'state' => 'HOME',
        'lang' => 'en',
        'data' => ['cart' => []],
    ]);

    $this->getJson('/api/bot/session?wa_id=255712345678@s.whatsapp.net')
        ->assertOk()
        ->assertJsonPath('exists', true)
        ->assertJsonPath('data.wa_id', '255712345678');
});

test('endpoints reject unauthenticated requests', function (): void {
    $this->app['auth']->forgetGuards();

    $this->withHeaders(['Accept' => 'application/json'])
        ->get('/api/bot/session?wa_id=255712345678')
        ->assertUnauthorized();
});

test('show expires idle restaurant sessions and returns expiry metadata', function (): void {
    BotSession::create([
        'wa_id' => '255712345678',
        'state' => 'HOME',
        'lang' => 'en',
        'data' => [
            'restaurant_id' => 9,
            'restaurant_name' => 'Samaki Samaki',
            'table_number' => '5',
        ],
        'last_message_at' => now()->subHours(13),
    ]);

    $response = $this->getJson('/api/bot/session?wa_id=255712345678');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('expired', true)
        ->assertJsonPath('exists', false)
        ->assertJsonPath('expired_restaurant_name', 'Samaki Samaki')
        ->assertJsonPath('lang', 'en')
        ->assertJsonPath('idle_hours', 12);

    expect(BotSession::query()->where('wa_id', '255712345678')->exists())->toBeFalse();
});

test('show keeps active restaurant sessions within idle window', function (): void {
    BotSession::create([
        'wa_id' => '255712345678',
        'state' => 'HOME',
        'lang' => 'sw',
        'data' => [
            'restaurant_id' => 9,
            'restaurant_name' => 'Samaki Samaki',
        ],
        'last_message_at' => now()->subHours(2),
    ]);

    $this->getJson('/api/bot/session?wa_id=255712345678')
        ->assertOk()
        ->assertJsonPath('exists', true)
        ->assertJsonMissingPath('expired')
        ->assertJsonPath('data.state', 'HOME')
        ->assertJsonPath('data.data.restaurant_name', 'Samaki Samaki');

    expect(BotSession::query()->where('wa_id', '255712345678')->exists())->toBeTrue();
});

test('show does not expire sessions without restaurant context', function (): void {
    BotSession::create([
        'wa_id' => '255712345678',
        'state' => 'SEARCH_RESTAURANT',
        'lang' => 'en',
        'data' => [],
        'last_message_at' => now()->subHours(20),
    ]);

    $this->getJson('/api/bot/session?wa_id=255712345678')
        ->assertOk()
        ->assertJsonPath('exists', true)
        ->assertJsonMissingPath('expired');

    expect(BotSession::query()->where('wa_id', '255712345678')->exists())->toBeTrue();
});

test('upsert logs change language event when lang changes with restaurant context', function (): void {
    $restaurant = \App\Models\Restaurant::create([
        'name' => 'Lang Test Grill',
        'location' => 'Test',
        'phone' => '0800000099',
        'is_active' => true,
    ]);

    BotSession::create([
        'wa_id' => '255712345678',
        'state' => 'HOME',
        'lang' => 'en',
        'data' => ['restaurant_id' => $restaurant->id],
    ]);

    $this->putJson('/api/bot/session', [
        'wa_id' => '255712345678',
        'state' => 'HOME',
        'lang' => 'sw',
        'data' => ['restaurant_id' => $restaurant->id],
    ])->assertOk();

    $event = \App\Models\BotEvent::query()
        ->where('event_type', 'change_language')
        ->first();

    expect($event)->not->toBeNull();
    expect($event->metadata['lang'])->toBe('sw');
    expect($event->restaurant_id)->toBe($restaurant->id);
});
