<?php

use App\Models\User;
use App\Support\AdminPortalAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

    $this->superAdmin = User::factory()->create(['email' => 'super@taptap.com']);
    $this->superAdmin->assignRole('super_admin');

    $this->admin = User::factory()->create(['email' => 'admin@taptap.com']);
    $this->admin->assignRole('admin');

    $this->technical = User::factory()->create(['email' => 'tech@taptap.com']);
    $this->technical->assignRole('technical');
});

test('super admin can access panel and technical routes', function (string $route) {
    $this->actingAs($this->superAdmin)
        ->get(route($route))
        ->assertOk();
})->with([
    'dashboard' => 'admin.dashboard',
    'settings' => 'admin.settings.index',
    'docker' => 'admin.docker.index',
    'users' => 'admin.users.index',
    'roles' => 'admin.roles.index',
]);

test('admin can access operational pages but not technical or user management', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('admin.restaurants.index'))
        ->assertOk();

    $this->actingAs($this->admin)
        ->get(route('admin.settings.index'))
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('technical user can access technical pages only', function () {
    $this->actingAs($this->technical)
        ->get(route('admin.settings.index'))
        ->assertOk();

    $this->actingAs($this->technical)
        ->get(route('admin.docker.index'))
        ->assertOk();

    $this->actingAs($this->technical)
        ->get(route('admin.dashboard'))
        ->assertForbidden();

    $this->actingAs($this->technical)
        ->get(route('admin.restaurants.index'))
        ->assertForbidden();
});

test('technical user login redirects to settings home', function () {
    expect(AdminPortalAccess::homeRouteName($this->technical))->toBe('admin.settings.index');
    expect(AdminPortalAccess::homeRouteName($this->admin))->toBe('admin.dashboard');
});

test('super admin can create admin and technical users', function () {
    $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
        'name' => 'Ops Admin',
        'email' => 'ops@taptap.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'admin',
    ]);

    $response->assertRedirect(route('admin.users.index'));
    expect(User::where('email', 'ops@taptap.com')->first()?->hasRole('admin'))->toBeTrue();

    $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
        'name' => 'Dev Tech',
        'email' => 'dev@taptap.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'role' => 'technical',
    ]);

    $response->assertRedirect(route('admin.users.index'));
    expect(User::where('email', 'dev@taptap.com')->first()?->hasRole('technical'))->toBeTrue();
});

test('roles page bootstraps missing admin and technical roles', function () {
    Role::query()->whereIn('name', ['admin', 'technical'])->delete();
    app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

    expect(Role::query()->where('name', 'admin')->exists())->toBeFalse();

    $this->actingAs($this->superAdmin)
        ->get(route('admin.roles.index'))
        ->assertOk();

    expect(Role::query()->where('name', 'admin')->exists())->toBeTrue();
    expect(Role::query()->where('name', 'technical')->exists())->toBeTrue();
});

test('super admin can update role permissions via api', function () {
    $role = Role::findByName('admin');

    $this->actingAs($this->superAdmin)
        ->putJson(route('admin.api.roles.update', 'admin'), [
            'permissions' => ['admin.panel.dashboard', 'admin.panel.orders'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'admin');

    expect($role->fresh()->permissions->pluck('name')->all())
        ->toEqual(['admin.panel.dashboard', 'admin.panel.orders']);
});

test('admin cannot update role permissions via api', function () {
    $this->actingAs($this->admin)
        ->putJson(route('admin.api.roles.update', 'admin'), [
            'permissions' => ['admin.panel.dashboard'],
        ])
        ->assertForbidden();
});

test('super admin can create user via api', function () {
    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.api.users.store'), [
            'name' => 'API Admin',
            'email' => 'api-admin@taptap.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',
        ])
        ->assertCreated()
        ->assertJsonPath('data.email', 'api-admin@taptap.com');
});

test('role permissions can be reset to defaults via api', function () {
    $role = Role::findByName('technical');
    $role->syncPermissions(['admin.technical.settings']);

    $this->actingAs($this->superAdmin)
        ->postJson(route('admin.api.roles.reset', 'technical'))
        ->assertSuccessful();

    expect($role->fresh()->permissions->pluck('name')->all())
        ->toEqual(AdminPortalAccess::defaultPermissionsForRole('technical'));
});
