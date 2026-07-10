<x-manager-layout>
    <x-slot name="header">Customer Engagement</x-slot>

    <div
        x-data="menuEngagementPage({
            alertsUrl: @json(route('manager.menu-engagement.alerts')),
            timeoutMinutes: {{ $timeoutMinutes }},
            initialAlerts: @json($activeAlerts),
            initialStats: @json($stats),
            unread: {{ $unreadNotifications }},
        })"
        class="space-y-8"
    >
        {{-- Header --}}
        <div class="relative overflow-hidden rounded-3xl border border-amber-500/20 bg-gradient-to-br from-amber-500/10 via-violet-600/10 to-transparent p-8">
            <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-amber-400/10 blur-3xl"></div>
            <div class="absolute -left-10 bottom-0 h-32 w-32 rounded-full bg-violet-500/10 blur-3xl"></div>
            <div class="relative flex flex-wrap items-start justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-[10px] font-bold uppercase tracking-[0.2em] text-amber-300">
                        <span class="h-2 w-2 rounded-full bg-amber-400 animate-pulse" x-show="alerts.some(a => a.is_overdue)"></span>
                        Live monitoring
                    </div>
                    <h2 class="mt-4 text-3xl font-bold tracking-tight text-white">Customer Engagement Alerts</h2>
                    <p class="mt-2 text-sm leading-relaxed text-white/55">
                        Get notified when a customer views your WhatsApp menu but does not place an order within
                        <span class="font-semibold text-white">{{ $timeoutMinutes }} minutes</span>.
                        Proactively engage guests before they leave.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    @if($unreadNotifications > 0)
                        <form action="{{ route('manager.menu-engagement.notifications.read') }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-semibold text-white/80 hover:bg-white/10">
                                Mark {{ $unreadNotifications }} notification{{ $unreadNotifications === 1 ? '' : 's' }} read
                            </button>
                        </form>
                    @endif
                    <button type="button" @click="refresh()" class="rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-violet-500">
                        Refresh now
                    </button>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Watching now</p>
                <p class="mt-1 text-3xl font-bold text-amber-300" x-text="stats.pending">{{ $stats['pending'] }}</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Alerts today</p>
                <p class="mt-1 text-3xl font-bold text-rose-400" x-text="stats.notified_today">{{ $stats['notified_today'] }}</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Converted today</p>
                <p class="mt-1 text-3xl font-bold text-emerald-400" x-text="stats.converted_today">{{ $stats['converted_today'] }}</p>
            </div>
            <div class="glass-card rounded-2xl p-5">
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Menu views today</p>
                <p class="mt-1 text-3xl font-bold text-violet-400" x-text="stats.views_today">{{ $stats['views_today'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
            {{-- Active alerts --}}
            <div class="xl:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Active sessions</h3>
                    <p class="text-xs text-white/40">Auto-refreshes every 30s</p>
                </div>

                <template x-if="alerts.length === 0">
                    <div class="glass-card rounded-2xl border border-dashed border-white/10 p-10 text-center">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 text-2xl">📋</div>
                        <p class="text-white font-semibold">No customers waiting right now</p>
                        <p class="mt-1 text-sm text-white/45">When someone views your menu via WhatsApp, they will appear here.</p>
                    </div>
                </template>

                <div class="space-y-3">
                    <template x-for="alert in alerts" :key="alert.id">
                        <div
                            class="glass-card rounded-2xl border p-5 transition-all"
                            :class="alert.is_overdue ? 'border-rose-500/40 bg-rose-500/5' : 'border-white/10'"
                        >
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl text-xl"
                                        :class="alert.is_overdue ? 'bg-rose-500/15' : 'bg-amber-500/15'"
                                        x-text="alert.is_overdue ? '⚠️' : '👀'"
                                    ></div>
                                    <div>
                                        <p class="text-sm font-bold text-white" x-text="'Table ' + alert.table"></p>
                                        <p class="mt-1 text-sm text-white/70" x-text="alert.message"></p>
                                        <p class="mt-2 text-[11px] uppercase tracking-wider text-white/35">
                                            Viewed <span x-text="alert.elapsed_minutes"></span> min ago
                                            <span x-show="alert.status === 'notified'" class="ml-2 text-rose-300">· Alert sent</span>
                                        </p>
                                    </div>
                                </div>
                                <form :action="`{{ url('/manager/menu-engagement') }}/${alert.id}/dismiss`" method="POST">
                                    @csrf
                                    <button type="submit" class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-white/70 hover:bg-white/10">
                                        Dismiss
                                    </button>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Settings --}}
            <div class="space-y-4">
                <h3 class="text-lg font-bold text-white">Alert settings</h3>
                <div class="glass-card rounded-2xl border border-white/10 p-6">
                    <form action="{{ route('manager.menu-engagement.settings') }}" method="POST" class="space-y-5">
                        @csrf
                        <label class="flex items-center justify-between gap-4">
                            <span>
                                <span class="block text-sm font-semibold text-white">Enable alerts</span>
                                <span class="block text-xs text-white/45 mt-1">Notify managers when guests don't order in time.</span>
                            </span>
                            <input type="hidden" name="menu_engagement_alerts_enabled" value="0">
                            <input
                                type="checkbox"
                                name="menu_engagement_alerts_enabled"
                                value="1"
                                @checked(old('menu_engagement_alerts_enabled', $restaurant->menu_engagement_alerts_enabled))
                                class="h-5 w-5 rounded border-white/20 bg-white/5 text-violet-500 focus:ring-violet-500"
                            >
                        </label>

                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40">Timeout (minutes)</label>
                            <input
                                type="number"
                                name="menu_engagement_timeout_minutes"
                                min="5"
                                max="60"
                                value="{{ old('menu_engagement_timeout_minutes', $restaurant->menu_engagement_timeout_minutes ?? 10) }}"
                                class="mt-2 w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-white text-sm focus:ring-2 focus:ring-violet-500"
                            >
                            <p class="mt-2 text-xs text-white/40">Recommended: 5–10 minutes.</p>
                            @error('menu_engagement_timeout_minutes')
                                <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-violet-600 py-3 text-sm font-semibold text-white hover:bg-violet-500">
                            Save settings
                        </button>
                    </form>
                </div>

                <div class="glass-card rounded-2xl border border-white/10 p-6">
                    <h4 class="text-sm font-bold text-white">How it works</h4>
                    <ol class="mt-4 space-y-3 text-sm text-white/55 list-decimal list-inside">
                        <li>Customer scans table QR and opens menu on WhatsApp.</li>
                        <li>System starts a timer for your configured timeout.</li>
                        <li>If no order is placed, managers receive an alert.</li>
                        <li>Staff can visit the table and assist the guest.</li>
                    </ol>
                </div>
            </div>
        </div>

        {{-- History --}}
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-white">Recent history</h3>
            <div class="glass-card rounded-2xl border border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white/5 text-left text-[10px] font-bold uppercase tracking-wider text-white/40">
                            <tr>
                                <th class="px-5 py-4">Table</th>
                                <th class="px-5 py-4">Menu viewed</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Outcome time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($history as $session)
                                <tr class="hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 font-semibold text-white">{{ $session->resolvedTableLabel() }}</td>
                                    <td class="px-5 py-4 text-white/60">{{ $session->menu_viewed_at?->format('M j, H:i') }}</td>
                                    <td class="px-5 py-4">
                                        @php
                                            $badge = match ($session->status) {
                                                'converted' => 'bg-emerald-500/15 text-emerald-300',
                                                'notified' => 'bg-rose-500/15 text-rose-300',
                                                'dismissed' => 'bg-white/10 text-white/50',
                                                default => 'bg-amber-500/15 text-amber-300',
                                            };
                                        @endphp
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $badge }}">
                                            {{ $session->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-white/60">
                                        {{ ($session->converted_at ?? $session->notified_at ?? $session->dismissed_at)?->format('M j, H:i') ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-white/40">No engagement history yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($history->hasPages())
                    <div class="border-t border-white/5 px-5 py-4">
                        {{ $history->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function menuEngagementPage(config) {
            return {
                alerts: config.initialAlerts || [],
                stats: config.initialStats || {},
                unread: config.unread || 0,
                refresh() {
                    fetch(config.alertsUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (!data.success) return;
                            this.alerts = data.alerts || [];
                            this.stats = data.stats || this.stats;
                            this.unread = data.unread_notifications ?? this.unread;
                        })
                        .catch(() => {});
                },
                init() {
                    setInterval(() => this.refresh(), 30000);
                },
            };
        }
    </script>
    @endpush
</x-manager-layout>
