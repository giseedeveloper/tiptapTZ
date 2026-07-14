<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::create([
        'name' => 'Busy Cafe',
        'location' => 'Dar',
        'phone' => '0700333444',
        'is_active' => true,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->category = Category::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Mains',
        'sort_order' => 1,
    ]);

    $this->item = MenuItem::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'category_id' => $this->category->id,
        'name' => 'Burger',
        'price' => 100,
        'preparation_time' => 20,
        'is_available' => true,
    ]);
});

it('extends ETA when busy mode is on', function (): void {
    expect($this->item->effectivePreparationMinutes($this->restaurant))->toBe(20);

    $this->restaurant->update(['busy_mode' => true, 'busy_eta_multiplier' => 1.5]);

    expect($this->item->effectivePreparationMinutes($this->restaurant->fresh()))->toBe(30);
});

it('lets manager toggle busy mode and multiplier', function (): void {
    $this->actingAs($this->manager)
        ->post(route('manager.menu.busy-mode'), [
            'busy_mode' => 1,
            'busy_eta_multiplier' => 2,
        ])
        ->assertRedirect();

    $fresh = $this->restaurant->fresh();
    expect($fresh->isBusy())->toBeTrue()
        ->and($fresh->busyEtaMultiplier())->toBe(2.0)
        ->and($this->item->fresh()->effectivePreparationMinutes($fresh))->toBe(40);
});
