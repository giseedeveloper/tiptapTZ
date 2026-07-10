<?php

namespace App\Services;

use App\Models\StaffAbsence;
use App\Models\Table;
use App\Models\User;
use App\Models\WaiterShift;
use App\Notifications\TableAssignmentChanged;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WaiterRosterService
{
    /**
     * @return list<int>
     */
    public function absentWaiterIds(int $restaurantId, ?Carbon $date = null): array
    {
        $date = ($date ?? now())->toDateString();

        return StaffAbsence::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->whereDate('starts_on', '<=', $date)
            ->whereDate('ends_on', '>=', $date)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->all();
    }

    public function isWaiterAbsent(User $waiter, ?Carbon $date = null): bool
    {
        if (! $waiter->restaurant_id) {
            return false;
        }

        return in_array($waiter->id, $this->absentWaiterIds((int) $waiter->restaurant_id, $date), true);
    }

    public function isWaiterOnShift(User $waiter, ?Carbon $date = null): bool
    {
        if (! $waiter->restaurant_id) {
            return false;
        }

        $date = ($date ?? now())->toDateString();
        $now = now()->format('H:i:s');

        return WaiterShift::withoutGlobalScopes()
            ->where('restaurant_id', $waiter->restaurant_id)
            ->where('user_id', $waiter->id)
            ->whereDate('shift_date', $date)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->exists();
    }

    /**
     * @param  list<int>  $tableIds
     */
    public function assignTables(User $waiter, array $tableIds, User $manager): int
    {
        $tables = Table::whereIn('id', $tableIds)
            ->where('restaurant_id', $waiter->restaurant_id)
            ->get();

        if ($tables->isEmpty()) {
            return 0;
        }

        Table::whereIn('id', $tables->pluck('id'))->update(['waiter_id' => $waiter->id]);

        $tableNames = $tables->pluck('name')->all();
        $waiter->notify(new TableAssignmentChanged(
            message: 'Manager amekupa meza: '.implode(', ', $tableNames).'.',
            tableNames: $tableNames,
            assignedBy: $manager->name,
        ));

        return $tables->count();
    }

    public function reassignAllTables(User $fromWaiter, User $toWaiter, User $manager): int
    {
        $tables = Table::where('waiter_id', $fromWaiter->id)
            ->where('restaurant_id', $fromWaiter->restaurant_id)
            ->get();

        if ($tables->isEmpty()) {
            return 0;
        }

        Table::whereIn('id', $tables->pluck('id'))->update(['waiter_id' => $toWaiter->id]);

        $tableNames = $tables->pluck('name')->all();

        $toWaiter->notify(new TableAssignmentChanged(
            message: 'Manager amekuhamishia meza kutoka kwa '.$fromWaiter->name.': '.implode(', ', $tableNames).'.',
            tableNames: $tableNames,
            assignedBy: $manager->name,
        ));

        $fromWaiter->notify(new TableAssignmentChanged(
            message: 'Meza zako zimehamishwa kwa '.$toWaiter->name.' na manager.',
            tableNames: $tableNames,
            assignedBy: $manager->name,
        ));

        return $tables->count();
    }

    public function markAbsent(
        User $waiter,
        User $manager,
        Carbon $startsOn,
        Carbon $endsOn,
        string $reason = 'sick',
        ?string $notes = null,
        ?User $reassignTo = null,
    ): StaffAbsence {
        $waiter->is_online = false;
        $waiter->last_online_at = now();
        $waiter->save();

        $reassignedCount = 0;
        if ($reassignTo !== null) {
            $reassignedCount = $this->reassignAllTables($waiter, $reassignTo, $manager);
        }

        $absence = StaffAbsence::create([
            'restaurant_id' => $waiter->restaurant_id,
            'user_id' => $waiter->id,
            'starts_on' => $startsOn->toDateString(),
            'ends_on' => $endsOn->toDateString(),
            'reason' => $reason,
            'notes' => $notes,
            'marked_by' => $manager->id,
            'reassigned_to_user_id' => $reassignTo?->id,
        ]);

        $waiter->notify(new TableAssignmentChanged(
            message: 'Manager amekuweka kuwa absent ('.$reason.') kuanzia '.$startsOn->format('d M').'.'
                .($reassignedCount > 0 ? ' Meza zako zimehamishwa kwa '.$reassignTo->name.'.' : ''),
            tableNames: [],
            assignedBy: $manager->name,
        ));

        return $absence;
    }

    public function clearAbsence(StaffAbsence $absence): void
    {
        $absence->delete();
    }

    /**
     * @return Collection<int, WaiterShift>
     */
    public function shiftsForDate(int $restaurantId, Carbon $date): Collection
    {
        return WaiterShift::with('waiter:id,name,is_online')
            ->where('restaurant_id', $restaurantId)
            ->whereDate('shift_date', $date->toDateString())
            ->orderBy('starts_at')
            ->get();
    }

    /**
     * Best waiter for a table call: assigned waiter if online & not absent, else free waiter.
     */
    public function resolveWaiterForTable(int $restaurantId, ?Table $table, FreeWaiterService $freeWaiterService): ?User
    {
        if ($table?->waiter_id) {
            $assigned = User::find($table->waiter_id);
            if ($assigned
                && (int) $assigned->restaurant_id === $restaurantId
                && $assigned->is_online
                && ! $this->isWaiterAbsent($assigned)
            ) {
                return $assigned;
            }
        }

        return $freeWaiterService->findAvailable($restaurantId);
    }
}
