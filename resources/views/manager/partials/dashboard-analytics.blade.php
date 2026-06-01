@php
    $weeklyTrend = $analytics['weekly_trend'] ?? [];
    $hourlyActivity = $analytics['hourly_activity'] ?? [];
    $statusCycle = $analytics['status_cycle'] ?? ['segments' => [], 'total' => 0];
    $weekComparison = $analytics['week_comparison'] ?? ['current' => 0, 'previous' => 0, 'change_pct' => 0, 'current_orders' => 0, 'previous_orders' => 0];
    $topMenuItems = $analytics['top_menu_items'] ?? [];
    $ratingHistogram = $analytics['rating_histogram'] ?? [];
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
    .analytics-bar-v {
        border-radius: 8px 8px 0 0;
        animation: analyticsBarFadeV 0.7s ease forwards;
        opacity: 0;
    }
    .analytics-bar-h {
        animation: analyticsBarFadeH 0.7s ease forwards;
        opacity: 0;
    }
    @keyframes analyticsBarFadeV {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes analyticsBarFadeH {
        from { opacity: 0; transform: translateX(-4px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .cycle-ring {
        background: conic-gradient({{ $conicGradient }});
        box-shadow: 0 0 40px rgba(140, 113, 246, 0.25), inset 0 0 30px rgba(0, 0, 0, 0.35);
    }
    .cycle-ring::after {
        content: '';
        position: absolute;
        inset: 18%;
        border-radius: 9999px;
        background: #12101c;
        box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.5);
    }
</style>

<section class="mb-10" aria-label="Restaurant analytics">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
        <div>
            <p class="text-[10px] font-bold text-fin-primary uppercase tracking-[0.2em] mb-1">Smart Analytics</p>
            <h3 class="text-2xl font-bold text-white tracking-tight">{{ $analytics['restaurant_name'] ?? 'Restaurant' }} Insights</h3>
            <p class="text-sm text-white/60 mt-1">7-day cycles, live pipeline, and performance histograms</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        <div class="analytics-shell glass-card rounded-2xl p-6 xl:col-span-2">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h4 class="text-lg font-bold text-white">Revenue & Orders Cycle</h4>
                    <p class="text-xs text-white/55 mt-1">Last 7 days · dual histogram</p>
                </div>
                <div class="text-right">
                    <p class="text-[11px] text-white/55 uppercase tracking-wider">Week growth</p>
                    <p id="week-growth-value" class="text-lg font-bold {{ ($weekComparison['change_pct'] ?? 0) >= 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        {{ ($weekComparison['change_pct'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($weekComparison['change_pct'] ?? 0, 1) }}%
                    </p>
                </div>
            </div>
            <div id="weekly-trend-chart" class="h-64 flex items-end gap-2 sm:gap-3 border-b border-white/10 pb-3">
                @foreach($weeklyTrend as $index => $day)
                    @php
                        $revH = max(($day['revenue'] / $maxWeeklyRevenue) * 100, $day['revenue'] > 0 ? 10 : 3);
                        $ordH = max(($day['orders'] / $maxWeeklyOrders) * 100, $day['orders'] > 0 ? 12 : 3);
                    @endphp
                    <div class="weekly-day flex-1 h-full flex flex-col justify-end items-center gap-1 min-w-[28px] group" data-index="{{ $index }}">
                        <div class="w-full flex items-end justify-center gap-0.5 h-[85%]">
                            <div class="weekly-rev-bar analytics-bar-v w-[42%] relative bg-gradient-to-t from-fin-primary-dark via-fin-primary to-fin-lavender opacity-90 group-hover:opacity-100"
                                 style="height: {{ $revH }}%; min-height: {{ $day['revenue'] > 0 ? '10px' : '3px' }}; animation-delay: {{ $index * 0.06 }}s;"
                                 title="{{ $currencySymbol }} {{ number_format($day['revenue']) }}"
                                 data-revenue="{{ $day['revenue'] }}"></div>
                            <div class="weekly-ord-bar analytics-bar-v w-[42%] relative bg-gradient-to-t from-amber-600/80 to-amber-400/90 group-hover:opacity-100"
                                 style="height: {{ $ordH }}%; min-height: {{ $day['orders'] > 0 ? '12px' : '3px' }}; animation-delay: {{ ($index * 0.06) + 0.03 }}s;"
                                 title="{{ $day['orders'] }} orders"
                                 data-orders="{{ $day['orders'] }}"></div>
                        </div>
                        <p class="text-[11px] font-semibold text-white/80">{{ $day['day'] }}</p>
                        <p class="text-[10px] text-white/50">{{ $day['date'] }}</p>
                    </div>
                @endforeach
            </div>
            <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-white/5 text-xs text-white/65">
                <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-gradient-to-t from-fin-primary-dark to-fin-primary"></span> Revenue</span>
                <span class="inline-flex items-center gap-2"><span class="w-3 h-3 rounded-sm bg-gradient-to-t from-amber-600 to-amber-400"></span> Orders</span>
            </div>
        </div>

        <div class="analytics-shell glass-card rounded-2xl p-6 flex flex-col">
            <h4 class="text-lg font-bold text-white mb-1">Order Pipeline</h4>
            <p class="text-xs text-white/55 mb-6">Today's status cycle</p>
            <div class="flex-1 flex flex-col items-center justify-center">
                <div class="relative w-44 h-44 cycle-ring rounded-full mb-6">
                    <div class="absolute inset-0 flex flex-col items-center justify-center z-10 text-center">
                        <span class="text-3xl font-black text-white">{{ $statusCycle['total'] ?? 0 }}</span>
                        <span class="text-[10px] uppercase tracking-wider text-white/55">orders</span>
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
            <p class="text-xs text-white/55 mb-4">Orders histogram · today (00:00–23:00)</p>
            <div class="overflow-x-auto -mx-1 px-1 pb-1">
                <div id="hourly-traffic-chart" class="h-52 flex items-end gap-1 sm:gap-1.5 border-b border-white/15 pb-3 min-w-[720px] sm:min-w-0">
                @foreach($hourlyActivity as $index => $slot)
                    @php $h = max(($slot['orders'] / $maxHourlyOrders) * 100, $slot['orders'] > 0 ? 12 : 4); @endphp
                    <div class="hourly-slot flex-1 min-w-[22px] flex flex-col justify-end items-center group" data-hour="{{ $slot['hour'] }}" data-orders="{{ $slot['orders'] }}">
                        @if($slot['orders'] > 0)
                            <span class="hourly-count text-[10px] font-bold text-fin-primary mb-1 tabular-nums">{{ $slot['orders'] }}</span>
                        @else
                            <span class="text-[10px] mb-1 opacity-0 select-none" aria-hidden="true">0</span>
                        @endif
                        <div class="hourly-bar analytics-bar-v w-full max-w-[28px] bg-gradient-to-t from-fin-primary-dark to-fin-primary rounded-t-md opacity-90 group-hover:opacity-100 min-h-[4px]"
                             style="height: {{ $h }}%; animation-delay: {{ $index * 0.03 }}s;"
                             title="{{ $slot['label'] }} · {{ $slot['orders'] }} orders"></div>
                        <span class="text-[10px] sm:text-[11px] font-medium text-white/70 mt-2 tabular-nums">{{ sprintf('%02d', (int) $slot['hour']) }}</span>
                    </div>
                @endforeach
                </div>
            </div>
        </div>

        <div class="analytics-shell glass-card rounded-2xl p-6">
            <h4 class="text-lg font-bold text-white mb-1">Top Menu Items</h4>
            <p class="text-xs text-white/55 mb-5">Best sellers · 7 days</p>
            <div class="space-y-3">
                @forelse($topMenuItems as $index => $item)
                    @php $barW = max(($item['quantity'] / $maxTopQty) * 100, $item['quantity'] > 0 ? 12 : 0); @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1 gap-2">
                            <span class="text-white/80 font-medium truncate">{{ $item['name'] }}</span>
                            <span class="text-fin-lavender font-semibold shrink-0">{{ $item['quantity'] }}×</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-surface-900/5 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-fin-primary to-fin-primary-dark analytics-bar-h"
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
            <p class="text-xs text-white/55 mb-5">Feedback histogram</p>
            <div class="space-y-2.5 mb-6">
                @foreach($ratingHistogram as $index => $row)
                    @php $rW = max(($row['count'] / $maxRatingCount) * 100, $row['count'] > 0 ? 10 : 0); @endphp
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-amber-400 font-semibold w-8">{{ $row['stars'] }}★</span>
                        <div class="flex-1 h-3 rounded-full bg-surface-900/5 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-400 analytics-bar-h"
                                 style="width: {{ $rW }}%; animation-delay: {{ $index * 0.07 }}s;"></div>
                        </div>
                        <span class="text-xs text-white/65 w-6 text-right tabular-nums">{{ $row['count'] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="pt-4 border-t border-white/5">
                <p class="text-[11px] text-white/55 uppercase tracking-wider mb-2">This week vs last</p>
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-2 rounded-full bg-surface-900/10 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-fin-primary-dark to-fin-primary" style="width: {{ min($weekProgress, 100) }}%"></div>
                    </div>
                    <span class="text-xs font-bold text-white">{{ $weekProgress }}%</span>
                </div>
                <p class="text-xs text-white/60 mt-2 tabular-nums">
                    {{ $currencySymbol }} {{ number_format($weekComparison['current'] ?? 0) }} · {{ $weekComparison['current_orders'] ?? 0 }} orders
                </p>
            </div>
        </div>
    </div>
</section>
