{{-- Hero --}}
<div class="jn-hero rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
    <div class="jn-hero-glow jn-hero-glow--violet"></div>
    <div class="jn-hero-glow jn-hero-glow--fuchsia"></div>
    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <span class="jn-live-dot"></span>
                <p class="text-[10px] font-black text-fuchsia-400 uppercase tracking-[0.25em]">Customer journey intelligence</p>
            </div>
            <p class="text-sm text-white/50 mb-1">End-to-end conversion · <span id="jn-period-label">last 30 days</span></p>
            <p id="jn-hero-conversion" class="text-4xl md:text-5xl font-black text-white tabular-nums tracking-tight jn-hero-value">
                <span class="analysis-skeleton inline-block w-28 h-12 rounded-xl align-middle"></span>
            </p>
            <p class="text-xs text-white/40 mt-2 max-w-md">QR scan → menu → order → payment — anonymous funnel, no venue names.</p>
        </div>
        <div class="flex flex-wrap gap-2 lg:justify-end" id="jn-hero-chips">
            @for ($i = 0; $i < 3; $i++)
                <span class="jn-chip analysis-skeleton w-32 h-8 rounded-full"></span>
            @endfor
        </div>
    </div>
</div>

{{-- KPI row --}}
<div id="funnel-kpis" class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    @for ($i = 0; $i < 4; $i++)
        <div class="analysis-skeleton h-28 rounded-2xl"></div>
    @endfor
</div>

{{-- ★ Conversion pipeline (main feature) --}}
<div class="jn-panel jn-pipeline-panel rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
    <div class="jn-pipeline-bg"></div>
    <div class="relative z-10">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
            <div>
                <h4 class="text-lg md:text-xl font-black text-white flex items-center gap-2">
                    <span class="jn-icon-badge">🛤️</span>
                    Conversion pipeline
                </h4>
                <p class="text-[11px] text-white/45 mt-1 max-w-lg">Watch customers flow from QR scan to successful payment — width shows retention at each step.</p>
            </div>
            <div class="flex items-center gap-5">
                <div id="jn-conversion-ring" class="jn-conversion-ring shrink-0">
                    <div class="analysis-skeleton w-24 h-24 rounded-full"></div>
                </div>
                <div class="flex flex-wrap gap-3" id="jn-pipeline-stats">
                    <div class="jn-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                    <div class="jn-stat-pill analysis-skeleton w-24 h-14 rounded-xl"></div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start">
            {{-- Visual funnel --}}
            <div class="xl:col-span-5">
                <p class="text-[10px] font-black text-white/35 uppercase tracking-widest mb-4 text-center xl:text-left">Funnel shape</p>
                <div id="jn-funnel-visual" class="jn-funnel-visual flex justify-center"></div>
            </div>

            {{-- Step flow timeline --}}
            <div class="xl:col-span-7">
                <p class="text-[10px] font-black text-white/35 uppercase tracking-widest mb-4">Step-by-step flow</p>
                <div id="chart-funnel" class="overflow-x-auto pb-1">
                    <div id="chart-funnel-inner" class="jn-flow-track"></div>
                </div>
            </div>
        </div>

        <div id="funnel-dropoff-banner" class="mt-8 rounded-2xl border px-5 py-4 text-sm hidden"></div>

        {{-- Step comparison bars --}}
        <div class="mt-8 pt-8 border-t border-white/10">
            <div class="flex items-center justify-between gap-4 mb-5">
                <h5 class="text-sm font-black text-white">Retention at each step</h5>
                <span id="funnel-overall-badge" class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full border border-white/10 text-white/50"></span>
            </div>
            <div id="jn-step-bars" class="space-y-3">
                @for ($i = 0; $i < 4; $i++)
                    <div class="analysis-skeleton h-11 rounded-xl"></div>
                @endfor
            </div>
        </div>
    </div>
</div>

{{-- Summary + payments + insights --}}
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-7 jn-panel rounded-3xl p-6 md:p-7">
        <h4 class="text-base font-black text-white flex items-center gap-2 mb-5">
            <span class="jn-icon-badge">📋</span>
            Journey data summary
        </h4>
        <div id="jn-journey-table" class="overflow-x-auto"></div>
    </div>
    <div class="xl:col-span-5 flex flex-col gap-6">
        <div class="jn-panel rounded-3xl p-6 md:p-7">
            <h4 class="text-base font-black text-white mb-1">Journey summary</h4>
            <p class="text-[11px] text-white/45 mb-5">Key funnel metrics</p>
            <div id="funnel-summary-stats" class="space-y-3"></div>
        </div>
        <div class="jn-panel jn-panel--pay rounded-3xl p-6 md:p-7 flex-1">
            <h4 class="text-base font-black text-white mb-1">How they paid</h4>
            <p class="text-[11px] text-white/45 mb-4">Cash vs digital in period</p>
            <div id="chart-funnel-payments"></div>
        </div>
        <div class="jn-panel rounded-3xl p-6 md:p-7">
            <h4 class="text-base font-black text-white mb-4 flex items-center gap-2">
                <span class="jn-icon-badge">✨</span>
                Smart signals
            </h4>
            <div id="jn-insights" class="space-y-3">
                @for ($i = 0; $i < 2; $i++)
                    <div class="analysis-skeleton h-20 rounded-2xl"></div>
                @endfor
            </div>
        </div>
    </div>
</div>
