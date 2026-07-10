<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminPortalAccess
{
    /**
     * Ensure portal permissions and roles exist (safe to call on every admin request).
     */
    public static function bootstrapPortalAccess(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::allPermissionNamesIncludingLegacy() as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (self::portalRoles() as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            if ($roleName === 'super_admin') {
                $role->syncPermissions(self::defaultPermissionsForRole('super_admin'));

                continue;
            }

            if ($role->wasRecentlyCreated) {
                $role->syncPermissions(self::defaultPermissionsForRole($roleName));
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Ensure restaurant-side roles exist (manager, branch_manager, etc.).
     * Safe to call before admin user role pickers.
     */
    public static function bootstrapRestaurantStaffRoles(): void
    {
        self::bootstrapPortalAccess();

        $definitions = [
            'manager' => [
                'manage_menu',
                'manage_waiters',
                'view_orders',
                'update_orders',
                'view_payments',
                'confirm_payments',
                'view_feedback',
                'view_reports_restaurant',
            ],
            'branch_manager' => [
                'manage_menu',
                'manage_waiters',
                'manage_branches',
                'view_orders',
                'update_orders',
                'view_payments',
                'confirm_payments',
                'view_feedback',
                'view_reports_restaurant',
            ],
            'floor_supervisor' => [
                'view_orders',
                'update_orders_status',
                'view_feedback',
            ],
            'waiter' => [
                'view_orders',
                'update_orders_status',
                'view_tips',
            ],
            'bot_service' => [
                'api_restaurant_search',
                'api_get_menu',
                'api_create_order',
                'api_create_payment',
                'api_check_payment',
                'api_submit_feedback',
                'api_submit_tip',
            ],
        ];

        foreach ($definitions as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return list<string>
     */
    public static function allPermissionNamesIncludingLegacy(): array
    {
        return array_values(array_unique(array_merge(
            self::permissionNames(),
            [
                'manage_restaurants',
                'manage_system_users',
                'view_all_reports',
                'manage_system_settings',
                'manage_menu',
                'manage_waiters',
                'manage_branches',
                'view_orders',
                'update_orders',
                'view_payments',
                'confirm_payments',
                'view_feedback',
                'view_reports_restaurant',
                'update_orders_status',
                'view_tips',
                'api_restaurant_search',
                'api_get_menu',
                'api_create_order',
                'api_create_payment',
                'api_check_payment',
                'api_submit_feedback',
                'api_submit_tip',
            ],
        )));
    }

    public static function countUsersWithRole(string $roleName): int
    {
        if (! Role::query()->where('name', $roleName)->where('guard_name', 'web')->exists()) {
            return 0;
        }

        return User::role($roleName)->count();
    }

    public static function portalRoles(): array
    {
        return config('admin_portal.portal_roles', []);
    }

    public static function isPortalUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(self::portalRoles());
    }

    public static function permissionNames(): array
    {
        return array_keys(config('admin_portal.permissions', []));
    }

    public static function panelPermissions(): array
    {
        return self::permissionsForSection('panel');
    }

    public static function technicalPermissions(): array
    {
        return self::permissionsForSection('technical');
    }

    public static function managementPermissions(): array
    {
        return self::permissionsForSection('management');
    }

    public static function permissionsForSection(string $section): array
    {
        return collect(config('admin_portal.permissions', []))
            ->filter(fn (array $meta): bool => ($meta['section'] ?? '') === $section)
            ->keys()
            ->all();
    }

    public static function canAccessSection(?User $user, string $section): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasAnyPermission(self::permissionsForSection($section));
    }

    public static function can(?User $user, string $permission): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->can($permission);
    }

    public static function homeRouteName(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        if ($user->hasRole('super_admin') || $user->hasAnyPermission(self::panelPermissions())) {
            return config('admin_portal.panel_home_route', 'admin.dashboard');
        }

        if ($user->hasAnyPermission(self::technicalPermissions())) {
            return config('admin_portal.technical_home_route', 'admin.settings.index');
        }

        return null;
    }

    public static function assignableUserRoles(): array
    {
        return config('admin_portal.assignable_user_roles', []);
    }

    public static function editableRoles(): array
    {
        return config('admin_portal.editable_roles', []);
    }

    public static function defaultPermissionsForRole(string $roleName): array
    {
        $defaults = config("admin_portal.default_role_permissions.{$roleName}");

        if ($defaults === '*') {
            return self::permissionNames();
        }

        return is_array($defaults) ? $defaults : [];
    }

    public static function permissionCatalog(): Collection
    {
        return collect(config('admin_portal.permissions', []))
            ->map(fn (array $meta, string $name): array => [
                'name' => $name,
                'label' => $meta['label'],
                'section' => $meta['section'],
                'group' => $meta['group'],
            ]);
    }

    public static function groupedPermissionCatalog(?string $section = null): Collection
    {
        return self::permissionCatalog()
            ->when($section, fn (Collection $items): Collection => $items->where('section', $section))
            ->groupBy('group');
    }
}
