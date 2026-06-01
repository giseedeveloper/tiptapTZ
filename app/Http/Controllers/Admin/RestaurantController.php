<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRestaurantRequest;
use App\Models\AdminActivityLog;
use App\Models\Feedback;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function index(Request $request): View
    {
        $query = Restaurant::query()
            ->withCount(['users' => function ($q) {
                $q->role('manager');
            }])
            ->withCount(['users as waiters_count' => function ($q) {
                $q->role('waiter');
            }])
            ->latest();

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(function ($qry) use ($search) {
                $qry->where('name', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }
            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $restaurants = $query->paginate(10)->withQueryString();

        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function create(): View
    {
        return view('admin.restaurants.create');
    }

    public function store(StoreRestaurantRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $result = DB::transaction(function () use ($validated, $request) {
            $restaurant = Restaurant::query()->create([
                'name' => $validated['restaurant_name'],
                'location' => $validated['location'],
                'phone' => $validated['phone'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            $manager = User::query()->create([
                'name' => $validated['manager_name'],
                'email' => $validated['manager_email'],
                'password' => Hash::make($validated['manager_password']),
                'restaurant_id' => $restaurant->id,
            ]);

            $manager->assignRole('manager');

            return compact('restaurant', 'manager');
        });

        AdminActivityLog::log(
            'restaurant.created',
            Restaurant::class,
            (int) $result['restaurant']->id,
            null,
            [
                'name' => $result['restaurant']->name,
                'manager_id' => $result['manager']->id,
                'manager_email' => $result['manager']->email,
            ],
        );

        return redirect()
            ->route('admin.restaurants.show', $result['restaurant'])
            ->with('success', 'Restaurant and manager account created successfully.');
    }

    public function show(Request $request, string $id): View
    {
        $tab = $request->string('tab', 'overview')->toString();
        $allowedTabs = ['overview', 'orders', 'payments', 'menu', 'staff', 'feedback'];
        if (! in_array($tab, $allowedTabs, true)) {
            $tab = 'overview';
        }

        $restaurant = Restaurant::query()
            ->with(['users' => function ($query) {
                $query->role(['manager', 'waiter']);
            }])
            ->findOrFail($id);

        $managers = $restaurant->users->filter(fn (User $user) => $user->hasRole('manager'));
        $waiters = $restaurant->users->filter(fn (User $user) => $user->hasRole('waiter'));

        $overview = $this->buildOverviewStats($restaurant);
        $venueAnalytics = $this->buildVenueAnalytics($restaurant);
        $tabCounts = $this->buildTabCounts($restaurant);

        $recentOrders = collect();
        $recentPayments = collect();
        $menuItems = collect();
        $recentFeedback = collect();

        if ($tab === 'orders') {
            $recentOrders = Order::query()
                ->withoutGlobalScopes()
                ->with('waiter')
                ->where('restaurant_id', $restaurant->id)
                ->latest()
                ->limit(25)
                ->get();
        }

        if ($tab === 'payments') {
            $recentPayments = Payment::query()
                ->with('order')
                ->where(function ($query) use ($restaurant) {
                    $query->where('restaurant_id', $restaurant->id)
                        ->orWhereHas('order', fn ($q) => $q->withoutGlobalScopes()->where('restaurant_id', $restaurant->id));
                })
                ->latest()
                ->limit(25)
                ->get();
        }

        if ($tab === 'menu') {
            $menuItems = MenuItem::withoutGlobalScope(RestaurantScope::class)
                ->with('category')
                ->where('restaurant_id', $restaurant->id)
                ->orderBy('category_id')
                ->orderBy('name')
                ->get();
        }

        if ($tab === 'feedback') {
            $recentFeedback = Feedback::withoutGlobalScope(RestaurantScope::class)
                ->with(['waiter', 'order'])
                ->where('restaurant_id', $restaurant->id)
                ->latest()
                ->limit(25)
                ->get();
        }

        return view('admin.restaurants.show', compact(
            'restaurant',
            'managers',
            'waiters',
            'overview',
            'venueAnalytics',
            'tabCounts',
            'tab',
            'recentOrders',
            'recentPayments',
            'menuItems',
            'recentFeedback',
        ));
    }

    public function edit(string $id): View
    {
        $restaurant = Restaurant::query()->findOrFail($id);

        return view('admin.restaurants.edit', compact('restaurant'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $restaurant = Restaurant::query()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'selcom_vendor_id' => 'nullable|string|max:255',
            'selcom_api_key' => 'nullable|string|max:255',
            'selcom_api_secret' => 'nullable|string|max:255',
            'selcom_is_live' => 'nullable|boolean',
        ]);

        $validated['selcom_is_live'] = $request->has('selcom_is_live');

        $restaurant->update($validated);

        return redirect()
            ->route('admin.restaurants.show', $restaurant)
            ->with('success', 'Restaurant updated successfully.');
    }

    public function toggleStatus(string $id): RedirectResponse
    {
        $restaurant = Restaurant::query()->findOrFail($id);
        $oldActive = $restaurant->is_active;
        $restaurant->is_active = ! $restaurant->is_active;
        $restaurant->save();

        AdminActivityLog::log(
            'restaurant.toggle_status',
            'restaurant',
            (int) $restaurant->id,
            ['is_active' => $oldActive, 'name' => $restaurant->name],
            ['is_active' => $restaurant->is_active, 'name' => $restaurant->name],
            null
        );

        $status = $restaurant->is_active ? 'activated' : 'blocked';

        return back()->with('success', "Restaurant has been {$status}.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $restaurant = Restaurant::query()->findOrFail($id);
        $name = $restaurant->name;
        $restaurant->delete();

        return redirect()
            ->route('admin.restaurants.index')
            ->with('success', "Restaurant \"{$name}\" deleted successfully.");
    }

    /**
     * @return array{total_earnings: float, total_orders: int, avg_rating: float}
     */
    private function buildOverviewStats(Restaurant $restaurant): array
    {
        $totalEarnings = (float) Payment::query()
            ->where(function ($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id)
                    ->orWhereHas('order', fn ($orderQuery) => $orderQuery
                        ->withoutGlobalScopes()
                        ->where('restaurant_id', $restaurant->id));
            })
            ->whereIn('status', ['paid', 'completed'])
            ->sum('amount');

        $totalOrders = Order::query()
            ->withoutGlobalScopes()
            ->where('restaurant_id', $restaurant->id)
            ->count();

        $avgRating = Feedback::query()
            ->withoutGlobalScopes()
            ->where('restaurant_id', $restaurant->id)
            ->avg('rating');

        return [
            'total_earnings' => $totalEarnings,
            'total_orders' => $totalOrders,
            'avg_rating' => round((float) ($avgRating ?? 0), 1),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildVenueAnalytics(Restaurant $restaurant): array
    {
        $todayStart = now()->startOfDay();
        $start = now()->subDays(6)->startOfDay();

        $revenueRows = Payment::query()
            ->whereIn('status', ['paid', 'completed'])
            ->where(function ($query) use ($restaurant) {
                $query->where('restaurant_id', $restaurant->id)
                    ->orWhereHas('order', fn ($q) => $q->withoutGlobalScopes()->where('restaurant_id', $restaurant->id));
            })
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $revenueTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $revenueTrend[] = [
                'label' => $date->format('D'),
                'date' => $date->format('M j'),
                'revenue' => (float) ($revenueRows[$key] ?? 0),
            ];
        }

        $statusMeta = [
            'pending' => ['label' => 'Pending', 'color' => '#f59e0b'],
            'preparing' => ['label' => 'Preparing', 'color' => '#3b82f6'],
            'ready' => ['label' => 'Ready', 'color' => '#10b981'],
            'served' => ['label' => 'Served', 'color' => '#8b5cf6'],
            'paid' => ['label' => 'Paid', 'color' => '#06b6d4'],
            'completed' => ['label' => 'Completed', 'color' => '#22d3ee'],
            'cancelled' => ['label' => 'Cancelled', 'color' => '#f43f5e'],
        ];

        $statusCounts = Order::query()
            ->withoutGlobalScopes()
            ->where('restaurant_id', $restaurant->id)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $ordersByStatus = [];
        foreach ($statusMeta as $key => $meta) {
            $value = (int) ($statusCounts[$key] ?? 0);
            if ($value > 0) {
                $ordersByStatus[] = [
                    'label' => $meta['label'],
                    'value' => $value,
                    'color' => $meta['color'],
                ];
            }
        }

        return [
            'revenue_trend' => $revenueTrend,
            'orders_by_status' => $ordersByStatus,
            'revenue_today' => (float) Payment::query()
                ->whereIn('status', ['paid', 'completed'])
                ->where(function ($query) use ($restaurant) {
                    $query->where('restaurant_id', $restaurant->id)
                        ->orWhereHas('order', fn ($q) => $q->withoutGlobalScopes()
                            ->where('restaurant_id', $restaurant->id));
                })
                ->where('created_at', '>=', $todayStart)
                ->sum('amount'),
            'orders_today' => Order::query()
                ->withoutGlobalScopes()
                ->where('restaurant_id', $restaurant->id)
                ->where('created_at', '>=', $todayStart)
                ->count(),
            'active_orders' => Order::query()
                ->withoutGlobalScopes()
                ->where('restaurant_id', $restaurant->id)
                ->whereIn('status', ['pending', 'preparing', 'ready'])
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function buildTabCounts(Restaurant $restaurant): array
    {
        return [
            'orders' => Order::query()->withoutGlobalScopes()->where('restaurant_id', $restaurant->id)->count(),
            'payments' => Payment::query()
                ->where(function ($query) use ($restaurant) {
                    $query->where('restaurant_id', $restaurant->id)
                        ->orWhereHas('order', fn ($q) => $q->withoutGlobalScopes()->where('restaurant_id', $restaurant->id));
                })
                ->count(),
            'menu' => MenuItem::withoutGlobalScope(RestaurantScope::class)->where('restaurant_id', $restaurant->id)->count(),
            'feedback' => Feedback::withoutGlobalScope(RestaurantScope::class)->where('restaurant_id', $restaurant->id)->count(),
            'staff' => User::role(['manager', 'waiter'])->where('restaurant_id', $restaurant->id)->count(),
        ];
    }
}
