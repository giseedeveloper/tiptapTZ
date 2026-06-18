<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="snapshot-kpis">
    @for ($i = 0; $i < 4; $i++)
        <div class="analysis-skeleton h-20 rounded-2xl"></div>
    @endfor
</div>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-7 analytics-shell glass-card rounded-2xl p-6 border border-fin-primary/25">
        <h4 class="text-sm font-bold text-white mb-1">Revenue trend</h4>
        <p class="text-[10px] text-white/40 mb-4">{{ $currencyCode }} · daily</p>
        <div id="chart-revenue-trend" class="h-52 flex items-end gap-1.5 border-b border-white/10 pb-2"></div>
    </div>
    <div class="xl:col-span-5 glass-card rounded-2xl p-6 border border-white/10">
        <h4 class="text-sm font-bold text-white mb-4">Restaurant health</h4>
        <div id="chart-restaurant-split"></div>
    </div>
</div>
<div class="mt-6 glass-card rounded-2xl p-6 border border-white/10">
    <h4 class="text-sm font-bold text-white mb-4">Top restaurants by revenue</h4>
    <div id="table-top-restaurants" class="overflow-x-auto"></div>
</div>
