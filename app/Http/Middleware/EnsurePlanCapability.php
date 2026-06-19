<?php

namespace App\Http\Middleware;

use App\Models\SubscriptionPackage;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanCapability
{
    /**
     * Block access to a feature the restaurant's plan does not include.
     */
    public function handle(Request $request, Closure $next, string $capability): Response
    {
        $restaurant = $request->user()?->restaurant;

        if ($restaurant && ! $restaurant->planAllows($capability)) {
            $label = SubscriptionPackage::CAPABILITIES[$capability] ?? $capability;
            $message = $label.' is not included in your current plan. Upgrade to unlock it.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('manager.dashboard')->with('error', $message);
        }

        return $next($request);
    }
}
