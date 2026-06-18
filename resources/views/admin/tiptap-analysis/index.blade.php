<x-admin-layout>
    <x-slot name="header">TipTap Analysis</x-slot>

    @include('admin.tiptap-analysis.partials.styles')

    <div class="analysis-hero rounded-3xl p-6 md:p-8 mb-8 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-80 h-80 bg-fuchsia-500/15 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="relative z-10">
            <p class="text-[10px] font-black text-fin-primary uppercase tracking-[0.3em] mb-2">Smart intelligence</p>
            <h2 class="text-2xl md:text-3xl font-black text-white tracking-tight">TipTap Analysis</h2>
            <p class="text-sm text-white/50 mt-2 max-w-2xl">
                Chagua aina ya data unayotaka kuona. Kila sehemu iko kwenye ukurasa wake — rahisi kusoma na kushughulikia.
            </p>
        </div>
    </div>

    @php
        $sections = [
            ['route' => 'admin.tiptap-analysis.platform', 'label' => 'Platform', 'desc' => 'Orders, revenue & top venues', 'icon' => '📊', 'border' => 'border-violet-500/25'],
            ['route' => 'admin.tiptap-analysis.whatsapp', 'label' => 'WhatsApp', 'desc' => 'Bot menu options & daily activity', 'icon' => '💬', 'border' => 'border-emerald-500/25'],
            ['route' => 'admin.tiptap-analysis.qr-entry', 'label' => 'QR Entry', 'desc' => 'Waiter, table & restaurant tag scans', 'icon' => '📱', 'border' => 'border-cyan-500/25'],
            ['route' => 'admin.tiptap-analysis.journey', 'label' => 'Journey', 'desc' => 'QR scan → payment funnel', 'icon' => '🛤️', 'border' => 'border-fuchsia-500/25'],
            ['route' => 'admin.tiptap-analysis.feedback', 'label' => 'Feedback', 'desc' => 'Ratings, alerts & comments', 'icon' => '⭐', 'border' => 'border-amber-500/25'],
            ['route' => 'admin.tiptap-analysis.tips-payments', 'label' => 'Tips & Pay', 'desc' => 'Tips, cash vs digital payments', 'icon' => '💳', 'border' => 'border-pink-500/25'],
            ['route' => 'admin.tiptap-analysis.language', 'label' => 'Language', 'desc' => 'EN/SW split & peak hours', 'icon' => '🌐', 'border' => 'border-indigo-500/25'],
            ['route' => 'admin.tiptap-analysis.venues', 'label' => 'Venues', 'desc' => 'Compare all restaurants', 'icon' => '🏪', 'border' => 'border-fin-primary/25'],
        ];
    @endphp

  {{-- Quick pills (like your image) --}}
    <div class="flex flex-wrap gap-2 mb-8">
        @foreach ($sections as $section)
            <a href="{{ route($section['route']) }}"
               class="analysis-hub-pill px-4 py-2 rounded-xl text-xs font-bold text-white/55">
                {{ $section['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Big cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($sections as $section)
            <a href="{{ route($section['route']) }}"
               class="analysis-hub-card rounded-2xl p-6 block {{ $section['border'] }} group">
                <span class="text-3xl mb-4 block group-hover:scale-110 transition-transform">{{ $section['icon'] }}</span>
                <h3 class="text-lg font-black text-white mb-1">{{ $section['label'] }}</h3>
                <p class="text-xs text-white/45 leading-relaxed">{{ $section['desc'] }}</p>
                <p class="text-[10px] font-bold text-fin-primary uppercase tracking-wider mt-4 group-hover:text-fin-lavender">
                    Open report →
                </p>
            </a>
        @endforeach
    </div>
</x-admin-layout>
