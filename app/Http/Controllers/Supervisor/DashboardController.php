<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Table;
use App\Models\TableZone;
use App\Models\User;
use App\Support\OrderWorkflow;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user         = Auth::user();
        $restaurantId = $user->restaurant_id;
        $zoneId       = $user->zone_id;

        // Zone info
        $zone = $zoneId
            ? TableZone::where('restaurant_id', $restaurantId)->find($zoneId)
            : null;

        // Tables in this zone (or all restaurant tables if no zone assigned)
        $tables = $zoneId
            ? Table::withoutGlobalScopes()
                   ->where('restaurant_id', $restaurantId)
                   ->where('zone_id', $zoneId)
                   ->get()
            : Table::withoutGlobalScopes()
                   ->where('restaurant_id', $restaurantId)
                   ->get();

        $tableNames = $tables->pluck('name')->toArray();

        // Live orders on zone's tables
        $liveOrders = Order::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('table_number', $tableNames)
            ->whereIn('status', OrderWorkflow::activeTableStatuses())
            ->with(['items.menuItem', 'waiter'])
            ->latest()
            ->get();

        // Waiters assigned to zone's tables
        $waiterIds = $tables->pluck('waiter_id')->filter()->unique();
        $waiters   = User::whereIn('id', $waiterIds)->get();

        // Today's stats
        $today   = Carbon::today();
        $allZoneOrders = Order::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->whereIn('table_number', $tableNames)
            ->whereDate('created_at', $today);

        $stats = [
            'live_orders'    => $liveOrders->count(),
            'orders_today'   => (clone $allZoneOrders)->count(),
            'revenue_today'  => (float) (clone $allZoneOrders)->whereIn('status', OrderWorkflow::terminalStatuses())->sum('total_amount'),
            'waiters_active' => $waiters->where('is_online', true)->count(),
            'avg_rating'     => round((float) (Feedback::withoutGlobalScopes()
                                    ->where('restaurant_id', $restaurantId)
                                    ->forService()
                                    ->whereDate('created_at', $today)
                                    ->avg('rating') ?? 0), 1),
        ];

        return view('supervisor.dashboard', compact('zone', 'tables', 'liveOrders', 'waiters', 'stats'));
    }

    public function getStats(): JsonResponse
    {
        $user         = Auth::user();
        $restaurantId = $user->restaurant_id;
        $zoneId       = $user->zone_id;
        $today        = Carbon::today();

        $tableNames = $zoneId
            ? Table::withoutGlobalScopes()
                   ->where('restaurant_id', $restaurantId)
                   ->where('zone_id', $zoneId)
                   ->pluck('name')->toArray()
            : Table::withoutGlobalScopes()
                   ->where('restaurant_id', $restaurantId)
                   ->pluck('name')->toArray();

        return response()->json([
            'live_orders'  => Order::withoutGlobalScopes()
                                ->where('restaurant_id', $restaurantId)
                                ->whereIn('table_number', $tableNames)
                                ->whereIn('status', OrderWorkflow::activeTableStatuses())
                                ->count(),
            'orders_today' => Order::withoutGlobalScopes()
                                ->where('restaurant_id', $restaurantId)
                                ->whereIn('table_number', $tableNames)
                                ->whereDate('created_at', $today)
                                ->count(),
        ]);
    }
}
