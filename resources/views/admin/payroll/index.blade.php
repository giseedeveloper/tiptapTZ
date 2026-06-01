<x-admin-layout>
    <x-slot name="header">Payroll (View)</x-slot>

    @include('admin.partials.page-styles')
    @include('admin.partials.flash')

    @include('admin.partials.page-hero', [
        'eyebrow' => 'Finance',
        'title' => 'Payroll Overview',
        'subtitle' => 'Read-only view of waiter salary payments across venues.',
        'accent' => 'blue',
    ])

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-6">
        @include('admin.partials.stat-chip', ['label' => 'Net pay (filtered)', 'value' => config('tiptap.currency_symbol').' '.number_format($totalNet), 'tone' => 'emerald'])
        @include('admin.partials.stat-chip', ['label' => 'Records', 'value' => number_format($payments->total()), 'tone' => 'blue'])
        @include('admin.partials.stat-chip', ['label' => 'This page', 'value' => $payments->count(), 'tone' => 'cyan'])
    </div>

    <div class="glass-card admin-data-panel rounded-3xl overflow-hidden">
        <div class="p-6 border-b border-white/5">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <select name="restaurant_id" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                    <option value="">All restaurants</option>
                    @foreach($restaurants as $r)<option value="{{ $r->id }}" @selected(request('restaurant_id') == $r->id)>{{ $r->name }}</option>@endforeach
                </select>
                <select name="period_month" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                    <option value="">All periods</option>
                    @foreach($months as $m)<option value="{{ $m }}" @selected(request('period_month') === $m)>{{ $m }}</option>@endforeach
                </select>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl text-sm font-semibold">Filter</button>
            </form>
        </div>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full min-w-[800px]">
                <thead><tr class="bg-white/5">
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Period</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Waiter</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Restaurant</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-white/40 uppercase tracking-widest">Net Pay</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase tracking-widest">Paid</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($payments as $payment)
                        <tr class="admin-table-row">
                            <td class="px-6 py-4 text-sm text-white font-medium">{{ $payment->period_label }}</td>
                            <td class="px-6 py-4 text-sm text-white">{{ $payment->user?->name }}</td>
                            <td class="px-6 py-4 text-sm text-white/70">{{ $payment->restaurant?->name }}</td>
                            <td class="px-6 py-4 text-sm text-right font-black text-white tabular-nums">{{ $currencySymbol }} {{ number_format($payment->net_pay) }}</td>
                            <td class="px-6 py-4 text-sm text-white/50">{{ $payment->paid_at?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-16 text-center text-white/40">No payroll records</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())<div class="p-6 border-t border-white/5">{{ $payments->links() }}</div>@endif
    </div>
</x-admin-layout>
