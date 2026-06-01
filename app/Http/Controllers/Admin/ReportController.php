<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use App\Models\Tip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->string('period', 'month')->toString();
        $startDate = $request->string('start_date')->toString() ?: null;
        $endDate = $request->string('end_date')->toString() ?: null;
        $restaurantId = $request->integer('restaurant_id') ?: null;

        [$start, $end] = $this->dateRange($period, $startDate, $endDate);

        $orderQuery = Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId));

        $paymentQuery = Payment::query()
            ->whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$start, $end])
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId));

        $totalOrders = (clone $orderQuery)->count();
        $totalRevenue = (float) (clone $paymentQuery)->sum('amount');
        $avgOrderValue = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 0) : 0;

        $tipsTotal = (float) Tip::withoutGlobalScope(RestaurantScope::class)
            ->whereBetween('created_at', [$start, $end])
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->sum('amount');

        $feedbackAvg = (float) Feedback::withoutGlobalScope(RestaurantScope::class)
            ->whereBetween('created_at', [$start, $end])
            ->when($restaurantId, fn ($q) => $q->where('restaurant_id', $restaurantId))
            ->avg('rating');

        $restaurantBreakdown = Restaurant::query()
            ->withCount([
                'orders as orders_count' => fn ($q) => $q->whereBetween('created_at', [$start, $end]),
            ])
            ->get()
            ->map(function (Restaurant $restaurant) use ($start, $end) {
                $revenue = Payment::query()
                    ->where('restaurant_id', $restaurant->id)
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereBetween('created_at', [$start, $end])
                    ->sum('amount');

                return [
                    'id' => $restaurant->id,
                    'name' => $restaurant->name,
                    'orders_count' => $restaurant->orders_count,
                    'revenue' => (float) $revenue,
                ];
            })
            ->when($restaurantId, fn ($c) => $c->where('id', $restaurantId))
            ->sortByDesc('revenue')
            ->values();

        $restaurants = Restaurant::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.reports.index', compact(
            'period',
            'startDate',
            'endDate',
            'restaurantId',
            'totalOrders',
            'totalRevenue',
            'avgOrderValue',
            'tipsTotal',
            'feedbackAvg',
            'restaurantBreakdown',
            'restaurants',
            'start',
            'end',
        ));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dateRange(string $period, ?string $startDate, ?string $endDate): array
    {
        if ($period === 'custom' && $startDate && $endDate) {
            return [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ];
        }

        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
