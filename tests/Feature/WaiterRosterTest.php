<?php

use App\Models\Restaurant;
use App\Models\StaffAbsence;
use App\Models\Table;
use App\Models\User;
use App\Models\WaiterShift;
use App\Notifications\TableAssignmentChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::create([
        'name' => 'Roster Test',
        'location' => 'Dar',
        'phone' => '0700000000',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->waiterA = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'is_online' => true,
    ]);
    $this->waiterA->assignRole('waiter');

    $this->waiterB = User::factory()->create([
        'restaurant_id' => $this->restaurant->id,
        'is_online' => true,
    ]);
    $this->waiterB->assignRole('waiter');

    $this->table = Table::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'T1',
        'waiter_id' => $this->waiterA->id,
        'is_active' => true,
    ]);
});

test('manager can assign tables via roster and waiter gets notification', function (): void {
    Notification::fake();

    $this->actingAs($this->manager)
        ->post(route('manager.roster.assign-tables'), [
            'waiter_id' => $this->waiterB->id,
            'table_ids' => [$this->table->id],
        ])
        ->assertRedirect();

    expect($this->table->fresh()->waiter_id)->toBe($this->waiterB->id);

    Notification::assertSentTo($this->waiterB, TableAssignmentChanged::class);
});

test('manager can create shift for waiter', function (): void {
    Notification::fake();

    $this->actingAs($this->manager)
        ->post(route('manager.roster.shifts.store'), [
            'user_id' => $this->waiterA->id,
            'shift_date' => now()->toDateString(),
            'starts_at' => '08:00',
            'ends_at' => '16:00',
            'label' => 'Morning',
        ])
        ->assertRedirect();

    expect(WaiterShift::count())->toBe(1);
    Notification::assertSentTo($this->waiterA, TableAssignmentChanged::class);
});

test('mark absent reassigns tables and sets waiter offline', function (): void {
    Notification::fake();

    $this->actingAs($this->manager)
        ->post(route('manager.roster.mark-absent'), [
            'user_id' => $this->waiterA->id,
            'starts_on' => now()->toDateString(),
            'ends_on' => now()->toDateString(),
            'reason' => 'sick',
            'reassign_to_user_id' => $this->waiterB->id,
        ])
        ->assertRedirect();

    expect($this->table->fresh()->waiter_id)->toBe($this->waiterB->id);
    expect($this->waiterA->fresh()->is_online)->toBeFalse();
    expect(StaffAbsence::count())->toBe(1);
});

test('waiter roster page loads for manager', function (): void {
    $this->actingAs($this->manager)
        ->get(route('manager.roster.index'))
        ->assertSuccessful()
        ->assertSee('Waiter Roster');
});
