<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Support\AdminPortalAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(AdminPortalAccess::can($request->user(), 'admin.manage_users'), 403);

        $query = User::with('restaurant')->latest();

        if ($request->filled('role')) {
            $query->role($request->role);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qry) use ($q): void {
                $qry->where('name', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%');
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::query()
            ->whereIn('name', array_keys(AdminPortalAccess::assignableUserRoles()))
            ->orderBy('name')
            ->get(['name']);

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        abort_unless(AdminPortalAccess::can(auth()->user(), 'admin.manage_users'), 403);

        $roles = Role::query()
            ->whereIn('name', array_keys(AdminPortalAccess::assignableUserRoles()))
            ->orderBy('name')
            ->get();
        $restaurants = \App\Models\Restaurant::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.users.create', compact('roles', 'restaurants'));
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
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
            ['email' => $user->email, 'role' => $validated['role']],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(string $id): View
    {
        abort_unless(AdminPortalAccess::can(auth()->user(), 'admin.manage_users'), 403);

        $user = User::with('restaurant')->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function edit(string $id): View
    {
        abort_unless(AdminPortalAccess::can(auth()->user(), 'admin.manage_users'), 403);

        $user = User::query()->findOrFail($id);
        $restaurants = \App\Models\Restaurant::query()->orderBy('name')->get();
        $roles = Role::query()
            ->whereIn('name', array_keys(AdminPortalAccess::assignableUserRoles()))
            ->orderBy('name')
            ->get();

        return view('admin.users.edit', compact('user', 'restaurants', 'roles'));
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        if ($user->id === auth()->id() && $request->input('role') !== 'super_admin') {
            return back()->with('error', 'You cannot remove your own super admin access.');
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

        AdminActivityLog::log(
            'user.updated',
            User::class,
            (int) $user->id,
            null,
            ['email' => $user->email, 'role' => $validated['role']],
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(AdminPortalAccess::can(auth()->user(), 'admin.manage_users'), 403);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }

        AdminActivityLog::log(
            'user.deleted',
            User::class,
            (int) $user->id,
            ['email' => $user->email],
            null,
        );

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
