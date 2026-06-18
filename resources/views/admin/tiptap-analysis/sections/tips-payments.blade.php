{{-- Hero --}}
<div class="tp-hero rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
    <div class="tp-hero-glow tp-hero-glow--pink"></div>
    <div class="tp-hero-glow tp-hero-glow--violet"></div>
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="tp-live-dot"></span>
                <p class="text-[10px] font-black text-pink-400 uppercase tracking-[0.25em]">Tips & payments intelligence</p>
            </div>
            <p class="text-sm text-white/50 mb-1">Platform money flow · <span id="tp-period-label">last 30 days</span></p>
            <p id="tp-hero-volume" class="text-4xl md:text-5xl font-black text-white tabular-nums tracking-tight tp-hero-value">
                <span class="analysis-skeleton inline-block w-40 h-12 rounded-xl align-middle"></span>
            </p>
            <p class="text-xs text-white/40 mt-2 max-w-md">Tips & payment totals — counts & methods only, no waiter or venue names.</p>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end" id="tp-hero-chips">
            @for ($i = 0; $i < 3; $i++)
                <span class="tp-chip analysis-skeleton w-32 h-8 rounded-full"></span>
            @endfor
        </div>
    </div>
</div>

{{-- KPI row --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6" id="tips-kpis">
    @for ($i = 0; $i < 4; $i++)
        <div class="analysis-skeleton h-28 rounded-2xl"></div>
    @endfor
</div>

{{-- Tips vs payments + top method --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <div class="xl:col-span-8 tp-panel tp-panel--flow rounded-3xl p-6 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h4 class="text-base font-black text-white flex items-center gap-2">
                    <span class="tp-icon-badge">💰</span>
                    Money flow split
                </h4>
                <p class="text-[11px] text-white/45 mt-1">Tips collected vs bill & quick payments</p>
            </div>
            <div class="flex flex-wrap gap-3" id="tp-flow-stats">
                <div class="tp-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="tp-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="tp-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
            </div>
        </div>
        <div id="tp-flow-bars" class="space-y-4"></div>
    </div>

    <div class="xl:col-span-4 tp-panel tp-panel--spotlight rounded-3xl p-6 md:p-7 flex flex-col">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="tp-icon-badge">🏆</span>
            Top method
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Most used payment channel</p>
        <div id="tp-top-method" class="tp-top-card flex-1 mb-5">
            <div class="analysis-skeleton h-32 rounded-2xl"></div>
        </div>
        <div id="chart-payment-methods" class="flex items-center justify-center"></div>
    </div>
</div>

{{-- Payment methods breakdown --}}
<div class="tp-panel rounded-3xl p-6 md:p-7 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h4 class="text-base font-black text-white flex items-center gap-2">
                <span class="tp-icon-badge">💳</span>
                Payment methods
            </h4>
            <p class="text-[11px] text-white/45 mt-1">Cash · USSD · Card — transaction counts & amounts</p>
        </div>
        <span id="tp-methods-total-label" class="text-xs font-bold text-pink-400/80 tabular-nums"></span>
    </div>
    <div id="tp-method-bars" class="space-y-3 mb-8">
        @for ($i = 0; $i < 3; $i++)
            <div class="analysis-skeleton h-12 rounded-xl"></div>
        @endfor
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="tp-method-cards">
        @for ($i = 0; $i < 3; $i++)
            <div class="analysis-skeleton h-28 rounded-2xl"></div>
        @endfor
    </div>
</div>

{{-- Payment purpose --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <div class="xl:col-span-7 tp-panel rounded-3xl p-6 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h4 class="text-base font-black text-white flex items-center gap-2">
                    <span class="tp-icon-badge">🧾</span>
                    Payment purpose
                </h4>
                <p class="text-[11px] text-white/45 mt-1">Bill payments vs quick pay — counts & volume</p>
            </div>
            <span id="tp-purpose-total-label" class="text-xs font-bold text-pink-400/80 tabular-nums"></span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8" id="tp-purpose-cards">
            @for ($i = 0; $i < 2; $i++)
                <div class="analysis-skeleton h-36 rounded-2xl"></div>
            @endfor
        </div>
        <div id="tp-purpose-bars" class="space-y-3"></div>
    </div>
    <div class="xl:col-span-5 tp-panel rounded-3xl p-6 md:p-7 flex flex-col items-center">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1 self-start w-full">
            <span class="tp-icon-badge">📊</span>
            Purpose split
        </h4>
        <p class="text-[11px] text-white/45 mb-6 self-start w-full">Bill vs quick share</p>
        <div id="chart-payment-purpose" class="w-full max-w-xs flex-1 flex items-center justify-center"></div>
    </div>
</div>

{{-- Data table + insights --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-7 tp-panel rounded-3xl p-6 md:p-7 overflow-x-auto">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-5">
            <span class="tp-icon-badge">📋</span>
            Data summary
        </h4>
        <div id="tp-data-table"></div>
    </div>
    <div class="xl:col-span-5 tp-panel rounded-3xl p-6 md:p-7">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="tp-icon-badge">✨</span>
            Smart signals
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Patterns from tips & payments</p>
        <div id="tp-insights" class="space-y-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="analysis-skeleton h-20 rounded-2xl"></div>
            @endfor
        </div>
    </div>
</div>
