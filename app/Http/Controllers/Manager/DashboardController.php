<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\User;
use App\Services\BranchComparisonService;
use App\Services\BranchOverviewService;
use App\Services\ManagerDashboardAnalytics;
use App\Support\OrderWorkflow;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BranchOverviewService $branchOverview,
        private readonly BranchComparisonService $branchComparison,
    ) {}

    public function index()
    {
        $user = Auth::user();

        if ($user->isBranchManager() && ! session('active_branch_id') && $user->managesMultipleBranches()) {
            $overview = $this->branchOverview->forUser($user);
            $overview['comparison'] = $this->branchComparison->comparisonForUser($user, 7);

            return view('manager.dashboard-branches', $overview);
        }

        $restaurantId = $user->restaurant_id;
        $today = Carbon::today();

        $totalOrdersToday = Order::where('restaurant_id', $restaurantId)->whereDate('created_at', $today)->count();
        $revenueToday = Order::where('restaurant_id', $restaurantId)->whereDate('created_at', $today)->whereIn('status', OrderWorkflow::terminalStatuses())->sum('total_amount');
        $avgRating = Feedback::query()->forService()->whereHas('order', function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId);
        })->avg('rating') ?? 0;
        $waitersOnline = User::role('waiter')->where('restaurant_id', $restaurantId)->where('is_online', true)->count();

        $pendingOrders = Order::with('items.menuItem')->where('restaurant_id', $restaurantId)
            ->whereIn('status', array_merge(OrderWorkflow::storageVariants(OrderWorkflow::RECEIVED), OrderWorkflow::storageVariants(OrderWorkflow::ACCEPTED)))
            ->latest()->get();
        $preparingOrders = Order::with('items.menuItem')->where('restaurant_id', $restaurantId)
            ->whereIn('status', OrderWorkflow::storageVariants(OrderWorkflow::PREPARING))
            ->latest()->get();
        $servedOrders = Order::with('items.menuItem')->where('restaurant_id', $restaurantId)
            ->whereIn('status', OrderWorkflow::storageVariants(OrderWorkflow::SERVED))
            ->latest()->get();
        $paidOrders = Order::with('items.menuItem')->where('restaurant_id', $restaurantId)
            ->whereIn('status', OrderWorkflow::terminalStatuses())
            ->whereDate('created_at', $today)->latest()->take(10)->get();

        $recentFeedback = Feedback::query()->forService()->with('order')->whereHas('order', function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId);
        })->latest()->take(5)->get();

        $waiterTips = collect();
        $analytics = app(ManagerDashboardAnalytics::class)->forRestaurant($restaurantId);

        return view('manager.dashboard', compact(
            'totalOrdersToday',
            'revenueToday',
            'avgRating',
            'waitersOnline',
            'pendingOrders',
            'preparingOrders',
            'servedOrders',
            'paidOrders',
            'recentFeedback',
            'waiterTips',
            'analytics'
        ));
    }

    public function getStats()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $today = Carbon::today();

        $stats = [
            'total_orders_today' => Order::where('restaurant_id', $restaurantId)->whereDate('created_at', $today)->count(),
            'revenue_today' => Order::where('restaurant_id', $restaurantId)->whereDate('created_at', $today)->whereIn('status', OrderWorkflow::terminalStatuses())->sum('total_amount'),
            'avg_rating' => number_format(Feedback::query()->forService()->whereHas('order', function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            })->avg('rating') ?? 0, 1),
            'waiters_online' => User::role('waiter')->where('restaurant_id', $restaurantId)->where('is_online', true)->count(),
        ];

        return response()->json($stats);
    }

    public function getAnalytics(): JsonResponse
    {
        $restaurantId = Auth::user()->restaurant_id;
        $analytics = app(ManagerDashboardAnalytics::class)->forRestaurant($restaurantId);

        return response()->json([
            'weekly_trend' => $analytics['weekly_trend'],
            'hourly_activity' => $analytics['hourly_activity'],
            'week_comparison' => $analytics['week_comparison'],
            'insights' => $analytics['insights'],
            'top_items' => $analytics['top_menu_items'],
            'rating_histogram' => $analytics['rating_histogram'],
            'order_status_cycle' => $analytics['status_cycle'],
        ]);
    }
}
