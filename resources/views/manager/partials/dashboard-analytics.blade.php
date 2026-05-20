@php
    $weeklyTrend = $analytics['weekly_trend'] ?? [];
    $hourlyActivity = $analytics['hourly_activity'] ?? [];
    $statusCycle = $analytics['status_cycle'] ?? ['segments' => [], 'total' => 0];
    $weekComparison = $analytics['week_comparison'] ?? ['current' => 0, 'previous' => 0, 'change_pct' => 0, 'current_orders' => 0, 'previous_orders' => 0];
    $topMenuItems = $analytics['top_menu_items'] ?? [];
    $ratingHistogram = $analytics['rating_histogram'] ?? [];
    $insights = $analytics['insights'] ?? [];

    $maxWeeklyRevenue = max(collect($weeklyTrend)->max('revenue') ?: 1, 1);
    $maxWeeklyOrders = max(collect($weeklyTrend)->max('orders') ?: 1, 1);
    $maxHourlyOrders = max(collect($hourlyActivity)->max('orders') ?: 1, 1);
    $maxTopQty = max(collect($topMenuItems)->max('quantity') ?: 1, 1);
    $maxRatingCount = max(collect($ratingHistogram)->max('count') ?: 1, 1);

    $statusTotal = max((int) ($statusCycle['total'] ?? 0), 1);
    $conicParts = [];
    $cursor = 0;
    foreach ($statusCycle['segments'] ?? [] as $segment) {
        if ($segment['count'] <= 0) {
            continue;
        }
        $pct = ($segment['count'] / $statusTotal) * 100;
        $end = $cursor + $pct;
        $conicParts[] = $segment['color'].' '.$cursor.'% '.$end.'%';
        $cursor = $end;
    }
    $conicGradient = count($conicParts) > 0 ? implode(', ', $conicParts) : 'rgba(255,255,255,0.08) 0% 100%';

    $weekProgress = $weekComparison['previous'] > 0
        ? min(100, round(($weekComparison['current'] / $weekComparison['previous']) * 100))
        : ($weekComparison['current'] > 0 ? 100 : 0);
@endphp

<style>
    .analytics-shell {
        background: linear-gradient(135deg, rgba(17, 24, 39, 0.85) 0%, rgba(30, 27, 75, 0.55) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.45), inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }
    .analytics-bar {
        border-radius: 8px 8px 0 0;
        transform-origin: bottom;
        animation: analyticsBarGrow 0.9s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        transform: scaleY(0);
    }
    @keyframes analyticsBarGrow {
        from { opacity: 0; transform: scaleY(0); }
        to { opacity: 1; transform: scaleY(1); }
    }
    .cycle-ring {
        background: conic-gradient({{ $conicGradient }});
        box-shadow: 0 0 40px rgba(139, 92, 246, 0.25), inset 0 0 30px rgba(0, 0, 0, 0.35);
    }
    .cycle-ring::after {
        content: '';
        position: absolute;
        inset: 18%;
        border-radius: 9999px;
        background: #0f0a1e;
        box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.5);
    }
    .insight-pill[data-tone="violet"] { border-color: rgba(139, 92, 246, 0.35); background: rgba(139, 92, 246, 0.12); }
    .insight-pill[data-tone="cyan"] { border-color: rgba(6, 182, 212, 0.35); background: rgba(6, 182, 212, 0.12); }
    .insight-pill[data-tone="emerald"] { border-color: rgba(16, 185, 129, 0.35); background: rgba(16, 185, 129, 0.12); }
    .insight-pill[data-tone="amber"] { border-color: rgba(245, 158, 11, 0.35); background: rgba(245, 158, 11, 0.12); }
    .insight-pill[data-tone="rose"] { border-color: rgba(244, 63, 94, 0.35); background: rgba(244, 63, 94, 0.12); }
</style>

