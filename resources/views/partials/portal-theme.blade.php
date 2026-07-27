{{-- Shared TIPTAP light portal theme. Keep this after layout-specific styles. --}}
<style>
    :root {
        --fin-primary: #5B2A78;
        --fin-primary-dark: #47205F;
        --fin-primary-deep: #321644;
        --fin-light: #F1EAF5;
        --fin-lavender: #E4D7EB;
        --fin-mist: #F8F4FA;
        --fin-ink: #18131D;
        --fin-muted: #554C5A;
        --fin-surface: #F7F5F8;
        --fin-whatsapp: #25D366;

        --portal-bg: #F7F5F8;
        --portal-surface: #FFFFFF;
        --portal-surface-elevated: #FFFFFF;
        --portal-sidebar: linear-gradient(180deg, #52276F 0%, #421E5B 58%, #351748 100%);
        --portal-glass: rgba(255, 255, 255, 0.94);
        --portal-glass-border: #E9E2ED;
        --portal-border: #E8E2EB;
        --portal-border-strong: #D9CDE0;
        --portal-ink: #18131D;
        --portal-ink-soft: #3D3442;
        --portal-muted: #554C5A;
        --portal-subtle: #6A6070;
        --portal-accent-soft: rgba(91, 42, 120, 0.08);
        --portal-accent-strong: rgba(91, 42, 120, 0.14);
        --portal-accent-glow: rgba(91, 42, 120, 0.22);
        --portal-gradient: linear-gradient(135deg, var(--fin-primary) 0%, var(--fin-primary-dark) 100%);
        --portal-gradient-vertical: linear-gradient(180deg, var(--fin-primary) 0%, var(--fin-primary-dark) 100%);
        --portal-shadow-sm: 0 1px 2px rgba(43, 24, 52, 0.03), 0 8px 22px rgba(43, 24, 52, 0.045);
        --portal-shadow: 0 18px 46px -32px rgba(56, 25, 72, 0.32), 0 4px 14px rgba(43, 24, 52, 0.04);
    }

    html:has(body.portal-light) {
        background: var(--portal-bg);
    }

    body.portal-light {
        background: var(--portal-bg) !important;
        color: var(--portal-ink) !important;
    }

    .portal-light .portal-shell,
    .portal-light .portal-ambient {
        background: var(--portal-bg) !important;
        color: var(--portal-ink) !important;
    }

    .portal-light .sidebar-gradient,
    .portal-light #sidebar,
    .portal-light #mobile-sidebar,
    .portal-light [class~="bg-surface-900"],
    .portal-light [class~="bg-surface-900/95"],
    .portal-light [class~="bg-surface-900/80"] {
        background: var(--portal-sidebar) !important;
    }

    .portal-light #sidebar,
    .portal-light #mobile-sidebar {
        color: #FFFFFF !important;
        border-color: rgba(255, 255, 255, 0.10) !important;
        box-shadow: 18px 0 48px -34px rgba(42, 18, 56, 0.72) !important;
    }

    .portal-light .glass,
    .portal-light .portal-glass {
        background: var(--portal-glass) !important;
        border-color: var(--portal-glass-border) !important;
        box-shadow: var(--portal-shadow-sm);
        backdrop-filter: blur(20px) saturate(150%);
        -webkit-backdrop-filter: blur(20px) saturate(150%);
    }

    .portal-light .glass-card,
    .portal-light .portal-glass-card,
    .portal-light .analytics-shell {
        background: #FFFFFF !important;
        border-color: var(--portal-glass-border) !important;
        box-shadow: var(--portal-shadow) !important;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    .portal-light .card-hover:hover {
        background: #FFFFFF !important;
        border-color: rgba(91, 42, 120, 0.22) !important;
        box-shadow: 0 18px 34px -24px rgba(56, 25, 72, 0.36) !important;
        transform: translateY(-2px);
    }

    .portal-light .gradient-text,
    .portal-light .portal-gradient-text {
        background: var(--portal-gradient) !important;
        -webkit-background-clip: text !important;
        background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
    }

    .portal-light .sidebar-link {
        color: var(--portal-muted) !important;
        border-color: transparent !important;
    }

    .portal-light .sidebar-link:hover {
        background: var(--portal-accent-soft) !important;
        color: var(--fin-primary-deep) !important;
    }

    .portal-light .sidebar-link-active {
        background: linear-gradient(90deg, rgba(91, 42, 120, 0.16), rgba(91, 42, 120, 0.04)) !important;
        color: var(--fin-primary-deep) !important;
    }

    .portal-light .sidebar-link-active::before {
        background: var(--portal-gradient-vertical) !important;
    }

    .portal-light [class~="text-white"],
    .portal-light [class~="text-white/90"],
    .portal-light [class~="text-white/85"],
    .portal-light [class~="text-white/80"] {
        color: var(--portal-ink) !important;
    }

    .portal-light [class~="text-white/75"],
    .portal-light [class~="text-white/70"],
    .portal-light [class~="text-white/65"],
    .portal-light [class~="text-white/60"],
    .portal-light [class~="text-white/55"],
    .portal-light [class~="text-white/50"] {
        color: var(--portal-muted) !important;
    }

    .portal-light [class~="text-white/45"],
    .portal-light [class~="text-white/40"],
    .portal-light [class~="text-white/35"],
    .portal-light [class~="text-white/30"],
    .portal-light [class~="text-white/25"],
    .portal-light [class~="text-white/20"] {
        color: var(--portal-subtle) !important;
    }

    .portal-light [class~="bg-white/5"] {
        background-color: rgba(91, 42, 120, 0.045) !important;
    }

    .portal-light [class~="bg-white/10"] {
        background-color: rgba(91, 42, 120, 0.08) !important;
    }

    .portal-light [class~="bg-white/15"],
    .portal-light [class~="bg-white/20"] {
        background-color: rgba(91, 42, 120, 0.13) !important;
    }

    .portal-light [class~="bg-white/[0.03]"],
    .portal-light [class~="bg-white/8"] {
        background-color: rgba(91, 42, 120, 0.04) !important;
    }

    .portal-light [class~="bg-[#0f0a1e]"],
    .portal-light [class~="bg-[#12101c]"],
    .portal-light [class~="bg-[#0f0a1e]/80"] {
        background-color: #FFFFFF !important;
    }

    .portal-light [class~="hover:bg-white/5"]:hover {
        background-color: rgba(91, 42, 120, 0.07) !important;
    }

    .portal-light [class~="hover:bg-white/10"]:hover,
    .portal-light [class~="hover:bg-white/15"]:hover {
        background-color: rgba(91, 42, 120, 0.13) !important;
    }

    .portal-light [class~="border-white/5"],
    .portal-light [class~="border-white/10"] {
        border-color: var(--portal-border) !important;
    }

    .portal-light [class~="border-white/20"],
    .portal-light [class~="border-white/30"] {
        border-color: var(--portal-border-strong) !important;
    }

    .portal-light [class~="divide-white/5"] > :not(:last-child),
    .portal-light [class~="divide-white/10"] > :not(:last-child) {
        border-color: var(--portal-border) !important;
    }

    .portal-light [class~="ring-white/10"],
    .portal-light [class~="ring-white/20"],
    .portal-light [class~="ring-white/30"] {
        --tw-ring-color: rgba(91, 42, 120, 0.22) !important;
    }

    .portal-light [class*="focus:ring-offset-[#0f0a1e]"],
    .portal-light [class*="focus:ring-offset-[#12101c]"] {
        --tw-ring-offset-color: var(--portal-bg) !important;
    }

    .portal-light input:not([type="checkbox"]):not([type="radio"]):not([type="range"]),
    .portal-light select,
    .portal-light textarea {
        background-color: #FFFFFF;
        color: var(--portal-ink);
        border-color: var(--portal-border);
    }

    .portal-light input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):focus,
    .portal-light select:focus,
    .portal-light textarea:focus {
        border-color: rgba(91, 42, 120, 0.58);
        box-shadow: 0 0 0 3px rgba(91, 42, 120, 0.12);
    }

    .portal-light input::placeholder,
    .portal-light textarea::placeholder,
    .portal-light [class*="placeholder-white"]::placeholder {
        color: var(--portal-subtle) !important;
        opacity: 1;
    }

    .portal-light select option {
        background: #FFFFFF;
        color: var(--portal-ink);
    }

    .portal-light [class*="from-violet-"][class*="to-cyan-"],
    .portal-light [class*="from-fin-primary"][class*="to-fin-primary-dark"] {
        background-image: var(--portal-gradient) !important;
    }

    .portal-light [class*="from-surface-900"][class*="to-surface-800"],
    .portal-light [class*="from-cyan-900/50"][class*="to-violet-900/50"],
    .portal-light [class*="from-violet-900/50"][class*="to-cyan-900/50"] {
        background-image: linear-gradient(135deg, rgba(91, 42, 120, 0.08), #FFFFFF 72%) !important;
        border-color: var(--portal-border-strong) !important;
        color: var(--portal-ink) !important;
    }

    .portal-light [class~="bg-cyan-600"][class~="text-white"] {
        background-color: var(--fin-primary-dark) !important;
        color: #FFFFFF !important;
    }

    .portal-light [class~="hover:bg-cyan-700"]:hover {
        background-color: var(--fin-primary-deep) !important;
    }

    .portal-light :is(a, button)[class*="bg-gradient-to-"],
    .portal-light [class~="bg-violet-600"][class~="text-white"],
    .portal-light [class~="bg-violet-500"][class~="text-white"],
    .portal-light [class~="bg-fin-primary"][class~="text-white"],
    .portal-light [class~="bg-fin-primary-dark"][class~="text-white"],
    .portal-light [class~="bg-indigo-600"][class~="text-white"],
    .portal-light [class~="bg-emerald-500"][class~="text-white"],
    .portal-light [class~="bg-rose-600"][class~="text-white"],
    .portal-light [class~="bg-red-600"][class~="text-white"],
    .portal-light [class~="bg-emerald-600"][class~="text-white"] {
        color: #FFFFFF !important;
    }

    .portal-light [class*="from-amber-600"][class~="text-white"],
    .portal-light [class*="from-emerald-600"][class~="text-white"],
    .portal-light [class*="from-rose-600"][class~="text-white"],
    .portal-light [class*="from-violet-600"][class~="text-white"] {
        color: #FFFFFF !important;
    }

    .portal-light :is(a, button)[class*="bg-gradient-to-"] [class*="text-white"],
    .portal-light :is(a, button)[class*="bg-gradient-to-"] svg,
    .portal-light :is(a, button)[class~="bg-violet-600"] svg,
    .portal-light :is(a, button)[class~="bg-violet-500"] svg,
    .portal-light :is(a, button)[class~="bg-fin-primary"] [class~="text-white"],
    .portal-light :is(a, button)[class~="bg-fin-primary"] svg {
        color: #FFFFFF !important;
    }

    .portal-light .portal-btn-primary,
    .portal-light .btn-fin {
        background: var(--portal-gradient) !important;
        color: #FFFFFF !important;
        box-shadow: 0 12px 26px -12px var(--portal-accent-glow) !important;
    }

    .portal-light table {
        color: var(--portal-ink);
    }

    .portal-light table thead {
        background: rgba(91, 42, 120, 0.05);
    }

    .portal-light table tbody tr {
        border-color: var(--portal-border);
    }

    .portal-light .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(91, 42, 120, 0.04) !important;
    }

    .portal-light .custom-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(91, 42, 120, 0.52), rgba(71, 32, 95, 0.52)) !important;
    }

    .portal-light .shadow-black\/50 {
        --tw-shadow-color: rgba(18, 20, 28, 0.10) !important;
    }

    .portal-light .manager-payroll-page .search-wrap svg,
    .portal-light .manager-payroll-page .field-label,
    .portal-light .manager-payroll-page .acc-net-peek .plabel,
    .portal-light .manager-payroll-page .fin-sub span:first-child,
    .portal-light .manager-payroll-page .no-results {
        color: var(--portal-muted) !important;
    }

    .portal-light .manager-payroll-page .search-input,
    .portal-light .manager-payroll-page .fin-input,
    .portal-light .manager-payroll-page .month-select {
        background: #FFFFFF !important;
        border-color: var(--portal-border) !important;
        color: var(--portal-ink) !important;
    }

    .portal-light .manager-payroll-page .filter-btn,
    .portal-light .manager-payroll-page .acc-item,
    .portal-light .manager-payroll-page .acc-chevron,
    .portal-light .manager-payroll-page .fin-panel,
    .portal-light .manager-payroll-page .net-panel,
    .portal-light .manager-payroll-page .btn-update,
    .portal-light .manager-payroll-page .btn-history,
    .portal-light .manager-payroll-page .chip-n {
        background: #FFFFFF !important;
        border-color: var(--portal-border) !important;
        color: var(--portal-muted) !important;
    }

    .portal-light .manager-payroll-page .filter-btn:hover,
    .portal-light .manager-payroll-page .filter-btn.f-all,
    .portal-light .manager-payroll-page .acc-item.is-open,
    .portal-light .manager-payroll-page .acc-trigger:hover,
    .portal-light .manager-payroll-page .btn-update:hover:not(:disabled),
    .portal-light .manager-payroll-page .btn-history:hover {
        background: var(--fin-mist) !important;
        border-color: var(--portal-border-strong) !important;
        color: var(--portal-ink) !important;
    }

    .portal-light .manager-payroll-page .acc-name,
    .portal-light .manager-payroll-page .acc-net-peek .pvalue,
    .portal-light .manager-payroll-page [style*="color:#fff"] {
        color: var(--portal-ink) !important;
    }

    .portal-light .manager-payroll-page [style*="color:rgba(255,255,255"] {
        color: var(--portal-muted) !important;
    }

    .portal-light .manager-payroll-page [style*="background:rgba(255,255,255"] {
        background: #FFFFFF !important;
        border-color: var(--portal-border) !important;
    }

    .portal-light .manager-payroll-page .btn-confirm,
    .portal-light .manager-payroll-page [style*="background:linear-gradient(135deg,#7c3aed,#0891b2)"] {
        background: var(--portal-gradient) !important;
        color: #FFFFFF !important;
    }

    .portal-light .manager-payroll-page .acc-avatar {
        background: var(--fin-light) !important;
        border-color: var(--fin-lavender) !important;
        color: var(--fin-primary) !important;
    }

    .portal-light .manager-payroll-page .acc-gw,
    .portal-light .manager-payroll-page [style*="color:rgba(6,182,212"] {
        color: #075E66 !important;
    }

    .portal-light .manager-payroll-page .filter-btn.f-paid,
    .portal-light .manager-payroll-page .acc-pill.paid,
    .portal-light .manager-payroll-page .fin-label.e,
    .portal-light .manager-payroll-page .chip-p,
    .portal-light .manager-payroll-page .confirmed-badge,
    .portal-light .manager-payroll-page .alert-s,
    .portal-light .manager-payroll-page [style*="color:#6ee7b7"],
    .portal-light .manager-payroll-page [style*="color:rgba(16,185,129"] {
        color: #066845 !important;
    }

    .portal-light .manager-payroll-page .filter-btn.f-unpaid,
    .portal-light .manager-payroll-page .acc-pill.pending,
    .portal-light .manager-payroll-page .chip-u,
    .portal-light .manager-payroll-page [style*="color:#fcd34d"] {
        color: #8A4B00 !important;
    }

    .portal-light .manager-payroll-page .fin-label.d,
    .portal-light .manager-payroll-page .alert-e,
    .portal-light .manager-payroll-page [style*="color:#f87171"],
    .portal-light .manager-payroll-page [style*="color:#fca5a5"] {
        color: #B42345 !important;
    }

    .portal-light .manager-payroll-page .acc-form-wrap,
    .portal-light .manager-payroll-page .fin-ph,
    .portal-light .manager-payroll-page .fin-sub {
        border-color: var(--portal-border) !important;
    }

    .portal-light .manager-payroll-page .net-div,
    .portal-light .manager-payroll-page .progress-track {
        background: var(--fin-light) !important;
    }

    .portal-light .roles-shell .role-segment[aria-selected="true"] {
        color: var(--fin-primary-deep) !important;
    }

    .portal-light .roles-shell .perm-toggle span {
        background: #E8E8ED !important;
        border-color: #D7D8E0 !important;
    }

    .portal-light .roles-shell .perm-toggle input:checked + span {
        background: var(--fin-primary) !important;
        border-color: transparent !important;
    }

    .portal-light .roles-shell [data-role-panel="technical"] .perm-toggle input:checked + span {
        background: #0EA5E9 !important;
    }

    .portal-light .revenue-chart-container {
        background: #FFFFFF !important;
        border-color: var(--portal-border) !important;
    }

    /*
     * Tailwind's 300/400 tones were designed for dark surfaces. Use their
     * accessible counterparts when the same components sit on the light portal.
     */
    .portal-light :is(#main-content, #supervisor-content) :is(.text-violet-300, .text-violet-400, .text-purple-300, .text-purple-400, .text-indigo-300, .text-indigo-400) {
        color: var(--fin-primary) !important;
    }

    .portal-light :is(#main-content, #supervisor-content) .text-fin-primary {
        color: var(--fin-primary) !important;
    }

    .portal-light :is(#main-content, #supervisor-content) :is(.text-cyan-300, .text-cyan-400, .text-sky-300, .text-sky-400, .text-blue-300, .text-blue-400) {
        color: #075E66 !important;
    }

    .portal-light :is(#main-content, #supervisor-content) :is(.text-emerald-300, .text-emerald-400, .text-teal-300, .text-teal-400, .text-green-300, .text-green-400) {
        color: #066845 !important;
    }

    .portal-light :is(#main-content, #supervisor-content) :is(.text-amber-300, .text-amber-400, .text-orange-300, .text-orange-400, .text-yellow-300, .text-yellow-400) {
        color: #8A4B00 !important;
    }

    .portal-light :is(#main-content, #supervisor-content) :is(.text-rose-300, .text-rose-400, .text-red-300, .text-red-400) {
        color: #B42345 !important;
    }

    .portal-light :is(#main-content, #supervisor-content) :is(.text-slate-300, .text-slate-400, .text-gray-300, .text-gray-400) {
        color: #49505E !important;
    }

    .portal-light .chart-tooltip {
        background: #FFFFFF !important;
        border-color: var(--portal-border-strong) !important;
        color: var(--portal-ink) !important;
        box-shadow: var(--portal-shadow) !important;
    }

    .portal-light .admin-dash-hero,
    .portal-light .venue-hero {
        background: linear-gradient(135deg, rgba(91, 42, 120, 0.11) 0%, rgba(248, 244, 250, 0.98) 48%, #FFFFFF 100%) !important;
        border-color: rgba(91, 42, 120, 0.18) !important;
        box-shadow: var(--portal-shadow) !important;
    }

    .portal-light .venue-tab-active {
        color: var(--fin-primary-deep) !important;
        background: linear-gradient(180deg, rgba(91, 42, 120, 0.12), rgba(91, 42, 120, 0.03)) !important;
    }

    .portal-light .admin-ring-track {
        stroke: rgba(91, 42, 120, 0.12) !important;
    }

    .portal-light .admin-data-panel,
    .portal-light .admin-empty-panel {
        background: #FFFFFF !important;
        border-color: var(--portal-border) !important;
    }

    .portal-light .cycle-ring::after,
    .portal-light .platform-venue-ring-center,
    .portal-light .jn-conversion-ring-center,
    .portal-light .fb-rating-gauge-center,
    .portal-light .pl-venue-ring-center {
        background: #FFFFFF !important;
        border-color: var(--portal-border) !important;
    }

    /* TIPTAP brand redesign: one purple identity, restrained semantic colors. */
    .portal-light #mobile-sidebar,
    .portal-light #sidebar {
        background: var(--portal-sidebar) !important;
    }

    .portal-light #mobile-sidebar > div:first-child,
    .portal-light #mobile-sidebar .sidebar-logo-row,
    .portal-light #mobile-sidebar .sidebar-user-area,
    .portal-light #mobile-sidebar .sidebar-logout-area {
        border-color: rgba(255, 255, 255, 0.10) !important;
    }

    .portal-light #mobile-sidebar [class*="text-white"],
    .portal-light #mobile-sidebar .sidebar-logo-text,
    .portal-light #mobile-sidebar .sidebar-user-text,
    .portal-light #mobile-sidebar #sidebar-toggle,
    .portal-light #mobile-sidebar button[onclick="closeSidebar()"],
    .portal-light #sidebar [class*="text-white"] {
        color: rgba(255, 255, 255, 0.88) !important;
    }

    .portal-light #mobile-sidebar [class~="text-white/45"],
    .portal-light #mobile-sidebar [class~="text-white/40"],
    .portal-light #mobile-sidebar [class~="text-white/35"],
    .portal-light #mobile-sidebar [class~="text-white/30"],
    .portal-light #mobile-sidebar [class~="text-white/25"] {
        color: rgba(255, 255, 255, 0.54) !important;
    }

    .portal-light #mobile-sidebar .sidebar-logo-text span {
        color: rgba(255, 255, 255, 0.68) !important;
    }

    .portal-light #mobile-sidebar .sidebar-label p {
        color: rgba(255, 255, 255, 0.48) !important;
        letter-spacing: 0.18em !important;
    }

    .portal-light #mobile-sidebar .sidebar-link {
        color: rgba(255, 255, 255, 0.76) !important;
        border: 1px solid transparent !important;
        min-height: 42px;
    }

    .portal-light #mobile-sidebar .sidebar-link > div:first-child {
        background: rgba(255, 255, 255, 0.09) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        box-shadow: none !important;
    }

    .portal-light #mobile-sidebar .sidebar-link > div:first-child svg {
        color: rgba(255, 255, 255, 0.76) !important;
    }

    .portal-light #mobile-sidebar .sidebar-link:hover {
        background: rgba(255, 255, 255, 0.09) !important;
        color: #FFFFFF !important;
        border-color: rgba(255, 255, 255, 0.06) !important;
    }

    .portal-light #mobile-sidebar .sidebar-link:hover > div:first-child {
        background: rgba(255, 255, 255, 0.14) !important;
    }

    .portal-light #mobile-sidebar .sidebar-link:hover > div:first-child svg {
        color: #FFFFFF !important;
    }

    .portal-light #mobile-sidebar .sidebar-link-active {
        background: rgba(255, 255, 255, 0.96) !important;
        color: var(--fin-primary-deep) !important;
        border-color: rgba(255, 255, 255, 0.82) !important;
        box-shadow: 0 10px 22px -16px rgba(18, 5, 26, 0.55) !important;
    }

    .portal-light #mobile-sidebar .sidebar-link-active::before {
        display: none !important;
    }

    .portal-light #mobile-sidebar .sidebar-link-active > div:first-child {
        background: var(--fin-light) !important;
        border-color: var(--fin-lavender) !important;
    }

    .portal-light #mobile-sidebar .sidebar-link-active > div:first-child svg,
    .portal-light #mobile-sidebar .sidebar-link-active > span:not(.ml-auto) {
        color: var(--fin-primary) !important;
    }

    .portal-light #mobile-sidebar .sidebar-profile-card {
        background: rgba(255, 255, 255, 0.10) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        box-shadow: none !important;
    }

    .portal-light #mobile-sidebar .sidebar-profile-card:hover {
        background: rgba(255, 255, 255, 0.14) !important;
        border-color: rgba(255, 255, 255, 0.20) !important;
        box-shadow: none !important;
    }

    .portal-light #sidebar-profile-avatar-btn,
    .portal-light #mobile-sidebar .sidebar-profile-avatar {
        background: #FFFFFF !important;
        color: var(--fin-primary) !important;
        box-shadow: 0 8px 18px -12px rgba(15, 4, 22, 0.68) !important;
    }

    .portal-light #mobile-sidebar .sidebar-profile-logout {
        color: rgba(255, 255, 255, 0.58) !important;
    }

    .portal-light #mobile-sidebar .sidebar-profile-logout:hover {
        color: #FFFFFF !important;
        background: rgba(255, 255, 255, 0.10) !important;
    }

    .portal-light .sidebar-profile-popover {
        background: #FFFFFF !important;
        border-color: var(--portal-border) !important;
        box-shadow: 0 18px 42px -20px rgba(43, 18, 56, 0.45) !important;
    }

    .portal-light .sidebar-profile-popover-submit {
        color: var(--portal-ink) !important;
    }

    .portal-light #mobile-sidebar img,
    .portal-light #sidebar img {
        box-shadow: 0 8px 20px -14px rgba(14, 4, 22, 0.72);
    }

    .portal-light #sidebar > div,
    .portal-light #sidebar nav {
        color: #FFFFFF;
    }

    .portal-light #sidebar a:not([class~="bg-fin-primary"]) {
        color: rgba(255, 255, 255, 0.76) !important;
    }

    .portal-light #sidebar a:hover {
        color: #FFFFFF !important;
        background: rgba(255, 255, 255, 0.09) !important;
    }

    .portal-light #sidebar [class~="bg-fin-primary"] {
        background: #FFFFFF !important;
        color: var(--fin-primary) !important;
    }

    .portal-light #main-content,
    .portal-light #supervisor-content,
    .portal-light .manager-page {
        color: var(--portal-ink);
    }

    .portal-light .manager-page {
        width: 100%;
        max-width: 1600px;
        margin-inline: auto;
    }

    .portal-light .manager-portal-heading p,
    .portal-light [class*="-portal-heading"] p {
        color: var(--fin-primary) !important;
    }

    .portal-light .manager-portal-heading h1,
    .portal-light [class*="-portal-heading"] h1 {
        color: var(--portal-ink) !important;
    }

    .portal-light .manager-kpi-grid {
        gap: 1rem !important;
        margin-bottom: 2rem !important;
    }

    .portal-light .manager-kpi-card {
        min-height: 178px;
        padding: 1.25rem !important;
        border-top: 3px solid var(--fin-primary) !important;
        box-shadow: var(--portal-shadow-sm) !important;
    }

    .portal-light .manager-kpi-card > .absolute {
        display: none !important;
    }

    .portal-light .manager-kpi-card .relative > .flex:first-child {
        margin-bottom: 1.15rem !important;
    }

    .portal-light .manager-kpi-card .relative > .flex:first-child > div:first-child {
        width: 2.75rem !important;
        height: 2.75rem !important;
        background: var(--fin-light) !important;
        border-color: var(--fin-lavender) !important;
        box-shadow: none !important;
    }

    .portal-light .manager-kpi-card .relative > .flex:first-child > div:first-child svg {
        color: var(--fin-primary) !important;
    }

    .portal-light .manager-kpi-card .relative > .flex:first-child > span {
        background: var(--fin-mist) !important;
        border-color: var(--fin-lavender) !important;
        color: var(--fin-primary) !important;
    }

    .portal-light .manager-kpi-card p[class*="uppercase"] {
        color: var(--portal-muted) !important;
        letter-spacing: 0.06em !important;
    }

    .portal-light .manager-kpi-card h3 {
        color: var(--portal-ink) !important;
        line-height: 1.12;
    }

    .portal-light .manager-live-tracking {
        margin-bottom: 2rem !important;
    }

    .portal-light .manager-live-header {
        margin-bottom: 1rem !important;
    }

    .portal-light .manager-live-header h3 {
        color: var(--portal-ink) !important;
    }

    .portal-light .manager-live-header p {
        color: var(--portal-muted) !important;
        letter-spacing: 0.05em !important;
    }

    .portal-light .manager-order-board {
        gap: 1rem !important;
    }

    .portal-light .manager-status-card {
        min-height: 190px;
        padding: 1.1rem !important;
        border-top-width: 3px !important;
        box-shadow: var(--portal-shadow-sm) !important;
    }

    .portal-light .manager-status-card:nth-child(1) { border-top-color: #F43F5E !important; }
    .portal-light .manager-status-card:nth-child(2) { border-top-color: #F59E0B !important; }
    .portal-light .manager-status-card:nth-child(3) { border-top-color: #10B981 !important; }
    .portal-light .manager-status-card:nth-child(4) { border-top-color: #0EA5A8 !important; }

    .portal-light .manager-status-card > .flex:first-child {
        margin-bottom: 1rem !important;
    }

    .portal-light .manager-status-card .glass {
        background: #FBFAFC !important;
        border-color: var(--portal-border) !important;
        box-shadow: none !important;
    }

    .portal-light .manager-secondary-grid {
        gap: 1rem !important;
    }

    .portal-light .manager-secondary-grid > .glass-card {
        box-shadow: var(--portal-shadow-sm) !important;
    }

    .portal-light .portal-brand-topbar {
        background: linear-gradient(135deg, #52276F 0%, #421E5B 100%) !important;
        color: #FFFFFF !important;
        border-color: rgba(255, 255, 255, 0.10) !important;
        box-shadow: 0 12px 30px -24px rgba(43, 18, 56, 0.75) !important;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    .portal-light .portal-brand-topbar [class*="text-white"] {
        color: rgba(255, 255, 255, 0.88) !important;
    }

    .portal-light .portal-brand-topbar .text-violet-400,
    .portal-light .portal-brand-topbar .gradient-text {
        color: #EEDFF5 !important;
        background: none !important;
        -webkit-text-fill-color: currentColor !important;
    }

    .portal-light .portal-brand-topbar .glass {
        background: rgba(255, 255, 255, 0.10) !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
        color: #FFFFFF !important;
        box-shadow: none !important;
    }

    .portal-light .portal-brand-ambient {
        opacity: 0.18;
    }

    @media (max-width: 767px) {
        .portal-light #mobile-sidebar {
            background: var(--portal-sidebar) !important;
        }

        .portal-light .manager-kpi-card,
        .portal-light .manager-status-card {
            min-height: auto;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .portal-light *,
        .portal-light *::before,
        .portal-light *::after {
            scroll-behavior: auto !important;
        }
    }
</style>
