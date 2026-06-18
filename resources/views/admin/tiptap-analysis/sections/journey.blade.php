<div id="funnel-kpis" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="analysis-skeleton h-20 rounded-2xl"></div>
    <div class="analysis-skeleton h-20 rounded-2xl"></div>
    <div class="analysis-skeleton h-20 rounded-2xl"></div>
</div>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-8 analytics-shell glass-card rounded-2xl p-5 sm:p-6 border border-violet-500/25 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-violet-500/8 to-transparent pointer-events-none"></div>
        <div class="relative">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
                <div>
                    <h4 class="text-sm font-bold text-white">Conversion pipeline</h4>
                    <p class="text-[10px] text-white/40 mt-0.5">Bar width = % of customers who started (QR scan)</p>
                </div>
                <span id="funnel-overall-badge" class="text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full bg-white/5 border border-white/10 text-white/60"></span>
            </div>
            <div id="chart-funnel" class="overflow-x-auto pb-1 -mx-1 px-1">
                <div id="chart-funnel-inner" class="journey-pipeline min-w-max lg:min-w-0"></div>
            </div>
            <div id="funnel-dropoff-banner" class="mt-5 rounded-xl border px-4 py-3 text-sm hidden"></div>
        </div>
    </div>
    <div class="xl:col-span-4 flex flex-col gap-4">
        <div class="glass-card rounded-2xl p-5 border border-white/10">
            <h4 class="text-[10px] font-bold text-white/40 uppercase tracking-wider mb-3">Journey summary</h4>
            <div id="funnel-summary-stats" class="space-y-3"></div>
        </div>
        <div class="glass-card rounded-2xl p-5 border border-emerald-500/20 flex-1">
            <h4 class="text-sm font-bold text-white mb-1">How they paid</h4>
            <p class="text-[10px] text-white/40 mb-4">Cash vs digital in this period</p>
            <div id="chart-funnel-payments"></div>
        </div>
    </div>
</div>
