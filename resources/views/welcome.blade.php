<!DOCTYPE html>
<html lang="{{ $landing['seo']['html_lang'] ?? 'en' }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.landing-seo-head')

    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('images/logo-64.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo-64.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root { --fin-primary: #8C71F6; --fin-dark: #6D52E8; --fin-ink: #12141C; }
        body { background: #FAFBFC; color: var(--fin-ink); font-feature-settings: "cv02", "cv03", "cv04", "cv11"; }
        .hero-section { background: linear-gradient(165deg, #DDD7FE 0%, #F5F3FF 35%, #FFFFFF 72%); position: relative; overflow: hidden; }
        .hero-blob { position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none; }
        .hero-blob-1 { width: 420px; height: 420px; background: rgba(140,113,246,0.35); top: -120px; right: -80px; }
        .hero-blob-2 { width: 320px; height: 320px; background: rgba(198,189,250,0.5); bottom: 0; left: -100px; }
        .hero-blob-3 { width: 200px; height: 200px; background: rgba(37,211,102,0.12); top: 40%; left: 30%; }
        .text-hero-gradient { background: linear-gradient(120deg, #12141C 0%, #6D52E8 45%, #8C71F6 85%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .text-hero-accent { background: linear-gradient(90deg, #6D52E8, #8C71F6, #A78BFA); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .nav-glass { background: rgba(255,255,255,0.88); backdrop-filter: blur(20px) saturate(180%); border: 1px solid rgba(255,255,255,0.9); box-shadow: 0 8px 32px rgba(18,20,28,0.06), 0 0 0 1px rgba(140,113,246,0.06); }
        .nav-glass.scrolled { box-shadow: 0 12px 40px rgba(109,82,232,0.12); }
        #site-nav.scrolled { box-shadow: 0 4px 24px rgba(18,20,28,0.08); }
        .btn-glow { background: linear-gradient(135deg, #8C71F6 0%, #6D52E8 100%); box-shadow: 0 4px 20px rgba(109,82,232,0.4), 0 0 0 1px rgba(255,255,255,0.15) inset; transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .btn-glow:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(109,82,232,0.5); }
        .section-label { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: #6D52E8; background: linear-gradient(135deg, rgba(237,233,254,0.9), rgba(255,255,255,0.9)); border: 1px solid rgba(140,113,246,0.2); }
        .fin-card { background: #fff; border: 1px solid rgba(18,20,28,0.06); box-shadow: 0 4px 24px rgba(18,20,28,0.04); transition: transform 0.4s cubic-bezier(0.22,1,0.36,1), box-shadow 0.4s ease, border-color 0.3s ease; }
        .fin-card:hover { transform: translateY(-6px); border-color: rgba(140,113,246,0.25); box-shadow: 0 20px 50px rgba(109,82,232,0.12); }
        .stat-card { background: linear-gradient(145deg, #fff 0%, #FAFAFE 100%); border: 1px solid rgba(140,113,246,0.12); border-radius: 1.25rem; padding: 1.5rem; text-align: center; position: relative; overflow: hidden; }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #8C71F6, #C6BDFA); }
        .stat-number { font-size: 2.25rem; font-weight: 300; letter-spacing: -0.03em; background: linear-gradient(135deg, #6D52E8, #8C71F6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .icon-box { width: 3rem; height: 3rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; background: linear-gradient(145deg, #F5F3FF, #EDE9FE); border: 1px solid rgba(140,113,246,0.15); }
        .feature-card-lg { background: linear-gradient(160deg, #fff 0%, #FAFAFE 50%, #F5F3FF 100%); }
        .phone-float { animation: phoneFloat 6s ease-in-out infinite; }
        @keyframes phoneFloat { 0%, 100% { transform: translateY(0) rotate(-1deg); } 50% { transform: translateY(-14px) rotate(1deg); } }
        .chat-bubble { opacity: 0; animation: bubbleIn 0.55s cubic-bezier(0.22,1,0.36,1) forwards; animation-delay: var(--delay, 0s); }
        @keyframes bubbleIn { from { opacity: 0; transform: translateY(12px) scale(0.96); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .typing-dot { width: 7px; height: 7px; border-radius: 50%; background: #8C71F6; animation: typing 1.2s infinite ease-in-out; }
        .typing-dot:nth-child(2) { animation-delay: 0.15s; }
        .typing-dot:nth-child(3) { animation-delay: 0.3s; }
        @keyframes typing { 0%, 80%, 100% { transform: scale(0.5); opacity: 0.35; } 40% { transform: scale(1); opacity: 1; } }
        .chat-scroll::-webkit-scrollbar { width: 3px; }
        .chat-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 4px; }
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.8s cubic-bezier(0.22,1,0.36,1), transform 0.8s cubic-bezier(0.22,1,0.36,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .step-line { position: relative; }
        @media (min-width: 768px) {
            .step-line::after { content: ''; position: absolute; top: 2rem; left: calc(50% + 2rem); width: calc(100% - 4rem); height: 2px; background: linear-gradient(90deg, #DDD7FE, #8C71F6, #DDD7FE); }
            .step-line:last-child::after { display: none; }
        }
        .pricing-featured { background: linear-gradient(180deg, rgba(237,233,254,0.6) 0%, #fff 40%); border: 2px solid rgba(140,113,246,0.35); box-shadow: 0 24px 60px rgba(109,82,232,0.15); }
        .pricing-billing-btn { border-radius: 9999px; padding: 0.55rem 1.15rem; font-size: 0.8rem; font-weight: 600; color: #64708B; transition: all 0.2s ease; }
        .pricing-billing-btn.is-active { background: #fff; color: #6D52E8; box-shadow: 0 4px 14px rgba(109,82,232,0.15); }
        .testimonial-card { background: #fff; border: 1px solid rgba(18,20,28,0.06); border-radius: 1.5rem; padding: 1.75rem; box-shadow: 0 8px 30px rgba(18,20,28,0.04); transition: transform 0.35s ease, box-shadow 0.35s ease, border-color 0.35s ease; }
        .testimonial-card:hover { transform: translateY(-4px); border-color: rgba(140,113,246,0.2); box-shadow: 0 16px 40px rgba(109,82,232,0.1); }
        .testimonial-avatar { background: linear-gradient(135deg, #8C71F6 0%, #6D52E8 100%); }
        .footer-dark { background: linear-gradient(180deg, #12141C 0%, #1a1d28 100%); color: #94A3B8; }
        details[open] summary { color: #6D52E8; }
        details summary { list-style: none; cursor: pointer; }
        details summary::-webkit-details-marker { display: none; }
        .trust-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.4rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; color: #64708B; background: rgba(255,255,255,0.7); border: 1px solid rgba(18,20,28,0.06); }
        .hero-trust-bar { background: rgba(255,255,255,0.78); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,0.95); border-radius: 1.35rem; padding: 1.2rem 1.3rem; box-shadow: 0 10px 32px rgba(18,20,28,0.06), 0 0 0 1px rgba(140,113,246,0.06); }
        .hero-trust-logo { filter: saturate(0.9); }
        .hero-trust-logo:hover { filter: saturate(1); }
        .demo-walkthrough-scene { transition: opacity 0.45s ease; }
        .demo-walkthrough-scene.is-active { opacity: 1 !important; }
        .demo-step-btn.is-active { border-color: rgba(140,113,246,0.25); background: rgba(255,255,255,0.88); }
        .partners-logo { filter: saturate(0.92); }
        .partners-logo:hover { filter: saturate(1); }
    </style>
</head>
<body class="antialiased font-sans selection:bg-fin-primary selection:text-white" data-landing-page="true">

    @include('partials.landing-nav-header')

    {{-- Hero --}}
    <section class="hero-section pt-28 pb-20 lg:pt-40 lg:pb-28">
        <div class="hero-blob hero-blob-1"></div>
        <div class="hero-blob hero-blob-2"></div>
        <div class="hero-blob hero-blob-3"></div>
        <div class="max-w-6xl mx-auto px-5 lg:px-8 relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-14 lg:gap-10">
                <div class="flex-1 text-center lg:text-left max-w-xl mx-auto lg:mx-0">
                    <div class="inline-flex items-center gap-2 rounded-full pl-1 pr-4 py-1 mb-8 bg-white/70 backdrop-blur-md border border-white shadow-sm">
                        <span class="rounded-full bg-linear-to-r from-fin-primary to-fin-primary-dark px-3 py-1 text-[10px] font-bold text-white uppercase tracking-wider shadow-sm">{{ $landing['hero']['live_badge'] }}</span>
                        <span class="text-xs font-medium text-fin-muted">{{ $landing['hero']['badge_text'] }}</span>
                    </div>
                    <h1 class="text-[2rem] sm:text-[2.75rem] lg:text-[3rem] font-light leading-[1.08] tracking-tight text-fin-ink mb-6">
                        <span class="text-hero-gradient font-normal">{{ $landing['hero']['title_line1'] }}</span><br>
                        <span class="text-hero-accent font-medium">{{ $landing['hero']['title_line2'] }}</span>
                    </h1>
                    @include('partials.landing-hero-rafiki-intro')
                    <p class="text-base sm:text-lg text-fin-muted leading-relaxed font-normal max-w-lg mx-auto lg:mx-0 mb-6">
                        {{ $landing['hero']['description'] }}
                    </p>
                    @include('partials.landing-hero-trust-bar')
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-3 mb-4">
                        <a href="{{ route('restaurant.register') }}" class="btn-glow w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl px-8 py-4 text-sm font-bold text-white">
                            {{ $landing['hero']['cta_primary'] }}
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                        <a href="#demo" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-white border border-fin-ink/8 px-8 py-4 text-sm font-semibold text-fin-ink shadow-sm hover:shadow-md hover:border-fin-primary/30 transition-all">
                            <i data-lucide="play" class="w-4 h-4 text-fin-primary"></i>
                            {{ $landing['hero']['cta_secondary'] }}
                        </a>
                    </div>
                    <div class="flex flex-col sm:flex-row flex-wrap items-center justify-center lg:justify-start gap-x-5 gap-y-2 mb-2">
                        @include('partials.landing-book-demo-link', ['variant' => 'ghost'])
                        @include('partials.landing-chat-with-us-link', ['variant' => 'ghost'])
                    </div>
                </div>
                <div class="flex-1 w-full flex justify-center lg:justify-end">
                    @include('partials.landing-phone-mockup')
                </div>
            </div>
        </div>
    </section>

    @include('partials.landing-demo-section')

    {{-- Stats --}}
    <section class="py-14 lg:py-20 bg-white relative">
        <div class="max-w-6xl mx-auto px-5">
            <p class="text-center text-fin-muted text-sm font-medium mb-10 max-w-md mx-auto leading-relaxed">
                {{ $landing['trust']['stats_headline'] }}
            </p>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">
                @foreach($landing['trust']['stats'] as $stat)
                    <div class="stat-card reveal">
                        <p class="stat-number">{{ $stat['value'] }}</p>
                        <p class="text-sm text-fin-muted mt-2 font-medium">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="py-20 lg:py-28 bg-fin-surface">
        <div class="max-w-6xl mx-auto px-5">
            <div class="text-center max-w-2xl mx-auto mb-16 reveal">
                <span class="section-label mb-4">Features</span>
                <h2 class="text-3xl lg:text-[2.75rem] font-light text-fin-ink tracking-tight mt-4 leading-tight">
                    Everything your <span class="text-fin-primary font-medium">restaurant</span> needs
                </h2>
                <p class="text-fin-muted mt-5 text-base leading-relaxed">{{ $landing['features']['subtitle'] }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                <div class="fin-card feature-card-lg rounded-3xl p-8 md:col-span-7 reveal">
                    <div class="icon-box mb-6"><i data-lucide="bot" class="w-6 h-6 text-fin-primary"></i></div>
                    <h3 class="text-2xl font-semibold text-fin-ink mb-3 tracking-tight">TipTap Rafiki on WhatsApp</h3>
                    <p class="text-fin-muted leading-relaxed max-w-md text-[0.95rem]">{{ $landing['features']['rafiki_card_body'] }}</p>
                    <div class="mt-8 p-5 rounded-2xl bg-white border border-fin-primary/10 shadow-inner max-w-sm">
                        <div class="flex gap-3 items-center">
                            <img src="{{ asset('images/logo-64.png') }}" width="40" height="40" class="w-10 h-10 rounded-full object-contain bg-fin-mist p-1 ring-2 ring-fin-primary/20" alt="">
                            <div>
                                <p class="text-xs font-bold text-fin-ink">Menu sent &middot; Table 7</p>
                                <p class="text-[10px] text-fin-muted mt-0.5">Ready to order</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fin-card rounded-3xl p-8 md:col-span-5 reveal">
                    <div class="icon-box mb-6 bg-emerald-50! border-emerald-100!"><i data-lucide="banknote" class="w-6 h-6 text-emerald-600"></i></div>
                    <h3 class="text-xl font-semibold text-fin-ink mb-2">Instant mobile money</h3>
                    <p class="text-fin-muted text-sm leading-relaxed">M-Pesa, TigoPesa, and Airtel Money via Selcom. Tips and bills sync to your dashboard in real time.</p>
                </div>
                <div class="fin-card rounded-3xl p-8 md:col-span-5 reveal">
                    <div class="icon-box mb-6 bg-amber-50! border-amber-100!"><i data-lucide="qr-code" class="w-6 h-6 text-amber-600"></i></div>
                    <h3 class="text-xl font-semibold text-fin-ink mb-2">QR code per table</h3>
                    <p class="text-fin-muted text-sm leading-relaxed">Every table gets its own code. Guests scan once and land straight in your branded bot flow.</p>
                </div>
                <div class="fin-card rounded-3xl p-8 md:col-span-7 reveal overflow-hidden">
                    <div class="flex flex-col sm:flex-row gap-8">
                        <div class="flex-1">
                            <div class="icon-box mb-6"><i data-lucide="monitor" class="w-6 h-6 text-fin-primary"></i></div>
                            <h3 class="text-xl font-semibold text-fin-ink mb-2">Kitchen display & live orders</h3>
                            <p class="text-fin-muted text-sm leading-relaxed">Orders hit the kitchen screen instantly. Managers track the full pipeline from pending to served.</p>
                        </div>
                        <div class="flex-1 rounded-2xl bg-linear-to-br from-fin-ink to-[#2a2d3a] p-5 text-white font-mono text-xs shadow-xl">
                            <div class="flex justify-between items-center mb-3 pb-3 border-b border-white/10">
                                <span class="text-fin-lavender font-bold">#1042</span>
                                <span class="text-emerald-400 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-400/15">PREPARING</span>
                            </div>
                            <p class="opacity-90">2x Grilled Fish</p>
                            <p class="opacity-70">1x Chips Masala</p>
                            <p class="text-fin-lavender mt-3 text-[10px]">Table 12 &middot; James</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="py-20 lg:py-28 bg-white">
        <div class="max-w-6xl mx-auto px-5">
            <div class="text-center mb-16 reveal">
                <span class="section-label">Workflow</span>
                <h2 class="text-3xl lg:text-4xl font-light text-fin-ink mt-4 tracking-tight">From scan to served</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-12 md:gap-6">
                @foreach([
                    ['01', 'qr-code', 'Scan QR at table', 'Guests scan the code, no app download required.'],
                    ['02', 'messages-square', 'Chat with TipTap Rafiki', 'Browse menu, order, and pay inside WhatsApp.'],
                    ['03', 'utensils-crossed', 'Kitchen & waiter sync', 'Orders flow instantly to staff and the kitchen.'],
                ] as [$num, $icon, $title, $desc])
                    <div class="step-line text-center reveal">
                        <div class="relative inline-flex flex-col items-center">
                            <span class="absolute -top-2 -right-2 w-8 h-8 rounded-full bg-fin-primary text-white text-xs font-bold flex items-center justify-center shadow-lg">{{ $num }}</span>
                            <div class="w-20 h-20 rounded-2xl bg-linear-to-br from-fin-mist to-fin-lavender flex items-center justify-center mb-6 shadow-md border border-fin-primary/10">
                                <i data-lucide="{{ $icon }}" class="w-8 h-8 text-fin-primary-dark"></i>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-fin-ink mb-2">{{ $title }}</h3>
                        <p class="text-sm text-fin-muted leading-relaxed max-w-xs mx-auto">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.landing-testimonials')

    @include('partials.landing-pricing')

    @include('partials.landing-partners-section')

    @include('partials.landing-faq')

    @include('partials.landing-contact')

    @include('partials.landing-lead-magnet')

    @include('partials.landing-bottom-cta')

    <footer class="footer-dark pt-16 pb-10">
        <div class="max-w-6xl mx-auto px-5">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-10 mb-14">
                <div class="col-span-2">
                    <a href="/" class="flex items-center gap-2.5 mb-5">
                        <img src="{{ asset('images/logo-64.png') }}" alt="TIPTAP" width="36" height="36" class="h-9 w-9 rounded-lg bg-white p-1 object-contain">
                        <span class="text-lg font-bold text-white">TIPTAP</span>
                    </a>
                    <p class="text-sm text-slate-400 max-w-xs leading-relaxed mb-4">{{ $landing['footer_tagline'] }}</p>
                    <a href="#contact" class="text-sm text-fin-lavender hover:text-white transition-colors">Our offices &rarr;</a>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Offices</h4>
                    <ul class="space-y-4 text-sm text-slate-400">
                        @foreach ($landing['offices'] as $office)
                            <li>
                                <p class="font-semibold text-slate-300">{{ $office['name'] }}</p>
                                <p>{{ $office['city'] }}</p>
                                @foreach ($office['lines'] as $line)
                                    <p>{{ $line }}</p>
                                @endforeach
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Product</h4>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><a href="#partners" class="hover:text-fin-lavender transition-colors">Payment partners</a></li>
                        <li><a href="#demo" class="hover:text-fin-lavender transition-colors">Live demo</a></li>
                        <li><a href="#features" class="hover:text-fin-lavender transition-colors">Features</a></li>
                        <li><a href="#pricing" class="hover:text-fin-lavender transition-colors">Pricing</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-fin-lavender transition-colors">Log in</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Get started</h4>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><a href="{{ route('restaurant.register') }}" class="hover:text-fin-lavender transition-colors">Register restaurant</a></li>
                        <li><a href="{{ route('waiter.register') }}" class="hover:text-fin-lavender transition-colors">Register waiter</a></li>
                        <li><a href="{{ $landing['whatsapp_url'] }}" class="hover:text-fin-lavender transition-colors">WhatsApp</a></li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 pt-8 border-t border-white/10 text-sm text-slate-500">
                <p>&copy; {{ date('Y') }} TIPTAP. All rights reserved.</p>
                @include('partials.social-links', ['onDark' => true])
            </div>
        </div>
    </footer>

    <a href="{{ $landing['nurture']['chat_with_us_url'] }}" class="fixed bottom-6 right-6 z-40 flex items-center justify-center w-14 h-14 rounded-2xl bg-whatsapp text-white shadow-[0_8px_30px_rgba(37,211,102,0.45)] hover:scale-110 transition-transform" aria-label="{{ $landing['nurture']['chat_with_us_label'] }} on WhatsApp" title="{{ $landing['nurture']['chat_with_us_label'] }}">
        <i data-lucide="message-circle" class="w-6 h-6"></i>
    </a>

    <script>
        const nav = document.getElementById('site-nav');
        window.addEventListener('scroll', () => nav?.classList.toggle('scrolled', window.scrollY > 24));

        const menuBtn = document.getElementById('mobile-menu-btn');
        const menuClose = document.getElementById('mobile-menu-close');
        const menu = document.getElementById('mobile-menu');
        menuBtn?.addEventListener('click', () => { menu.classList.replace('hidden', 'flex'); });
        menuClose?.addEventListener('click', () => { menu.classList.replace('flex', 'hidden'); });

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));
    </script>
</body>
</html>
