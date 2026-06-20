<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TIPTAP Admin</title>
    
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    @include('partials.brand-icons')
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.portal-theme')
    @stack('styles')
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        
        body { 
            background: #12101c;
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
            background: linear-gradient(180deg, rgba(140, 113, 246, 0.5) 0%, rgba(109, 82, 232, 0.5) 100%);
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, rgba(140, 113, 246, 0.8) 0%, rgba(109, 82, 232, 0.8) 100%);
        }

        /* Sidebar nav: scroll when expanded or collapsed; scrollbar never visible */
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

        /* Sidebar Styling */
        .sidebar-gradient {
            background: #12101c;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .sidebar-link {
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 44px;
        }
        .sidebar-link:focus {
            outline: none;
            box-shadow: 0 0 0 2px #12101c, 0 0 0 4px rgba(140, 113, 246, 0.6);
        }
        .sidebar-link:hover {
            background: linear-gradient(90deg, rgba(140, 113, 246, 0.1) 0%, transparent 100%);
            color: #fff;
        }
        
        .sidebar-link-active {
            background: linear-gradient(90deg, rgba(140, 113, 246, 0.2) 0%, transparent 100%);
            color: #fff !important;
            border-left: 3px solid #8C71F6;
        }
        
        .sidebar-link-active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: linear-gradient(180deg, #8C71F6 0%, #6D52E8 100%);
            border-radius: 0 4px 4px 0;
        }

        /* Stat Card Glow Effects */
        .stat-glow-purple { box-shadow: 0 0 40px -10px rgba(140, 113, 246, 0.4); }
        .stat-glow-cyan { box-shadow: 0 0 40px -10px rgba(109, 82, 232, 0.4); }
        .stat-glow-emerald { box-shadow: 0 0 40px -10px rgba(16, 185, 129, 0.4); }
        .stat-glow-amber { box-shadow: 0 0 40px -10px rgba(245, 158, 11, 0.4); }

        /* Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #8C71F6 0%, #6D52E8 100%);
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
            0%, 100% { box-shadow: 0 0 20px rgba(140, 113, 246, 0.3); }
            50% { box-shadow: 0 0 40px rgba(140, 113, 246, 0.5); }
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

        .bg-surface-900 { background: #12101c; }
        #mobile-sidebar { transition: transform 0.3s ease-out, width 0.3s ease-out; }
        #mobile-sidebar.sidebar-closed-mobile { transform: translateX(-100%) !important; }
        #mobile-sidebar.sidebar-open { transform: translateX(0) !important; visibility: visible !important; }
        @media (min-width: 768px) {
            #mobile-sidebar,
            #mobile-sidebar.sidebar-closed-mobile { transform: translateX(0) !important; visibility: visible !important; }
            #mobile-sidebar.sidebar-collapsed { width: 3.5rem !important; }
            main#main-content { margin-left: 13rem; }
            body.sidebar-collapsed-main main#main-content { margin-left: 3.5rem; }
        }
        body.sidebar-mobile-open #sidebar-overlay { display: block !important; opacity: 1 !important; pointer-events: auto !important; }
        #mobile-sidebar.sidebar-collapsed { width: 3.5rem; }
        #mobile-sidebar.sidebar-collapsed .sidebar-link span,
        #mobile-sidebar.sidebar-collapsed .sidebar-label,
        #mobile-sidebar.sidebar-collapsed .sidebar-logo-text,
        #mobile-sidebar.sidebar-collapsed .sidebar-logout-text,
        #mobile-sidebar.sidebar-collapsed .sidebar-link .absolute { display: none !important; }
        #mobile-sidebar.sidebar-collapsed .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; margin-left: 0.5rem; margin-right: 0.5rem; }
        #mobile-sidebar.sidebar-collapsed .sidebar-link > div:first-child { margin: 0; }
        #mobile-sidebar.sidebar-collapsed nav .px-6 { padding-left: 0; padding-right: 0; }
        #mobile-sidebar.sidebar-collapsed .sidebar-logout-area .sidebar-logout-text { display: none; }
        #mobile-sidebar.sidebar-collapsed .sidebar-logout-area button { justify-content: center; padding: 0.75rem; }
        body.sidebar-collapsed-main main#main-content { margin-left: 5rem; }
    </style>
</head>
<body class="font-sans antialiased text-white min-h-screen pt-[env(safe-area-inset-top)] pl-[env(safe-area-inset-left)] pr-[env(safe-area-inset-right)] pb-[env(safe-area-inset-bottom)]">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-violet-600 focus:text-white focus:rounded-xl focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 focus:ring-offset-[#12101c] focus:outline-none">Skip to main content</a>
    
    <!-- Overlay (mobile only) -->
    <div id="sidebar-overlay" onclick="closeSidebar()" class="fixed inset-0 bg-black/60 z-40 backdrop-blur-sm hidden md:hidden transition-opacity duration-300 opacity-0 cursor-pointer" aria-hidden="true"></div>

    <div class="flex min-h-screen">
        <!-- Premium Dark Sidebar: drawer on mobile, persistent on md+ with toggle -->
        <aside id="mobile-sidebar" class="fixed inset-y-0 left-0 z-[100] w-52 sidebar-gradient flex flex-col h-screen shadow-2xl shadow-black/50 border-r border-white/5 sidebar-closed-mobile" style="width: 13rem;">
            <div class="p-4 pb-3 flex justify-between items-center border-b border-white/5 shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 flex shrink-0 items-center justify-center overflow-hidden rounded-full">
                        <img src="{{ public_asset('images/logo.png') }}" alt="TIPTAP Logo" class="w-full h-full object-contain bg-white">
                    </div>
                    <div class="sidebar-logo-text min-w-0">
                        <span class="text-base font-black text-white tracking-tight block leading-none hidden">TIP<span class="gradient-text">TAP</span></span>
                        <span class="text-[9px] font-semibold text-white/40 uppercase tracking-[0.2em]">Super Admin</span>
                    </div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" id="sidebar-toggle" class="hidden md:flex min-h-[44px] min-w-[44px] items-center justify-center p-2.5 text-white/40 hover:text-white hover:bg-white/10 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-[#12101c]" aria-label="Collapse sidebar" title="Collapse sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="sidebar-toggle-icon-collapse" title="Collapse"><path d="m15 18-6-6 6-6"/></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="sidebar-toggle-icon-expand" class="hidden" title="Expand"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                    <button type="button" onclick="closeSidebar()" class="md:hidden min-h-[44px] min-w-[44px] inline-flex items-center justify-center p-2.5 text-white/40 hover:text-white hover:bg-white/10 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-[#12101c]" aria-label="Close menu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="flex-1 py-4 sidebar-nav-scroll overflow-y-auto overflow-x-hidden">
                @include('admin.partials.sidebar-nav')
            </nav>

            <div class="p-3 border-t border-white/5 shrink-0 sidebar-logout-area">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-white/50 hover:text-red-400 hover:bg-red-500/10 transition-all font-medium text-xs group">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="group-hover:-translate-x-1 transition-transform shrink-0">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
                        </svg>
                        <span class="sidebar-logout-text">Sign Out</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main id="main-content" class="flex-1 min-h-screen flex flex-col w-full relative z-0 transition-[margin] duration-300 md:ml-52" tabindex="-1">
            <!-- Mobile Header -->
            <div class="md:hidden glass sticky top-0 z-30 px-4 py-3 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="openSidebar()" class="min-h-[44px] min-w-[44px] inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 transition-all active:scale-95 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 focus:ring-offset-[#12101c]" aria-label="Open menu">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>
                        </svg>
                    </button>
                    <span class="font-bold text-white/90 text-lg tracking-tight">TIP<span class="gradient-text">TAP</span></span>
                </div>
                    <div class="w-9 h-9 flex items-center justify-center overflow-hidden rounded-full">
                        <img src="{{ public_asset('images/logo.png') }}" alt="TIPTAP Logo" class="w-full h-full object-contain bg-white">
                    </div>
            </div>

            <!-- Desktop Header & Content -->
            <div class="p-4 md:p-8 flex-1">
                <div class="hidden md:flex justify-between items-center mb-8">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-violet-400 uppercase tracking-[0.15em] mb-1">System Overview</p>
                        <h1 class="text-3xl font-bold text-white tracking-tight break-words">{{ $header ?? 'Dashboard' }}</h1>
                    </div>
                    
                    <div class="flex items-center gap-5">
                        <div class="glass px-4 py-2.5 rounded-xl flex items-center gap-3">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                            <span class="text-[11px] font-semibold text-white/80 uppercase tracking-wider">System Online</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-sm font-semibold text-white leading-none mb-1">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] font-medium text-violet-400 uppercase tracking-wider">SUPER ADMIN</p>
                            </div>
                            <div class="w-11 h-11 bg-gradient-to-br from-fin-primary to-fin-primary-dark rounded-xl flex items-center justify-center shadow-lg shadow-violet-500/30 text-white font-bold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        </div>
                    </div>
                </div>

                {{ $slot }}
            </div>

            <!-- Toast Notifications (same pattern as Waiter/Manager) -->
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
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white/20 hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-white/30 rounded" aria-label="Dismiss">×</button>
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
        }
        function toggleSidebar() {
            if (document.getElementById('mobile-sidebar').classList.contains('sidebar-open')) closeSidebar();
            else openSidebar();
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSidebar();
            }
        });

        var ADMIN_SIDEBAR_KEY = 'adminSidebarCollapsed';
        function isAdminSidebarCollapsed() { return document.getElementById('mobile-sidebar').classList.contains('sidebar-collapsed'); }
        function setAdminSidebarCollapsed(collapsed) {
            var s = document.getElementById('mobile-sidebar');
            var ic = document.getElementById('sidebar-toggle-icon-collapse');
            var ie = document.getElementById('sidebar-toggle-icon-expand');
            if (collapsed) {
                s.classList.add('sidebar-collapsed');
                document.body.classList.add('sidebar-collapsed-main');
                if (ic) ic.classList.add('hidden');
                if (ie) ie.classList.remove('hidden');
                try { localStorage.setItem(ADMIN_SIDEBAR_KEY, '1'); } catch (e) {}
            } else {
                s.classList.remove('sidebar-collapsed');
                document.body.classList.remove('sidebar-collapsed-main');
                if (ic) ic.classList.remove('hidden');
                if (ie) ie.classList.add('hidden');
                try { localStorage.setItem(ADMIN_SIDEBAR_KEY, '0'); } catch (e) {}
            }
        }
        function toggleAdminSidebar() { setAdminSidebarCollapsed(!isAdminSidebarCollapsed()); }
        document.getElementById('sidebar-toggle') && document.getElementById('sidebar-toggle').addEventListener('click', toggleAdminSidebar);
        try { if (localStorage.getItem(ADMIN_SIDEBAR_KEY) === '1') setAdminSidebarCollapsed(true); } catch (e) {}

        window.addEventListener('load', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
