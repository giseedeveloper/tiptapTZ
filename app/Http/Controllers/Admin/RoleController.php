<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPortalAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(AdminPortalAccess::can($request->user(), 'admin.manage_roles'), 403);

        AdminPortalAccess::bootstrapPortalAccess();

        $roles = Role::query()
            ->whereIn('name', AdminPortalAccess::editableRoles())
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $panelGroups = AdminPortalAccess::groupedPermissionCatalog('panel');
        $technicalGroups = AdminPortalAccess::groupedPermissionCatalog('technical');

        $userCountsByRole = collect(AdminPortalAccess::editableRoles())
            ->mapWithKeys(fn (string $roleName): array => [
                $roleName => AdminPortalAccess::countUsersWithRole($roleName),
            ]);

        $roleSummaries = $roles->mapWithKeys(function (Role $role) use ($panelGroups, $technicalGroups): array {
            $groups = $role->name === 'technical' ? $technicalGroups : $panelGroups;
            $totalPermissions = $groups->flatten(1)->count();
            $assignedCount = $role->permissions
                ->pluck('name')
                ->intersect($groups->flatten(1)->pluck('name'))
                ->count();

            return [
                $role->name => [
                    'label' => AdminPortalAccess::assignableUserRoles()[$role->name] ?? ucwords(str_replace('_', ' ', $role->name)),
                    'description' => $this->roleDescription($role->name),
                    'accent' => $role->name === 'technical' ? 'sky' : 'indigo',
                    'total' => $totalPermissions,
                    'assigned' => $assignedCount,
                ],
            ];
        });

        return view('admin.roles.index', compact(
            'roles',
            'panelGroups',
            'technicalGroups',
            'userCountsByRole',
            'roleSummaries',
        ));
    }

    private function roleDescription(string $roleName): string
    {
        return match ($roleName) {
            'admin' => 'Operations team — restaurants, orders, finance, and content without system tools.',
            'technical' => 'Engineering team — Docker, WhatsApp bot, payment keys, settings, and audit logs.',
            default => 'Configure which admin portal pages this role can access.',
        };
    }
}
