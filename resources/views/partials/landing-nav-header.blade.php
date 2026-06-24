@php
    $logoUrl = function_exists('public_asset') ? public_asset('images/logo.png') : asset('images/logo.png');
    $flagAsset = fn (string $path) => function_exists('public_asset') ? public_asset($path) : asset($path);
    $market = config('tiptap.market', 'tz');
    $flagPath = $market === 'za' ? 'images/flags/za.svg' : 'images/flags/tz.svg';
    $flagLabel = $market === 'za' ? 'South Africa' : 'Tanzania';
@endphp

<header id="site-nav" class="fixed top-0 inset-x-0 z-50 bg-white/95 backdrop-blur-md border-b border-fin-ink/[0.06] shadow-[0_1px_0_rgba(18,20,28,0.04)] transition-shadow duration-300">
    <div class="max-w-6xl mx-auto px-5 lg:px-8">
        <div class="relative flex items-center justify-between h-16 lg:h-[4.5rem]">
            <a href="/" class="relative z-10 flex items-center gap-2.5 shrink-0 group">
                <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-fin-primary to-fin-primary-dark p-1.5 shadow-md shadow-fin-primary/25 group-hover:scale-105 transition-transform">
                    <img src="{{ $logoUrl }}" alt="TIPTAP" class="h-full w-full object-contain rounded-lg bg-white">
                </div>
                <span class="text-lg font-bold tracking-tight text-fin-ink">TIP<span class="text-fin-primary">TAP</span></span>
            </a>

            <nav class="hidden lg:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 items-center gap-8 text-[0.9375rem] font-medium text-fin-muted whitespace-nowrap" aria-label="Primary">
                <a href="/" class="hover:text-fin-primary-dark transition-colors">Home</a>
                <a href="#how-it-works" class="hover:text-fin-primary-dark transition-colors">About</a>
                <a href="#pricing" class="hover:text-fin-primary-dark transition-colors">Pricing</a>
                <a href="#faq" class="hover:text-fin-primary-dark transition-colors">FAQs</a>
                <a href="#contact" class="hover:text-fin-primary-dark transition-colors">Contact</a>
            </nav>

            <div class="relative z-10 hidden lg:flex items-center gap-5 shrink-0">
                @auth
                    <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-fin-ink hover:text-fin-primary transition-colors">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-fin-muted hover:text-fin-ink transition-colors">Log in</a>
                    <div class="relative group">
                        <button type="button" class="btn-glow inline-flex items-center gap-1.5 rounded-xl px-5 py-2.5 text-sm font-semibold text-white">
                            Get Started
                            <i data-lucide="chevron-down" class="w-4 h-4 opacity-80"></i>
                        </button>
                        <div class="absolute right-0 top-full mt-2 w-60 rounded-2xl border border-fin-ink/5 bg-white py-2 shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                            <a href="{{ route('restaurant.register') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-fin-muted hover:bg-fin-mist hover:text-fin-ink transition-colors">
                                <span class="icon-box !w-8 !h-8"><i data-lucide="store" class="w-4 h-4 text-fin-primary"></i></span>
                                <span><span class="font-semibold text-fin-ink block">Restaurant / Manager</span><span class="text-xs">Free trial</span></span>
                            </a>
                            <a href="{{ route('waiter.register') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-fin-muted hover:bg-fin-mist hover:text-fin-ink transition-colors">
                                <span class="icon-box !w-8 !h-8"><i data-lucide="user" class="w-4 h-4 text-fin-primary"></i></span>
                                <span><span class="font-semibold text-fin-ink block">Waiter</span><span class="text-xs">Get your code</span></span>
                            </a>
                            @include('partials.landing-book-demo-link', ['variant' => 'menu-item'])
                        </div>
                    </div>
                @endauth
            </div>

            <div class="relative z-10 flex items-center gap-2 lg:hidden">
                <img
                    src="{{ $flagAsset($flagPath) }}"
                    alt="{{ $flagLabel }}"
                    width="28"
                    height="20"
                    class="h-5 w-7 shrink-0 rounded-[2px] shadow-sm ring-1 ring-black/10 object-cover"
                    title="{{ $flagLabel }}"
                >
                <button type="button" id="mobile-menu-btn" class="p-2 rounded-xl hover:bg-fin-mist text-fin-ink" aria-label="Menu">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<div id="mobile-menu" class="fixed inset-0 z-[60] bg-white hidden flex-col p-8 lg:hidden">
    <div class="flex justify-between items-center mb-10">
        <a href="/" class="flex items-center gap-2">
            <img src="{{ $logoUrl }}" alt="TIPTAP" class="h-8 w-8 rounded-lg">
            <span class="text-xl font-bold text-fin-ink">TIPTAP</span>
        </a>
        <button type="button" id="mobile-menu-close" class="p-2 rounded-xl hover:bg-fin-surface" aria-label="Close menu">
            <i data-lucide="x" class="w-7 h-7"></i>
        </button>
    </div>
    <nav class="flex flex-col gap-5 text-lg font-medium text-fin-muted">
        <a href="/" class="hover:text-fin-primary">Home</a>
        <a href="#how-it-works" class="hover:text-fin-primary">About</a>
        <a href="#features" class="hover:text-fin-primary">Features</a>
        <a href="#demo" class="hover:text-fin-primary">Demo</a>
        <a href="#pricing" class="hover:text-fin-primary">Pricing</a>
        <a href="#faq" class="hover:text-fin-primary">FAQs</a>
        <a href="#contact" class="hover:text-fin-primary">Contact</a>
        <a href="#lead-magnet" class="hover:text-fin-primary">Free guide</a>
        <hr class="border-fin-ink/10 my-2">
        @guest
            @include('partials.landing-book-demo-link', ['variant' => 'solid-light', 'class' => 'w-full justify-center'])
            @include('partials.landing-chat-with-us-link', ['variant' => 'solid', 'class' => 'w-full justify-center'])
            <hr class="border-fin-ink/10 my-2">
        @endguest
        <a href="{{ route('login') }}">Log in</a>
        <a href="{{ route('restaurant.register') }}" class="text-fin-primary font-semibold">Register restaurant</a>
        <a href="{{ route('waiter.register') }}" class="text-fin-primary font-semibold">Register as waiter</a>
    </nav>
</div>
