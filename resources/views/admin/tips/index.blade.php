<x-admin-layout>
    <x-slot name="header">Tips</x-slot>

    @include('admin.partials.page-styles')
    @include('admin.partials.flash')

    @include('admin.partials.page-hero', [
        'eyebrow' => 'Finance',
        'title' => 'Platform Tips',
        'subtitle' => 'Gratuities collected across venues and waiters.',
        'accent' => 'amber',
    ])

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
        @include('admin.partials.stat-chip', ['label' => 'Total (filtered)', 'value' => config('tiptap.currency_symbol').' '.number_format($totalTips), 'tone' => 'emerald'])
        @include('admin.partials.stat-chip', ['label' => 'Records', 'value' => number_format($tips->total()), 'tone' => 'amber'])
        @include('admin.partials.stat-chip', ['label' => 'This page', 'value' => $tips->count(), 'tone' => 'cyan'])
    </div>

    <div class="glass-card admin-data-panel rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-white/5">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <select name="restaurant_id" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                    <option value="">All restaurants</option>
                    @foreach($restaurants as $r)<option value="{{ $r->id }}" @selected(request('restaurant_id') == $r->id)>{{ $r->name }}</option>@endforeach
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl text-sm font-semibold">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[600px]">
                <thead><tr class="bg-white/5">
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Date</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Restaurant</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Waiter</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Order</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-white/40 uppercase tracking-widest">Amount</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($tips as $tip)
                        <tr class="admin-table-row">
                            <td class="px-6 py-4 text-sm text-white/60">{{ $tip->created_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-white font-medium">{{ $tip->restaurant?->name }}</td>
                            <td class="px-6 py-4 text-sm text-white/80">{{ $tip->waiter?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm"><a href="{{ route('admin.orders.show', $tip->order_id) }}" class="text-violet-400 hover:text-violet-300 font-semibold">#{{ str_pad($tip->order_id, 6, '0', STR_PAD_LEFT) }}</a></td>
                            <td class="px-6 py-4 text-sm text-right font-black text-emerald-400 tabular-nums">{{ $currencySymbol }} {{ number_format($tip->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-16 text-center text-white/40">No tips in this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tips->hasPages())<div class="p-6 border-t border-white/5">{{ $tips->links() }}</div>@endif
    </div>
</x-admin-layout>
