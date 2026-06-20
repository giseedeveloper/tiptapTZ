<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Support\AdminPortalAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        abort_unless(AdminPortalAccess::can($request->user(), 'admin.manage_users'), 403);

        $query = User::with('restaurant:id,name')->latest();

        if ($request->filled('role')) {
            $query->role($request->string('role')->toString());
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($builder) use ($q): void {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->paginate($request->integer('per_page', 15));

        return response()->json($users);
    }

    public function store(StoreAdminUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'restaurant_id' => $validated['restaurant_id'] ?? null,
        ]);

        $user->syncRoles($validated['role']);

        AdminActivityLog::log(
            'user.created',
            User::class,
            (int) $user->id,
            null,
            ['email' => $user->email, 'role' => $validated['role'], 'via' => 'api'],
        );

        return response()->json([
            'message' => 'User created.',
            'data' => $user->load('roles'),
        ], 201);
    }

    public function update(UpdateAdminUserRequest $request, User $user): JsonResponse
    {
        if ($user->id === auth()->id() && $request->input('role') !== 'super_admin') {
            return response()->json(['message' => 'You cannot remove your own super admin access.'], 422);
        }

        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'restaurant_id' => $validated['restaurant_id'] ?? null,
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => $validated['password']]);
        }

        $user->syncRoles($validated['role']);

        return response()->json([
            'message' => 'User updated.',
            'data' => $user->fresh()->load('roles'),
        ]);
    }
}
