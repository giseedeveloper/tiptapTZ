<x-manager-layout>
    <x-slot name="header">Tip Reports</x-slot>

    @php
        $totals = $report['totals'];
        $mask = fn ($value) => $valueVisible ? ($currencySymbol.' '.number_format((float) $value, 2)) : '••••';
    @endphp

    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[10px] font-bold text-cyan-400 uppercase tracking-[0.2em] mb-1">Digital Tipping</p>
            <h2 class="text-3xl font-bold text-white tracking-tight">Tip Reports</h2>
            <p class="text-sm text-white/45 mt-1">By waiter, barista, kitchen · branch · shift · date range</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('manager.tips.index') }}" class="glass px-4 py-2.5 rounded-xl text-xs font-semibold text-white/70 hover:text-white transition-all">Settings</a>
            @if($valueVisible)
                <a href="{{ route('manager.tips.reports.export', request()->query()) }}" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2.5 rounded-xl text-xs font-semibold">Export CSV</a>
            @else
                <span class="glass px-4 py-2.5 rounded-xl text-xs font-semibold text-white/40" title="Enable tip-value visibility in settings">Export disabled</span>
            @endif
        </div>
    </div>

    <form method="GET" action="{{ route('manager.tips.reports') }}" class="glass p-5 rounded-2xl mb-8">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="min-w-[150px]">
                <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Period</label>
                <select name="period" onchange="this.form.submit()" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white [&>option]:text-black">
                    @foreach(['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'custom' => 'Custom'] as $key => $label)
                        <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if($period === 'custom')
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Start</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white">
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">End</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white">
                </div>
            @endif
            <div class="min-w-[140px]">
                <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Shift</label>
                <select name="shift" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white [&>option]:text-black">
                    @foreach(['all' => 'All shifts', 'morning' => 'Morning', 'afternoon' => 'Afternoon', 'evening' => 'Evening', 'night' => 'Night'] as $key => $label)
                        <option value="{{ $key }}" @selected($shift === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if($branches->isNotEmpty())
                <div class="min-w-[160px]">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Branch</label>
                    <select name="branch_id" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white [&>option]:text-black">
                        <option value="all" @selected($selectedBranch === 'all')>All branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) $selectedBranch === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button type="submit" class="px-6 py-3 rounded-xl bg-linear-to-r from-fin-primary to-fin-primary-dark text-white font-semibold">Apply</button>
        </div>
    </form>

    @unless($valueVisible)
        <div class="mb-6 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-200">
            Tip-value visibility is OFF. Amounts are hidden; only counts are shown. Enable it in Tip Settings.
        </div>
    @endunless

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Waiter tips</p>
            <p class="text-2xl font-bold text-white">{{ $mask($totals['waiter']) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Barista tips</p>
            <p class="text-2xl font-bold text-white">{{ $mask($totals['barista']) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Kitchen tips</p>
            <p class="text-2xl font-bold text-amber-300">{{ $mask($totals['kitchen']) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Grand total · {{ $totals['count'] }} tips</p>
            <p class="text-2xl font-bold text-emerald-300">{{ $mask($totals['grand']) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        @foreach(['waiters' => 'Waiters', 'baristas' => 'Baristas', 'kitchen' => 'Kitchen team'] as $key => $label)
            <div class="glass-card rounded-2xl p-6 border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4">{{ $label }}</h3>
                <div class="space-y-2">
                    @forelse($report[$key] as $row)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-white/80">{{ $row['name'] }} <span class="text-white/35 text-xs">· {{ $row['count'] }}</span></span>
                            <span class="font-semibold text-white tabular-nums">{{ $mask($row['total']) }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-white/40 text-center py-6">No tips in this range</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        @if(!empty($report['by_branch']))
            <div class="glass-card rounded-2xl p-6 border border-white/10">
                <h3 class="text-lg font-bold text-white mb-4">By branch</h3>
                <div class="space-y-2">
                    @foreach($report['by_branch'] as $row)
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="text-white/80">{{ $row['name'] }} <span class="text-white/35 text-xs">· {{ $row['count'] }}</span></span>
                            <span class="font-semibold text-white tabular-nums">{{ $mask($row['total']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        <div class="glass-card rounded-2xl p-6 border border-white/10">
            <h3 class="text-lg font-bold text-white mb-4">By shift</h3>
            <div class="space-y-2">
                @foreach($report['by_shift'] as $row)
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <span class="text-white/80">{{ $row['label'] }} <span class="text-white/35 text-xs">· {{ $row['count'] }}</span></span>
                        <span class="font-semibold text-white tabular-nums">{{ $mask($row['total']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-manager-layout>
