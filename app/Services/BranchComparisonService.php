<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BranchComparisonService
{
    public function __construct(
        private readonly ManagerDashboardAnalytics $analytics,
    ) {}

    /**
     * @return array{
     *     period_days: int,
     *     branches: Collection<int, array<string, mixed>>,
     *     highlights: array<string, mixed>,
     *     combined_daily: list<array{date: string, day: string, orders: int, revenue: float}>
     * }
     */
    public function comparisonForUser(User $user, int $days = 7): array
    {
        $days = max(1, min(30, $days));
        $ids = $user->accessibleRestaurantIds();
        $start = Carbon::today()->subDays($days - 1)->startOfDay();
        $end = Carbon::today()->endOfDay();
        $today = Carbon::today();

        $branches = Restaurant::query()
            ->whereIn('id', $ids)
            ->orderBy('branch_sort_order')
            ->get()
            ->map(function (Restaurant $restaurant) use ($start, $end, $today) {
                $ordersPeriod = Order::withoutGlobalScopes()
                    ->where('restaurant_id', $restaurant->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();

                $revenuePeriod = (float) Payment::query()
                    ->where('restaurant_id', $restaurant->id)
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount');

                $ordersToday = Order::withoutGlobalScopes()
                    ->where('restaurant_id', $restaurant->id)
                    ->whereDate('created_at', $today)
                    ->count();

                $revenueToday = $this->analytics->revenueForPaidOrdersOnDate($restaurant->id, $today);

                $avgRating = round((float) (Feedback::withoutGlobalScopes()
                    ->where('restaurant_id', $restaurant->id)
                    ->forService()
                    ->whereBetween('created_at', [$start, $end])
                    ->avg('rating') ?? 0), 1);

                $liveOrders = Order::withoutGlobalScopes()
                    ->where('restaurant_id', $restaurant->id)
                    ->whereIn('status', ['pending', 'preparing', 'served'])
                    ->count();

                return [
                    'id' => $restaurant->id,
                    'name' => $restaurant->displayName(),
                    'location' => $restaurant->location,
                    'orders_period' => $ordersPeriod,
                    'revenue_period' => $revenuePeriod,
                    'orders_today' => $ordersToday,
                    'revenue_today' => $revenueToday,
                    'avg_rating' => $avgRating,
                    'live_orders' => $liveOrders,
                ];
            });

        $maxOrders = max((int) $branches->max('orders_period'), 1);
        $maxRevenue = max((float) $branches->max('revenue_period'), 1.0);

        $branches = $branches->map(function (array $row) use ($maxOrders, $maxRevenue) {
            $row['orders_bar_pct'] = round(($row['orders_period'] / $maxOrders) * 100, 1);
            $row['revenue_bar_pct'] = round(($row['revenue_period'] / $maxRevenue) * 100, 1);

            return $row;
        });

        $topOrders = $branches->sortByDesc('orders_period')->first();
        $topRevenue = $branches->sortByDesc('revenue_period')->first();
        $needsAttention = $branches->sortByDesc('live_orders')->first();
        $lowestRating = $branches->filter(fn (array $row) => $row['avg_rating'] > 0)
            ->sortBy('avg_rating')
            ->first();

        return [
            'period_days' => $days,
            'branches' => $branches->values(),
            'highlights' => [
                'top_orders' => $topOrders,
                'top_revenue' => $topRevenue,
                'needs_attention' => ($needsAttention['live_orders'] ?? 0) > 0 ? $needsAttention : null,
                'lowest_rating' => $lowestRating,
            ],
            'combined_daily' => $this->combinedDailyTrend($ids, $days),
        ];
    }

    /**
     * @param  list<int>  $restaurantIds
     * @return list<array{branch: string, location: string|null, orders: int, revenue: float, avg_rating: float, live_orders: int}>
     */
    public function performanceRows(User $user, Carbon $start, Carbon $end): array
    {
        $ids = $user->accessibleRestaurantIds();

        return Restaurant::query()
            ->whereIn('id', $ids)
            ->orderBy('branch_sort_order')
            ->get()
            ->map(function (Restaurant $restaurant) use ($start, $end) {
                $orders = Order::withoutGlobalScopes()
                    ->where('restaurant_id', $restaurant->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count();

                $revenue = (float) Payment::query()
                    ->where('restaurant_id', $restaurant->id)
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount');

                $avgRating = round((float) (Feedback::withoutGlobalScopes()
                    ->where('restaurant_id', $restaurant->id)
                    ->forService()
                    ->whereBetween('created_at', [$start, $end])
                    ->avg('rating') ?? 0), 1);

                $liveOrders = Order::withoutGlobalScopes()
                    ->where('restaurant_id', $restaurant->id)
                    ->whereIn('status', ['pending', 'preparing', 'served'])
                    ->count();

                return [
                    'branch' => $restaurant->displayName(),
                    'location' => $restaurant->location,
                    'orders' => $orders,
                    'revenue' => $revenue,
                    'avg_rating' => $avgRating,
                    'live_orders' => $liveOrders,
                ];
            })
            ->all();
    }

    /**
     * @param  list<int>  $restaurantIds
     * @return list<array{date: string, day: string, orders: int, revenue: float}>
     */
    private function combinedDailyTrend(array $restaurantIds, int $days): array
    {
        $today = Carbon::today();
        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $start = $day->copy()->startOfDay();
            $end = $day->copy()->endOfDay();

            $orders = Order::withoutGlobalScopes()
                ->whereIn('restaurant_id', $restaurantIds)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $revenue = (float) Payment::query()
                ->whereIn('restaurant_id', $restaurantIds)
                ->whereIn('status', ['paid', 'completed'])
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount');

            $trend[] = [
                'date' => $day->format('M d'),
                'day' => $day->format('D'),
                'orders' => $orders,
                'revenue' => $revenue,
            ];
        }

        return $trend;
    }
}
