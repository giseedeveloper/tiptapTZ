<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For branch_manager users: overrides Auth::user()->restaurant_id in memory
 * (no DB write) to whichever branch is selected in session.
 *
 * This lets all existing controllers and the RestaurantScope work transparently
 * without any per-controller changes.
 */
class SetActiveBranch
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasRole('branch_manager')) {
            $activeBranchId = session('active_branch_id');

            if ($activeBranchId) {
                $accessible = $user->accessibleRestaurantIds();
                if (in_array((int) $activeBranchId, $accessible)) {
                    // In-memory override — never persisted to DB
                    $user->restaurant_id = (int) $activeBranchId;
                }
            }
            // null active_branch_id = "All Branches" mode;
            // restaurant_id stays as-is (primary branch) for scoped pages;
            // DashboardController handles the aggregate view specially.
        }

        return $next($request);
    }
}
