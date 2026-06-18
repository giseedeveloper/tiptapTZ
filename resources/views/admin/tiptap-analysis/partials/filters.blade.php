<div class="analysis-filter-bar sticky top-0 z-20 rounded-2xl border border-white/10 p-4 mb-6 flex flex-col lg:flex-row lg:items-center gap-4">
    <div class="flex flex-wrap items-center gap-3 flex-1">
        <label class="text-[10px] font-bold text-white/40 uppercase tracking-wider">Period</label>
        <select id="filter-days" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:ring-2 focus:ring-fin-primary/50 focus:border-fin-primary/50">
            <option value="7">Last 7 days</option>
            <option value="14">Last 14 days</option>
            <option value="30" selected>Last 30 days</option>
            <option value="60">Last 60 days</option>
            <option value="90">Last 90 days</option>
        </select>
        <label class="text-[10px] font-bold text-white/40 uppercase tracking-wider ml-2">Restaurant</label>
        <select id="filter-restaurant" class="bg-white/5 border border-white/10 rounded-xl px-3 py-2 text-sm text-white min-w-[200px] focus:ring-2 focus:ring-fin-primary/50">
            <option value="">All restaurants</option>
            @foreach ($restaurants as $restaurant)
                <option value="{{ $restaurant->id }}">{{ $restaurant->name }}{{ $restaurant->is_active ? '' : ' (inactive)' }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex items-center gap-3">
        <span id="analysis-last-updated" class="text-[10px] text-white/35 tabular-nums"></span>
        <button type="button" id="analysis-refresh-btn" class="px-4 py-2 rounded-xl bg-fin-primary/20 border border-fin-primary/40 text-fin-primary text-xs font-bold hover:bg-fin-primary/30 transition">
            Refresh
        </button>
    </div>
</div>
