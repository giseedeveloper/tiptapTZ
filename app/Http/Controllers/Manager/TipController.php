<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\TipPool;
use App\Models\TipPoolMember;
use App\Models\User;
use App\Services\TipPoolService;
use App\Services\TipReportService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TipController extends Controller
{
    public function __construct(
        private TipPoolService $pools,
        private TipReportService $reports,
    ) {
    }

    public function index(): View
    {
        $restaurantId = (int) Auth::user()->restaurant_id;
        $pool = $this->pools->ensureKitchenPool($restaurantId);
        $pool->load(['members.user', 'contributions' => fn ($q) => $q->latest()->limit(15)]);

        $availableStaff = User::role(['waiter', 'barista'])
            ->activeAtRestaurant($restaurantId)
            ->orderBy('name')
            ->get(['id', 'name', 'global_waiter_number']);

        $memberUserIds = $pool->members->pluck('user_id')->all();
        $availableStaff = $availableStaff->reject(fn (User $u) => in_array($u->id, $memberUserIds, true))->values();

        $recentContributions = $pool->contributions()->with(['allocations.user'])->latest()->limit(15)->get();

        return view('manager.tips.index', [
            'pool' => $pool,
            'availableStaff' => $availableStaff,
            'recentContributions' => $recentContributions,
            'methods' => TipPool::distributionMethods(),
            'tipSettings' => Auth::user()->restaurant->tipSettings(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_waiter' => 'nullable|boolean',
            'category_barista' => 'nullable|boolean',
            'category_kitchen' => 'nullable|boolean',
            'suggestion_mode' => 'required|in:percent,fixed',
            'percentages' => 'nullable|string|max:100',
            'fixed_amounts' => 'nullable|string|max:200',
            'value_visible' => 'nullable|boolean',
        ]);

        $restaurant = Auth::user()->restaurant;

        $percentages = $this->parseNumberList($validated['percentages'] ?? '', 1, 100);
        $fixed = $this->parseNumberList($validated['fixed_amounts'] ?? '', 1, 10_000_000);

        $settings = [
            'categories' => [
                'waiter' => $request->boolean('category_waiter'),
                'barista' => $request->boolean('category_barista'),
                'kitchen' => $request->boolean('category_kitchen'),
            ],
            'suggestion_mode' => $validated['suggestion_mode'],
            'percentages' => $percentages !== [] ? $percentages : Restaurant::TIP_SETTINGS_DEFAULTS['percentages'],
            'fixed_amounts' => $fixed !== [] ? $fixed : Restaurant::TIP_SETTINGS_DEFAULTS['fixed_amounts'],
            'value_visible' => $request->boolean('value_visible'),
        ];

        $restaurant->update(['tip_settings' => $settings]);

        return back()->with('success', 'Tip settings saved.');
    }

    public function reports(Request $request): View
    {
        [$from, $to, $period] = $this->range($request);
        $shift = (string) $request->get('shift', 'all');
        $user = Auth::user();

        $restaurantIds = $this->reportRestaurantIds($request, $user);
        $report = $this->reports->build($restaurantIds, $from, $to, $shift);

        return view('manager.tips.reports', [
            'report' => $report,
            'period' => $period,
            'shift' => $report['shift'],
            'startDate' => $from->toDateString(),
            'endDate' => $to->toDateString(),
            'valueVisible' => $user->restaurant->tipsValueVisible(),
            'branches' => $user->managesMultipleBranches()
                ? Restaurant::whereIn('id', $user->accessibleRestaurantIds())->get(['id', 'name'])
                : collect(),
            'selectedBranch' => $request->get('branch_id', 'all'),
        ]);
    }

    public function exportReports(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);
        $shift = (string) $request->get('shift', 'all');
        $user = Auth::user();
        abort_unless($user->restaurant->tipsValueVisible(), 403, 'Tip values are hidden for this restaurant.');

        $report = $this->reports->build($this->reportRestaurantIds($request, $user), $from, $to, $shift);

        $filename = 'tips_report_'.$from->toDateString().'_'.$to->toDateString().'.csv';
        $symbol = (string) config('tiptap.currency_symbol', 'R');

        return response()->stream(function () use ($report, $symbol) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Category', 'Staff', 'Tips Count', 'Total ('.$symbol.')']);

            foreach (['waiters' => 'Waiter', 'baristas' => 'Barista', 'kitchen' => 'Kitchen'] as $key => $label) {
                foreach ($report[$key] as $row) {
                    fputcsv($file, [$label, $row['name'], $row['count'], number_format($row['total'], 2, '.', '')]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['Summary', '', '', '']);
            fputcsv($file, ['Waiter total', '', '', number_format($report['totals']['waiter'], 2, '.', '')]);
            fputcsv($file, ['Barista total', '', '', number_format($report['totals']['barista'], 2, '.', '')]);
            fputcsv($file, ['Kitchen total', '', '', number_format($report['totals']['kitchen'], 2, '.', '')]);
            fputcsv($file, ['Grand total', '', $report['totals']['count'], number_format($report['totals']['grand'], 2, '.', '')]);

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return list<int>
     */
    private function reportRestaurantIds(Request $request, User $user): array
    {
        $accessible = $user->accessibleRestaurantIds();
        $branch = $request->get('branch_id', 'all');

        if ($branch !== 'all' && is_numeric($branch) && in_array((int) $branch, $accessible, true)) {
            return [(int) $branch];
        }

        return $accessible !== [] ? $accessible : [(int) $user->restaurant_id];
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function range(Request $request): array
    {
        $period = (string) $request->get('period', 'week');

        return match ($period) {
            'today' => [Carbon::today(), Carbon::now(), 'today'],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now(), 'month'],
            'custom' => [
                Carbon::parse($request->get('start_date', Carbon::today()->toDateString()))->startOfDay(),
                Carbon::parse($request->get('end_date', Carbon::today()->toDateString()))->endOfDay(),
                'custom',
            ],
            default => [Carbon::now()->startOfWeek(), Carbon::now(), 'week'],
        };
    }

    /**
     * @return list<int>
     */
    private function parseNumberList(string $raw, int $min, int $max): array
    {
        $clean = [];
        foreach (preg_split('/[,\s]+/', trim($raw)) ?: [] as $piece) {
            if ($piece === '' || ! is_numeric($piece)) {
                continue;
            }
            $n = (int) round((float) $piece);
            if ($n >= $min && $n <= $max) {
                $clean[] = $n;
            }
        }

        return array_values(array_unique($clean));
    }

    public function updatePool(Request $request): RedirectResponse
    {
        $restaurantId = (int) Auth::user()->restaurant_id;
        $pool = $this->pools->ensureKitchenPool($restaurantId);

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'is_enabled' => 'nullable|boolean',
            'distribution_method' => 'required|in:equal,weighted',
        ]);

        $pool->update([
            'name' => $validated['name'],
            'is_enabled' => $request->boolean('is_enabled'),
            'distribution_method' => $validated['distribution_method'],
        ]);

        return back()->with('success', 'Kitchen tip pool settings saved.');
    }

    public function addMember(Request $request): RedirectResponse
    {
        $restaurantId = (int) Auth::user()->restaurant_id;
        $pool = $this->pools->ensureKitchenPool($restaurantId);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'weight' => 'nullable|integer|min:1|max:1000',
        ]);

        $user = User::role(['waiter', 'barista'])
            ->activeAtRestaurant($restaurantId)
            ->whereKey($validated['user_id'])
            ->first();

        if (! $user) {
            return back()->with('error', 'Staff must be linked to your restaurant.');
        }

        TipPoolMember::updateOrCreate(
            [
                'tip_pool_id' => $pool->id,
                'user_id' => $user->id,
            ],
            [
                'weight' => (int) ($validated['weight'] ?? 1),
                'is_active' => true,
            ]
        );

        return back()->with('success', "{$user->name} added to the kitchen tip pool.");
    }

    public function updateMember(Request $request, TipPoolMember $member): RedirectResponse
    {
        $this->assertMemberBelongsToManager($member);

        $validated = $request->validate([
            'weight' => 'required|integer|min:1|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $member->update([
            'weight' => $validated['weight'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $member->is_active,
        ]);

        return back()->with('success', 'Pool member updated.');
    }

    public function removeMember(TipPoolMember $member): RedirectResponse
    {
        $this->assertMemberBelongsToManager($member);
        $name = $member->user?->name ?? 'Staff';
        $member->delete();

        return back()->with('success', "{$name} removed from the kitchen tip pool.");
    }

    private function assertMemberBelongsToManager(TipPoolMember $member): void
    {
        $member->loadMissing('tipPool');
        if ((int) $member->tipPool?->restaurant_id !== (int) Auth::user()->restaurant_id) {
            abort(403);
        }
    }
}
