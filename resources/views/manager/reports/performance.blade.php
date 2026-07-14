<x-manager-layout>
    <x-slot name="header">
        Performance Report
    </x-slot>

    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-3xl font-bold text-white tracking-tight">Performance Report</h2>
                <p class="text-sm font-medium text-white/40 uppercase tracking-wider">Restaurant & Waiter Performance Metrics</p>
            </div>
            <a href="{{ route('manager.reports.export-performance', ['period' => $period, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="bg-linear-to-r from-fin-primary to-fin-primary-dark text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg hover:shadow-fin-primary/25 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>
                </svg>
                Export CSV
            </a>
        </div>

        <!-- Date Range Filter -->
        <form method="GET" action="{{ route('manager.reports.performance') }}" class="glass p-6 rounded-2xl mb-8">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Period</label>
                    <select name="period" id="periodSelect" onchange="toggleCustomDates()" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white focus:ring-2 focus:ring-fin-primary focus:border-transparent transition-all">
                        <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                        <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div id="customDates" class="flex gap-4 flex-1 {{ $period === 'custom' ? '' : 'hidden' }}">
                    <div class="flex-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Start Date</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white focus:ring-2 focus:ring-fin-primary focus:border-transparent transition-all">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">End Date</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-medium text-white focus:ring-2 focus:ring-fin-primary focus:border-transparent transition-all">
                    </div>
                </div>
                <button type="submit" class="bg-linear-to-r from-fin-primary to-fin-primary-dark text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg hover:shadow-fin-primary/25 transition-all">
                    Apply Filter
                </button>
            </div>
        </form>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass-card p-6 rounded-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-violet-500/20 rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-fin-primary">
                            <path d="M3 3h18v18H3zM8 12h8M12 8v8"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-white mb-1">{{ number_format($totalOrders) }}</h3>
                <p class="text-sm text-white/40 font-medium uppercase tracking-wider">Total Orders</p>
            </div>

            <div class="glass-card p-6 rounded-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600">
                            <line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-white mb-1">{{ $currencySymbol }} {{ number_format($totalRevenue) }}</h3>
                <p class="text-sm text-white/40 font-medium uppercase tracking-wider">Total Revenue</p>
            </div>

            <div class="glass-card p-6 rounded-2xl">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-amber-600">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-3xl font-bold text-white mb-1">{{ $avgRating > 0 ? number_format($avgRating, 1) : 'N/A' }}</h3>
                <p class="text-sm text-white/40 font-medium uppercase tracking-wider">Average Rating</p>
            </div>
        </div>

        @php
            $waitTime = $waitTime ?? [];
            $waitTrend = $waitTrend ?? [];
            $maxWaitTrend = max(collect($waitTrend)->max('avg_to_served_minutes') ?: 1, 1);
        @endphp

        <!-- Customer wait-time -->
        <div class="mb-8">
            <div class="mb-4">
                <h3 class="text-xl font-bold text-white">Customer wait-time</h3>
                <p class="text-sm text-white/40">Order received → ready / served · from workflow timestamps</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div class="glass-card p-5 rounded-2xl">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Avg to served</p>
                    <p class="text-3xl font-bold text-cyan-300">{{ $waitTime['avg_to_served_minutes'] !== null ? number_format($waitTime['avg_to_served_minutes'], 1).'m' : '—' }}</p>
                    <p class="text-xs text-white/40 mt-1">n={{ $waitTime['sample_to_served'] ?? 0 }}</p>
                </div>
                <div class="glass-card p-5 rounded-2xl">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Median to served</p>
                    <p class="text-3xl font-bold text-white">{{ $waitTime['median_to_served_minutes'] !== null ? number_format($waitTime['median_to_served_minutes'], 1).'m' : '—' }}</p>
                </div>
                <div class="glass-card p-5 rounded-2xl">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Avg to ready</p>
                    <p class="text-3xl font-bold text-amber-300">{{ $waitTime['avg_to_ready_minutes'] !== null ? number_format($waitTime['avg_to_ready_minutes'], 1).'m' : '—' }}</p>
                    <p class="text-xs text-white/40 mt-1">n={{ $waitTime['sample_to_ready'] ?? 0 }}</p>
                </div>
                <div class="glass-card p-5 rounded-2xl">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Full cycle</p>
                    <p class="text-3xl font-bold text-violet-300">{{ $waitTime['avg_cycle_minutes'] !== null ? number_format($waitTime['avg_cycle_minutes'], 1).'m' : '—' }}</p>
                    <p class="text-xs text-white/40 mt-1">n={{ $waitTime['sample_cycle'] ?? 0 }}</p>
                </div>
            </div>

            @if(count($waitTrend) > 1)
            <div class="glass-card p-5 rounded-2xl mb-4">
                <p class="text-sm font-semibold text-white mb-3">Wait trend (avg to served)</p>
                <div class="flex items-end gap-2 h-28">
                    @foreach($waitTrend as $i => $day)
                        @php
                            $mins = $day['avg_to_served_minutes'];
                            $h = $mins !== null ? max(($mins / $maxWaitTrend) * 100, 8) : 4;
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end gap-1">
                            <span class="text-[10px] tabular-nums text-cyan-300/90">{{ $mins !== null ? number_format($mins, 0) : '—' }}</span>
                            <div class="w-full rounded-t-md {{ $mins !== null ? 'bg-gradient-to-t from-cyan-700 to-fin-primary' : 'bg-white/10' }}" style="height: {{ $h }}%"></div>
                            <span class="text-[9px] text-white/40">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($waitTime['bottlenecks']))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($waitTime['bottlenecks'] as $bn)
                    <div class="glass p-4 rounded-xl border {{ ($bn['severity'] ?? '') === 'critical' ? 'border-rose-500/40' : 'border-amber-500/30' }}">
                        <p class="text-sm font-bold text-white">{{ $bn['label'] }} bottleneck</p>
                        <p class="text-xs text-white/50 mt-1">Avg {{ number_format($bn['avg_minutes'], 1) }}m · threshold {{ $bn['threshold_minutes'] }}m</p>
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Top Performer Badge -->
        @if($topPerformer)
        <div class="glass-card p-6 rounded-2xl mb-8 border-2 border-violet-500/30">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-linear-to-br from-fin-primary to-fin-primary-dark rounded-2xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                        <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-xl font-bold text-white mb-1">Top Performer</h4>
                    <p class="text-white/60">{{ $topPerformer['name'] }} - {{ $topPerformer['orders_count'] }} orders handled
                        @if(($topPerformer['avg_to_served_minutes'] ?? null) !== null)
                            · avg wait {{ number_format($topPerformer['avg_to_served_minutes'], 1) }}m
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-fin-primary">{{ $currencySymbol }} {{ number_format($topPerformer['tips_earned']) }}</div>
                    <div class="text-sm text-white/40">Tips Earned</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Waiter Performance Table -->
        <div class="glass-card rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/5">
                <h3 class="text-xl font-bold text-white">Waiter Performance</h3>
                <p class="text-sm text-white/40">Orders, tips, ratings, and customer wait speed</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full" id="performanceTable">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left p-4 text-[10px] font-bold uppercase tracking-wider text-white/40">Waiter Name</th>
                            <th class="text-left p-4 text-[10px] font-bold uppercase tracking-wider text-white/40">Orders</th>
                            <th class="text-left p-4 text-[10px] font-bold uppercase tracking-wider text-white/40">Tips</th>
                            <th class="text-left p-4 text-[10px] font-bold uppercase tracking-wider text-white/40">Avg Rating</th>
                            <th class="text-left p-4 text-[10px] font-bold uppercase tracking-wider text-white/40">Avg to Ready</th>
                            <th class="text-left p-4 text-[10px] font-bold uppercase tracking-wider text-white/40">Avg Wait to Served</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waiterStats as $stat)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-all">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/5 rounded-full flex items-center justify-center border border-white/10">
                                        <span class="text-sm font-bold text-white/60">{{ substr($stat['name'], 0, 1) }}</span>
                                    </div>
                                    <span class="font-semibold text-white">{{ $stat['name'] }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-white">{{ number_format($stat['orders_count']) }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-emerald-600">{{ $currencySymbol }} {{ number_format($stat['tips_earned']) }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-amber-600">{{ $stat['avg_rating'] > 0 ? number_format($stat['avg_rating'], 1) : 'N/A' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-white/80">{{ $stat['avg_to_ready_minutes'] !== null ? number_format($stat['avg_to_ready_minutes'], 1).'m' : '—' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-semibold text-cyan-300">{{ $stat['avg_to_served_minutes'] !== null ? number_format($stat['avg_to_served_minutes'], 1).'m' : '—' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-white/40">
                                No waiter data available for this period
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleCustomDates() {
            const period = document.getElementById('periodSelect').value;
            const customDates = document.getElementById('customDates');
            if (period === 'custom') {
                customDates.classList.remove('hidden');
            } else {
                customDates.classList.add('hidden');
            }
        }
    </script>
</x-manager-layout>
