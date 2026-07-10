<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TIPTAP Manager</title>
    
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        :root {
            --font-family-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-size-page-title: clamp(28px, 2vw + 1rem, 32px);
            --font-size-section-heading: clamp(18px, 1vw + 0.75rem, 20px);
            --font-size-body: 15px;
            --font-size-badge: 11px;
        }

        * {
            font-family: var(--font-family-sans);
        }

        body,
        button,
        input,
        select,
        textarea,
        label {
            font-family: var(--font-family-sans);
            font-weight: 400;
            font-size: var(--font-size-body);
        }

        h1,
        .heading-page {
            font-size: var(--font-size-page-title);
            font-weight: 700;
            letter-spacing: -0.01em;
        }

        h2,
        h3,
        .section-heading {
            font-size: var(--font-size-section-heading);
            font-weight: 600;
        }

        label,
        button,
        .btn,
        .action-link {
            font-weight: 500;
        }

        .badge,
        .status-pill,
        .tag-pill,
        .uppercase-pill {
            font-weight: 500;
            font-size: var(--font-size-badge);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        body { 
            background: #0f0a1e;
            min-height: 100vh;
        }

        /* Premium Scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, rgba(139, 92, 246, 0.5) 0%, rgba(6, 182, 212, 0.5) 100%);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, rgba(139, 92, 246, 0.8) 0%, rgba(6, 182, 212, 0.8) 100%);
        }

        /* Sidebar nav: scroll with pointer/wheel when expanded or collapsed; scrollbar never visible */
        #mobile-sidebar nav.sidebar-nav-scroll {
            scrollbar-width: none;
            -ms-overflow-style: none;
            overscroll-behavior: contain;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #mobile-sidebar nav.sidebar-nav-scroll::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        /* Glassmorphism Effects */
        .glass {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        
        .glass-card {
            background: rgba(28, 22, 51, 0.6);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .bg-surface-900 {
            background: #0f0a1e;
        }

        /* Sidebar Styling */
        .sidebar-gradient {
            background: #0f0a1e;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .sidebar-link {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .sidebar-link:hover {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.1) 0%, transparent 100%);
            color: #fff;
        }
        
        .sidebar-link-active {
            background: linear-gradient(90deg, rgba(139, 92, 246, 0.2) 0%, transparent 100%);
            color: #fff !important;
        }
        
        .sidebar-link-active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: linear-gradient(180deg, #8b5cf6 0%, #06b6d4 100%);
            border-radius: 0 4px 4px 0;
        }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #8b5cf6 0%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(139, 92, 246, 0.3); }
            50% { box-shadow: 0 0 40px rgba(139, 92, 246, 0.5); }
        }

        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-pulse-glow { animation: pulse-glow 2s ease-in-out infinite; }

        /* Card Hover Effects */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
            background: rgba(35, 28, 64, 0.8);
        }

        /* Hide scrollbar utility */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .sidebar-link { min-height: 44px; }
        .sidebar-link:focus { outline: none; box-shadow: 0 0 0 2px #0f0a1e, 0 0 0 4px rgba(139, 92, 246, 0.6); }

        .sidebar-profile-card {
            background: linear-gradient(145deg, rgba(28, 22, 51, 0.85) 0%, rgba(15, 10, 30, 0.95) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow:
                0 4px 24px -4px rgba(0, 0, 0, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.06);
        }

        .sidebar-profile-card:hover {
            border-color: rgba(139, 92, 246, 0.2);
            box-shadow:
                0 8px 28px -6px rgba(139, 92, 246, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
        }

        .sidebar-profile-avatar {
            background: linear-gradient(135deg, #7c3aed 0%, #06b6d4 100%);
            box-shadow: 0 4px 14px -2px rgba(124, 58, 237, 0.45);
        }

        .sidebar-profile-logout {
            color: rgba(255, 255, 255, 0.35);
            transition: color 0.2s ease, background-color 0.2s ease, transform 0.2s ease;
        }

        .sidebar-profile-logout:hover {
            color: #f87171;
            background-color: rgba(248, 113, 113, 0.1);
            transform: translateX(1px);
        }

        #mobile-sidebar.sidebar-collapsed .sidebar-profile-card {
            padding: 0.5rem;
            justify-content: center;
        }

        #mobile-sidebar.sidebar-collapsed .sidebar-profile-logout-form {
            display: none;
        }

        .sidebar-user-area {
            position: relative;
        }

        .sidebar-profile-popover {
            position: fixed;
            z-index: 200;
            min-width: 10.5rem;
            padding: 0.35rem;
            border-radius: 0.75rem;
            background: linear-gradient(145deg, rgba(28, 22, 51, 0.98) 0%, rgba(15, 10, 30, 0.99) 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 12px 40px -8px rgba(0, 0, 0, 0.65);
        }

        .sidebar-profile-popover.hidden {
            display: none;
        }

        .sidebar-profile-popover-submit {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.625rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
            transition: color 0.2s ease, background-color 0.2s ease;
        }

        .sidebar-profile-popover-submit:hover {
            color: #fca5a5;
            background-color: rgba(248, 113, 113, 0.12);
        }

        #sidebar-profile-avatar-btn {
            cursor: pointer;
            border: none;
            padding: 0;
            background: transparent;
        }

        #sidebar-profile-avatar-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 2px #0f0a1e, 0 0 0 4px rgba(139, 92, 246, 0.55);
            border-radius: 0.75rem;
        }
        /* Sidebar visibility: NOT relying on Tailwind – layout CSS only */
        #mobile-sidebar { transition: transform 0.3s ease-out, width 0.3s ease-out; }
        /* Mobile: closed by default */
        #mobile-sidebar.sidebar-closed-mobile { transform: translateX(-100%) !important; }
        /* Mobile: open when user clicks menu */
        #mobile-sidebar.sidebar-open { transform: translateX(0) !important; visibility: visible !important; }
        /* Desktop (768px+): always visible */
        @media (min-width: 768px) {
            #mobile-sidebar,
            #mobile-sidebar.sidebar-closed-mobile { transform: translateX(0) !important; visibility: visible !important; }
            #mobile-sidebar.sidebar-collapsed { width: 3.5rem !important; }
            main#main-content { margin-left: 12rem; }
            body.sidebar-collapsed-main main#main-content { margin-left: 3.5rem; }
        }
        body.sidebar-mobile-open #sidebar-overlay { display: block !important; opacity: 1 !important; pointer-events: auto !important; }
        @media (max-width: 767px) {
            body.sidebar-mobile-open { overflow: hidden; }
        }

        main#main-content {
            min-width: 0;
            width: 100%;
        }

        .manager-portal-heading {
            min-width: 0;
        }

        .manager-portal-heading h1 {
            font-size: clamp(1.25rem, 2.5vw + 0.5rem, 1.875rem);
            line-height: 1.2;
            word-break: break-word;
        }

        /* Sidebar collapsed (desktop): narrow, icons only */
        #mobile-sidebar.sidebar-collapsed { width: 3.5rem; }
        #mobile-sidebar.sidebar-collapsed .sidebar-link span,
        #mobile-sidebar.sidebar-collapsed .sidebar-label,
        #mobile-sidebar.sidebar-collapsed .sidebar-user-text { display: none !important; }
        #mobile-sidebar.sidebar-collapsed .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; margin-left: 0.5rem; margin-right: 0.5rem; }
        #mobile-sidebar.sidebar-collapsed .sidebar-link > div:first-child { margin: 0; }
        #mobile-sidebar.sidebar-collapsed nav .px-6 { padding-left: 0; padding-right: 0; }
        #mobile-sidebar.sidebar-collapsed .sidebar-user-area .flex-1 { display: none; }
        #mobile-sidebar.sidebar-collapsed .sidebar-user-area { justify-content: center; }
        body.sidebar-collapsed-main main#main-content { margin-left: 3.5rem; }

        #mobile-sidebar.sidebar-collapsed .sidebar-logo-row {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        #mobile-sidebar.sidebar-collapsed .sidebar-logo-row .sidebar-logo-spacer,
        #mobile-sidebar.sidebar-collapsed .sidebar-logo-row #sidebar-toggle {
            display: none !important;
        }

        #sidebar-logo-toggle {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        #sidebar-logo-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.25);
        }

        #sidebar-logo-toggle:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body class="font-sans antialiased text-white min-h-screen pt-[env(safe-area-inset-top)] pl-[env(safe-area-inset-left)] pr-[env(safe-area-inset-right)] pb-[env(safe-area-inset-bottom)]">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-violet-600 focus:text-white focus:rounded-xl focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 focus:ring-offset-[#0f0a1e] focus:outline-none">Skip to main content</a>
    
    <!-- Overlay (mobile only) -->
    <div id="sidebar-overlay" onclick="closeSidebar()" class="fixed inset-0 bg-black/70 z-40 backdrop-blur-sm hidden md:hidden transition-opacity duration-300 opacity-0 cursor-pointer" aria-hidden="true"></div>

    @php
        $managerUser = Auth::user();
        $managerUser?->loadMissing('restaurant');
        $managerRestaurantName = $managerUser?->restaurant?->name;
    @endphp

    <div class="flex min-h-screen">
        <!-- Premium Manager Sidebar: drawer on mobile, persistent on md+ with toggle -->
        <aside id="mobile-sidebar" class="fixed inset-y-0 left-0 z-[100] w-[min(252px,85vw)] md:w-48 bg-surface-900/95 backdrop-blur-xl border-r border-white/10 flex flex-col overflow-hidden sidebar-closed-mobile" role="navigation" aria-label="Manager navigation">
            <!-- Logo Area (click logo to expand/collapse on desktop) -->
            <div class="px-4 py-4 flex items-center gap-2 border-b border-white/5 shrink-0 sidebar-logo-row">
                <button
                    type="button"
                    id="sidebar-logo-toggle"
                    class="w-10 h-10 flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-white focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-[#0f0a1e]"
                    aria-label="Toggle sidebar"
                    title="Toggle sidebar"
                >
                    <img src="{{ asset('images/logo.png') }}" alt="TIPTAP" class="w-full h-full object-contain">
                </button>
                <div class="flex-1 sidebar-logo-spacer"></div>
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" id="sidebar-toggle" class="hidden md:flex min-h-[44px] min-w-[44px] items-center justify-center p-2.5 text-white/40 hover:text-white hover:bg-white/10 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-[#0f0a1e]" aria-label="Collapse sidebar" title="Collapse sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="sidebar-toggle-icon-collapse" title="Collapse"><path d="m15 18-6-6 6-6"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="sidebar-toggle-icon-expand" class="hidden" title="Expand"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                    <button type="button" onclick="closeSidebar()" class="md:hidden min-h-[44px] min-w-[44px] inline-flex items-center justify-center p-2.5 text-white/40 hover:text-white hover:bg-white/10 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-[#0f0a1e]" aria-label="Close menu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 py-4 sidebar-nav-scroll overflow-y-auto overflow-x-hidden">
                <div class="mb-3 px-5 sidebar-label">
                    <p class="text-[9px] font-bold text-white/25 uppercase tracking-[0.25em]">Main Menu</p>
                </div>

                <a href="{{ route('manager.dashboard') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.dashboard') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-violet-500/20 to-cyan-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.dashboard') ? 'text-violet-400' : 'text-white/50' }}">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Dashboard</span>
                </a>

                <a href="{{ route('manager.orders.live') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.orders.live') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-amber-500/20 to-orange-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.orders.live') ? 'text-amber-400' : 'text-white/50' }}">
                            <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Live Orders</span>
                </a>

                <a href="{{ route('manager.orders.history') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.orders.history') || request()->routeIs('manager.orders.show') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-cyan-500/20 to-blue-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.orders.history') || request()->routeIs('manager.orders.show') ? 'text-cyan-400' : 'text-white/50' }}">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Order History</span>
                </a>

                <a href="{{ route('manager.menu.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.menu.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.menu.index') ? 'text-emerald-400' : 'text-white/50' }}">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Dishes</span>
                </a>

                <a href="{{ route('manager.menu-pdf.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.menu-pdf.*') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-rose-500/20 to-orange-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.menu-pdf.*') ? 'text-rose-400' : 'text-white/50' }}">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M10 13H8"/><path d="M16 13h-2"/><path d="M10 17H8"/><path d="M16 17h-2"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Menu PDF</span>
                </a>

                <a href="{{ route('manager.waiters.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.waiters.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-blue-500/20 to-indigo-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.waiters.index') ? 'text-blue-400' : 'text-white/50' }}">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Waiters & Staff</span>
                </a>

                <a href="{{ route('manager.roster.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.roster.*') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-teal-500/20 to-emerald-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.roster.*') ? 'text-teal-400' : 'text-white/50' }}">
                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Waiter Roster</span>
                </a>

                <a href="{{ route('manager.menu-engagement.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.menu-engagement.*') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-amber-500/20 to-orange-500/20 flex items-center justify-center shrink-0 relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.menu-engagement.*') ? 'text-amber-400' : 'text-white/50' }}">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                        </svg>
                        @if(($menuEngagementUnread ?? 0) > 0)
                            <span class="absolute -top-1 -right-1 min-w-[16px] h-4 px-1 rounded-full bg-amber-400 text-[9px] font-bold text-black flex items-center justify-center">{{ $menuEngagementUnread > 9 ? '9+' : $menuEngagementUnread }}</span>
                        @endif
                    </div>
                    <span class="font-medium text-xs">Customer Engagement</span>
                </a>

                @if(auth()->user()?->isBranchManager())
                <a href="{{ route('manager.branches.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.branches.*') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-cyan-500/20 to-blue-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.branches.*') ? 'text-cyan-400' : 'text-white/50' }}">
                            <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">My Branches</span>
                </a>
                @endif

                <a href="{{ route('manager.floor-supervisors.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.floor-supervisors.*') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-indigo-500/20 to-violet-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.floor-supervisors.*') ? 'text-indigo-400' : 'text-white/50' }}">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Floor Supervisors</span>
                </a>

                <a href="{{ route('manager.tables.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.tables.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.tables.index') ? 'text-purple-400' : 'text-white/50' }}">
                            <rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Tables & QR Codes</span>
                </a>

                <div class="mt-5 mb-3 px-5 sidebar-label">
                    <p class="text-[9px] font-bold text-white/25 uppercase tracking-[0.25em]">Finance</p>
                </div>

                <a href="{{ route('manager.payroll.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.payroll.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-amber-500/20 to-yellow-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.payroll.index') ? 'text-amber-400' : 'text-white/50' }}">
                            <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Payroll</span>
                </a>

                <a href="{{ route('manager.payroll.history') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.payroll.history') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-slate-500/20 to-zinc-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.payroll.history') ? 'text-slate-300' : 'text-white/50' }}">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Payroll History</span>
                </a>

                <a href="{{ route('manager.wallet.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.wallet.*') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-emerald-500/20 to-teal-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.wallet.*') ? 'text-emerald-400' : 'text-white/50' }}">
                            <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Wallet</span>
                </a>

                <a href="{{ route('manager.payments.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.payments.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-pink-500/20 to-rose-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.payments.index') ? 'text-pink-400' : 'text-white/50' }}">
                            <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Payments</span>
                </a>

                <div class="mt-5 mb-3 px-5 sidebar-label">
                    <p class="text-[9px] font-bold text-white/25 uppercase tracking-[0.25em]">Other</p>
                </div>

                <a href="{{ route('manager.feedback.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.feedback.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-cyan-500/20 to-teal-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.feedback.index') ? 'text-cyan-400' : 'text-white/50' }}">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Customer Feedback</span>
                </a>

                <a href="{{ route('manager.food-ratings.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.food-ratings.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-amber-500/20 to-orange-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.food-ratings.index') ? 'text-amber-400' : 'text-white/50' }}">
                            <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Food Ratings</span>
                </a>

                <a href="{{ route('manager.api.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.api.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-violet-500/20 to-purple-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.api.index') ? 'text-violet-400' : 'text-white/50' }}">
                            <rect width="5" height="5" x="3" y="3" rx="1"/><rect width="5" height="5" x="16" y="3" rx="1"/><rect width="5" height="5" x="3" y="16" rx="1"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><path d="M21 21v.01"/><path d="M12 7v3a2 2 0 0 1-2 2H7"/><path d="M3 12h.01"/><path d="M12 3h.01"/><path d="M12 16v.01"/><path d="M16 12h1"/><path d="M21 12v.01"/><path d="M12 21v-1"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">API Settings</span>
                </a>

                <a href="{{ route('manager.tips.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.tips.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-yellow-500/20 to-amber-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.tips.index') ? 'text-yellow-400' : 'text-white/50' }}">
                            <circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/><path d="m16.71 13.88.7.71-2.82 2.82"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Tips</span>
                </a>

                <a href="{{ route('manager.reports.performance') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.reports.*') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-indigo-500/20 to-violet-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.reports.*') ? 'text-indigo-400' : 'text-white/50' }}">
                            <path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Reports</span>
                </a>

                <a href="{{ route('manager.help.index') }}" onclick="closeSidebar()" class="sidebar-link flex items-center gap-2.5 px-4 py-2 mx-2 rounded-lg {{ request()->routeIs('manager.help.index') ? 'sidebar-link-active' : 'text-white/55' }}">
                    <div class="w-7 h-7 rounded-md bg-gradient-to-br from-sky-500/20 to-blue-500/20 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ request()->routeIs('manager.help.index') ? 'text-sky-400' : 'text-white/50' }}">
                            <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>
                        </svg>
                    </div>
                    <span class="font-medium text-xs">Help & Docs</span>
                </a>
            </nav>

            <!-- User Profile Area -->
            <div class="p-3 pt-2 border-t border-white/5 shrink-0 sidebar-user-area">
                <div id="sidebar-profile-popover" class="sidebar-profile-popover hidden" role="menu" aria-label="Account menu">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sidebar-profile-popover-submit" role="menuitem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-rose-400">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" x2="9" y1="12" y2="12"/>
                            </svg>
                            Sign out
                        </button>
                    </form>
                </div>
                <div class="sidebar-profile-card rounded-2xl p-3.5 flex items-center gap-3 transition-all duration-300">
                    <button
                        type="button"
                        id="sidebar-profile-avatar-btn"
                        class="sidebar-profile-avatar w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-white text-base font-semibold lowercase select-none"
                        aria-label="Account menu"
                        aria-expanded="false"
                        aria-controls="sidebar-profile-popover"
                    >
                        {{ strtolower(substr($managerUser->name, 0, 1)) }}
                    </button>
                    <div class="flex-1 min-w-0 sidebar-user-text">
                        <p class="text-sm font-bold text-white leading-tight truncate" title="{{ $managerUser->name }}">
                            {{ $managerUser->name }}
                        </p>
                        <p class="text-[11px] font-medium text-white/45 leading-snug truncate mt-0.5" title="{{ $managerRestaurantName ?? $managerUser->email }}">
                            {{ $managerRestaurantName ?? $managerUser->email }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="sidebar-profile-logout-form shrink-0">
                        @csrf
                        <button
                            type="submit"
                            class="sidebar-profile-logout min-h-[40px] min-w-[40px] inline-flex items-center justify-center rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:ring-offset-2 focus:ring-offset-[#0f0a1e]"
                            aria-label="Log out"
                            title="Log out"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" x2="9" y1="12" y2="12"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main id="main-content" class="flex-1 min-h-screen flex flex-col w-full relative z-0 transition-[margin] duration-300 md:ml-48 portal-ambient" tabindex="-1">
            <!-- Mobile Header -->
            <div class="md:hidden glass sticky top-0 z-30 px-4 py-3 flex items-center gap-3 min-w-0">
                <button type="button" onclick="openSidebar()" class="shrink-0 min-h-[44px] min-w-[44px] inline-flex items-center justify-center p-2.5 bg-gradient-to-r from-violet-600 to-cyan-600 text-white rounded-xl shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 transition-all active:scale-95 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-[#0f0a1e]" aria-label="Open menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>
                    </svg>
                </button>
                <div class="flex-1 min-w-0 manager-portal-heading">
                    <p class="text-[10px] font-semibold text-violet-400 uppercase tracking-wide mb-0.5">Manager Portal</p>
                    <h1 class="font-bold text-white tracking-tight break-words">{{ $header ?? 'Dashboard' }}</h1>
                </div>
                <x-branch-switcher />
            </div>

            <!-- Desktop Header & Content -->
            <div class="p-4 md:p-6 lg:p-8 flex-1">
                <!-- Desktop Top Bar -->
                <div class="hidden md:flex justify-between items-start gap-4 mb-8 min-w-0">
                    <div class="min-w-0 flex-1 manager-portal-heading">
                        <p class="text-[11px] font-semibold text-violet-400 uppercase tracking-wide mb-1">Manager Portal</p>
                        <h1 class="font-bold text-white tracking-tight break-words">{{ $header ?? 'Dashboard' }}</h1>
                    </div>

                    <div class="flex items-center gap-5 shrink-0">
                        <x-branch-switcher />
                        <div class="glass px-4 py-2.5 rounded-xl flex items-center gap-3">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                            <span class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">System Live</span>
                        </div>
                    </div>
                </div>

                @if(session('impersonator_id'))
                    <div class="mb-6 p-4 rounded-xl border border-amber-500/30 bg-amber-500/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <p class="text-xs font-black text-amber-400 uppercase tracking-wider">Impersonation mode</p>
                            <p class="text-sm text-white/80 mt-0.5">You are viewing this portal as <strong class="text-white">{{ Auth::user()->name }}</strong>. Changes apply to their account.</p>
                        </div>
                        <form method="POST" action="{{ route('impersonate.stop') }}" class="shrink-0">@csrf
                            <button type="submit" class="px-4 py-2 bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 rounded-lg text-xs font-bold uppercase tracking-wider border border-amber-500/30">Exit impersonation</button>
                        </form>
                    </div>
                @endif

                <div class="manager-page">
                    {{ $slot }}
                </div>
            </div>

            @if(session('status'))
                <div id="toast-status" class="fixed bottom-8 right-8 z-[200] animate-float">
                    <div class="glass-card px-6 py-4 rounded-2xl border-violet-500/20 flex items-center gap-4 shadow-2xl shadow-violet-500/10">
                        <div class="w-10 h-10 bg-violet-500/20 rounded-xl flex items-center justify-center text-violet-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white uppercase tracking-wider">Welcome</p>
                            <p class="text-sm text-white/60">{{ session('status') }}</p>
                        </div>
                    </div>
                </div>
                <script>setTimeout(() => document.getElementById('toast-status')?.remove(), 6000);</script>
            @endif

            <!-- Toast Notifications -->
            @if(session('success'))
                <div id="toast-success" class="fixed bottom-8 right-8 z-[200] animate-float">
                    <div class="glass-card px-6 py-4 rounded-2xl border-emerald-500/20 flex items-center gap-4 shadow-2xl shadow-emerald-500/10">
                        <div class="w-10 h-10 bg-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white uppercase tracking-wider">Success</p>
                            <p class="text-sm text-white/60">{{ session('success') }}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white/20 hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </div>
                <script>setTimeout(() => document.getElementById('toast-success')?.remove(), 5000);</script>
            @endif

            @if(session('error'))
                <div id="toast-error" class="fixed bottom-8 right-8 z-[200] animate-float">
                    <div class="glass-card px-6 py-4 rounded-2xl border-rose-500/20 flex items-center gap-4 shadow-2xl shadow-rose-500/10">
                        <div class="w-10 h-10 bg-rose-500/20 rounded-xl flex items-center justify-center text-rose-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white uppercase tracking-wider">Error</p>
                            <p class="text-sm text-white/60">{{ session('error') }}</p>
                        </div>
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white/20 hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-white/30 rounded" aria-label="Dismiss">×</button>
                    </div>
                </div>
                <script>setTimeout(() => document.getElementById('toast-error')?.remove(), 5000);</script>
            @endif
            @if(session('info'))
                <div id="toast-info" class="fixed bottom-8 right-8 z-[200] animate-float">
                    <div class="glass-card px-6 py-4 rounded-2xl border-cyan-500/20 flex items-center gap-4 shadow-2xl shadow-cyan-500/10">
                        <div class="w-10 h-10 bg-cyan-500/20 rounded-xl flex items-center justify-center text-cyan-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-white uppercase tracking-wider">Info</p>
                            <p class="text-sm text-white/60">{{ session('info') }}</p>
                        </div>
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white/20 hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-white/30 rounded" aria-label="Dismiss">×</button>
                    </div>
                </div>
                <script>setTimeout(() => document.getElementById('toast-info')?.remove(), 5000);</script>
            @endif
        </main>
    </div>

    <script>
        function openSidebar() {
            const sidebar = document.getElementById('mobile-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (!sidebar || !overlay) return;
            sidebar.classList.remove('sidebar-closed-mobile');
            sidebar.classList.add('sidebar-open');
            document.body.classList.add('sidebar-mobile-open');
            overlay.classList.remove('hidden');
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            const sidebar = document.getElementById('mobile-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (!sidebar || !overlay) return;
            sidebar.classList.remove('sidebar-open');
            sidebar.classList.add('sidebar-closed-mobile');
            document.body.classList.remove('sidebar-mobile-open');
            overlay.classList.remove('opacity-100');
            overlay.classList.add('opacity-0');
            setTimeout(function() { overlay.classList.add('hidden'); }, 300);
            document.body.style.overflow = '';
            closeProfilePopover();
        }
        function isSidebarVisible() {
            var el = document.getElementById('mobile-sidebar');
            if (!el) return false;
            var r = el.getBoundingClientRect();
            return r.left >= -10 && r.width > 0;
        }
        function toggleSidebar() {
            if (document.getElementById('mobile-sidebar').classList.contains('sidebar-open')) closeSidebar();
            else openSidebar();
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProfilePopover();
                closeSidebar();
            }
        });

        // Sidebar toggle (collapse/expand) – desktop only, persisted
        var STORAGE_KEY = 'managerSidebarCollapsed';
        function isSidebarCollapsed() {
            return document.getElementById('mobile-sidebar').classList.contains('sidebar-collapsed');
        }
        function closeProfilePopover() {
            var popover = document.getElementById('sidebar-profile-popover');
            var avatarBtn = document.getElementById('sidebar-profile-avatar-btn');
            if (popover) {
                popover.classList.add('hidden');
            }
            if (avatarBtn) {
                avatarBtn.setAttribute('aria-expanded', 'false');
            }
        }

        function positionProfilePopover() {
            var popover = document.getElementById('sidebar-profile-popover');
            var avatarBtn = document.getElementById('sidebar-profile-avatar-btn');
            var sidebar = document.getElementById('mobile-sidebar');
            if (!popover || !avatarBtn || !sidebar || sidebar.classList.contains('sidebar-closed-mobile')) {
                return;
            }
            var rect = avatarBtn.getBoundingClientRect();
            popover.style.left = (rect.right + 8) + 'px';
            popover.style.top = Math.max(8, rect.top - 4) + 'px';
            popover.style.bottom = 'auto';
        }

        function toggleProfilePopover() {
            var sidebar = document.getElementById('mobile-sidebar');
            var popover = document.getElementById('sidebar-profile-popover');
            var avatarBtn = document.getElementById('sidebar-profile-avatar-btn');
            if (!sidebar || !popover || !avatarBtn || !sidebar.classList.contains('sidebar-collapsed')) {
                return;
            }
            var willOpen = popover.classList.contains('hidden');
            if (willOpen) {
                positionProfilePopover();
                popover.classList.remove('hidden');
                avatarBtn.setAttribute('aria-expanded', 'true');
            } else {
                closeProfilePopover();
            }
        }

        function setSidebarCollapsed(collapsed) {
            var sidebar = document.getElementById('mobile-sidebar');
            var iconCollapse = document.getElementById('sidebar-toggle-icon-collapse');
            var iconExpand = document.getElementById('sidebar-toggle-icon-expand');
            if (collapsed) {
                sidebar.classList.add('sidebar-collapsed');
                document.body.classList.add('sidebar-collapsed-main');
                if (iconCollapse) iconCollapse.classList.add('hidden');
                if (iconExpand) iconExpand.classList.remove('hidden');
                try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-collapsed-main');
                if (iconCollapse) iconCollapse.classList.remove('hidden');
                if (iconExpand) iconExpand.classList.add('hidden');
                try { localStorage.setItem(STORAGE_KEY, '0'); } catch (e) {}
                closeProfilePopover();
            }
        }
        function toggleManagerSidebar() {
            setSidebarCollapsed(!isSidebarCollapsed());
        }
        function isDesktopLayout() {
            return window.matchMedia('(min-width: 768px)').matches;
        }
        function handleLogoSidebarToggle() {
            if (isDesktopLayout()) {
                toggleManagerSidebar();
            } else {
                toggleSidebar();
            }
        }
        document.getElementById('sidebar-toggle')?.addEventListener('click', toggleManagerSidebar);
        document.getElementById('sidebar-logo-toggle')?.addEventListener('click', handleLogoSidebarToggle);
        document.getElementById('sidebar-profile-avatar-btn')?.addEventListener('click', function (event) {
            event.stopPropagation();
            toggleProfilePopover();
        });
        document.addEventListener('click', function (event) {
            var popover = document.getElementById('sidebar-profile-popover');
            var avatarBtn = document.getElementById('sidebar-profile-avatar-btn');
            if (!popover || popover.classList.contains('hidden')) {
                return;
            }
            if (popover.contains(event.target) || avatarBtn?.contains(event.target)) {
                return;
            }
            closeProfilePopover();
        });
        window.addEventListener('resize', function () {
            var popover = document.getElementById('sidebar-profile-popover');
            if (popover && !popover.classList.contains('hidden')) {
                positionProfilePopover();
            }
        });
        (function applySavedSidebarState() {
            try {
                if (localStorage.getItem(STORAGE_KEY) === '1') setSidebarCollapsed(true);
            } catch (e) {}
        })();

        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
