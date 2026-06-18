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
    };

    const daysEl = document.getElementById('filter-days');
    const restaurantEl = document.getElementById('filter-restaurant');
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
        const rid = restaurantEl?.value;
        if (rid) params.set('restaurant_id', rid);
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

    const renderSnapshot = (payload) => {
        const s = payload.snapshot || {};
        const orders = s.orders || {};
        const restaurants = s.restaurants || {};

        document.getElementById('snapshot-kpis').innerHTML =
            kpiCard('Orders today', fmt(orders.today || 0), 'Completed & active', 'text-emerald-400') +
            kpiCard('This week', fmt(orders.week || 0), 'Orders', 'text-white') +
            kpiCard('This month', fmt(orders.month || 0), 'Orders', 'text-white') +
            kpiCard('Restaurants', fmt(restaurants.total || 0), (restaurants.active || 0) + ' active', 'text-fin-primary');

        const trend = s.revenue_trend || [];
        buildBarChart('chart-revenue-trend', trend.map(d => ({ ...d, label: d.label || d.day })), 'revenue', 'label', 'bg-gradient-to-t from-violet-900 to-fin-primary');

        buildDonut('chart-restaurant-split', restaurants.segments || [], restaurants.active || 0, 'active');

        const top = s.top_restaurants || [];
        const tableRoot = document.getElementById('table-top-restaurants');
        if (tableRoot) {
            if (top.length === 0) {
                tableRoot.innerHTML = '<p class="text-sm text-white/40">No revenue data yet</p>';
            } else {
                tableRoot.innerHTML = '<table class="w-full text-sm"><thead><tr class="text-left text-[10px] text-white/40 uppercase">' +
                    '<th class="pb-3 pr-4">Restaurant</th><th class="pb-3 pr-4">Revenue</th><th class="pb-3">Orders</th></tr></thead><tbody>' +
                    top.map(r => '<tr class="border-t border-white/5"><td class="py-3 pr-4 font-semibold text-white">' + esc(r.name) +
                    '</td><td class="py-3 pr-4 text-fin-lavender tabular-nums">' + fmtMoney(r.revenue) +
                    '</td><td class="py-3 tabular-nums text-white/70">' + fmt(r.orders) + '</td></tr>').join('') +
                    '</tbody></table>';
            }
        }
    };

    const renderWhatsapp = (payload) => {
        const w = payload.whatsapp_engagement || {};
        const heroBot = document.getElementById('hero-bot-events');
        if (heroBot) heroBot.textContent = fmt(w.total_events || 0);

        buildDonut('chart-wa-options', w.option_usage || [], w.total_events || 0, 'events');
        buildBarChart('chart-wa-trend', w.daily_trend || [], 'count', 'label', 'bg-gradient-to-t from-emerald-700 to-emerald-400');
    };

    const renderQr = (payload) => {
        const q = payload.qr_entry_points || {};
        const heroQr = document.getElementById('hero-qr-scans');
        if (heroQr) heroQr.textContent = fmt(q.total_scans || 0);
        buildDonut('chart-qr-split', q.split || [], q.total_scans || 0, 'scans');

        const rows = q.per_restaurant || [];
        const root = document.getElementById('table-qr-per-restaurant');
        if (!root) return;
        if (rows.length === 0) {
            root.innerHTML = '<p class="text-sm text-white/40">No QR scans recorded yet</p>';
            return;
        }
        root.innerHTML = '<table class="w-full text-sm"><thead><tr class="text-left text-[10px] text-white/40 uppercase">' +
            '<th class="pb-3 pr-3">Restaurant</th><th class="pb-3 pr-3">Waiter</th><th class="pb-3 pr-3">Table</th><th class="pb-3 pr-3">Tag</th><th class="pb-3">Insight</th></tr></thead><tbody>' +
            rows.map(r => '<tr class="border-t border-white/5"><td class="py-3 pr-3 font-semibold text-white">' + esc(r.name) +
            '</td><td class="py-3 pr-3 tabular-nums">' + r.waiter + '</td><td class="py-3 pr-3 tabular-nums">' + r.table +
            '</td><td class="py-3 pr-3 tabular-nums">' + r.restaurant + '</td><td class="py-3 text-xs text-cyan-400/90">' + esc(r.insight) + '</td></tr>').join('') +
            '</tbody></table>';
    };

    const funnelStepIcon = (key) => ({
        qr_scan: '📱',
        bot_home: '🏠',
        view_menu: '📋',
        add_to_cart: '🛒',
        confirm_order: '✅',
        pay_bill: '💳',
        payment_success: '🎉',
    }[key] || '•');

    const renderFunnel = (payload) => {
        const f = payload.customer_journey || {};
        const steps = f.steps || [];
        const inner = document.getElementById('chart-funnel-inner');
        const kpiRoot = document.getElementById('funnel-kpis');
        const banner = document.getElementById('funnel-dropoff-banner');
        const overallBadge = document.getElementById('funnel-overall-badge');
        const summaryRoot = document.getElementById('funnel-summary-stats');

        if (!inner) return;

        if (steps.length === 0) {
            inner.innerHTML = '<p class="text-sm text-white/40 py-8 text-center w-full">No funnel data yet — scans and orders will appear here</p>';
            if (kpiRoot) kpiRoot.innerHTML = '';
            if (banner) banner.classList.add('hidden');
            if (overallBadge) overallBadge.textContent = 'No data';
            if (summaryRoot) summaryRoot.innerHTML = '<p class="text-xs text-white/40">Waiting for customer activity</p>';
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

        if (kpiRoot) {
            kpiRoot.innerHTML =
                kpiCard('Started journey', fmt(startCount), 'QR scans in period', 'text-violet-300') +
                kpiCard('Completed payment', fmt(endCount), 'Reached success step', 'text-emerald-400') +
                kpiCard('End-to-end conversion', overallPct + '%', totalDrop > 0 ? fmt(totalDrop) + ' dropped overall' : 'Full retention', overallPct >= 50 ? 'text-emerald-400' : 'text-amber-400');
        }

        if (overallBadge) {
            overallBadge.textContent = overallPct + '% reach payment';
            overallBadge.className = 'text-[10px] font-bold uppercase tracking-wider px-3 py-1.5 rounded-full border ' +
                (overallPct >= 40 ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-amber-500/10 border-amber-500/30 text-amber-300');
        }

        let html = '';
        steps.forEach((step, i) => {
            const retention = startCount > 0 ? Math.round(((step.count || 0) / startCount) * 1000) / 10 : 0;
            const isLast = i === steps.length - 1;
            const isWeakest = step.key === weakestKey || step.label === f.biggest_drop_off?.step;
            const cardClass = isLast && (step.count || 0) > 0
                ? 'journey-step-card is-success'
                : (isWeakest && (step.drop_off || 0) > 0 ? 'journey-step-card is-weakest' : 'journey-step-card');

            const convBadge = i === 0
                ? '<span class="text-[9px] font-bold text-violet-300/90 bg-violet-500/15 px-2 py-0.5 rounded-full">Start</span>'
                : '<span class="text-[9px] font-bold text-white/50 bg-white/5 px-2 py-0.5 rounded-full">' + (step.conversion_pct ?? 0) + '% prev</span>';

            html += '<div class="journey-step">' +
                '<div class="' + cardClass + '">' +
                '<div class="flex items-center justify-between gap-2 mb-2">' +
                '<span class="w-6 h-6 rounded-lg bg-white/10 flex items-center justify-center text-sm">' + funnelStepIcon(step.key) + '</span>' +
                '<span class="text-[9px] font-black text-white/30 tabular-nums">#' + (i + 1) + '</span></div>' +
                '<p class="text-[11px] font-bold text-white leading-tight min-h-[2rem]">' + esc(step.label) + '</p>' +
                '<p class="text-2xl font-black text-white tabular-nums mt-1">' + fmt(step.count || 0) + '</p>' +
                '<div class="flex items-center justify-between gap-1 mt-2">' + convBadge +
                '<span class="text-[9px] text-white/40 tabular-nums">' + retention + '% total</span></div>' +
                '<div class="journey-retention-track"><div class="journey-retention-fill" style="width:' + Math.max(retention, step.count > 0 ? 4 : 0) + '%"></div></div>' +
                '</div></div>';

            if (!isLast) {
                const nextStep = steps[i + 1];
                const drop = nextStep?.drop_off ?? 0;
                const dropClass = drop > 0 ? 'journey-drop-badge' : 'journey-drop-badge is-none';
                const dropLabel = drop > 0 ? '−' + fmt(drop) : '✓';
                html += '<div class="journey-connector" aria-hidden="true">' +
                    '<div class="journey-connector-line"></div>' +
                    '<span class="' + dropClass + '" title="Customers lost before next step">' + dropLabel + '</span>' +
                    '<div class="journey-connector-line"></div></div>';
            }
        });

        inner.innerHTML = html;

        if (banner) {
            if (f.biggest_drop_off && f.biggest_drop_off.drop_off > 0) {
                banner.className = 'mt-5 rounded-xl border px-4 py-3 text-sm insight-card-warning border';
                banner.innerHTML = '<p class="font-semibold text-amber-200">⚡ Biggest drop-off: <span class="text-white">' + esc(f.biggest_drop_off.step) + '</span></p>' +
                    '<p class="text-xs text-white/55 mt-1">' + fmt(f.biggest_drop_off.drop_off) + ' customers did not continue to the next step — review menu, ordering flow, or payment options here.</p>';
                banner.classList.remove('hidden');
            } else {
                banner.className = 'mt-5 rounded-xl border px-4 py-3 text-sm insight-card-positive border';
                banner.innerHTML = '<p class="font-semibold text-emerald-200">✓ Healthy funnel</p>' +
                    '<p class="text-xs text-white/55 mt-1">No major drop-offs between steps in this period.</p>';
                banner.classList.remove('hidden');
            }
        }

        if (summaryRoot) {
            const payMethods = f.payment_methods || [];
            const payTotal = payMethods.reduce((s, m) => s + (m.value || 0), 0);
            summaryRoot.innerHTML =
                '<div class="flex justify-between text-sm"><span class="text-white/50">Steps tracked</span><span class="font-bold text-white tabular-nums">' + steps.length + '</span></div>' +
                '<div class="flex justify-between text-sm"><span class="text-white/50">Started → Paid</span><span class="font-bold text-white tabular-nums">' + fmt(startCount) + ' → ' + fmt(endCount) + '</span></div>' +
                '<div class="flex justify-between text-sm"><span class="text-white/50">Payments recorded</span><span class="font-bold text-white tabular-nums">' + fmt(payTotal) + '</span></div>';

            buildDonut('chart-funnel-payments', payMethods, payTotal || '0', 'paid');
        } else {
            buildDonut('chart-funnel-payments', f.payment_methods || [], endCount || '0', 'paid');
        }
    };

    const renderFeedback = (payload) => {
        const f = payload.feedback_overview || {};
        const summary = f.summary || {};
        const heroRating = document.getElementById('hero-avg-rating');
        if (heroRating) heroRating.textContent = (summary.avg_rating || 0) + '★';

        const kpiRoot = document.getElementById('feedback-kpis');
        if (kpiRoot) {
            kpiRoot.innerHTML =
                kpiCard('Total reviews', fmt(summary.total_reviews || 0), 'In selected period', 'text-amber-400') +
                kpiCard('Average rating', (summary.avg_rating || 0) + ' ★', 'Platform-wide', 'text-white');
        }

        const ratingBars = document.getElementById('chart-rating-bars');
        if (ratingBars) {
            const dist = f.rating_distribution || [];
            const maxStars = Math.max(...dist.map(d => d.count || 0), 1);
            ratingBars.innerHTML = dist.map(d => {
                const w = pctHeight(d.count || 0, maxStars, 4, 2);
                return '<div class="flex items-center gap-3"><span class="text-xs text-white/50 w-8">' + d.stars + '★</span>' +
                    '<div class="flex-1 h-6 bg-white/5 rounded-lg overflow-hidden"><div class="h-full bg-gradient-to-r from-amber-600 to-amber-400 rounded-lg" style="width:' + w + '%"></div></div>' +
                    '<span class="text-xs font-bold text-white tabular-nums w-8 text-right">' + d.count + '</span></div>';
            }).join('');
        }

        const alertsEl = document.getElementById('feedback-alerts');
        if (alertsEl) {
            const alerts = f.low_rating_alerts || [];
            alertsEl.innerHTML = alerts.length === 0
                ? '<p class="text-sm text-emerald-400/90">✓ No venues below 3.5★ with 3+ reviews</p>'
                : alerts.map(a => '<div class="flex items-center justify-between py-2 border-b border-white/5 last:border-0">' +
                    '<span class="font-semibold text-white">' + esc(a.name) + '</span>' +
                    '<span class="text-rose-400 font-bold tabular-nums">' + a.avg_rating + '★ <span class="text-white/40 font-normal">(' + a.review_count + ')</span></span></div>').join('');
        }

        const commentsEl = document.getElementById('feedback-comments');
        if (commentsEl) {
            const comments = f.recent_comments || [];
            commentsEl.innerHTML = comments.length === 0
                ? '<p class="text-sm text-white/40">No comments yet</p>'
                : comments.map(c => '<div class="p-3 rounded-xl bg-white/5 border border-white/5">' +
                    '<div class="flex items-center justify-between gap-2 mb-1">' +
                    '<span class="text-amber-400 font-bold">' + '★'.repeat(c.rating || 0) + '</span>' +
                    '<span class="text-[10px] text-white/35">' + esc(c.restaurant_name || '') + '</span></div>' +
                    '<p class="text-sm text-white/80">' + esc(c.comment) + '</p></div>').join('');
        }
    };

    const renderPayments = (payload) => {
        const p = payload.tips_and_payments || {};
        const tips = p.tips || {};
        const sym = payload.currency_symbol || currencySymbol;

        document.getElementById('tips-kpis').innerHTML =
            kpiCard('Tips collected', sym + ' ' + fmt(tips.total_amount || 0), tips.count + ' tips', 'text-pink-400') +
            kpiCard('Avg tip', sym + ' ' + fmt(tips.avg_amount || 0), 'Per tip', 'text-white') +
            kpiCard('Tip count', fmt(tips.count || 0), 'In period', 'text-white');

        buildDonut('chart-payment-methods', (p.payment_methods || []).map(m => ({ ...m, label: m.label, value: m.value })), 'pay', 'txns');
        buildDonut('chart-payment-purpose', (p.payment_purpose || []).map(m => ({ ...m, label: m.label, value: m.value })), 'type', 'split');

        const waiters = p.top_tipped_waiters || [];
        document.getElementById('table-top-tipped').innerHTML = waiters.length === 0
            ? '<p class="text-sm text-white/40">No tips in this period</p>'
            : waiters.map((w, i) => '<div class="flex items-center justify-between py-2 border-b border-white/5">' +
                '<span class="text-sm text-white"><span class="text-white/30 mr-2">#' + (i + 1) + '</span>' + esc(w.name) +
                (w.restaurant_name ? ' <span class="text-white/35 text-xs">· ' + esc(w.restaurant_name) + '</span>' : '') + '</span>' +
                '<span class="font-bold text-pink-400 tabular-nums">' + sym + ' ' + fmt(w.total_tips) + '</span></div>').join('');
    };

    const renderLanguage = (payload) => {
        const l = payload.language_and_behavior || {};
        const langs = l.language_split || [];
        const totalLang = langs.reduce((s, x) => s + (x.value || 0), 0);
        buildDonut('chart-language', langs, totalLang, 'sessions');

        const peak = l.peak_hours || {};
        const hours = peak.events || [];
        buildBarChart('chart-peak-hours', hours.map(h => ({ ...h, label: h.label || (h.hour + 'h') })), 'count', 'label', 'bg-gradient-to-t from-indigo-800 to-indigo-400');
    };

    const renderVenueComparison = (data) => {
        const top = data.snapshot?.snapshot?.top_restaurants || [];
        const ratings = data.feedback?.feedback_overview?.avg_rating_by_restaurant || [];
        const qrRows = data.qr?.qr_entry_points?.per_restaurant || [];
        const langRows = data.language?.language_and_behavior?.per_restaurant || [];

        const ratingMap = Object.fromEntries(ratings.map(r => [r.id, r]));
        const qrMap = Object.fromEntries(qrRows.map(r => [r.id, r]));
        const langMap = Object.fromEntries(langRows.map(r => [r.id, r]));

        const ids = new Set([
            ...top.map(r => r.id),
            ...ratings.map(r => r.id),
            ...qrRows.map(r => r.id),
        ]);

        const rows = [...ids].map(id => {
            const t = top.find(r => r.id === id) || {};
            const rating = ratingMap[id];
            const qr = qrMap[id];
            const lang = langMap[id];
            return {
                id,
                name: t.name || rating?.name || qr?.name || 'Restaurant #' + id,
                revenue: t.revenue || 0,
                orders: t.orders || 0,
                avg_rating: rating?.avg_rating ?? '—',
                reviews: rating?.review_count ?? 0,
                qr_total: qr?.total ?? 0,
                qr_insight: qr?.insight ?? '—',
                language: lang?.preferred_label ?? '—',
            };
        }).sort((a, b) => b.revenue - a.revenue);

        const root = document.getElementById('table-venue-comparison');
        if (!root) return;
        if (rows.length === 0) {
            root.innerHTML = '<p class="text-sm text-white/40">No venue data yet</p>';
            return;
        }

        root.innerHTML = '<table class="w-full text-sm min-w-[720px]"><thead><tr class="text-left text-[10px] text-white/40 uppercase">' +
            '<th class="pb-3 pr-4">Venue</th><th class="pb-3 pr-4">Revenue</th><th class="pb-3 pr-4">Orders</th>' +
            '<th class="pb-3 pr-4">Rating</th><th class="pb-3 pr-4">QR scans</th><th class="pb-3 pr-4">QR insight</th><th class="pb-3">Language</th></tr></thead><tbody>' +
            rows.map(r => '<tr class="border-t border-white/5 hover:bg-white/[0.02]">' +
            '<td class="py-3 pr-4 font-semibold text-white">' + esc(r.name) + '</td>' +
            '<td class="py-3 pr-4 text-fin-lavender tabular-nums">' + fmtMoney(r.revenue) + '</td>' +
            '<td class="py-3 pr-4 tabular-nums text-white/70">' + fmt(r.orders) + '</td>' +
            '<td class="py-3 pr-4"><span class="text-amber-400 font-bold">' + r.avg_rating + '</span>' +
            (r.reviews ? ' <span class="text-white/35 text-xs">(' + r.reviews + ')</span>' : '') + '</td>' +
            '<td class="py-3 pr-4 tabular-nums text-cyan-400">' + fmt(r.qr_total) + '</td>' +
            '<td class="py-3 pr-4 text-xs text-white/55 max-w-[180px]">' + esc(r.qr_insight) + '</td>' +
            '<td class="py-3 text-xs text-indigo-300">' + esc(r.language) + '</td></tr>').join('') +
            '</tbody></table>';
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
                const [snapshot, feedback, qr, language] = await Promise.all([
                    fetchJson(urls.snapshot),
                    fetchJson(urls.feedback),
                    fetchJson(urls.qr),
                    fetchJson(urls.language),
                ]);
                renderVenueComparison({ snapshot, feedback, qr, language });
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
    restaurantEl?.addEventListener('change', loadSection);
    refreshBtn?.addEventListener('click', loadSection);

    loadSection();
    setInterval(loadSection, 60000);
})();
</script>
