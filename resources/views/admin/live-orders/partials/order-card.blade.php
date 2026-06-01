@php
    /** @var \App\Models\Order $order */
@endphp
<a href="{{ route('admin.orders.show', $order) }}" class="block p-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 transition-all group">
    <p class="text-sm font-bold text-white group-hover:text-violet-200">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }} · Table {{ $order->table_number }}</p>
    <p class="text-[10px] text-white/50 mt-1 truncate">{{ $order->restaurant?->name }}</p>
    <p class="text-xs text-cyan-300 mt-2 font-semibold tabular-nums">{{ $currencySymbol }} {{ number_format($order->total_amount) }}</p>
    @if($order->waiter)
        <p class="text-[10px] text-white/40 mt-1">{{ $order->waiter->name }}</p>
    @endif
</a>
