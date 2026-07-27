<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Order;
use Illuminate\Http\Request;

trait AuthorizesRestaurantAccess
{
    protected function authorizeRestaurantAccess(Request $request, int $restaurantId): void
    {
        $user = $request->user();

        abort_unless(
            $user && (
                $user->hasRole('super_admin')
                || in_array($restaurantId, $user->accessibleRestaurantIds(), true)
            ),
            403,
        );
    }

    protected function authorizeOrderAccess(Request $request, Order $order): void
    {
        $this->authorizeRestaurantAccess($request, (int) $order->restaurant_id);
    }
}
