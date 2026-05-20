@props([
    'title' => 'TIPTAP |  ',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('images/logo.png') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            * {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            }

            body {
                background: #FAFBFC;
                color: #12141C;
                min-height: 100vh;
                min-height: 100dvh;
            }

            .login-hero-bg {
                background: linear-gradient(165deg, #DDD7FE 0%, #F5F3FF 35%, #FFFFFF 72%);
            }

            .login-hero-blob {
                position: absolute;
                border-radius: 50%;
                filter: blur(80px);
                pointer-events: none;
            }
            .login-hero-blob-1 { width: 420px; height: 420px; background: rgba(140,113,246,0.35); top: -120px; right: -80px; }
            .login-hero-blob-2 { width: 320px; height: 320px; background: rgba(198,189,250,0.5); bottom: 0; left: -100px; }
            .login-hero-blob-3 { width: 200px; height: 200px; background: rgba(37,211,102,0.12); top: 40%; left: 30%; }

            .glass-card {
                background: rgba(255, 255, 255, 0.92);
                backdrop-filter: blur(20px) saturate(180%);
                -webkit-backdrop-filter: blur(20px) saturate(180%);
                border: 1px solid rgba(140, 113, 246, 0.15);
                box-shadow:
                    0 8px 32px rgba(18, 20, 28, 0.06),
                    0 0 0 1px rgba(140, 113, 246, 0.06);
            }

            .gradient-text {
                background: linear-gradient(135deg, #6D52E8 0%, #8C71F6 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .btn-fin {
                background: linear-gradient(135deg, #8C71F6 0%, #6D52E8 100%);
                box-shadow: 0 4px 20px rgba(109, 82, 232, 0.35);
                transition: transform 0.25s ease, box-shadow 0.25s ease;
            }
            .btn-fin:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 28px rgba(109, 82, 232, 0.45);
            }

            @keyframes pulse-glow {
                0%, 100% { box-shadow: 0 0 30px rgba(140, 113, 246, 0.3); }
                50% { box-shadow: 0 0 60px rgba(140, 113, 246, 0.5); }
            }
            .animate-pulse-glow { animation: pulse-glow 3s ease-in-out infinite; }

            @media (max-width: 640px) {
                .glass-card {
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(10px);
                    -webkit-backdrop-filter: blur(10px);
                }
            }

            @media (hover: none) {
                button, a {
                    min-height: 44px;
                }
            }

            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-track { background: rgba(140, 113, 246, 0.05); }
            ::-webkit-scrollbar-thumb {
                background: linear-gradient(180deg, rgba(140, 113, 246, 0.5) 0%, rgba(109, 82, 232, 0.5) 100%);
                border-radius: 10px;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="fixed inset-0 z-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute inset-0 login-hero-bg"></div>
            <div class="login-hero-blob login-hero-blob-1"></div>
            <div class="login-hero-blob login-hero-blob-2"></div>
            <div class="login-hero-blob login-hero-blob-3"></div>
        </div>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-4 sm:pt-0 px-3 sm:px-4 relative z-10">
            <a href="/" class="flex items-center gap-2 sm:gap-3 group mb-6 sm:mb-8">
                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-br from-[#8C71F6] to-[#6D52E8] p-2 shadow-xl shadow-[#8C71F6]/30 transform group-hover:rotate-6 transition-all duration-500 animate-pulse-glow">
                    <img src="{{ asset('images/logo.png') }}" alt="TIPTAP Logo" class="w-full h-full object-contain bg-white rounded-lg">
                </div>
                <span class="text-xl sm:text-2xl font-black text-[#12141C] tracking-tight">TIP<span class="gradient-text">TAP</span></span>
            </a>

            <div class="w-full sm:max-w-md glass-card rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-xl shadow-[#6D52E8]/10 relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-32 h-32 sm:w-40 sm:h-40 bg-[#8C71F6]/10 rounded-full blur-2xl sm:blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 sm:w-40 sm:h-40 bg-[#DDD7FE]/60 rounded-full blur-2xl sm:blur-3xl"></div>

                <div class="relative z-10">
                    {{ $slot }}
                </div>
            </div>

            <p class="mt-6 sm:mt-8 text-[#64708B] text-xs font-medium text-center">&copy; {{ date('Y') }} TIPTAP. All rights reserved.</p>
        </div>
    </body>
</html>
