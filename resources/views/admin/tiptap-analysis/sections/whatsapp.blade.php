{{-- Hero --}}
<div class="wa-hero rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
    <div class="wa-hero-glow wa-hero-glow--emerald"></div>
    <div class="wa-hero-glow wa-hero-glow--teal"></div>
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="wa-live-dot"></span>
                <p class="text-[10px] font-black text-emerald-400 uppercase tracking-[0.25em]">WhatsApp bot intelligence</p>
            </div>
            <p class="text-sm text-white/50 mb-1">Total bot interactions · <span id="wa-period-label">last 30 days</span></p>
            <p id="wa-hero-events" class="text-4xl md:text-5xl font-black text-white tabular-nums tracking-tight wa-hero-value">
                <span class="analysis-skeleton inline-block w-32 h-12 rounded-xl align-middle"></span>
            </p>
            <p class="text-xs text-white/40 mt-2 max-w-md">Menu taps & bot actions — platform-wide, anonymous counts only.</p>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end" id="wa-hero-chips">
            @for ($i = 0; $i < 3; $i++)
                <span class="wa-chip analysis-skeleton w-32 h-8 rounded-full"></span>
            @endfor
        </div>
    </div>
</div>

{{-- KPI row --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6" id="wa-kpis">
    @for ($i = 0; $i < 4; $i++)
        <div class="analysis-skeleton h-28 rounded-2xl"></div>
    @endfor
</div>

{{-- Charts row --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <div class="xl:col-span-8 wa-panel wa-panel--activity rounded-3xl p-6 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5">
            <div>
                <h4 class="text-base font-black text-white flex items-center gap-2">
                    <span class="wa-icon-badge">📊</span>
                    Daily bot activity
                </h4>
                <p class="text-[11px] text-white/45 mt-1">Engagement events per day · platform-wide</p>
            </div>
            <div class="flex flex-wrap gap-3" id="wa-activity-stats">
                <div class="wa-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="wa-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="wa-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
            </div>
        </div>
        <div id="chart-wa-trend" class="wa-area-chart-wrap"></div>
        <div id="wa-trend-legend" class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-white/10 text-[10px] text-white/40"></div>
    </div>

    <div class="xl:col-span-4 wa-panel wa-panel--spotlight rounded-3xl p-6 md:p-7 flex flex-col">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="wa-icon-badge">🏆</span>
            Top action
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Most used menu option</p>
        <div id="wa-top-action" class="wa-top-action-card flex-1 mb-5">
            <div class="analysis-skeleton h-32 rounded-2xl"></div>
        </div>
        <div id="chart-wa-options" class="flex items-center justify-center"></div>
    </div>
</div>

{{-- Menu breakdown --}}
<div class="wa-panel rounded-3xl p-6 md:p-7 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h4 class="text-base font-black text-white flex items-center gap-2">
                <span class="wa-icon-badge">💬</span>
                Menu option breakdown
            </h4>
            <p class="text-[11px] text-white/45 mt-1">Share of each bot action · sorted by usage</p>
        </div>
        <span id="wa-options-total-label" class="text-xs font-bold text-emerald-400/80 tabular-nums"></span>
    </div>
    <div id="wa-options-bars" class="space-y-3">
        @for ($i = 0; $i < 5; $i++)
            <div class="analysis-skeleton h-12 rounded-xl"></div>
        @endfor
    </div>
</div>

{{-- Formatted data grid + insights --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-7 wa-panel rounded-3xl p-6 md:p-7 overflow-x-auto">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-5">
            <span class="wa-icon-badge">📋</span>
            Data summary
        </h4>
        <div id="wa-options-table"></div>
    </div>
    <div class="xl:col-span-5 wa-panel rounded-3xl p-6 md:p-7">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="wa-icon-badge">✨</span>
            Smart signals
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Patterns from bot usage</p>
        <div id="wa-insights" class="space-y-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="analysis-skeleton h-20 rounded-2xl"></div>
            @endfor
        </div>
    </div>
</div>
