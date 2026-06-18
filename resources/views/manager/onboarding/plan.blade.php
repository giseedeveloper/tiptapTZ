<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TIPTAP · Choose your plan</title>
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
        @keyframes fadeUp { from { opacity:0; transform: translateY(16px); } to { opacity:1; transform: translateY(0); } }
        .fade-up { animation: fadeUp 0.6s cubic-bezier(0.34,1.2,0.64,1) both; }
        .plan-card { transition: transform 0.25s cubic-bezier(0.34,1.4,0.64,1), border-color 0.25s ease, box-shadow 0.25s ease; cursor: pointer; }
        .plan-card:hover { transform: translateY(-6px); }
        .plan-card.is-selected { border-color: rgba(140,113,246,0.85) !important; box-shadow: 0 24px 60px -24px rgba(140,113,246,0.6); transform: translateY(-6px); }
        .plan-card.is-selected .plan-check { opacity: 1; transform: scale(1); }
        .plan-check { opacity: 0; transform: scale(0.5); transition: all 0.2s ease; }
    </style>
</head>
<body class="text-white antialiased">
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="orb absolute top-[-8rem] right-[-6rem] w-[32rem] h-[32rem] bg-violet-600/15 rounded-full blur-[130px]"></div>
        <div class="orb absolute bottom-[-10rem] left-[-6rem] w-[30rem] h-[30rem] bg-cyan-600/10 rounded-full blur-[130px]" style="animation-delay:-7s"></div>
    </div>

    <div class="min-h-screen flex flex-col items-center px-4 py-10 relative z-10">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-11 h-11 flex items-center justify-center overflow-hidden rounded-full ring-2 ring-white/10">
                <img src="{{ public_asset('images/logo.png') }}" alt="TIPTAP" class="w-full h-full object-contain bg-white">
            </div>
            <span class="text-xl font-black tracking-tight">TIP<span class="gradient-text">TAP</span></span>
        </div>

        <div class="text-center mb-10 fade-up">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                <span class="text-[11px] font-black text-emerald-300 uppercase tracking-wider">{{ $restaurant->name }} approved</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black">Choose your <span class="gradient-text">plan</span></h1>
            <p class="text-sm text-white/50 mt-2 max-w-lg mx-auto">Pick the plan that fits your venue. You can change it later — select one to activate your dashboard.</p>
        </div>

        @if ($errors->any())
            <div class="w-full max-w-5xl mb-5 p-4 rounded-xl bg-rose-500/10 border border-rose-500/25 text-rose-200 text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($packages->isEmpty())
            <div class="glass-card rounded-3xl p-10 text-center max-w-md">
                <div class="text-4xl mb-3">🏷️</div>
                <p class="font-bold text-white">No plans available yet</p>
                <p class="text-sm text-white/50 mt-1">Please contact support — plans are being set up.</p>
            </div>
        @else
            <form method="POST" action="{{ route('manager.onboarding.plan.store') }}" class="w-full max-w-5xl">
                @csrf
                <input type="hidden" name="subscription_package_id" id="selected-plan-id" value="{{ old('subscription_package_id') }}">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @foreach ($packages as $i => $package)
                        <div class="plan-card glass-card rounded-3xl p-6 border {{ $package->is_featured ? 'border-fin-primary/50' : 'border-white/10' }} relative flex flex-col fade-up"
                             style="animation-delay: {{ $i * 0.08 }}s"
                             data-plan-id="{{ $package->id }}"
                             role="button" tabindex="0" aria-pressed="false">
                            @if ($package->is_featured)
                                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg whitespace-nowrap">Most popular</span>
                            @endif

                            <div class="plan-check absolute top-4 right-4 w-7 h-7 rounded-full bg-fin-primary flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>

                            <h3 class="text-lg font-black text-white">{{ $package->name }}</h3>
                            @if ($package->tagline)
                                <p class="text-[11px] text-white/45 mt-0.5 mb-3">{{ $package->tagline }}</p>
                            @endif

                            <div class="my-3">
                                <span class="text-3xl font-black text-white tabular-nums">{{ $package->priceLabel() }}</span>
                                <span class="text-sm text-white/40 ml-1">{{ $package->periodLabel() }}</span>
                            </div>

                            <ul class="space-y-2.5 mt-2 mb-2 flex-1">
                                <li class="flex items-start gap-2.5 text-xs text-white/65">
                                    <span class="w-4 h-4 mt-0.5 rounded-full bg-fin-primary/15 flex items-center justify-center shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-fin-primary"><polyline points="20 6 9 17 4 12"/></svg>
                                    </span>
                                    {{ $package->tableLimitLabel() }}
                                </li>
                                @foreach (($package->features ?? []) as $feature)
                                    <li class="flex items-start gap-2.5 text-xs text-white/65">
                                        <span class="w-4 h-4 mt-0.5 rounded-full bg-fin-primary/15 flex items-center justify-center shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-fin-primary"><polyline points="20 6 9 17 4 12"/></svg>
                                        </span>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>

                            <div class="plan-select-label mt-4 text-center px-4 py-2.5 rounded-xl border border-white/10 bg-white/5 text-xs font-bold text-white/60" data-default="Select {{ $package->name }}">
                                Select {{ $package->name }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-8">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs font-bold text-white/40 hover:text-white/70 transition">Sign out</button>
                    </form>
                    <button type="submit" id="continue-btn" disabled
                            class="px-10 py-4 rounded-2xl bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white font-black text-base shadow-xl shadow-violet-500/25 transition disabled:opacity-40 disabled:cursor-not-allowed hover:scale-[1.02] enabled:hover:scale-[1.02]">
                        Activate my dashboard →
                    </button>
                </div>
            </form>
        @endif

        <p class="mt-10 text-white/25 text-xs">© {{ date('Y') }} TIPTAP · Secure onboarding</p>
    </div>

    <script>
    (function () {
        const cards = document.querySelectorAll('.plan-card');
        const hidden = document.getElementById('selected-plan-id');
        const continueBtn = document.getElementById('continue-btn');
        if (!cards.length) return;

        function select(card) {
            cards.forEach(c => {
                c.classList.remove('is-selected');
                c.setAttribute('aria-pressed', 'false');
                const label = c.querySelector('.plan-select-label');
                if (label) { label.classList.remove('bg-fin-primary','text-white','border-fin-primary'); label.classList.add('bg-white/5','text-white/60','border-white/10'); label.textContent = label.dataset.default || 'Select'; }
            });
            card.classList.add('is-selected');
            card.setAttribute('aria-pressed', 'true');
            const label = card.querySelector('.plan-select-label');
            if (label) { label.classList.add('bg-fin-primary','text-white','border-fin-primary'); label.classList.remove('bg-white/5','text-white/60','border-white/10'); label.textContent = 'Selected'; }
            hidden.value = card.dataset.planId;
            if (continueBtn) continueBtn.disabled = false;
        }

        cards.forEach(card => {
            card.addEventListener('click', () => select(card));
            card.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); select(card); }
            });
            if (hidden.value && hidden.value === card.dataset.planId) select(card);
        });
    })();
    </script>
</body>
</html>
