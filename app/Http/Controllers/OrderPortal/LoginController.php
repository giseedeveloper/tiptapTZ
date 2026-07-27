<?php

namespace App\Http\Controllers\OrderPortal;

use App\Http\Controllers\Controller;
use App\Models\OrderPortalPassword;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('order-portal.login');
    }

    /**
     * Login with password only. Password is unique per waiter/restaurant;
     * system identifies which restaurant (and waiter) from the password.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'password' => 'required|string|max:50',
        ]);

        $plain = $request->password;
        $lookupHash = OrderPortalPassword::passwordLookupHash($plain);

        $credential = OrderPortalPassword::query()
            ->whereNull('revoked_at')
            ->where('lookup_hash', $lookupHash)
            ->with(['user', 'restaurant'])
            ->first();

        // Upgrade credentials created before indexed password lookup existed.
        if (! $credential) {
            $credential = OrderPortalPassword::query()
                ->whereNull('revoked_at')
                ->whereNull('lookup_hash')
                ->with(['user', 'restaurant'])
                ->get()
                ->first(fn (OrderPortalPassword $candidate) => $candidate->checkPassword($plain));

            if ($credential) {
                $credential->forceFill(['lookup_hash' => $lookupHash])->saveQuietly();
            }
        }

        if (! $credential) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password si sahihi au imekwisha tamaa. Omba mpya kwa manager wako.',
                ], 422);
            }

            return back()->with('error', 'Password si sahihi au imekwisha tamaa. Omba mpya kwa manager wako.');
        }

        $user = $credential->user;
        if (! $user->hasRole('waiter') || $user->restaurant_id != $credential->restaurant_id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Huna ufikiaji wa Order Portal. Wasiliana na manager wako.',
                ], 403);
            }

            return back()->with('error', 'Huna ufikiaji wa Order Portal. Wasiliana na manager wako.');
        }

        $request->session()->regenerate();
        $request->session()->put([
            'order_portal_restaurant_id' => $credential->restaurant_id,
            'order_portal_user_id' => $user->id,
            'order_portal_credential_id' => $credential->id,
            'order_portal_credential_version' => $credential->versionFingerprint(),
        ]);

        if ($request->expectsJson()) {
            $token = Str::random(64);
            Cache::put(OrderPortalPassword::tokenCacheKey($token), [
                'restaurant_id' => $credential->restaurant_id,
                'user_id' => $user->id,
                'credential_id' => $credential->id,
                'credential_version' => $credential->versionFingerprint(),
            ], now()->addHours(max(1, (int) config('order_portal.token_ttl_hours', 12))));

            return response()->json([
                'success' => true,
                'message' => 'Umefanikiwa kuingia.',
                'data' => [
                    'token' => $token,
                    'restaurant_id' => $credential->restaurant_id,
                    'restaurant_name' => $credential->restaurant?->name,
                    'user_id' => $user->id,
                    'user_name' => $user->name ?? null,
                ],
            ]);
        }

        return redirect()->route('order-portal.orders')->with('success', 'Umefanikiwa kuingia.');
    }

    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        $bearer = $request->bearerToken();
        if ($bearer) {
            Cache::forget(OrderPortalPassword::tokenCacheKey($bearer));
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Umetoka.',
            ]);
        }

        return redirect()->route('order-portal.login')->with('success', 'Umetoka.');
    }
}
