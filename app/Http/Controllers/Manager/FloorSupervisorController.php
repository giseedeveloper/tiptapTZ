<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\TableZone;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class FloorSupervisorController extends Controller
{
    /**
     * List all floor supervisors for the current/active restaurant.
     */
    public function index(): View
    {
        $restaurantId = Auth::user()->restaurant_id;

        $supervisors = User::role('floor_supervisor')
            ->where('restaurant_id', $restaurantId)
            ->with('zone')
            ->get();

        $zones = TableZone::where('restaurant_id', $restaurantId)
            ->with('supervisor')
            ->orderBy('sort_order')
            ->get();

        // Waiters available to be promoted to floor_supervisor
        $waiters = User::role('waiter')
            ->where('restaurant_id', $restaurantId)
            ->whereNotIn('id', $supervisors->pluck('id'))
            ->get();

        return view('manager.floor-supervisors.index', compact('supervisors', 'zones', 'waiters'));
    }

    /**
     * Assign the floor_supervisor role to an existing waiter (or standalone user).
     * The user keeps their restaurant_id; we just change their role.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'zone_id' => 'nullable|exists:table_zones,id',
        ]);

        $restaurantId = Auth::user()->restaurant_id;
        $user = User::findOrFail($request->user_id);

        // Verify the user belongs to this restaurant
        abort_unless($user->restaurant_id === $restaurantId, 403);

        // Assign role (Spatie — removes waiter role if adding supervisor)
        $user->syncRoles(['floor_supervisor']);

        // Assign zone if provided
        if ($request->zone_id) {
            $zone = TableZone::where('restaurant_id', $restaurantId)->findOrFail($request->zone_id);
            $user->update(['zone_id' => $zone->id]);
            $zone->update(['supervisor_id' => $user->id]);
        }

        return back()->with('success', $user->name.' is now a Floor Supervisor.');
    }

    /**
     * Assign a supervisor to a specific zone (or change zone).
     */
    public function assignZone(Request $request, User $supervisor): RedirectResponse
    {
        $request->validate(['zone_id' => 'nullable|exists:table_zones,id']);

        $restaurantId = Auth::user()->restaurant_id;
        abort_unless($supervisor->restaurant_id === $restaurantId, 403);
        abort_unless($supervisor->hasRole('floor_supervisor'), 403);

        // Detach from old zone
        if ($supervisor->zone_id) {
            TableZone::where('id', $supervisor->zone_id)
                ->where('restaurant_id', $restaurantId)
                ->update(['supervisor_id' => null]);
        }

        $zoneId = $request->zone_id ? (int) $request->zone_id : null;
        $supervisor->update(['zone_id' => $zoneId]);

        if ($zoneId) {
            TableZone::where('id', $zoneId)
                ->where('restaurant_id', $restaurantId)
                ->update(['supervisor_id' => $supervisor->id]);
        }

        return back()->with('success', 'Zone assignment updated.');
    }

    /**
     * Demote floor_supervisor back to waiter.
     */
    public function destroy(User $supervisor): RedirectResponse
    {
        $restaurantId = Auth::user()->restaurant_id;
        abort_unless($supervisor->restaurant_id === $restaurantId, 403);
        abort_unless($supervisor->hasRole('floor_supervisor'), 403);

        // Clear zone link
        if ($supervisor->zone_id) {
            TableZone::where('id', $supervisor->zone_id)->update(['supervisor_id' => null]);
        }
        $supervisor->update(['zone_id' => null]);
        $supervisor->syncRoles(['waiter']);

        return back()->with('success', $supervisor->name.' has been moved back to Waiter.');
    }
}
