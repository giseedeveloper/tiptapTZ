<?php

namespace App\Http\Middleware;

use App\Models\OrderPortalPassword;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderPortalAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $restaurantId = session('order_portal_restaurant_id');
        $userId = session('order_portal_user_id');
        $credentialId = session('order_portal_credential_id');
        $credentialVersion = session('order_portal_credential_version');
        $bearer = $request->bearerToken();

        if (! $restaurantId || ! $userId || ! $credentialId || ! $credentialVersion) {
            if ($bearer) {
                $payload = Cache::get(OrderPortalPassword::tokenCacheKey($bearer));

                if (is_array($payload) && isset(
                    $payload['restaurant_id'],
                    $payload['user_id'],
                    $payload['credential_id'],
                    $payload['credential_version'],
                )) {
                    $restaurantId = $payload['restaurant_id'];
                    $userId = $payload['user_id'];
                    $credentialId = $payload['credential_id'];
                    $credentialVersion = $payload['credential_version'];

                    session([
                        'order_portal_restaurant_id' => $restaurantId,
                        'order_portal_user_id' => $userId,
                        'order_portal_credential_id' => $credentialId,
                        'order_portal_credential_version' => $credentialVersion,
                    ]);
                }
            }
        }

        $credential = $credentialId
            ? OrderPortalPassword::query()
                ->whereKey($credentialId)
                ->where('restaurant_id', $restaurantId)
                ->where('user_id', $userId)
                ->whereNull('revoked_at')
                ->with('user')
                ->first()
            : null;

        $user = $credential?->user;
        $valid = $credential
            && is_string($credentialVersion)
            && hash_equals($credential->versionFingerprint(), $credentialVersion)
            && $user
            && $user->hasRole('waiter')
            && (int) $user->restaurant_id === (int) $restaurantId;

        if (! $valid) {
            if ($bearer) {
                Cache::forget(OrderPortalPassword::tokenCacheKey($bearer));
            }

            $request->session()->forget([
                'order_portal_restaurant_id',
                'order_portal_user_id',
                'order_portal_credential_id',
                'order_portal_credential_version',
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Ingia kwanza kwenye TIPTAP ORDER.',
                    'error' => 'unauthenticated',
                ], 401);
            }

            return redirect()->route('order-portal.login')
                ->with('error', 'Ingia kwanza kwenye TIPTAP ORDER.');
        }

        return $next($request);
    }
}
