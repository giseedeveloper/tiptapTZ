<script>
(function () {
    const currencySymbol = @json($currencySymbol);
    const pulseUrl = @json(route('admin.tiptap-analysis.platform-pulse'));
    const root = document.getElementById('hub-live-stats');
    if (!root) return;

    const fmt = (n) => new Intl.NumberFormat().format(Math.round(n));

    fetch(pulseUrl + '?days=30&overview=1', {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(payload => {
            const p = payload.platform_pulse || {};
            const chips = [
                { label: 'Active venues', value: fmt(p.active_venues || 0), sub: 'of ' + fmt(p.total_venues || 0) },
                { label: 'Orders today', value: fmt(p.orders_today || 0), sub: 'platform-wide' },
                { label: 'Bot events', value: fmt(p.bot_events || 0), sub: 'last 30 days' },
                { label: 'Avg rating', value: (p.avg_rating || 0) + '★', sub: fmt(p.feedback_count || 0) + ' reviews' },
            ];
            root.innerHTML = chips.map(c =>
                '<div class="hub-stat-chip hub-animate-in">' +
                '<p class="hub-stat-chip__label">' + c.label + '</p>' +
                '<p class="hub-stat-chip__value">' + c.value + '</p>' +
                '<p class="hub-stat-chip__sub">' + c.sub + '</p></div>'
            ).join('');
        })
        .catch(() => {
            root.innerHTML =
                '<div class="hub-stat-chip col-span-2 sm:col-span-4 hub-animate-in">' +
                '<p class="hub-stat-chip__label text-white/40">Live stats</p>' +
                '<p class="hub-stat-chip__value text-base text-white/50">Open a report to explore data</p></div>';
        });
})();
</script>
