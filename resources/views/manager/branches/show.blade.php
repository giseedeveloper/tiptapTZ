<x-manager-layout>
    <x-slot name="header">{{ $branch->displayName() }}</x-slot>

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

    {{-- Top action bar --}}
    <div class="flex flex-wrap items-center gap-3 mb-8">
        <a href="{{ route('manager.branches.index') }}"
           class="glass px-4 py-2.5 rounded-xl font-semibold text-white/70 hover:text-white transition-all text-sm flex items-center gap-2 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            All Branches
        </a>

        <div class="flex-1"></div>

        <form method="POST" action="{{ route('manager.switch-branch') }}" class="shrink-0">
            @csrf
            <input type="hidden" name="branch_id" value="{{ $branch->id }}">
            <button type="submit"
                    class="bg-gradient-to-r from-violet-600 to-cyan-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:shadow-lg hover:shadow-violet-500/25 transition-all flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
                Switch to This Branch
            </button>
        </form>

        <a href="{{ route('manager.branches.edit', $branch) }}"
           class="glass px-4 py-2.5 rounded-xl font-semibold text-white/70 hover:text-white transition-all text-sm flex items-center gap-2 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
            </svg>
            Edit
        </a>
    </div>

    {{-- Branch Info Banner --}}
    <div class="glass-card rounded-2xl p-5 mb-8 flex flex-wrap items-center gap-4">
        <div class="w-12 h-12 bg-gradient-to-br from-violet-500/20 to-cyan-500/20 rounded-xl flex items-center justify-center border border-violet-500/20 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-400">
                <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-lg font-bold text-white leading-tight">{{ $branch->displayName() }}</h2>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1">
                @if($branch->location)
                    <span class="text-xs text-white/50 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                        {{ $branch->location }}
                    </span>
                @endif
                @if($branch->phone)
                    <span class="text-xs text-white/50 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.62 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        {{ $branch->phone }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        {{-- Orders Today --}}
        <div class="glass-card rounded-2xl p-6 card-hover relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-violet-500/20 to-violet-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-5">
                    <div class="w-12 h-12 bg-gradient-to-br from-violet-500/20 to-purple-500/20 rounded-xl flex items-center justify-center border border-violet-500/20 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-400">
                            <circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>
                        </svg>
                    </div>
                    <span class="px-3 py-1.5 bg-violet-500/10 text-violet-400 text-[10px] font-bold rounded-full uppercase tracking-wider border border-violet-500/20">Today</span>
                </div>
                <p class="text-xs font-semibold text-white/65 uppercase tracking-wider mb-1">Orders Today</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums">{{ number_format($stats['orders_today']) }}</h3>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="glass-card rounded-2xl p-6 card-hover relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-cyan-500/20 to-cyan-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-5">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500/20 to-blue-500/20 rounded-xl flex items-center justify-center border border-cyan-500/20 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400">
                            <line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <span class="px-3 py-1.5 bg-cyan-500/10 text-cyan-400 text-[10px] font-bold rounded-full uppercase tracking-wider border border-cyan-500/20">Revenue</span>
                </div>
                <p class="text-xs font-semibold text-white/65 uppercase tracking-wider mb-1">Revenue Today</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums break-all sm:break-normal">
                    {{ $currencySymbol }} {{ number_format($stats['revenue_today']) }}
                </h3>
            </div>
        </div>

        {{-- Avg Rating --}}
        <div class="glass-card rounded-2xl p-6 card-hover relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-amber-500/20 to-amber-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-5">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-xl flex items-center justify-center border border-amber-500/20 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-400">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    </div>
                    <span class="px-3 py-1.5 bg-amber-500/10 text-amber-400 text-[10px] font-bold rounded-full uppercase tracking-wider border border-amber-500/20">Rating</span>
                </div>
                <p class="text-xs font-semibold text-white/65 uppercase tracking-wider mb-1">Avg. Rating</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums">{{ number_format($stats['avg_rating'], 1) }}/5.0</h3>
            </div>
        </div>

        {{-- Waiters Online --}}
        <div class="glass-card rounded-2xl p-6 card-hover relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-emerald-500/20 to-emerald-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-5">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl flex items-center justify-center border border-emerald-500/20 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span class="px-3 py-1.5 bg-emerald-500/10 text-emerald-400 text-[10px] font-bold rounded-full uppercase tracking-wider border border-emerald-500/20 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                        Online
                    </span>
                </div>
                <p class="text-xs font-semibold text-white/65 uppercase tracking-wider mb-1">Waiters Online</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums">{{ $stats['waiters_online'] }} Active</h3>
            </div>
        </div>

    </div>

    {{-- Two-column layout for supervisors + waiters --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        {{-- Floor Supervisors --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Floor Supervisors</h3>
                    <p class="text-xs text-white/40 mt-0.5">{{ $supervisors->count() }} assigned</p>
                </div>
                <a href="{{ route('manager.floor-supervisors.index') }}"
                   class="glass px-3 py-1.5 rounded-lg text-xs font-semibold text-white/70 hover:text-white transition-all flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                    Manage
                </a>
            </div>

            @if($supervisors->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="text-left py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Name</th>
                                <th class="text-left py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Zone</th>
                                <th class="text-right py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($supervisors as $supervisor)
                                <tr class="hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3 px-4 text-white/70">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-lg flex items-center justify-center border border-amber-500/20 shrink-0">
                                                <span class="text-[10px] font-bold text-amber-400 uppercase">{{ substr($supervisor->name, 0, 1) }}</span>
                                            </div>
                                            <span class="font-medium text-white text-sm">{{ $supervisor->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-white/70">
                                        @if($supervisor->zone)
                                            <span class="px-2.5 py-1 bg-cyan-500/10 text-cyan-400 text-[10px] font-bold rounded-full border border-cyan-500/20 uppercase tracking-wider">
                                                {{ $supervisor->zone->name }}
                                            </span>
                                        @else
                                            <span class="text-white/30 text-xs italic">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="{{ route('manager.floor-supervisors.index') }}"
                                           class="text-xs text-violet-400 hover:text-violet-300 transition-colors font-medium">
                                            Manage
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center">
                    <p class="text-sm text-white/40">No floor supervisors assigned to this branch.</p>
                    <a href="{{ route('manager.floor-supervisors.index') }}"
                       class="inline-block mt-3 text-xs text-violet-400 hover:text-violet-300 transition-colors font-medium">
                        Add supervisor &rarr;
                    </a>
                </div>
            @endif
        </div>

        {{-- Waiters --}}
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">Waiters</h3>
                    <p class="text-xs text-white/40 mt-0.5">{{ $waiters->count() }} assigned to this branch</p>
                </div>
                <a href="{{ route('manager.waiters.index') }}"
                   class="glass px-3 py-1.5 rounded-lg text-xs font-semibold text-white/70 hover:text-white transition-all flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    View All
                </a>
            </div>

            @if($waiters->isNotEmpty())
                <div class="p-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($waiters as $waiter)
                            <div class="glass rounded-xl px-3 py-2 flex items-center gap-2">
                                <div class="w-6 h-6 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-lg flex items-center justify-center border border-emerald-500/20 shrink-0">
                                    <span class="text-[9px] font-bold text-emerald-400 uppercase">{{ substr($waiter->name, 0, 1) }}</span>
                                </div>
                                <span class="text-xs text-white/70 font-medium">{{ $waiter->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="p-8 text-center">
                    <p class="text-sm text-white/40">No waiters assigned to this branch.</p>
                    <a href="{{ route('manager.waiters.index') }}"
                       class="inline-block mt-3 text-xs text-emerald-400 hover:text-emerald-300 transition-colors font-medium">
                        Add waiters &rarr;
                    </a>
                </div>
            @endif
        </div>

    </div>

    {{-- Weekly Trend --}}
    @if(!empty($analytics['weekly_trend']))
        <div class="glass-card rounded-2xl overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-white/5">
                <h3 class="text-base font-bold text-white tracking-tight">Weekly Trend</h3>
                <p class="text-xs text-white/40 mt-0.5">Orders and revenue for the past 7 days</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left py-3 px-6 text-[10px] font-bold text-white/40 uppercase tracking-wider">Day</th>
                            <th class="text-right py-3 px-6 text-[10px] font-bold text-white/40 uppercase tracking-wider">Orders</th>
                            <th class="text-right py-3 px-6 text-[10px] font-bold text-white/40 uppercase tracking-wider">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($analytics['weekly_trend'] as $row)
                            @php
                                $dayLabel = $row['day'] ?? ($row['date'] ?? ($row['label'] ?? 'N/A'));
                                $ordersCount = $row['orders'] ?? ($row['order_count'] ?? 0);
                                $dayRevenue = $row['revenue'] ?? 0;
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-3 px-6 text-white/70 font-medium">{{ $dayLabel }}</td>
                                <td class="py-3 px-6 text-right">
                                    <span class="text-violet-400 font-semibold tabular-nums">{{ number_format($ordersCount) }}</span>
                                </td>
                                <td class="py-3 px-6 text-right">
                                    <span class="text-cyan-400 font-semibold tabular-nums">{{ $currencySymbol }} {{ number_format($dayRevenue) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(!empty($analytics))
        <div class="mb-8">
            @include('manager.partials.dashboard-analytics', ['analytics' => $analytics])
        </div>
    @endif

</x-manager-layout>
