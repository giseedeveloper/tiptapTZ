<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\CustomerRequest;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use App\Models\Tip;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = $this->buildStats();
        $analytics = $this->buildAnalytics();

        $recent_restaurants = Restaurant::query()->latest()->take(5)->get();
        $recent_activities = Activity::query()->with('user')->latest()->take(8)->get();

        return view('admin.dashboard', compact(
            'stats',
            'analytics',
            'recent_restaurants',
            'recent_activities',
        ));
    }

    public function getStats(): JsonResponse
    {
        return response()->json($this->buildStats());
    }

    public function getAnalytics(): JsonResponse
    {
        return response()->json([
            'analytics' => $this->buildAnalytics(),
            'stats' => $this->buildStats(),
            'currency_symbol' => \App\Support\Money::symbol(),
        ]);
    }

    /**
     * @return array<string, int|float>
     */
    private function buildStats(): array
    {
        $todayStart = now()->startOfDay();

        return [
            'total_restaurants' => Restaurant::query()->count(),
            'active_restaurants' => Restaurant::query()->where('is_active', true)->count(),
            'total_waiters' => User::role('waiter')->count(),
            'total_managers' => User::role('manager')->count(),
            'active_orders' => Order::query()
                ->whereIn('status', ['pending', 'preparing', 'ready'])
                ->count(),
            'orders_today' => Order::query()->where('created_at', '>=', $todayStart)->count(),
            'total_revenue' => (float) Payment::query()
                ->whereIn('status', ['paid', 'completed'])
                ->sum('amount'),
            'revenue_today' => (float) Payment::query()
                ->whereIn('status', ['paid', 'completed'])
                ->where('created_at', '>=', $todayStart)
                ->sum('amount'),
            'pending_withdrawals' => Withdrawal::query()
                ->where('status', 'pending')
                ->count(),
            'pending_customer_requests' => CustomerRequest::withoutGlobalScope(RestaurantScope::class)
                ->where('status', 'pending')
                ->count(),
            'total_tips' => (float) Tip::withoutGlobalScope(RestaurantScope::class)->sum('amount'),
            'avg_feedback_rating' => round((float) Feedback::withoutGlobalScope(RestaurantScope::class)->avg('rating'), 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAnalytics(): array
    {
        return [
            'revenue_trend' => $this->dailyRevenueTrend(7),
            'orders_trend' => $this->dailyOrdersTrend(7),
            'orders_by_status' => $this->ordersByStatus(),
            'restaurant_split' => $this->restaurantSplit(),
            'payment_methods' => $this->paymentMethods(),
            'rating_distribution' => $this->ratingDistribution(),
            'top_restaurants' => $this->topRestaurantsByRevenue(5),
            'week_comparison' => $this->weekComparison(),
        ];
    }

    /**
     * @return array<int, array{label: string, date: string, revenue: float}>
     */
    private function dailyRevenueTrend(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $rows = Payment::query()
            ->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillDailySeries($days, $rows, 'revenue');
    }

    /**
     * @return array<int, array{label: string, date: string, count: int}>
     */
    private function dailyOrdersTrend(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $rows = Order::query()
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillDailySeries($days, $rows, 'count');
    }

    /**
     * @param  Collection<string, float|int>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function fillDailySeries(int $days, Collection $rows, string $valueKey): array
    {
        $series = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $series[] = [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                $valueKey => $valueKey === 'revenue'
                    ? (float) ($rows[$key] ?? 0)
                    : (int) ($rows[$key] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * @return array<int, array{label: string, value: int, color: string}>
     */
    private function ordersByStatus(): array
    {
        $statuses = [
            'pending' => ['label' => 'Pending', 'color' => '#f59e0b'],
            'preparing' => ['label' => 'Preparing', 'color' => '#3b82f6'],
            'ready' => ['label' => 'Ready', 'color' => '#10b981'],
            'served' => ['label' => 'Served', 'color' => '#8C71F6'],
            'paid' => ['label' => 'Paid', 'color' => '#06b6d4'],
            'completed' => ['label' => 'Completed', 'color' => '#22d3ee'],
            'cancelled' => ['label' => 'Cancelled', 'color' => '#f43f5e'],
        ];

        $counts = Order::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $result = [];
        foreach ($statuses as $key => $meta) {
            $value = (int) ($counts[$key] ?? 0);
            if ($value > 0) {
                $result[] = [
                    'label' => $meta['label'],
                    'value' => $value,
                    'color' => $meta['color'],
                ];
            }
        }

        return $result;
    }

    /**
     * @return array{active: int, inactive: int, colors: array<string, string>}
     */
    private function restaurantSplit(): array
    {
        $active = Restaurant::query()->where('is_active', true)->count();
        $inactive = Restaurant::query()->where('is_active', false)->count();

        return [
            'active' => $active,
            'inactive' => $inactive,
            'segments' => [
                ['label' => 'Active', 'value' => $active, 'color' => '#10b981'],
                ['label' => 'Inactive', 'value' => $inactive, 'color' => '#f43f5e'],
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, value: int, color: string}>
     */
    private function paymentMethods(): array
    {
        $colors = ['#8C71F6', '#6D52E8', '#10b981', '#f59e0b', '#06b6d4', '#ec4899'];
        $methods = Payment::query()
            ->whereIn('status', ['paid', 'completed'])
            ->select('method', DB::raw('COUNT(*) as total'))
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        return $methods->values()->map(function ($row, $index) use ($colors) {
            return [
                'label' => ucfirst((string) $row->method) ?: 'Unknown',
                'value' => (int) $row->total,
                'color' => $colors[$index % count($colors)],
            ];
        })->all();
    }

    /**
     * @return array<int, array{stars: int, count: int}>
     */
    private function ratingDistribution(): array
    {
        $counts = Feedback::withoutGlobalScope(RestaurantScope::class)
            ->select('rating', DB::raw('COUNT(*) as total'))
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $result = [];
        for ($stars = 5; $stars >= 1; $stars--) {
            $result[] = [
                'stars' => $stars,
                'count' => (int) ($counts[$stars] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array{id: int, name: string, revenue: float, orders: int}>
     */
    private function topRestaurantsByRevenue(int $limit): array
    {
        return Restaurant::query()
            ->select('restaurants.id', 'restaurants.name')
            ->selectSub(
                Payment::query()
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereColumn('payments.restaurant_id', 'restaurants.id')
                    ->selectRaw('COALESCE(SUM(amount), 0)'),
                'revenue'
            )
            ->selectSub(
                Order::query()
                    ->withoutGlobalScopes()
                    ->whereColumn('orders.restaurant_id', 'restaurants.id')
                    ->selectRaw('COUNT(*)'),
                'orders'
            )
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'revenue' => (float) $r->revenue,
                'orders' => (int) $r->orders,
                'url' => route('admin.restaurants.show', $r->id),
            ])
            ->all();
    }

    /**
     * @return array{revenue_change: float, orders_change: float, revenue_this_week: float, revenue_last_week: float}
     */
    private function weekComparison(): array
    {
        $thisWeekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        $revenueThisWeek = (float) Payment::query()
            ->whereIn('status', ['paid', 'completed'])
            ->where('created_at', '>=', $thisWeekStart)
            ->sum('amount');

        $revenueLastWeek = (float) Payment::query()
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->sum('amount');

        $ordersThisWeek = Order::query()->where('created_at', '>=', $thisWeekStart)->count();
        $ordersLastWeek = Order::query()->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])->count();

        return [
            'revenue_this_week' => $revenueThisWeek,
            'revenue_last_week' => $revenueLastWeek,
            'revenue_change' => $this->percentChange($revenueLastWeek, $revenueThisWeek),
            'orders_change' => $this->percentChange($ordersLastWeek, $ordersThisWeek),
        ];
    }

    private function percentChange(float $previous, float $current): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
