<?php

use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Setting;
use App\Models\Tip;
use App\Models\TipPool;
use App\Models\TipPoolAllocation;
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

    $this->restaurant = Restaurant::create([
        'name' => 'Kitchen Pool Cafe',
        'location' => 'Dar',
        'phone' => '0700222333',
        'tag_prefix' => 'KPC',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->chefA = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Chef A',
        'employment_type' => 'permanent',
    ]);
    $this->chefA->assignRole('waiter');

    $this->chefB = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Chef B',
        'employment_type' => 'permanent',
    ]);
    $this->chefB->assignRole('waiter');

    $this->chefC = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Chef C',
        'employment_type' => 'permanent',
    ]);
    $this->chefC->assignRole('waiter');

    $this->bot = User::factory()->create(['email' => 'pool-bot@test']);
    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }
    $this->bot->assignRole('bot_service');

    $this->pools = app(TipPoolService::class);
});

it('splits equal kitchen tip pool amounts fairly including remainder cents', function (): void {
    $pool = $this->pools->ensureKitchenPool($this->restaurant->id);
    foreach ([$this->chefA, $this->chefB, $this->chefC] as $chef) {
        TipPoolMember::create([
            'tip_pool_id' => $pool->id,
            'user_id' => $chef->id,
            'weight' => 1,
            'is_active' => true,
        ]);
    }

    $shares = $this->pools->calculateShares($pool->fresh()->activeMembers, 100.00, TipPool::METHOD_EQUAL);
    $cents = collect($shares)->pluck('cents')->sort()->values()->all();

    expect(array_sum(collect($shares)->pluck('cents')->all()))->toBe(10000)
        ->and($cents)->toBe([3333, 3333, 3334]);
});

it('applies weighted distribution rules', function (): void {
    $pool = $this->pools->ensureKitchenPool($this->restaurant->id);
    TipPoolMember::create(['tip_pool_id' => $pool->id, 'user_id' => $this->chefA->id, 'weight' => 2, 'is_active' => true]);
    TipPoolMember::create(['tip_pool_id' => $pool->id, 'user_id' => $this->chefB->id, 'weight' => 1, 'is_active' => true]);

    $shares = collect($this->pools->calculateShares(
        $pool->fresh()->activeMembers,
        90.00,
        TipPool::METHOD_WEIGHTED
    ))->keyBy('user_id');

    expect($shares[$this->chefA->id]['cents'])->toBe(6000)
        ->and($shares[$this->chefB->id]['cents'])->toBe(3000);
});

it('lets manager configure pool enable members and rules then shows tippable pool to bot', function (): void {
    $this->actingAs($this->manager)
        ->get(route('manager.tips.index'))
        ->assertOk()
        ->assertSee('Pooled digital tipping', false)
        ->assertSee('Distribution rule', false);

    $this->actingAs($this->manager)
        ->post(route('manager.tips.pool.update'), [
            'name' => 'Kitchen tip jar',
            'is_enabled' => 1,
            'distribution_method' => 'weighted',
        ])
        ->assertRedirect();

    $this->actingAs($this->manager)
        ->post(route('manager.tips.members.store'), [
            'user_id' => $this->chefA->id,
            'weight' => 3,
        ])
        ->assertRedirect();

    $this->actingAs($this->manager)
        ->post(route('manager.tips.members.store'), [
            'user_id' => $this->chefB->id,
            'weight' => 1,
        ])
        ->assertRedirect();

    $pool = TipPool::query()->where('restaurant_id', $this->restaurant->id)->first();
    expect($pool->is_enabled)->toBeTrue()
        ->and($pool->distribution_method)->toBe('weighted')
        ->and($pool->isTippable())->toBeTrue();

    Sanctum::actingAs($this->bot);
    $this->getJson("/api/bot/restaurant/{$this->restaurant->id}/tip-pools")
        ->assertOk()
        ->assertJsonPath('data.0.id', $pool->id)
        ->assertJsonPath('data.0.code', 'kitchen');
});

it('distributes a paid kitchen pool tip to members and creates tip rows', function (): void {
    $pool = $this->pools->ensureKitchenPool($this->restaurant->id);
    $pool->update(['is_enabled' => true, 'distribution_method' => TipPool::METHOD_EQUAL]);
    TipPoolMember::create(['tip_pool_id' => $pool->id, 'user_id' => $this->chefA->id, 'weight' => 1, 'is_active' => true]);
    TipPoolMember::create(['tip_pool_id' => $pool->id, 'user_id' => $this->chefB->id, 'weight' => 1, 'is_active' => true]);

    Setting::set('demo_push', '1');
    Sanctum::actingAs($this->bot);

    $response = $this->postJson('/api/bot/payment/quick', [
        'restaurant_id' => $this->restaurant->id,
        'phone_number' => '255700000222',
        'amount' => 1000,
        'description' => 'Kitchen tip pool: Kitchen tip jar',
        'tip_pool_id' => $pool->id,
    ])->assertOk();

    $paymentId = $response->json('payment_id');
    expect(TipPoolContribution::where('payment_id', $paymentId)->exists())->toBeTrue();

    $tips = Tip::withoutGlobalScopes()->where('payment_id', $paymentId)->get();
    expect($tips)->toHaveCount(2)
        ->and((float) $tips->sum('amount'))->toBe(1000.0);

    expect(TipPoolAllocation::query()->count())->toBe(2);
});
