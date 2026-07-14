<x-manager-layout>
    <x-slot name="header">Daily Reports</x-slot>

    @php
        $symbol = $metrics['currency_symbol'] ?? ($currencySymbol ?? 'R');
        $peak = $metrics['peak_hour'] ?? null;
    @endphp

    <div class="space-y-8">
        <div class="relative overflow-hidden rounded-3xl border border-indigo-500/20 bg-gradient-to-br from-indigo-500/10 via-violet-600/10 to-transparent p-8">
            <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-indigo-400/10 blur-3xl"></div>
            <div class="relative flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-400/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-300">
                        Auto PDF · Excel
                    </div>
                    <h2 class="mt-4 text-3xl font-bold tracking-tight text-white">Daily business reports</h2>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Exportable daily reports covering orders, revenue, AOV, customers, items, waiter performance, table turnover, and peak hours.
                        Reports also auto-generate every night for the previous day.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('manager.reports.performance') }}" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-semibold text-white/80 hover:bg-white/10">
                        Waiter performance (legacy)
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="glass-card rounded-2xl p-5">
            <form method="GET" action="{{ route('manager.reports.daily') }}" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[180px]">
                    <label class="mb-2 block text-[10px] font-bold uppercase tracking-wider text-white/40">Report date</label>
                    <input type="date" name="date" value="{{ $selectedDate->toDateString() }}"
                           class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="rounded-xl bg-white/10 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/15">
                    View
                </button>
            </form>
            <div class="mt-4 flex flex-wrap gap-3">
                <form method="POST" action="{{ route('manager.reports.daily.generate') }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ $selectedDate->toDateString() }}">
                    <input type="hidden" name="force" value="1">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                        {{ $report ? 'Regenerate PDF & Excel' : 'Generate PDF & Excel' }}
                    </button>
                </form>
                @if($report)
                    <a href="{{ route('manager.reports.daily.download', ['date' => $selectedDate->toDateString(), 'format' => 'pdf']) }}"
                       class="rounded-xl border border-rose-400/30 bg-rose-500/10 px-4 py-2.5 text-sm font-semibold text-rose-200 hover:bg-rose-500/20">
                        Download PDF
                    </a>
                    <a href="{{ route('manager.reports.daily.download', ['date' => $selectedDate->toDateString(), 'format' => 'excel']) }}"
                       class="rounded-xl border border-emerald-400/30 bg-emerald-500/10 px-4 py-2.5 text-sm font-semibold text-emerald-200 hover:bg-emerald-500/20">
                        Download Excel
                    </a>
                @endif
            </div>
            @if($report)
                <p class="mt-3 text-xs text-white/40">
                    Last generated {{ $report->generated_at?->diffForHumans() }}
                    · source {{ $report->generation_source }}
                </p>
            @else
                <p class="mt-3 text-xs text-amber-200/80">
                    Preview below is live from data. Generate to create saved PDF/Excel files.
                </p>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Orders</p>
                <p class="mt-1 text-3xl font-bold text-white">{{ number_format($metrics['orders']['total'] ?? 0) }}</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Revenue</p>
                <p class="mt-1 text-3xl font-bold text-emerald-400">{{ $symbol }} {{ number_format($metrics['revenue']['total'] ?? 0, 2) }}</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">AOV</p>
                <p class="mt-1 text-3xl font-bold text-violet-300">{{ $symbol }} {{ number_format($metrics['aov'] ?? 0, 2) }}</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Customers</p>
                <p class="mt-1 text-3xl font-bold text-cyan-300">{{ number_format($metrics['customers']['unique'] ?? 0) }}</p>
                <p class="mt-1 text-[11px] text-white/40">{{ $metrics['customers']['new'] ?? 0 }} new · {{ $metrics['customers']['returning'] ?? 0 }} returning</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Turnover rate</p>
                <p class="mt-1 text-3xl font-bold text-amber-300">{{ number_format($metrics['turnover']['rate'] ?? 0, 2) }}x</p>
                <p class="mt-1 text-[11px] text-white/40">{{ $metrics['turnover']['completed_turns'] ?? 0 }} turns / {{ $metrics['turnover']['active_tables'] ?? 0 }} tables</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Tables used</p>
                <p class="mt-1 text-3xl font-bold text-white">{{ number_format($metrics['turnover']['tables_used'] ?? 0) }}</p>
            </div>
            <div class="glass-card rounded-2xl p-5 lg:col-span-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Peak hour</p>
                <p class="mt-1 text-3xl font-bold text-rose-300">{{ $peak['label'] ?? '—' }}</p>
                <p class="mt-1 text-[11px] text-white/40">{{ $peak['orders'] ?? 0 }} orders in peak hour</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="glass-card rounded-2xl p-6">
                <h3 class="mb-4 text-lg font-bold text-white">Top items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[10px] uppercase tracking-wider text-white/40">
                                <th class="pb-3">Item</th>
                                <th class="pb-3 text-right">Qty</th>
                                <th class="pb-3 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse(($metrics['items'] ?? []) as $item)
                                <tr>
                                    <td class="py-2.5 text-white">{{ $item['name'] }}</td>
                                    <td class="py-2.5 text-right text-white/70">{{ $item['quantity'] }}</td>
                                    <td class="py-2.5 text-right text-emerald-300">{{ $symbol }} {{ number_format($item['revenue'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-8 text-center text-white/40">No items for this day</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6">
                <h3 class="mb-4 text-lg font-bold text-white">Customer wait-time</h3>
                @php $wt = $metrics['wait_time'] ?? []; @endphp
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-white/40">Avg to served</p>
                        <p class="mt-1 text-xl font-bold text-cyan-300">{{ ($wt['avg_to_served_minutes'] ?? null) !== null ? number_format($wt['avg_to_served_minutes'], 1).'m' : '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-white/40">Avg to ready</p>
                        <p class="mt-1 text-xl font-bold text-amber-300">{{ ($wt['avg_to_ready_minutes'] ?? null) !== null ? number_format($wt['avg_to_ready_minutes'], 1).'m' : '—' }}</p>
                    </div>
                </div>
                <h3 class="mb-4 text-lg font-bold text-white">Waiter performance</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[10px] uppercase tracking-wider text-white/40">
                                <th class="pb-3">Waiter</th>
                                <th class="pb-3 text-right">Orders</th>
                                <th class="pb-3 text-right">Revenue</th>
                                <th class="pb-3 text-right">Tips</th>
                                <th class="pb-3 text-right">Wait</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse(($metrics['waiter_performance'] ?? []) as $waiter)
                                <tr>
                                    <td class="py-2.5 text-white">{{ $waiter['name'] }}</td>
                                    <td class="py-2.5 text-right text-white/70">{{ $waiter['orders'] }}</td>
                                    <td class="py-2.5 text-right text-emerald-300">{{ $symbol }} {{ number_format($waiter['revenue'], 2) }}</td>
                                    <td class="py-2.5 text-right text-amber-300">{{ $symbol }} {{ number_format($waiter['tips'], 2) }}</td>
                                    <td class="py-2.5 text-right text-cyan-300">{{ ($waiter['avg_to_served_minutes'] ?? null) !== null ? number_format($waiter['avg_to_served_minutes'], 1).'m' : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-white/40">No waiter activity</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <h3 class="mb-4 text-lg font-bold text-white">Peak hours</h3>
            <div class="grid grid-cols-4 gap-2 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-12">
                @foreach(($metrics['peak_hours'] ?? []) as $hour)
                    @php $isPeak = $peak && ($peak['hour'] ?? null) === ($hour['hour'] ?? null) && ($hour['orders'] ?? 0) > 0; @endphp
                    <div class="rounded-xl border px-2 py-3 text-center {{ $isPeak ? 'border-rose-400/40 bg-rose-500/15' : 'border-white/10 bg-white/5' }}">
                        <p class="text-[10px] font-semibold text-white/45">{{ $hour['label'] }}</p>
                        <p class="mt-1 text-sm font-bold {{ $isPeak ? 'text-rose-300' : 'text-white' }}">{{ $hour['orders'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-white">Generated history</h3>
                <p class="text-xs text-white/40">Last 30 saved reports</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-[10px] uppercase tracking-wider text-white/40">
                            <th class="pb-3">Date</th>
                            <th class="pb-3">Source</th>
                            <th class="pb-3">Generated</th>
                            <th class="pb-3 text-right">Exports</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($history as $row)
                            <tr>
                                <td class="py-2.5">
                                    <a href="{{ route('manager.reports.daily', ['date' => $row->report_date->toDateString()]) }}" class="font-medium text-indigo-300 hover:text-indigo-200">
                                        {{ $row->report_date->format('D, d M Y') }}
                                    </a>
                                </td>
                                <td class="py-2.5 text-white/60">{{ $row->generation_source }}</td>
                                <td class="py-2.5 text-white/60">{{ $row->generated_at?->format('d M H:i') ?? '—' }}</td>
                                <td class="py-2.5 text-right">
                                    <a href="{{ route('manager.reports.daily.download', ['date' => $row->report_date->toDateString(), 'format' => 'pdf']) }}" class="mr-2 text-rose-300 hover:underline">PDF</a>
                                    <a href="{{ route('manager.reports.daily.download', ['date' => $row->report_date->toDateString(), 'format' => 'excel']) }}" class="text-emerald-300 hover:underline">Excel</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-white/40">No generated reports yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-manager-layout>
