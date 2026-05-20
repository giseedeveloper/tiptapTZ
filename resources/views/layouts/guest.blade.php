<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>TIPTAP</title>

        <!-- Premium Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            * {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            }
            
            body { 
                background: #0f0a1e;
                min-height: 100vh;
            }

            /* Gradient Text */
            .gradient-text {
                background: linear-gradient(135deg, #8b5cf6 0%, #06b6d4 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            /* Glassmorphism */
            .glass-card {
                background: rgba(28, 22, 51, 0.6);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.08);
            }

            /* Animations */
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-15px); }
            }
            
            @keyframes pulse-glow {
                0%, 100% { box-shadow: 0 0 30px rgba(139, 92, 246, 0.3); }
                50% { box-shadow: 0 0 60px rgba(139, 92, 246, 0.5); }
            }

            .animate-float { animation: float 6s ease-in-out infinite; }
            .animate-pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }

            /* Custom Scrollbar */
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.02); }
            ::-webkit-scrollbar-thumb {
                background: linear-gradient(180deg, rgba(139, 92, 246, 0.5) 0%, rgba(6, 182, 212, 0.5) 100%);
                border-radius: 10px;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-white">
        <!-- Background Effects -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-violet-600/10 rounded-full blur-[150px] -mr-48 -mt-48"></div>
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-cyan-600/10 rounded-full blur-[150px] -ml-48 -mb-48"></div>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 px-4 relative z-10">
            <!-- Logo -->
            <a href="/" class="flex items-center gap-3 group mb-8">
                <div class="w-14 h-14 flex items-center justify-center overflow-hidden rounded-full transform group-hover:rotate-12 transition-all duration-500 animate-pulse-glow">
                    <img src="{{ asset('images/logo.png') }}" alt="TIPTAP Logo" class="w-full h-full object-contain bg-white">
                </div>
                <div>
                    <span class="text-2xl font-black text-white tracking-tight block leading-none hidden">TIP<span class="gradient-text">TAP</span></span>
                    <span class="text-[10px] font-semibold text-white/40 uppercase tracking-[0.2em]"> </span>
                </div>
            </a>

            <!-- Content Card -->
            <div class="w-full sm:max-w-md glass-card rounded-3xl p-8 shadow-2xl shadow-black/50 animate-float relative overflow-hidden">
                <!-- Decorative elements inside card -->
                <div class="absolute -top-20 -right-20 w-40 h-40 bg-violet-500/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-cyan-500/10 rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-white/30 text-xs font-medium flex items-center justify-center gap-2">
                <img
                    src="{{ asset('images/flags/tz.svg') }}"
                    alt="Tanzania flag"
                    width="24"
                    height="18"
                    class="h-[18px] w-6 shrink-0 rounded-[2px] shadow-sm ring-1 ring-white/25 object-cover"
                    title="Tanzania"
                >
                <span>&copy; {{ date('Y') }} TIPTAP. All rights reserved.</span>
            </p>
        </div>
    </body>
</html>
