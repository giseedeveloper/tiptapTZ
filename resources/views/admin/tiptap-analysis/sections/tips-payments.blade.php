<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6" id="tips-kpis">
    @for ($i = 0; $i < 3; $i++)
        <div class="analysis-skeleton h-20 rounded-2xl"></div>
    @endfor
</div>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
    <div class="xl:col-span-4 glass-card rounded-2xl p-6 border border-pink-500/20">
        <div id="chart-payment-methods"></div>
    </div>
    <div class="xl:col-span-4 glass-card rounded-2xl p-6 border border-white/10">
        <div id="chart-payment-purpose"></div>
    </div>
    <div class="xl:col-span-4 glass-card rounded-2xl p-6 border border-white/10">
        <h4 class="text-sm font-bold text-white mb-4">Top tipped waiters</h4>
        <div id="table-top-tipped" class="space-y-2"></div>
    </div>
</div>
