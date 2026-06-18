<?php

namespace App\Http\Middleware;

use App\Models\Restaurant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantApproved
{
    /**
     * Gate the manager portal behind restaurant approval + plan selection.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->restaurant_id) {
            return $next($request);
        }

        $restaurant = $user->restaurant;

        if (! $restaurant) {
            return $next($request);
        }

        if ($restaurant->isPending() || $restaurant->isRejected()) {
            return redirect()->route('manager.onboarding.waiting');
        }

        if ($restaurant->needsPlanSelection()) {
            return redirect()->route('manager.onboarding.plan');
        }

        return $next($request);
    }
}
