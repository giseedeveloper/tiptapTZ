@php
    $pollSeconds = (int) config('tiptap.admin_live_poll_seconds', 30);
@endphp
<script>
(function () {
    const statsUrl = @json(route('admin.dashboard.stats'));
    const analyticsUrl = @json(route('admin.dashboard.analytics'));
    const pollMs = {{ max(10, $pollSeconds) }} * 1000;
    const currencySymbol = @json($currencySymbol);

    const pctHeight = (value, max, minPos = 6, minZero = 2) => {
        if (max <= 0) return value > 0 ? minPos : minZero;
        return Math.max((value / max) * 100, value > 0 ? minPos : minZero);
    };

    const setText = (id, text) => {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    };

    const formatK = (n) => currencySymbol + ' ' + new Intl.NumberFormat().format(Math.round(n / 100) / 10) + 'K';

    const buildDonut = (containerId, segments, centerLabel, centerSub) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        const total = Math.max(segments.reduce((s, x) => s + (x.value || 0), 0), 1);
        let cursor = 0;
        const parts = segments.map(seg => {
            const pct = ((seg.value || 0) / total) * 100;
            const end = Math.min(100, cursor + pct);
            const bit = (seg.color || '#8C71F6') + ' ' + cursor + '% ' + end + '%';
            cursor = end;
            return bit;
        });
        const gradient = parts.length
            ? 'conic-gradient(from -90deg, ' + parts.join(', ') + ')'
            : 'conic-gradient(from -90deg, rgba(255,255,255,0.08) 0% 100%)';

        const legend = segments.map(seg => (
            '<li class="flex items-center justify-between gap-2 text-xs">' +
            '<span class="flex items-center gap-2 text-white/70 min-w-0">' +
            '<span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:' + seg.color + '"></span>' +
            '<span class="truncate">' + seg.label + '</span></span>' +
            '<span class="font-bold text-white tabular-nums">' + seg.value + '</span></li>'
        )).join('');

        root.innerHTML =
            '<div class="flex flex-col items-center gap-5">' +
            '<div class="relative shrink-0" style="width:9rem;height:9rem">' +
            '<div class="w-full h-full rounded-full shadow-lg shadow-fin-primary/20" style="background:' + gradient + '"></div>' +
            '<div class="absolute inset-[14%] rounded-full bg-[#12101c] border border-white/10 flex flex-col items-center justify-center text-center px-2">' +
            '<span class="text-2xl font-black text-white leading-none">' + centerLabel + '</span>' +
            (centerSub ? '<span class="text-[9px] font-bold text-white/40 uppercase tracking-widest mt-1">' + centerSub + '</span>' : '') +
            '</div></div>' +
            '<ul class="w-full space-y-2">' + legend + '</ul></div>';
    };

    const refreshStats = (data) => {
        setText('stat-total-restaurants', data.total_restaurants);
        setText('stat-total-waiters', data.total_waiters ?? 0);
        setText('stat-active-orders', data.active_orders);
        setText('stat-pending-withdrawals', data.pending_withdrawals);
        setText('stat-pending-customer-requests', data.pending_customer_requests);
        setText('stat-orders-today', data.orders_today);
        setText('stat-revenue-today', currencySymbol + ' ' + new Intl.NumberFormat().format(data.revenue_today ?? 0));
        setText('stat-total-revenue', formatK(data.total_revenue ?? 0));
        setText('stat-avg-feedback', data.avg_feedback_rating ?? '0');
    };

    const refreshAnalytics = (payload) => {
        const a = payload.analytics || {};
        const stats = payload.stats || {};
        refreshStats(stats);

        const wc = a.week_comparison || {};
        setText('hero-revenue-this-week', currencySymbol + ' ' + new Intl.NumberFormat().format(wc.revenue_this_week ?? 0));
        const revCh = Number(wc.revenue_change ?? 0);
        const ordCh = Number(wc.orders_change ?? 0);
        const revBadge = document.getElementById('badge-revenue-wow');
        const ordBadge = document.getElementById('badge-orders-wow');
        if (revBadge) {
            revBadge.textContent = (revCh >= 0 ? '↑' : '↓') + ' ' + Math.abs(revCh) + '% vs last week';
            revBadge.className = 'text-[10px] font-bold mt-1 ' + (revCh >= 0 ? 'text-emerald-400' : 'text-rose-400');
        }
        const revChartBadge = document.getElementById('badge-revenue-wow-chart');
        if (revChartBadge) {
            revChartBadge.textContent = (revCh >= 0 ? '+' : '') + revCh + '% WoW';
        }
        if (ordBadge) {
            ordBadge.textContent = (ordCh >= 0 ? '+' : '') + ordCh + '% WoW';
            ordBadge.className = 'text-xs font-bold px-2 py-1 rounded-lg border ' +
                (ordCh >= 0 ? 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20' : 'text-rose-400 bg-rose-500/10 border-rose-500/20');
        }

        const revTrend = a.revenue_trend || [];
        const maxRev = Math.max(...revTrend.map(d => d.revenue ?? 0), 1);
        revTrend.forEach((day, i) => {
            const col = document.querySelector('#admin-revenue-chart .admin-rev-day[data-index="' + i + '"]');
            if (!col) return;
            const bar = col.querySelector('.admin-rev-bar');
            const tip = col.querySelector('.admin-rev-tip');
            const rev = Number(day.revenue ?? 0);
            if (bar) bar.style.height = pctHeight(rev, maxRev) + '%';
            if (tip) tip.textContent = currencySymbol + ' ' + new Intl.NumberFormat().format(rev);
        });

        const ordTrend = a.orders_trend || [];
        const maxOrd = Math.max(...ordTrend.map(d => d.count ?? 0), 1);
        ordTrend.forEach((day, i) => {
            const col = document.querySelector('#admin-orders-chart .admin-ord-day[data-index="' + i + '"]');
            if (!col) return;
            const bar = col.querySelector('.admin-ord-bar');
            const countEl = col.querySelector('.admin-ord-count');
            const cnt = Number(day.count ?? 0);
            if (bar) bar.style.height = pctHeight(cnt, maxOrd) + '%';
            if (countEl) countEl.textContent = String(cnt);
        });

        const orderTotal = (a.orders_by_status || []).reduce((s, x) => s + (x.value || 0), 0);
        buildDonut('admin-donut-order-pipeline', a.orders_by_status || [], orderTotal, 'Orders');

        const venue = a.restaurant_split || {};
        buildDonut('admin-donut-venue-health', venue.segments || [], stats.total_restaurants ?? 0, 'Venues');

        const payMethods = a.payment_methods || [];
        const payTotal = payMethods.reduce((s, x) => s + (x.value || 0), 0);
        const payRoot = document.getElementById('admin-donut-payment-mix');
        if (payRoot) {
            if (payMethods.length === 0) {
                payRoot.innerHTML = '<p class="text-center text-white/40 text-sm py-12">No payment data yet</p>';
            } else {
                buildDonut('admin-donut-payment-mix', payMethods, payTotal, 'Payments');
            }
        }

        const ratings = a.rating_distribution || [];
        const maxRating = Math.max(...ratings.map(r => r.count ?? 0), 1);
        ratings.forEach(row => {
            const bar = document.querySelector('#admin-rating-bars [data-stars="' + row.stars + '"] .admin-rating-fill');
            if (bar) {
                const w = maxRating > 0 ? (row.count / maxRating) * 100 : 0;
                bar.style.width = Math.max(w, row.count > 0 ? 4 : 0) + '%';
            }
            const cnt = document.querySelector('#admin-rating-bars [data-stars="' + row.stars + '"] .admin-rating-count');
            if (cnt) cnt.textContent = String(row.count ?? 0);
        });

        const topList = document.getElementById('admin-top-restaurants');
        if (topList) {
            const venues = a.top_restaurants || [];
            if (venues.length === 0) {
                topList.innerHTML = '<p class="px-6 py-12 text-center text-white/40">No venue revenue yet</p>';
            } else {
                topList.innerHTML = venues.map((v, i) => (
                    '<a href="' + (v.url || ('/admin/restaurants/' + v.id)) + '" class="flex items-center gap-4 px-6 py-4 hover:bg-white/5 transition-all group">' +
                    '<span class="w-8 h-8 rounded-lg bg-gradient-to-br from-fin-primary/30 to-fin-primary-dark/30 flex items-center justify-center text-xs font-black text-white border border-white/10">' + (i + 1) + '</span>' +
                    '<div class="flex-1 min-w-0"><p class="font-bold text-white truncate group-hover:text-fin-lavender">' + v.name + '</p>' +
                    '<p class="text-[10px] text-white/40">' + new Intl.NumberFormat().format(v.orders) + ' orders</p></div>' +
                    '<p class="text-sm font-black text-emerald-400 tabular-nums">' + currencySymbol + ' ' + new Intl.NumberFormat().format(v.revenue) + '</p></a>'
                )).join('');
            }
        }
    };

    const tick = () => {
        fetch(analyticsUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(refreshAnalytics)
            .catch(() => {
                fetch(statsUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(refreshStats)
                    .catch(() => {});
            });
    };

    tick();
    setInterval(tick, pollMs);
})();
</script>
