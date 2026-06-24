{{-- Hero trust bar: social proof metrics + payment partner logos (below sub-headline, before CTAs) --}}
@if (! empty($landing['trust']['metrics']) || ! empty($landing['trust']['partners']) || ! empty($landing['trust']['featured_in']))
    <div class="hero-trust-bar mb-8 max-w-lg mx-auto lg:mx-0" aria-label="TipTap trust and credibility">
        @if (! empty($landing['trust']['headline']))
            <p class="flex items-center justify-center lg:justify-start gap-2 text-xs font-semibold text-fin-primary-dark mb-4">
                <i data-lucide="shield-check" class="w-4 h-4 shrink-0 text-fin-primary"></i>
                {{ $landing['trust']['headline'] }}
            </p>
        @endif

        @if (! empty($landing['trust']['metrics']))
            <div class="hero-trust-metrics grid grid-cols-2 gap-3">
                @foreach ($landing['trust']['metrics'] as $metric)
                    <div class="hero-trust-metric flex items-center gap-3 rounded-xl bg-white/80 border border-fin-ink/5 px-3.5 py-3">
                        @if (! empty($metric['icon']))
                            <span class="hero-trust-metric-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-fin-mist border border-fin-primary/10">
                                <i data-lucide="{{ $metric['icon'] }}" class="w-4 h-4 text-fin-primary-dark"></i>
                            </span>
                        @endif
                        <div class="min-w-0 text-left">
                            <p class="text-lg sm:text-xl font-semibold tracking-tight text-fin-ink leading-none">{{ $metric['value'] }}</p>
                            <p class="text-[11px] sm:text-xs font-medium text-fin-muted mt-1 leading-snug">{{ $metric['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if (! empty($landing['trust']['partners']))
            <div class="hero-trust-partners mt-4 pt-4 border-t border-fin-ink/8">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-fin-muted mb-3 text-center lg:text-left">
                    {{ $landing['trust']['partners_label'] }}
                </p>
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-x-5 gap-y-3">
                    @foreach ($landing['trust']['partners'] as $partner)
                        <img
                            src="{{ asset($partner['logo']) }}"
                            alt="{{ $partner['name'] }}"
                            width="{{ $partner['width'] ?? 96 }}"
                            height="28"
                            class="hero-trust-logo h-7 w-auto max-w-[7rem] object-contain object-left opacity-90 hover:opacity-100 transition-opacity"
                            loading="lazy"
                            decoding="async"
                        >
                    @endforeach
                </div>
            </div>
        @endif

        @if (! empty($landing['trust']['featured_in']))
            <div class="hero-trust-featured mt-4 pt-4 border-t border-fin-ink/8">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-fin-muted mb-3 text-center lg:text-left">
                    {{ $landing['trust']['featured_in_label'] }}
                </p>
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-x-5 gap-y-3">
                    @foreach ($landing['trust']['featured_in'] as $outlet)
                        <img
                            src="{{ asset($outlet['logo']) }}"
                            alt="{{ $outlet['name'] }}"
                            width="{{ $outlet['width'] ?? 88 }}"
                            height="24"
                            class="hero-trust-logo h-5 w-auto max-w-[6rem] object-contain object-left opacity-75 hover:opacity-100 transition-opacity grayscale hover:grayscale-0"
                            loading="lazy"
                            decoding="async"
                        >
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
