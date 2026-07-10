<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Carbon\Carbon;

class BranchOverviewService
{
    public function __construct(
        private readonly ManagerDashboardAnalytics $analytics,
    ) {}

    /**
     * @return array{branches: \Illuminate\Support\Collection<int, array<string, mixed>>, totals: array<string, int|float>}
     */
    public function forUser(User $user): array
    {
        $ids = $user->accessibleRestaurantIds();
        $today = Carbon::today();

        $branches = Restaurant::query()
            ->whereIn("id", $ids)
            ->orderBy("branch_sort_order")
            ->get()
            ->map(function (Restaurant $restaurant) use ($today) {
                return [
                    "restaurant" => $restaurant,
                    "orders_today" => Order::withoutGlobalScopes()
                        ->where("restaurant_id", $restaurant->id)
                        ->whereDate("created_at", $today)
                        ->count(),
                    "revenue_today" => $this->analytics->revenueForPaidOrdersOnDate(
                        $restaurant->id,
                        $today,
                    ),
                    "waiters_online" => User::role("waiter")
                        ->where("restaurant_id", $restaurant->id)
                        ->where("is_online", true)
                        ->count(),
                    "avg_rating" => round(
                        (float) (Feedback::withoutGlobalScopes()
                            ->where("restaurant_id", $restaurant->id)
                            ->forService()
                            ->avg("rating") ?? 0),
                        1,
                    ),
                    "live_orders" => Order::withoutGlobalScopes()
                        ->where("restaurant_id", $restaurant->id)
                        ->whereIn("status", ["pending", "preparing", "served"])
                        ->count(),
                ];
            });

        $totals = [
            "orders_today" => (int) $branches->sum("orders_today"),
            "revenue_today" => (float) $branches->sum("revenue_today"),
            "waiters_online" => (int) $branches->sum("waiters_online"),
            "live_orders" => (int) $branches->sum("live_orders"),
            "branches" => $branches->count(),
        ];

        return compact("branches", "totals");
    }
}
