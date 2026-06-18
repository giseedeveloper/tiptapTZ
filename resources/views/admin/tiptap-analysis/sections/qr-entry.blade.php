{{-- Hero --}}
<div class="qr-hero rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
    <div class="qr-hero-glow qr-hero-glow--cyan"></div>
    <div class="qr-hero-glow qr-hero-glow--violet"></div>
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="qr-live-dot"></span>
                <p class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.25em]">QR entry intelligence</p>
            </div>
            <p class="text-sm text-white/50 mb-1">Total QR scans · <span id="qr-period-label">last 30 days</span></p>
            <p id="qr-hero-scans" class="text-4xl md:text-5xl font-black text-white tabular-nums tracking-tight qr-hero-value">
                <span class="analysis-skeleton inline-block w-32 h-12 rounded-xl align-middle"></span>
            </p>
            <p class="text-xs text-white/40 mt-2 max-w-md">How customers enter via WhatsApp — waiter, table & restaurant tag. Anonymous totals only.</p>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end" id="qr-hero-chips">
            @for ($i = 0; $i < 3; $i++)
                <span class="qr-chip analysis-skeleton w-32 h-8 rounded-full"></span>
            @endfor
        </div>
    </div>
</div>

{{-- KPI row --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6" id="qr-kpis">
    @for ($i = 0; $i < 4; $i++)
        <div class="analysis-skeleton h-28 rounded-2xl"></div>
    @endfor
</div>

{{-- Charts row --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <div class="xl:col-span-8 qr-panel qr-panel--trend rounded-3xl p-6 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-5">
            <div>
                <h4 class="text-base font-black text-white flex items-center gap-2">
                    <span class="qr-icon-badge">📈</span>
                    Daily scan activity
                </h4>
                <p class="text-[11px] text-white/45 mt-1">QR scans per day · platform-wide</p>
            </div>
            <div class="flex flex-wrap gap-3" id="qr-activity-stats">
                <div class="qr-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="qr-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                <div class="qr-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
            </div>
        </div>
        <div id="chart-qr-trend" class="qr-area-chart-wrap"></div>
        <div id="qr-trend-legend" class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-white/10 text-[10px] text-white/40"></div>
    </div>

    <div class="xl:col-span-4 qr-panel qr-panel--spotlight rounded-3xl p-6 md:p-7 flex flex-col">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="qr-icon-badge">🏆</span>
            Top entry point
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Most scanned QR type</p>
        <div id="qr-top-entry" class="qr-top-entry-card flex-1 mb-5">
            <div class="analysis-skeleton h-32 rounded-2xl"></div>
        </div>
        <div id="chart-qr-split" class="flex items-center justify-center"></div>
    </div>
</div>

{{-- Entry type cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6" id="qr-type-cards">
    @for ($i = 0; $i < 3; $i++)
        <div class="analysis-skeleton h-28 rounded-2xl"></div>
    @endfor
</div>

{{-- Breakdown + table + insights --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-7 qr-panel rounded-3xl p-6 md:p-7">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h4 class="text-base font-black text-white flex items-center gap-2">
                    <span class="qr-icon-badge">📊</span>
                    Entry point share
                </h4>
                <p class="text-[11px] text-white/45 mt-1">Waiter vs table vs restaurant tag</p>
            </div>
            <span id="qr-split-total-label" class="text-xs font-bold text-cyan-400/80 tabular-nums"></span>
        </div>
        <div id="qr-entry-bars" class="space-y-4">
            @for ($i = 0; $i < 3; $i++)
                <div class="analysis-skeleton h-14 rounded-xl"></div>
            @endfor
        </div>
    </div>

    <div class="xl:col-span-5 qr-panel rounded-3xl p-6 md:p-7">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-1">
            <span class="qr-icon-badge">✨</span>
            Smart signals
        </h4>
        <p class="text-[11px] text-white/45 mb-5">Patterns from QR usage</p>
        <div id="qr-insights" class="space-y-3 mb-6">
            @for ($i = 0; $i < 3; $i++)
                <div class="analysis-skeleton h-20 rounded-2xl"></div>
            @endfor
        </div>
        <h4 class="text-sm font-bold text-white mb-4">Data summary</h4>
        <div id="qr-entry-table" class="overflow-x-auto"></div>
    </div>
</div>
