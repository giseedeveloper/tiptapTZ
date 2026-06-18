<script>
(function () {
    const currencySymbol = @json($currencySymbol);
    const activeSection = @json($activeSection ?? 'platform');
    const urls = {
        snapshot: @json(route('admin.tiptap-analysis.snapshot')),
        whatsapp: @json(route('admin.tiptap-analysis.whatsapp-engagement')),
        qr: @json(route('admin.tiptap-analysis.qr-entry-points')),
        funnel: @json(route('admin.tiptap-analysis.customer-journey')),
        feedback: @json(route('admin.tiptap-analysis.feedback-overview')),
        payments: @json(route('admin.tiptap-analysis.tips-and-payments')),
        language: @json(route('admin.tiptap-analysis.language-and-behavior')),
        pulse: @json(route('admin.tiptap-analysis.platform-pulse')),
    };

    const daysEl = document.getElementById('filter-days');
    const refreshBtn = document.getElementById('analysis-refresh-btn');
    const lastUpdatedEl = document.getElementById('analysis-last-updated');

    const fmt = (n) => new Intl.NumberFormat().format(Math.round(n));
    const fmtMoney = (n) => currencySymbol + ' ' + fmt(n);
    const esc = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    const queryParams = () => {
        const params = new URLSearchParams();
        const days = daysEl?.value || '30';
        params.set('days', days);
        params.set('trend_days', days);
        params.set('overview', '1');
        return params.toString();
    };

    const fetchJson = async (url) => {
        const sep = url.includes('?') ? '&' : '?';
        const res = await fetch(url + sep + queryParams(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) throw new Error('Failed to load ' + url);
        return res.json();
    };

    const pctHeight = (value, max, minPos = 8, minZero = 3) => {
        if (max <= 0) return value > 0 ? minPos : minZero;
        return Math.max((value / max) * 100, value > 0 ? minPos : minZero);
    };

    const buildDonut = (containerId, segments, centerLabel, centerSub) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        const filtered = (segments || []).filter(s => (s.value || 0) > 0);
        const total = Math.max(filtered.reduce((s, x) => s + (x.value || 0), 0), 1);
        let cursor = 0;
        const parts = filtered.map(seg => {
            const pct = ((seg.value || 0) / total) * 100;
            const end = Math.min(100, cursor + pct);
            const bit = (seg.color || '#8C71F6') + ' ' + cursor + '% ' + end + '%';
            cursor = end;
            return bit;
        });
        const gradient = parts.length
            ? 'conic-gradient(from -90deg, ' + parts.join(', ') + ')'
            : 'conic-gradient(from -90deg, rgba(255,255,255,0.08) 0% 100%)';

        if (filtered.length === 0) {
            root.innerHTML = '<p class="text-sm text-white/40 text-center py-8">No data yet for this period</p>';
            return;
        }

        const legend = filtered.map(seg => (
            '<li class="flex items-center justify-between gap-2 text-xs">' +
            '<span class="flex items-center gap-2 text-white/70 min-w-0">' +
            '<span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:' + seg.color + '"></span>' +
            '<span class="truncate">' + esc(seg.label) + '</span></span>' +
            '<span class="font-bold text-white tabular-nums">' + seg.value + '</span></li>'
        )).join('');

        root.innerHTML =
            '<div class="flex flex-col items-center gap-5">' +
            '<div class="relative shrink-0" style="width:9rem;height:9rem">' +
            '<div class="w-full h-full rounded-full shadow-lg shadow-fin-primary/20" style="background:' + gradient + '"></div>' +
            '<div class="absolute inset-[14%] rounded-full bg-[#12101c] border border-white/10 flex flex-col items-center justify-center text-center px-2">' +
            '<span class="text-2xl font-black text-white leading-none">' + esc(centerLabel) + '</span>' +
            (centerSub ? '<span class="text-[9px] font-bold text-white/40 uppercase tracking-widest mt-1">' + esc(centerSub) + '</span>' : '') +
            '</div></div>' +
            '<ul class="w-full space-y-2">' + legend + '</ul></div>';
    };

    const buildBarChart = (containerId, items, valueKey, labelKey, barClass) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        const data = items || [];
        if (data.length === 0) {
            root.innerHTML = '<p class="text-sm text-white/40 text-center py-8 w-full">No data yet</p>';
            return;
        }
        const max = Math.max(...data.map(d => d[valueKey] || 0), 1);
        root.innerHTML = data.map((d, i) => {
            const h = pctHeight(d[valueKey] || 0, max);
            const label = d[labelKey] || d.day || d.label || '';
            return '<div class="flex-1 h-full flex flex-col justify-end items-center gap-1 min-w-[20px] group" title="' + esc(label) + ': ' + (d[valueKey] || 0) + '">' +
                '<div class="' + (barClass || 'bg-gradient-to-t from-fin-primary-dark to-fin-primary') + ' w-full rounded-t-md opacity-90 group-hover:opacity-100 transition" style="height:' + h + '%;min-height:' + ((d[valueKey] || 0) > 0 ? '8px' : '3px') + '"></div>' +
                '<span class="text-[8px] text-white/35 truncate w-full text-center">' + esc(String(label).slice(-5)) + '</span></div>';
        }).join('');
    };

    const kpiCard = (label, value, sub, colorClass) =>
        '<div class="glass-card rounded-2xl p-5 border border-white/10">' +
        '<p class="text-[9px] font-black text-white/40 uppercase tracking-wider">' + esc(label) + '</p>' +
        '<p class="text-xl font-black ' + (colorClass || 'text-white') + ' mt-2 tabular-nums">' + value + '</p>' +
        (sub ? '<p class="text-[10px] text-white/40 mt-1">' + esc(sub) + '</p>' : '') +
        '</div>';

    const renderInsights = (data) => {
        const root = document.getElementById('analysis-insights');
        if (!root) return;
        const cards = [];
        const wa = data.whatsapp?.whatsapp_engagement;
        const qr = data.qr?.qr_entry_points;
        const funnel = data.funnel?.customer_journey;
        const fb = data.feedback?.feedback_overview;

        if (wa?.option_usage?.length) {
            const top = [...wa.option_usage].sort((a, b) => b.value - a.value)[0];
            if (top && top.value > 0) {
                cards.push({ type: 'info', title: 'Top bot action', text: top.label + ' — ' + top.value + ' times', icon: '💬' });
            }
        }

        if (qr?.split?.length) {
            const topQr = [...qr.split].sort((a, b) => b.value - a.value)[0];
            if (topQr && topQr.value > 0) {
                cards.push({ type: 'positive', title: 'Main QR entry', text: topQr.label + ' (' + topQr.value + ' scans)', icon: '📱' });
            }
        }

        if (funnel?.biggest_drop_off) {
            cards.push({
                type: 'warning',
                title: 'Biggest drop-off',
                text: funnel.biggest_drop_off.step + ' — ' + funnel.biggest_drop_off.drop_off + ' customers lost',
                icon: '⚠️',
            });
        }

        if (fb?.low_rating_alerts?.length) {
            const worst = fb.low_rating_alerts[0];
            cards.push({
                type: 'alert',
                title: 'Rating alert',
                text: worst.name + ' — avg ' + worst.avg_rating + '★ (' + worst.review_count + ' reviews)',
                icon: '⭐',
            });
        } else if (fb?.summary?.avg_rating) {
            cards.push({
                type: 'positive',
                title: 'Customer satisfaction',
                text: 'Platform avg ' + fb.summary.avg_rating + '★ from ' + fb.summary.total_reviews + ' reviews',
                icon: '✨',
            });
        }

        if (cards.length === 0) {
            cards.push({ type: 'info', title: 'Getting started', text: 'Data will appear as customers use WhatsApp bot & QR codes', icon: '📊' });
        }

        root.innerHTML = cards.slice(0, 4).map(c =>
            '<div class="insight-card-' + c.type + ' rounded-2xl p-4 border">' +
            '<p class="text-lg mb-1">' + c.icon + '</p>' +
            '<p class="text-[10px] font-bold text-white/50 uppercase tracking-wider">' + esc(c.title) + '</p>' +
            '<p class="text-sm font-semibold text-white mt-1 leading-snug">' + esc(c.text) + '</p></div>'
        ).join('');
    };

    const platformKpiCard = (icon, label, value, sub, accent, iconStyle) =>
        '<div class="platform-kpi-card platform-animate-in" style="--kpi-accent:' + accent + '">' +
        '<div class="flex items-start justify-between gap-3">' +
        '<div class="platform-kpi-icon" style="' + (iconStyle || '') + '">' + icon + '</div>' +
        '<div class="text-right flex-1 min-w-0"><p class="text-[9px] font-black text-white/40 uppercase tracking-wider">' + esc(label) + '</p>' +
        '<p class="text-2xl font-black text-white mt-1 tabular-nums leading-none">' + value + '</p>' +
        (sub ? '<p class="text-[10px] text-white/45 mt-1.5">' + esc(sub) + '</p>' : '') +
        '</div></div></div>';

    const platformStatPill = (label, value, colorClass) =>
        '<div class="platform-stat-pill platform-animate-in">' +
        '<p class="label">' + esc(label) + '</p>' +
        '<p class="value tabular-nums ' + (colorClass || 'text-white') + '">' + value + '</p></div>';

    const buildPlatformRevenueChart = (containerId, trend) => {
        const root = document.getElementById(containerId);
        if (!root) return { total: 0, peak: null, avg: 0 };
        const data = trend || [];
        if (data.length === 0) {
            root.innerHTML = '<p class="text-sm text-white/40 text-center py-12 w-full">No revenue data yet — payments will appear here</p>';
            return { total: 0, peak: null, avg: 0 };
        }
        const max = Math.max(...data.map(d => d.revenue || 0), 1);
        const total = data.reduce((s, d) => s + (d.revenue || 0), 0);
        const peak = data.reduce((best, d) => ((d.revenue || 0) > (best?.revenue || 0) ? d : best), data[0]);
        const avg = total / data.length;

        root.innerHTML = data.map(d => {
            const h = pctHeight(d.revenue || 0, max, 6, 2);
            const label = d.label || d.day || '';
            const isPeak = peak && (d.revenue || 0) === (peak.revenue || 0) && (d.revenue || 0) > 0;
            return '<div class="platform-bar-wrap' + (isPeak ? ' is-peak' : '') + '" title="' + esc(label) + '">' +
                '<div class="platform-bar-tooltip">' + esc(label) + ' · ' + fmtMoney(d.revenue || 0) + '</div>' +
                '<div class="platform-bar" style="height:' + h + '%;min-height:' + ((d.revenue || 0) > 0 ? '6px' : '2px') + '"></div>' +
                '<span class="platform-bar-label">' + esc(String(label).slice(-5)) + '</span></div>';
        }).join('');

        return { total, peak, avg };
    };

    const buildVenueHealthRing = (containerId, restaurants) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        const active = restaurants.active || 0;
        const total = restaurants.total || 0;
        const inactive = restaurants.inactive || 0;
        const pct = total > 0 ? Math.round((active / total) * 100) : 0;
        const circumference = 2 * Math.PI * 42;
        const dash = (pct / 100) * circumference;

        if (total === 0) {
            root.innerHTML = '<p class="text-sm text-white/40 text-center">No venues registered yet</p>';
            return;
        }

        root.innerHTML =
            '<div class="platform-venue-ring platform-animate-in">' +
            '<svg width="152" height="152" viewBox="0 0 100 100">' +
            '<circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="10"/>' +
            '<circle cx="50" cy="50" r="42" fill="none" stroke="url(#venueGrad)" stroke-width="10" ' +
            'stroke-linecap="round" stroke-dasharray="' + dash + ' ' + circumference + '"/>' +
            '<defs><linearGradient id="venueGrad" x1="0%" y1="0%" x2="100%" y2="0%">' +
            '<stop offset="0%" stop-color="#10b981"/><stop offset="100%" stop-color="#34d399"/></linearGradient></defs>' +
            '</svg>' +
            '<div class="platform-venue-ring-center">' +
            '<span class="text-3xl font-black text-white tabular-nums">' + pct + '%</span>' +
            '<span class="text-[9px] font-bold text-white/40 uppercase tracking-widest mt-0.5">active</span>' +
            '</div></div>';

        const pillsRoot = document.getElementById('platform-venue-pills');
        if (pillsRoot) {
            pillsRoot.innerHTML =
                '<div class="platform-venue-pill platform-animate-in">' +
                '<p class="text-[9px] font-black text-emerald-400/80 uppercase tracking-wider">Active</p>' +
                '<p class="text-xl font-black text-white tabular-nums mt-1">' + fmt(active) + '</p></div>' +
                '<div class="platform-venue-pill platform-animate-in">' +
                '<p class="text-[9px] font-black text-rose-400/80 uppercase tracking-wider">Inactive</p>' +
                '<p class="text-xl font-black text-white tabular-nums mt-1">' + fmt(inactive) + '</p></div>';
        }
    };

    const renderPlatformInsights = (orders, restaurants, revenueStats) => {
        const root = document.getElementById('platform-insights');
        if (!root) return;
        const cards = [];
        const total = restaurants.total || 0;
        const active = restaurants.active || 0;
        const activePct = total > 0 ? Math.round((active / total) * 100) : 0;

        if (orders.today > 0) {
            cards.push({ type: 'positive', icon: '🔥', title: 'Today is active', text: fmt(orders.today) + ' orders so far today across the platform.' });
        } else {
            cards.push({ type: 'info', icon: '🌙', title: 'Quiet start', text: 'No orders yet today — peak hours may bring more activity.' });
        }

        if (orders.week > 0 && orders.month > 0) {
            const weekShare = Math.round((orders.week / orders.month) * 100);
            cards.push({
                type: weekShare >= 40 ? 'positive' : 'warning',
                icon: '📅',
                title: 'Weekly momentum',
                text: 'This week accounts for ' + weekShare + '% of monthly orders (' + fmt(orders.week) + ' of ' + fmt(orders.month) + ').',
            });
        }

        if (revenueStats.total > 0) {
            cards.push({
                type: 'info',
                icon: '💰',
                title: 'Revenue highlight',
                text: 'Best day: ' + (revenueStats.peak?.label || '—') + ' at ' + fmtMoney(revenueStats.peak?.revenue || 0) + '. Avg ' + fmtMoney(revenueStats.avg) + '/day.',
            });
        } else {
            cards.push({ type: 'info', icon: '📊', title: 'Revenue building', text: 'Payment data will populate the revenue pulse as customers pay bills.' });
        }

        if (total > 0) {
            cards.push({
                type: activePct >= 70 ? 'positive' : 'warning',
                icon: '🏪',
                title: 'Venue health',
                text: activePct + '% of venues are active (' + fmt(active) + ' of ' + fmt(total) + ').',
            });
        }

        root.innerHTML = cards.slice(0, 3).map(c =>
            '<div class="platform-insight-card platform-animate-in is-' + c.type + '">' +
            '<p class="text-lg mb-2">' + c.icon + '</p>' +
            '<p class="text-[10px] font-black text-white/45 uppercase tracking-wider">' + esc(c.title) + '</p>' +
            '<p class="text-sm font-semibold text-white/90 mt-1 leading-relaxed">' + esc(c.text) + '</p></div>'
        ).join('');
    };

    const renderSnapshot = (payload) => {
        const s = payload.snapshot || {};
        const orders = s.orders || {};
        const restaurants = s.restaurants || {};
        const sym = payload.currency_symbol || currencySymbol;
        const days = parseInt(daysEl?.value || '30', 10);

        const periodLabel = document.getElementById('platform-period-label');
        if (periodLabel) periodLabel.textContent = 'last ' + days + ' days';

        const trend = s.revenue_trend || [];
        const revenueStats = buildPlatformRevenueChart('chart-revenue-trend', trend);

        const heroRevenue = document.getElementById('platform-hero-revenue');
        if (heroRevenue) {
            heroRevenue.innerHTML = '<span class="text-fin-lavender/90 text-2xl md:text-3xl font-bold mr-1">' + esc(sym) + '</span>' +
                '<span>' + fmt(revenueStats.total) + '</span>';
        }

        const heroChips = document.getElementById('platform-hero-chips');
        if (heroChips) {
            heroChips.innerHTML =
                '<span class="platform-chip platform-animate-in">📦 <strong>' + fmt(orders.month || 0) + '</strong> orders/mo</span>' +
                '<span class="platform-chip platform-animate-in">🏪 <strong>' + fmt(restaurants.active || 0) + '</strong> active venues</span>' +
                '<span class="platform-chip platform-animate-in">📈 <strong>' + fmt(orders.today || 0) + '</strong> today</span>';
        }

        const statsRoot = document.getElementById('platform-revenue-stats');
        if (statsRoot) {
            statsRoot.innerHTML =
                platformStatPill('Total', sym + ' ' + fmt(revenueStats.total), 'text-fin-lavender') +
                platformStatPill('Daily avg', sym + ' ' + fmt(revenueStats.avg), 'text-white') +
                platformStatPill('Peak day', revenueStats.peak ? fmtMoney(revenueStats.peak.revenue || 0) : '—', 'text-amber-400');
        }

        const kpiRoot = document.getElementById('snapshot-kpis');
        if (kpiRoot) {
            kpiRoot.innerHTML =
                platformKpiCard('📦', 'Orders today', fmt(orders.today || 0), 'Live platform activity', 'linear-gradient(90deg, #10b981, #34d399)', 'background:rgba(16,185,129,0.15);border-color:rgba(16,185,129,0.35)') +
                platformKpiCard('📅', 'This week', fmt(orders.week || 0), 'Week to date', 'linear-gradient(90deg, #8C71F6, #a78bfa)', 'background:rgba(140,113,246,0.15);border-color:rgba(140,113,246,0.35)') +
                platformKpiCard('🗓️', 'This month', fmt(orders.month || 0), 'Month to date', 'linear-gradient(90deg, #ec4899, #f472b6)', 'background:rgba(236,72,153,0.15);border-color:rgba(236,72,153,0.35)') +
                platformKpiCard('🏪', 'Venues', fmt(restaurants.total || 0), (restaurants.active || 0) + ' active · ' + (restaurants.inactive || 0) + ' inactive', 'linear-gradient(90deg, #5B3FD6, #8C71F6)', 'background:rgba(91,63,214,0.2);border-color:rgba(91,63,214,0.4)');
        }

        buildVenueHealthRing('chart-restaurant-split', restaurants);
        renderPlatformInsights(orders, restaurants, revenueStats);
    };

    const fmtCompact = (n) => {
        const v = Number(n) || 0;
        if (v >= 1000000) return (v / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
        if (v >= 1000) return (v / 1000).toFixed(1).replace(/\.0$/, '') + 'K';
        return fmt(v);
    };
    const fmtPct = (part, whole) => (whole > 0 ? (Math.round((part / whole) * 1000) / 10) + '%' : '0%');

    const fmtHourLabel = (h) => {
        if (h == null || h === undefined) return '—';
        const hour = parseInt(h, 10);
        if (hour === 0) return '12 AM';
        if (hour < 12) return hour + ' AM';
        if (hour === 12) return '12 PM';
        return (hour - 12) + ' PM';
    };

    const lgHourAxisTicks = () => [0, 6, 12, 18, 23];

    const buildHourlyBarChart = (containerId, hours, peakHour, config) => {
        const root = document.getElementById(containerId);
        const emptyResult = { total: 0, peak: null, max: 0 };
        if (!root) return emptyResult;

        const data = hours || [];
        if (data.length === 0) {
            root.innerHTML = config.emptyHtml || '<p class="text-sm text-white/40 text-center py-8">No hourly data yet</p>';
            return emptyResult;
        }

        const max = Math.max(...data.map(d => d.count || 0), 1);
        const total = data.reduce((s, d) => s + (d.count || 0), 0);
        const peakItem = data.find(d => d.hour === peakHour) ||
            data.reduce((best, d) => ((d.count || 0) > (best?.count || 0) ? d : best), data[0]);
        const fillClass = config.theme === 'emerald' ? 'lg-hour-bar__fill lg-hour-bar__fill--emerald' : 'lg-hour-bar__fill lg-hour-bar__fill--indigo';
        const yTicks = [1, 0.75, 0.5, 0.25, 0].map(pct => fmtCompact(Math.round(max * pct)));

        const barsHtml = data.map(d => {
            const h = pctHeight(d.count || 0, max, 10, 3);
            const isPeak = peakHour != null && d.hour === peakHour && (d.count || 0) > 0;
            const title = esc(d.label || fmtHourLabel(d.hour)) + ': ' + fmt(d.count || 0) + ' ' + (config.unit || '');
            return '<div class="lg-hour-bar" title="' + title + '">' +
                '<div class="' + fillClass + (isPeak ? ' is-peak' : '') + '" style="height:' + h + '%"></div></div>';
        }).join('');

        const xLabelsHtml = lgHourAxisTicks().map(hour => {
            const left = (hour / 23) * 100;
            return '<span class="lg-hour-xtick" style="left:' + left + '%">' + esc(fmtHourLabel(hour)) + '</span>';
        }).join('');

        root.innerHTML =
            '<div class="lg-hour-chart ' + (config.compact ? 'is-compact ' : '') + (config.animateClass || 'lg-animate-in') + '">' +
            '<div class="lg-hour-chart__yaxis" aria-hidden="true">' +
            yTicks.map(t => '<span class="lg-hour-ytick">' + t + '</span>').join('') +
            '</div>' +
            '<div class="lg-hour-chart__main">' +
            '<div class="lg-hour-chart__plot"><div class="lg-hour-chart__bars">' + barsHtml + '</div></div>' +
            '<div class="lg-hour-chart__xaxis" aria-hidden="true">' + xLabelsHtml + '</div>' +
            '</div></div>';

        return { total, peak: peakItem, max };
    };

    const waKpiCard = (icon, label, value, sub, accent, iconStyle) =>
        '<div class="wa-kpi-card wa-animate-in" style="--wa-kpi-accent:' + accent + '">' +
        '<div class="flex items-start justify-between gap-3">' +
        '<div class="wa-kpi-icon" style="' + (iconStyle || '') + '">' + icon + '</div>' +
        '<div class="text-right flex-1 min-w-0"><p class="text-[9px] font-black text-white/40 uppercase tracking-wider">' + esc(label) + '</p>' +
        '<p class="text-2xl font-black text-white mt-1 tabular-nums leading-none">' + value + '</p>' +
        (sub ? '<p class="text-[10px] text-white/45 mt-1.5">' + esc(sub) + '</p>' : '') +
        '</div></div></div>';

    const waStatPill = (label, value, colorClass) =>
        '<div class="wa-stat-pill wa-animate-in"><p class="label">' + esc(label) + '</p>' +
        '<p class="value tabular-nums ' + (colorClass || 'text-white') + '">' + value + '</p></div>';

    const waOptionIcon = (key) => ({
        view_menu: '📋', call_waiter: '🙋', pay_bill: '💳', rate_service: '⭐',
        give_tips: '💝', change_language: '🌐', exit_bot: '👋',
    }[key] || '💬');

    const waTrendShortDate = (d) => {
        const raw = String(d?.date || d?.label || '').trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw.slice(5);
        if (raw.length > 6) return raw.slice(-5);
        return raw || '—';
    };

    const waTrendLabelIndices = (len, maxLabels) => {
        if (len <= 1) return [0];
        const cap = Math.max(3, Math.min(maxLabels, len));
        if (len <= cap) return Array.from({ length: len }, (_, i) => i);
        const indices = [];
        for (let i = 0; i < cap; i++) {
            indices.push(Math.round((i / (cap - 1)) * (len - 1)));
        }
        return [...new Set(indices)].sort((a, b) => a - b);
    };

    const buildDailyTrendChart = (containerId, trend, config) => {
        const root = document.getElementById(containerId);
        const emptyResult = { total: 0, peak: null, avg: 0, activeDays: 0 };
        if (!root) return emptyResult;

        const data = trend || [];
        if (data.length === 0) {
            root.innerHTML = config.emptyHtml;
            return emptyResult;
        }

        const max = Math.max(...data.map(d => d.count || 0), 1);
        const total = data.reduce((s, d) => s + (d.count || 0), 0);
        const peak = data.reduce((best, d) => ((d.count || 0) > (best?.count || 0) ? d : best), data[0]);
        const avg = total / data.length;
        const activeDays = data.filter(d => (d.count || 0) > 0).length;

        const plotTop = 6;
        const plotBottom = 4;
        const plotH = 100;
        const innerH = plotH - plotTop - plotBottom;
        const xAt = (i) => (data.length > 1 ? (i / (data.length - 1)) * 100 : 50);
        const yAt = (count) => plotTop + innerH - ((count || 0) / max) * innerH;

        const points = data.map((d, i) => ({
            x: xAt(i),
            y: yAt(d.count || 0),
            bottomPct: ((plotH - yAt(d.count || 0)) / plotH) * 100,
            ...d,
        }));

        const linePath = points.map((p, i) => (i === 0 ? 'M' : 'L') + p.x.toFixed(2) + ' ' + p.y.toFixed(2)).join(' ');
        const areaPath = linePath +
            ' L100 ' + (plotH - plotBottom).toFixed(2) +
            ' L0 ' + (plotH - plotBottom).toFixed(2) + ' Z';

        const gridLines = [0, 0.25, 0.5, 0.75, 1].map(pct => {
            const y = plotTop + innerH * (1 - pct);
            return '<line x1="0" y1="' + y.toFixed(2) + '" x2="100" y2="' + y.toFixed(2) + '" class="wa-area-grid-line"/>';
        }).join('');

        const yTicks = [1, 0.75, 0.5, 0.25, 0].map(pct => fmtCompact(Math.round(max * pct)));
        const labelMax = typeof window !== 'undefined' && window.innerWidth < 640 ? 5 : 7;
        const labelIndices = waTrendLabelIndices(data.length, labelMax);
        const dotClass = 'wa-trend-dot' + (config.dotClass ? ' ' + config.dotClass : '');

        const dotsHtml = points.map(p => {
            const isPeak = peak && (p.count || 0) === (peak.count || 0) && (p.count || 0) > 0;
            const title = esc(p.date || p.label || '') + ': ' + fmt(p.count || 0) + ' ' + config.unit;
            return '<span class="' + dotClass + (isPeak ? ' is-peak' : '') + '" style="left:' + p.x + '%;bottom:' + p.bottomPct + '%" title="' + title + '"></span>';
        }).join('');

        const xLabelsHtml = labelIndices.map(i => {
            const left = data.length > 1 ? (i / (data.length - 1)) * 100 : 50;
            return '<span class="wa-trend-xtick" style="left:' + left + '%" title="' + esc(data[i].date || data[i].label || '') + '">' +
                esc(waTrendShortDate(data[i])) + '</span>';
        }).join('');

        root.innerHTML =
            '<div class="wa-trend-chart ' + (config.chartAnimateClass || 'wa-animate-in') + '">' +
            '<div class="wa-trend-chart__yaxis" aria-hidden="true">' +
            yTicks.map(t => '<span class="wa-trend-ytick">' + t + '</span>').join('') +
            '</div>' +
            '<div class="wa-trend-chart__main">' +
            '<div class="wa-trend-chart__plot">' +
            '<svg viewBox="0 0 100 ' + plotH + '" preserveAspectRatio="none" aria-hidden="true">' +
            '<defs><linearGradient id="' + config.gradId + '" x1="0" y1="0" x2="0" y2="1">' +
            '<stop offset="0%" stop-color="' + config.gradTop + '"/>' +
            '<stop offset="100%" stop-color="' + config.gradBottom + '"/></linearGradient></defs>' +
            gridLines +
            '<path d="' + areaPath + '" fill="url(#' + config.gradId + ')" stroke="none"/>' +
            '<path d="' + linePath + '" class="' + config.lineClass + '"/>' +
            '</svg>' +
            '<div class="wa-trend-chart__dots">' + dotsHtml + '</div>' +
            '</div>' +
            '<div class="wa-trend-chart__xaxis" aria-hidden="true">' + xLabelsHtml + '</div>' +
            '</div></div>';

        return { total, peak, avg, activeDays };
    };

    const buildWaAreaChart = (containerId, trend) => buildDailyTrendChart(containerId, trend, {
        emptyHtml: '<p class="text-sm text-white/40 text-center py-14">No bot activity yet — events appear as customers use WhatsApp</p>',
        unit: 'events',
        gradId: 'waAreaGrad',
        gradTop: 'rgba(16,185,129,0.42)',
        gradBottom: 'rgba(16,185,129,0.02)',
        lineClass: 'wa-area-line',
        chartAnimateClass: 'wa-animate-in',
    });

    const buildWaOptionBars = (containerId, options, total) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        const sorted = [...(options || [])].sort((a, b) => (b.value || 0) - (a.value || 0));
        if (sorted.length === 0 || total === 0) {
            root.innerHTML = '<p class="text-sm text-white/40">No menu option data yet</p>';
            return;
        }
        const max = Math.max(...sorted.map(o => o.value || 0), 1);
        root.innerHTML = sorted.map((o, i) => {
            const pct = total > 0 ? ((o.value || 0) / total) * 100 : 0;
            const barW = Math.max(((o.value || 0) / max) * 100, (o.value || 0) > 0 ? 4 : 0);
            return '<div class="wa-option-row wa-animate-in" style="animation-delay:' + (i * 0.04) + 's">' +
                '<span class="text-xs font-semibold text-white/80 truncate flex items-center gap-1.5">' +
                '<span>' + waOptionIcon(o.key) + '</span>' + esc(o.label) + '</span>' +
                '<div class="wa-option-bar-track"><div class="wa-option-bar-fill" style="width:' + barW + '%;background:' + (o.color || '#10b981') + ';color:' + (o.color || '#10b981') + '"></div></div>' +
                '<span class="text-xs font-bold text-emerald-400 tabular-nums text-right">' + fmtPct(o.value || 0, total) + '</span>' +
                '<span class="text-xs font-bold text-white tabular-nums text-right">' + fmt(o.value || 0) + '</span></div>';
        }).join('');
    };

    const buildWaOptionsTable = (containerId, options, total) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        const sorted = [...(options || [])].sort((a, b) => (b.value || 0) - (a.value || 0));
        if (sorted.length === 0) {
            root.innerHTML = '<p class="text-sm text-white/40">Waiting for bot interactions</p>';
            return;
        }
        root.innerHTML =
            '<table class="wa-data-table min-w-[480px]"><thead><tr>' +
            '<th>#</th><th>Action</th><th>Events</th><th>Share</th><th>Bar</th></tr></thead><tbody>' +
            sorted.map((o, i) => {
                const pct = total > 0 ? ((o.value || 0) / total) * 100 : 0;
                return '<tr class="wa-animate-in">' +
                    '<td><span class="wa-rank-badge' + (i === 0 ? ' is-top' : '') + '">' + (i + 1) + '</span></td>' +
                    '<td class="font-semibold text-white"><span class="mr-1.5">' + waOptionIcon(o.key) + '</span>' + esc(o.label) + '</td>' +
                    '<td class="tabular-nums font-bold text-emerald-300">' + fmt(o.value || 0) + '</td>' +
                    '<td class="tabular-nums text-white/60">' + fmtPct(o.value || 0, total) + '</td>' +
                    '<td style="min-width:120px"><div class="wa-option-bar-track"><div class="wa-option-bar-fill" style="width:' + pct + '%;background:' + (o.color || '#10b981') + '"></div></div></td></tr>';
            }).join('') +
            '</tbody></table>';
    };

    const renderWaInsights = (w, trendStats, topOption) => {
        const root = document.getElementById('wa-insights');
        if (!root) return;
        const cards = [];
        const total = w.total_events || 0;
        const options = w.option_usage || [];
        const withUsage = options.filter(o => (o.value || 0) > 0);

        if (topOption && topOption.value > 0) {
            cards.push({
                type: 'positive', icon: '🏆',
                title: 'Most popular',
                text: topOption.label + ' leads with ' + fmt(topOption.value) + ' taps (' + fmtPct(topOption.value, total) + ' of all actions).',
            });
        }

        if (trendStats.peak && trendStats.peak.count > 0) {
            cards.push({
                type: 'info', icon: '📈',
                title: 'Busiest day',
                text: (trendStats.peak.date || trendStats.peak.label || 'Peak') + ' had ' + fmt(trendStats.peak.count) + ' events — ' + fmtCompact(Math.round(trendStats.avg)) + ' avg/day.',
            });
        }

        const exitOpt = options.find(o => o.key === 'exit_bot');
        if (exitOpt && total > 0) {
            const exitPct = Math.round(((exitOpt.value || 0) / total) * 100);
            cards.push({
                type: exitPct > 25 ? 'warning' : 'positive',
                icon: '👋',
                title: 'Exit rate',
                text: exitPct + '% of actions were exits (' + fmt(exitOpt.value || 0) + ') — ' + (exitPct > 25 ? 'consider simplifying the menu flow.' : 'healthy engagement retention.'),
            });
        } else if (withUsage.length > 0) {
            cards.push({
                type: 'info', icon: '💬',
                title: 'Active options',
                text: withUsage.length + ' of ' + options.length + ' menu options used in this period.',
            });
        } else {
            cards.push({ type: 'info', icon: '📊', title: 'Getting started', text: 'Bot usage data will appear as customers interact via WhatsApp.' });
        }

        root.innerHTML = cards.slice(0, 3).map(c =>
            '<div class="wa-insight-card wa-animate-in is-' + c.type + '">' +
            '<p class="text-lg mb-2">' + c.icon + '</p>' +
            '<p class="text-[10px] font-black text-white/45 uppercase tracking-wider">' + esc(c.title) + '</p>' +
            '<p class="text-sm font-semibold text-white/90 mt-1 leading-relaxed">' + esc(c.text) + '</p></div>'
        ).join('');
    };

    const renderWhatsapp = (payload) => {
        const w = payload.whatsapp_engagement || {};
        const options = w.option_usage || [];
        const total = w.total_events || 0;
        const days = parseInt(daysEl?.value || '30', 10);
        const sorted = [...options].sort((a, b) => (b.value || 0) - (a.value || 0));
        const topOption = sorted.find(o => (o.value || 0) > 0) || sorted[0] || null;
        const withUsage = options.filter(o => (o.value || 0) > 0).length;

        const periodLabel = document.getElementById('wa-period-label');
        if (periodLabel) periodLabel.textContent = 'last ' + days + ' days';

        const heroEl = document.getElementById('wa-hero-events');
        if (heroEl) heroEl.textContent = fmt(total);

        const chipsEl = document.getElementById('wa-hero-chips');
        if (chipsEl) {
            chipsEl.innerHTML =
                '<span class="wa-chip wa-animate-in">📋 <strong>' + withUsage + '</strong> options used</span>' +
                '<span class="wa-chip wa-animate-in">🏆 <strong>' + esc(topOption?.label || '—') + '</strong></span>' +
                '<span class="wa-chip wa-animate-in">📅 <strong>' + days + 'd</strong> period</span>';
        }

        const trendStats = buildWaAreaChart('chart-wa-trend', w.daily_trend || []);

        const statsRoot = document.getElementById('wa-activity-stats');
        if (statsRoot) {
            statsRoot.innerHTML =
                waStatPill('Daily avg', fmtCompact(Math.round(trendStats.avg)), 'text-emerald-400') +
                waStatPill('Peak day', trendStats.peak ? fmt(trendStats.peak.count || 0) : '—', 'text-amber-400') +
                waStatPill('Active days', fmt(trendStats.activeDays || 0), 'text-white');
        }

        const legendEl = document.getElementById('wa-trend-legend');
        if (legendEl) {
            legendEl.innerHTML =
                '<span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Daily events</span>' +
                '<span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Peak day</span>' +
                '<span>Total: <strong class="text-white">' + fmt(trendStats.total) + '</strong> in period</span>';
        }

        const kpiRoot = document.getElementById('wa-kpis');
        if (kpiRoot) {
            kpiRoot.innerHTML =
                waKpiCard('💬', 'Total events', fmt(total), 'All bot interactions', 'linear-gradient(90deg, #10b981, #34d399)', 'background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35)') +
                waKpiCard('📈', 'Daily average', fmtCompact(Math.round(trendStats.avg)), 'Events per day', 'linear-gradient(90deg, #06b6d4, #22d3ee)', 'background:rgba(6,182,212,0.15);border:1px solid rgba(6,182,212,0.35)') +
                waKpiCard('🏆', 'Top action', topOption ? fmt(topOption.value || 0) : '0', topOption ? topOption.label : 'No data yet', 'linear-gradient(90deg, #8C71F6, #a78bfa)', 'background:rgba(140,113,246,0.15);border:1px solid rgba(140,113,246,0.35)') +
                waKpiCard('📅', 'Active days', fmt(trendStats.activeDays || 0), 'Days with bot usage', 'linear-gradient(90deg, #f59e0b, #fbbf24)', 'background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.35)');
        }

        const topActionEl = document.getElementById('wa-top-action');
        if (topActionEl) {
            if (!topOption || !(topOption.value > 0)) {
                topActionEl.innerHTML = '<p class="text-sm text-white/40 text-center py-6">No top action yet</p>';
            } else {
                topActionEl.innerHTML =
                    '<div class="flex items-center gap-3 mb-3">' +
                    '<span class="text-3xl">' + waOptionIcon(topOption.key) + '</span>' +
                    '<div><p class="text-lg font-black text-white">' + esc(topOption.label) + '</p>' +
                    '<p class="text-xs text-emerald-400/80 font-bold">' + fmtPct(topOption.value, total) + ' of all actions</p></div></div>' +
                    '<p class="text-3xl font-black text-emerald-400 tabular-nums">' + fmt(topOption.value) + '</p>' +
                    '<p class="text-[10px] text-white/40 mt-1 uppercase tracking-wider">total taps</p>';
            }
        }

        buildDonut('chart-wa-options', options.filter(o => (o.value || 0) > 0), total || '0', 'events');

        const totalLabel = document.getElementById('wa-options-total-label');
        if (totalLabel) totalLabel.textContent = fmt(total) + ' total events';

        buildWaOptionBars('wa-options-bars', options, total);
        buildWaOptionsTable('wa-options-table', options, total);
        renderWaInsights(w, trendStats, topOption);
    };

    const renderQr = (payload) => {
        const q = payload.qr_entry_points || {};
        const split = q.split || [];
        const total = q.total_scans || 0;
        const days = parseInt(daysEl?.value || '30', 10);
        const sorted = [...split].sort((a, b) => (b.value || 0) - (a.value || 0));
        const topEntry = sorted.find(s => (s.value || 0) > 0) || sorted[0] || null;
        const typesUsed = split.filter(s => (s.value || 0) > 0).length;

        const qrKpiCard = (icon, label, value, sub, accent, iconStyle) =>
            '<div class="qr-kpi-card qr-animate-in" style="--qr-kpi-accent:' + accent + '">' +
            '<div class="flex items-start justify-between gap-3">' +
            '<div class="qr-kpi-icon" style="' + (iconStyle || '') + '">' + icon + '</div>' +
            '<div class="text-right flex-1 min-w-0"><p class="text-[9px] font-black text-white/40 uppercase tracking-wider">' + esc(label) + '</p>' +
            '<p class="text-2xl font-black text-white mt-1 tabular-nums leading-none">' + value + '</p>' +
            (sub ? '<p class="text-[10px] text-white/45 mt-1.5">' + esc(sub) + '</p>' : '') +
            '</div></div></div>';

        const qrStatPill = (label, value, colorClass) =>
            '<div class="qr-stat-pill qr-animate-in"><p class="label">' + esc(label) + '</p>' +
            '<p class="value tabular-nums ' + (colorClass || 'text-white') + '">' + value + '</p></div>';

        const qrEntryIcon = (key) => ({
            qr_waiter: '🙋', qr_table: '🪑', qr_restaurant: '🏷️',
        }[key] || '📱');

        const qrEntryDesc = (key) => ({
            qr_waiter: 'Staff personal QR codes',
            qr_table: 'Table-specific entry codes',
            qr_restaurant: 'Venue-wide restaurant tag',
        }[key] || 'QR entry point');

        const buildQrAreaChart = (containerId, trend) => buildDailyTrendChart(containerId, trend, {
            emptyHtml: '<p class="text-sm text-white/40 text-center py-14">No QR scans yet — activity appears when customers scan codes</p>',
            unit: 'scans',
            gradId: 'qrAreaGrad',
            gradTop: 'rgba(6,182,212,0.42)',
            gradBottom: 'rgba(6,182,212,0.02)',
            lineClass: 'qr-area-line',
            dotClass: 'qr-trend-dot',
            chartAnimateClass: 'qr-animate-in',
        });

        const periodLabel = document.getElementById('qr-period-label');
        if (periodLabel) periodLabel.textContent = 'last ' + days + ' days';

        const heroEl = document.getElementById('qr-hero-scans');
        if (heroEl) heroEl.textContent = fmt(total);

        const chipsEl = document.getElementById('qr-hero-chips');
        if (chipsEl) {
            chipsEl.innerHTML =
                '<span class="qr-chip qr-animate-in">📱 <strong>' + fmt(total) + '</strong> scans</span>' +
                '<span class="qr-chip qr-animate-in">🏆 <strong>' + esc(topEntry?.label || '—') + '</strong></span>' +
                '<span class="qr-chip qr-animate-in">🔀 <strong>' + typesUsed + '/3</strong> types</span>';
        }

        const trendStats = buildQrAreaChart('chart-qr-trend', q.daily_trend || []);

        const statsRoot = document.getElementById('qr-activity-stats');
        if (statsRoot) {
            statsRoot.innerHTML =
                qrStatPill('Daily avg', fmtCompact(Math.round(trendStats.avg)), 'text-cyan-400') +
                qrStatPill('Peak day', trendStats.peak ? fmt(trendStats.peak.count || 0) : '—', 'text-amber-400') +
                qrStatPill('Active days', fmt(trendStats.activeDays || 0), 'text-white');
        }

        const legendEl = document.getElementById('qr-trend-legend');
        if (legendEl) {
            legendEl.innerHTML =
                '<span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-cyan-400"></span> Daily scans</span>' +
                '<span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Peak day</span>' +
                '<span>Period total: <strong class="text-white">' + fmt(trendStats.total) + '</strong></span>';
        }

        const kpiRoot = document.getElementById('qr-kpis');
        if (kpiRoot) {
            kpiRoot.innerHTML =
                qrKpiCard('📱', 'Total scans', fmt(total), 'All QR entry types', 'linear-gradient(90deg, #06b6d4, #22d3ee)', 'background:rgba(6,182,212,0.15);border:1px solid rgba(6,182,212,0.35)') +
                qrKpiCard('🏆', 'Top entry', topEntry ? fmt(topEntry.value || 0) : '0', topEntry ? topEntry.label : 'No data', 'linear-gradient(90deg, #8C71F6, #a78bfa)', 'background:rgba(140,113,246,0.15);border:1px solid rgba(140,113,246,0.35)') +
                qrKpiCard('🔀', 'Types active', typesUsed + ' / 3', 'Entry channels used', 'linear-gradient(90deg, #10b981, #34d399)', 'background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35)') +
                qrKpiCard('📅', 'Scan days', fmt(trendStats.activeDays || 0), 'Days with QR activity', 'linear-gradient(90deg, #f59e0b, #fbbf24)', 'background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.35)');
        }

        const topEl = document.getElementById('qr-top-entry');
        if (topEl) {
            if (!topEntry || !(topEntry.value > 0)) {
                topEl.innerHTML = '<p class="text-sm text-white/40 text-center py-6">No scans recorded yet</p>';
            } else {
                topEl.innerHTML =
                    '<div class="flex items-center gap-3 mb-3">' +
                    '<span class="text-3xl">' + qrEntryIcon(topEntry.key) + '</span>' +
                    '<div><p class="text-lg font-black text-white">' + esc(topEntry.label) + '</p>' +
                    '<p class="text-xs text-cyan-400/80 font-bold">' + fmtPct(topEntry.value, total) + ' of scans</p></div></div>' +
                    '<p class="text-3xl font-black text-cyan-400 tabular-nums">' + fmt(topEntry.value) + '</p>' +
                    '<p class="text-[10px] text-white/40 mt-1">' + esc(qrEntryDesc(topEntry.key)) + '</p>';
            }
        }

        buildDonut('chart-qr-split', split.filter(s => (s.value || 0) > 0), total || '0', 'scans');

        const typeCardsEl = document.getElementById('qr-type-cards');
        if (typeCardsEl) {
            typeCardsEl.innerHTML = split.map((s, i) => {
                const isLeader = topEntry && s.key === topEntry.key && (s.value || 0) > 0;
                return '<div class="qr-type-card qr-animate-in' + (isLeader ? ' is-leader' : '') + '" style="animation-delay:' + (i * 0.05) + 's;border-top:3px solid ' + (s.color || '#06b6d4') + '">' +
                    '<div class="flex items-center justify-between mb-3">' +
                    '<span class="text-2xl">' + qrEntryIcon(s.key) + '</span>' +
                    (isLeader ? '<span class="text-[9px] font-black text-cyan-400 uppercase tracking-wider">Leader</span>' : '') +
                    '</div>' +
                    '<p class="text-sm font-bold text-white">' + esc(s.label) + '</p>' +
                    '<p class="text-2xl font-black text-white tabular-nums mt-2">' + fmt(s.value || 0) + '</p>' +
                    '<p class="text-xs text-cyan-400/70 font-bold mt-1">' + fmtPct(s.value || 0, total) + '</p>' +
                    '<p class="text-[10px] text-white/35 mt-2">' + esc(qrEntryDesc(s.key)) + '</p></div>';
            }).join('');
        }

        const totalLabel = document.getElementById('qr-split-total-label');
        if (totalLabel) totalLabel.textContent = fmt(total) + ' total scans';

        const barsEl = document.getElementById('qr-entry-bars');
        if (barsEl) {
            const max = Math.max(...sorted.map(s => s.value || 0), 1);
            barsEl.innerHTML = sorted.length === 0 || total === 0
                ? '<p class="text-sm text-white/40">No entry point data yet</p>'
                : sorted.map((s, i) => {
                    const barW = Math.max(((s.value || 0) / max) * 100, (s.value || 0) > 0 ? 6 : 0);
                    return '<div class="qr-entry-row qr-animate-in" style="animation-delay:' + (i * 0.05) + 's">' +
                        '<span class="text-sm font-semibold text-white/85 flex items-center gap-2">' +
                        '<span>' + qrEntryIcon(s.key) + '</span>' + esc(s.label) + '</span>' +
                        '<div class="qr-entry-bar-track"><div class="qr-entry-bar-fill" style="width:' + barW + '%;background:' + (s.color || '#06b6d4') + ';color:' + (s.color || '#06b6d4') + '"></div></div>' +
                        '<span class="text-sm font-bold text-cyan-400 tabular-nums text-right">' + fmtPct(s.value || 0, total) + '</span>' +
                        '<span class="text-sm font-bold text-white tabular-nums text-right">' + fmt(s.value || 0) + '</span></div>';
                }).join('');
        }

        const tableEl = document.getElementById('qr-entry-table');
        if (tableEl) {
            tableEl.innerHTML = sorted.length === 0
                ? '<p class="text-sm text-white/40">Waiting for QR scans</p>'
                : '<table class="qr-data-table"><thead><tr><th>Type</th><th>Scans</th><th>Share</th></tr></thead><tbody>' +
                sorted.map(s =>
                    '<tr><td class="font-semibold"><span class="mr-1">' + qrEntryIcon(s.key) + '</span>' + esc(s.label) + '</td>' +
                    '<td class="tabular-nums font-bold text-cyan-300">' + fmt(s.value || 0) + '</td>' +
                    '<td class="tabular-nums text-white/60">' + fmtPct(s.value || 0, total) + '</td></tr>'
                ).join('') + '</tbody></table>';
        }

        const insightsEl = document.getElementById('qr-insights');
        if (insightsEl) {
            const cards = [];
            if (topEntry && topEntry.value > 0) {
                cards.push({
                    type: 'positive', icon: '🏆',
                    title: 'Dominant entry',
                    text: topEntry.label + ' accounts for ' + fmtPct(topEntry.value, total) + ' (' + fmt(topEntry.value) + ' scans).',
                });
            }
            const waiter = split.find(s => s.key === 'qr_waiter');
            const table = split.find(s => s.key === 'qr_table');
            if (waiter && table && total > 0) {
                const waiterPct = Math.round(((waiter.value || 0) / total) * 100);
                const tablePct = Math.round(((table.value || 0) / total) * 100);
                cards.push({
                    type: waiterPct > tablePct ? 'info' : 'warning',
                    icon: '⚖️',
                    title: 'Waiter vs table',
                    text: 'Waiter QR ' + waiterPct + '% · Table QR ' + tablePct + '% — ' +
                        (waiterPct > tablePct ? 'staff codes drive most entries.' : 'table codes are the main channel.'),
                });
            }
            if (trendStats.peak && trendStats.peak.count > 0) {
                cards.push({
                    type: 'info', icon: '📈',
                    title: 'Busiest scan day',
                    text: (trendStats.peak.date || trendStats.peak.label) + ' peaked at ' + fmt(trendStats.peak.count) + ' scans.',
                });
            } else if (cards.length === 0) {
                cards.push({ type: 'info', icon: '📱', title: 'Getting started', text: 'QR scan data appears when customers scan waiter, table or restaurant codes.' });
            }
            insightsEl.innerHTML = cards.slice(0, 3).map(c =>
                '<div class="qr-insight-card qr-animate-in is-' + c.type + '">' +
                '<p class="text-lg mb-2">' + c.icon + '</p>' +
                '<p class="text-[10px] font-black text-white/45 uppercase tracking-wider">' + esc(c.title) + '</p>' +
                '<p class="text-sm font-semibold text-white/90 mt-1 leading-relaxed">' + esc(c.text) + '</p></div>'
            ).join('');
        }
    };

    const funnelStepIcon = (key) => ({
        qr_scan: '📱', bot_home: '🏠', view_menu: '📋', add_to_cart: '🛒',
        confirm_order: '✅', pay_bill: '💳', payment_success: '🎉',
    }[key] || '•');

    const funnelStepColor = (key) => ({
        qr_scan: '#8C71F6', bot_home: '#7c6ce0', view_menu: '#6d52e8',
        add_to_cart: '#5b8dee', confirm_order: '#14b8a6', pay_bill: '#10b981', payment_success: '#34d399',
    }[key] || '#8C71F6');

    const buildJnConversionRing = (containerId, pct) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        const circumference = 2 * Math.PI * 36;
        const dash = (Math.min(pct, 100) / 100) * circumference;
        const color = pct >= 50 ? '#34d399' : (pct >= 25 ? '#fbbf24' : '#f87171');
        root.innerHTML =
            '<div class="jn-conversion-ring jn-animate-in">' +
            '<svg width="96" height="96" viewBox="0 0 80 80">' +
            '<circle cx="40" cy="40" r="36" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="8"/>' +
            '<circle cx="40" cy="40" r="36" fill="none" stroke="' + color + '" stroke-width="8" ' +
            'stroke-linecap="round" stroke-dasharray="' + dash + ' ' + circumference + '"/></svg>' +
            '<div class="jn-conversion-ring-center">' +
            '<span class="text-xl font-black text-white tabular-nums">' + pct + '%</span>' +
            '<span class="text-[8px] font-bold text-white/40 uppercase tracking-widest">convert</span></div></div>';
    };

    const buildJnFunnelVisual = (containerId, steps, startCount) => {
        const root = document.getElementById(containerId);
        if (!root || !steps.length) {
            if (root) root.innerHTML = '<p class="text-sm text-white/40 text-center py-8">No funnel data</p>';
            return;
        }
        const maxW = 160;
        const cx = 200;
        const layerH = 44;
        const gap = 3;
        let y = 10;
        let shapes = '';
        let labels = '';

        steps.forEach((step, i) => {
            const topRatio = startCount > 0 ? (step.count || 0) / startCount : 0;
            const next = steps[i + 1];
            const bottomRatio = next && startCount > 0 ? (next.count || 0) / startCount : topRatio * 0.88;
            const topW = Math.max(24, maxW * topRatio);
            const bottomW = Math.max(20, maxW * bottomRatio);
            const color = funnelStepColor(step.key);
            const x1 = cx - topW / 2, x2 = cx + topW / 2;
            const x3 = cx + bottomW / 2, x4 = cx - bottomW / 2;
            const y1 = y, y2 = y + layerH;
            const points = x1 + ',' + y1 + ' ' + x2 + ',' + y1 + ' ' + x3 + ',' + y2 + ' ' + x4 + ',' + y2;
            const pct = startCount > 0 ? Math.round((step.count / startCount) * 1000) / 10 : 0;

            shapes += '<polygon class="jn-funnel-layer" points="' + points + '" fill="' + color + '" opacity="0.88" stroke="rgba(255,255,255,0.15)" stroke-width="1">' +
                '<title>' + esc(step.label) + ': ' + fmt(step.count || 0) + ' (' + pct + '%)</title></polygon>';

            if (topW > 50) {
                labels += '<text x="' + cx + '" y="' + (y + layerH / 2 + 4) + '" text-anchor="middle" fill="#fff" font-size="11" font-weight="700">' + esc(step.label) + '</text>' +
                    '<text x="' + cx + '" y="' + (y + layerH / 2 + 16) + '" text-anchor="middle" fill="rgba(255,255,255,0.65)" font-size="9" font-weight="600">' + fmt(step.count || 0) + ' · ' + pct + '%</text>';
            }
            y += layerH + gap;
        });

        root.innerHTML = '<svg viewBox="0 0 400 ' + (y + 10) + '" class="jn-animate-in">' + shapes + labels + '</svg>';
    };

    const buildJnFlowTrack = (containerId, steps, startCount, weakestKey, biggestDropOff) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        if (!steps.length) {
            root.innerHTML = '<p class="text-sm text-white/40 py-6">No funnel data yet</p>';
            return;
        }

        let html = '';
        steps.forEach((step, i) => {
            const retention = startCount > 0 ? Math.round(((step.count || 0) / startCount) * 1000) / 10 : 0;
            const isLast = i === steps.length - 1;
            const isStart = i === 0;
            const isSuccess = step.key === 'payment_success' && (step.count || 0) > 0;
            const isWeakest = step.key === weakestKey || step.label === biggestDropOff?.step;
            const stepClass = 'jn-flow-step jn-animate-in' +
                (isStart ? ' is-start' : '') + (isSuccess ? ' is-success' : '') +
                (isWeakest && (step.drop_off || 0) > 0 ? ' is-weakest' : '');

            const prevBadge = i === 0
                ? '<span class="text-[9px] font-bold text-violet-300 bg-violet-500/20 px-2 py-0.5 rounded-full">Entry</span>'
                : '<span class="text-[9px] font-bold text-white/50 bg-white/5 px-2 py-0.5 rounded-full">' + (step.conversion_pct ?? 0) + '% from prev</span>';

            html += '<div class="' + stepClass + '" style="animation-delay:' + (i * 0.04) + 's">' +
                '<div class="jn-flow-icon">' + funnelStepIcon(step.key) + '</div>' +
                '<div class="min-w-0">' +
                '<div class="flex items-center justify-between gap-2 flex-wrap">' +
                '<p class="text-sm font-bold text-white">' + esc(step.label) + '</p>' + prevBadge + '</div>' +
                '<div class="jn-retention-track"><div class="jn-retention-fill" style="width:' + Math.max(retention, step.count > 0 ? 3 : 0) + '%"></div></div>' +
                '</div>' +
                '<div class="text-right shrink-0">' +
                '<p class="text-xl font-black text-white tabular-nums">' + fmt(step.count || 0) + '</p>' +
                '<p class="text-[9px] text-white/40 font-bold">' + retention + '% total</p></div></div>';

            if (!isLast) {
                const nextStep = steps[i + 1];
                const drop = nextStep?.drop_off ?? 0;
                html += '<div class="jn-flow-connector jn-animate-in">' +
                    '<div class="jn-flow-connector-line"></div>' +
                    '<span class="jn-flow-drop' + (drop > 0 ? '' : ' is-none') + '">' +
                    (drop > 0 ? '−' + fmt(drop) + ' dropped' : '✓ retained') + '</span></div>';
            }
        });
        root.innerHTML = html;
    };

    const buildJnStepBars = (containerId, steps, startCount) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        root.innerHTML = steps.map((step, i) => {
            const retention = startCount > 0 ? ((step.count || 0) / startCount) * 100 : 0;
            const color = funnelStepColor(step.key);
            return '<div class="jn-step-bar-row jn-animate-in" style="animation-delay:' + (i * 0.03) + 's">' +
                '<span class="text-xs font-semibold text-white/80 flex items-center gap-1.5">' +
                '<span>' + funnelStepIcon(step.key) + '</span>' + esc(step.label) + '</span>' +
                '<div class="jn-step-bar-track"><div class="jn-step-bar-fill" style="width:' + Math.max(retention, step.count > 0 ? 2 : 0) + '%;background:' + color + '"></div></div>' +
                '<span class="text-xs font-bold text-violet-300 tabular-nums text-right">' + (Math.round(retention * 10) / 10) + '%</span>' +
                '<span class="text-xs font-bold text-white tabular-nums text-right">' + fmt(step.count || 0) + '</span></div>';
        }).join('');
    };

    const buildJnJourneyTable = (containerId, steps, startCount) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        root.innerHTML =
            '<table class="jn-data-table min-w-[520px]"><thead><tr>' +
            '<th>Step</th><th>Customers</th><th>Of start</th><th>Step conv.</th><th>Drop-off</th></tr></thead><tbody>' +
            steps.map((step, i) => {
                const retention = startCount > 0 ? fmtPct(step.count || 0, startCount) : '0%';
                const drop = i === 0 ? '—' : fmt(step.drop_off || 0);
                return '<tr class="jn-animate-in">' +
                    '<td class="font-semibold text-white"><span class="mr-1.5">' + funnelStepIcon(step.key) + '</span>' + esc(step.label) + '</td>' +
                    '<td class="tabular-nums font-bold text-violet-300">' + fmt(step.count || 0) + '</td>' +
                    '<td class="tabular-nums text-white/70">' + retention + '</td>' +
                    '<td class="tabular-nums">' + (i === 0 ? '100%' : (step.conversion_pct ?? 0) + '%') + '</td>' +
                    '<td class="tabular-nums ' + ((step.drop_off || 0) > 0 ? 'text-rose-400' : 'text-emerald-400/80') + '">' + drop + '</td></tr>';
            }).join('') + '</tbody></table>';
    };

    const renderJnInsights = (f, steps, startCount, endCount, overallPct) => {
        const root = document.getElementById('jn-insights');
        if (!root) return;
        const cards = [];

        if (f.biggest_drop_off && f.biggest_drop_off.drop_off > 0) {
            cards.push({
                type: 'warning', icon: '⚡',
                title: 'Fix this step',
                text: f.biggest_drop_off.step + ' loses ' + fmt(f.biggest_drop_off.drop_off) + ' customers — biggest leak in the pipeline.',
            });
        } else if (overallPct >= 40) {
            cards.push({ type: 'positive', icon: '✓', title: 'Healthy funnel', text: 'No major drop-offs between steps in this period.' });
        }

        const menuStep = steps.find(s => s.key === 'view_menu');
        const payStep = steps.find(s => s.key === 'pay_bill');
        if (menuStep && payStep && (menuStep.count || 0) > 0) {
            const menuToPay = Math.round(((payStep.count || 0) / menuStep.count) * 1000) / 10;
            cards.push({
                type: menuToPay >= 30 ? 'positive' : 'info',
                icon: '📋',
                title: 'Menu → payment',
                text: menuToPay + '% who viewed menu reached payment step.',
            });
        }

        cards.push({
            type: overallPct >= 30 ? 'positive' : 'info',
            icon: '🎯',
            title: 'End-to-end',
            text: fmt(startCount) + ' started → ' + fmt(endCount) + ' paid (' + overallPct + '% conversion).',
        });

        root.innerHTML = cards.slice(0, 2).map(c =>
            '<div class="jn-insight-card jn-animate-in is-' + c.type + '">' +
            '<p class="text-lg mb-2">' + c.icon + '</p>' +
            '<p class="text-[10px] font-black text-white/45 uppercase tracking-wider">' + esc(c.title) + '</p>' +
            '<p class="text-sm font-semibold text-white/90 mt-1 leading-relaxed">' + esc(c.text) + '</p></div>'
        ).join('');
    };

    const renderFunnel = (payload) => {
        const f = payload.customer_journey || {};
        const steps = f.steps || [];
        const days = parseInt(daysEl?.value || '30', 10);

        const periodLabel = document.getElementById('jn-period-label');
        if (periodLabel) periodLabel.textContent = 'last ' + days + ' days';

        const inner = document.getElementById('chart-funnel-inner');
        const kpiRoot = document.getElementById('funnel-kpis');
        const banner = document.getElementById('funnel-dropoff-banner');
        const overallBadge = document.getElementById('funnel-overall-badge');
        const summaryRoot = document.getElementById('funnel-summary-stats');

        if (!inner) return;

        if (steps.length === 0) {
            inner.innerHTML = '<p class="text-sm text-white/40 py-8 text-center w-full">No funnel data yet — scans and orders will appear here</p>';
            document.getElementById('jn-funnel-visual')?.replaceChildren();
            if (kpiRoot) kpiRoot.innerHTML = '';
            if (banner) banner.classList.add('hidden');
            if (overallBadge) overallBadge.textContent = 'No data';
            if (summaryRoot) summaryRoot.innerHTML = '<p class="text-xs text-white/40">Waiting for customer activity</p>';
            document.getElementById('jn-hero-conversion').textContent = '—';
            buildDonut('chart-funnel-payments', f.payment_methods || [], '—', 'paid');
            return;
        }

        const startCount = Math.max(steps[0]?.count || 0, 1);
        const endCount = steps[steps.length - 1]?.count || 0;
        const overallPct = startCount > 0 ? Math.round((endCount / startCount) * 1000) / 10 : 0;
        const totalDrop = Math.max(0, startCount - endCount);
        const weakestKey = f.biggest_drop_off
            ? steps.find(s => s.label === f.biggest_drop_off.step)?.key
            : null;

        const heroEl = document.getElementById('jn-hero-conversion');
        if (heroEl) heroEl.textContent = overallPct + '%';

        const chipsEl = document.getElementById('jn-hero-chips');
        if (chipsEl) {
            chipsEl.innerHTML =
                '<span class="jn-chip jn-animate-in">📱 <strong>' + fmt(startCount) + '</strong> started</span>' +
                '<span class="jn-chip jn-animate-in">🎉 <strong>' + fmt(endCount) + '</strong> paid</span>' +
                '<span class="jn-chip jn-animate-in">📉 <strong>' + fmt(totalDrop) + '</strong> dropped</span>';
        }

        const jnKpiCard = (icon, label, value, sub, accent, iconStyle) =>
            '<div class="jn-kpi-card jn-animate-in" style="--jn-kpi-accent:' + accent + '">' +
            '<div class="flex items-start justify-between gap-3">' +
            '<div class="jn-kpi-icon" style="' + iconStyle + '">' + icon + '</div>' +
            '<div class="text-right flex-1"><p class="text-[9px] font-black text-white/40 uppercase tracking-wider">' + esc(label) + '</p>' +
            '<p class="text-2xl font-black text-white mt-1 tabular-nums leading-none">' + value + '</p>' +
            (sub ? '<p class="text-[10px] text-white/45 mt-1.5">' + esc(sub) + '</p>' : '') + '</div></div></div>';

        if (kpiRoot) {
            kpiRoot.innerHTML =
                jnKpiCard('📱', 'Started', fmt(startCount), 'QR scans · entry point', 'linear-gradient(90deg, #8C71F6, #a78bfa)', 'background:rgba(140,113,246,0.18);border:1px solid rgba(140,113,246,0.35)') +
                jnKpiCard('🎉', 'Completed', fmt(endCount), 'Reached payment success', 'linear-gradient(90deg, #10b981, #34d399)', 'background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35)') +
                jnKpiCard('🎯', 'Conversion', overallPct + '%', 'Scan → paid', 'linear-gradient(90deg, #d946ef, #e879f9)', 'background:rgba(217,70,239,0.15);border:1px solid rgba(217,70,239,0.35)') +
                jnKpiCard('📉', 'Dropped', fmt(totalDrop), 'Did not complete journey', 'linear-gradient(90deg, #f59e0b, #fbbf24)', 'background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.35)');
        }

        buildJnConversionRing('jn-conversion-ring', overallPct);

        const pipelineStats = document.getElementById('jn-pipeline-stats');
        if (pipelineStats) {
            pipelineStats.innerHTML =
                '<div class="jn-stat-pill jn-animate-in"><p class="label">Steps</p><p class="value">' + steps.length + '</p></div>' +
                '<div class="jn-stat-pill jn-animate-in"><p class="label">Paid</p><p class="value text-emerald-400">' + fmt(endCount) + '</p></div>';
        }

        buildJnFunnelVisual('jn-funnel-visual', steps, startCount);
        buildJnFlowTrack('chart-funnel-inner', steps, startCount, weakestKey, f.biggest_drop_off);
        buildJnStepBars('jn-step-bars', steps, startCount);
        buildJnJourneyTable('jn-journey-table', steps, startCount);
        renderJnInsights(f, steps, startCount, endCount, overallPct);

        if (overallBadge) {
            overallBadge.textContent = overallPct + '% end-to-end';
            overallBadge.className = 'text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full border ' +
                (overallPct >= 40 ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-amber-500/10 border-amber-500/30 text-amber-300');
        }

        if (banner) {
            if (f.biggest_drop_off && f.biggest_drop_off.drop_off > 0) {
                banner.className = 'mt-8 rounded-2xl border px-5 py-4 text-sm insight-card-warning border jn-animate-in';
                banner.innerHTML = '<p class="font-bold text-amber-200 text-base">⚡ Biggest drop-off: <span class="text-white">' + esc(f.biggest_drop_off.step) + '</span></p>' +
                    '<p class="text-sm text-white/60 mt-2">' + fmt(f.biggest_drop_off.drop_off) + ' customers lost before the next step. Review menu flow, ordering UX, or payment options at this stage.</p>';
                banner.classList.remove('hidden');
            } else {
                banner.className = 'mt-8 rounded-2xl border px-5 py-4 text-sm insight-card-positive border jn-animate-in';
                banner.innerHTML = '<p class="font-bold text-emerald-200">✓ Strong conversion pipeline</p>' +
                    '<p class="text-sm text-white/60 mt-2">No major leaks between steps — customers are moving smoothly through the journey.</p>';
                banner.classList.remove('hidden');
            }
        }

        const payMethods = f.payment_methods || [];
        const payTotal = payMethods.reduce((s, m) => s + (m.value || 0), 0);

        if (summaryRoot) {
            summaryRoot.innerHTML =
                '<div class="flex justify-between text-sm py-2 border-b border-white/5"><span class="text-white/50">Funnel steps</span><span class="font-bold text-white tabular-nums">' + steps.length + '</span></div>' +
                '<div class="flex justify-between text-sm py-2 border-b border-white/5"><span class="text-white/50">Started → Paid</span><span class="font-bold text-white tabular-nums">' + fmt(startCount) + ' → ' + fmt(endCount) + '</span></div>' +
                '<div class="flex justify-between text-sm py-2 border-b border-white/5"><span class="text-white/50">Conversion rate</span><span class="font-bold text-emerald-400 tabular-nums">' + overallPct + '%</span></div>' +
                '<div class="flex justify-between text-sm py-2"><span class="text-white/50">Payments logged</span><span class="font-bold text-white tabular-nums">' + fmt(payTotal) + '</span></div>';
        }

        buildDonut('chart-funnel-payments', payMethods, payTotal || '0', 'paid');
    };

    const fbStarColor = (stars) => ({
        5: '#fbbf24', 4: '#f59e0b', 3: '#eab308', 2: '#f97316', 1: '#ef4444',
    }[stars] || '#f59e0b');

    const fbTypeIcon = (key) => ({
        waiter: '🙋', food: '🍽️', restaurant: '🏪',
    }[key] || '💬');

    const fbTypeColor = (key) => ({
        waiter: '#ec4899', food: '#f59e0b', restaurant: '#8C71F6',
    }[key] || '#f59e0b');

    const fbTypeLabel = (key) => ({
        waiter: 'Waiter service', food: 'Food quality', restaurant: 'Restaurant',
    }[key] || key);

    const renderStarDisplay = (rating, size) => {
        const full = Math.floor(rating);
        const half = rating - full >= 0.3;
        let html = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= full) html += '<span class="text-amber-400">★</span>';
            else if (i === full + 1 && half) html += '<span class="text-amber-400/60">★</span>';
            else html += '<span class="text-white/20">★</span>';
        }
        return '<span class="' + (size || 'text-lg') + ' tracking-tight">' + html + '</span>';
    };

    const satisfactionLabel = (avg) => {
        if (avg >= 4.5) return { text: 'Excellent', class: 'text-emerald-400' };
        if (avg >= 4) return { text: 'Very good', class: 'text-amber-300' };
        if (avg >= 3.5) return { text: 'Good', class: 'text-amber-400' };
        if (avg >= 3) return { text: 'Fair', class: 'text-orange-400' };
        if (avg > 0) return { text: 'Needs improvement', class: 'text-rose-400' };
        return { text: 'No ratings yet', class: 'text-white/40' };
    };

    const buildFbRatingGauge = (containerId, avg, total) => {
        const root = document.getElementById(containerId);
        if (!root) return;
        const pct = (avg / 5) * 100;
        const circumference = 2 * Math.PI * 42;
        const dash = (pct / 100) * circumference;
        const color = avg >= 4 ? '#34d399' : (avg >= 3 ? '#fbbf24' : '#f87171');

        root.innerHTML =
            '<div class="fb-rating-gauge-ring fb-animate-in">' +
            '<svg width="144" height="144" viewBox="0 0 100 100">' +
            '<circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="10"/>' +
            '<circle cx="50" cy="50" r="42" fill="none" stroke="' + color + '" stroke-width="10" ' +
            'stroke-linecap="round" stroke-dasharray="' + dash + ' ' + circumference + '"/></svg>' +
            '<div class="fb-rating-gauge-center">' +
            '<span class="text-3xl font-black text-white tabular-nums">' + (avg || 0) + '</span>' +
            '<span class="text-[9px] font-bold text-amber-400/90 mt-0.5">out of 5</span></div></div>';

        const labelEl = document.getElementById('fb-satisfaction-label');
        if (labelEl) {
            const sat = satisfactionLabel(avg);
            labelEl.innerHTML = '<span class="' + sat.class + '">' + sat.text + '</span>' +
                (total > 0 ? ' · <span class="text-white/40 font-normal">' + fmt(total) + ' reviews</span>' : '');
        }
    };

    const renderFbInsights = (summary, dist, byType) => {
        const root = document.getElementById('fb-insights');
        if (!root) return;
        const cards = [];
        const total = summary.total_reviews || 0;
        const avg = summary.avg_rating || 0;
        const fiveStar = dist.find(d => d.stars === 5)?.count || 0;
        const lowStar = summary.low_ratings_count || 0;

        if (avg >= 4) {
            cards.push({ type: 'positive', icon: '✨', title: 'Strong satisfaction', text: 'Platform average is ' + avg + '★ — customers are generally happy.' });
        } else if (avg > 0) {
            cards.push({ type: 'warning', icon: '📊', title: 'Room to improve', text: 'Average ' + avg + '★ — focus on service speed and food quality.' });
        }

        if (fiveStar > 0 && total > 0) {
            cards.push({
                type: 'positive', icon: '🌟',
                title: '5-star champions',
                text: fmt(fiveStar) + ' perfect ratings (' + fmtPct(fiveStar, total) + ' of all feedback).',
            });
        }

        if (lowStar > 0) {
            cards.push({
                type: 'alert', icon: '⚠️',
                title: 'Low ratings',
                text: fmt(lowStar) + ' reviews at 1–2★ — worth investigating common issues.',
            });
        } else if (byType.length) {
            const topType = [...byType].sort((a, b) => (b.count || 0) - (a.count || 0))[0];
            if (topType && topType.count > 0) {
                cards.push({
                    type: 'info', icon: '💬',
                    title: 'Most reviewed',
                    text: fbTypeLabel(topType.key) + ' gets the most feedback (' + fmt(topType.count) + ', avg ' + (topType.avg_rating || 0) + '★).',
                });
            }
        }

        if (cards.length === 0) {
            cards.push({ type: 'info', icon: '⭐', title: 'Getting started', text: 'Ratings appear when customers submit feedback via WhatsApp.' });
        }

        root.innerHTML = cards.slice(0, 3).map(c =>
            '<div class="fb-insight-card fb-animate-in is-' + c.type + '">' +
            '<p class="text-lg mb-2">' + c.icon + '</p>' +
            '<p class="text-[10px] font-black text-white/45 uppercase tracking-wider">' + esc(c.title) + '</p>' +
            '<p class="text-sm font-semibold text-white/90 mt-1 leading-relaxed">' + esc(c.text) + '</p></div>'
        ).join('');
    };

    const renderFeedback = (payload) => {
        const f = payload.feedback_overview || {};
        const summary = f.summary || {};
        const dist = f.rating_distribution || [];
        const byType = f.by_type || [];
        const total = summary.total_reviews || 0;
        const avg = summary.avg_rating || 0;
        const days = parseInt(daysEl?.value || '30', 10);
        const positiveCount = dist.filter(d => (d.stars || 0) >= 4).reduce((s, d) => s + (d.count || 0), 0);

        const periodLabel = document.getElementById('fb-period-label');
        if (periodLabel) periodLabel.textContent = 'last ' + days + ' days';

        const heroValue = document.querySelector('#fb-hero-rating .fb-hero-value');
        if (heroValue) heroValue.textContent = (avg || 0) + '★';

        const heroStars = document.getElementById('fb-hero-stars');
        if (heroStars) heroStars.innerHTML = renderStarDisplay(avg, 'text-2xl md:text-3xl');

        const chipsEl = document.getElementById('fb-hero-chips');
        if (chipsEl) {
            chipsEl.innerHTML =
                '<span class="fb-chip fb-animate-in">📝 <strong>' + fmt(total) + '</strong> reviews</span>' +
                '<span class="fb-chip fb-animate-in">👍 <strong>' + fmt(positiveCount) + '</strong> positive</span>' +
                '<span class="fb-chip fb-animate-in">⚠️ <strong>' + fmt(summary.low_ratings_count || 0) + '</strong> low</span>';
        }

        const fbKpiCard = (icon, label, value, sub, accent, iconStyle) =>
            '<div class="fb-kpi-card fb-animate-in" style="--fb-kpi-accent:' + accent + '">' +
            '<div class="flex items-start justify-between gap-3">' +
            '<div class="fb-kpi-icon" style="' + iconStyle + '">' + icon + '</div>' +
            '<div class="text-right flex-1"><p class="text-[9px] font-black text-white/40 uppercase tracking-wider">' + esc(label) + '</p>' +
            '<p class="text-2xl font-black text-white mt-1 tabular-nums leading-none">' + value + '</p>' +
            (sub ? '<p class="text-[10px] text-white/45 mt-1.5">' + esc(sub) + '</p>' : '') + '</div></div></div>';

        const kpiRoot = document.getElementById('feedback-kpis');
        if (kpiRoot) {
            kpiRoot.innerHTML =
                fbKpiCard('📝', 'Total reviews', fmt(total), 'Customers who rated', 'linear-gradient(90deg, #f59e0b, #fbbf24)', 'background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.35)') +
                fbKpiCard('⭐', 'Avg rating', (avg || 0) + ' ★', satisfactionLabel(avg).text, 'linear-gradient(90deg, #fbbf24, #fde047)', 'background:rgba(251,191,36,0.15);border:1px solid rgba(251,191,36,0.35)') +
                fbKpiCard('👍', 'Positive (4–5★)', fmt(positiveCount), fmtPct(positiveCount, total) + ' of total', 'linear-gradient(90deg, #10b981, #34d399)', 'background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35)') +
                fbKpiCard('⚠️', 'Low (1–2★)', fmt(summary.low_ratings_count || 0), 'Needs attention', 'linear-gradient(90deg, #ef4444, #f87171)', 'background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.35)');
        }

        const maxStars = Math.max(...dist.map(d => d.count || 0), 1);
        const fiveCount = dist.find(d => d.stars === 5)?.count || 0;
        const oneCount = dist.find(d => d.stars === 1)?.count || 0;

        const statsRoot = document.getElementById('fb-star-stats');
        if (statsRoot) {
            statsRoot.innerHTML =
                '<div class="fb-stat-pill fb-animate-in"><p class="label">5★ count</p><p class="value text-amber-400">' + fmt(fiveCount) + '</p></div>' +
                '<div class="fb-stat-pill fb-animate-in"><p class="label">Positive %</p><p class="value text-emerald-400">' + fmtPct(positiveCount, total) + '</p></div>' +
                '<div class="fb-stat-pill fb-animate-in"><p class="label">1★ count</p><p class="value text-rose-400">' + fmt(oneCount) + '</p></div>';
        }

        const ratingBars = document.getElementById('chart-rating-bars');
        if (ratingBars) {
            ratingBars.innerHTML = dist.length === 0
                ? '<p class="text-sm text-white/40 py-6">No star ratings yet</p>'
                : dist.map((d, i) => {
                    const w = pctHeight(d.count || 0, maxStars, 8, 2);
                    const color = fbStarColor(d.stars);
                    return '<div class="fb-star-row fb-animate-in" style="animation-delay:' + (i * 0.04) + 's">' +
                        '<span class="text-sm font-bold text-amber-400/90 tabular-nums">' + d.stars + ' ★</span>' +
                        '<div class="fb-star-bar-track"><div class="fb-star-bar-fill" style="width:' + w + '%;background:linear-gradient(90deg,' + color + 'cc,' + color + ')"></div></div>' +
                        '<span class="text-sm font-bold text-white tabular-nums text-right">' + fmt(d.count || 0) + '</span>' +
                        '<span class="text-xs font-bold text-white/45 tabular-nums text-right fb-star-pct">' + fmtPct(d.count || 0, total) + '</span></div>';
                }).join('');
        }

        buildFbRatingGauge('fb-rating-gauge', avg, total);

        const sortedTypes = [...byType].sort((a, b) => (b.count || 0) - (a.count || 0));
        const topType = sortedTypes.find(t => (t.count || 0) > 0) || sortedTypes[0];

        const typeCardsEl = document.getElementById('fb-type-cards');
        if (typeCardsEl) {
            typeCardsEl.innerHTML = byType.length === 0
                ? '<p class="text-sm text-white/40 col-span-3">No category data yet</p>'
                : byType.map((t, i) => {
                    const isTop = topType && t.key === topType.key && (t.count || 0) > 0;
                    const color = fbTypeColor(t.key);
                    return '<div class="fb-type-card fb-animate-in' + (isTop ? ' is-top' : '') + '" style="animation-delay:' + (i * 0.05) + 's;border-top:3px solid ' + color + '">' +
                        '<div class="flex items-center justify-between mb-2">' +
                        '<span class="text-2xl">' + fbTypeIcon(t.key) + '</span>' +
                        (isTop ? '<span class="text-[9px] font-black text-amber-400 uppercase">Most reviewed</span>' : '') + '</div>' +
                        '<p class="text-sm font-bold text-white">' + esc(fbTypeLabel(t.key)) + '</p>' +
                        '<p class="text-2xl font-black text-white tabular-nums mt-2">' + fmt(t.count || 0) + '</p>' +
                        '<p class="text-sm font-bold text-amber-400 mt-1">' + (t.avg_rating || 0) + ' ★ avg</p>' +
                        '<p class="text-[10px] text-white/40 mt-1">' + fmtPct(t.count || 0, total) + ' of reviews</p></div>';
                }).join('');
        }

        const totalLabel = document.getElementById('fb-type-total-label');
        if (totalLabel) totalLabel.textContent = fmt(total) + ' total reviews';

        const byTypeEl = document.getElementById('chart-feedback-by-type');
        if (byTypeEl) {
            const maxType = Math.max(...byType.map(t => t.count || 0), 1);
            byTypeEl.innerHTML = byType.length === 0
                ? '<p class="text-sm text-white/40">No feedback by type yet</p>'
                : sortedTypes.map((t, i) => {
                    const barW = Math.max(((t.count || 0) / maxType) * 100, (t.count || 0) > 0 ? 6 : 0);
                    const color = fbTypeColor(t.key);
                    return '<div class="fb-type-row fb-animate-in" style="animation-delay:' + (i * 0.04) + 's">' +
                        '<span class="text-xs font-semibold text-white/80 flex items-center gap-1.5">' +
                        fbTypeIcon(t.key) + ' ' + esc(fbTypeLabel(t.key)) + '</span>' +
                        '<div class="fb-type-bar-track"><div class="fb-type-bar-fill" style="width:' + barW + '%;background:' + color + '"></div></div>' +
                        '<span class="text-xs font-bold text-amber-400 tabular-nums text-right">' + (t.avg_rating || 0) + ' ★</span>' +
                        '<span class="text-xs font-bold text-white tabular-nums text-right">' + fmt(t.count || 0) + '</span></div>';
                }).join('');
        }

        buildDonut('chart-fb-type-donut', byType.filter(t => (t.count || 0) > 0).map(t => ({
            label: fbTypeLabel(t.key), value: t.count, color: fbTypeColor(t.key),
        })), total || '0', 'reviews');

        const tableEl = document.getElementById('fb-data-table');
        if (tableEl) {
            tableEl.innerHTML =
                '<table class="fb-data-table min-w-[480px]"><thead><tr>' +
                '<th>Stars</th><th>Count</th><th>Share</th></tr></thead><tbody>' +
                dist.map(d =>
                    '<tr class="fb-animate-in"><td class="font-bold text-amber-400">' + d.stars + ' ★</td>' +
                    '<td class="tabular-nums font-bold text-white">' + fmt(d.count || 0) + '</td>' +
                    '<td class="tabular-nums text-white/60">' + fmtPct(d.count || 0, total) + '</td></tr>'
                ).join('') +
                '</tbody></table>' +
                (byType.length ? '<h5 class="text-sm font-bold text-white mt-8 mb-4">By category</h5>' +
                '<table class="fb-data-table min-w-[480px]"><thead><tr><th>Category</th><th>Reviews</th><th>Avg</th><th>Share</th></tr></thead><tbody>' +
                sortedTypes.map(t =>
                    '<tr class="fb-animate-in"><td class="font-semibold">' + fbTypeIcon(t.key) + ' ' + esc(fbTypeLabel(t.key)) + '</td>' +
                    '<td class="tabular-nums font-bold text-white">' + fmt(t.count || 0) + '</td>' +
                    '<td class="tabular-nums text-amber-400 font-bold">' + (t.avg_rating || 0) + ' ★</td>' +
                    '<td class="tabular-nums text-white/60">' + fmtPct(t.count || 0, total) + '</td></tr>'
                ).join('') + '</tbody></table>' : '');
        }

        renderFbInsights(summary, dist, byType);
    };

    const renderPayments = (payload) => {
        const p = payload.tips_and_payments || {};
        const tips = p.tips || {};
        const payments = p.payments || {};
        const sym = payload.currency_symbol || currencySymbol;
        const methods = p.payment_methods || [];
        const purpose = p.payment_purpose || [];
        const days = parseInt(daysEl?.value || '30', 10);

        const money = (n) => sym + ' ' + fmt(n || 0);
        const tipAmount = tips.total_amount || 0;
        const payAmount = payments.total_amount || 0;
        const totalVolume = tipAmount + payAmount;
        const txnTotal = (tips.count || 0) + (payments.count || 0);
        const methodTxnTotal = methods.reduce((s, m) => s + (m.value || 0), 0);
        const purposeTxnTotal = purpose.reduce((s, m) => s + (m.value || 0), 0);

        const tpKpiCard = (icon, label, value, sub, accent, iconStyle) =>
            '<div class="tp-kpi-card tp-animate-in" style="--tp-kpi-accent:' + accent + '">' +
            '<div class="flex items-start justify-between gap-3">' +
            '<div class="tp-kpi-icon" style="' + iconStyle + '">' + icon + '</div>' +
            '<div class="text-right flex-1"><p class="text-[9px] font-black text-white/40 uppercase tracking-wider">' + esc(label) + '</p>' +
            '<p class="text-2xl font-black text-white mt-1 tabular-nums leading-none">' + value + '</p>' +
            (sub ? '<p class="text-[10px] text-white/45 mt-1.5">' + esc(sub) + '</p>' : '') + '</div></div></div>';

        const tpStatPill = (label, value, colorClass) =>
            '<div class="tp-stat-pill tp-animate-in"><p class="label">' + esc(label) + '</p>' +
            '<p class="value tabular-nums ' + (colorClass || 'text-white') + '">' + value + '</p></div>';

        const tpMethodIcon = (key) => ({ cash: '💵', ussd: '📱', card: '💳' })[key] || '💰';
        const tpPurposeIcon = (key) => ({ order: '🧾', quick: '⚡' })[key] || '💳';
        const tpPurposeDesc = (key) => key === 'quick'
            ? 'Fast pay without full bill flow'
            : 'Full table bill settlements';

        const periodLabel = document.getElementById('tp-period-label');
        if (periodLabel) periodLabel.textContent = 'last ' + days + ' days';

        const heroEl = document.getElementById('tp-hero-volume');
        if (heroEl) heroEl.textContent = money(totalVolume);

        const sortedMethods = [...methods].sort((a, b) => (b.value || 0) - (a.value || 0));
        const topMethod = sortedMethods.find(m => (m.value || 0) > 0) || sortedMethods[0];
        const sortedPurpose = [...purpose].sort((a, b) => (b.value || 0) - (a.value || 0));
        const topPurpose = sortedPurpose.find(m => (m.value || 0) > 0) || sortedPurpose[0];

        const chipsEl = document.getElementById('tp-hero-chips');
        if (chipsEl) {
            chipsEl.innerHTML =
                '<span class="tp-chip tp-animate-in">💝 <strong>' + money(tipAmount) + '</strong> tips</span>' +
                '<span class="tp-chip tp-animate-in">💳 <strong>' + fmt(payments.count || 0) + '</strong> payments</span>' +
                '<span class="tp-chip tp-animate-in">👥 <strong>' + fmt(tips.unique_waiters_tipped || 0) + '</strong> staff tipped</span>';
        }

        const kpiRoot = document.getElementById('tips-kpis');
        if (kpiRoot) {
            kpiRoot.innerHTML =
                tpKpiCard('💝', 'Tips collected', money(tipAmount), fmt(tips.count || 0) + ' tips', 'linear-gradient(90deg, #ec4899, #f472b6)', 'background:rgba(236,72,153,0.15);border:1px solid rgba(236,72,153,0.35)') +
                tpKpiCard('💳', 'Payments', money(payAmount), fmt(payments.count || 0) + ' transactions', 'linear-gradient(90deg, #10b981, #34d399)', 'background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35)') +
                tpKpiCard('📊', 'Avg tip', money(tips.avg_amount || 0), 'Per tip amount', 'linear-gradient(90deg, #8C71F6, #a78bfa)', 'background:rgba(140,113,246,0.15);border:1px solid rgba(140,113,246,0.35)') +
                tpKpiCard('👥', 'Staff tipped', fmt(tips.unique_waiters_tipped || 0), 'Unique waiters — no names', 'linear-gradient(90deg, #f59e0b, #fbbf24)', 'background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.35)');
        }

        const flowStats = document.getElementById('tp-flow-stats');
        if (flowStats) {
            flowStats.innerHTML =
                tpStatPill('Tips share', fmtPct(tipAmount, totalVolume), 'text-pink-400') +
                tpStatPill('Pay share', fmtPct(payAmount, totalVolume), 'text-emerald-400') +
                tpStatPill('Transactions', fmt(txnTotal), 'text-white');
        }

        const flowBars = document.getElementById('tp-flow-bars');
        if (flowBars) {
            const flows = [
                { key: 'tips', label: 'Tips', icon: '💝', amount: tipAmount, count: tips.count || 0, color: '#ec4899' },
                { key: 'payments', label: 'Payments', icon: '💳', amount: payAmount, count: payments.count || 0, color: '#10b981' },
            ];
            const maxFlow = Math.max(...flows.map(f => f.amount || 0), 1);
            flowBars.innerHTML = totalVolume === 0 && txnTotal === 0
                ? '<p class="text-sm text-white/40 py-6">No tips or payments yet for this period</p>'
                : flows.map((f, i) => {
                    const w = pctHeight(f.amount || 0, maxFlow, 8, 2);
                    return '<div class="tp-flow-row tp-animate-in" style="animation-delay:' + (i * 0.05) + 's">' +
                        '<span class="text-sm font-bold text-white flex items-center gap-2"><span>' + f.icon + '</span>' + esc(f.label) + '</span>' +
                        '<div class="tp-flow-bar-track"><div class="tp-flow-bar-fill" style="width:' + w + '%;background:linear-gradient(90deg,' + f.color + 'cc,' + f.color + ')"></div></div>' +
                        '<span class="text-sm font-bold text-white tabular-nums text-right">' + money(f.amount) + '</span>' +
                        '<span class="text-xs font-bold text-pink-400/80 tabular-nums text-right">' + fmt(f.count) + ' txns</span></div>';
                }).join('');
        }

        const topMethodEl = document.getElementById('tp-top-method');
        if (topMethodEl) {
            if (!topMethod || !(topMethod.value > 0)) {
                topMethodEl.innerHTML = '<p class="text-sm text-white/40 text-center py-6">No payment methods used yet</p>';
            } else {
                topMethodEl.innerHTML =
                    '<div class="flex items-center gap-3 mb-3">' +
                    '<span class="text-3xl">' + tpMethodIcon(topMethod.key) + '</span>' +
                    '<div><p class="text-lg font-black text-white">' + esc(topMethod.label) + '</p>' +
                    '<p class="text-xs text-pink-400/80 font-bold">' + fmtPct(topMethod.value, methodTxnTotal) + ' of transactions</p></div></div>' +
                    '<p class="text-3xl font-black text-pink-400 tabular-nums">' + fmt(topMethod.value) + '</p>' +
                    '<p class="text-[10px] text-white/40 mt-1">' + money(topMethod.amount || 0) + ' volume</p>';
            }
        }

        buildDonut('chart-payment-methods', sortedMethods.filter(m => (m.value || 0) > 0).map(m => ({
            label: m.label, value: m.value, color: m.color,
        })), methodTxnTotal || '0', 'txns');

        const methodsTotalLabel = document.getElementById('tp-methods-total-label');
        if (methodsTotalLabel) methodsTotalLabel.textContent = fmt(methodTxnTotal) + ' transactions';

        const methodBars = document.getElementById('tp-method-bars');
        if (methodBars) {
            const maxMethod = Math.max(...sortedMethods.map(m => m.value || 0), 1);
            methodBars.innerHTML = sortedMethods.length === 0 || methodTxnTotal === 0
                ? '<p class="text-sm text-white/40">No payment method data yet</p>'
                : sortedMethods.map((m, i) => {
                    const w = pctHeight(m.value || 0, maxMethod, 8, 2);
                    return '<div class="tp-method-row tp-animate-in" style="animation-delay:' + (i * 0.04) + 's">' +
                        '<span class="text-sm font-semibold text-white/85 flex items-center gap-2">' +
                        '<span>' + tpMethodIcon(m.key) + '</span>' + esc(m.label) + '</span>' +
                        '<div class="tp-method-bar-track"><div class="tp-method-bar-fill" style="width:' + w + '%;background:' + (m.color || '#ec4899') + '"></div></div>' +
                        '<span class="text-sm font-bold text-pink-400 tabular-nums text-right">' + fmtPct(m.value || 0, methodTxnTotal) + '</span>' +
                        '<span class="text-sm font-bold text-white tabular-nums text-right">' + fmt(m.value || 0) + '</span></div>';
                }).join('');
        }

        const methodCards = document.getElementById('tp-method-cards');
        if (methodCards) {
            methodCards.innerHTML = sortedMethods.length === 0
                ? '<p class="text-sm text-white/40 col-span-3">No method data yet</p>'
                : sortedMethods.map((m, i) => {
                    const isTop = topMethod && m.key === topMethod.key && (m.value || 0) > 0;
                    return '<div class="tp-method-card tp-animate-in' + (isTop ? ' is-top' : '') + '" style="animation-delay:' + (i * 0.05) + 's;border-top:3px solid ' + (m.color || '#ec4899') + '">' +
                        '<div class="flex items-center justify-between mb-2">' +
                        '<span class="text-2xl">' + tpMethodIcon(m.key) + '</span>' +
                        (isTop ? '<span class="text-[9px] font-black text-pink-400 uppercase">Most used</span>' : '') + '</div>' +
                        '<p class="text-sm font-bold text-white">' + esc(m.label) + '</p>' +
                        '<p class="text-2xl font-black text-white tabular-nums mt-2">' + fmt(m.value || 0) + '</p>' +
                        '<p class="text-sm font-bold text-pink-400 mt-1">' + money(m.amount || 0) + '</p>' +
                        '<p class="text-[10px] text-white/40 mt-1">' + fmtPct(m.value || 0, methodTxnTotal) + ' of txns</p></div>';
                }).join('');
        }

        const purposeTotalLabel = document.getElementById('tp-purpose-total-label');
        if (purposeTotalLabel) purposeTotalLabel.textContent = fmt(purposeTxnTotal) + ' transactions';

        const purposeCards = document.getElementById('tp-purpose-cards');
        if (purposeCards) {
            purposeCards.innerHTML = sortedPurpose.length === 0
                ? '<p class="text-sm text-white/40 col-span-2">No purpose data yet</p>'
                : sortedPurpose.map((m, i) => {
                    const isTop = topPurpose && m.key === topPurpose.key && (m.value || 0) > 0;
                    return '<div class="tp-purpose-card tp-animate-in' + (isTop ? ' is-top' : '') + '" style="animation-delay:' + (i * 0.05) + 's;border-top:3px solid ' + (m.color || '#8C71F6') + '">' +
                        '<div class="flex items-center justify-between mb-2">' +
                        '<span class="text-2xl">' + tpPurposeIcon(m.key) + '</span>' +
                        (isTop ? '<span class="text-[9px] font-black text-pink-400 uppercase">Leading</span>' : '') + '</div>' +
                        '<p class="text-sm font-bold text-white">' + esc(m.label) + '</p>' +
                        '<p class="text-2xl font-black text-white tabular-nums mt-2">' + fmt(m.value || 0) + '</p>' +
                        '<p class="text-sm font-bold text-emerald-400 mt-1">' + money(m.amount || 0) + '</p>' +
                        '<p class="text-[10px] text-white/40 mt-1">' + esc(tpPurposeDesc(m.key)) + '</p></div>';
                }).join('');
        }

        const purposeBars = document.getElementById('tp-purpose-bars');
        if (purposeBars) {
            const maxPurpose = Math.max(...sortedPurpose.map(m => m.value || 0), 1);
            purposeBars.innerHTML = sortedPurpose.length === 0 || purposeTxnTotal === 0
                ? '<p class="text-sm text-white/40">No payment purpose data yet</p>'
                : sortedPurpose.map((m, i) => {
                    const w = pctHeight(m.value || 0, maxPurpose, 8, 2);
                    return '<div class="tp-purpose-row tp-animate-in" style="animation-delay:' + (i * 0.04) + 's">' +
                        '<span class="text-sm font-semibold text-white/85 flex items-center gap-2">' +
                        '<span>' + tpPurposeIcon(m.key) + '</span>' + esc(m.label) + '</span>' +
                        '<div class="tp-purpose-bar-track"><div class="tp-purpose-bar-fill" style="width:' + w + '%;background:' + (m.color || '#8C71F6') + '"></div></div>' +
                        '<span class="text-sm font-bold text-pink-400 tabular-nums text-right">' + fmtPct(m.value || 0, purposeTxnTotal) + '</span>' +
                        '<span class="text-sm font-bold text-white tabular-nums text-right">' + fmt(m.value || 0) + '</span></div>';
                }).join('');
        }

        buildDonut('chart-payment-purpose', sortedPurpose.filter(m => (m.value || 0) > 0).map(m => ({
            label: m.label, value: m.value, color: m.color,
        })), purposeTxnTotal || '0', 'split');

        const tableEl = document.getElementById('tp-data-table');
        if (tableEl) {
            tableEl.innerHTML =
                '<table class="tp-data-table min-w-[480px]"><thead><tr>' +
                '<th>Metric</th><th>Count</th><th>Amount</th></tr></thead><tbody>' +
                '<tr class="tp-animate-in"><td class="font-semibold">💝 Tips</td>' +
                '<td class="tabular-nums font-bold text-white">' + fmt(tips.count || 0) + '</td>' +
                '<td class="tabular-nums text-pink-400 font-bold">' + money(tipAmount) + '</td></tr>' +
                '<tr class="tp-animate-in"><td class="font-semibold">💳 Payments</td>' +
                '<td class="tabular-nums font-bold text-white">' + fmt(payments.count || 0) + '</td>' +
                '<td class="tabular-nums text-emerald-400 font-bold">' + money(payAmount) + '</td></tr>' +
                '</tbody></table>' +
                (sortedMethods.length ? '<h5 class="text-sm font-bold text-white mt-8 mb-4">Payment methods</h5>' +
                '<table class="tp-data-table min-w-[480px]"><thead><tr><th>Method</th><th>Txns</th><th>Amount</th><th>Share</th></tr></thead><tbody>' +
                sortedMethods.map(m =>
                    '<tr class="tp-animate-in"><td class="font-semibold">' + tpMethodIcon(m.key) + ' ' + esc(m.label) + '</td>' +
                    '<td class="tabular-nums font-bold text-white">' + fmt(m.value || 0) + '</td>' +
                    '<td class="tabular-nums text-pink-400 font-bold">' + money(m.amount || 0) + '</td>' +
                    '<td class="tabular-nums text-white/60">' + fmtPct(m.value || 0, methodTxnTotal) + '</td></tr>'
                ).join('') + '</tbody></table>' : '') +
                (sortedPurpose.length ? '<h5 class="text-sm font-bold text-white mt-8 mb-4">Payment purpose</h5>' +
                '<table class="tp-data-table min-w-[480px]"><thead><tr><th>Type</th><th>Txns</th><th>Amount</th><th>Share</th></tr></thead><tbody>' +
                sortedPurpose.map(m =>
                    '<tr class="tp-animate-in"><td class="font-semibold">' + tpPurposeIcon(m.key) + ' ' + esc(m.label) + '</td>' +
                    '<td class="tabular-nums font-bold text-white">' + fmt(m.value || 0) + '</td>' +
                    '<td class="tabular-nums text-emerald-400 font-bold">' + money(m.amount || 0) + '</td>' +
                    '<td class="tabular-nums text-white/60">' + fmtPct(m.value || 0, purposeTxnTotal) + '</td></tr>'
                ).join('') + '</tbody></table>' : '');
        }

        const insightsEl = document.getElementById('tp-insights');
        if (insightsEl) {
            const cards = [];
            if (topMethod && topMethod.value > 0) {
                cards.push({
                    type: 'positive', icon: '🏆',
                    title: 'Top payment method',
                    text: topMethod.label + ' leads with ' + fmt(topMethod.value) + ' transactions (' + fmtPct(topMethod.value, methodTxnTotal) + ').',
                });
            }
            const bill = purpose.find(m => m.key === 'order');
            const quick = purpose.find(m => m.key === 'quick');
            if (bill && quick && purposeTxnTotal > 0) {
                const billPct = Math.round(((bill.value || 0) / purposeTxnTotal) * 100);
                cards.push({
                    type: billPct >= 50 ? 'info' : 'warning',
                    icon: '⚖️',
                    title: 'Bill vs quick',
                    text: 'Bill payments ' + billPct + '% · Quick pay ' + (100 - billPct) + '% — ' +
                        (billPct >= 50 ? 'most customers settle full bills.' : 'quick pay is popular.'),
                });
            }
            if ((tips.avg_amount || 0) > 0 && (tips.count || 0) > 0) {
                cards.push({
                    type: 'positive', icon: '💝',
                    title: 'Tip generosity',
                    text: 'Average tip is ' + money(tips.avg_amount) + ' across ' + fmt(tips.count) + ' tips.',
                });
            } else if (totalVolume > 0) {
                cards.push({
                    type: 'info', icon: '💰',
                    title: 'Platform volume',
                    text: money(totalVolume) + ' moved in tips & payments over the last ' + days + ' days.',
                });
            }
            if (cards.length === 0) {
                cards.push({ type: 'info', icon: '💳', title: 'Getting started', text: 'Tips and payment data appear when customers pay bills or tip staff via WhatsApp.' });
            }
            insightsEl.innerHTML = cards.slice(0, 3).map(c =>
                '<div class="tp-insight-card tp-animate-in is-' + c.type + '">' +
                '<p class="text-lg mb-2">' + c.icon + '</p>' +
                '<p class="text-[10px] font-black text-white/45 uppercase tracking-wider">' + esc(c.title) + '</p>' +
                '<p class="text-sm font-semibold text-white/90 mt-1 leading-relaxed">' + esc(c.text) + '</p></div>'
            ).join('');
        }
    };

    const renderLanguage = (payload) => {
        const l = payload.language_and_behavior || {};
        const langs = l.language_split || [];
        const peak = l.peak_hours || {};
        const eventHours = peak.events || [];
        const sessionHours = peak.sessions || [];
        const days = parseInt(daysEl?.value || '30', 10);

        const totalLang = langs.reduce((s, x) => s + (x.value || 0), 0);
        const sortedLangs = [...langs].sort((a, b) => (b.value || 0) - (a.value || 0));
        const topLang = sortedLangs.find(x => (x.value || 0) > 0) || sortedLangs[0];
        const eventTotal = eventHours.reduce((s, h) => s + (h.count || 0), 0);
        const sessionTotal = sessionHours.reduce((s, h) => s + (h.count || 0), 0);

        const lgKpiCard = (icon, label, value, sub, accent, iconStyle) =>
            '<div class="lg-kpi-card lg-animate-in" style="--lg-kpi-accent:' + accent + '">' +
            '<div class="flex items-start justify-between gap-3">' +
            '<div class="lg-kpi-icon" style="' + iconStyle + '">' + icon + '</div>' +
            '<div class="text-right flex-1"><p class="text-[9px] font-black text-white/40 uppercase tracking-wider">' + esc(label) + '</p>' +
            '<p class="text-2xl font-black text-white mt-1 tabular-nums leading-none">' + value + '</p>' +
            (sub ? '<p class="text-[10px] text-white/45 mt-1.5">' + esc(sub) + '</p>' : '') + '</div></div></div>';

        const lgStatPill = (label, value, colorClass) =>
            '<div class="lg-stat-pill lg-animate-in"><p class="label">' + esc(label) + '</p>' +
            '<p class="value tabular-nums ' + (colorClass || 'text-white') + '">' + value + '</p></div>';

        const lgLangFlag = (key) => ({ en: '🇬🇧', sw: '🇹🇿', af: '🇿🇦', fr: '🇫🇷', pt: '🇵🇹' })[key] || '🌐';

        const periodLabel = document.getElementById('lg-period-label');
        if (periodLabel) periodLabel.textContent = 'last ' + days + ' days';

        const heroEl = document.getElementById('lg-hero-sessions');
        if (heroEl) heroEl.textContent = fmt(totalLang);

        const chipsEl = document.getElementById('lg-hero-chips');
        if (chipsEl) {
            chipsEl.innerHTML =
                '<span class="lg-chip lg-animate-in">🌐 <strong>' + fmt(sortedLangs.length) + '</strong> languages</span>' +
                '<span class="lg-chip lg-animate-in">🏆 <strong>' + esc(topLang?.label || '—') + '</strong></span>' +
                '<span class="lg-chip lg-animate-in">⏰ <strong>' + fmtHourLabel(peak.peak_event_hour) + '</strong> peak</span>';
        }

        const kpiRoot = document.getElementById('lg-kpis');
        if (kpiRoot) {
            kpiRoot.innerHTML =
                lgKpiCard('🌐', 'Language sessions', fmt(totalLang), sortedLangs.length + ' languages active', 'linear-gradient(90deg, #6366f1, #818cf8)', 'background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.35)') +
                lgKpiCard('🏆', 'Top language', topLang ? fmt(topLang.value || 0) : '0', topLang ? topLang.label : 'No data', 'linear-gradient(90deg, #8C71F6, #a78bfa)', 'background:rgba(140,113,246,0.15);border:1px solid rgba(140,113,246,0.35)') +
                lgKpiCard('📈', 'Peak bot hour', fmtHourLabel(peak.peak_event_hour), eventTotal ? fmt(eventTotal) + ' events total' : 'No events', 'linear-gradient(90deg, #f59e0b, #fbbf24)', 'background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.35)') +
                lgKpiCard('💬', 'Peak session hour', fmtHourLabel(peak.peak_session_hour), sessionTotal ? fmt(sessionTotal) + ' sessions total' : 'No sessions', 'linear-gradient(90deg, #10b981, #34d399)', 'background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35)');
        }

        const langStats = document.getElementById('lg-lang-stats');
        if (langStats) {
            langStats.innerHTML =
                lgStatPill('Languages', fmt(sortedLangs.length), 'text-indigo-400') +
                lgStatPill('Top share', topLang ? fmtPct(topLang.value, totalLang) : '0%', 'text-violet-400') +
                lgStatPill('Sessions', fmt(totalLang), 'text-white');
        }

        const langBars = document.getElementById('lg-lang-bars');
        if (langBars) {
            const maxLang = Math.max(...sortedLangs.map(x => x.value || 0), 1);
            langBars.innerHTML = sortedLangs.length === 0 || totalLang === 0
                ? '<p class="text-sm text-white/40 py-4">No language preference data yet</p>'
                : sortedLangs.map((x, i) => {
                    const w = pctHeight(x.value || 0, maxLang, 8, 2);
                    return '<div class="lg-lang-row lg-animate-in" style="animation-delay:' + (i * 0.04) + 's">' +
                        '<span class="text-sm font-semibold text-white/85 flex items-center gap-2">' +
                        '<span>' + lgLangFlag(x.key) + '</span>' + esc(x.label) + '</span>' +
                        '<div class="lg-lang-bar-track"><div class="lg-lang-bar-fill" style="width:' + w + '%;background:' + (x.color || '#6366f1') + '"></div></div>' +
                        '<span class="text-sm font-bold text-indigo-400 tabular-nums text-right">' + fmtPct(x.value || 0, totalLang) + '</span>' +
                        '<span class="text-sm font-bold text-white tabular-nums text-right">' + fmt(x.value || 0) + '</span></div>';
                }).join('');
        }

        const langCards = document.getElementById('lg-lang-cards');
        if (langCards) {
            langCards.innerHTML = sortedLangs.length === 0
                ? '<p class="text-sm text-white/40 col-span-3">No language data yet</p>'
                : sortedLangs.slice(0, 3).map((x, i) => {
                    const isTop = topLang && x.key === topLang.key && (x.value || 0) > 0;
                    return '<div class="lg-lang-card lg-animate-in' + (isTop ? ' is-top' : '') + '" style="animation-delay:' + (i * 0.05) + 's;border-top:3px solid ' + (x.color || '#6366f1') + '">' +
                        '<div class="flex items-center justify-between mb-2">' +
                        '<span class="text-2xl">' + lgLangFlag(x.key) + '</span>' +
                        (isTop ? '<span class="text-[9px] font-black text-indigo-400 uppercase">Most used</span>' : '') + '</div>' +
                        '<p class="text-sm font-bold text-white">' + esc(x.label) + '</p>' +
                        '<p class="text-2xl font-black text-white tabular-nums mt-2">' + fmt(x.value || 0) + '</p>' +
                        '<p class="text-xs text-indigo-400/80 font-bold mt-1">' + fmtPct(x.value || 0, totalLang) + ' of sessions</p></div>';
                }).join('');
        }

        const topLangEl = document.getElementById('lg-top-lang');
        if (topLangEl) {
            if (!topLang || !(topLang.value > 0)) {
                topLangEl.innerHTML = '<p class="text-sm text-white/40 text-center py-6">No language selections yet</p>';
            } else {
                topLangEl.innerHTML =
                    '<div class="flex items-center gap-3 mb-3">' +
                    '<span class="text-3xl">' + lgLangFlag(topLang.key) + '</span>' +
                    '<div><p class="text-lg font-black text-white">' + esc(topLang.label) + '</p>' +
                    '<p class="text-xs text-indigo-400/80 font-bold">' + fmtPct(topLang.value, totalLang) + ' of sessions</p></div></div>' +
                    '<p class="text-3xl font-black text-indigo-400 tabular-nums">' + fmt(topLang.value) + '</p>' +
                    '<p class="text-[10px] text-white/40 mt-1">Code: ' + esc(topLang.key) + '</p>';
            }
        }

        buildDonut('chart-language', sortedLangs.filter(x => (x.value || 0) > 0).map(x => ({
            label: x.label, value: x.value, color: x.color,
        })), totalLang || '0', 'sessions');

        const peakTotalLabel = document.getElementById('lg-peak-total-label');
        if (peakTotalLabel) peakTotalLabel.textContent = fmt(eventTotal) + ' bot events · ' + fmt(sessionTotal) + ' sessions';

        const eventStats = buildHourlyBarChart('chart-peak-hours', eventHours, peak.peak_event_hour, {
            unit: 'events',
            theme: 'indigo',
            animateClass: 'lg-animate-in',
        });
        const sessionStats = buildHourlyBarChart('chart-peak-sessions', sessionHours, peak.peak_session_hour, {
            unit: 'sessions',
            theme: 'emerald',
            compact: true,
            animateClass: 'lg-animate-in',
        });

        const legendEl = document.getElementById('lg-peak-legend');
        if (legendEl) {
            legendEl.innerHTML =
                '<span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-indigo-400"></span> Bot events</span>' +
                '<span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-sm bg-amber-400"></span> Peak hour</span>' +
                (eventStats.peak ? '<span>Busiest: <strong class="text-white">' + esc(eventStats.peak.label || fmtHourLabel(eventStats.peak.hour)) + '</strong> · ' + fmt(eventStats.peak.count || 0) + ' events</span>' : '');
        }

        const peakCards = document.getElementById('lg-peak-cards');
        if (peakCards) {
            peakCards.innerHTML =
                '<div class="lg-peak-pill lg-animate-in"><p class="label">Peak events</p><p class="value text-amber-400">' + fmtHourLabel(peak.peak_event_hour) + '</p></div>' +
                '<div class="lg-peak-pill lg-animate-in"><p class="label">Peak sessions</p><p class="value text-emerald-400">' + fmtHourLabel(peak.peak_session_hour) + '</p></div>';
        }

        const tableEl = document.getElementById('lg-data-table');
        if (tableEl) {
            tableEl.innerHTML =
                (sortedLangs.length ? '<table class="lg-data-table min-w-[480px]"><thead><tr>' +
                '<th>Language</th><th>Sessions</th><th>Share</th></tr></thead><tbody>' +
                sortedLangs.map(x =>
                    '<tr class="lg-animate-in"><td class="font-semibold">' + lgLangFlag(x.key) + ' ' + esc(x.label) + '</td>' +
                    '<td class="tabular-nums font-bold text-white">' + fmt(x.value || 0) + '</td>' +
                    '<td class="tabular-nums text-indigo-400 font-bold">' + fmtPct(x.value || 0, totalLang) + '</td></tr>'
                ).join('') + '</tbody></table>' : '<p class="text-sm text-white/40">No language data yet</p>') +
                (eventHours.length ? '<h5 class="text-sm font-bold text-white mt-8 mb-4">Peak hours (bot events)</h5>' +
                '<table class="lg-data-table min-w-[480px]"><thead><tr><th>Hour</th><th>Events</th><th>Share</th></tr></thead><tbody>' +
                [...eventHours].sort((a, b) => (b.count || 0) - (a.count || 0)).slice(0, 6).map(h =>
                    '<tr class="lg-animate-in"><td class="font-semibold">' + esc(h.label || fmtHourLabel(h.hour)) + '</td>' +
                    '<td class="tabular-nums font-bold text-white">' + fmt(h.count || 0) + '</td>' +
                    '<td class="tabular-nums text-white/60">' + fmtPct(h.count || 0, eventTotal) + '</td></tr>'
                ).join('') + '</tbody></table>' : '');
        }

        const insightsEl = document.getElementById('lg-insights');
        if (insightsEl) {
            const cards = [];
            if (topLang && topLang.value > 0) {
                cards.push({
                    type: 'positive', icon: '🌐',
                    title: 'Dominant language',
                    text: topLang.label + ' leads with ' + fmt(topLang.value) + ' sessions (' + fmtPct(topLang.value, totalLang) + ').',
                });
            }
            const secondLang = sortedLangs[1];
            if (topLang && secondLang && totalLang > 0 && secondLang.value > 0) {
                const gap = Math.round(((topLang.value - secondLang.value) / totalLang) * 100);
                cards.push({
                    type: gap < 15 ? 'warning' : 'info',
                    icon: '⚖️',
                    title: 'Language balance',
                    text: topLang.label + ' vs ' + secondLang.label + ' — ' + (gap < 15 ? 'usage is fairly balanced.' : gap + '% gap between top two.'),
                });
            }
            if (peak.peak_event_hour != null && eventStats.peak) {
                cards.push({
                    type: 'info', icon: '⏰',
                    title: 'Busiest hour',
                    text: 'Most bot activity at ' + fmtHourLabel(peak.peak_event_hour) + ' with ' + fmt(eventStats.peak.count || 0) + ' events.',
                });
            } else if (cards.length === 0) {
                cards.push({ type: 'info', icon: '🌐', title: 'Getting started', text: 'Language and peak-hour data appear as customers interact with the WhatsApp bot.' });
            }
            insightsEl.innerHTML = cards.slice(0, 3).map(c =>
                '<div class="lg-insight-card lg-animate-in is-' + c.type + '">' +
                '<p class="text-lg mb-2">' + c.icon + '</p>' +
                '<p class="text-[10px] font-black text-white/45 uppercase tracking-wider">' + esc(c.title) + '</p>' +
                '<p class="text-sm font-semibold text-white/90 mt-1 leading-relaxed">' + esc(c.text) + '</p></div>'
            ).join('');
        }
    };

    const renderPulse = (payload) => {
        const p = payload.platform_pulse || {};
        const sym = payload.currency_symbol || currencySymbol;
        const days = parseInt(daysEl?.value || '30', 10) || p.filters?.days || 30;

        const active = p.active_venues || 0;
        const total = p.total_venues || 0;
        const inactive = Math.max(total - active, 0);
        const activePct = total > 0 ? Math.round((active / total) * 100) : 0;
        const ordersToday = p.orders_today || 0;
        const ordersMonth = p.orders_month || 0;
        const feedbackCount = p.feedback_count || 0;
        const avgRating = p.avg_rating || 0;
        const qrScans = p.qr_scans || 0;
        const payCount = p.payments_count || 0;
        const payTotal = p.payments_total || 0;
        const botEvents = p.bot_events || 0;

        const money = (n) => sym + ' ' + fmt(n || 0);

        const plKpiCard = (icon, label, value, sub, accent, iconStyle) =>
            '<div class="pl-kpi-card pl-animate-in" style="--pl-kpi-accent:' + accent + '">' +
            '<div class="flex items-start justify-between gap-3">' +
            '<div class="pl-kpi-icon" style="' + iconStyle + '">' + icon + '</div>' +
            '<div class="text-right flex-1"><p class="text-[9px] font-black text-white/40 uppercase tracking-wider">' + esc(label) + '</p>' +
            '<p class="text-2xl font-black text-white mt-1 tabular-nums leading-none">' + value + '</p>' +
            (sub ? '<p class="text-[10px] text-white/45 mt-1.5">' + esc(sub) + '</p>' : '') + '</div></div></div>';

        const plStatPill = (label, value, colorClass) =>
            '<div class="pl-stat-pill pl-animate-in"><p class="label">' + esc(label) + '</p>' +
            '<p class="value tabular-nums ' + (colorClass || 'text-white') + '">' + value + '</p></div>';

        const buildPulseVenueRing = () => {
            const root = document.getElementById('pulse-venue-ring');
            if (!root) return;
            const pct = activePct;
            const circumference = 2 * Math.PI * 42;
            const dash = (pct / 100) * circumference;

            if (total === 0) {
                root.innerHTML = '<p class="text-sm text-white/40 text-center">No venues registered yet</p>';
                return;
            }

            root.innerHTML =
                '<div class="pl-venue-ring pl-animate-in">' +
                '<svg width="152" height="152" viewBox="0 0 100 100">' +
                '<circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.08)" stroke-width="10"/>' +
                '<circle cx="50" cy="50" r="42" fill="none" stroke="url(#pulseVenueGrad)" stroke-width="10" ' +
                'stroke-linecap="round" stroke-dasharray="' + dash + ' ' + circumference + '"/>' +
                '<defs><linearGradient id="pulseVenueGrad" x1="0%" y1="0%" x2="100%" y2="0%">' +
                '<stop offset="0%" stop-color="#10b981"/><stop offset="100%" stop-color="#34d399"/></linearGradient></defs>' +
                '</svg>' +
                '<div class="pl-venue-ring-center">' +
                '<span class="text-3xl font-black text-white tabular-nums">' + pct + '%</span>' +
                '<span class="text-[9px] font-bold text-white/40 uppercase tracking-widest mt-0.5">active</span>' +
                '</div></div>';

            const pillsRoot = document.getElementById('pulse-venue-pills');
            if (pillsRoot) {
                pillsRoot.innerHTML =
                    '<div class="pl-venue-pill pl-animate-in"><p class="label text-emerald-400/80">Active</p>' +
                    '<p class="value text-xl">' + fmt(active) + '</p></div>' +
                    '<div class="pl-venue-pill pl-animate-in"><p class="label text-rose-400/80">Inactive</p>' +
                    '<p class="value text-xl">' + fmt(inactive) + '</p></div>';
            }
        };

        const periodLabel = document.getElementById('pl-period-label');
        if (periodLabel) periodLabel.textContent = 'last ' + days + ' days';

        const heroEl = document.getElementById('pl-hero-active');
        if (heroEl) heroEl.textContent = activePct + '%';

        const heroSub = document.getElementById('pl-hero-sub');
        if (heroSub) heroSub.textContent = fmt(active) + ' of ' + fmt(total) + ' venues active — anonymous platform snapshot.';

        const chipsEl = document.getElementById('pl-hero-chips');
        if (chipsEl) {
            chipsEl.innerHTML =
                '<span class="pl-chip pl-animate-in">📦 <strong>' + fmt(ordersToday) + '</strong> orders today</span>' +
                '<span class="pl-chip pl-animate-in">💬 <strong>' + fmt(botEvents) + '</strong> bot events</span>' +
                '<span class="pl-chip pl-animate-in">⭐ <strong>' + (avgRating || 0) + '★</strong> avg rating</span>';
        }

        const kpiRoot = document.getElementById('pulse-kpis');
        if (kpiRoot) {
            kpiRoot.innerHTML =
                plKpiCard('🏪', 'Active venues', fmt(active), 'of ' + fmt(total) + ' total', 'linear-gradient(90deg, #10b981, #34d399)', 'background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.35)') +
                plKpiCard('📦', 'Orders today', fmt(ordersToday), 'Platform-wide', 'linear-gradient(90deg, #8C71F6, #a78bfa)', 'background:rgba(140,113,246,0.15);border:1px solid rgba(140,113,246,0.35)') +
                plKpiCard('📅', 'Orders month', fmt(ordersMonth), 'Last ' + days + ' days', 'linear-gradient(90deg, #6366f1, #818cf8)', 'background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.35)') +
                plKpiCard('⭐', 'Feedback', fmt(feedbackCount), (avgRating || 0) + ' ★ avg', 'linear-gradient(90deg, #f59e0b, #fbbf24)', 'background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.35)');
        }

        buildPulseVenueRing();

        const orderStats = document.getElementById('pl-order-stats');
        if (orderStats) {
            const todayShare = ordersMonth > 0 ? fmtPct(ordersToday, ordersMonth) : '0%';
            orderStats.innerHTML =
                plStatPill('Today', fmt(ordersToday), 'text-violet-400') +
                plStatPill('This month', fmt(ordersMonth), 'text-white') +
                plStatPill('Today share', todayShare, 'text-emerald-400');
        }

        const orderBars = document.getElementById('pl-order-bars');
        if (orderBars) {
            const rows = [
                { key: 'today', label: 'Orders today', icon: '🔥', value: ordersToday, color: '#8C71F6' },
                { key: 'month', label: 'Orders this month', icon: '📅', value: ordersMonth, color: '#6366f1' },
            ];
            const maxOrders = Math.max(...rows.map(r => r.value || 0), 1);
            orderBars.innerHTML = rows.map((r, i) => {
                const w = pctHeight(r.value || 0, maxOrders, 8, 2);
                return '<div class="pl-order-row pl-animate-in" style="animation-delay:' + (i * 0.05) + 's">' +
                    '<span class="text-sm font-bold text-white flex items-center gap-2"><span>' + r.icon + '</span>' + esc(r.label) + '</span>' +
                    '<div class="pl-order-bar-track"><div class="pl-order-bar-fill" style="width:' + w + '%;background:linear-gradient(90deg,' + r.color + 'cc,' + r.color + ')"></div></div>' +
                    '<span class="text-sm font-bold text-white tabular-nums text-right">' + fmt(r.value) + '</span>' +
                    '<span class="text-xs font-bold text-violet-400/80 tabular-nums text-right">' + (r.key === 'today' && ordersMonth > 0 ? fmtPct(ordersToday, ordersMonth) : '—') + '</span></div>';
            }).join('');
        }

        const activities = [
            { key: 'bot', label: 'Bot events', icon: '💬', value: botEvents, color: '#6366f1', sub: 'WhatsApp activity' },
            { key: 'qr', label: 'QR scans', icon: '📱', value: qrScans, color: '#06b6d4', sub: 'All entry types' },
            { key: 'pay', label: 'Payments', icon: '💳', value: payCount, color: '#ec4899', sub: money(payTotal) },
            { key: 'fb', label: 'Feedback', icon: '⭐', value: feedbackCount, color: '#f59e0b', sub: (avgRating || 0) + ' ★ avg' },
        ];
        const activityTotal = activities.reduce((s, a) => s + (a.value || 0), 0);
        const topActivity = [...activities].sort((a, b) => (b.value || 0) - (a.value || 0)).find(a => (a.value || 0) > 0);

        const activityLabel = document.getElementById('pl-activity-total-label');
        if (activityLabel) activityLabel.textContent = fmt(activityTotal) + ' total signals';

        const activityCards = document.getElementById('pl-activity-cards');
        if (activityCards) {
            activityCards.innerHTML = activities.map((a, i) => {
                const isTop = topActivity && a.key === topActivity.key && (a.value || 0) > 0;
                return '<div class="pl-activity-card pl-animate-in" style="animation-delay:' + (i * 0.05) + 's;border-top:3px solid ' + a.color + '">' +
                    '<div class="flex items-center justify-between mb-2">' +
                    '<span class="text-2xl">' + a.icon + '</span>' +
                    (isTop ? '<span class="text-[9px] font-black text-fin-primary uppercase">Top signal</span>' : '') + '</div>' +
                    '<p class="text-sm font-bold text-white">' + esc(a.label) + '</p>' +
                    '<p class="text-2xl font-black text-white tabular-nums mt-2">' + fmt(a.value) + '</p>' +
                    '<p class="text-[10px] text-white/40 mt-1">' + esc(a.sub) + '</p></div>';
            }).join('');
        }

        const activityBars = document.getElementById('pl-activity-bars');
        if (activityBars) {
            const maxAct = Math.max(...activities.map(a => a.value || 0), 1);
            activityBars.innerHTML = activityTotal === 0
                ? '<p class="text-sm text-white/40">No platform activity recorded yet for this period</p>'
                : activities.map((a, i) => {
                    const w = pctHeight(a.value || 0, maxAct, 8, 2);
                    return '<div class="pl-activity-row pl-animate-in" style="animation-delay:' + (i * 0.04) + 's">' +
                        '<span class="text-sm font-semibold text-white/85 flex items-center gap-2">' +
                        '<span>' + a.icon + '</span>' + esc(a.label) + '</span>' +
                        '<div class="pl-activity-bar-track"><div class="pl-activity-bar-fill" style="width:' + w + '%;background:' + a.color + '"></div></div>' +
                        '<span class="text-sm font-bold text-fin-primary tabular-nums text-right">' + fmtPct(a.value || 0, activityTotal) + '</span>' +
                        '<span class="text-sm font-bold text-white tabular-nums text-right">' + fmt(a.value) + '</span></div>';
                }).join('');
        }

        const tableEl = document.getElementById('pl-data-table');
        if (tableEl) {
            tableEl.innerHTML =
                '<table class="pl-data-table min-w-[480px]"><thead><tr>' +
                '<th>Metric</th><th>Count</th><th>Detail</th></tr></thead><tbody>' +
                '<tr class="pl-animate-in"><td class="font-semibold">🏪 Active venues</td><td class="tabular-nums font-bold text-white">' + fmt(active) + '</td><td class="text-white/60">' + fmt(total) + ' total · ' + activePct + '% active</td></tr>' +
                '<tr class="pl-animate-in"><td class="font-semibold">📦 Orders today</td><td class="tabular-nums font-bold text-white">' + fmt(ordersToday) + '</td><td class="text-white/60">Platform-wide</td></tr>' +
                '<tr class="pl-animate-in"><td class="font-semibold">📅 Orders month</td><td class="tabular-nums font-bold text-white">' + fmt(ordersMonth) + '</td><td class="text-white/60">Last ' + days + ' days</td></tr>' +
                '<tr class="pl-animate-in"><td class="font-semibold">💬 Bot events</td><td class="tabular-nums font-bold text-white">' + fmt(botEvents) + '</td><td class="text-white/60">WhatsApp engagement</td></tr>' +
                '<tr class="pl-animate-in"><td class="font-semibold">📱 QR scans</td><td class="tabular-nums font-bold text-white">' + fmt(qrScans) + '</td><td class="text-white/60">All entry types</td></tr>' +
                '<tr class="pl-animate-in"><td class="font-semibold">💳 Payments</td><td class="tabular-nums font-bold text-white">' + fmt(payCount) + '</td><td class="text-emerald-400 font-bold">' + money(payTotal) + '</td></tr>' +
                '<tr class="pl-animate-in"><td class="font-semibold">⭐ Feedback</td><td class="tabular-nums font-bold text-white">' + fmt(feedbackCount) + '</td><td class="text-amber-400 font-bold">' + (avgRating || 0) + ' ★ avg</td></tr>' +
                '</tbody></table>';
        }

        const insightsEl = document.getElementById('pl-insights');
        if (insightsEl) {
            const cards = [];
            if (total > 0) {
                cards.push({
                    type: activePct >= 70 ? 'positive' : 'warning',
                    icon: '🏪',
                    title: 'Venue health',
                    text: activePct + '% of venues are active (' + fmt(active) + ' of ' + fmt(total) + ').',
                });
            }
            if (ordersToday > 0) {
                cards.push({ type: 'positive', icon: '🔥', title: 'Today is active', text: fmt(ordersToday) + ' orders so far today across the platform.' });
            } else {
                cards.push({ type: 'info', icon: '🌙', title: 'Quiet start', text: 'No orders yet today — activity may pick up during peak hours.' });
            }
            if (topActivity && topActivity.value > 0) {
                cards.push({
                    type: 'info', icon: topActivity.icon,
                    title: 'Strongest signal',
                    text: topActivity.label + ' leads with ' + fmt(topActivity.value) + ' (' + fmtPct(topActivity.value, activityTotal) + ' of activity mix).',
                });
            } else if (cards.length < 3) {
                cards.push({ type: 'info', icon: '📡', title: 'Getting started', text: 'Platform pulse fills in as venues, customers, and bot usage grow.' });
            }
            insightsEl.innerHTML = cards.slice(0, 3).map(c =>
                '<div class="pl-insight-card pl-animate-in is-' + c.type + '">' +
                '<p class="text-lg mb-2">' + c.icon + '</p>' +
                '<p class="text-[10px] font-black text-white/45 uppercase tracking-wider">' + esc(c.title) + '</p>' +
                '<p class="text-sm font-semibold text-white/90 mt-1 leading-relaxed">' + esc(c.text) + '</p></div>'
            ).join('');
        }
    };

    let loading = false;
    const loadSection = async () => {
        if (loading) return;
        loading = true;
        refreshBtn?.setAttribute('disabled', 'disabled');
        try {
            if (activeSection === 'platform') {
                renderSnapshot(await fetchJson(urls.snapshot));
            } else if (activeSection === 'whatsapp') {
                renderWhatsapp(await fetchJson(urls.whatsapp));
            } else if (activeSection === 'qr-entry') {
                renderQr(await fetchJson(urls.qr));
            } else if (activeSection === 'journey') {
                renderFunnel(await fetchJson(urls.funnel));
            } else if (activeSection === 'feedback') {
                renderFeedback(await fetchJson(urls.feedback));
            } else if (activeSection === 'tips-payments') {
                renderPayments(await fetchJson(urls.payments));
            } else if (activeSection === 'language') {
                renderLanguage(await fetchJson(urls.language));
            } else if (activeSection === 'venues') {
                renderPulse(await fetchJson(urls.pulse));
            }

            if (lastUpdatedEl) {
                lastUpdatedEl.textContent = 'Updated ' + new Date().toLocaleTimeString();
            }
        } catch (e) {
            console.error(e);
        } finally {
            loading = false;
            refreshBtn?.removeAttribute('disabled');
        }
    };

    daysEl?.addEventListener('change', loadSection);
    refreshBtn?.addEventListener('click', loadSection);

    loadSection();
    setInterval(loadSection, 60000);
})();
</script>
