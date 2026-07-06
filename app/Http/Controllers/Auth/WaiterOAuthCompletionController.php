<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class WaiterOAuthCompletionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('waiter') && $user->global_waiter_number) {
            return redirect()->route('waiter.dashboard');
        }

        if (! $user->usesOAuth()) {
            return redirect()->route('waiter.register');
        }

        return view('auth.complete-waiter-oauth', [
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('waiter') && $user->global_waiter_number) {
            return redirect()->route('waiter.dashboard');
        }

        if (! $user->usesOAuth()) {
            return redirect()->route('waiter.register');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'location' => 'nullable|string|max:255',
        ]);

        if (! Role::where('name', 'waiter')->where('guard_name', 'web')->exists()) {
            (new RolesAndPermissionsSeeder)->run();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        $user->forceFill([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'phone' => $validated['phone'],
            'location' => $validated['location'] ?? null,
            'global_waiter_number' => $user->global_waiter_number ?: User::generateGlobalWaiterNumber(),
        ])->save();

        if (! $user->hasRole('waiter')) {
            $user->assignRole('waiter');
        }

        return redirect()->route('waiter.dashboard')
            ->with('success', 'Your account has been created. Your unique number: '.$user->global_waiter_number.'. Ask a restaurant manager to link you.');
    }
}
