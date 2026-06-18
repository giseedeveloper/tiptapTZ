{{-- Hero --}}
<div class="pl-hero rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
    <div class="pl-hero-glow pl-hero-glow--violet"></div>
    <div class="pl-hero-glow pl-hero-glow--emerald"></div>
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="pl-live-dot"></span>
                <p class="text-[10px] font-black text-fin-primary uppercase tracking-[0.25em]">Platform pulse intelligence</p>
            </div>
            <p class="text-sm text-white/50 mb-1">Venue health · <span id="pl-period-label">last 30 days</span></p>
            <p id="pl-hero-active" class="text-4xl md:text-5xl font-black text-white tabular-nums tracking-tight pl-hero-value">
                <span class="analysis-skeleton inline-block w-28 h-12 rounded-xl align-middle"></span>
            </p>
            <p id="pl-hero-sub" class="text-xs text-white/40 mt-2 max-w-md">Active venue share — platform totals only, no restaurant names.</p>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end" id="pl-hero-chips">
            @for ($i = 0; $i < 3; $i++)
                <span class="pl-chip analysis-skeleton w-32 h-8 rounded-full"></span>
            @endfor
        </div>
    </div>
</div>

{{-- KPI row --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6" id="pulse-kpis">
    @for ($i = 0; $i < 4; $i++)
        <div class="analysis-skeleton h-28 rounded-2xl"></div>
    @endfor
</div>

{{-- Venue health + orders momentum --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <div class="xl:col-span-4 pl-panel pl-panel--venue rounded-3xl p-6 md:p-7 flex flex-col">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="pl-icon-badge">🏪</span>
            Venue health
        </h4>
        <p class="text-[11px] text-white/45 mb-6">Active vs inactive — counts only</p>
        <div id="pulse-venue-ring" class="flex-1 flex items-center justify-center min-h-[10rem]">
            <div class="analysis-skeleton w-36 h-36 rounded-full"></div>
        </div>
        <div id="pulse-venue-pills" class="grid grid-cols-2 gap-3 mt-5 pt-5 border-t border-white/10">
            <div class="analysis-skeleton h-16 rounded-xl"></div>
            <div class="analysis-skeleton h-16 rounded-xl"></div>
        </div>
    </div>

    <div class="xl:col-span-8 pl-panel pl-panel--orders rounded-3xl p-6 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
            <div>
                <h4 class="text-base font-black text-white flex items-center gap-2">
                    <span class="pl-icon-badge">📦</span>
                    Order momentum
                </h4>
                <p class="text-[11px] text-white/45 mt-1">Today vs this month — platform-wide</p>
            </div>
            <div class="flex flex-wrap gap-3" id="pl-order-stats">
                <div class="pl-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="pl-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="pl-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
            </div>
        </div>
        <div id="pl-order-bars" class="space-y-4"></div>
    </div>
</div>

{{-- Activity mix --}}
<div class="pl-panel rounded-3xl p-6 md:p-7 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h4 class="text-base font-black text-white flex items-center gap-2">
                <span class="pl-icon-badge">📡</span>
                Platform activity mix
            </h4>
            <p class="text-[11px] text-white/45 mt-1">Bot events, QR scans, payments & feedback — relative volume</p>
        </div>
        <span id="pl-activity-total-label" class="text-xs font-bold text-fin-primary/80 tabular-nums"></span>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8" id="pl-activity-cards">
        @for ($i = 0; $i < 4; $i++)
            <div class="analysis-skeleton h-32 rounded-2xl"></div>
        @endfor
    </div>
    <div id="pl-activity-bars" class="space-y-3"></div>
</div>

{{-- Data table + insights --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-7 pl-panel rounded-3xl p-6 md:p-7 overflow-x-auto">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-5">
            <span class="pl-icon-badge">📋</span>
            Data summary
        </h4>
        <div id="pl-data-table"></div>
    </div>
    <div class="xl:col-span-5 pl-panel rounded-3xl p-6 md:p-7">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="pl-icon-badge">✨</span>
            Smart signals
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Live patterns from platform totals</p>
        <div id="pl-insights" class="space-y-3">
            @for ($i = 0; $i < 3; $i++)
                <div class="analysis-skeleton h-20 rounded-2xl"></div>
            @endfor
        </div>
    </div>
</div>
