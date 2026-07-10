<?php

use App\Models\Restaurant;
use App\Models\RestaurantBranchGroup;
use App\Models\TableZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->group = RestaurantBranchGroup::create(['name' => 'Kili Group']);

    $this->branchA = Restaurant::create([
        'name' => 'Kili Main',
        'branch_name' => 'Dar CBD',
        'location' => 'Dar',
        'phone' => '0700000100',
        'is_active' => true,
        'branch_group_id' => $this->group->id,
        'branch_sort_order' => 1,
    ]);

    $this->branchB = Restaurant::create([
        'name' => 'Kili Main',
        'branch_name' => 'Masaki',
        'location' => 'Dar',
        'phone' => '0700000101',
        'is_active' => true,
        'branch_group_id' => $this->group->id,
        'branch_sort_order' => 2,
    ]);

    $this->branchManager = User::factory()->create([
        'restaurant_id' => $this->branchA->id,
    ]);
    $this->branchManager->assignRole('branch_manager');
    $this->branchManager->managedBranches()->sync([
        $this->branchA->id => ['is_primary' => true],
        $this->branchB->id => ['is_primary' => false],
    ]);

    $this->waiter = User::factory()->create([
        'restaurant_id' => $this->branchA->id,
    ]);
    $this->waiter->assignRole('waiter');

    $this->zone = TableZone::withoutGlobalScopes()->create([
        'restaurant_id' => $this->branchA->id,
        'name' => 'Ground Floor',
        'sort_order' => 1,
    ]);
});

test('branch manager sees all branches overview on dashboard', function (): void {
    $this->actingAs($this->branchManager)
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSee('All Branches Overview')
        ->assertSee('Dar CBD')
        ->assertSee('Masaki');
});

test('branch manager can switch to a specific branch', function (): void {
    $this->actingAs($this->branchManager)
        ->post(route('manager.switch-branch'), ['branch_id' => $this->branchB->id])
        ->assertRedirect(route('manager.dashboard'));

    expect(session('active_branch_id'))->toBe($this->branchB->id);

    $this->actingAs($this->branchManager)
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertDontSee('All Branches Overview');
});

test('branch manager can create a new branch', function (): void {
    $this->actingAs($this->branchManager)
        ->post(route('manager.branches.store'), [
            'name' => 'Kili Main',
            'branch_name' => 'Arusha',
            'location' => 'Arusha',
            'group_id' => (string) $this->group->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('restaurants', [
        'branch_name' => 'Arusha',
        'branch_group_id' => $this->group->id,
    ]);
});

test('branch manager can promote waiter to floor supervisor and assign zone', function (): void {
    $this->actingAs($this->branchManager)
        ->post(route('manager.switch-branch'), ['branch_id' => $this->branchA->id]);

    $this->actingAs($this->branchManager)
        ->post(route('manager.floor-supervisors.store'), [
            'user_id' => $this->waiter->id,
            'zone_id' => $this->zone->id,
        ])
        ->assertRedirect();

    $this->waiter->refresh();
    expect($this->waiter->hasRole('floor_supervisor'))->toBeTrue();
    expect($this->waiter->zone_id)->toBe($this->zone->id);
    expect($this->zone->fresh()->supervisor_id)->toBe($this->waiter->id);
});

test('floor supervisor can access supervisor dashboard', function (): void {
    $supervisor = User::factory()->create([
        'restaurant_id' => $this->branchA->id,
        'zone_id' => $this->zone->id,
    ]);
    $supervisor->assignRole('floor_supervisor');
    $this->zone->update(['supervisor_id' => $supervisor->id]);

    $this->actingAs($supervisor)
        ->get(route('supervisor.dashboard'))
        ->assertOk()
        ->assertSee('Ground Floor');
});

test('regular manager cannot access branch management routes', function (): void {
    $manager = User::factory()->create(['restaurant_id' => $this->branchA->id]);
    $manager->assignRole('manager');

    $this->actingAs($manager)
        ->get(route('manager.branches.index'))
        ->assertForbidden();
});

test('branch manager dashboard shows performance comparison', function (): void {
    $this->actingAs($this->branchManager)
        ->get(route('manager.dashboard'))
        ->assertOk()
        ->assertSee('Performance Comparison')
        ->assertSee('Orders by branch');
});

test('branch manager can export branch performance csv', function (): void {
    $response = $this->actingAs($this->branchManager)
        ->get(route('manager.branches.export', ['days' => 7]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');
    expect($response->streamedContent())->toContain('Branch');
});

test('branch comparison api returns branch metrics', function (): void {
    $this->actingAs($this->branchManager)
        ->getJson(route('manager.branches.comparison', ['days' => 7]))
        ->assertOk()
        ->assertJsonStructure([
            'period_days',
            'branches',
            'highlights',
            'combined_daily',
        ]);
});

test('admin can promote manager to branch manager with branches', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $manager = User::factory()->create(['restaurant_id' => $this->branchA->id]);
    $manager->assignRole('manager');

    $this->actingAs($admin)
        ->put(route('admin.users.update', $manager), [
            'name' => $manager->name,
            'email' => $manager->email,
            'role' => 'branch_manager',
            'restaurant_id' => $this->branchA->id,
            'branch_ids' => [$this->branchA->id, $this->branchB->id],
        ])
        ->assertRedirect(route('admin.users.index'));

    $manager->refresh();
    expect($manager->hasRole('branch_manager'))->toBeTrue();
    expect($manager->managedBranches()->pluck('restaurants.id')->sort()->values()->all())
        ->toBe(collect([$this->branchA->id, $this->branchB->id])->sort()->values()->all());
});
