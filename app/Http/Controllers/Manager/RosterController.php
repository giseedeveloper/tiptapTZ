<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\StaffAbsence;
use App\Models\Table;
use App\Models\TableZone;
use App\Models\User;
use App\Models\WaiterShift;
use App\Services\WaiterRosterService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RosterController extends Controller
{
    public function __construct(
        private WaiterRosterService $rosterService,
    ) {}

    public function index(Request $request): View
    {
        $restaurantId = Auth::user()->restaurant_id;
        $selectedDate = Carbon::parse($request->input('date', now()->toDateString()));

        $waiters = User::role('waiter')
            ->where('restaurant_id', $restaurantId)
            ->with(['assignedTables' => fn ($q) => $q->with('zone')->orderBy('name')])
            ->orderBy('name')
            ->get();

        $tables = Table::with(['waiter:id,name', 'zone'])
            ->where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $zones = TableZone::withCount('tables')->orderBy('sort_order')->orderBy('name')->get();

        $shifts = $this->rosterService->shiftsForDate($restaurantId, $selectedDate);

        $absentIds = $this->rosterService->absentWaiterIds($restaurantId, $selectedDate);
        $absences = StaffAbsence::with(['waiter:id,name', 'reassignedTo:id,name'])
            ->where('restaurant_id', $restaurantId)
            ->whereDate('starts_on', '<=', $selectedDate)
            ->whereDate('ends_on', '>=', $selectedDate)
            ->get();

        return view('manager.roster.index', compact(
            'waiters',
            'tables',
            'zones',
            'shifts',
            'absences',
            'absentIds',
            'selectedDate',
        ));
    }

    public function storeShift(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_date' => 'required|date',
            'starts_at' => 'required|date_format:H:i',
            'ends_at' => 'required|date_format:H:i|after:starts_at',
            'label' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $manager = Auth::user();
        $waiter = User::role('waiter')
            ->where('restaurant_id', $manager->restaurant_id)
            ->findOrFail($request->user_id);

        WaiterShift::create([
            'restaurant_id' => $manager->restaurant_id,
            'user_id' => $waiter->id,
            'shift_date' => $request->shift_date,
            'starts_at' => $request->starts_at.':00',
            'ends_at' => $request->ends_at.':00',
            'label' => $request->label,
            'notes' => $request->notes,
            'created_by' => $manager->id,
        ]);

        $waiter->notify(new \App\Notifications\TableAssignmentChanged(
            message: 'Manager amekupa shift '.$request->starts_at.' – '.$request->ends_at.' tarehe '.Carbon::parse($request->shift_date)->format('d M Y').'.',
            tableNames: [],
            assignedBy: $manager->name,
        ));

        return back()->with('success', 'Shift imeongezwa kwa '.$waiter->name.'.');
    }

    public function destroyShift(WaiterShift $shift): RedirectResponse
    {
        $this->authorizeShift($shift);
        $shift->delete();

        return back()->with('success', 'Shift imefutwa.');
    }

    public function assignTables(Request $request): RedirectResponse
    {
        $request->validate([
            'waiter_id' => 'required|exists:users,id',
            'table_ids' => 'required|array|min:1',
            'table_ids.*' => 'integer|exists:tables,id',
        ]);

        $manager = Auth::user();
        $waiter = User::role('waiter')
            ->where('restaurant_id', $manager->restaurant_id)
            ->findOrFail($request->waiter_id);

        $count = $this->rosterService->assignTables($waiter, $request->table_ids, $manager);

        return back()->with('success', $count.' table(s) assigned to '.$waiter->name.'.');
    }

    public function reassignTables(Request $request): RedirectResponse
    {
        $request->validate([
            'from_waiter_id' => 'required|exists:users,id',
            'to_waiter_id' => 'required|exists:users,id|different:from_waiter_id',
        ]);

        $manager = Auth::user();
        $from = User::role('waiter')->where('restaurant_id', $manager->restaurant_id)->findOrFail($request->from_waiter_id);
        $to = User::role('waiter')->where('restaurant_id', $manager->restaurant_id)->findOrFail($request->to_waiter_id);

        $count = $this->rosterService->reassignAllTables($from, $to, $manager);

        if ($count === 0) {
            return back()->with('info', $from->name.' hana meza zilizopewa.');
        }

        return back()->with('success', $count.' table(s) reassigned from '.$from->name.' to '.$to->name.'.');
    }

    public function markAbsent(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'starts_on' => 'required|date',
            'ends_on' => 'required|date|after_or_equal:starts_on',
            'reason' => 'required|in:sick,leave,other',
            'notes' => 'nullable|string|max:500',
            'reassign_to_user_id' => 'nullable|exists:users,id',
        ]);

        $manager = Auth::user();
        $waiter = User::role('waiter')
            ->where('restaurant_id', $manager->restaurant_id)
            ->findOrFail($request->user_id);

        $reassignTo = null;
        if ($request->filled('reassign_to_user_id')) {
            $reassignTo = User::role('waiter')
                ->where('restaurant_id', $manager->restaurant_id)
                ->where('id', '!=', $waiter->id)
                ->findOrFail($request->reassign_to_user_id);
        }

        $this->rosterService->markAbsent(
            $waiter,
            $manager,
            Carbon::parse($request->starts_on),
            Carbon::parse($request->ends_on),
            $request->reason,
            $request->notes,
            $reassignTo,
        );

        return back()->with('success', $waiter->name.' marked absent. Tables updated if reassigned.');
    }

    public function clearAbsence(StaffAbsence $absence): RedirectResponse
    {
        if ((int) $absence->restaurant_id !== (int) Auth::user()->restaurant_id) {
            abort(403);
        }

        $this->rosterService->clearAbsence($absence);

        return back()->with('success', 'Absence record removed.');
    }

    public function storeZone(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        TableZone::create([
            'restaurant_id' => Auth::user()->restaurant_id,
            'name' => $request->name,
            'sort_order' => TableZone::count(),
        ]);

        return back()->with('success', 'Zone "'.$request->name.'" created.');
    }

    public function assignZone(Request $request): RedirectResponse
    {
        $request->validate([
            'zone_id' => 'nullable|exists:table_zones,id',
            'table_ids' => 'required|array|min:1',
            'table_ids.*' => 'integer|exists:tables,id',
            'waiter_id' => 'nullable|exists:users,id',
        ]);

        $manager = Auth::user();
        $tableIds = $request->table_ids;

        if ($request->filled('zone_id')) {
            Table::whereIn('id', $tableIds)
                ->where('restaurant_id', $manager->restaurant_id)
                ->update(['zone_id' => $request->zone_id]);
        }

        if ($request->filled('waiter_id')) {
            $waiter = User::role('waiter')
                ->where('restaurant_id', $manager->restaurant_id)
                ->findOrFail($request->waiter_id);

            $this->rosterService->assignTables($waiter, $tableIds, $manager);
        }

        return back()->with('success', 'Zone/table assignment updated.');
    }

    private function authorizeShift(WaiterShift $shift): void
    {
        if ((int) $shift->restaurant_id !== (int) Auth::user()->restaurant_id) {
            abort(403);
        }
    }
}
