<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Services\OrderWorkflowService;
use App\Support\OrderWorkflow;
use Carbon\Carbon;

class ManagerDashboardAnalytics
{
    public function __construct(
        private OrderWorkflowService $workflow,
        private WaitTimeAnalyticsService $waitTimes,
    ) {
    }

    public function revenueForPaidOrdersOnDate(int $restaurantId, Carbon $date): float
    {
        return (float) Payment::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereDate('created_at', $date)
            ->sum('amount');
    }

    /**
     * @return array<string, mixed>
     */
    public function forRestaurant(int $restaurantId): array
    {
        $today = Carbon::today();
        $restaurant = Restaurant::query()->find($restaurantId);

        $weeklyTrend = $this->weeklyTrend($restaurantId, $today);
        $hourlyActivity = $this->hourlyActivity($restaurantId, $today);
        $statusCycle = $this->statusCycle($restaurantId, $today);
        $weekComparison = $this->weekComparison($restaurantId, $today);
        $topMenuItems = $this->topMenuItems($restaurantId, $today->copy()->subDays(6), $today->copy()->endOfDay());
        $ratingHistogram = $this->ratingHistogram($restaurantId);
        $waitFrom = $today->copy()->subDays(6)->startOfDay();
        $waitTo = $today->copy()->endOfDay();
        $waitTime = $this->waitTimes->summarize($restaurantId, $waitFrom, $waitTo);
        $waitTrend = $this->waitTimes->dailyTrend($restaurantId, $waitFrom, $waitTo);
        $waiterSpeed = $this->waitTimes->waiterSpeedMetrics($restaurantId, $waitFrom, $waitTo);

        return [
            'restaurant_name' => $restaurant?->name ?? 'Your Restaurant',
            'weekly_trend' => $weeklyTrend,
            'hourly_activity' => $hourlyActivity,
            'status_cycle' => $statusCycle,
            'week_comparison' => $weekComparison,
            'top_menu_items' => $topMenuItems,
            'rating_histogram' => $ratingHistogram,
            'wait_time' => $waitTime,
            'wait_trend' => $waitTrend,
            'waiter_speed' => array_slice($waiterSpeed, 0, 5),
            'insights' => $this->buildInsights($weeklyTrend, $hourlyActivity, $statusCycle, $weekComparison, $waitTime),
        ];
    }

