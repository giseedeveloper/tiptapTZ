<style>
    /* ── Analytics grid (self-contained — production CSS may lack Tailwind xl: utilities) ── */
    .tiptap-analysis-content .grid {
        display: grid;
        width: 100%;
    }
    .tiptap-analysis-content .grid > [class*="col-span"] {
        min-width: 0;
    }
    @media (min-width: 1280px) {
        .tiptap-analysis-content .grid[class*="xl:grid-cols-12"] {
            grid-template-columns: repeat(12, minmax(0, 1fr));
        }
        .tiptap-analysis-content .grid[class*="xl:grid-cols-4"]:not([class*="xl:grid-cols-12"]) {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .tiptap-analysis-content .grid > [class*="xl:col-span-8"] {
            grid-column: span 8 / span 8;
        }
        .tiptap-analysis-content .grid > [class*="xl:col-span-7"] {
            grid-column: span 7 / span 7;
        }
        .tiptap-analysis-content .grid > [class*="xl:col-span-5"] {
            grid-column: span 5 / span 5;
        }
        .tiptap-analysis-content .grid > [class*="xl:col-span-4"] {
            grid-column: span 4 / span 4;
        }
    }
    .platform-panel, .wa-panel, .qr-panel, .fb-panel, .tp-panel, .lg-panel, .pl-panel, .jn-panel {
        min-width: 0;
    }
    .wa-area-chart-wrap, .qr-area-chart-wrap, .lg-peak-chart-wrap {
        width: 100%;
        min-width: 0;
        overflow: hidden;
    }
    .wa-trend-chart, .lg-hour-chart {
        width: 100%;
        min-width: 0;
    }
    .platform-revenue-chart {
        width: 100%;
        min-width: 0;
    }
    .chart-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        width: 100%;
        min-height: 10rem;
        padding: 2rem 1.25rem;
        border-radius: 1rem;
        border: 1px dashed rgba(255, 255, 255, 0.12);
        background: rgba(255, 255, 255, 0.02);
        text-align: center;
    }
    .chart-zero-hint {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.28);
        pointer-events: none;
        white-space: nowrap;
    }
    .wa-trend-chart__plot.is-zero-data svg path:first-of-type,
    .wa-trend-chart__plot.is-zero-data .wa-area-line,
    .wa-trend-chart__plot.is-zero-data .qr-area-line {
        opacity: 0.35;
    }
    .spotlight-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-height: 8rem;
        padding: 1.25rem 1rem;
        border-radius: 1rem;
        border: 1px dashed rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.02);
    }
    .spotlight-placeholder__icon {
        font-size: 2rem;
        opacity: 0.35;
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    .chart-empty-state__icon {
        font-size: 1.75rem;
        opacity: 0.55;
        line-height: 1;
    }
    .chart-empty-state__text {
        font-size: 0.8125rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.45);
        max-width: 18rem;
        line-height: 1.45;
    }
    .wa-top-action-card, .qr-top-entry-card, .lg-top-card, .tp-top-card {
        min-height: 7rem;
        overflow: hidden;
    }
    .wa-panel--spotlight #chart-wa-options,
    .qr-panel--spotlight #chart-qr-split,
    .lg-panel--spotlight #chart-language,
    .tp-panel--spotlight #chart-payment-methods {
        width: 100%;
        margin-top: auto;
        padding-top: 0.75rem;
    }
    .fb-rating-gauge-ring {
        margin-left: auto;
        margin-right: auto;
    }

    /* ── Analytics hub (index) ── */
    .hub-hero {
        background: linear-gradient(135deg, rgba(91, 63, 214, 0.24) 0%, rgba(236, 72, 153, 0.1) 35%, rgba(6, 182, 212, 0.06) 60%, rgba(10, 8, 20, 0.98) 100%);
        border: 1px solid rgba(140, 113, 246, 0.35);
        box-shadow: 0 28px 70px -32px rgba(91, 63, 214, 0.55);
    }
    .hub-hero-glow { position: absolute; border-radius: 9999px; filter: blur(70px); pointer-events: none; }
    .hub-hero-glow--violet { width: 18rem; height: 18rem; top: -6rem; right: 5%; background: rgba(140, 113, 246, 0.4); }
    .hub-hero-glow--pink { width: 12rem; height: 12rem; bottom: -4rem; left: 8%; background: rgba(236, 72, 153, 0.22); }
    .hub-hero-glow--cyan { width: 10rem; height: 10rem; top: 40%; right: 35%; background: rgba(6, 182, 212, 0.12); }
    .hub-live-dot {
        width: 8px; height: 8px; border-radius: 9999px; background: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
        animation: platform-pulse-dot 2s ease infinite;
    }
    .hub-hero-title { text-shadow: 0 0 48px rgba(140, 113, 246, 0.25); }
    .hub-badge {
        display: inline-flex; align-items: center; gap: 0.3rem;
        padding: 0.35rem 0.75rem; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.8);
        background: rgba(255,255,255,0.05); border: 1px solid rgba(140,113,246,0.25);
    }
    .hub-stat-chip {
        padding: 0.85rem 1rem; border-radius: 1rem;
        background: rgba(255,255,255,0.04);
        border: 1px solid rgba(255,255,255,0.1);
        backdrop-filter: blur(8px);
        transition: border-color 0.2s ease, transform 0.2s ease;
    }
    .hub-stat-chip:hover { border-color: rgba(140,113,246,0.35); transform: translateY(-2px); }
    .hub-stat-chip__label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); }
    .hub-stat-chip__value { font-size: 1.35rem; font-weight: 900; color: #fff; margin-top: 0.35rem; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .hub-stat-chip__sub { font-size: 10px; color: rgba(255,255,255,0.38); margin-top: 0.25rem; font-weight: 600; }
    .hub-quicknav__track {
        display: flex; flex-wrap: wrap; gap: 0.5rem;
        padding-bottom: 0.15rem;
    }
    @media (max-width: 640px) {
        .hub-quicknav__track { flex-wrap: nowrap; overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        .hub-quicknav__track::-webkit-scrollbar { display: none; }
    }
    .hub-pill {
        display: inline-flex; align-items: center; gap: 0.4rem;
        padding: 0.5rem 0.95rem; border-radius: 9999px;
        font-size: 11px; font-weight: 800; color: rgba(255,255,255,0.65);
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);
        transition: all 0.22s ease; white-space: nowrap; flex-shrink: 0;
    }
    .hub-pill__icon { font-size: 0.95rem; line-height: 1; }
    .hub-pill:hover { color: #fff; transform: translateY(-2px); }
    .hub-pill--platform:hover { background: rgba(139,92,246,0.2); border-color: rgba(139,92,246,0.45); box-shadow: 0 8px 24px -8px rgba(139,92,246,0.4); }
    .hub-pill--whatsapp:hover { background: rgba(16,185,129,0.18); border-color: rgba(16,185,129,0.4); box-shadow: 0 8px 24px -8px rgba(16,185,129,0.35); }
    .hub-pill--qr:hover { background: rgba(6,182,212,0.18); border-color: rgba(6,182,212,0.4); box-shadow: 0 8px 24px -8px rgba(6,182,212,0.35); }
    .hub-pill--journey:hover { background: rgba(217,70,239,0.18); border-color: rgba(217,70,239,0.4); box-shadow: 0 8px 24px -8px rgba(217,70,239,0.35); }
    .hub-pill--feedback:hover { background: rgba(245,158,11,0.18); border-color: rgba(245,158,11,0.4); box-shadow: 0 8px 24px -8px rgba(245,158,11,0.35); }
    .hub-pill--tips:hover { background: rgba(236,72,153,0.18); border-color: rgba(236,72,153,0.4); box-shadow: 0 8px 24px -8px rgba(236,72,153,0.35); }
    .hub-pill--language:hover { background: rgba(99,102,241,0.18); border-color: rgba(99,102,241,0.4); box-shadow: 0 8px 24px -8px rgba(99,102,241,0.35); }
    .hub-pill--pulse:hover { background: rgba(140,113,246,0.22); border-color: rgba(140,113,246,0.45); box-shadow: 0 8px 24px -8px rgba(140,113,246,0.4); }
    .hub-card {
        position: relative; overflow: hidden; display: flex; flex-direction: column;
        padding: 1.35rem 1.4rem 1.2rem; border-radius: 1.35rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.07) 0%, rgba(14, 12, 22, 0.94) 55%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.28s cubic-bezier(0.34, 1.2, 0.64, 1), border-color 0.25s ease, box-shadow 0.28s ease;
        min-height: 11.5rem;
    }
    .hub-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--hub-accent, linear-gradient(90deg, #8C71F6, #a78bfa));
        opacity: 0.85;
    }
    .hub-card__glow {
        position: absolute; width: 8rem; height: 8rem; top: -3rem; right: -2rem;
        border-radius: 9999px; filter: blur(40px); opacity: 0;
        background: var(--hub-glow, rgba(140,113,246,0.35));
        transition: opacity 0.3s ease; pointer-events: none;
    }
    .hub-card:hover { transform: translateY(-5px); border-color: var(--hub-border, rgba(140,113,246,0.45)); box-shadow: 0 22px 50px -18px var(--hub-shadow, rgba(140,113,246,0.45)); }
    .hub-card:hover .hub-card__glow { opacity: 1; }
    .hub-card:hover .hub-card__arrow { transform: translateX(4px); opacity: 1; }
    .hub-card:hover .hub-card__icon { transform: scale(1.08) rotate(-3deg); }
    .hub-card--platform { --hub-accent: linear-gradient(90deg, #7c3aed, #a78bfa); --hub-glow: rgba(124,58,237,0.4); --hub-border: rgba(124,58,237,0.45); --hub-shadow: rgba(124,58,237,0.4); }
    .hub-card--whatsapp { --hub-accent: linear-gradient(90deg, #059669, #34d399); --hub-glow: rgba(16,185,129,0.35); --hub-border: rgba(16,185,129,0.42); --hub-shadow: rgba(16,185,129,0.38); }
    .hub-card--qr { --hub-accent: linear-gradient(90deg, #0891b2, #22d3ee); --hub-glow: rgba(6,182,212,0.35); --hub-border: rgba(6,182,212,0.42); --hub-shadow: rgba(6,182,212,0.38); }
    .hub-card--journey { --hub-accent: linear-gradient(90deg, #c026d3, #e879f9); --hub-glow: rgba(192,38,211,0.35); --hub-border: rgba(192,38,211,0.42); --hub-shadow: rgba(192,38,211,0.38); }
    .hub-card--feedback { --hub-accent: linear-gradient(90deg, #d97706, #fbbf24); --hub-glow: rgba(245,158,11,0.35); --hub-border: rgba(245,158,11,0.42); --hub-shadow: rgba(245,158,11,0.38); }
    .hub-card--tips { --hub-accent: linear-gradient(90deg, #db2777, #f472b6); --hub-glow: rgba(236,72,153,0.35); --hub-border: rgba(236,72,153,0.42); --hub-shadow: rgba(236,72,153,0.38); }
    .hub-card--language { --hub-accent: linear-gradient(90deg, #4f46e5, #818cf8); --hub-glow: rgba(99,102,241,0.35); --hub-border: rgba(99,102,241,0.42); --hub-shadow: rgba(99,102,241,0.38); }
    .hub-card--pulse { --hub-accent: linear-gradient(90deg, #8C71F6, #c4b5fd); --hub-glow: rgba(140,113,246,0.4); --hub-border: rgba(140,113,246,0.45); --hub-shadow: rgba(140,113,246,0.42); }
    .hub-card__top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; margin-bottom: 1rem; }
    .hub-card__icon {
        font-size: 1.85rem; line-height: 1;
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.75rem; height: 2.75rem; border-radius: 0.9rem;
        background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
        transition: transform 0.25s ease;
    }
    .hub-card__tag {
        font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em;
        padding: 0.25rem 0.55rem; border-radius: 9999px;
        color: rgba(255,255,255,0.55); background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.08);
    }
    .hub-card__title { font-size: 1.1rem; font-weight: 900; color: #fff; margin-bottom: 0.4rem; }
    .hub-card__desc { font-size: 11px; line-height: 1.55; color: rgba(255,255,255,0.45); flex: 1; }
    .hub-card__footer {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 1.1rem; padding-top: 0.9rem; border-top: 1px solid rgba(255,255,255,0.07);
    }
    .hub-card__cta { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: rgba(255,255,255,0.45); transition: color 0.2s ease; }
    .hub-card:hover .hub-card__cta { color: #fff; }
    .hub-card__arrow { font-size: 1.1rem; font-weight: 900; color: rgba(255,255,255,0.35); transition: transform 0.22s ease, opacity 0.22s ease; opacity: 0.7; }
    .hub-animate-in { animation: platform-fade-up 0.5s cubic-bezier(0.34, 1.2, 0.64, 1) both; }

    .analysis-hero {
        background: linear-gradient(135deg, rgba(140, 113, 246, 0.18) 0%, rgba(236, 72, 153, 0.08) 40%, rgba(15, 10, 30, 0.95) 100%);
        border: 1px solid rgba(140, 113, 246, 0.28);
    }
    .analysis-filter-bar {
        backdrop-filter: blur(12px);
        background: rgba(18, 16, 28, 0.85);
    }
    .analysis-hub-card {
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.06) 0%, rgba(18, 16, 28, 0.92) 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .analysis-hub-card:hover {
        transform: translateY(-3px);
        border-color: rgba(140, 113, 246, 0.45);
        box-shadow: 0 16px 40px -16px rgba(140, 113, 246, 0.45);
    }
    .analysis-hub-pill {
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.12);
        transition: all 0.2s ease;
    }
    .analysis-hub-pill:hover {
        background: linear-gradient(135deg, rgba(140, 113, 246, 0.35), rgba(109, 82, 232, 0.2));
        border-color: rgba(140, 113, 246, 0.5);
        color: #fff;
    }
    .journey-pipeline { display: flex; align-items: stretch; gap: 0; padding: 0.25rem 0; }
    @media (max-width: 1023px) { .journey-pipeline { flex-direction: column; align-items: center; } }
    .journey-step { flex: 1 1 0; min-width: 9.5rem; max-width: 11rem; position: relative; }
    @media (max-width: 1023px) { .journey-step { min-width: 100%; max-width: 22rem; width: 100%; } }
    .journey-step-card {
        background: linear-gradient(160deg, rgba(109, 82, 232, 0.22) 0%, rgba(18, 16, 28, 0.95) 100%);
        border: 1px solid rgba(140, 113, 246, 0.35);
        border-radius: 1rem;
        padding: 1rem 0.85rem;
        height: 100%;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .journey-step-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px -12px rgba(140, 113, 246, 0.45); }
    .journey-step-card.is-weakest { border-color: rgba(245, 158, 11, 0.55); }
    .journey-step-card.is-success { border-color: rgba(16, 185, 129, 0.45); background: linear-gradient(160deg, rgba(16, 185, 129, 0.15) 0%, rgba(18, 16, 28, 0.95) 100%); }
    .journey-retention-track { height: 6px; border-radius: 9999px; background: rgba(255, 255, 255, 0.08); overflow: hidden; margin-top: 0.65rem; }
    .journey-retention-fill { height: 100%; border-radius: 9999px; background: linear-gradient(90deg, #5B3FD6, #8C71F6); transition: width 0.6s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .journey-connector { display: flex; flex-direction: column; align-items: center; justify-content: center; flex: 0 0 auto; width: 2.75rem; padding: 0.5rem 0; }
    @media (max-width: 1023px) { .journey-connector { width: 100%; max-width: 22rem; height: 2.5rem; flex-direction: row; gap: 0.5rem; } }
    .journey-connector-line { flex: 1; width: 2px; min-height: 1.25rem; background: linear-gradient(180deg, rgba(140, 113, 246, 0.5), rgba(140, 113, 246, 0.15)); }
    @media (max-width: 1023px) { .journey-connector-line { width: auto; height: 2px; min-height: 0; flex: 1; background: linear-gradient(90deg, rgba(140, 113, 246, 0.5), rgba(140, 113, 246, 0.15)); } }
    .journey-drop-badge { font-size: 9px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; padding: 0.2rem 0.45rem; border-radius: 9999px; background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.35); color: #fda4af; white-space: nowrap; }
    .journey-drop-badge.is-none { background: rgba(16, 185, 129, 0.1); border-color: rgba(16, 185, 129, 0.3); color: #6ee7b7; }
    @keyframes analysis-shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
    .analysis-skeleton { background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%); background-size: 200% 100%; animation: analysis-shimmer 1.4s infinite; }
    .insight-card-positive { border-color: rgba(16, 185, 129, 0.35); background: rgba(16, 185, 129, 0.08); }
    .insight-card-warning { border-color: rgba(245, 158, 11, 0.35); background: rgba(245, 158, 11, 0.08); }
    .insight-card-alert { border-color: rgba(244, 63, 94, 0.35); background: rgba(244, 63, 94, 0.08); }
    .insight-card-info { border-color: rgba(140, 113, 246, 0.35); background: rgba(140, 113, 246, 0.08); }

    /* ── Platform section ── */
    .platform-hero {
        background: linear-gradient(135deg, rgba(91, 63, 214, 0.22) 0%, rgba(236, 72, 153, 0.1) 45%, rgba(12, 10, 22, 0.98) 100%);
        border: 1px solid rgba(140, 113, 246, 0.32);
        box-shadow: 0 24px 60px -28px rgba(91, 63, 214, 0.55);
    }
    .platform-hero-glow {
        position: absolute;
        border-radius: 9999px;
        filter: blur(60px);
        pointer-events: none;
    }
    .platform-hero-glow--violet { width: 14rem; height: 14rem; top: -4rem; right: 10%; background: rgba(140, 113, 246, 0.35); }
    .platform-hero-glow--pink { width: 10rem; height: 10rem; bottom: -3rem; left: 5%; background: rgba(236, 72, 153, 0.2); }
    .platform-live-dot {
        width: 8px; height: 8px; border-radius: 9999px;
        background: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
        animation: platform-pulse-dot 2s ease infinite;
    }
    @keyframes platform-pulse-dot {
        0%, 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.45); }
        50% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    }
    .platform-hero-value { text-shadow: 0 0 40px rgba(140, 113, 246, 0.35); }
    .platform-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.4rem 0.85rem; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.85);
        background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12);
        backdrop-filter: blur(8px);
    }
    .platform-chip strong { color: #fff; font-weight: 800; }
    .platform-kpi-card {
        position: relative; overflow: hidden;
        border-radius: 1.25rem;
        padding: 1.25rem 1.35rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.07) 0%, rgba(18,16,28,0.95) 55%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease, border-color 0.25s ease;
    }
    .platform-kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--kpi-accent, linear-gradient(90deg, #5B3FD6, #8C71F6));
        opacity: 0.85;
    }
    .platform-kpi-card:hover {
        transform: translateY(-4px);
        border-color: rgba(140, 113, 246, 0.4);
        box-shadow: 0 20px 48px -20px rgba(91, 63, 214, 0.5);
    }
    .platform-kpi-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem;
        background: var(--kpi-icon-bg, rgba(140,113,246,0.15));
        border: 1px solid var(--kpi-icon-border, rgba(140,113,246,0.3));
    }
    .platform-panel {
        background: linear-gradient(160deg, rgba(255,255,255,0.05) 0%, rgba(14,12,24,0.96) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .platform-panel--revenue { border-color: rgba(140, 113, 246, 0.28); }
    .platform-panel--venue { border-color: rgba(16, 185, 129, 0.22); }
    .platform-icon-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 0.6rem; font-size: 0.85rem;
    }
    .platform-icon-badge--violet { background: rgba(140,113,246,0.2); border: 1px solid rgba(140,113,246,0.35); }
    .platform-icon-badge--emerald { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); }
    .platform-icon-badge--amber { background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3); }
    .platform-stat-pill {
        padding: 0.55rem 0.85rem; border-radius: 0.85rem;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
        min-width: 5.5rem;
    }
    .platform-stat-pill .label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); }
    .platform-stat-pill .value { font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px; }
    .platform-revenue-chart .platform-bar-wrap {
        flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center;
        min-width: 6px; position: relative;
    }
    .platform-revenue-chart .platform-bar {
        width: 100%; border-radius: 6px 6px 2px 2px;
        background: linear-gradient(180deg, #a78bfa 0%, #5B3FD6 55%, #4c2db8 100%);
        opacity: 0.88; transition: opacity 0.2s ease, transform 0.2s ease, filter 0.2s ease;
        box-shadow: 0 -4px 16px -4px rgba(140, 113, 246, 0.5);
    }
    .platform-revenue-chart .platform-bar-wrap:hover .platform-bar {
        opacity: 1; transform: scaleY(1.02); filter: brightness(1.15);
    }
    .platform-revenue-chart .platform-bar-wrap.is-peak .platform-bar {
        background: linear-gradient(180deg, #fbbf24 0%, #f59e0b 50%, #d97706 100%);
        box-shadow: 0 -4px 20px -2px rgba(245, 158, 11, 0.55);
    }
    .platform-revenue-chart .platform-bar-label {
        font-size: 8px; color: rgba(255,255,255,0.3); margin-top: 6px;
        writing-mode: horizontal-tb; transform: none;
    }
    .platform-revenue-chart .platform-bar-tooltip {
        position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%) scale(0.9);
        opacity: 0; pointer-events: none; transition: opacity 0.15s ease, transform 0.15s ease;
        background: rgba(18,16,28,0.95); border: 1px solid rgba(140,113,246,0.4);
        border-radius: 8px; padding: 4px 8px; white-space: nowrap; z-index: 10;
        font-size: 10px; font-weight: 700; color: #fff;
        box-shadow: 0 8px 24px -8px rgba(0,0,0,0.5);
    }
    .platform-revenue-chart .platform-bar-wrap:hover .platform-bar-tooltip {
        opacity: 1; transform: translateX(-50%) scale(1);
    }
    .platform-venue-ring {
        position: relative; width: 9.5rem; height: 9.5rem;
    }
    .platform-venue-ring svg { transform: rotate(-90deg); }
    .platform-venue-ring-center {
        position: absolute; inset: 18%; border-radius: 9999px;
        background: rgba(12,10,22,0.95); border: 1px solid rgba(255,255,255,0.1);
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .platform-venue-pill {
        padding: 0.85rem 1rem; border-radius: 1rem;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
    }
    .platform-insight-card {
        border-radius: 1rem; padding: 1rem 1.15rem;
        border: 1px solid rgba(255,255,255,0.1);
        background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(18,16,28,0.9) 100%);
        transition: border-color 0.2s ease, transform 0.2s ease;
    }
    .platform-insight-card:hover { transform: translateY(-2px); border-color: rgba(140,113,246,0.35); }
    .platform-insight-card.is-positive { border-color: rgba(16,185,129,0.3); background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(18,16,28,0.9) 100%); }
    .platform-insight-card.is-warning { border-color: rgba(245,158,11,0.3); background: linear-gradient(135deg, rgba(245,158,11,0.08) 0%, rgba(18,16,28,0.9) 100%); }
    .platform-insight-card.is-info { border-color: rgba(140,113,246,0.3); background: linear-gradient(135deg, rgba(140,113,246,0.1) 0%, rgba(18,16,28,0.9) 100%); }
    @keyframes platform-fade-up {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .platform-animate-in { animation: platform-fade-up 0.45s cubic-bezier(0.34, 1.2, 0.64, 1) both; }
    .platform-animate-in:nth-child(2) { animation-delay: 0.05s; }
    .platform-animate-in:nth-child(3) { animation-delay: 0.1s; }
    .platform-animate-in:nth-child(4) { animation-delay: 0.15s; }

    /* ── WhatsApp section ── */
    .wa-hero {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(6, 182, 212, 0.1) 45%, rgba(10, 18, 16, 0.98) 100%);
        border: 1px solid rgba(16, 185, 129, 0.32);
        box-shadow: 0 24px 60px -28px rgba(16, 185, 129, 0.45);
    }
    .wa-hero-glow { position: absolute; border-radius: 9999px; filter: blur(60px); pointer-events: none; }
    .wa-hero-glow--emerald { width: 14rem; height: 14rem; top: -4rem; right: 8%; background: rgba(16, 185, 129, 0.35); }
    .wa-hero-glow--teal { width: 10rem; height: 10rem; bottom: -3rem; left: 6%; background: rgba(6, 182, 212, 0.22); }
    .wa-live-dot {
        width: 8px; height: 8px; border-radius: 9999px; background: #10b981;
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.5);
        animation: platform-pulse-dot 2s ease infinite;
    }
    .wa-hero-value { text-shadow: 0 0 40px rgba(16, 185, 129, 0.35); }
    .wa-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.4rem 0.85rem; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.85);
        background: rgba(255,255,255,0.06); border: 1px solid rgba(16,185,129,0.25);
    }
    .wa-chip strong { color: #fff; font-weight: 800; }
    .wa-panel {
        background: linear-gradient(160deg, rgba(255,255,255,0.05) 0%, rgba(10,18,16,0.96) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .wa-panel--activity { border-color: rgba(16, 185, 129, 0.28); }
    .wa-panel--spotlight { border-color: rgba(6, 182, 212, 0.25); }
    .wa-icon-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 0.6rem; font-size: 0.85rem;
        background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3);
    }
    .wa-stat-pill {
        padding: 0.55rem 0.85rem; border-radius: 0.85rem;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
        min-width: 5.5rem;
    }
    .wa-stat-pill .label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); }
    .wa-stat-pill .value { font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px; }
    .wa-kpi-card {
        position: relative; overflow: hidden; border-radius: 1.25rem;
        padding: 1.25rem 1.35rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.07) 0%, rgba(10,18,16,0.95) 55%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
    }
    .wa-kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--wa-kpi-accent, linear-gradient(90deg, #10b981, #34d399));
    }
    .wa-kpi-card:hover { transform: translateY(-4px); border-color: rgba(16,185,129,0.4); box-shadow: 0 20px 48px -20px rgba(16,185,129,0.45); }
    .wa-kpi-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .wa-area-chart-wrap { position: relative; min-height: 14rem; }
    .wa-trend-chart {
        display: grid;
        grid-template-columns: 2.85rem minmax(0, 1fr);
        gap: 0.5rem;
        align-items: stretch;
    }
    .wa-trend-chart__yaxis {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 0.15rem 0 1.85rem;
        text-align: right;
    }
    .wa-trend-ytick {
        font-size: 10px;
        font-weight: 700;
        color: rgba(255,255,255,0.32);
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .wa-trend-chart__main { min-width: 0; display: flex; flex-direction: column; }
    .wa-trend-chart__plot {
        position: relative;
        height: 11.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .wa-trend-chart__plot svg {
        width: 100%;
        height: 100%;
        display: block;
    }
    .wa-trend-chart__dots {
        position: absolute;
        inset: 0 0 0 0;
        pointer-events: none;
    }
    .wa-trend-dot {
        position: absolute;
        width: 7px;
        height: 7px;
        border-radius: 9999px;
        background: #34d399;
        border: 1.5px solid #064e3b;
        transform: translate(-50%, 50%);
        pointer-events: auto;
        cursor: default;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .wa-trend-dot:hover {
        transform: translate(-50%, 50%) scale(1.25);
        box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.2);
    }
    .wa-trend-dot.is-peak {
        width: 10px;
        height: 10px;
        background: #fbbf24;
        border-color: #fff;
        z-index: 2;
        box-shadow: 0 0 0 4px rgba(251, 191, 36, 0.2);
    }
    .wa-trend-chart__xaxis {
        position: relative;
        height: 1.35rem;
        margin-top: 0.35rem;
    }
    .wa-trend-xtick {
        position: absolute;
        top: 0;
        transform: translateX(-50%);
        font-size: 9px;
        font-weight: 600;
        color: rgba(255,255,255,0.38);
        white-space: nowrap;
        max-width: 3.5rem;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .wa-area-grid-line { stroke: rgba(255,255,255,0.07); stroke-width: 1; vector-effect: non-scaling-stroke; }
    .wa-area-line { fill: none; stroke: #34d399; stroke-width: 2; vector-effect: non-scaling-stroke; stroke-linejoin: round; stroke-linecap: round; }
    .wa-top-action-card {
        border-radius: 1.25rem; padding: 1.25rem;
        background: linear-gradient(135deg, rgba(16,185,129,0.12) 0%, rgba(10,18,16,0.9) 100%);
        border: 1px solid rgba(16,185,129,0.3);
    }
    .wa-option-row {
        display: grid; grid-template-columns: 8rem 1fr 3.5rem 3rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) {
        .wa-option-row { grid-template-columns: 1fr; gap: 0.35rem; }
    }
    .wa-option-bar-track {
        height: 0.65rem; border-radius: 9999px; background: rgba(255,255,255,0.06); overflow: hidden;
    }
    .wa-option-bar-fill {
        height: 100%; border-radius: 9999px;
        transition: width 0.6s cubic-bezier(0.34, 1.2, 0.64, 1);
        box-shadow: 0 0 12px -2px currentColor;
    }
    .wa-data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .wa-data-table th {
        text-align: left; font-size: 10px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.06em; color: rgba(255,255,255,0.4); padding: 0 0.75rem 0.75rem 0;
    }
    .wa-data-table td {
        padding: 0.85rem 0.75rem 0.85rem 0; border-top: 1px solid rgba(255,255,255,0.06);
        font-size: 13px; color: rgba(255,255,255,0.85);
    }
    .wa-data-table tr:hover td { background: rgba(255,255,255,0.02); }
    .wa-rank-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.5rem; height: 1.5rem; border-radius: 0.5rem;
        font-size: 10px; font-weight: 800; background: rgba(255,255,255,0.06);
    }
    .wa-rank-badge.is-top { background: rgba(16,185,129,0.2); color: #6ee7b7; border: 1px solid rgba(16,185,129,0.35); }
    .wa-insight-card {
        border-radius: 1rem; padding: 1rem 1.15rem;
        border: 1px solid rgba(255,255,255,0.1);
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(10,18,16,0.9) 100%);
    }
    .wa-insight-card.is-positive { border-color: rgba(16,185,129,0.3); background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(10,18,16,0.9) 100%); }
    .wa-insight-card.is-warning { border-color: rgba(245,158,11,0.3); background: linear-gradient(135deg, rgba(245,158,11,0.08) 0%, rgba(10,18,16,0.9) 100%); }
    .wa-insight-card.is-info { border-color: rgba(6,182,212,0.3); background: linear-gradient(135deg, rgba(6,182,212,0.1) 0%, rgba(10,18,16,0.9) 100%); }
    .wa-animate-in { animation: platform-fade-up 0.45s cubic-bezier(0.34, 1.2, 0.64, 1) both; }

    /* ── QR Entry section ── */
    .qr-hero {
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.2) 0%, rgba(140, 113, 246, 0.1) 45%, rgba(8, 14, 22, 0.98) 100%);
        border: 1px solid rgba(6, 182, 212, 0.32);
        box-shadow: 0 24px 60px -28px rgba(6, 182, 212, 0.45);
    }
    .qr-hero-glow { position: absolute; border-radius: 9999px; filter: blur(60px); pointer-events: none; }
    .qr-hero-glow--cyan { width: 14rem; height: 14rem; top: -4rem; right: 8%; background: rgba(6, 182, 212, 0.35); }
    .qr-hero-glow--violet { width: 10rem; height: 10rem; bottom: -3rem; left: 6%; background: rgba(140, 113, 246, 0.22); }
    .qr-live-dot {
        width: 8px; height: 8px; border-radius: 9999px; background: #06b6d4;
        box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.5);
        animation: platform-pulse-dot 2s ease infinite;
    }
    .qr-hero-value { text-shadow: 0 0 40px rgba(6, 182, 212, 0.35); }
    .qr-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.4rem 0.85rem; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.85);
        background: rgba(255,255,255,0.06); border: 1px solid rgba(6,182,212,0.25);
    }
    .qr-chip strong { color: #fff; font-weight: 800; }
    .qr-panel {
        background: linear-gradient(160deg, rgba(255,255,255,0.05) 0%, rgba(8,14,22,0.96) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .qr-panel--trend { border-color: rgba(6, 182, 212, 0.28); }
    .qr-panel--spotlight { border-color: rgba(140, 113, 246, 0.25); }
    .qr-icon-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 0.6rem; font-size: 0.85rem;
        background: rgba(6,182,212,0.15); border: 1px solid rgba(6,182,212,0.3);
    }
    .qr-stat-pill {
        padding: 0.55rem 0.85rem; border-radius: 0.85rem;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
        min-width: 5.5rem;
    }
    .qr-stat-pill .label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); }
    .qr-stat-pill .value { font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px; }
    .qr-kpi-card {
        position: relative; overflow: hidden; border-radius: 1.25rem;
        padding: 1.25rem 1.35rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.07) 0%, rgba(8,14,22,0.95) 55%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease;
    }
    .qr-kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--qr-kpi-accent, linear-gradient(90deg, #06b6d4, #22d3ee));
    }
    .qr-kpi-card:hover { transform: translateY(-4px); border-color: rgba(6,182,212,0.4); box-shadow: 0 20px 48px -20px rgba(6,182,212,0.45); }
    .qr-kpi-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .qr-area-chart-wrap { position: relative; min-height: 14rem; }
    .qr-area-line {
        fill: none;
        stroke: #22d3ee;
        stroke-width: 2;
        vector-effect: non-scaling-stroke;
        stroke-linejoin: round;
        stroke-linecap: round;
    }
    .qr-trend-dot {
        background: #22d3ee;
        border-color: #164e63;
    }
    .qr-trend-dot:hover {
        box-shadow: 0 0 0 4px rgba(34, 211, 238, 0.22);
    }
    .qr-top-entry-card {
        border-radius: 1.25rem; padding: 1.25rem;
        background: linear-gradient(135deg, rgba(6,182,212,0.12) 0%, rgba(8,14,22,0.9) 100%);
        border: 1px solid rgba(6,182,212,0.3);
    }
    .qr-type-card {
        border-radius: 1.25rem; padding: 1.25rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.06) 0%, rgba(8,14,22,0.92) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .qr-type-card:hover { transform: translateY(-3px); }
    .qr-type-card.is-leader { border-color: rgba(6,182,212,0.45); box-shadow: 0 12px 32px -16px rgba(6,182,212,0.4); }
    .qr-entry-row {
        display: grid; grid-template-columns: 7rem 1fr 3.5rem 3rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) { .qr-entry-row { grid-template-columns: 1fr; gap: 0.35rem; } }
    .qr-entry-bar-track {
        height: 0.75rem; border-radius: 9999px; background: rgba(255,255,255,0.06); overflow: hidden;
    }
    .qr-entry-bar-fill {
        height: 100%; border-radius: 9999px;
        transition: width 0.6s cubic-bezier(0.34, 1.2, 0.64, 1);
        box-shadow: 0 0 14px -2px currentColor;
    }
    .qr-data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .qr-data-table th {
        text-align: left; font-size: 10px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.06em; color: rgba(255,255,255,0.4); padding: 0 0.5rem 0.6rem 0;
    }
    .qr-data-table td {
        padding: 0.7rem 0.5rem 0.7rem 0; border-top: 1px solid rgba(255,255,255,0.06);
        font-size: 12px; color: rgba(255,255,255,0.85);
    }
    .qr-insight-card {
        border-radius: 1rem; padding: 1rem 1.15rem;
        border: 1px solid rgba(255,255,255,0.1);
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(8,14,22,0.9) 100%);
    }
    .qr-insight-card.is-positive { border-color: rgba(6,182,212,0.3); background: linear-gradient(135deg, rgba(6,182,212,0.1) 0%, rgba(8,14,22,0.9) 100%); }
    .qr-insight-card.is-warning { border-color: rgba(245,158,11,0.3); background: linear-gradient(135deg, rgba(245,158,11,0.08) 0%, rgba(8,14,22,0.9) 100%); }
    .qr-insight-card.is-info { border-color: rgba(140,113,246,0.3); background: linear-gradient(135deg, rgba(140,113,246,0.1) 0%, rgba(8,14,22,0.9) 100%); }
    .qr-animate-in { animation: platform-fade-up 0.45s cubic-bezier(0.34, 1.2, 0.64, 1) both; }

    /* ── Journey / Conversion pipeline ── */
    .jn-hero {
        background: linear-gradient(135deg, rgba(140, 113, 246, 0.22) 0%, rgba(217, 70, 239, 0.12) 45%, rgba(12, 10, 24, 0.98) 100%);
        border: 1px solid rgba(192, 132, 252, 0.32);
        box-shadow: 0 24px 60px -28px rgba(140, 113, 246, 0.5);
    }
    .jn-hero-glow { position: absolute; border-radius: 9999px; filter: blur(60px); pointer-events: none; }
    .jn-hero-glow--violet { width: 14rem; height: 14rem; top: -4rem; right: 8%; background: rgba(140, 113, 246, 0.38); }
    .jn-hero-glow--fuchsia { width: 11rem; height: 11rem; bottom: -3rem; left: 5%; background: rgba(217, 70, 239, 0.22); }
    .jn-live-dot {
        width: 8px; height: 8px; border-radius: 9999px; background: #d946ef;
        box-shadow: 0 0 0 0 rgba(217, 70, 239, 0.5);
        animation: platform-pulse-dot 2s ease infinite;
    }
    .jn-hero-value { text-shadow: 0 0 40px rgba(192, 132, 252, 0.4); }
    .jn-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.4rem 0.85rem; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.85);
        background: rgba(255,255,255,0.06); border: 1px solid rgba(192,132,252,0.28);
    }
    .jn-chip strong { color: #fff; font-weight: 800; }
    .jn-panel {
        background: linear-gradient(160deg, rgba(255,255,255,0.05) 0%, rgba(12,10,24,0.96) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .jn-panel--pay { border-color: rgba(16, 185, 129, 0.22); }
    .jn-pipeline-panel {
        border-color: rgba(140, 113, 246, 0.35);
        box-shadow: 0 32px 80px -32px rgba(91, 63, 214, 0.45), inset 0 1px 0 rgba(255,255,255,0.06);
    }
    .jn-pipeline-bg {
        position: absolute; inset: 0; pointer-events: none;
        background:
            radial-gradient(ellipse 80% 50% at 50% 0%, rgba(140,113,246,0.12) 0%, transparent 55%),
            radial-gradient(ellipse 60% 40% at 80% 100%, rgba(217,70,239,0.08) 0%, transparent 50%);
    }
    .jn-icon-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 0.6rem; font-size: 0.85rem;
        background: rgba(140,113,246,0.18); border: 1px solid rgba(140,113,246,0.35);
    }
    .jn-stat-pill {
        padding: 0.55rem 0.85rem; border-radius: 0.85rem;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
        min-width: 5.5rem;
    }
    .jn-stat-pill .label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); }
    .jn-stat-pill .value { font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px; }
    .jn-kpi-card {
        position: relative; overflow: hidden; border-radius: 1.25rem;
        padding: 1.25rem 1.35rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.07) 0%, rgba(12,10,24,0.95) 55%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .jn-kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--jn-kpi-accent, linear-gradient(90deg, #8C71F6, #d946ef));
    }
    .jn-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px -16px rgba(140,113,246,0.45); }
    .jn-kpi-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .jn-conversion-ring { position: relative; width: 6rem; height: 6rem; }
    .jn-conversion-ring svg { transform: rotate(-90deg); }
    .jn-conversion-ring-center {
        position: absolute; inset: 16%; border-radius: 9999px;
        background: rgba(12,10,24,0.95); border: 1px solid rgba(255,255,255,0.12);
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .jn-funnel-visual svg { max-width: 280px; width: 100%; height: auto; filter: drop-shadow(0 20px 40px rgba(91,63,214,0.35)); }
    .jn-funnel-layer { transition: opacity 0.3s ease; cursor: default; }
    .jn-funnel-layer:hover { opacity: 0.92; filter: brightness(1.08); }
    .jn-flow-track { display: flex; flex-direction: column; gap: 0; position: relative; }
    .jn-flow-step {
        display: grid; grid-template-columns: 3rem 1fr auto; gap: 1rem; align-items: center;
        padding: 0.85rem 1rem; border-radius: 1rem;
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(18,16,28,0.6) 100%);
        border: 1px solid rgba(255,255,255,0.08);
        transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
    }
    .jn-flow-step:hover { border-color: rgba(140,113,246,0.4); transform: translateX(4px); }
    .jn-flow-step.is-start { border-color: rgba(140,113,246,0.35); background: linear-gradient(135deg, rgba(140,113,246,0.12) 0%, rgba(18,16,28,0.7) 100%); }
    .jn-flow-step.is-success { border-color: rgba(16,185,129,0.4); background: linear-gradient(135deg, rgba(16,185,129,0.12) 0%, rgba(18,16,28,0.7) 100%); }
    .jn-flow-step.is-weakest { border-color: rgba(245,158,11,0.45); box-shadow: 0 0 24px -8px rgba(245,158,11,0.35); }
    .jn-flow-connector {
        display: flex; align-items: center; gap: 0.75rem; padding: 0.35rem 0 0.35rem 1.5rem; min-height: 2rem;
    }
    .jn-flow-connector-line {
        width: 2px; flex: 0 0 2px; height: 1.25rem;
        background: linear-gradient(180deg, rgba(140,113,246,0.6), rgba(140,113,246,0.15));
        margin-left: 1.35rem;
    }
    .jn-flow-drop {
        font-size: 10px; font-weight: 800; padding: 0.2rem 0.55rem; border-radius: 9999px;
        background: rgba(244,63,94,0.15); border: 1px solid rgba(244,63,94,0.35); color: #fda4af;
    }
    .jn-flow-drop.is-none { background: rgba(16,185,129,0.1); border-color: rgba(16,185,129,0.3); color: #6ee7b7; }
    .jn-flow-icon {
        width: 2.75rem; height: 2.75rem; border-radius: 0.9rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12);
    }
    .jn-retention-track { height: 5px; border-radius: 9999px; background: rgba(255,255,255,0.08); overflow: hidden; margin-top: 0.5rem; }
    .jn-retention-fill {
        height: 100%; border-radius: 9999px;
        background: linear-gradient(90deg, #5B3FD6, #8C71F6, #d946ef);
        transition: width 0.7s cubic-bezier(0.34, 1.2, 0.64, 1);
    }
    .jn-flow-step.is-success .jn-retention-fill { background: linear-gradient(90deg, #059669, #34d399); }
    .jn-step-bar-row {
        display: grid; grid-template-columns: 8.5rem 1fr 3.5rem 3.5rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) { .jn-step-bar-row { grid-template-columns: 1fr; gap: 0.35rem; } }
    .jn-step-bar-track { height: 0.7rem; border-radius: 9999px; background: rgba(255,255,255,0.06); overflow: hidden; }
    .jn-step-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.6s ease; }
    .jn-data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .jn-data-table th {
        text-align: left; font-size: 10px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.06em; color: rgba(255,255,255,0.4); padding: 0 0.75rem 0.75rem 0;
    }
    .jn-data-table td {
        padding: 0.8rem 0.75rem 0.8rem 0; border-top: 1px solid rgba(255,255,255,0.06);
        font-size: 13px; color: rgba(255,255,255,0.88);
    }
    .jn-insight-card {
        border-radius: 1rem; padding: 1rem 1.1rem;
        border: 1px solid rgba(255,255,255,0.1);
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(12,10,24,0.9) 100%);
    }
    .jn-insight-card.is-positive { border-color: rgba(16,185,129,0.3); background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(12,10,24,0.9) 100%); }
    .jn-insight-card.is-warning { border-color: rgba(245,158,11,0.35); background: linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(12,10,24,0.9) 100%); }
    .jn-insight-card.is-info { border-color: rgba(140,113,246,0.35); background: linear-gradient(135deg, rgba(140,113,246,0.1) 0%, rgba(12,10,24,0.9) 100%); }
    .jn-animate-in { animation: platform-fade-up 0.45s cubic-bezier(0.34, 1.2, 0.64, 1) both; }

    /* ── Feedback section ── */
    .fb-hero {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(251, 191, 36, 0.1) 40%, rgba(20, 14, 10, 0.98) 100%);
        border: 1px solid rgba(245, 158, 11, 0.32);
        box-shadow: 0 24px 60px -28px rgba(245, 158, 11, 0.4);
    }
    .fb-hero-glow { position: absolute; border-radius: 9999px; filter: blur(60px); pointer-events: none; }
    .fb-hero-glow--amber { width: 14rem; height: 14rem; top: -4rem; right: 8%; background: rgba(245, 158, 11, 0.35); }
    .fb-hero-glow--rose { width: 10rem; height: 10rem; bottom: -3rem; left: 6%; background: rgba(244, 63, 94, 0.15); }
    .fb-live-dot {
        width: 8px; height: 8px; border-radius: 9999px; background: #fbbf24;
        box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.5);
        animation: platform-pulse-dot 2s ease infinite;
    }
    .fb-hero-value { text-shadow: 0 0 40px rgba(251, 191, 36, 0.35); }
    .fb-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.4rem 0.85rem; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.85);
        background: rgba(255,255,255,0.06); border: 1px solid rgba(245,158,11,0.28);
    }
    .fb-chip strong { color: #fff; font-weight: 800; }
    .fb-panel {
        background: linear-gradient(160deg, rgba(255,255,255,0.05) 0%, rgba(16,12,10,0.96) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .fb-panel--stars { border-color: rgba(245, 158, 11, 0.28); }
    .fb-panel--gauge { border-color: rgba(251, 191, 36, 0.22); }
    .fb-icon-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 0.6rem; font-size: 0.85rem;
        background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3);
    }
    .fb-stat-pill {
        padding: 0.55rem 0.85rem; border-radius: 0.85rem;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
        min-width: 5.5rem;
    }
    .fb-stat-pill .label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); }
    .fb-stat-pill .value { font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px; }
    .fb-kpi-card {
        position: relative; overflow: hidden; border-radius: 1.25rem;
        padding: 1.25rem 1.35rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.07) 0%, rgba(16,12,10,0.95) 55%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .fb-kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--fb-kpi-accent, linear-gradient(90deg, #f59e0b, #fbbf24));
    }
    .fb-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px -16px rgba(245,158,11,0.4); }
    .fb-kpi-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .fb-star-row {
        display: grid; grid-template-columns: 5rem 1fr 3rem 3.5rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) { .fb-star-row { grid-template-columns: 4rem 1fr 2.5rem; } .fb-star-row .fb-star-pct { display: none; } }
    .fb-star-bar-track {
        height: 1.75rem; border-radius: 0.65rem; background: rgba(255,255,255,0.06); overflow: hidden;
    }
    .fb-star-bar-fill {
        height: 100%; border-radius: 0.65rem;
        transition: width 0.65s cubic-bezier(0.34, 1.2, 0.64, 1);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.2);
    }
    .fb-rating-gauge-ring { position: relative; width: 9rem; height: 9rem; }
    .fb-rating-gauge-ring svg { transform: rotate(-90deg); }
    .fb-rating-gauge-center {
        position: absolute; inset: 14%; border-radius: 9999px;
        background: rgba(16,12,10,0.95); border: 1px solid rgba(255,255,255,0.12);
        display: flex; flex-direction: column; align-items: center; justify-content: center;
    }
    .fb-type-card {
        border-radius: 1.25rem; padding: 1.25rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.06) 0%, rgba(16,12,10,0.92) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .fb-type-card:hover { transform: translateY(-3px); }
    .fb-type-card.is-top { border-color: rgba(245,158,11,0.45); box-shadow: 0 12px 32px -16px rgba(245,158,11,0.35); }
    .fb-type-row {
        display: grid; grid-template-columns: 6.5rem 1fr 4rem 3rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) { .fb-type-row { grid-template-columns: 1fr; gap: 0.35rem; } }
    .fb-type-bar-track { height: 0.65rem; border-radius: 9999px; background: rgba(255,255,255,0.06); overflow: hidden; }
    .fb-type-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.6s ease; }
    .fb-data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .fb-data-table th {
        text-align: left; font-size: 10px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.06em; color: rgba(255,255,255,0.4); padding: 0 0.75rem 0.75rem 0;
    }
    .fb-data-table td {
        padding: 0.8rem 0.75rem 0.8rem 0; border-top: 1px solid rgba(255,255,255,0.06);
        font-size: 13px; color: rgba(255,255,255,0.88);
    }
    .fb-insight-card {
        border-radius: 1rem; padding: 1rem 1.15rem;
        border: 1px solid rgba(255,255,255,0.1);
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(16,12,10,0.9) 100%);
    }
    .fb-insight-card.is-positive { border-color: rgba(16,185,129,0.3); background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(16,12,10,0.9) 100%); }
    .fb-insight-card.is-warning { border-color: rgba(245,158,11,0.35); background: linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(16,12,10,0.9) 100%); }
    .fb-insight-card.is-alert { border-color: rgba(244,63,94,0.35); background: linear-gradient(135deg, rgba(244,63,94,0.1) 0%, rgba(16,12,10,0.9) 100%); }
    .fb-insight-card.is-info { border-color: rgba(251,191,36,0.3); background: linear-gradient(135deg, rgba(251,191,36,0.08) 0%, rgba(16,12,10,0.9) 100%); }
    .fb-animate-in { animation: platform-fade-up 0.45s cubic-bezier(0.34, 1.2, 0.64, 1) both; }

    /* ── Tips & payments section ── */
    .tp-hero {
        background: linear-gradient(135deg, rgba(236, 72, 153, 0.22) 0%, rgba(244, 63, 94, 0.1) 40%, rgba(18, 10, 16, 0.98) 100%);
        border: 1px solid rgba(236, 72, 153, 0.32);
        box-shadow: 0 24px 60px -28px rgba(236, 72, 153, 0.4);
    }
    .tp-hero-glow { position: absolute; border-radius: 9999px; filter: blur(60px); pointer-events: none; }
    .tp-hero-glow--pink { width: 14rem; height: 14rem; top: -4rem; right: 8%; background: rgba(236, 72, 153, 0.35); }
    .tp-hero-glow--violet { width: 10rem; height: 10rem; bottom: -3rem; left: 6%; background: rgba(140, 113, 246, 0.18); }
    .tp-live-dot {
        width: 8px; height: 8px; border-radius: 9999px; background: #f472b6;
        box-shadow: 0 0 0 0 rgba(244, 114, 182, 0.5);
        animation: platform-pulse-dot 2s ease infinite;
    }
    .tp-hero-value { text-shadow: 0 0 40px rgba(244, 114, 182, 0.35); }
    .tp-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.4rem 0.85rem; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.85);
        background: rgba(255,255,255,0.06); border: 1px solid rgba(236,72,153,0.28);
    }
    .tp-chip strong { color: #fff; font-weight: 800; }
    .tp-panel {
        background: linear-gradient(160deg, rgba(255,255,255,0.05) 0%, rgba(16,10,14,0.96) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .tp-panel--flow { border-color: rgba(236, 72, 153, 0.28); }
    .tp-panel--spotlight { border-color: rgba(140, 113, 246, 0.25); }
    .tp-icon-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 0.6rem; font-size: 0.85rem;
        background: rgba(236,72,153,0.15); border: 1px solid rgba(236,72,153,0.3);
    }
    .tp-stat-pill {
        padding: 0.55rem 0.85rem; border-radius: 0.85rem;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
        min-width: 5.5rem;
    }
    .tp-stat-pill .label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); }
    .tp-stat-pill .value { font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px; }
    .tp-kpi-card {
        position: relative; overflow: hidden; border-radius: 1.25rem;
        padding: 1.25rem 1.35rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.07) 0%, rgba(16,10,14,0.95) 55%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .tp-kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--tp-kpi-accent, linear-gradient(90deg, #ec4899, #f472b6));
    }
    .tp-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px -16px rgba(236,72,153,0.4); }
    .tp-kpi-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .tp-top-card {
        border-radius: 1.15rem; padding: 1.25rem;
        background: linear-gradient(145deg, rgba(236,72,153,0.12) 0%, rgba(16,10,14,0.9) 100%);
        border: 1px solid rgba(236,72,153,0.25);
    }
    .tp-flow-row {
        display: grid; grid-template-columns: 7rem 1fr 5rem 5rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) { .tp-flow-row { grid-template-columns: 1fr; gap: 0.35rem; } }
    .tp-flow-bar-track { height: 0.75rem; border-radius: 9999px; background: rgba(255,255,255,0.06); overflow: hidden; }
    .tp-flow-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.6s ease; }
    .tp-method-row {
        display: grid; grid-template-columns: 8rem 1fr 4rem 4.5rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) { .tp-method-row { grid-template-columns: 1fr; gap: 0.35rem; } }
    .tp-method-bar-track { height: 0.7rem; border-radius: 9999px; background: rgba(255,255,255,0.06); overflow: hidden; }
    .tp-method-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.6s ease; }
    .tp-method-card, .tp-purpose-card {
        border-radius: 1.15rem; padding: 1.15rem 1.25rem;
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .tp-method-card.is-top, .tp-purpose-card.is-top {
        border-color: rgba(236,72,153,0.35);
        background: linear-gradient(145deg, rgba(236,72,153,0.1) 0%, rgba(16,10,14,0.92) 100%);
    }
    .tp-method-card:hover, .tp-purpose-card:hover { transform: translateY(-2px); }
    .tp-purpose-row {
        display: grid; grid-template-columns: 8rem 1fr 4rem 4.5rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) { .tp-purpose-row { grid-template-columns: 1fr; gap: 0.35rem; } }
    .tp-purpose-bar-track { height: 0.7rem; border-radius: 9999px; background: rgba(255,255,255,0.06); overflow: hidden; }
    .tp-purpose-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.6s ease; }
    .tp-data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .tp-data-table th {
        text-align: left; font-size: 10px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.06em; color: rgba(255,255,255,0.4); padding: 0 0.75rem 0.75rem 0;
    }
    .tp-data-table td {
        padding: 0.8rem 0.75rem 0.8rem 0; border-top: 1px solid rgba(255,255,255,0.06);
        font-size: 13px; color: rgba(255,255,255,0.88);
    }
    .tp-insight-card {
        border-radius: 1rem; padding: 1rem 1.15rem;
        border: 1px solid rgba(255,255,255,0.1);
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(16,10,14,0.9) 100%);
    }
    .tp-insight-card.is-positive { border-color: rgba(16,185,129,0.3); background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(16,10,14,0.9) 100%); }
    .tp-insight-card.is-warning { border-color: rgba(245,158,11,0.35); background: linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(16,10,14,0.9) 100%); }
    .tp-insight-card.is-info { border-color: rgba(236,72,153,0.35); background: linear-gradient(135deg, rgba(236,72,153,0.1) 0%, rgba(16,10,14,0.9) 100%); }
    .tp-animate-in { animation: platform-fade-up 0.45s cubic-bezier(0.34, 1.2, 0.64, 1) both; }

    /* ── Language section ── */
    .lg-hero {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.22) 0%, rgba(140, 113, 246, 0.1) 40%, rgba(12, 10, 22, 0.98) 100%);
        border: 1px solid rgba(99, 102, 241, 0.32);
        box-shadow: 0 24px 60px -28px rgba(99, 102, 241, 0.4);
    }
    .lg-hero-glow { position: absolute; border-radius: 9999px; filter: blur(60px); pointer-events: none; }
    .lg-hero-glow--indigo { width: 14rem; height: 14rem; top: -4rem; right: 8%; background: rgba(99, 102, 241, 0.35); }
    .lg-hero-glow--violet { width: 10rem; height: 10rem; bottom: -3rem; left: 6%; background: rgba(140, 113, 246, 0.18); }
    .lg-live-dot {
        width: 8px; height: 8px; border-radius: 9999px; background: #818cf8;
        box-shadow: 0 0 0 0 rgba(129, 140, 248, 0.5);
        animation: platform-pulse-dot 2s ease infinite;
    }
    .lg-hero-value { text-shadow: 0 0 40px rgba(129, 140, 248, 0.35); }
    .lg-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.4rem 0.85rem; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.85);
        background: rgba(255,255,255,0.06); border: 1px solid rgba(99,102,241,0.28);
    }
    .lg-chip strong { color: #fff; font-weight: 800; }
    .lg-panel {
        background: linear-gradient(160deg, rgba(255,255,255,0.05) 0%, rgba(12,10,22,0.96) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .lg-panel--langs { border-color: rgba(99, 102, 241, 0.28); }
    .lg-panel--spotlight { border-color: rgba(140, 113, 246, 0.25); }
    .lg-icon-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 0.6rem; font-size: 0.85rem;
        background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.3);
    }
    .lg-stat-pill, .lg-peak-pill {
        padding: 0.55rem 0.85rem; border-radius: 0.85rem;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
        min-width: 5.5rem;
    }
    .lg-stat-pill .label, .lg-peak-pill .label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); }
    .lg-stat-pill .value, .lg-peak-pill .value { font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px; }
    .lg-kpi-card {
        position: relative; overflow: hidden; border-radius: 1.25rem;
        padding: 1.25rem 1.35rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.07) 0%, rgba(12,10,22,0.95) 55%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .lg-kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--lg-kpi-accent, linear-gradient(90deg, #6366f1, #818cf8));
    }
    .lg-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px -16px rgba(99,102,241,0.4); }
    .lg-kpi-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .lg-top-card {
        border-radius: 1.15rem; padding: 1.25rem;
        background: linear-gradient(145deg, rgba(99,102,241,0.12) 0%, rgba(12,10,22,0.9) 100%);
        border: 1px solid rgba(99,102,241,0.25);
    }
    .lg-lang-row {
        display: grid; grid-template-columns: 8rem 1fr 4rem 4.5rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) { .lg-lang-row { grid-template-columns: 1fr; gap: 0.35rem; } }
    .lg-lang-bar-track { height: 0.7rem; border-radius: 9999px; background: rgba(255,255,255,0.06); overflow: hidden; }
    .lg-lang-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.6s ease; }
    .lg-lang-card {
        border-radius: 1.15rem; padding: 1.15rem 1.25rem;
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
        transition: transform 0.2s ease, border-color 0.2s ease;
    }
    .lg-lang-card.is-top {
        border-color: rgba(99,102,241,0.35);
        background: linear-gradient(145deg, rgba(99,102,241,0.1) 0%, rgba(12,10,22,0.92) 100%);
    }
    .lg-lang-card:hover { transform: translateY(-2px); }
    .lg-peak-chart-wrap { min-height: 10rem; }
    .lg-peak-chart-wrap--compact { min-height: 8rem; }
    .lg-hour-chart {
        display: grid;
        grid-template-columns: 2.5rem minmax(0, 1fr);
        gap: 0.45rem;
        align-items: stretch;
    }
    .lg-hour-chart__yaxis {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 0.1rem 0 1.55rem;
        text-align: right;
    }
    .lg-hour-ytick {
        font-size: 10px;
        font-weight: 700;
        color: rgba(255,255,255,0.32);
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .lg-hour-chart__main { min-width: 0; display: flex; flex-direction: column; }
    .lg-hour-chart__plot {
        height: 8.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        padding: 0 0.1rem;
    }
    .lg-hour-chart.is-compact .lg-hour-chart__plot { height: 6.75rem; }
    .lg-hour-chart__bars {
        display: flex;
        align-items: flex-end;
        gap: 2px;
        height: 100%;
    }
    .lg-hour-bar {
        flex: 1;
        min-width: 0;
        height: 100%;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }
    .lg-hour-bar__fill {
        width: 100%;
        max-width: 10px;
        border-radius: 3px 3px 0 0;
        min-height: 3px;
        transition: height 0.5s cubic-bezier(0.34, 1.2, 0.64, 1);
        background: linear-gradient(to top, #4338ca, #818cf8);
    }
    .lg-hour-bar__fill--emerald {
        background: linear-gradient(to top, #059669, #34d399);
    }
    .lg-hour-bar__fill.is-peak {
        background: linear-gradient(to top, #d97706, #fbbf24) !important;
        box-shadow: 0 0 8px rgba(251, 191, 36, 0.35);
    }
    .lg-hour-chart__xaxis {
        position: relative;
        height: 1.25rem;
        margin-top: 0.3rem;
    }
    .lg-hour-xtick {
        position: absolute;
        top: 0;
        transform: translateX(-50%);
        font-size: 9px;
        font-weight: 600;
        color: rgba(255,255,255,0.38);
        white-space: nowrap;
    }
    .lg-data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .lg-data-table th {
        text-align: left; font-size: 10px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.06em; color: rgba(255,255,255,0.4); padding: 0 0.75rem 0.75rem 0;
    }
    .lg-data-table td {
        padding: 0.8rem 0.75rem 0.8rem 0; border-top: 1px solid rgba(255,255,255,0.06);
        font-size: 13px; color: rgba(255,255,255,0.88);
    }
    .lg-insight-card {
        border-radius: 1rem; padding: 1rem 1.15rem;
        border: 1px solid rgba(255,255,255,0.1);
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(12,10,22,0.9) 100%);
    }
    .lg-insight-card.is-positive { border-color: rgba(16,185,129,0.3); background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(12,10,22,0.9) 100%); }
    .lg-insight-card.is-warning { border-color: rgba(245,158,11,0.35); background: linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(12,10,22,0.9) 100%); }
    .lg-insight-card.is-info { border-color: rgba(99,102,241,0.35); background: linear-gradient(135deg, rgba(99,102,241,0.1) 0%, rgba(12,10,22,0.9) 100%); }
    .lg-animate-in { animation: platform-fade-up 0.45s cubic-bezier(0.34, 1.2, 0.64, 1) both; }

    /* ── Platform pulse (venues) section ── */
    .pl-hero {
        background: linear-gradient(135deg, rgba(140, 113, 246, 0.22) 0%, rgba(16, 185, 129, 0.08) 40%, rgba(12, 10, 22, 0.98) 100%);
        border: 1px solid rgba(140, 113, 246, 0.32);
        box-shadow: 0 24px 60px -28px rgba(140, 113, 246, 0.4);
    }
    .pl-hero-glow { position: absolute; border-radius: 9999px; filter: blur(60px); pointer-events: none; }
    .pl-hero-glow--violet { width: 14rem; height: 14rem; top: -4rem; right: 8%; background: rgba(140, 113, 246, 0.35); }
    .pl-hero-glow--emerald { width: 10rem; height: 10rem; bottom: -3rem; left: 6%; background: rgba(16, 185, 129, 0.15); }
    .pl-live-dot {
        width: 8px; height: 8px; border-radius: 9999px; background: #a78bfa;
        box-shadow: 0 0 0 0 rgba(167, 139, 250, 0.5);
        animation: platform-pulse-dot 2s ease infinite;
    }
    .pl-hero-value { text-shadow: 0 0 40px rgba(140, 113, 246, 0.35); }
    .pl-chip {
        display: inline-flex; align-items: center; gap: 0.35rem;
        padding: 0.4rem 0.85rem; border-radius: 9999px;
        font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.85);
        background: rgba(255,255,255,0.06); border: 1px solid rgba(140,113,246,0.28);
    }
    .pl-chip strong { color: #fff; font-weight: 800; }
    .pl-panel {
        background: linear-gradient(160deg, rgba(255,255,255,0.05) 0%, rgba(12,10,22,0.96) 100%);
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .pl-panel--venue { border-color: rgba(16, 185, 129, 0.28); }
    .pl-panel--orders { border-color: rgba(140, 113, 246, 0.28); }
    .pl-icon-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 1.75rem; height: 1.75rem; border-radius: 0.6rem; font-size: 0.85rem;
        background: rgba(140,113,246,0.15); border: 1px solid rgba(140,113,246,0.3);
    }
    .pl-stat-pill, .pl-venue-pill {
        padding: 0.55rem 0.85rem; border-radius: 0.85rem;
        background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
        min-width: 5.5rem;
    }
    .pl-stat-pill .label, .pl-venue-pill .label { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: rgba(255,255,255,0.4); }
    .pl-stat-pill .value, .pl-venue-pill .value { font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px; }
    .pl-kpi-card {
        position: relative; overflow: hidden; border-radius: 1.25rem;
        padding: 1.25rem 1.35rem;
        background: linear-gradient(155deg, rgba(255,255,255,0.07) 0%, rgba(12,10,22,0.95) 55%);
        border: 1px solid rgba(255,255,255,0.1);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .pl-kpi-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: var(--pl-kpi-accent, linear-gradient(90deg, #8C71F6, #a78bfa));
    }
    .pl-kpi-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px -16px rgba(140,113,246,0.4); }
    .pl-kpi-icon {
        width: 2.5rem; height: 2.5rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .pl-venue-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; }
    .pl-venue-ring-center {
        position: absolute; inset: 0; display: flex; flex-direction: column;
        align-items: center; justify-content: center; text-align: center;
    }
    .pl-order-row, .pl-activity-row {
        display: grid; grid-template-columns: 8rem 1fr 5rem 5rem; gap: 0.75rem; align-items: center;
    }
    @media (max-width: 640px) { .pl-order-row, .pl-activity-row { grid-template-columns: 1fr; gap: 0.35rem; } }
    .pl-order-bar-track, .pl-activity-bar-track { height: 0.75rem; border-radius: 9999px; background: rgba(255,255,255,0.06); overflow: hidden; }
    .pl-order-bar-fill, .pl-activity-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.6s ease; }
    .pl-activity-card {
        border-radius: 1.15rem; padding: 1.15rem 1.25rem;
        background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);
        transition: transform 0.2s ease;
    }
    .pl-activity-card:hover { transform: translateY(-2px); }
    .pl-data-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .pl-data-table th {
        text-align: left; font-size: 10px; font-weight: 800; text-transform: uppercase;
        letter-spacing: 0.06em; color: rgba(255,255,255,0.4); padding: 0 0.75rem 0.75rem 0;
    }
    .pl-data-table td {
        padding: 0.8rem 0.75rem 0.8rem 0; border-top: 1px solid rgba(255,255,255,0.06);
        font-size: 13px; color: rgba(255,255,255,0.88);
    }
    .pl-insight-card {
        border-radius: 1rem; padding: 1rem 1.15rem;
        border: 1px solid rgba(255,255,255,0.1);
        background: linear-gradient(135deg, rgba(255,255,255,0.04) 0%, rgba(12,10,22,0.9) 100%);
    }
    .pl-insight-card.is-positive { border-color: rgba(16,185,129,0.3); background: linear-gradient(135deg, rgba(16,185,129,0.1) 0%, rgba(12,10,22,0.9) 100%); }
    .pl-insight-card.is-warning { border-color: rgba(245,158,11,0.35); background: linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(12,10,22,0.9) 100%); }
    .pl-insight-card.is-info { border-color: rgba(140,113,246,0.35); background: linear-gradient(135deg, rgba(140,113,246,0.1) 0%, rgba(12,10,22,0.9) 100%); }
    .pl-animate-in { animation: platform-fade-up 0.45s cubic-bezier(0.34, 1.2, 0.64, 1) both; }
</style>
