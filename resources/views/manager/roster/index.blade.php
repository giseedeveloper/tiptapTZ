<x-manager-layout>
    <x-slot name="header">Waiter Roster</x-slot>

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Waiter Roster</h2>
            <p class="text-sm font-medium text-white/40 uppercase tracking-wider">Shifts, table assignments & absences</p>
        </div>
        <form method="GET" action="{{ route('manager.roster.index') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $selectedDate->toDateString() }}"
                   class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm focus:ring-2 focus:ring-violet-500">
            <button type="submit" class="px-4 py-2.5 bg-violet-600 hover:bg-violet-500 text-white rounded-xl text-sm font-semibold">View</button>
        </form>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Waiters on duty</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $waiters->whereNotIn('id', $absentIds)->count() }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Absent today</p>
            <p class="text-2xl font-bold text-rose-400 mt-1">{{ count($absentIds) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Shifts scheduled</p>
            <p class="text-2xl font-bold text-teal-400 mt-1">{{ $shifts->count() }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Tables assigned</p>
            <p class="text-2xl font-bold text-violet-400 mt-1">{{ $tables->whereNotNull('waiter_id')->count() }}/{{ $tables->count() }}</p>
        </div>
    </div>

    <div x-data="{ tab: 'floor' }" class="space-y-6">
        <div class="flex flex-wrap gap-2 border-b border-white/10 pb-4">
            <button type="button" @click="tab = 'floor'" :class="tab === 'floor' ? 'bg-violet-600 text-white' : 'bg-white/5 text-white/60 hover:text-white'" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all">Floor View</button>
            <button type="button" @click="tab = 'shifts'" :class="tab === 'shifts' ? 'bg-violet-600 text-white' : 'bg-white/5 text-white/60 hover:text-white'" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all">Shifts</button>
            <button type="button" @click="tab = 'absences'" :class="tab === 'absences' ? 'bg-violet-600 text-white' : 'bg-white/5 text-white/60 hover:text-white'" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all">Absences</button>
            <button type="button" @click="tab = 'zones'" :class="tab === 'zones' ? 'bg-violet-600 text-white' : 'bg-white/5 text-white/60 hover:text-white'" class="px-4 py-2 rounded-xl text-sm font-semibold transition-all">Zones</button>
        </div>

        {{-- FLOOR VIEW --}}
        <div x-show="tab === 'floor'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($waiters as $waiter)
                    @php $isAbsent = in_array($waiter->id, $absentIds, true); @endphp
                    <div class="glass-card rounded-2xl p-6 border {{ $isAbsent ? 'border-rose-500/30 bg-rose-500/5' : 'border-white/10' }}">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ $waiter->name }}</h3>
                                <p class="text-xs text-white/40 font-mono">{{ $waiter->global_waiter_number }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2 justify-end">
                                @if($isAbsent)
                                    <span class="px-2 py-1 rounded-full bg-rose-500/20 text-rose-300 text-[10px] font-bold uppercase">Absent</span>
                                @elseif($waiter->is_online)
                                    <span class="px-2 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-bold uppercase">Online</span>
                                @else
                                    <span class="px-2 py-1 rounded-full bg-white/10 text-white/50 text-[10px] font-bold uppercase">Offline</span>
                                @endif
                            </div>
                        </div>

                        @if($waiter->assignedTables->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach($waiter->assignedTables as $table)
                                    <span class="px-2.5 py-1 rounded-lg bg-violet-500/15 text-violet-300 text-xs font-semibold border border-violet-500/20">
                                        {{ $table->name }}
                                        @if($table->zone)<span class="text-white/40">· {{ $table->zone->name }}</span>@endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-white/40 mb-4">No tables assigned.</p>
                        @endif

                        <details class="group">
                            <summary class="cursor-pointer text-xs font-semibold text-violet-400 hover:text-violet-300">Quick actions</summary>
                            <div class="mt-3 space-y-3 pt-3 border-t border-white/10">
                                <form action="{{ route('manager.roster.assign-tables') }}" method="POST" class="space-y-2">
                                    @csrf
                                    <input type="hidden" name="waiter_id" value="{{ $waiter->id }}">
                                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40">Assign tables</label>
                                    <select name="table_ids[]" multiple size="4" class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                                        @foreach($tables as $table)
                                            <option value="{{ $table->id }}">{{ $table->name }}@if($table->waiter && $table->waiter_id !== $waiter->id) ({{ $table->waiter->name }})@endif</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="w-full py-2 bg-violet-600 hover:bg-violet-500 text-white rounded-xl text-xs font-semibold">Assign selected</button>
                                </form>

                                @if($waiter->assignedTables->isNotEmpty())
                                    <form action="{{ route('manager.roster.reassign-tables') }}" method="POST" class="space-y-2">
                                        @csrf
                                        <input type="hidden" name="from_waiter_id" value="{{ $waiter->id }}">
                                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40">Reassign all tables to</label>
                                        <select name="to_waiter_id" required class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                                            <option value="">— Select waiter —</option>
                                            @foreach($waiters->where('id', '!=', $waiter->id) as $colleague)
                                                <option value="{{ $colleague->id }}">{{ $colleague->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="w-full py-2 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl text-xs font-semibold">Reassign all</button>
                                    </form>
                                @endif
                            </div>
                        </details>
                    </div>
                @endforeach
            </div>

            @if($waiters->isEmpty())
                <div class="glass-card rounded-2xl p-12 text-center text-white/40">
                    <p>No waiters linked yet. Link waiters from <a href="{{ route('manager.waiters.index') }}" class="text-violet-400 underline">Waiters & Staff</a>.</p>
                </div>
            @endif
        </div>

        {{-- SHIFTS --}}
        <div x-show="tab === 'shifts'" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Add shift</h3>
                <form action="{{ route('manager.roster.shifts.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="shift_date" value="{{ $selectedDate->toDateString() }}">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Waiter</label>
                        <select name="user_id" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                            @foreach($waiters as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Start</label>
                            <input type="time" name="starts_at" required value="08:00" class="w-full px-3 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">End</label>
                            <input type="time" name="ends_at" required value="17:00" class="w-full px-3 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Label (optional)</label>
                        <input type="text" name="label" placeholder="Morning / Evening" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-white/30">
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-violet-600 to-cyan-600 text-white rounded-xl font-semibold text-sm">Save shift</button>
                </form>
            </div>

            <div class="lg:col-span-2 glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Shifts for {{ $selectedDate->format('d M Y') }}</h3>
                @forelse($shifts as $shift)
                    <div class="flex items-center justify-between gap-4 py-4 border-b border-white/5 last:border-0">
                        <div>
                            <p class="font-semibold text-white">{{ $shift->waiter->name }}</p>
                            <p class="text-sm text-teal-400">{{ $shift->timeRangeLabel() }}</p>
                            @if($shift->label)<p class="text-xs text-white/40">{{ $shift->label }}</p>@endif
                        </div>
                        <form action="{{ route('manager.roster.shifts.destroy', $shift) }}" method="POST" onsubmit="return confirm('Delete this shift?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold">Remove</button>
                        </form>
                    </div>
                @empty
                    <p class="text-white/40 text-sm">No shifts scheduled for this date.</p>
                @endforelse
            </div>
        </div>

        {{-- ABSENCES --}}
        <div x-show="tab === 'absences'" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Mark absent</h3>
                <form action="{{ route('manager.roster.mark-absent') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Waiter</label>
                        <select name="user_id" required class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                            @foreach($waiters as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">From</label>
                            <input type="date" name="starts_on" required value="{{ $selectedDate->toDateString() }}" class="w-full px-3 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">To</label>
                            <input type="date" name="ends_on" required value="{{ $selectedDate->toDateString() }}" class="w-full px-3 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Reason</label>
                        <select name="reason" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                            <option value="sick">Sick leave</option>
                            <option value="leave">Other leave</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Reassign tables to (optional)</label>
                        <select name="reassign_to_user_id" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                            <option value="">— Don't reassign —</option>
                            @foreach($waiters as $w)
                                <option value="{{ $w->id }}">{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <textarea name="notes" rows="2" placeholder="Notes (optional)" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm placeholder-white/30"></textarea>
                    <button type="submit" class="w-full py-3 bg-rose-600 hover:bg-rose-500 text-white rounded-xl font-semibold text-sm">Mark absent</button>
                </form>
            </div>

            <div class="lg:col-span-2 glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Active absences on {{ $selectedDate->format('d M Y') }}</h3>
                @forelse($absences as $absence)
                    <div class="flex items-center justify-between gap-4 py-4 border-b border-white/5 last:border-0">
                        <div>
                            <p class="font-semibold text-white">{{ $absence->waiter->name }}</p>
                            <p class="text-sm text-rose-300 capitalize">{{ str_replace('_', ' ', $absence->reason) }}</p>
                            <p class="text-xs text-white/40">{{ $absence->starts_on->format('d M') }} – {{ $absence->ends_on->format('d M Y') }}</p>
                            @if($absence->reassignedTo)
                                <p class="text-xs text-cyan-400 mt-1">Tables → {{ $absence->reassignedTo->name }}</p>
                            @endif
                        </div>
                        <form action="{{ route('manager.roster.absences.destroy', $absence) }}" method="POST" onsubmit="return confirm('Remove absence record?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-white/40 hover:text-white text-xs font-semibold">Clear</button>
                        </form>
                    </div>
                @empty
                    <p class="text-white/40 text-sm">No absences recorded for this date.</p>
                @endforelse
            </div>
        </div>

        {{-- ZONES --}}
        <div x-show="tab === 'zones'" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Create zone</h3>
                <form action="{{ route('manager.roster.zones.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="text" name="name" required placeholder="e.g. Patio, VIP, Indoor" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-white/30">
                    <button type="submit" class="w-full py-3 bg-violet-600 text-white rounded-xl font-semibold text-sm">Add zone</button>
                </form>
                @if($zones->isNotEmpty())
                    <div class="mt-6 space-y-2">
                        @foreach($zones as $zone)
                            <div class="flex justify-between text-sm text-white/70 py-2 border-b border-white/5">
                                <span>{{ $zone->name }}</span>
                                <span class="text-white/40">{{ $zone->tables_count }} tables</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="lg:col-span-2 glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Assign zone + waiter to tables</h3>
                <form action="{{ route('manager.roster.assign-zone') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Zone</label>
                            <select name="zone_id" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                                <option value="">— No zone change —</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Assign waiter</label>
                            <select name="waiter_id" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                                <option value="">— No waiter change —</option>
                                @foreach($waiters as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Tables (Ctrl/Cmd + click for multiple)</label>
                        <select name="table_ids[]" multiple required size="8" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}">{{ $table->name }}@if($table->zone) · {{ $table->zone->name }}@endif</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-violet-600 to-cyan-600 text-white rounded-xl font-semibold text-sm">Apply</button>
                </form>
            </div>
        </div>
    </div>
</x-manager-layout>
