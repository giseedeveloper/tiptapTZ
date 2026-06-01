<x-admin-layout>
    <x-slot name="header">{{ $restaurant->name }}</x-slot>
    @include('admin.partials.flash')

    @php
        $tabs = [
            'overview' => ['label' => 'Overview', 'count' => null],
            'orders' => ['label' => 'Orders', 'count' => $tabCounts['orders']],
            'payments' => ['label' => 'Payments', 'count' => $tabCounts['payments']],
            'menu' => ['label' => 'Menu', 'count' => $tabCounts['menu']],
            'staff' => ['label' => 'Staff', 'count' => $tabCounts['staff']],
            'feedback' => ['label' => 'Feedback', 'count' => $tabCounts['feedback']],
        ];
        $maxVenueRevenue = max(collect($venueAnalytics['revenue_trend'])->max('revenue'), 1);
        $ordersPipelineTotal = collect($venueAnalytics['orders_by_status'])->sum('value');
        $statusColors = [
            'pending' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
            'preparing' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
            'ready' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
            'served' => 'bg-violet-500/20 text-violet-300 border-violet-500/30',
            'paid' => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30',
            'completed' => 'bg-teal-500/20 text-teal-300 border-teal-500/30',
            'cancelled' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
        ];
    @endphp

    <style>
        .venue-hero {
            background: linear-gradient(135deg, rgba(140, 113, 246, 0.18) 0%, rgba(109, 82, 232, 0.1) 50%, rgba(15, 10, 30, 0.9) 100%);
            border: 1px solid rgba(140, 113, 246, 0.3);
        }
        .venue-tab-active {
            background: linear-gradient(180deg, rgba(140, 113, 246, 0.25) 0%, rgba(140, 113, 246, 0.05) 100%);
            border-bottom: 2px solid #a78bfa;
            color: #e9d5ff;
        }
        .venue-bar {
            background: linear-gradient(to top, #5b21b6, #8C71F6 45%, #6D52E8);
            border-radius: 6px 6px 0 0;
            box-shadow: 0 -4px 16px rgba(140, 113, 246, 0.4);
            transition: filter 0.2s ease;
        }
        .venue-bar:hover { filter: brightness(1.15); }
    </style>

    {{-- Hero --}}
    <div class="venue-hero rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-violet-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/4 w-48 h-48 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center gap-6">
            <div class="flex items-center gap-5 min-w-0 flex-1">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-violet-600 via-purple-600 to-cyan-500 flex items-center justify-center text-3xl font-black text-white shadow-xl shadow-violet-500/30 shrink-0 ring-2 ring-white/10">
                    {{ substr($restaurant->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.25em] mb-1">Venue profile</p>
                    <h1 class="text-2xl md:text-3xl font-black text-white truncate">{{ $restaurant->name }}</h1>
                    <p class="text-sm text-white/50 mt-1 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/></svg>
                            {{ $restaurant->location ?? 'No location' }}
                        </span>
                        <span class="text-white/20">·</span>
                        <span>ID #{{ str_pad($restaurant->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $restaurant->is_active ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/15 text-rose-400 border-rose-500/30' }}">
                            @if($restaurant->is_active)<span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>@endif
                            {{ $restaurant->is_active ? 'Active' : 'Blocked' }}
                        </span>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $restaurant->hasSelcomConfigured() ? 'bg-cyan-500/15 text-cyan-400 border-cyan-500/30' : 'bg-amber-500/15 text-amber-400 border-amber-500/30' }}">
                            {{ $restaurant->hasSelcomConfigured() ? ($restaurant->selcom_is_live ? config('tiptap.payment_gateway').' Live' : config('tiptap.payment_gateway').' Test') : 'Payments not configured' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('admin.restaurants.index') }}" class="px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white/70 text-xs font-semibold hover:bg-white/10 transition-all">← All venues</a>
                <a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="px-4 py-2.5 rounded-xl bg-violet-600/30 border border-violet-500/40 text-violet-200 text-xs font-bold hover:bg-violet-600/50 transition-all">Edit venue</a>
                <form action="{{ route('admin.restaurants.toggle-status', $restaurant) }}" method="POST" class="inline">@csrf
                    <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-bold border transition-all {{ $restaurant->is_active ? 'bg-rose-500/15 border-rose-500/30 text-rose-300 hover:bg-rose-500/25' : 'bg-emerald-500/15 border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/25' }}">
                        {{ $restaurant->is_active ? 'Block venue' : 'Activate venue' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Quick stats strip --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
        <div class="glass-card rounded-xl p-4 border border-emerald-500/20">
            <p class="text-[9px] font-black text-white/40 uppercase">Total revenue</p>
            <p class="text-lg font-black text-emerald-400 mt-1 tabular-nums">{{ $currencySymbol }} {{ number_format($overview['total_earnings']) }}</p>
        </div>
        <div class="glass-card rounded-xl p-4 border border-blue-500/20">
            <p class="text-[9px] font-black text-white/40 uppercase">All orders</p>
            <p class="text-lg font-black text-blue-400 mt-1 tabular-nums">{{ number_format($overview['total_orders']) }}</p>
        </div>
        <div class="glass-card rounded-xl p-4 border border-amber-500/20">
            <p class="text-[9px] font-black text-white/40 uppercase">Avg rating</p>
            <p class="text-lg font-black text-amber-400 mt-1">{{ $overview['avg_rating'] > 0 ? $overview['avg_rating'].' ★' : '—' }}</p>
        </div>
        <div class="glass-card rounded-xl p-4 border border-violet-500/20">
            <p class="text-[9px] font-black text-white/40 uppercase">Today revenue</p>
            <p class="text-lg font-black text-violet-400 mt-1 tabular-nums">{{ $currencySymbol }} {{ number_format($venueAnalytics['revenue_today']) }}</p>
        </div>
        <div class="glass-card rounded-xl p-4 border border-cyan-500/20">
            <p class="text-[9px] font-black text-white/40 uppercase">Live orders</p>
            <p class="text-lg font-black text-cyan-400 mt-1 tabular-nums">{{ $venueAnalytics['active_orders'] }}</p>
        </div>
        <a href="{{ route('admin.live-orders.index', ['restaurant_id' => $restaurant->id]) }}" class="glass-card rounded-xl p-4 border border-white/10 hover:border-violet-500/30 transition-all flex flex-col justify-center">
            <p class="text-[9px] font-black text-violet-400 uppercase">Quick action</p>
            <p class="text-xs font-bold text-white mt-1">Live board →</p>
        </a>
    </div>

    {{-- Tabs --}}
    <nav class="flex gap-1 overflow-x-auto sidebar-nav-scroll mb-6 p-1 rounded-2xl bg-white/[0.03] border border-white/10">
        @foreach($tabs as $key => $meta)
            <a href="{{ route('admin.restaurants.show', ['restaurant' => $restaurant, 'tab' => $key]) }}"
               class="flex items-center gap-2 px-4 py-3 rounded-xl text-xs font-bold uppercase tracking-wider whitespace-nowrap transition-all {{ $tab === $key ? 'venue-tab-active' : 'text-white/45 hover:text-white hover:bg-white/5' }}">
                {{ $meta['label'] }}
                @if($meta['count'] !== null)
                    <span class="px-1.5 py-0.5 rounded-md text-[9px] tabular-nums {{ $tab === $key ? 'bg-violet-500/30 text-violet-200' : 'bg-white/10 text-white/50' }}">{{ $meta['count'] }}</span>
                @endif
            </a>
        @endforeach
    </nav>

    @if($tab === 'overview')
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            {{-- Revenue mini histogram --}}
            <div class="xl:col-span-7 glass-card rounded-2xl p-6 border border-violet-500/20 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-violet-600/5 to-transparent pointer-events-none"></div>
                <div class="relative">
                    <h3 class="text-lg font-black text-white">Revenue trend</h3>
                    <p class="text-[10px] text-violet-400/80 uppercase tracking-widest mt-1">Last 7 days at this venue</p>
                    <div class="h-44 flex items-end gap-2 mt-6">
                        @foreach($venueAnalytics['revenue_trend'] as $day)
                            @php $h = max(($day['revenue'] / $maxVenueRevenue) * 100, $day['revenue'] > 0 ? 8 : 3); @endphp
                            <div class="flex-1 flex flex-col items-center justify-end h-full group">
                                <div class="venue-bar w-full relative" style="height:{{ $h }}%">
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-0.5 rounded bg-black/90 text-[9px] text-white whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">
                                        {{ $currencySymbol }} {{ number_format($day['revenue']) }}
                                    </div>
                                </div>
                                <p class="text-[9px] text-white/45 mt-2 font-bold">{{ $day['label'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Order pipeline donut --}}
            <div class="xl:col-span-5 glass-card rounded-2xl p-6 border border-white/10">
                <h3 class="text-lg font-black text-white mb-1">Order pipeline</h3>
                <p class="text-[10px] text-white/40 uppercase tracking-widest mb-2">Status breakdown</p>
                @if($ordersPipelineTotal > 0)
                    @include('admin.partials.donut-chart', [
                        'segments' => $venueAnalytics['orders_by_status'],
                        'centerLabel' => $ordersPipelineTotal,
                        'centerSub' => 'Orders',
                        'size' => '9.5rem',
                    ])
                @else
                    <p class="text-center text-white/40 py-16 text-sm">No orders yet</p>
                @endif
            </div>

            {{-- Details + Selcom --}}
            <div class="xl:col-span-5 glass-card rounded-2xl p-6 border border-white/10">
                <h3 class="text-sm font-black text-white uppercase tracking-wider mb-4">Venue details</h3>
                <dl class="space-y-4">
                    <div class="flex justify-between gap-4 p-3 rounded-xl bg-white/[0.03] border border-white/5">
                        <dt class="text-[10px] font-bold text-white/40 uppercase">Phone</dt>
                        <dd class="text-sm font-bold text-white">{{ $restaurant->phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 p-3 rounded-xl bg-white/[0.03] border border-white/5">
                        <dt class="text-[10px] font-bold text-white/40 uppercase">Joined</dt>
                        <dd class="text-sm font-bold text-white">{{ $restaurant->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 p-3 rounded-xl bg-white/[0.03] border border-white/5">
                        <dt class="text-[10px] font-bold text-white/40 uppercase">Managers</dt>
                        <dd class="text-sm font-bold text-blue-400">{{ $managers->count() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 p-3 rounded-xl bg-white/[0.03] border border-white/5">
                        <dt class="text-[10px] font-bold text-white/40 uppercase">Waiters</dt>
                        <dd class="text-sm font-bold text-amber-400">{{ $waiters->count() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="xl:col-span-7 glass-card rounded-2xl p-6 border border-cyan-500/15">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-sm font-black text-white uppercase tracking-wider">Payment gateway</h3>
                        <p class="text-xs text-white/40 mt-1">{{ config('tiptap.payment_gateway') }} configuration</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase border {{ $restaurant->hasSelcomConfigured() ? 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/15 text-amber-400 border-amber-500/30' }}">
                        {{ $restaurant->hasSelcomConfigured() ? ($restaurant->selcom_is_live ? 'Live mode' : 'Test mode') : 'Not configured' }}
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-4 rounded-xl bg-black/20 border border-white/5">
                        <p class="text-[9px] text-white/40 uppercase font-bold mb-1">Vendor ID</p>
                        <p class="text-xs font-mono text-white/70 truncate">{{ $restaurant->selcom_vendor_id ?? '—' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-black/20 border border-white/5">
                        <p class="text-[9px] text-white/40 uppercase font-bold mb-1">API key</p>
                        <p class="text-xs font-mono text-white/70">{{ $restaurant->selcom_api_key ? '••••'.substr($restaurant->selcom_api_key, -4) : '—' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-black/20 border border-white/5">
                        <p class="text-[9px] text-white/40 uppercase font-bold mb-1">API secret</p>
                        <p class="text-xs font-mono text-white/70">{{ $restaurant->selcom_api_secret ? '••••••••' : '—' }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <a href="{{ route('admin.restaurants.edit', $restaurant) }}" class="text-xs font-semibold text-violet-400 hover:text-violet-300">Configure payments →</a>
                    <a href="{{ route('admin.orders.index', ['restaurant_id' => $restaurant->id]) }}" class="text-xs font-semibold text-cyan-400 hover:text-cyan-300">View orders →</a>
                </div>
            </div>
        </div>

    @elseif($tab === 'orders')
        <div class="glass-card rounded-2xl overflow-hidden border border-blue-500/15">
            <div class="p-5 border-b border-white/5 flex flex-wrap justify-between items-center gap-3 bg-gradient-to-r from-blue-600/10 to-transparent">
                <div>
                    <h3 class="text-lg font-black text-white">Orders</h3>
                    <p class="text-[10px] text-white/40 uppercase tracking-widest">{{ $tabCounts['orders'] }} total</p>
                </div>
                <a href="{{ route('admin.orders.index', ['restaurant_id' => $restaurant->id]) }}" class="px-4 py-2 bg-violet-600/20 border border-violet-500/30 text-violet-300 text-xs font-bold rounded-xl hover:bg-violet-600/30">Full history →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px]">
                    <thead><tr class="bg-white/5">
                        <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Order</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Table</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Waiter</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Status</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-white/40 uppercase">Amount</th>
                    </tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-white/[0.03] transition-colors">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-violet-400 font-mono font-bold hover:text-violet-300">#{{ $order->id }}</a>
                                    <p class="text-[10px] text-white/35 mt-0.5">{{ $order->created_at->format('d M, H:i') }}</p>
                                </td>
                                <td class="px-6 py-4 text-white/80 text-sm font-medium">Table {{ $order->table_number }}</td>
                                <td class="px-6 py-4 text-white/50 text-sm">{{ $order->waiter?->name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border {{ $statusColors[$order->status] ?? 'bg-white/10 text-white/60 border-white/10' }}">{{ $order->status }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-black text-white tabular-nums">{{ $currencySymbol }} {{ number_format($order->total_amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-16 text-center text-white/40">No orders for this venue</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($tab === 'payments')
        <div class="glass-card rounded-2xl overflow-hidden border border-emerald-500/15">
            <div class="p-5 border-b border-white/5 flex flex-wrap justify-between items-center gap-3 bg-gradient-to-r from-emerald-600/10 to-transparent">
                <div>
                    <h3 class="text-lg font-black text-white">Payments</h3>
                    <p class="text-[10px] text-white/40 uppercase tracking-widest">{{ $tabCounts['payments'] }} records</p>
                </div>
                <a href="{{ route('admin.payments.index', ['restaurant_id' => $restaurant->id]) }}" class="px-4 py-2 bg-emerald-600/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold rounded-xl">All payments →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px]">
                    <thead><tr class="bg-white/5">
                        <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">When</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Order</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Method</th>
                        <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Status</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black text-white/40 uppercase">Amount</th>
                    </tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($recentPayments as $payment)
                            <tr class="hover:bg-white/[0.03]">
                                <td class="px-6 py-4 text-sm text-white/55">{{ $payment->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-6 py-4"><a href="{{ route('admin.orders.show', $payment->order_id) }}" class="text-violet-400 font-mono text-sm font-bold">#{{ $payment->order_id }}</a></td>
                                <td class="px-6 py-4 text-sm text-white/70 capitalize">{{ $payment->method ?? '—' }}</td>
                                <td class="px-6 py-4"><span class="text-[10px] font-bold uppercase text-emerald-400">{{ $payment->status }}</span></td>
                                <td class="px-6 py-4 text-right font-black text-emerald-400 tabular-nums">{{ $currencySymbol }} {{ number_format($payment->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-16 text-center text-white/40">No payments recorded</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @elseif($tab === 'menu')
        <div class="glass-card rounded-2xl overflow-hidden border border-lime-500/15">
            <div class="p-5 border-b border-white/5 flex flex-wrap justify-between items-center gap-3 bg-gradient-to-r from-lime-600/10 to-transparent">
                <div>
                    <h3 class="text-lg font-black text-white">Menu</h3>
                    <p class="text-[10px] text-white/40 uppercase tracking-widest">{{ $tabCounts['menu'] }} items · read-only</p>
                </div>
                <a href="{{ route('admin.menus.show', $restaurant) }}" class="px-4 py-2 bg-lime-600/20 border border-lime-500/30 text-lime-300 text-xs font-bold rounded-xl">Expanded view →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-px bg-white/5">
                @forelse($menuItems as $item)
                    <div class="p-4 bg-[#0f0a1e]/80 flex justify-between gap-3 {{ !$item->is_available ? 'opacity-50' : '' }}">
                        <div class="min-w-0">
                            <p class="font-bold text-white truncate">{{ $item->name }}</p>
                            <p class="text-[10px] text-white/40 mt-0.5">{{ $item->category?->name ?? 'Uncategorized' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-black text-lime-400 text-sm">{{ $currencySymbol }} {{ number_format($item->price) }}</p>
                            <p class="text-[9px] font-bold uppercase mt-1 {{ $item->is_available ? 'text-emerald-400' : 'text-rose-400' }}">{{ $item->is_available ? 'Available' : 'Off' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="col-span-2 p-16 text-center text-white/40">No menu items</p>
                @endforelse
            </div>
        </div>

    @elseif($tab === 'staff')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="glass-card rounded-2xl p-6 border border-blue-500/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <h3 class="text-lg font-black text-white mb-1 relative">Managers</h3>
                <p class="text-[10px] text-blue-400/80 uppercase tracking-widest mb-5 relative">{{ $managers->count() }} assigned</p>
                <div class="space-y-3 relative">
                    @forelse($managers as $manager)
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-gradient-to-r from-blue-600/10 to-transparent border border-blue-500/20">
                            <div class="w-12 h-12 rounded-xl bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-lg font-black text-blue-300 shrink-0">
                                {{ substr($manager->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-white truncate">{{ $manager->name }}</p>
                                <p class="text-[10px] text-white/45 truncate">{{ $manager->email }}</p>
                            </div>
                            <form method="POST" action="{{ route('admin.impersonate.start', $manager) }}" class="shrink-0">@csrf
                                <button type="submit" class="px-3 py-2 text-[10px] font-black uppercase tracking-wider bg-violet-600 hover:bg-violet-500 text-white rounded-lg shadow-lg shadow-violet-500/20 transition-all">
                                    Impersonate
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-white/40 text-sm py-8 text-center">No managers linked</p>
                    @endforelse
                </div>
            </div>
            <div class="glass-card rounded-2xl p-6 border border-amber-500/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <h3 class="text-lg font-black text-white mb-1 relative">Waiters</h3>
                <p class="text-[10px] text-amber-400/80 uppercase tracking-widest mb-5 relative">{{ $waiters->count() }} on staff</p>
                <div class="space-y-3 max-h-[420px] overflow-y-auto sidebar-nav-scroll relative">
                    @forelse($waiters as $waiter)
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/[0.03] border border-white/10 hover:border-amber-500/20 transition-colors">
                            <div class="w-11 h-11 rounded-xl bg-amber-500/15 border border-amber-500/25 flex items-center justify-center font-black text-amber-300 shrink-0">
                                {{ substr($waiter->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-white text-sm truncate">{{ $waiter->name }}</p>
                                <p class="text-[10px] text-white/40 truncate">{{ $waiter->email }}</p>
                                @if($waiter->global_waiter_number)
                                    <p class="text-[10px] font-mono text-cyan-400/80 mt-0.5">{{ $waiter->global_waiter_number }}</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-white/40 text-sm py-8 text-center">No waiters linked yet</p>
                    @endforelse
                </div>
            </div>
        </div>

    @elseif($tab === 'feedback')
        <div class="glass-card rounded-2xl border border-amber-500/15 overflow-hidden">
            <div class="p-5 border-b border-white/5 bg-gradient-to-r from-amber-600/10 to-transparent">
                <h3 class="text-lg font-black text-white">Customer feedback</h3>
                <p class="text-[10px] text-white/40 uppercase tracking-widest">{{ $tabCounts['feedback'] }} reviews · avg {{ $overview['avg_rating'] > 0 ? $overview['avg_rating'].'★' : '—' }}</p>
            </div>
            <div class="divide-y divide-white/5">
                @forelse($recentFeedback as $item)
                    <article class="p-6 hover:bg-white/[0.02] transition-colors">
                        <div class="flex flex-wrap justify-between gap-2 mb-3">
                            <div class="flex gap-0.5">
                                @for($s = 1; $s <= 5; $s++)
                                    <svg class="w-4 h-4 {{ $s <= $item->rating ? 'text-amber-400' : 'text-white/15' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endfor
                            </div>
                            <time class="text-[10px] text-white/40 font-medium">{{ $item->created_at->diffForHumans() }}</time>
                        </div>
                        <p class="text-white/90 text-sm leading-relaxed">{{ $item->comment ?: 'No written comment.' }}</p>
                        <p class="text-xs text-white/40 mt-3 flex flex-wrap gap-2">
                            <span class="px-2 py-0.5 rounded-md bg-white/5">{{ $item->waiter?->name ?? 'No waiter' }}</span>
                            <a href="{{ route('admin.orders.show', $item->order_id) }}" class="text-violet-400 hover:underline">Order #{{ $item->order_id }}</a>
                        </p>
                    </article>
                @empty
                    <p class="p-16 text-center text-white/40">No feedback yet</p>
                @endforelse
            </div>
        </div>
    @endif
</x-admin-layout>
