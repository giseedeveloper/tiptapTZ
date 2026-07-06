<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class WaiterRegistrationController extends Controller
{
    private const SESSION_KEY = 'waiter_registration.credentials';

    public function create(): View
    {
        return view('auth.register-waiter');
    }

    public function storeCredentials(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        $request->session()->put(self::SESSION_KEY, [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return redirect()->route('waiter.register.details');
    }

    public function createDetails(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has(self::SESSION_KEY)) {
            return redirect()->route('waiter.register');
        }

        return view('auth.register-waiter-details', [
            'waiterEmail' => $request->session()->get(self::SESSION_KEY.'.email'),
        ]);
    }

    public function storeDetails(Request $request): RedirectResponse
    {
        $credentials = $request->session()->get(self::SESSION_KEY);

        if (! is_array($credentials) || empty($credentials['email']) || empty($credentials['password'])) {
            return redirect()->route('waiter.register');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'location' => 'nullable|string|max:255',
        ]);

        if (User::where('email', $credentials['email'])->exists()) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()
                ->route('waiter.register')
                ->withErrors(['email' => 'This email is already registered. Please sign in instead.'])
                ->withInput(['email' => $credentials['email']]);
        }

        $user = User::create([
            'name' => trim($validated['first_name'].' '.$validated['last_name']),
            'email' => $credentials['email'],
            'auth_provider' => 'email',
            'password' => Hash::make($credentials['password']),
            'phone' => $validated['phone'],
            'location' => $validated['location'] ?? null,
            'restaurant_id' => null,
            'waiter_code' => null,
            'global_waiter_number' => User::generateGlobalWaiterNumber(),
        ]);

        $user->assignRole('waiter');

        $request->session()->forget(self::SESSION_KEY);

        Auth::login($user);

        return redirect()->route('waiter.dashboard')
            ->with('success', 'Your account has been created. Your unique number: '.$user->global_waiter_number.'. Ask a restaurant manager to link you.');
    }
}
