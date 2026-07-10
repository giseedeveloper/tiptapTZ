@php
    $comparisonBranches = $comparison['branches'] ?? collect();
    $highlights = $comparison['highlights'] ?? [];
    $combinedDaily = $comparison['combined_daily'] ?? [];
    $periodDays = (int) ($comparison['period_days'] ?? 7);
    $maxDailyOrders = max(collect($combinedDaily)->max('orders') ?: 1, 1);
    $maxDailyRevenue = max((float) (collect($combinedDaily)->max('revenue') ?: 1), 1.0);
@endphp

<section class="mb-10" aria-label="Branch comparison analytics">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
        <div>
            <p class="text-[10px] font-bold text-cyan-400 uppercase tracking-[0.2em] mb-1">Branch Intelligence</p>
            <h3 class="text-2xl font-bold text-white tracking-tight">Performance Comparison</h3>
            <p class="text-sm text-white/55 mt-1">Last {{ $periodDays }} days across all your branches</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('manager.branches.export', ['days' => $periodDays]) }}"
               class="glass px-4 py-2.5 rounded-xl text-xs font-semibold text-white/80 hover:text-white transition-all">
                Export CSV
            </a>
        </div>
    </div>

    @if(!empty($highlights['top_orders']) || !empty($highlights['needs_attention']))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            @if(!empty($highlights['top_orders']))
                <div class="glass-card rounded-2xl p-5 border border-violet-500/20 bg-violet-500/5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-violet-300/80">Top orders ({{ $periodDays }}d)</p>
                    <p class="text-lg font-bold text-white mt-1">{{ $highlights['top_orders']['name'] }}</p>
                    <p class="text-sm text-violet-300 tabular-nums mt-1">{{ number_format($highlights['top_orders']['orders_period']) }} orders</p>
                </div>
            @endif
            @if(!empty($highlights['top_revenue']))
                <div class="glass-card rounded-2xl p-5 border border-cyan-500/20 bg-cyan-500/5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-cyan-300/80">Top revenue ({{ $periodDays }}d)</p>
                    <p class="text-lg font-bold text-white mt-1">{{ $highlights['top_revenue']['name'] }}</p>
                    <p class="text-sm text-cyan-300 tabular-nums mt-1">{{ $currencySymbol }} {{ number_format($highlights['top_revenue']['revenue_period']) }}</p>
                </div>
            @endif
            @if(!empty($highlights['needs_attention']))
                <div class="glass-card rounded-2xl p-5 border border-rose-500/20 bg-rose-500/5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-rose-300/80">Needs attention</p>
                    <p class="text-lg font-bold text-white mt-1">{{ $highlights['needs_attention']['name'] }}</p>
                    <p class="text-sm text-rose-300 mt-1">{{ $highlights['needs_attention']['live_orders'] }} live orders right now</p>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
        <div class="glass-card rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-1">Orders by branch</h4>
            <p class="text-xs text-white/45 mb-5">Relative volume · {{ $periodDays }}-day window</p>
            <div class="space-y-4">
                @foreach($comparisonBranches as $row)
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-1.5">
                            <span class="text-sm font-semibold text-white truncate">{{ $row['name'] }}</span>
                            <span class="text-xs text-violet-300 tabular-nums font-bold">{{ number_format($row['orders_period']) }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-white/5 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-violet-600 to-purple-400" style="width: {{ $row['orders_bar_pct'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-1">Revenue by branch</h4>
            <p class="text-xs text-white/45 mb-5">Relative earnings · {{ $periodDays }}-day window</p>
            <div class="space-y-4">
                @foreach($comparisonBranches as $row)
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-1.5">
                            <span class="text-sm font-semibold text-white truncate">{{ $row['name'] }}</span>
                            <span class="text-xs text-cyan-300 tabular-nums font-bold">{{ $currencySymbol }} {{ number_format($row['revenue_period']) }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-white/5 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-600 to-blue-400" style="width: {{ $row['revenue_bar_pct'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if(count($combinedDaily) > 0)
        <div class="glass-card rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-1">Combined daily trend</h4>
            <p class="text-xs text-white/45 mb-5">All branches aggregated</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                @foreach($combinedDaily as $day)
                    <div class="glass rounded-xl p-3 text-center">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">{{ $day['day'] }}</p>
                        <p class="text-xs text-white/55 mb-2">{{ $day['date'] }}</p>
                        <p class="text-sm font-bold text-violet-300 tabular-nums">{{ number_format($day['orders']) }}</p>
                        <p class="text-[10px] text-white/35 mt-1">orders</p>
                        <p class="text-xs font-semibold text-cyan-300 tabular-nums mt-2">{{ $currencySymbol }}{{ number_format($day['revenue']) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</section>
