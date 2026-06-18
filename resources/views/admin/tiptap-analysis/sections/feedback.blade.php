<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" id="feedback-kpis">
    @for ($i = 0; $i < 2; $i++)
        <div class="analysis-skeleton h-16 rounded-2xl"></div>
    @endfor
</div>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
    <div class="xl:col-span-5 glass-card rounded-2xl p-6 border border-amber-500/20">
        <h4 class="text-sm font-bold text-white mb-4">Star distribution</h4>
        <div id="chart-rating-bars" class="space-y-2"></div>
    </div>
    <div class="xl:col-span-7 glass-card rounded-2xl p-6 border border-white/10">
        <h4 class="text-sm font-bold text-white mb-4">Low-rating alerts</h4>
        <div id="feedback-alerts"></div>
    </div>
</div>
<div class="glass-card rounded-2xl p-6 border border-white/10">
    <h4 class="text-sm font-bold text-white mb-4">Recent customer comments</h4>
    <div id="feedback-comments" class="space-y-3 max-h-80 overflow-y-auto"></div>
</div>
