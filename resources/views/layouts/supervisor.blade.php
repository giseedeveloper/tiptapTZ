<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Supervisor &mdash; TIPTAP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        /* Glassmorphism */
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

        /* Card Hover */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
            background: rgba(35, 28, 64, 0.8);
        }

        /* Hide scrollbar */
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        main#supervisor-content {
            min-width: 0;
            width: 100%;
        }
    </style>
</head>
<body class="font-sans antialiased text-white min-h-screen pt-[env(safe-area-inset-top)] pl-[env(safe-area-inset-left)] pr-[env(safe-area-inset-right)] pb-[env(safe-area-inset-bottom)]">

    <!-- Top Navigation Bar -->
    <nav class="glass sticky top-0 z-30 px-4 md:px-6 py-3 flex items-center justify-between border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center overflow-hidden shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="TIPTAP" class="w-full h-full object-contain">
            </div>
            <div class="min-w-0">
                <span class="text-xs font-bold text-violet-400 uppercase tracking-wider block leading-tight">Floor Supervisor</span>
                @if(!empty($header))
                    <span class="text-[10px] text-white/40 font-medium leading-tight block truncate max-w-[200px] sm:max-w-xs">{{ $header }}</span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <div class="hidden sm:flex glass px-3 py-1.5 rounded-lg items-center gap-2">
                <div class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></div>
                <span class="text-[10px] font-semibold text-white/70 uppercase tracking-wider">On Duty</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-white/50 hover:text-white uppercase tracking-wider px-3 py-1.5 glass rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-violet-500/50">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Content -->
    <main id="supervisor-content" class="p-4 md:p-6 lg:p-8">
        {{ $slot }}
    </main>

    <!-- Toast: Success -->
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

    <!-- Toast: Error -->
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
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white/20 hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-white/30 rounded" aria-label="Dismiss">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>
        <script>setTimeout(() => document.getElementById('toast-error')?.remove(), 5000);</script>
    @endif

    <!-- Toast: Info -->
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
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white/20 hover:text-white transition-colors focus:outline-none focus:ring-2 focus:ring-white/30 rounded" aria-label="Dismiss">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>
        <script>setTimeout(() => document.getElementById('toast-info')?.remove(), 5000);</script>
    @endif

    @stack('scripts')
</body>
</html>
