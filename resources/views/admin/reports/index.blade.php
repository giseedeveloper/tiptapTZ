<x-admin-layout>
    <x-slot name="header">Reports &amp; Analytics</x-slot>

    @include('admin.partials.page-styles')
    @include('admin.partials.flash')

    @include('admin.partials.page-hero', [
        'eyebrow' => 'Finance',
        'title' => 'Reports & Analytics',
        'subtitle' => 'Platform-wide performance for the selected period and venue.',
        'accent' => 'violet',
    ])

    <div class="glass-card admin-data-panel rounded-3xl p-6 mb-6 border border-violet-500/15">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-[10px] font-bold uppercase text-white/40 mb-1 block">Period</label>
                <select name="period" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                    @foreach(['today'=>'Today','week'=>'This week','month'=>'This month','year'=>'This year','custom'=>'Custom'] as $val => $label)
                        <option value="{{ $val }}" @selected($period === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if($period === 'custom')
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
            @endif
            <select name="restaurant_id" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                <option value="">All restaurants</option>
                @foreach($restaurants as $r)<option value="{{ $r->id }}" @selected($restaurantId == $r->id)>{{ $r->name }}</option>@endforeach
            </select>
            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl text-sm font-semibold">Apply</button>
        </form>
        <p class="text-xs text-cyan-400/80 mt-3 font-medium">{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</p>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        @php
            $kpis = [
                ['Orders', number_format($totalOrders), 'border-violet-500/20', 'text-violet-400'],
                ['Revenue', config('tiptap.currency_symbol').' '.number_format($totalRevenue), 'border-emerald-500/20', 'text-emerald-400'],
                ['Avg order', config('tiptap.currency_symbol').' '.number_format($avgOrderValue), 'border-cyan-500/20', 'text-cyan-400'],
                ['Tips', config('tiptap.currency_symbol').' '.number_format($tipsTotal), 'border-amber-500/20', 'text-amber-400'],
                ['Avg rating', number_format($feedbackAvg, 1).' ★', 'border-orange-500/20', 'text-orange-400'],
            ];
        @endphp
        @foreach($kpis as [$label, $value, $borderClass, $valueClass])
            <div class="glass-card admin-data-panel rounded-2xl p-5 border {{ $borderClass }}">
                <p class="text-[10px] font-bold uppercase text-white/40 tracking-wider">{{ $label }}</p>
                <p class="text-xl md:text-2xl font-black mt-2 tabular-nums {{ $valueClass }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="glass-card admin-data-panel rounded-3xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            <h2 class="text-sm font-black text-white uppercase tracking-wider">By restaurant</h2>
            <span class="text-[10px] text-white/40 font-bold uppercase">{{ $restaurantBreakdown->count() }} venues</span>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[480px]">
                <thead><tr class="bg-white/5">
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Restaurant</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-white/40 uppercase tracking-widest">Orders</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-white/40 uppercase tracking-widest">Revenue</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($restaurantBreakdown as $row)
                        <tr class="admin-table-row">
                            <td class="px-6 py-4 text-white font-medium">{{ $row['name'] }}</td>
                            <td class="px-6 py-4 text-right text-white/80 tabular-nums">{{ $row['orders_count'] }}</td>
                            <td class="px-6 py-4 text-right text-emerald-400 font-semibold tabular-nums">{{ $currencySymbol }} {{ number_format($row['revenue']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-16 text-center text-white/40">No data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
