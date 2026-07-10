<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantBranchGroup;
use App\Models\User;
use App\Services\BranchComparisonService;
use App\Services\BranchOverviewService;
use App\Services\ManagerDashboardAnalytics;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct(
        private readonly BranchOverviewService $branchOverview,
        private readonly BranchComparisonService $branchComparison,
    ) {}

    public function index(): View
    {
        $this->ensureBranchManager();
        $overview = $this->branchOverview->forUser(Auth::user());
        $overview['comparison'] = $this->branchComparison->comparisonForUser(Auth::user(), 7);

        return view('manager.branches.index', $overview);
    }

    public function comparison(Request $request): JsonResponse
    {
        $this->ensureBranchManager();

        $days = (int) $request->get('days', 7);

        return response()->json(
            $this->branchComparison->comparisonForUser(Auth::user(), $days)
        );
    }

    public function export(Request $request)
    {
        $this->ensureBranchManager();

        $days = max(1, min(90, (int) $request->get('days', 7)));
        $end = Carbon::today()->endOfDay();
        $start = Carbon::today()->subDays($days - 1)->startOfDay();

        $rows = $this->branchComparison->performanceRows(Auth::user(), $start, $end);
        $filename = 'branch_performance_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows, $start, $end) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Period', $start->toDateString().' to '.$end->toDateString()]);
            fputcsv($file, []);
            fputcsv($file, ['Branch', 'Location', 'Orders', 'Revenue', 'Avg Rating', 'Live Orders']);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row['branch'],
                    $row['location'] ?? '',
                    $row['orders'],
                    number_format($row['revenue'], 2, '.', ''),
                    $row['avg_rating'],
                    $row['live_orders'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create(): View
    {
        $this->ensureBranchManager();

        $user = Auth::user();
        $groups = RestaurantBranchGroup::whereHas('branches', function ($q) use ($user) {
            $q->whereIn('id', $user->accessibleRestaurantIds());
        })->get();

        return view('manager.branches.create', compact('groups'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureBranchManager();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'group_id' => 'nullable|string',
            'group_name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $branchGroupId = $this->resolveBranchGroupId($request, $user);

        $branch = Restaurant::create([
            'name' => $data['name'],
            'branch_name' => $data['branch_name'] ?? $data['name'],
            'location' => $data['location'] ?? null,
            'phone' => $data['phone'] ?? null,
            'branch_group_id' => $branchGroupId,
            'approval_status' => Restaurant::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        $user->managedBranches()->syncWithoutDetaching([
            $branch->id => ['is_primary' => false],
        ]);

        return redirect()
            ->route('manager.branches.show', $branch)
            ->with('success', 'Branch "'.$branch->displayName().'" created successfully.');
    }

    public function show(Restaurant $branch): View
    {
        $this->ensureBranchManager();

        $user = Auth::user();
        abort_unless(in_array($branch->id, $user->accessibleRestaurantIds(), true), 403);

        $today = Carbon::today();
        $analytics = app(ManagerDashboardAnalytics::class)->forRestaurant($branch->id);

        $stats = [
            'orders_today' => Order::withoutGlobalScopes()
                ->where('restaurant_id', $branch->id)
                ->whereDate('created_at', $today)->count(),
            'revenue_today' => app(ManagerDashboardAnalytics::class)
                ->revenueForPaidOrdersOnDate($branch->id, $today),
            'waiters_online' => User::role('waiter')
                ->where('restaurant_id', $branch->id)
                ->where('is_online', true)->count(),
            'avg_rating' => round((float) (Feedback::withoutGlobalScopes()
                ->where('restaurant_id', $branch->id)
                ->forService()->avg('rating') ?? 0), 1),
        ];

        $supervisors = User::role('floor_supervisor')
            ->where('restaurant_id', $branch->id)
            ->with('zone')
            ->get();

        $waiters = User::role('waiter')
            ->where('restaurant_id', $branch->id)
            ->get();

        return view('manager.branches.show', compact('branch', 'stats', 'analytics', 'supervisors', 'waiters'));
    }

    public function edit(Restaurant $branch): View
    {
        $this->ensureBranchManager();

        $user = Auth::user();
        abort_unless(in_array($branch->id, $user->accessibleRestaurantIds(), true), 403);

        return view('manager.branches.edit', compact('branch'));
    }

    public function update(Request $request, Restaurant $branch): RedirectResponse
    {
        $this->ensureBranchManager();

        $user = Auth::user();
        abort_unless(in_array($branch->id, $user->accessibleRestaurantIds(), true), 403);

        $data = $request->validate([
            'branch_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'branch_sort_order' => 'integer|min:0',
        ]);

        $branch->update($data);

        return back()->with('success', 'Branch updated successfully.');
    }

    public function switchBranch(Request $request): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isBranchManager(), 403);

        $branchId = $request->input('branch_id');

        if ($branchId === null || $branchId === '' || $branchId === 'all') {
            session()->forget('active_branch_id');

            return redirect()->route('manager.dashboard');
        }

        $branchId = (int) $branchId;
        abort_unless(in_array($branchId, $user->accessibleRestaurantIds(), true), 403);

        session(['active_branch_id' => $branchId]);

        return redirect()
            ->route('manager.dashboard')
            ->with('success', 'Switched to '.Restaurant::find($branchId)?->displayName().'.');
    }

    private function resolveBranchGroupId(Request $request, User $user): ?int
    {
        $groupInput = $request->input('group_id');

        if ($groupInput && $groupInput !== '__new__') {
            return (int) $groupInput;
        }

        $primary = Restaurant::find($user->restaurant_id);
        if ($primary?->branch_group_id) {
            return (int) $primary->branch_group_id;
        }

        $groupName = $request->input('group_name', $primary?->name.' Group');
        $group = RestaurantBranchGroup::create(['name' => $groupName]);

        if ($primary && ! $primary->branch_group_id) {
            $primary->update(['branch_group_id' => $group->id]);
        }

        return $group->id;
    }

    private function ensureBranchManager(): void
    {
        abort_unless(Auth::user()?->isBranchManager(), 403);
    }
}
