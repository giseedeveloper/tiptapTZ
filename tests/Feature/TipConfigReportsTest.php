<?php

use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\TipPool;
use App\Models\TipPoolMember;
use App\Models\User;
use App\Services\TipPoolService;
use App\Services\TipReportService;
use Carbon\Carbon;
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
        'name' => 'Tip Config Cafe',
        'location' => 'Dar',
        'phone' => '0700123123',
        'tag_prefix' => 'TCC',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->waiter = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Waiter One',
        'digital_tips_enabled' => true,
        'employment_type' => 'permanent',
    ]);
    $this->waiter->assignRole('waiter');

    $this->barista = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Barista One',
        'digital_tips_enabled' => true,
        'employment_type' => 'permanent',
    ]);
    $this->barista->assignRole('barista');

    $this->chef = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Chef One',
        'employment_type' => 'permanent',
    ]);
    $this->chef->assignRole('waiter');

    $this->bot = User::factory()->create(['email' => 'tipcfg-bot@test']);
    $this->bot->assignRole('bot_service');

    $this->pools = app(TipPoolService::class);
});

it('saves tip settings including categories, suggestions and visibility', function (): void {
    $this->actingAs($this->manager)
        ->post(route('manager.tips.settings.update'), [
            'category_waiter' => 1,
            'category_barista' => 0,
            'category_kitchen' => 1,
            'suggestion_mode' => 'percent',
            'percentages' => '5, 10, 20',
            'fixed_amounts' => '1000, 2000',
            'value_visible' => 0,
        ])
        ->assertRedirect();

    $settings = $this->restaurant->fresh()->tipSettings();

    expect($settings['categories']['waiter'])->toBeTrue()
        ->and($settings['categories']['barista'])->toBeFalse()
        ->and($settings['suggestion_mode'])->toBe('percent')
        ->and($settings['percentages'])->toBe([5, 10, 20])
        ->and($settings['fixed_amounts'])->toBe([1000, 2000])
        ->and($settings['value_visible'])->toBeFalse();
});

it('hides disabled tip categories from the bot post-payment options', function (): void {
    $this->restaurant->update([
        'tip_settings' => [
            'categories' => ['waiter' => true, 'barista' => false, 'kitchen' => true],
            'suggestion_mode' => 'percent',
            'percentages' => [5, 10, 15],
            'fixed_amounts' => [500, 1000],
            'value_visible' => true,
        ],
    ]);

    $pool = $this->pools->ensureKitchenPool($this->restaurant->id);
    $pool->update(['is_enabled' => true]);
    TipPoolMember::create(['tip_pool_id' => $pool->id, 'user_id' => $this->chef->id, 'weight' => 1, 'is_active' => true]);

    Sanctum::actingAs($this->bot);

    $this->getJson("/api/bot/restaurant/{$this->restaurant->id}/post-payment-tip-options")
        ->assertOk()
        ->assertJsonPath('data.options.waiter.available', true)
        ->assertJsonPath('data.options.barista.available', false)
        ->assertJsonPath('data.options.kitchen.available', true)
        ->assertJsonPath('data.suggestions.mode', 'percent')
        ->assertJsonPath('data.suggestions.percentages.0', 5);
});

it('builds tip reports split by waiter, barista and kitchen', function (): void {
    $pool = $this->pools->ensureKitchenPool($this->restaurant->id);
    $pool->update(['is_enabled' => true, 'distribution_method' => TipPool::METHOD_EQUAL]);
    TipPoolMember::create(['tip_pool_id' => $pool->id, 'user_id' => $this->chef->id, 'weight' => 1, 'is_active' => true]);

    Setting::set('demo_push', '1');
    Sanctum::actingAs($this->bot);

    // Waiter tip
    $this->postJson('/api/bot/payment/quick', [
        'restaurant_id' => $this->restaurant->id,
        'phone_number' => '255700000001',
        'amount' => 1000,
        'description' => 'Tip waiter',
        'waiter_id' => $this->waiter->id,
    ])->assertOk();

    // Barista tip
    $this->postJson('/api/bot/payment/quick', [
        'restaurant_id' => $this->restaurant->id,
        'phone_number' => '255700000002',
        'amount' => 600,
        'description' => 'Tip barista',
        'waiter_id' => $this->barista->id,
    ])->assertOk();

    // Kitchen pool tip
    $this->postJson('/api/bot/payment/quick', [
        'restaurant_id' => $this->restaurant->id,
        'phone_number' => '255700000003',
        'amount' => 800,
        'description' => 'Tip kitchen',
        'tip_pool_id' => $pool->id,
    ])->assertOk();

    $report = app(TipReportService::class)->build(
        [$this->restaurant->id],
        Carbon::today()->startOfDay(),
        Carbon::now()->endOfDay(),
    );

    expect($report['totals']['waiter'])->toBe(1000.0)
        ->and($report['totals']['barista'])->toBe(600.0)
        ->and($report['totals']['kitchen'])->toBe(800.0)
        ->and($report['totals']['grand'])->toBe(2400.0)
        ->and(collect($report['waiters'])->pluck('name')->all())->toContain('Waiter One')
        ->and(collect($report['baristas'])->pluck('name')->all())->toContain('Barista One')
        ->and(collect($report['kitchen'])->pluck('name')->all())->toContain('Chef One');
});

it('shows tip reports page and blocks export when values are hidden', function (): void {
    $this->actingAs($this->manager->fresh())
        ->get(route('manager.tips.reports', ['period' => 'week']))
        ->assertOk()
        ->assertSee('Tip Reports', false);

    $this->restaurant->update([
        'tip_settings' => ['value_visible' => false],
    ]);

    $this->actingAs($this->manager->fresh())
        ->get(route('manager.tips.reports.export', ['period' => 'week']))
        ->assertForbidden();

    $this->restaurant->update([
        'tip_settings' => ['value_visible' => true],
    ]);

    $this->actingAs($this->manager->fresh())
        ->get(route('manager.tips.reports.export', ['period' => 'week']))
        ->assertOk();
});
