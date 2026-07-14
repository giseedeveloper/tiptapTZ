<x-manager-layout>
    <x-slot name="header">Table Occupancy</x-slot>

    @php
        $summary = $snapshot['summary'] ?? [];
        $zones = $snapshot['zones'] ?? [];
        $occupancyPct = (float) ($summary['occupancy_percent'] ?? 0);
    @endphp

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[10px] font-bold text-cyan-400 uppercase tracking-[0.2em] mb-1">Floor Operations</p>
            <h2 class="text-3xl font-bold text-white tracking-tight">Table Occupancy</h2>
            <p class="text-sm text-white/45 mt-1">{{ $restaurantName }} · live free / occupied floor map</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 glass px-4 py-2.5 rounded-xl">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-[11px] font-bold text-emerald-500 uppercase tracking-wider">Live · <span id="occupancy-refreshed">just now</span></span>
            </div>
            <a href="{{ route('manager.tables.index') }}" class="glass px-4 py-2.5 rounded-xl text-xs font-semibold text-white/70 hover:text-white transition-all">
                Manage tables
            </a>
            <a href="{{ route('manager.orders.live') }}" class="glass px-4 py-2.5 rounded-xl text-xs font-semibold text-white/70 hover:text-white transition-all">
                Live orders
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8" id="occupancy-summary">
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Occupancy</p>
            <p class="text-3xl font-bold text-cyan-300 tabular-nums" data-field="occupancy_percent">{{ number_format($occupancyPct, 1) }}%</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Occupied</p>
            <p class="text-3xl font-bold text-rose-300 tabular-nums" data-field="occupied">{{ (int) ($summary['occupied'] ?? 0) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Free</p>
            <p class="text-3xl font-bold text-emerald-300 tabular-nums" data-field="free">{{ (int) ($summary['free'] ?? 0) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Active tables</p>
            <p class="text-3xl font-bold text-white tabular-nums" data-field="active_tables">{{ (int) ($summary['active_tables'] ?? 0) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Live orders</p>
            <p class="text-3xl font-bold text-violet-300 tabular-nums" data-field="live_orders">{{ (int) ($summary['live_orders'] ?? 0) }}</p>
        </div>
        <div class="glass-card rounded-2xl p-5">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Overdue 45m+</p>
            <p class="text-3xl font-bold text-amber-300 tabular-nums" data-field="overdue">{{ (int) ($summary['overdue'] ?? 0) }}</p>
        </div>
    </div>

    <div class="mb-6 flex flex-wrap gap-2 text-xs">
        <span class="inline-flex items-center gap-2 glass px-3 py-1.5 rounded-lg text-white/70">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> Free
        </span>
        <span class="inline-flex items-center gap-2 glass px-3 py-1.5 rounded-lg text-white/70">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span> Occupied
        </span>
        <span class="inline-flex items-center gap-2 glass px-3 py-1.5 rounded-lg text-white/70">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Overdue
        </span>
        <span class="inline-flex items-center gap-2 glass px-3 py-1.5 rounded-lg text-white/70">
            <span class="w-2.5 h-2.5 rounded-full bg-white/30"></span> Inactive
        </span>
    </div>

    <div id="occupancy-floor" class="space-y-8">
        @forelse($zones as $zone)
            <section class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <div>
                        <h3 class="text-lg font-bold text-white">{{ $zone['name'] }}</h3>
                        <p class="text-xs text-white/45 mt-0.5">
                            {{ $zone['occupied'] }} occupied · {{ $zone['free'] }} free
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-3">
                    @foreach($zone['tables'] as $table)
                        @php
                            $status = $table['status'];
                            $overdue = !empty($table['order']['is_overdue']);
                            $border = match(true) {
                                $status === 'inactive' => 'border-white/10 bg-white/5 opacity-60',
                                $overdue => 'border-amber-400/50 bg-amber-500/10',
                                $status === 'occupied' => 'border-rose-400/40 bg-rose-500/10',
                                default => 'border-emerald-400/35 bg-emerald-500/10',
                            };
                            $dot = match(true) {
                                $status === 'inactive' => 'bg-white/30',
                                $overdue => 'bg-amber-400',
                                $status === 'occupied' => 'bg-rose-400',
                                default => 'bg-emerald-400',
                            };
                        @endphp
                        <article class="rounded-2xl border p-4 {{ $border }}" data-table-id="{{ $table['id'] }}">
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div>
                                    <p class="text-lg font-bold text-white leading-none">{{ $table['name'] }}</p>
                                    <p class="text-[10px] uppercase tracking-wider text-white/40 mt-1">
                                        {{ $table['capacity'] > 0 ? $table['capacity'].' seats' : 'No capacity' }}
                                    </p>
                                </div>
                                <span class="w-2.5 h-2.5 rounded-full {{ $dot }} mt-1"></span>
                            </div>

                            @if($status === 'occupied' && !empty($table['order']))
                                <p class="text-xs font-semibold text-white/85 mb-1">
                                    Order #{{ $table['order']['id'] }}
                                </p>
                                <p class="text-[11px] text-white/55 mb-2">
                                    {{ $table['order']['status_label'] }}
                                    @if($table['order']['elapsed_minutes'] !== null)
                                        · {{ $table['order']['elapsed_minutes'] }}m
                                    @endif
                                </p>
                                <p class="text-[11px] text-white/45 truncate">
                                    {{ $table['order']['waiter_name'] ?: ($table['assigned_waiter']['name'] ?? 'No waiter') }}
                                </p>
                            @elseif($status === 'inactive')
                                <p class="text-xs text-white/40">Inactive</p>
                            @else
                                <p class="text-xs font-semibold text-emerald-300 mb-1">Free</p>
                                <p class="text-[11px] text-white/45 truncate">
                                    {{ $table['assigned_waiter']['name'] ?? 'Unassigned' }}
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="glass-card rounded-2xl p-10 text-center">
                <p class="text-white/70 font-medium">No tables yet</p>
                <p class="text-sm text-white/40 mt-1">Add tables to see live occupancy.</p>
                <a href="{{ route('manager.tables.index') }}" class="inline-flex mt-4 px-5 py-2.5 rounded-xl bg-fin-primary text-white text-sm font-semibold">
                    Add tables
                </a>
            </div>
        @endforelse
    </div>

    <script>
        (function () {
            const feedUrl = @json(route('manager.tables.occupancy.feed'));
            const floor = document.getElementById('occupancy-floor');
            const summaryEl = document.getElementById('occupancy-summary');
            const refreshedEl = document.getElementById('occupancy-refreshed');

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;');
            }

            function cardBorder(table) {
                if (table.status === 'inactive') return 'border-white/10 bg-white/5 opacity-60';
                if (table.order?.is_overdue) return 'border-amber-400/50 bg-amber-500/10';
                if (table.status === 'occupied') return 'border-rose-400/40 bg-rose-500/10';
                return 'border-emerald-400/35 bg-emerald-500/10';
            }

            function cardDot(table) {
                if (table.status === 'inactive') return 'bg-white/30';
                if (table.order?.is_overdue) return 'bg-amber-400';
                if (table.status === 'occupied') return 'bg-rose-400';
                return 'bg-emerald-400';
            }

            function renderTableCard(table) {
                let body = '';
                if (table.status === 'occupied' && table.order) {
                    const mins = table.order.elapsed_minutes != null ? ` · ${table.order.elapsed_minutes}m` : '';
                    const waiter = table.order.waiter_name || table.assigned_waiter?.name || 'No waiter';
                    body = `
                        <p class="text-xs font-semibold text-white/85 mb-1">Order #${escapeHtml(table.order.id)}</p>
                        <p class="text-[11px] text-white/55 mb-2">${escapeHtml(table.order.status_label)}${escapeHtml(mins)}</p>
                        <p class="text-[11px] text-white/45 truncate">${escapeHtml(waiter)}</p>
                    `;
                } else if (table.status === 'inactive') {
                    body = `<p class="text-xs text-white/40">Inactive</p>`;
                } else {
                    const waiter = table.assigned_waiter?.name || 'Unassigned';
                    body = `
                        <p class="text-xs font-semibold text-emerald-300 mb-1">Free</p>
                        <p class="text-[11px] text-white/45 truncate">${escapeHtml(waiter)}</p>
                    `;
                }

                return `
                    <article class="rounded-2xl border p-4 ${cardBorder(table)}" data-table-id="${table.id}">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <p class="text-lg font-bold text-white leading-none">${escapeHtml(table.name)}</p>
                                <p class="text-[10px] uppercase tracking-wider text-white/40 mt-1">
                                    ${table.capacity > 0 ? `${table.capacity} seats` : 'No capacity'}
                                </p>
                            </div>
                            <span class="w-2.5 h-2.5 rounded-full ${cardDot(table)} mt-1"></span>
                        </div>
                        ${body}
                    </article>
                `;
            }

            function renderFloor(data) {
                const zones = data.zones || [];
                if (!zones.length) {
                    floor.innerHTML = `
                        <div class="glass-card rounded-2xl p-10 text-center">
                            <p class="text-white/70 font-medium">No tables yet</p>
                        </div>`;
                    return;
                }

                floor.innerHTML = zones.map((zone) => `
                    <section class="glass-card rounded-2xl p-6">
                        <div class="flex items-center justify-between gap-3 mb-5">
                            <div>
                                <h3 class="text-lg font-bold text-white">${escapeHtml(zone.name)}</h3>
                                <p class="text-xs text-white/45 mt-0.5">${zone.occupied} occupied · ${zone.free} free</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-3">
                            ${(zone.tables || []).map(renderTableCard).join('')}
                        </div>
                    </section>
                `).join('');
            }

            function renderSummary(summary) {
                const map = {
                    occupancy_percent: `${Number(summary.occupancy_percent || 0).toFixed(1)}%`,
                    occupied: summary.occupied || 0,
                    free: summary.free || 0,
                    active_tables: summary.active_tables || 0,
                    live_orders: summary.live_orders || 0,
                    overdue: summary.overdue || 0,
                };
                Object.entries(map).forEach(([key, value]) => {
                    const el = summaryEl.querySelector(`[data-field="${key}"]`);
                    if (el) el.textContent = value;
                });
            }

            async function refresh() {
                try {
                    const res = await fetch(feedUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) return;
                    const payload = await res.json();
                    if (!payload.success || !payload.data) return;
                    renderSummary(payload.data.summary || {});
                    renderFloor(payload.data);
                    if (refreshedEl) refreshedEl.textContent = 'just now';
                } catch (e) {
                    console.error('Occupancy refresh failed', e);
                }
            }

            setInterval(refresh, 10000);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) refresh();
            });
        })();
    </script>
</x-manager-layout>
