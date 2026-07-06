<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class RestaurantOAuthCompletionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('manager') && $user->restaurant_id) {
            return redirect()->route('manager.onboarding.waiting');
        }

        if (! $user->usesOAuth()) {
            return redirect()->route('restaurant.register');
        }

        return view('auth.complete-restaurant-oauth', [
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('manager') && $user->restaurant_id) {
            return redirect()->route('manager.onboarding.waiting');
        }

        if (! $user->usesOAuth()) {
            return redirect()->route('restaurant.register');
        }

        $validated = $request->validate([
            'manager_name' => 'required|string|max:255',
            'restaurant_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        if (! Role::where('name', 'manager')->where('guard_name', 'web')->exists()) {
            (new RolesAndPermissionsSeeder)->run();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }

        try {
            DB::transaction(function () use ($validated, $user) {
                $restaurant = Restaurant::create([
                    'name' => $validated['restaurant_name'],
                    'location' => $validated['location'],
                    'phone' => $validated['phone'],
                    'is_active' => false,
                    'approval_status' => Restaurant::STATUS_PENDING,
                ]);

                $user->forceFill([
                    'name' => $validated['manager_name'],
                    'restaurant_id' => $restaurant->id,
                    'phone' => $validated['phone'],
                ])->save();

                if (! $user->hasRole('manager')) {
                    $user->assignRole('manager');
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['restaurant_name' => 'Could not complete registration. Please try again.']);
        }

        return redirect()
            ->route('manager.onboarding.waiting')
            ->with('status', 'Registration received! Your restaurant is awaiting approval.');
    }
}
