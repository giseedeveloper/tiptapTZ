<?php

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\TableZone;
use App\Models\User;
use App\Services\TableOccupancyService;
use App\Support\OrderWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::create([
        'name' => 'Occupancy Cafe',
        'location' => 'Dar',
        'phone' => '0700777888',
        'tag_prefix' => 'OCC',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->waiter = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Floor Waiter',
    ]);
    $this->waiter->assignRole('waiter');

    $this->zone = TableZone::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Patio',
        'sort_order' => 1,
    ]);

    $this->tableA = Table::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'zone_id' => $this->zone->id,
        'waiter_id' => $this->waiter->id,
        'name' => 'T1',
        'capacity' => 4,
        'is_active' => true,
    ]);

    $this->tableB = Table::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'zone_id' => $this->zone->id,
        'name' => 'T2',
        'capacity' => 2,
        'is_active' => true,
    ]);

    $this->tableInactive = Table::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'T3',
        'capacity' => 2,
        'is_active' => false,
    ]);

    $this->occupancy = app(TableOccupancyService::class);
});

it('marks tables occupied from active orders and computes occupancy percent', function (): void {
    Order::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'waiter_id' => $this->waiter->id,
        'table_number' => 'T1',
        'status' => OrderWorkflow::PREPARING,
        'total_amount' => 2500,
        'received_at' => now()->subMinutes(20),
    ]);

    $snapshot = $this->occupancy->snapshot($this->restaurant->id);
    $summary = $snapshot['summary'];
    $byName = collect($snapshot['tables'])->keyBy('name');

    expect($summary['occupied'])->toBe(1)
        ->and($summary['free'])->toBe(1)
        ->and($summary['active_tables'])->toBe(2)
        ->and($summary['inactive_tables'])->toBe(1)
        ->and($summary['occupancy_percent'])->toBe(50.0)
        ->and($byName['T1']['status'])->toBe('occupied')
        ->and($byName['T1']['order']['id'])->not->toBeNull()
        ->and($byName['T2']['status'])->toBe('free')
        ->and($byName['T3']['status'])->toBe('inactive')
        ->and($snapshot['zones'][0]['name'])->toBe('Patio');
});

it('serves occupancy dashboard and live feed for managers', function (): void {
    Order::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'table_number' => 'T2',
        'status' => OrderWorkflow::READY,
        'total_amount' => 1800,
        'received_at' => now()->subMinutes(10),
    ]);

    $this->actingAs($this->manager)
        ->get(route('manager.tables.occupancy'))
        ->assertOk()
        ->assertSee('Table Occupancy', false)
        ->assertSee('Floor Operations', false)
        ->assertSee('T2', false);

    $this->actingAs($this->manager)
        ->getJson(route('manager.tables.occupancy.feed'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.summary.occupied', 1)
        ->assertJsonPath('data.summary.free', 1);
});
