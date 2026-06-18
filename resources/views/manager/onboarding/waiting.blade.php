<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TIPTAP · Awaiting approval</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @include('partials.brand-icons')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body { background: #0c0a14; min-height: 100vh; }
        .glass-card { background: rgba(28, 22, 51, 0.55); backdrop-filter: blur(22px); -webkit-backdrop-filter: blur(22px); border: 1px solid rgba(255,255,255,0.08); }
        .gradient-text { background: linear-gradient(135deg,#8C71F6 0%,#6D52E8 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        @keyframes orb { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(20px,-30px) scale(1.08); } }
        .orb { animation: orb 14s ease-in-out infinite; }
        @keyframes ringPulse { 0% { transform: scale(0.8); opacity: 0.7; } 80%,100% { transform: scale(2.2); opacity: 0; } }
        .ring-pulse::before, .ring-pulse::after { content:''; position:absolute; inset:0; border-radius:9999px; border:2px solid rgba(140,113,246,0.5); animation: ringPulse 2.8s ease-out infinite; }
        .ring-pulse::after { animation-delay: 1.4s; }
        @keyframes spinSlow { to { transform: rotate(360deg); } }
        .spin-slow { animation: spinSlow 8s linear infinite; }
        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        .floaty { animation: floaty 5s ease-in-out infinite; }
        @keyframes barFlow { 0% { background-position: 0% 50%; } 100% { background-position: 200% 50%; } }
        .bar-flow { background-size: 200% 100%; animation: barFlow 2.2s linear infinite; }
        @keyframes fadeUp { from { opacity:0; transform: translateY(14px); } to { opacity:1; transform: translateY(0); } }
        .fade-up { animation: fadeUp 0.6s cubic-bezier(0.34,1.2,0.64,1) both; }
        .fade-up-2 { animation: fadeUp 0.6s cubic-bezier(0.34,1.2,0.64,1) 0.12s both; }
        .fade-up-3 { animation: fadeUp 0.6s cubic-bezier(0.34,1.2,0.64,1) 0.24s both; }
        @keyframes dotBlink { 0%,80%,100% { opacity:0.25; } 40% { opacity:1; } }
        .dot-blink span { animation: dotBlink 1.4s infinite; }
        .dot-blink span:nth-child(2) { animation-delay: 0.2s; }
        .dot-blink span:nth-child(3) { animation-delay: 0.4s; }
    </style>
</head>
<body class="text-white antialiased">
    @php $isRejected = $restaurant->isRejected(); @endphp

    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="orb absolute top-[-8rem] right-[-6rem] w-[32rem] h-[32rem] rounded-full blur-[130px] {{ $isRejected ? 'bg-rose-600/15' : 'bg-violet-600/15' }}"></div>
        <div class="orb absolute bottom-[-10rem] left-[-6rem] w-[30rem] h-[30rem] bg-cyan-600/10 rounded-full blur-[130px]" style="animation-delay:-7s"></div>
    </div>

    <div class="min-h-screen flex flex-col items-center justify-center px-4 py-10 relative z-10">
        <div class="flex items-center gap-3 mb-8 floaty">
            <div class="w-12 h-12 flex items-center justify-center overflow-hidden rounded-full ring-2 ring-white/10">
                <img src="{{ public_asset('images/logo.png') }}" alt="TIPTAP" class="w-full h-full object-contain bg-white">
            </div>
            <span class="text-xl font-black tracking-tight">TIP<span class="gradient-text">TAP</span></span>
        </div>

        <div class="w-full max-w-xl glass-card rounded-3xl p-8 sm:p-10 shadow-2xl shadow-black/50 relative overflow-hidden fade-up">
            <div class="absolute -top-24 -right-24 w-48 h-48 {{ $isRejected ? 'bg-rose-500/10' : 'bg-violet-500/10' }} rounded-full blur-3xl"></div>

            <div class="relative z-10 text-center">
                @if ($isRejected)
                    {{-- Rejected state --}}
                    <div class="relative w-24 h-24 mx-auto mb-6">
                        <div class="w-24 h-24 rounded-full bg-rose-500/15 border border-rose-500/30 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#fb7185" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </div>
                    </div>
                    <p class="text-[11px] font-black text-rose-400 uppercase tracking-[0.25em] mb-2">Application not approved</p>
                    <h1 class="text-2xl sm:text-3xl font-black mb-3">We couldn't approve <span class="gradient-text">{{ $restaurant->name }}</span> yet</h1>
                    @if ($restaurant->rejection_reason)
                        <div class="text-left bg-rose-500/10 border border-rose-500/25 rounded-2xl p-5 mb-4 fade-up-2">
                            <p class="text-[10px] font-black text-rose-300 uppercase tracking-wider mb-1.5">Reason from our team</p>
                            <p class="text-sm text-white/80 leading-relaxed">{{ $restaurant->rejection_reason }}</p>
                        </div>
                    @endif
                    <p class="text-sm text-white/50 leading-relaxed fade-up-3">Please review the note above. Our team may reach out, or you can contact support to resolve this. This page updates automatically if your status changes.</p>
                @else
                    {{-- Pending state --}}
                    <div class="relative w-28 h-28 mx-auto mb-7">
                        <div class="ring-pulse absolute inset-0 rounded-full"></div>
                        <div class="absolute inset-2 rounded-full border-2 border-dashed border-violet-400/40 spin-slow"></div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-fin-primary to-fin-primary-dark flex items-center justify-center shadow-lg shadow-violet-500/40">
                                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                        </div>
                    </div>

                    <p class="text-[11px] font-black text-violet-300 uppercase tracking-[0.25em] mb-2 flex items-center justify-center gap-1">
                        Under review <span class="dot-blink inline-flex gap-0.5"><span>.</span><span>.</span><span>.</span></span>
                    </p>
                    <h1 class="text-2xl sm:text-3xl font-black mb-3 fade-up-2"><span class="gradient-text">{{ $restaurant->name }}</span> is awaiting approval</h1>
                    <p class="text-sm text-white/50 leading-relaxed mb-7 fade-up-3">Thanks for registering! Our team is reviewing your restaurant details. You'll be moved to plan selection automatically the moment you're approved — no need to refresh.</p>

                    {{-- Progress steps --}}
                    <div class="space-y-3 text-left mb-2">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/25">
                            <span class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <div><p class="text-sm font-bold text-white">Registration submitted</p><p class="text-[11px] text-white/40">{{ $restaurant->created_at?->diffForHumans() }}</p></div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/5 border border-white/10">
                            <span class="w-7 h-7 rounded-full bg-violet-500/20 border border-violet-400/40 flex items-center justify-center shrink-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-violet-400 animate-pulse"></span>
                            </span>
                            <div><p class="text-sm font-bold text-white">Admin review in progress</p><p class="text-[11px] text-white/40">Usually within a few hours</p></div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/10 opacity-60">
                            <span class="w-7 h-7 rounded-full bg-white/5 border border-white/15 flex items-center justify-center shrink-0">
                                <span class="text-[10px] font-black text-white/40">3</span>
                            </span>
                            <div><p class="text-sm font-bold text-white/70">Choose your plan & go live</p><p class="text-[11px] text-white/35">Unlocks after approval</p></div>
                        </div>
                    </div>

                    <div class="mt-6 h-1.5 rounded-full bg-white/8 overflow-hidden">
                        <div class="bar-flow h-full w-2/3 rounded-full" style="background-image: linear-gradient(90deg, #6D52E8, #8C71F6, #6D52E8);"></div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Account row + logout (security) --}}
        <div class="w-full max-w-xl mt-5 flex items-center justify-between gap-3 fade-up-3">
            <p class="text-xs text-white/35">Signed in as <span class="text-white/60 font-semibold">{{ auth()->user()->email }}</span></p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/5 border border-white/10 text-white/60 hover:text-white hover:bg-white/10 transition text-xs font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Sign out
                </button>
            </form>
        </div>

        <p class="mt-8 text-white/25 text-xs">© {{ date('Y') }} TIPTAP · Secure onboarding</p>
    </div>

    <script>
    (function () {
        const statusUrl = @json(route('manager.onboarding.status'));
        let stopped = false;

        async function poll() {
            if (stopped) return;
            try {
                const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
                if (res.ok) {
                    const data = await res.json();
                    if (data.redirect) {
                        stopped = true;
                        window.location.href = data.redirect;
                        return;
                    }
                }
            } catch (e) { /* keep polling */ }
            setTimeout(poll, 8000);
        }
        setTimeout(poll, 8000);
    })();
    </script>
</body>
</html>
