<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRolePermissionsRequest;
use App\Models\AdminActivityLog;
use App\Support\AdminPortalAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(AdminPortalAccess::can($request->user(), 'admin.manage_roles'), 403);

        $roles = Role::query()
            ->whereIn('name', AdminPortalAccess::editableRoles())
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => [
                'name' => $role->name,
                'label' => AdminPortalAccess::assignableUserRoles()[$role->name] ?? ucwords(str_replace('_', ' ', $role->name)),
                'permissions' => $role->permissions->pluck('name')->values(),
            ]);

        return response()->json([
            'data' => $roles,
            'catalog' => [
                'panel' => AdminPortalAccess::groupedPermissionCatalog('panel')->values(),
                'technical' => AdminPortalAccess::groupedPermissionCatalog('technical')->values(),
            ],
        ]);
    }

    public function update(UpdateRolePermissionsRequest $request, string $role): JsonResponse
    {
        $roleModel = Role::query()->where('name', $role)->firstOrFail();

        $permissions = collect($request->validated('permissions'))
            ->unique()
            ->values()
            ->all();

        $roleModel->syncPermissions($permissions);

        AdminActivityLog::log(
            'role.permissions_updated',
            Role::class,
            (int) $roleModel->id,
            null,
            ['role' => $role, 'permissions' => $permissions],
        );

        return response()->json([
            'message' => 'Role permissions updated.',
            'data' => [
                'name' => $roleModel->name,
                'permissions' => $roleModel->fresh()->permissions->pluck('name')->values(),
            ],
        ]);
    }

    public function reset(string $role): JsonResponse
    {
        abort_unless(AdminPortalAccess::can(auth()->user(), 'admin.manage_roles'), 403);
        abort_unless(in_array($role, AdminPortalAccess::editableRoles(), true), 403);

        $roleModel = Role::query()->where('name', $role)->firstOrFail();
        $defaults = AdminPortalAccess::defaultPermissionsForRole($role);
        $roleModel->syncPermissions($defaults);

        AdminActivityLog::log(
            'role.permissions_reset',
            Role::class,
            (int) $roleModel->id,
            null,
            ['role' => $role],
        );

        return response()->json([
            'message' => 'Role reset to defaults.',
            'data' => [
                'name' => $roleModel->name,
                'permissions' => $roleModel->fresh()->permissions->pluck('name')->values(),
            ],
        ]);
    }
}
