{{-- Live hero banner --}}
<div class="platform-hero rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
    <div class="platform-hero-glow platform-hero-glow--violet"></div>
    <div class="platform-hero-glow platform-hero-glow--pink"></div>
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="platform-live-dot"></span>
                <p class="text-[10px] font-black text-fin-primary uppercase tracking-[0.25em]">Live platform snapshot</p>
            </div>
            <p class="text-sm text-white/50 mb-1">Total revenue · <span id="platform-period-label">last 30 days</span></p>
            <p id="platform-hero-revenue" class="text-4xl md:text-5xl font-black text-white tabular-nums tracking-tight platform-hero-value">
                <span class="analysis-skeleton inline-block w-40 h-12 rounded-xl align-middle"></span>
            </p>
            <p class="text-xs text-white/40 mt-2 max-w-md">Anonymous overview — platform-wide totals only, no venue names.</p>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end" id="platform-hero-chips">
            @for ($i = 0; $i < 3; $i++)
                <span class="platform-chip analysis-skeleton w-28 h-8 rounded-full"></span>
            @endfor
        </div>
    </div>
</div>

{{-- KPI row --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6" id="snapshot-kpis">
    @for ($i = 0; $i < 4; $i++)
        <div class="platform-kpi-skeleton analysis-skeleton h-28 rounded-2xl"></div>
    @endfor
</div>

{{-- Charts --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <div class="xl:col-span-8 platform-panel platform-panel--revenue rounded-3xl p-6 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h4 class="text-base font-black text-white flex items-center gap-2">
                    <span class="platform-icon-badge platform-icon-badge--violet">📈</span>
                    Revenue pulse
                </h4>
                <p class="text-[11px] text-white/45 mt-1">{{ $currencyCode }} · daily · platform-wide</p>
            </div>
            <div class="flex flex-wrap gap-3" id="platform-revenue-stats">
                <div class="platform-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="platform-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="platform-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
            </div>
        </div>
        <div id="chart-revenue-trend" class="platform-revenue-chart h-56 flex items-end gap-1 border-b border-white/10 pb-3"></div>
    </div>

    <div class="xl:col-span-4 platform-panel platform-panel--venue rounded-3xl p-6 md:p-7 flex flex-col">
        <div class="mb-5">
            <h4 class="text-base font-black text-white flex items-center gap-2">
                <span class="platform-icon-badge platform-icon-badge--emerald">🏪</span>
                Venue health
            </h4>
            <p class="text-[11px] text-white/45 mt-1">Active vs inactive — counts only</p>
        </div>
        <div id="chart-restaurant-split" class="flex-1 flex items-center justify-center min-h-[10rem]"></div>
        <div id="platform-venue-pills" class="grid grid-cols-2 gap-3 mt-5 pt-5 border-t border-white/10">
            <div class="analysis-skeleton h-16 rounded-xl"></div>
            <div class="analysis-skeleton h-16 rounded-xl"></div>
        </div>
    </div>
</div>

{{-- Smart signals --}}
<div class="platform-panel rounded-3xl p-6 md:p-7 border border-white/10">
    <div class="flex items-center justify-between gap-4 mb-5">
        <div>
            <h4 class="text-base font-black text-white flex items-center gap-2">
                <span class="platform-icon-badge platform-icon-badge--amber">✨</span>
                Smart signals
            </h4>
            <p class="text-[11px] text-white/45 mt-1">Auto-generated from your platform data</p>
        </div>
        <span class="text-[10px] font-bold text-white/30 uppercase tracking-wider hidden sm:inline">AI-style insights</span>
    </div>
    <div id="platform-insights" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        @for ($i = 0; $i < 3; $i++)
            <div class="analysis-skeleton h-20 rounded-2xl"></div>
        @endfor
    </div>
</div>