    /**
     * @return list<array{date: string, day: string, revenue: float, orders: int}>
     */
    private function weeklyTrend(int $restaurantId, Carbon $today): array
    {
        $start = $today->copy()->subDays(6)->startOfDay();
        $end = $today->copy()->endOfDay();

        $revenueByDay = [];
        $payments = Payment::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$start, $end])
            ->get(['amount', 'created_at']);

        foreach ($payments as $payment) {
            $key = $payment->created_at->toDateString();
            $revenueByDay[$key] = ($revenueByDay[$key] ?? 0) + (float) $payment->amount;
        }

        $ordersByDay = [];
        $orders = Order::query()
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$start, $end])
            ->get(['created_at']);

        foreach ($orders as $order) {
            $key = $order->created_at->toDateString();
            $ordersByDay[$key] = ($ordersByDay[$key] ?? 0) + 1;
        }

        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $key = $day->toDateString();

            $trend[] = [
                'date' => $day->format('M d'),
                'day' => $day->format('D'),
                'revenue' => (float) ($revenueByDay[$key] ?? 0),
                'orders' => (int) ($ordersByDay[$key] ?? 0),
            ];
        }

        return $trend;
    }

    /**
     * @return list<array{hour: string, label: string, orders: int}>
     */
    private function hourlyActivity(int $restaurantId, Carbon $today): array
    {
        $counts = [];
        $orders = Order::query()
            ->where('restaurant_id', $restaurantId)
            ->whereDate('created_at', $today)
            ->get(['created_at']);

        foreach ($orders as $order) {
            $hour = (int) $order->created_at->format('G');
            $counts[$hour] = ($counts[$hour] ?? 0) + 1;
        }

        $hours = [];
        for ($h = 0; $h <= 23; $h++) {
            $hours[] = [
                'hour' => (string) $h,
                'label' => sprintf('%02d:00', $h),
                'orders' => (int) ($counts[$h] ?? 0),
            ];
        }

        return $hours;
    }

    /**
     * Delegates to OrderWorkflowService::dashboardMetrics for the canonical
     * received→accepted→preparing→ready→served→completed pipeline, including
     * per-stage average times and bottleneck detection.
     *
     * @return array{
     *     segments: list<array{key: string, label: string, count: int, color: string, avg_minutes: float|null}>,
     *     total: int,
     *     stage_times: list<array{key: string, label: string, avg_minutes: float, sample_size: int, threshold_minutes: int, is_bottleneck: bool}>,
     *     bottlenecks: list<array{key: string, label: string, avg_minutes: float, threshold_minutes: int, severity: string}>,
     *     avg_total_minutes: float|null
     * }
     */
    private function statusCycle(int $restaurantId, Carbon $today): array
    {
        return $this->workflow->dashboardMetrics($restaurantId, $today);
    }

    /**
     * @return array{current: float, previous: float, change_pct: float, current_orders: int, previous_orders: int}
     */
    private function weekComparison(int $restaurantId, Carbon $today): array
    {
        $currentStart = $today->copy()->startOfWeek();
        $currentEnd = $today->copy()->endOfDay();
        $previousStart = $currentStart->copy()->subWeek();
        $previousEnd = $currentStart->copy()->subSecond();

        $currentRevenue = (float) Payment::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->sum('amount');

        $previousRevenue = (float) Payment::query()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('amount');

        $currentOrders = Order::query()
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->count();

        $previousOrders = Order::query()
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->count();

        $changePct = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : ($currentRevenue > 0 ? 100.0 : 0.0);

        return [
            'current' => $currentRevenue,
            'previous' => $previousRevenue,
            'change_pct' => $changePct,
            'current_orders' => $currentOrders,
            'previous_orders' => $previousOrders,
        ];
    }

    /**
     * @return list<array{name: string, quantity: int, revenue: float}>
     */
    private function topMenuItems(int $restaurantId, Carbon $start, Carbon $end): array
    {
        $rows = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->where('orders.restaurant_id', $restaurantId)
            ->whereBetween('orders.created_at', [$start, $end])
            ->selectRaw('COALESCE(menu_items.name, order_items.name) as item_name')
            ->selectRaw('SUM(order_items.quantity) as qty')
            ->selectRaw('SUM(order_items.total) as revenue')
            ->groupBy('item_name')
            ->orderByDesc('qty')
            ->limit(6)
            ->get();

        return $rows->map(fn ($row) => [
            'name' => (string) $row->item_name,
            'quantity' => (int) $row->qty,
            'revenue' => (float) $row->revenue,
        ])->values()->all();
    }

    /**
     * @return list<array{stars: int, count: int}>
     */
    private function ratingHistogram(int $restaurantId): array
    {
        $counts = Feedback::query()
            ->forService()
            ->where('restaurant_id', $restaurantId)
            ->get(['rating'])
            ->countBy('rating');

        $histogram = [];
        for ($stars = 5; $stars >= 1; $stars--) {
            $histogram[] = [
                'stars' => $stars,
                'count' => (int) ($counts[$stars] ?? 0),
            ];
        }

        return $histogram;
    }

    /**
     * @param  list<array{date: string, day: string, revenue: float, orders: int}>  $weeklyTrend
     * @param  list<array{hour: string, label: string, orders: int}>  $hourlyActivity
     * @param  array{segments: list<array{key: string, label: string, count: int, color: string, avg_minutes: float|null}>, total: int, stage_times: array, bottlenecks: array, avg_total_minutes: float|null}  $statusCycle
     * @param  array{current: float, previous: float, change_pct: float, current_orders: int, previous_orders: int}  $weekComparison
     * @param  array{avg_to_served_minutes: float|null, avg_to_ready_minutes: float|null, sample_to_served: int}|null  $waitTime
     * @return list<array{label: string, value: string, tone: string}>
     */
    private function buildInsights(
        array $weeklyTrend,
        array $hourlyActivity,
        array $statusCycle,
        array $weekComparison,
        ?array $waitTime = null,
    ): array {
        $insights = [];

        $peakHour = collect($hourlyActivity)->sortByDesc('orders')->first();
        if ($peakHour && $peakHour['orders'] > 0) {
            $insights[] = [
                'label' => 'Peak hour today',
                'value' => $peakHour['label'].' · '.$peakHour['orders'].' orders',
                'tone' => 'cyan',
            ];
        }

        $bestDay = collect($weeklyTrend)->sortByDesc('revenue')->first();
        if ($bestDay && $bestDay['revenue'] > 0) {
            $insights[] = [
                'label' => 'Best revenue day (7d)',
                'value' => $bestDay['day'].' · Tsh '.number_format($bestDay['revenue']),
                'tone' => 'violet',
            ];
        }

        if ($weekComparison['change_pct'] !== 0.0) {
            $insights[] = [
                'label' => 'Week vs last week',
                'value' => ($weekComparison['change_pct'] >= 0 ? '+' : '').$weekComparison['change_pct'].'% revenue',
                'tone' => $weekComparison['change_pct'] >= 0 ? 'emerald' : 'rose',
            ];
        }

        $activePipeline = collect($statusCycle['segments'])
            ->whereIn('key', OrderWorkflow::liveStatuses())
            ->sum('count');

        if ($activePipeline > 0) {
            $insights[] = [
                'label' => 'Live pipeline',
                'value' => $activePipeline.' orders in progress',
                'tone' => 'amber',
            ];
        }

        if (($waitTime['avg_to_served_minutes'] ?? null) !== null) {
            $avgWait = (float) $waitTime['avg_to_served_minutes'];
            $insights[] = [
                'label' => 'Avg customer wait',
                'value' => $avgWait.'m to served · n='.((int) ($waitTime['sample_to_served'] ?? 0)),
                'tone' => $avgWait > 30 ? 'rose' : ($avgWait > 20 ? 'amber' : 'emerald'),
            ];
        } else {
            $bottleneck = collect($statusCycle['bottlenecks'] ?? [])->first();
            if ($bottleneck) {
                $insights[] = [
                    'label' => 'Bottleneck stage',
                    'value' => $bottleneck['label'].' · '.$bottleneck['avg_minutes'].'m avg',
                    'tone' => $bottleneck['severity'] === 'critical' ? 'rose' : 'amber',
                ];
            }
        }

        return array_slice($insights, 0, 5);
    }
}
