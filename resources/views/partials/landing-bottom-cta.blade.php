{{-- Bottom CTA — primary signup + nurture paths --}}
<section class="py-16 lg:py-24 px-5">
    <div class="max-w-4xl mx-auto rounded-[2.5rem] overflow-hidden relative reveal">
        <div class="absolute inset-0 bg-gradient-to-br from-[#8C71F6] via-[#6D52E8] to-[#5B3FD6]"></div>
        <div class="absolute inset-0 opacity-30" style="background: radial-gradient(circle at 30% 20%, white, transparent 45%), radial-gradient(circle at 80% 80%, #C6BDFA, transparent 40%);"></div>
        <div class="relative px-8 py-16 lg:py-20 text-center">
            <h2 class="text-3xl lg:text-4xl font-light text-white mb-4 tracking-tight">{{ $landing['cta']['title'] }}</h2>
            <p class="text-white/85 font-normal mb-8 max-w-md mx-auto text-base leading-relaxed">{{ $landing['cta']['description'] }}</p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-6">
                <a href="{{ route('restaurant.register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-10 py-4 text-sm font-bold text-fin-primary-dark shadow-2xl hover:scale-[1.03] transition-transform">
                    {{ $landing['cta']['button'] }}
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                @include('partials.landing-book-demo-link', ['variant' => 'white'])
            </div>

            @if (filled($landing['nurture']['cta_secondary_hint']))
                <p class="text-white/75 text-sm max-w-lg mx-auto leading-relaxed mb-5">{{ $landing['nurture']['cta_secondary_hint'] }}</p>
            @endif

            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                @include('partials.landing-chat-with-us-link', ['variant' => 'ghost', 'class' => '!text-white/90 hover:!text-white'])
                <a href="#lead-magnet" class="inline-flex items-center gap-1.5 text-sm font-medium text-white/80 hover:text-white transition-colors">
                    <i data-lucide="book-open" class="w-4 h-4"></i>
                    Free efficiency guide
                </a>
            </div>
        </div>
    </div>
</section>