<section class="mb-10" aria-label="Restaurant analytics">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
        <div>
            <p class="text-[10px] font-bold text-violet-400 uppercase tracking-[0.2em] mb-1">Smart Analytics</p>
            <h3 class="text-2xl font-bold text-white tracking-tight">{{ $analytics['restaurant_name'] ?? 'Restaurant' }} Insights</h3>
            <p class="text-sm text-white/45 mt-1">7-day cycles, live pipeline, and performance histograms</p>
        </div>
        @if(count($insights) > 0)
            <div class="flex flex-wrap gap-2">
                @foreach($insights as $insight)
                    <div class="insight-pill px-3 py-2 rounded-xl border text-xs" data-tone="{{ $insight['tone'] }}">
                        <span class="text-white/50 block">{{ $insight['label'] }}</span>
                        <span class="font-semibold text-white">{{ $insight['value'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <div class="analytics-shell glass-card rounded-2xl p-6 xl:col-span-2">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h4 class="text-lg font-bold text-white">Revenue & Orders Cycle</h4>
                    <p class="text-xs text-white/40 mt-1">Last 7 days · dual histogram</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-white/40 uppercase tracking-wider">Week growth</p>
                    <p class="text-lg font-bold {{ ($weekComparison['change_pct'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ ($weekComparison['change_pct'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($weekComparison['change_pct'] ?? 0, 1) }}%
                    </p>
                </div>
            </div>
            <div class="h-64 flex items-end gap-2 sm:gap-3 border-b border-white/10 pb-3">
                @foreach($weeklyTrend as $index => $day)
                    @php
                        $revH = max(($day['revenue'] / $maxWeeklyRevenue) * 100, $day['revenue'] > 0 ? 6 : 2);
                        $ordH = max(($day['orders'] / $maxWeeklyOrders) * 55, $day['orders'] > 0 ? 8 : 0);
                    @endphp
                    <div class="flex-1 h-full flex flex-col justify-end items-center gap-1 min-w-[28px] group">
                        <div class="w-full flex items-end justify-center gap-0.5 h-[85%]">
                            <div class="analytics-bar w-[42%] relative bg-gradient-to-t from-violet-600 via-violet-500 to-cyan-400 opacity-90 group-hover:opacity-100"
                                 style="height: {{ $revH }}%; animation-delay: {{ $index * 0.06 }}s;"
                                 title="Tsh {{ number_format($day['revenue']) }}"></div>
                            <div class="analytics-bar w-[42%] relative bg-gradient-to-t from-amber-600/80 to-amber-400/90 group-hover:opacity-100"
                                 style="height: {{ $ordH }}%; animation-delay: {{ ($index * 0.06) + 0.03 }}s;"
                                 title="{{ $day['orders'] }} orders"></div>
                        </div>
                        <p class="text-[10px] font-bold text-white/70">{{ $day['day'] }}</p>
                        <p class="text-[9px] text-white/35">{{ $day['date'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-white/5 text-xs text-white/50">
                <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-gradient-to-t from-violet-600 to-cyan-400"></span> Revenue</span>
                <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-gradient-to-t from-amber-600 to-amber-400"></span> Orders</span>
            </div>
        </div>

        <div class="analytics-shell glass-card rounded-2xl p-6 flex flex-col">
            <h4 class="text-lg font-bold text-white mb-1">Order Pipeline</h4>
            <p class="text-xs text-white/40 mb-6">Today's status cycle</p>
            <div class="flex-1 flex flex-col items-center justify-center">
                <div class="relative w-44 h-44 cycle-ring rounded-full mb-6">
                    <div class="absolute inset-0 flex flex-col items-center justify-center z-10 text-center">
                        <span class="text-3xl font-black text-white">{{ $statusCycle['total'] ?? 0 }}</span>
                        <span class="text-[10px] uppercase tracking-wider text-white/40">orders</span>
                    </div>
                </div>
                <div class="w-full space-y-2">
                    @foreach($statusCycle['segments'] ?? [] as $segment)
                        @php $segPct = $statusTotal > 0 ? round(($segment['count'] / $statusTotal) * 100) : 0; @endphp
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="inline-flex items-center gap-2 text-white/70">
                                <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $segment['color'] }}"></span>
                                {{ $segment['label'] }}
                            </span>
                            <span class="font-semibold text-white">{{ $segment['count'] }} <span class="text-white/35">({{ $segPct }}%)</span></span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <div class="analytics-shell glass-card rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-1">Hourly Traffic</h4>
            <p class="text-xs text-white/40 mb-5">Orders histogram · today</p>
            <div class="h-48 flex items-end gap-1 border-b border-white/10 pb-2">
                @foreach($hourlyActivity as $index => $slot)
                    @php $h = max(($slot['orders'] / $maxHourlyOrders) * 100, $slot['orders'] > 0 ? 10 : 3); @endphp
                    <div class="flex-1 flex flex-col justify-end items-center group min-w-0">
                        <div class="analytics-bar w-full bg-gradient-to-t from-cyan-600 to-violet-500 rounded-t-md opacity-80 group-hover:opacity-100"
                             style="height: {{ $h }}%; animation-delay: {{ $index * 0.03 }}s;"
                             title="{{ $slot['orders'] }} orders"></div>
                        @if($index % 2 === 0)
                            <span class="text-[8px] text-white/35 mt-1 truncate w-full text-center">{{ substr($slot['label'], 0, 2) }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="analytics-shell glass-card rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-1">Top Menu Items</h4>
            <p class="text-xs text-white/40 mb-5">Best sellers · 7 days</p>
            <div class="space-y-3">
                @forelse($topMenuItems as $index => $item)
                    @php $barW = max(($item['quantity'] / $maxTopQty) * 100, $item['quantity'] > 0 ? 12 : 0); @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1 gap-2">
                            <span class="text-white/80 font-medium truncate">{{ $item['name'] }}</span>
                            <span class="text-violet-300 font-semibold shrink-0">{{ $item['quantity'] }}×</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-white/5 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-cyan-400 analytics-bar"
                                 style="width: {{ $barW }}%; animation-delay: {{ $index * 0.08 }}s;"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-white/40 text-center py-8">No menu sales yet this week</p>
                @endforelse
            </div>
        </div>

        <div class="analytics-shell glass-card rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-1">Customer Ratings</h4>
            <p class="text-xs text-white/40 mb-5">Feedback histogram</p>
            <div class="space-y-2.5 mb-6">
                @foreach($ratingHistogram as $index => $row)
                    @php $rW = max(($row['count'] / $maxRatingCount) * 100, $row['count'] > 0 ? 10 : 0); @endphp
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-amber-400 font-semibold w-8">{{ $row['stars'] }}★</span>
                        <div class="flex-1 h-3 rounded-full bg-white/5 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-400 analytics-bar"
                                 style="width: {{ $rW }}%; animation-delay: {{ $index * 0.07 }}s;"></div>
                        </div>
                        <span class="text-xs text-white/50 w-6 text-right">{{ $row['count'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="pt-4 border-t border-white/5">
                <p class="text-[10px] text-white/40 uppercase tracking-wider mb-2">This week vs last</p>
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-2 rounded-full bg-white/10 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-cyan-400" style="width: {{ min($weekProgress, 100) }}%"></div>
                    </div>
                    <span class="text-xs font-bold text-white">{{ $weekProgress }}%</span>
                </div>
                <p class="text-[11px] text-white/45 mt-2">
                    Tsh {{ number_format($weekComparison['current'] ?? 0) }} · {{ $weekComparison['current_orders'] ?? 0 }} orders
                </p>
            </div>
        </div>
    </div>
</section>
