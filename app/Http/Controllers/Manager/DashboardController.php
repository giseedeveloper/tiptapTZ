<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\User;
use App\Services\ManagerDashboardAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $restaurantId = Auth::user()->restaurant_id;
        $today = Carbon::today();

        // Stats
        $totalOrdersToday = Order::where('restaurant_id', $restaurantId)->whereDate('created_at', $today)->count();
        $revenueToday = Order::where('restaurant_id', $restaurantId)->whereDate('created_at', $today)->where('status', 'paid')->sum('total_amount');
        $avgRating = Feedback::whereHas('order', function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId);
        })->avg('rating') ?? 0;
        $waitersOnline = User::role('waiter')->where('restaurant_id', $restaurantId)->where('is_online', true)->count();

        // Live Orders
        $pendingOrders = Order::with('items.menuItem')->where('restaurant_id', $restaurantId)->where('status', 'pending')->latest()->get();
        $preparingOrders = Order::with('items.menuItem')->where('restaurant_id', $restaurantId)->where('status', 'preparing')->latest()->get();
        $servedOrders = Order::with('items.menuItem')->where('restaurant_id', $restaurantId)->where('status', 'served')->latest()->get();
        $paidOrders = Order::with('items.menuItem')->where('restaurant_id', $restaurantId)->where('status', 'paid')->whereDate('created_at', $today)->latest()->take(10)->get();

        // Feedback
        $recentFeedback = Feedback::with('order')->whereHas('order', function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId);
        })->latest()->take(5)->get();

        // Tips: not shown to manager (policy: don't show tips to manager)
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
            'revenue_today' => Order::where('restaurant_id', $restaurantId)->whereDate('created_at', $today)->where('status', 'paid')->sum('total_amount'),
            'avg_rating' => number_format(Feedback::whereHas('order', function ($q) use ($restaurantId) {
                $q->where('restaurant_id', $restaurantId);
            })->avg('rating') ?? 0, 1),
            'waiters_online' => User::role('waiter')->where('restaurant_id', $restaurantId)->where('is_online', true)->count(),
        ];

        return response()->json($stats);
    }
}
