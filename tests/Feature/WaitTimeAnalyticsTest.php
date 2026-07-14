<?php

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\WaitTimeAnalyticsService;
use App\Support\OrderWorkflow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::create([
        'name' => 'Wait Time Cafe',
        'location' => 'Dar',
        'phone' => '0700555666',
        'tag_prefix' => 'WTC',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->waiterFast = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Fast Waiter',
    ]);
    $this->waiterFast->assignRole('waiter');

    $this->waiterSlow = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Slow Waiter',
    ]);
    $this->waiterSlow->assignRole('waiter');

    $this->waitTimes = app(WaitTimeAnalyticsService::class);
});

function makeTimedOrder(array $attrs): Order
{
    $timestamps = [
        'created_at' => $attrs['created_at'] ?? now(),
        'received_at' => $attrs['received_at'] ?? null,
        'accepted_at' => $attrs['accepted_at'] ?? null,
        'preparing_at' => $attrs['preparing_at'] ?? null,
        'ready_at' => $attrs['ready_at'] ?? null,
        'served_at' => $attrs['served_at'] ?? null,
        'completed_at' => $attrs['completed_at'] ?? null,
    ];

    unset(
        $attrs['created_at'],
        $attrs['received_at'],
        $attrs['accepted_at'],
        $attrs['preparing_at'],
        $attrs['ready_at'],
        $attrs['served_at'],
        $attrs['completed_at'],
    );

    $order = Order::withoutGlobalScopes()->create(array_merge([
        'restaurant_id' => test()->restaurant->id,
        'table_number' => '1',
        'customer_phone' => '255700000099',
        'status' => OrderWorkflow::SERVED,
        'total_amount' => 1000,
        'payment_status' => 'pending',
    ], $attrs));

    $order->forceFill($timestamps)->saveQuietly();

    return $order->fresh();
}

it('computes customer wait-time averages and waiter speed', function (): void {
    $base = Carbon::parse('2026-07-13 12:00:00');

    makeTimedOrder([
        'waiter_id' => $this->waiterFast->id,
        'created_at' => $base,
        'received_at' => $base,
        'ready_at' => $base->copy()->addMinutes(10),
        'served_at' => $base->copy()->addMinutes(15),
        'completed_at' => $base->copy()->addMinutes(20),
    ]);

    makeTimedOrder([
        'waiter_id' => $this->waiterSlow->id,
        'created_at' => $base->copy()->addHour(),
        'received_at' => $base->copy()->addHour(),
        'ready_at' => $base->copy()->addHour()->addMinutes(25),
        'served_at' => $base->copy()->addHour()->addMinutes(40),
        'completed_at' => $base->copy()->addHour()->addMinutes(50),
    ]);

    $from = $base->copy()->startOfDay();
    $to = $base->copy()->endOfDay();

    $summary = $this->waitTimes->summarize($this->restaurant->id, $from, $to);

    expect($summary['avg_to_ready_minutes'])->toBe(17.5)
        ->and($summary['avg_to_served_minutes'])->toBe(27.5)
        ->and($summary['median_to_served_minutes'])->toBe(27.5)
        ->and($summary['sample_to_served'])->toBe(2)
        ->and($summary['avg_cycle_minutes'])->toBe(35.0);

    $speed = collect($this->waitTimes->waiterSpeedMetrics($this->restaurant->id, $from, $to))->keyBy('waiter_id');

    expect($speed[$this->waiterFast->id]['avg_to_served_minutes'])->toBe(15.0)
        ->and($speed[$this->waiterSlow->id]['avg_to_served_minutes'])->toBe(40.0);

    // Sorted fastest first
    $ordered = $this->waitTimes->waiterSpeedMetrics($this->restaurant->id, $from, $to);
    expect($ordered[0]['waiter_id'])->toBe($this->waiterFast->id);
});

it('shows wait-time analytics on performance report and dashboard', function (): void {
    $base = now()->startOfDay()->addHours(11);

    makeTimedOrder([
        'waiter_id' => $this->waiterFast->id,
        'created_at' => $base,
        'received_at' => $base,
        'ready_at' => $base->copy()->addMinutes(12),
        'served_at' => $base->copy()->addMinutes(18),
        'completed_at' => $base->copy()->addMinutes(25),
    ]);

    $this->actingAs($this->manager)
        ->get(route('manager.reports.performance', ['period' => 'today']))
        ->assertOk()
        ->assertSee('Customer wait-time', false)
        ->assertSee('Avg Wait to Served', false)
        ->assertSee('Fast Waiter', false);

    $this->actingAs($this->manager)
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSee('Avg customer wait', false)
        ->assertSee('Waiter speed', false)
        ->assertSee('Customer wait trend', false);
});
