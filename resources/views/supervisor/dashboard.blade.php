<x-supervisor-layout>
    <x-slot name="header">Floor Dashboard &mdash; {{ $zone->name ?? 'All Floors' }}</x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
            <p class="text-sm font-medium text-emerald-100/90">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Page Heading --}}
    <div class="mb-8">
        <div class="flex flex-wrap items-center gap-3 mb-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">
                Floor Dashboard
            </h1>
            @if($zone)
                <span class="px-3 py-1.5 bg-cyan-500/10 text-cyan-400 text-[10px] font-bold rounded-full uppercase tracking-wider border border-cyan-500/20">
                    {{ $zone->name }}
                </span>
            @else
                <span class="px-3 py-1.5 bg-white/10 text-white/60 text-[10px] font-bold rounded-full uppercase tracking-wider border border-white/10">
                    All Floors
                </span>
            @endif
        </div>
        <p class="text-sm text-white/40 uppercase tracking-wide">
            @if($zone)
                Monitoring zone: <span class="text-white/65">{{ $zone->name }}</span>
            @else
                Monitoring all zones &amp; floors
            @endif
        </p>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        {{-- Live Orders --}}
        <div class="glass-card rounded-2xl p-6 card-hover relative overflow-hidden group">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br from-rose-500/20 to-rose-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-5">
                    <div class="w-12 h-12 bg-gradient-to-br from-rose-500/20 to-pink-500/20 rounded-xl flex items-center justify-center border border-rose-500/20 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-400">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                    </div>
                    <span class="px-3 py-1.5 bg-rose-500/10 text-rose-400 text-[10px] font-bold rounded-full uppercase tracking-wider border border-rose-500/20 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-rose-400 rounded-full animate-pulse"></span>
                        Live
                    </span>
                </div>
                <p class="text-xs font-semibold text-white/65 uppercase tracking-wider mb-1">Live Orders</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums">{{ number_format($stats['live_orders']) }}</h3>
            </div>
        </div>

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

        {{-- Revenue Today --}}
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

        {{-- Active Waiters --}}
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
                        Active
                    </span>
                </div>
                <p class="text-xs font-semibold text-white/65 uppercase tracking-wider mb-1">Active Waiters</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums">{{ $stats['waiters_active'] }}</h3>
            </div>
        </div>

    </div>

    {{-- Live Orders Table --}}
    <div class="glass-card rounded-2xl overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white tracking-tight">Live Orders</h2>
                <p class="text-xs text-white/40 mt-0.5">{{ $liveOrders->count() }} active {{ Str::plural('order', $liveOrders->count()) }}</p>
            </div>
            <button onclick="window.location.reload()"
                    class="glass px-4 py-2 rounded-xl font-semibold text-white/70 hover:text-white transition-all text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/>
                </svg>
                Refresh
            </button>
        </div>

        @if($liveOrders->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Table</th>
                            <th class="text-left py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider hidden sm:table-cell">Customer</th>
                            <th class="text-left py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Items</th>
                            <th class="text-left py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Status</th>
                            <th class="text-left py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider hidden md:table-cell">Waiter</th>
                            <th class="text-right py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($liveOrders as $order)
                            @php
                                $statusColor = match($order->status ?? 'pending') {
                                    'pending'   => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                    'preparing' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'served'    => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'ready'     => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                                    default     => 'bg-white/10 text-white/50 border-white/10',
                                };
                                $itemNames = $order->items
                                    ->map(fn($i) => $i->menuItem?->name ?? $i->name ?? 'Item')
                                    ->take(3)
                                    ->implode(', ');
                                $extraCount = max(0, $order->items->count() - 3);
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                {{-- Table --}}
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-gradient-to-br from-violet-500/20 to-purple-500/20 rounded-lg flex items-center justify-center border border-violet-500/20 shrink-0">
                                            <span class="text-[10px] font-bold text-violet-400">{{ $order->table_number ?? '?' }}</span>
                                        </div>
                                        <span class="font-semibold text-white text-sm">#{{ $order->table_number ?? '?' }}</span>
                                    </div>
                                </td>

                                {{-- Customer --}}
                                <td class="py-3 px-4 text-white/70 hidden sm:table-cell">
                                    {{ $order->customer_name ?? 'Guest' }}
                                </td>

                                {{-- Items --}}
                                <td class="py-3 px-4 text-white/70 max-w-[180px]">
                                    <span class="truncate block">{{ $itemNames }}</span>
                                    @if($extraCount > 0)
                                        <span class="text-[10px] text-white/40">+{{ $extraCount }} more</span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase tracking-wider border {{ $statusColor }}">
                                        {{ ucfirst($order->status ?? 'pending') }}
                                    </span>
                                </td>

                                {{-- Waiter --}}
                                <td class="py-3 px-4 text-white/70 hidden md:table-cell">
                                    @if($order->waiter)
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-md flex items-center justify-center border border-emerald-500/20 shrink-0">
                                                <span class="text-[9px] font-bold text-emerald-400 uppercase">{{ substr($order->waiter->name, 0, 1) }}</span>
                                            </div>
                                            <span class="text-sm">{{ $order->waiter->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-white/30 italic text-xs">Unassigned</span>
                                    @endif
                                </td>

                                {{-- Time --}}
                                <td class="py-3 px-4 text-right text-white/40 text-xs whitespace-nowrap">
                                    {{ $order->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-400">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-white mb-1">All Clear</h3>
                <p class="text-sm text-white/40">No active orders right now. The floor is quiet.</p>
            </div>
        @endif
    </div>

    {{-- Waiters on Duty --}}
    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-xl font-bold text-white tracking-tight">Waiters on Duty</h2>
            <p class="text-xs text-white/40 mt-0.5">{{ $waiters->count() }} {{ Str::plural('waiter', $waiters->count()) }} in this zone</p>
        </div>

        @if($waiters->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Name</th>
                            <th class="text-left py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Status</th>
                            <th class="text-right py-3 px-4 text-[10px] font-bold text-white/40 uppercase tracking-wider">Tables Assigned</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($waiters as $waiter)
                            @php
                                $isOnline = $waiter->is_online ?? false;
                                $tableCount = $waiter->tables?->count() ?? 0;
                            @endphp
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                {{-- Name --}}
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl flex items-center justify-center border border-emerald-500/20 shrink-0">
                                            <span class="text-xs font-bold text-emerald-400 uppercase">{{ substr($waiter->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-white text-sm">{{ $waiter->name }}</p>
                                            <p class="text-[11px] text-white/35 truncate">{{ $waiter->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="py-3 px-4">
                                    @if($isOnline)
                                        <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-400 text-[10px] font-bold rounded-full uppercase tracking-wider border border-emerald-500/20 flex items-center gap-1 w-fit">
                                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse shrink-0"></span>
                                            Online
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-white/5 text-white/35 text-[10px] font-bold rounded-full uppercase tracking-wider border border-white/10">
                                            Offline
                                        </span>
                                    @endif
                                </td>

                                {{-- Tables --}}
                                <td class="py-3 px-4 text-right">
                                    @if($tableCount > 0)
                                        <span class="text-white/70 font-semibold tabular-nums">{{ $tableCount }}</span>
                                        <span class="text-white/30 text-xs ml-1">{{ Str::plural('table', $tableCount) }}</span>
                                    @else
                                        <span class="text-white/25 text-xs italic">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-10 text-center">
                <p class="text-sm text-white/40">No waiters currently assigned to this zone.</p>
            </div>
        @endif
    </div>

</x-supervisor-layout>
