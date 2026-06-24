{{-- Dedicated payment partners logo bar --}}
@php
    $partners = $landing['partners'] ?? [];
    $groups = $partners['groups'] ?? [];
@endphp

@if (! empty($groups))
    <section id="partners" class="py-16 lg:py-22 bg-white border-y border-fin-ink/5">
        <div class="max-w-6xl mx-auto px-5">
            <div class="text-center max-w-2xl mx-auto mb-12 reveal">
                @if (filled($partners['eyebrow'] ?? ''))
                    <span class="section-label mb-4">{{ $partners['eyebrow'] }}</span>
                @endif
                <h2 class="text-2xl lg:text-3xl font-light text-fin-ink tracking-tight mt-4 leading-tight">
                    {{ $partners['title'] ?? 'Powered by trusted payment partners' }}
                </h2>
                @if (filled($partners['subtitle'] ?? ''))
                    <p class="text-fin-muted mt-4 text-base leading-relaxed">{{ $partners['subtitle'] }}</p>
                @endif
            </div>

            <div class="grid gap-6 lg:gap-8">
                @foreach ($groups as $group)
                    @if (! empty($group['items']))
                        <div class="partners-group reveal rounded-2xl border border-fin-ink/6 bg-gradient-to-br from-fin-surface/80 via-white to-fin-mist/40 px-6 py-6 sm:px-8 sm:py-7 shadow-sm">
                            @if (filled($group['label'] ?? ''))
                                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-fin-muted mb-5 text-center">
                                    {{ $group['label'] }}
                                </p>
                            @endif
                            <div class="flex flex-wrap items-center justify-center gap-x-10 gap-y-6 sm:gap-x-14">
                                @foreach ($group['items'] as $partner)
                                    <div class="partners-logo-slot flex flex-col items-center gap-2 min-w-[5.5rem]">
                                        <img
                                            src="{{ asset($partner['logo']) }}"
                                            alt="{{ $partner['name'] }}"
                                            width="{{ $partner['width'] ?? 96 }}"
                                            height="32"
                                            class="partners-logo h-8 sm:h-9 w-auto max-w-[8.5rem] object-contain opacity-90 hover:opacity-100 transition-opacity"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                        <span class="text-[10px] font-medium text-fin-muted/80 sr-only">{{ $partner['name'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            @if (filled($partners['footnote'] ?? ''))
                <p class="text-center text-xs text-fin-muted mt-8 max-w-xl mx-auto leading-relaxed reveal">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 inline-block -mt-0.5 mr-1 text-fin-primary"></i>
                    {{ $partners['footnote'] }}
                </p>
            @endif
        </div>
    </section>
@endif
