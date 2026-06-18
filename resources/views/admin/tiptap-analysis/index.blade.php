<x-admin-layout>
    <x-slot name="header">TipTap Analytics</x-slot>

    @include('admin.tiptap-analysis.partials.styles')

    @php
        $sections = [
            ['route' => 'admin.tiptap-analysis.platform', 'label' => 'Platform', 'tag' => 'Revenue', 'desc' => 'Orders, revenue trends & venue health — anonymous totals only', 'icon' => '📊', 'theme' => 'platform'],
            ['route' => 'admin.tiptap-analysis.whatsapp', 'label' => 'WhatsApp', 'tag' => 'Bot', 'desc' => 'Daily bot activity, menu taps & engagement signals', 'icon' => '💬', 'theme' => 'whatsapp'],
            ['route' => 'admin.tiptap-analysis.qr-entry', 'label' => 'QR Entry', 'tag' => 'Scans', 'desc' => 'Scan trends & waiter / table / tag entry share', 'icon' => '📱', 'theme' => 'qr'],
            ['route' => 'admin.tiptap-analysis.journey', 'label' => 'Journey', 'tag' => 'Funnel', 'desc' => 'QR scan → menu → order → payment conversion pipeline', 'icon' => '🛤️', 'theme' => 'journey'],
            ['route' => 'admin.tiptap-analysis.feedback', 'label' => 'Feedback', 'tag' => 'Ratings', 'desc' => 'Star distribution & satisfaction — no comments shown', 'icon' => '⭐', 'theme' => 'feedback'],
            ['route' => 'admin.tiptap-analysis.tips-payments', 'label' => 'Tips & Pay', 'tag' => 'Money', 'desc' => 'Tips collected, payment methods & bill vs quick pay', 'icon' => '💳', 'theme' => 'tips'],
            ['route' => 'admin.tiptap-analysis.language', 'label' => 'Language', 'tag' => 'Behavior', 'desc' => 'EN / SW preference & peak activity hours', 'icon' => '🌐', 'theme' => 'language'],
            ['route' => 'admin.tiptap-analysis.venues', 'label' => 'Pulse', 'tag' => 'Live', 'desc' => 'Platform-wide totals — venues, orders & engagement', 'icon' => '📡', 'theme' => 'pulse'],
        ];
    @endphp

    {{-- Hero --}}
    <div class="hub-hero rounded-3xl p-6 md:p-10 mb-6 relative overflow-hidden">
        <div class="hub-hero-glow hub-hero-glow--violet"></div>
        <div class="hub-hero-glow hub-hero-glow--pink"></div>
        <div class="hub-hero-glow hub-hero-glow--cyan"></div>
        <div class="relative z-10">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-8">
                <div class="max-w-2xl">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="hub-live-dot"></span>
                        <p class="text-[10px] font-black text-fin-primary uppercase tracking-[0.28em]">Intelligence hub</p>
                    </div>
                    <h2 class="text-3xl md:text-4xl font-black text-white tracking-tight leading-tight hub-hero-title">
                        TipTap Analytics
                    </h2>
                    <p class="text-sm md:text-base text-white/50 mt-3 leading-relaxed">
                        Chagua sehemu unayotaka kuchambua. Kila report iko kwenye ukurasa wake — premium, rahisi kusoma, na <strong class="text-white/70 font-semibold">anonymous overview</strong> tu (hakuna majina ya restaurant au waiter).
                    </p>
                    <div class="flex flex-wrap gap-2 mt-5">
                        <span class="hub-badge">🔒 Overview tu</span>
                        <span class="hub-badge">📊 8 reports</span>
                        <span class="hub-badge">⚡ Live data</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 lg:max-w-xl w-full" id="hub-live-stats">
                    @foreach (['Active venues', 'Orders today', 'Bot events', 'Avg rating'] as $label)
                        <div class="hub-stat-chip">
                            <p class="hub-stat-chip__label">{{ $label }}</p>
                            <p class="hub-stat-chip__value analysis-skeleton h-7 rounded-lg"></p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Quick jump --}}
    <div class="hub-quicknav mb-6">
        <p class="text-[10px] font-black text-white/35 uppercase tracking-widest mb-3 px-1">Quick jump</p>
        <div class="hub-quicknav__track">
            @foreach ($sections as $section)
                <a href="{{ route($section['route']) }}"
                   class="hub-pill hub-pill--{{ $section['theme'] }}">
                    <span class="hub-pill__icon">{{ $section['icon'] }}</span>
                    <span>{{ $section['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Section cards --}}
    <div class="flex items-center justify-between gap-4 mb-5 px-1">
        <div>
            <h3 class="text-lg font-black text-white">Analytics reports</h3>
            <p class="text-xs text-white/40 mt-0.5">Chagua report — data zote ni platform-wide counts</p>
        </div>
        <span class="text-[10px] font-bold text-white/30 uppercase tracking-wider hidden sm:inline">8 sections</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-5">
        @foreach ($sections as $index => $section)
            <a href="{{ route($section['route']) }}"
               class="hub-card hub-card--{{ $section['theme'] }} hub-animate-in group"
               style="animation-delay: {{ $index * 0.04 }}s">
                <div class="hub-card__glow" aria-hidden="true"></div>
                <div class="hub-card__top">
                    <span class="hub-card__icon">{{ $section['icon'] }}</span>
                    <span class="hub-card__tag">{{ $section['tag'] }}</span>
                </div>
                <h3 class="hub-card__title">{{ $section['label'] }}</h3>
                <p class="hub-card__desc">{{ $section['desc'] }}</p>
                <div class="hub-card__footer">
                    <span class="hub-card__cta">Open report</span>
                    <span class="hub-card__arrow" aria-hidden="true">→</span>
                </div>
            </a>
        @endforeach
    </div>

    @include('admin.tiptap-analysis.partials.hub-engine', ['currencySymbol' => $currencySymbol])
</x-admin-layout>
