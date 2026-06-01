<x-admin-layout>
    <x-slot name="header">Live Orders</x-slot>

    @include('admin.partials.page-styles')
    @include('admin.partials.flash')

    @include('admin.partials.page-hero', [
        'eyebrow' => 'Operations',
        'title' => 'Live Orders Board',
        'subtitle' => 'Real-time kitchen pipeline across all venues. Auto-refreshes every ' . config('tiptap.admin_live_poll_seconds', 30) . ' seconds.',
        'accent' => 'cyan',
    ])

    @php
        $totalLive = collect($counts)->sum();
        $feedUrl = route('admin.live-orders.feed', $restaurantId ? ['restaurant_id' => $restaurantId] : []);
    @endphp

    <div id="live-orders-root"
         data-feed-url="{{ $feedUrl }}"
         data-poll="{{ config('tiptap.admin_live_poll_seconds', 30) }}">

        <div class="flex items-center justify-between gap-4 mb-4">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">
                <span class="inline-flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Live · <span id="live-orders-refreshed">just now</span>
                </span>
            </p>
            <button type="button" id="live-orders-refresh" class="px-3 py-2 text-[10px] font-bold uppercase tracking-widest text-white/70 glass rounded-lg border border-white/10 hover:bg-white/10">
                Refresh
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6" id="live-orders-stats">
            @include('admin.partials.stat-chip', ['label' => 'Active now', 'value' => number_format($totalLive), 'tone' => 'cyan', 'id' => 'live-stat-total'])
            @foreach($counts as $status => $count)
                @include('admin.partials.stat-chip', [
                    'label' => ucfirst($status),
                    'value' => $count,
                    'tone' => match($status) {
                        'pending' => 'amber',
                        'preparing' => 'blue',
                        'ready' => 'emerald',
                        'served' => 'violet',
                        default => 'white',
                    },
                    'id' => 'live-stat-' . $status,
                ])
            @endforeach
        </div>

        <div class="glass-card admin-data-panel rounded-3xl p-6 mb-6 border border-cyan-500/15">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div class="min-w-[200px] flex-1">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-1 block">Restaurant</label>
                    <select name="restaurant_id" class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white [&>option]:bg-gray-900">
                        <option value="">All venues</option>
                        @foreach($restaurants as $r)
                            <option value="{{ $r->id }}" @selected($restaurantId == $r->id)>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl font-semibold text-sm">Filter</button>
                <a href="{{ route('admin.live-orders.index') }}" class="px-5 py-2.5 bg-white/10 text-white rounded-xl text-sm border border-white/10">Clear</a>
            </form>
        </div>

        @php
            $columns = [
                'pending' => ['label' => 'Pending', 'orders' => $pendingOrders, 'titleClass' => 'text-amber-400', 'colClass' => 'admin-kanban-col--pending'],
                'preparing' => ['label' => 'Preparing', 'orders' => $preparingOrders, 'titleClass' => 'text-blue-400', 'colClass' => 'admin-kanban-col--preparing'],
                'ready' => ['label' => 'Ready', 'orders' => $readyOrders, 'titleClass' => 'text-emerald-400', 'colClass' => 'admin-kanban-col--ready'],
                'served' => ['label' => 'Served', 'orders' => $servedOrders, 'titleClass' => 'text-violet-400', 'colClass' => 'admin-kanban-col--served'],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4" id="live-orders-board">
            @foreach($columns as $key => $col)
                <div class="glass-card admin-data-panel rounded-3xl border border-white/10 min-h-[220px] flex flex-col {{ $col['colClass'] }}" data-column="{{ $key }}">
                    <div class="px-4 py-3 border-b border-white/5 flex justify-between items-center">
                        <h3 class="text-xs font-black uppercase tracking-widest {{ $col['titleClass'] }}">{{ $col['label'] }}</h3>
                        <span class="text-xs font-bold text-white/40 tabular-nums live-col-count">{{ $col['orders']->count() }}</span>
                    </div>
                    <div class="p-3 space-y-3 flex-1 overflow-y-auto max-h-[70vh] custom-scrollbar live-col-body">
                        @forelse($col['orders'] as $order)
                            @include('admin.live-orders.partials.order-card', ['order' => $order])
                        @empty
                            <p class="text-center text-white/30 text-xs py-10 live-col-empty">No orders in this stage</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
    (function () {
        const root = document.getElementById('live-orders-root');
        if (!root) return;

        const feedUrl = root.dataset.feedUrl;
        const pollMs = Math.max(10, parseInt(root.dataset.poll || '30', 10)) * 1000;

        const renderOrder = (o) => (
            '<a href="' + o.url + '" class="block p-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/5 transition-all group">' +
            '<p class="text-sm font-bold text-white group-hover:text-violet-200">#' + o.number + ' · Table ' + o.table + '</p>' +
            '<p class="text-[10px] text-white/50 mt-1 truncate">' + (o.restaurant || '') + '</p>' +
            '<p class="text-xs text-cyan-300 mt-2 font-semibold tabular-nums">' + o.amount_formatted + '</p>' +
            (o.waiter ? '<p class="text-[10px] text-white/40 mt-1">' + o.waiter + '</p>' : '') +
            '</a>'
        );

        const applyFeed = (data) => {
            const counts = data.counts || {};
            const setStat = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
            setStat('live-stat-total', data.total_live ?? 0);
            ['pending', 'preparing', 'ready', 'served'].forEach(s => setStat('live-stat-' + s, counts[s] ?? 0));

            const columns = data.columns || {};
            document.querySelectorAll('#live-orders-board [data-column]').forEach(col => {
                const key = col.dataset.column;
                const orders = columns[key] || [];
                const countEl = col.querySelector('.live-col-count');
                const body = col.querySelector('.live-col-body');
                if (countEl) countEl.textContent = String(orders.length);
                if (!body) return;
                body.innerHTML = orders.length
                    ? orders.map(renderOrder).join('')
                    : '<p class="text-center text-white/30 text-xs py-10 live-col-empty">No orders in this stage</p>';
            });

            const refreshed = document.getElementById('live-orders-refreshed');
            if (refreshed) refreshed.textContent = new Date().toLocaleTimeString();
        };

        const fetchFeed = () => {
            fetch(feedUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(applyFeed)
                .catch(() => {});
        };

        document.getElementById('live-orders-refresh')?.addEventListener('click', fetchFeed);
        setInterval(fetchFeed, pollMs);
    })();
    </script>
</x-admin-layout>
