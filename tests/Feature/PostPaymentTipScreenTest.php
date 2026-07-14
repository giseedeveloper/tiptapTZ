<?php

use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\Tip;
use App\Models\TipPool;
use App\Models\TipPoolContribution;
use App\Models\TipPoolMember;
use App\Models\User;
use App\Services\TipPoolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    if (! Role::where('name', 'barista')->exists()) {
        Role::create(['name' => 'barista', 'guard_name' => 'web']);
    }
    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }

    $this->restaurant = Restaurant::create([
        'name' => 'Post Pay Tips Cafe',
        'location' => 'Dar',
        'phone' => '0700999888',
        'tag_prefix' => 'PPT',
        'is_active' => true,
    ]);

    $this->waiter = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Preferred Waiter',
        'digital_tips_enabled' => true,
        'employment_type' => 'permanent',
    ]);
    $this->waiter->assignRole('waiter');

    $this->barista = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Preferred Barista',
        'digital_tips_enabled' => true,
        'employment_type' => 'permanent',
    ]);
    $this->barista->assignRole('barista');

    $this->chef = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Pool Chef',
        'employment_type' => 'permanent',
    ]);
    $this->chef->assignRole('waiter');

    $this->bot = User::factory()->create(['email' => 'post-tip-bot@test']);
    $this->bot->assignRole('bot_service');

    $this->pools = app(TipPoolService::class);
});

it('exposes post-payment tip options for waiter barista kitchen and split', function (): void {
    $pool = $this->pools->ensureKitchenPool($this->restaurant->id);
    $pool->update(['is_enabled' => true, 'distribution_method' => TipPool::METHOD_EQUAL]);
    TipPoolMember::create([
        'tip_pool_id' => $pool->id,
        'user_id' => $this->chef->id,
        'weight' => 1,
        'is_active' => true,
    ]);

    Sanctum::actingAs($this->bot);

    $this->getJson("/api/bot/restaurant/{$this->restaurant->id}/post-payment-tip-options?waiter_id={$this->waiter->id}")
        ->assertOk()
        ->assertJsonPath('data.options.waiter.available', true)
        ->assertJsonPath('data.options.barista.available', true)
        ->assertJsonPath('data.options.kitchen.available', true)
        ->assertJsonPath('data.options.split.available', true)
        ->assertJsonPath('data.options.waiter.default.id', $this->waiter->id)
        ->assertJsonPath('data.options.kitchen.pool.id', $pool->id)
        ->assertJsonPath('data.options.split.staff.id', $this->waiter->id)
        ->assertJsonPath('data.amounts.0', 500);
});

it('filters tippable staff by role query', function (): void {
    Sanctum::actingAs($this->bot);

    $waiters = $this->getJson("/api/bot/restaurant/{$this->restaurant->id}/waiters?tippable_only=1&role=waiter")
        ->assertOk()
        ->json('data');

    expect(collect($waiters)->pluck('id')->all())
        ->toContain($this->waiter->id)
        ->not->toContain($this->barista->id);

    $baristas = $this->getJson("/api/bot/restaurant/{$this->restaurant->id}/waiters?tippable_only=1&role=barista")
        ->assertOk()
        ->json('data');

    expect(collect($baristas)->pluck('id')->all())
        ->toContain($this->barista->id)
        ->not->toContain($this->waiter->id);
});

it('settles a 50/50 split tip across waiter and kitchen pool', function (): void {
    $pool = $this->pools->ensureKitchenPool($this->restaurant->id);
    $pool->update(['is_enabled' => true, 'distribution_method' => TipPool::METHOD_EQUAL]);
    TipPoolMember::create([
        'tip_pool_id' => $pool->id,
        'user_id' => $this->chef->id,
        'weight' => 1,
        'is_active' => true,
    ]);

    Setting::set('demo_push', '1');
    Sanctum::actingAs($this->bot);

    $response = $this->postJson('/api/bot/payment/quick', [
        'restaurant_id' => $this->restaurant->id,
        'phone_number' => '255700000333',
        'amount' => 1000,
        'description' => 'Split tip',
        'waiter_id' => $this->waiter->id,
        'tip_pool_id' => $pool->id,
    ])->assertOk();

    $paymentId = $response->json('payment_id');
    $payment = Payment::findOrFail($paymentId);

    expect($payment->waiter_id)->toBe($this->waiter->id)
        ->and($payment->tip_pool_id)->toBe($pool->id);

    $waiterTip = Tip::withoutGlobalScopes()
        ->where('payment_id', $paymentId)
        ->where('waiter_id', $this->waiter->id)
        ->first();

    expect($waiterTip)->not->toBeNull()
        ->and((float) $waiterTip->amount)->toBe(500.0);

    $contribution = TipPoolContribution::where('payment_id', $paymentId)->first();
    expect($contribution)->not->toBeNull()
        ->and((float) $contribution->amount)->toBe(500.0)
        ->and(data_get($contribution->meta, 'source'))->toBe('split');

    $chefTip = Tip::withoutGlobalScopes()
        ->where('payment_id', $paymentId)
        ->where('waiter_id', $this->chef->id)
        ->first();

    expect($chefTip)->not->toBeNull()
        ->and((float) $chefTip->amount)->toBe(500.0);
});
