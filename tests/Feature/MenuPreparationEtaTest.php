<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\MenuItemEta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->restaurant = Restaurant::create([
        'name' => 'ETA Test Rest',
        'location' => 'Dar',
        'phone' => '0700999888',
        'is_active' => true,
    ]);

    $this->category = Category::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'name' => 'Mains',
        'sort_order' => 1,
    ]);

    $this->manager = User::factory()->create(['restaurant_id' => $this->restaurant->id]);
    $this->manager->assignRole('manager');

    $this->botUser = User::factory()->create(['email' => 'eta-bot@test']);
    if (! Role::where('name', 'bot_service')->exists()) {
        Role::create(['name' => 'bot_service', 'guard_name' => 'web']);
    }
    $this->botUser->assignRole('bot_service');
});

test('menu item eta helper defaults and respects overrides', function (): void {
    expect(MenuItemEta::minutes(null))->toBe(15)
        ->and(MenuItemEta::minutes(0))->toBe(15)
        ->and(MenuItemEta::minutes(25))->toBe(25)
        ->and(MenuItemEta::orderMinutes([
            ['preparation_time' => 10],
            ['preparation_time' => 30],
            ['preparation_time' => 12],
        ]))->toBe(30);
});

test('manager can configure and override preparation time per item', function (): void {
    $item = MenuItem::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'category_id' => $this->category->id,
        'name' => 'Steak',
        'price' => 450,
        'is_available' => true,
        'preparation_time' => 15,
    ]);

    $this->actingAs($this->manager)
        ->put(route('manager.menu.update', $item), [
            'name' => 'Steak',
            'category_id' => $this->category->id,
            'price' => 450,
            'description' => 'Grilled',
            'preparation_time' => 35,
            'is_available' => 1,
        ])
        ->assertRedirect();

    expect($item->fresh()->preparation_time)->toBe(35)
        ->and($item->fresh()->effectivePreparationMinutes())->toBe(35);

    $this->actingAs($this->manager)
        ->get(route('manager.menu.index'))
        ->assertOk()
        ->assertSee('Estimated prep time')
        ->assertSee('ETA 35 min');
});

test('bot category items and item details expose eta before ordering', function (): void {
    $custom = MenuItem::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'category_id' => $this->category->id,
        'name' => 'Slow Ribs',
        'price' => 280,
        'description' => 'Smoky',
        'is_available' => true,
        'preparation_time' => 40,
    ]);

    $fallback = MenuItem::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'category_id' => $this->category->id,
        'name' => 'Water',
        'price' => 10,
        'is_available' => true,
        'preparation_time' => null,
    ]);

    Sanctum::actingAs($this->botUser);

    $this->getJson('/api/bot/category/'.$this->category->id.'/items')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonFragment([
            'id' => $custom->id,
            'name' => 'Slow Ribs',
            'preparation_time' => 40,
            'eta_minutes' => 40,
            'eta_label' => 'Ready in ~40 min',
        ])
        ->assertJsonFragment([
            'id' => $fallback->id,
            'name' => 'Water',
            'eta_minutes' => 15,
            'eta_label' => 'Ready in ~15 min',
        ]);

    $this->getJson('/api/bot/item/'.$custom->id)
        ->assertOk()
        ->assertJsonPath('data.eta_minutes', 40)
        ->assertJsonPath('data.eta_label', 'Ready in ~40 min')
        ->assertJsonPath('data.preparation_time', 40);

    $this->getJson('/api/bot/restaurant/'.$this->restaurant->id.'/full-menu')
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Slow Ribs',
            'eta_minutes' => 40,
        ]);
});

test('manager api can override preparation time used for customer eta', function (): void {
    $item = MenuItem::withoutGlobalScopes()->create([
        'restaurant_id' => $this->restaurant->id,
        'category_id' => $this->category->id,
        'name' => 'Pasta',
        'price' => 120,
        'is_available' => true,
        'preparation_time' => 20,
    ]);

    Sanctum::actingAs($this->manager);

    $this->putJson('/api/v1/manager/menu/'.$item->id, [
        'preparation_time' => 12,
        'name' => 'Pasta',
        'category_id' => $this->category->id,
        'price' => 120,
        'is_available' => true,
    ])
        ->assertOk()
        ->assertJsonPath('data.preparation_time', 12)
        ->assertJsonPath('data.eta_minutes', 12)
        ->assertJsonPath('data.eta_label', 'Ready in ~12 min');

    expect($item->fresh()->preparation_time)->toBe(12);

    Sanctum::actingAs($this->botUser);

    $this->getJson('/api/bot/item/'.$item->id)
        ->assertOk()
        ->assertJsonPath('data.eta_minutes', 12)
        ->assertJsonPath('data.eta_label', 'Ready in ~12 min');
});
