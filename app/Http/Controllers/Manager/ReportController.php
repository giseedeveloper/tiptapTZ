<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\WaitTimeAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private WaitTimeAnalyticsService $waitTimes)
    {
    }

    public function performance(Request $request)
    {
        $restaurantId = auth()->user()->restaurant_id;

        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$start, $end] = $this->getDateRange($period, $startDate, $endDate);

        $totalOrders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $totalRevenue = Payment::where('restaurant_id', $restaurantId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        $ordersWithRating = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$start, $end])
            ->whereHas('feedback')
            ->with('feedback')
            ->get();

        $avgRating = $ordersWithRating->isNotEmpty()
            ? round($ordersWithRating->avg('feedback.rating'), 2)
            : 0;

        $speedByWaiter = collect($this->waitTimes->waiterSpeedMetrics($restaurantId, $start, $end))
            ->keyBy('waiter_id');

        $waiterStats = User::role('waiter')
            ->where('restaurant_id', $restaurantId)
            ->with(['orders' => function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            }, 'tips' => function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            }])
            ->get()
            ->map(function ($waiter) use ($speedByWaiter) {
                $orders = $waiter->orders;
                $tips = $waiter->tips;
                $speed = $speedByWaiter->get($waiter->id);

                $ordersWithFeedback = $orders->filter(function ($order) {
                    return $order->feedback !== null;
                });

                $avgRating = $ordersWithFeedback->isNotEmpty()
                    ? round($ordersWithFeedback->avg('feedback.rating'), 2)
                    : 0;

                return [
                    'id' => $waiter->id,
                    'name' => $waiter->name,
                    'orders_count' => $orders->count(),
                    'tips_earned' => $tips->sum('amount'),
                    'avg_rating' => $avgRating,
                    'avg_to_ready_minutes' => $speed['avg_to_ready_minutes'] ?? null,
                    'avg_to_served_minutes' => $speed['avg_to_served_minutes'] ?? null,
                    'sample_to_served' => $speed['sample_to_served'] ?? 0,
                ];
            })
            ->sortByDesc('orders_count')
            ->values();

        $topPerformer = $waiterStats->first();
        $waitTime = $this->waitTimes->summarize($restaurantId, $start, $end);
        $waitTrend = $this->waitTimes->dailyTrend($restaurantId, $start->copy()->startOfDay(), $end);

        return view('manager.reports.performance', compact(
            'totalOrders',
            'totalRevenue',
            'avgRating',
            'waiterStats',
            'topPerformer',
            'waitTime',
            'waitTrend',
            'period',
            'startDate',
            'endDate'
        ));
    }

    public function exportPerformance(Request $request)
    {
        $restaurantId = auth()->user()->restaurant_id;

        $period = $request->get('period', 'today');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        [$start, $end] = $this->getDateRange($period, $startDate, $endDate);

        $speedByWaiter = collect($this->waitTimes->waiterSpeedMetrics($restaurantId, $start, $end))
            ->keyBy('waiter_id');

        $waiterStats = User::role('waiter')
            ->where('restaurant_id', $restaurantId)
            ->with(['orders' => function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            }, 'tips' => function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end]);
            }])
            ->get()
            ->map(function ($waiter) use ($speedByWaiter) {
                $orders = $waiter->orders;
                $tips = $waiter->tips;
                $speed = $speedByWaiter->get($waiter->id);

                $ordersWithFeedback = $orders->filter(function ($order) {
                    return $order->feedback !== null;
                });

                $avgRating = $ordersWithFeedback->isNotEmpty()
                    ? round($ordersWithFeedback->avg('feedback.rating'), 2)
                    : 0;

                return [
                    'name' => $waiter->name,
                    'orders_count' => $orders->count(),
                    'tips_earned' => $tips->sum('amount'),
                    'avg_rating' => $avgRating,
                    'avg_to_ready_minutes' => $speed['avg_to_ready_minutes'] ?? null,
                    'avg_to_served_minutes' => $speed['avg_to_served_minutes'] ?? null,
                ];
            });

        $filename = 'performance_report_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($waiterStats) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Waiter Name',
                'Orders Handled',
                'Tips Earned ('.config('tiptap.currency_symbol').')',
                'Average Rating',
                'Avg Wait to Ready (min)',
                'Avg Customer Wait to Served (min)',
            ]);

            foreach ($waiterStats as $stat) {
                fputcsv($file, [
                    $stat['name'],
                    $stat['orders_count'],
                    number_format($stat['tips_earned'], 2),
                    $stat['avg_rating'],
                    $stat['avg_to_ready_minutes'] ?? '',
                    $stat['avg_to_served_minutes'] ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getDateRange($period, $startDate = null, $endDate = null)
    {
        switch ($period) {
            case 'today':
                return [Carbon::today(), Carbon::now()];
            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()];
            case 'month':
                return [Carbon::now()->startOfMonth(), Carbon::now()];
            case 'custom':
                return [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ];
            default:
                return [Carbon::today(), Carbon::now()];
        }
    }
}
