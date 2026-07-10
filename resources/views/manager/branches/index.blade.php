<x-manager-layout>
    <x-slot name="header">My Branches</x-slot>

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

    {{-- Info Banner --}}
    <div class="mb-8 p-4 bg-cyan-500/10 border border-cyan-500/20 rounded-2xl flex items-center gap-4">
        <div class="w-10 h-10 bg-cyan-500/20 rounded-xl flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-cyan-400">
                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-cyan-100/90">
            You are viewing all <span class="font-bold text-cyan-300">{{ $totals['branches'] }}</span>
            {{ Str::plural('branch', $totals['branches']) }}. Select a branch to manage it.
        </p>
    </div>

    {{-- Aggregate Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

        {{-- Total Orders Today --}}
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
                <p class="text-xs font-semibold text-white/65 uppercase tracking-wider mb-1">Total Orders Today</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums">{{ number_format($totals['orders_today']) }}</h3>
            </div>
        </div>

        {{-- Total Revenue --}}
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
                <p class="text-xs font-semibold text-white/65 uppercase tracking-wider mb-1">Total Revenue Today</p>
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums break-all sm:break-normal">
                    {{ $currencySymbol }} {{ number_format($totals['revenue_today']) }}
                </h3>
            </div>
        </div>

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
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums">{{ number_format($totals['live_orders']) }}</h3>
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
                <h3 class="text-2xl sm:text-3xl font-bold text-white tracking-tight tabular-nums">{{ number_format($totals['waiters_online']) }} Active</h3>
            </div>
        </div>

    </div>

    @if(!empty($comparison))
        @include('manager.partials.branch-comparison', ['comparison' => $comparison])
    @endif

    {{-- Branch Cards Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">All Branches</h2>
            <p class="text-xs text-white/40 uppercase tracking-wide mt-0.5">
                {{ $totals['branches'] }} {{ Str::plural('branch', $totals['branches']) }} managed
            </p>
        </div>
        <a href="{{ route('manager.branches.create') }}"
           class="bg-gradient-to-r from-violet-600 to-cyan-600 text-white px-5 py-2.5 rounded-xl font-semibold hover:shadow-lg hover:shadow-violet-500/25 transition-all flex items-center gap-2 text-sm shrink-0 self-start sm:self-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14"/><path d="M12 5v14"/>
            </svg>
            Add Branch
        </a>
    </div>

    {{-- Branch Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($branches as $item)
            @php
                $restaurant = $item['restaurant'];
            @endphp
            <div class="glass-card rounded-2xl p-6 card-hover relative overflow-hidden group flex flex-col">
                <div class="absolute -top-8 -right-8 w-28 h-28 bg-gradient-to-br from-violet-500/10 to-cyan-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-500 pointer-events-none"></div>

                <div class="relative z-10 flex-1 flex flex-col">
                    {{-- Branch Header --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-violet-500/20 to-cyan-500/20 rounded-xl flex items-center justify-center shrink-0 border border-violet-500/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-violet-400">
                                    <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-white text-sm leading-tight truncate">{{ $restaurant->displayName() }}</h3>
                                @if($restaurant->location)
                                    <p class="text-[11px] text-white/45 mt-0.5 flex items-center gap-1 truncate">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                                        </svg>
                                        {{ $restaurant->location }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if($item['live_orders'] > 0)
                            <span class="px-2.5 py-1 bg-rose-500/10 text-rose-400 text-[10px] font-bold rounded-full uppercase tracking-wider border border-rose-500/20 flex items-center gap-1 shrink-0 ml-2">
                                <span class="w-1.5 h-1.5 bg-rose-400 rounded-full animate-pulse"></span>
                                {{ $item['live_orders'] }} Live
                            </span>
                        @else
                            <span class="px-2.5 py-1 bg-white/5 text-white/30 text-[10px] font-bold rounded-full uppercase tracking-wider border border-white/10 shrink-0 ml-2">Idle</span>
                        @endif
                    </div>

                    {{-- Branch Stats --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="glass rounded-xl p-3">
                            <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider mb-1">Orders</p>
                            <p class="text-lg font-bold text-violet-400 tabular-nums">{{ number_format($item['orders_today']) }}</p>
                        </div>
                        <div class="glass rounded-xl p-3">
                            <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider mb-1">Revenue</p>
                            <p class="text-lg font-bold text-cyan-400 tabular-nums truncate">{{ $currencySymbol }} {{ number_format($item['revenue_today']) }}</p>
                        </div>
                        <div class="glass rounded-xl p-3">
                            <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider mb-1">Rating</p>
                            <p class="text-lg font-bold text-amber-400 tabular-nums">&#9733; {{ number_format($item['avg_rating'], 1) }}</p>
                        </div>
                        <div class="glass rounded-xl p-3">
                            <p class="text-[10px] font-semibold text-white/40 uppercase tracking-wider mb-1">Waiters</p>
                            <p class="text-lg font-bold text-emerald-400 tabular-nums">{{ $item['waiters_online'] }} on</p>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-2 mt-auto pt-2">
                        <form method="POST" action="{{ route('manager.switch-branch') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="branch_id" value="{{ $restaurant->id }}">
                            <button type="submit"
                                    class="w-full bg-gradient-to-r from-violet-600 to-cyan-600 text-white px-4 py-2.5 rounded-xl font-semibold hover:shadow-lg hover:shadow-violet-500/25 transition-all text-xs text-center flex items-center justify-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6"/>
                                </svg>
                                Switch to Branch
                            </button>
                        </form>
                        <a href="{{ route('manager.branches.show', $restaurant) }}"
                           class="glass px-4 py-2.5 rounded-xl font-semibold text-white/70 hover:text-white transition-all text-xs text-center flex items-center gap-1.5 shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            Details
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card rounded-2xl p-12 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-violet-500/20 to-cyan-500/20 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-violet-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-violet-400">
                        <path d="M20 7H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">No branches yet</h3>
                <p class="text-sm text-white/50 mb-6">Create your first branch to start managing your restaurant group.</p>
                <a href="{{ route('manager.branches.create') }}"
                   class="inline-flex items-center gap-2 bg-gradient-to-r from-violet-600 to-cyan-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg hover:shadow-violet-500/25 transition-all text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="M12 5v14"/>
                    </svg>
                    Create First Branch
                </a>
            </div>
        @endforelse
    </div>

</x-manager-layout>
