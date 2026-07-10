<x-manager-layout>
    <x-slot name="header">Floor Supervisors</x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
            <p class="text-sm font-medium text-emerald-100/90">{{ session('success') }}</p>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-xl">
            <p class="text-sm font-medium text-rose-100/90">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Intro Banner --}}
    <div class="mb-8 p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center gap-4">
        <div class="w-10 h-10 bg-amber-500/20 rounded-xl flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-amber-100/90">
                <span class="font-bold text-amber-300">{{ $supervisors->count() }}</span> floor
                {{ Str::plural('supervisor', $supervisors->count()) }} assigned.
                Supervisors can oversee zones and monitor waiter activity.
            </p>
        </div>
    </div>

    {{-- Current Supervisors --}}
    <div class="glass-card rounded-2xl overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white tracking-tight">Current Floor Supervisors</h2>
                <p class="text-xs text-white/40 mt-0.5">{{ $supervisors->count() }} {{ Str::plural('supervisor', $supervisors->count()) }}</p>
            </div>
        </div>

        @if($supervisors->isNotEmpty())
            <div class="divide-y divide-white/5">
                @foreach($supervisors as $supervisor)
                    <div class="px-6 py-4" x-data="{ assigningZone: false }">
                        <div class="flex flex-wrap items-center gap-3">

                            {{-- Avatar + Name --}}
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-xl flex items-center justify-center border border-amber-500/20 shrink-0">
                                    <span class="text-sm font-bold text-amber-400 uppercase">{{ substr($supervisor->name, 0, 1) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-white text-sm truncate">{{ $supervisor->name }}</p>
                                    <p class="text-[11px] text-white/40 truncate">{{ $supervisor->email }}</p>
                                </div>
                            </div>

                            {{-- Zone Badge --}}
                            <div class="shrink-0">
                                @if($supervisor->zone)
                                    <span class="px-3 py-1.5 bg-cyan-500/10 text-cyan-400 text-[10px] font-bold rounded-full uppercase tracking-wider border border-cyan-500/20">
                                        {{ $supervisor->zone->name }}
                                    </span>
                                @else
                                    <span class="px-3 py-1.5 bg-white/5 text-white/30 text-[10px] font-bold rounded-full uppercase tracking-wider border border-white/10">
                                        Unassigned
                                    </span>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    type="button"
                                    @click="assigningZone = !assigningZone"
                                    class="glass px-3 py-2 rounded-lg text-xs font-semibold text-white/70 hover:text-white transition-all flex items-center gap-1.5"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>
                                    </svg>
                                    Assign Zone
                                </button>

                                <form method="POST"
                                      action="{{ route('manager.floor-supervisors.destroy', $supervisor) }}"
                                      onsubmit="return confirm('Demote {{ addslashes($supervisor->name) }} back to waiter?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg text-xs font-semibold uppercase tracking-wider border border-rose-500/20 transition-colors flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="22" x2="16" y1="11" y2="11"/>
                                        </svg>
                                        Demote
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Assign Zone Inline Form --}}
                        <div
                            x-show="assigningZone"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="mt-4 ml-13"
                        >
                            <form method="POST"
                                  action="{{ route('manager.floor-supervisors.assign-zone', $supervisor) }}"
                                  class="flex flex-wrap items-center gap-3">
                                @csrf
                                <div class="flex-1 min-w-[200px]">
                                    <select
                                        name="zone_id"
                                        class="w-full glass rounded-xl px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500/50 transition-all appearance-none"
                                        style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff40' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25em 1.25em; padding-right: 2.5rem;"
                                    >
                                        <option value="">No Zone (Unassigned)</option>
                                        @foreach($zones as $zone)
                                            <option value="{{ $zone->id }}"
                                                {{ optional($supervisor->zone)->id == $zone->id ? 'selected' : '' }}>
                                                {{ $zone->name }}
                                                @if($zone->supervisor)
                                                    ({{ $zone->supervisor->id === $supervisor->id ? 'Current' : 'Taken — ' . $zone->supervisor->name }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="submit"
                                        class="bg-gradient-to-r from-cyan-600 to-teal-600 text-white px-4 py-2 rounded-xl font-semibold hover:shadow-lg hover:shadow-cyan-500/25 transition-all text-sm flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    Save Zone
                                </button>
                                <button type="button"
                                        @click="assigningZone = false"
                                        class="glass px-4 py-2 rounded-xl font-semibold text-white/50 hover:text-white transition-all text-sm">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-amber-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white mb-2">No Floor Supervisors Yet</h3>
                <p class="text-sm text-white/40">Promote a waiter to floor supervisor using the form below.</p>
            </div>
        @endif
    </div>

    {{-- Zone Overview --}}
    @if($zones->isNotEmpty())
        <div class="glass-card rounded-2xl overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="text-xl font-bold text-white tracking-tight">Zones Overview</h2>
                <p class="text-xs text-white/40 mt-0.5">{{ $zones->count() }} {{ Str::plural('zone', $zones->count()) }} configured</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left py-3 px-6 text-[10px] font-bold text-white/40 uppercase tracking-wider">Zone Name</th>
                            <th class="text-left py-3 px-6 text-[10px] font-bold text-white/40 uppercase tracking-wider">Supervisor</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($zones as $zone)
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-6">
                                    <span class="font-medium text-white">{{ $zone->name }}</span>
                                </td>
                                <td class="py-3 px-6 text-white/70">
                                    @if($zone->supervisor)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-md flex items-center justify-center border border-amber-500/20">
                                                <span class="text-[9px] font-bold text-amber-400 uppercase">{{ substr($zone->supervisor->name, 0, 1) }}</span>
                                            </div>
                                            <span class="text-sm">{{ $zone->supervisor->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-white/30 italic text-xs">No supervisor</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Add Floor Supervisor Section --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-xl font-bold text-white tracking-tight">Add Floor Supervisor</h2>
            <p class="text-xs text-white/40 mt-0.5">Promote a waiter to floor supervisor role.</p>
        </div>

        <div class="p-6">
            @if($waiters->isNotEmpty())
                <form method="POST" action="{{ route('manager.floor-supervisors.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                        {{-- Select Waiter --}}
                        <div>
                            <label for="user_id" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Select Waiter <span class="text-rose-400">*</span>
                            </label>
                            <select
                                id="user_id"
                                name="user_id"
                                required
                                class="w-full glass rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all appearance-none"
                                style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff40' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25em 1.25em; padding-right: 2.5rem;"
                            >
                                <option value="">-- Choose a waiter --</option>
                                @foreach($waiters as $waiter)
                                    <option value="{{ $waiter->id }}" {{ old('user_id') == $waiter->id ? 'selected' : '' }}>
                                        {{ $waiter->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Select Zone (Optional) --}}
                        <div>
                            <label for="zone_id" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Assign Zone
                                <span class="normal-case font-normal text-white/30 ml-1">(optional)</span>
                            </label>
                            <select
                                id="zone_id"
                                name="zone_id"
                                class="w-full glass rounded-xl px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all appearance-none"
                                style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff40' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25em 1.25em; padding-right: 2.5rem;"
                            >
                                <option value="">No Zone (assign later)</option>
                                @foreach($zones as $zone)
                                    <option value="{{ $zone->id }}" {{ old('zone_id') == $zone->id ? 'selected' : '' }}>
                                        {{ $zone->name }}
                                        @if($zone->supervisor)
                                            ({{ $zone->supervisor->name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('zone_id')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="bg-gradient-to-r from-violet-600 to-cyan-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg hover:shadow-violet-500/25 transition-all flex items-center gap-2 text-sm"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/>
                        </svg>
                        Promote to Floor Supervisor
                    </button>
                </form>
            @else
                {{-- Empty State --}}
                <div class="py-10 text-center">
                    <div class="w-14 h-14 bg-gradient-to-br from-white/5 to-white/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white/30">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-white/50">All staff are already supervisors or there are no waiters assigned to this branch.</p>
                    <a href="{{ route('manager.waiters.index') }}"
                       class="inline-block mt-3 text-xs text-violet-400 hover:text-violet-300 transition-colors font-medium">
                        Manage waiters &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>

</x-manager-layout>
