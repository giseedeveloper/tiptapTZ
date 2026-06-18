{{-- Hero --}}
<div class="fb-hero rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
    <div class="fb-hero-glow fb-hero-glow--amber"></div>
    <div class="fb-hero-glow fb-hero-glow--rose"></div>
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="fb-live-dot"></span>
                <p class="text-[10px] font-black text-amber-400 uppercase tracking-[0.25em]">Customer satisfaction intelligence</p>
            </div>
            <p class="text-sm text-white/50 mb-1">Platform average rating · <span id="fb-period-label">last 30 days</span></p>
            <div id="fb-hero-rating" class="flex items-end gap-3">
                <p class="text-4xl md:text-5xl font-black text-white tabular-nums tracking-tight fb-hero-value">
                    <span class="analysis-skeleton inline-block w-24 h-12 rounded-xl"></span>
                </p>
                <div id="fb-hero-stars" class="pb-2 text-2xl"></div>
            </div>
            <p class="text-xs text-white/40 mt-2 max-w-md">Ratings & counts only — no comments, no restaurant names.</p>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end" id="fb-hero-chips">
            @for ($i = 0; $i < 3; $i++)
                <span class="fb-chip analysis-skeleton w-32 h-8 rounded-full"></span>
            @endfor
        </div>
    </div>
</div>

{{-- KPI row --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6" id="feedback-kpis">
    @for ($i = 0; $i < 4; $i++)
        <div class="analysis-skeleton h-28 rounded-2xl"></div>
    @endfor
</div>

{{-- Star distribution + satisfaction gauge --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <div class="xl:col-span-8 fb-panel fb-panel--stars rounded-3xl p-6 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h4 class="text-base font-black text-white flex items-center gap-2">
                    <span class="fb-icon-badge">⭐</span>
                    Star distribution
                </h4>
                <p class="text-[11px] text-white/45 mt-1">How customers rated — 1★ to 5★ breakdown</p>
            </div>
            <div class="flex flex-wrap gap-3" id="fb-star-stats">
                <div class="fb-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="fb-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="fb-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
            </div>
        </div>
        <div id="chart-rating-bars" class="space-y-3"></div>
    </div>

    <div class="xl:col-span-4 fb-panel fb-panel--gauge rounded-3xl p-6 md:p-7 flex flex-col items-center">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1 self-start w-full">
            <span class="fb-icon-badge">🎯</span>
            Satisfaction score
        </h4>
        <p class="text-[11px] text-white/45 mb-6 self-start w-full">Platform-wide average</p>
        <div id="fb-rating-gauge" class="flex-1 flex items-center justify-center w-full">
            <div class="analysis-skeleton w-36 h-36 rounded-full"></div>
        </div>
        <div id="fb-satisfaction-label" class="text-center mt-4 text-sm font-bold text-white/70"></div>
    </div>
</div>

{{-- Feedback by type --}}
<div class="fb-panel rounded-3xl p-6 md:p-7 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h4 class="text-base font-black text-white flex items-center gap-2">
                <span class="fb-icon-badge">💬</span>
                Feedback by category
            </h4>
            <p class="text-[11px] text-white/45 mt-1">Waiter · Food · Restaurant — counts & avg rating</p>
        </div>
        <span id="fb-type-total-label" class="text-xs font-bold text-amber-400/80 tabular-nums"></span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8" id="fb-type-cards">
        @for ($i = 0; $i < 3; $i++)
            <div class="analysis-skeleton h-32 rounded-2xl"></div>
        @endfor
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-center">
        <div class="xl:col-span-7">
            <div id="chart-feedback-by-type" class="space-y-4"></div>
        </div>
        <div class="xl:col-span-5 flex justify-center">
            <div id="chart-fb-type-donut" class="w-full max-w-xs"></div>
        </div>
    </div>
</div>

{{-- Data table + insights --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-7 fb-panel rounded-3xl p-6 md:p-7 overflow-x-auto">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-5">
            <span class="fb-icon-badge">📋</span>
            Data summary
        </h4>
        <div id="fb-data-table"></div>
    </div>
    <div class="xl:col-span-5 fb-panel rounded-3xl p-6 md:p-7">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="fb-icon-badge">✨</span>
            Smart signals
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Patterns from customer ratings</p>
        <div id="fb-insights" class="space-y-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="analysis-skeleton h-20 rounded-2xl"></div>
            @endfor
        </div>
    </div>
</div>
