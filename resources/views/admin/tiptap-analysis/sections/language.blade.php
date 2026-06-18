{{-- Hero --}}
<div class="lg-hero rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
    <div class="lg-hero-glow lg-hero-glow--indigo"></div>
    <div class="lg-hero-glow lg-hero-glow--violet"></div>
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="lg-live-dot"></span>
                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-[0.25em]">Language and behavior intelligence</p>
            </div>
            <p class="text-sm text-white/50 mb-1">Bot language sessions · <span id="lg-period-label">last 30 days</span></p>
            <p id="lg-hero-sessions" class="text-4xl md:text-5xl font-black text-white tabular-nums tracking-tight lg-hero-value">
                <span class="analysis-skeleton inline-block w-32 h-12 rounded-xl align-middle"></span>
            </p>
            <p class="text-xs text-white/40 mt-2 max-w-md">Language preferences and peak hours — platform-wide counts only, no venue names.</p>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end" id="lg-hero-chips">
            @for ($i = 0; $i < 3; $i++)
                <span class="lg-chip analysis-skeleton w-32 h-8 rounded-full"></span>
            @endfor
        </div>
    </div>
</div>

{{-- KPI row --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6" id="lg-kpis">
    @for ($i = 0; $i < 4; $i++)
        <div class="analysis-skeleton h-28 rounded-2xl"></div>
    @endfor
</div>

{{-- Language split + top language --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <div class="xl:col-span-8 lg-panel lg-panel--langs rounded-3xl p-6 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h4 class="text-base font-black text-white flex items-center gap-2">
                    <span class="lg-icon-badge">🌐</span>
                    Language preference
                </h4>
                <p class="text-[11px] text-white/45 mt-1">How customers choose to interact with the bot</p>
            </div>
            <div class="flex flex-wrap gap-3" id="lg-lang-stats">
                <div class="lg-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="lg-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="lg-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
            </div>
        </div>
        <div id="lg-lang-bars" class="space-y-3 mb-8"></div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4" id="lg-lang-cards">
            @for ($i = 0; $i < 3; $i++)
                <div class="analysis-skeleton h-28 rounded-2xl"></div>
            @endfor
        </div>
    </div>

    <div class="xl:col-span-4 lg-panel lg-panel--spotlight rounded-3xl p-6 md:p-7 flex flex-col">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="lg-icon-badge">🏆</span>
            Top language
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Most selected bot language</p>
        <div id="lg-top-lang" class="lg-top-card flex-1 mb-5">
            <div class="analysis-skeleton h-32 rounded-2xl"></div>
        </div>
        <div id="chart-language" class="flex items-center justify-center"></div>
    </div>
</div>

{{-- Peak hours --}}
<div class="lg-panel rounded-3xl p-6 md:p-7 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h4 class="text-base font-black text-white flex items-center gap-2">
                <span class="lg-icon-badge">⏰</span>
                Peak activity hours
            </h4>
            <p class="text-[11px] text-white/45 mt-1">Bot events and sessions by hour of day — platform-wide</p>
        </div>
        <span id="lg-peak-total-label" class="text-xs font-bold text-indigo-400/80 tabular-nums"></span>
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
        <div class="xl:col-span-8">
            <p class="text-[10px] font-black text-white/40 uppercase tracking-wider mb-3">Bot events by hour</p>
            <div id="chart-peak-hours" class="lg-peak-chart-wrap"></div>
            <div id="lg-peak-legend" class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-white/10 text-[10px] text-white/40"></div>
        </div>
        <div class="xl:col-span-4">
            <p class="text-[10px] font-black text-white/40 uppercase tracking-wider mb-3">Session activity by hour</p>
            <div id="chart-peak-sessions" class="lg-peak-chart-wrap lg-peak-chart-wrap--compact"></div>
            <div class="grid grid-cols-2 gap-3 mt-5" id="lg-peak-cards">
                <div class="lg-peak-pill analysis-skeleton h-20 rounded-xl"></div>
                <div class="lg-peak-pill analysis-skeleton h-20 rounded-xl"></div>
            </div>
        </div>
    </div>
</div>

{{-- Data table + insights --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-7 lg-panel rounded-3xl p-6 md:p-7 overflow-x-auto">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-5">
            <span class="lg-icon-badge">📋</span>
            Data summary
        </h4>
        <div id="lg-data-table"></div>
    </div>
    <div class="xl:col-span-5 lg-panel rounded-3xl p-6 md:p-7">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="lg-icon-badge">✨</span>
            Smart signals
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Patterns from language and timing</p>
        <div id="lg-insights" class="space-y-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="analysis-skeleton h-20 rounded-2xl"></div>
            @endfor
        </div>
    </div>
</div>
