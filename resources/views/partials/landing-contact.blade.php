@php
    $market = $landing['market'] ?? config('tiptap.market', 'tz');
    $offices = $landing['offices'] ?? [];
    $whatsappUrl = $landing['whatsapp_url'] ?? '';
    $orderedOfficeKeys = array_values(array_unique([$market, ...array_keys($offices)]));
@endphp

<section id="contact" class="py-16 lg:py-24 px-5 bg-fin-surface">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 reveal">
            <span class="section-label mb-4">{{ $landing['contact']['label'] }}</span>
            <h2 class="text-3xl lg:text-4xl font-light text-fin-ink tracking-tight mb-4">{{ $landing['contact']['title'] }}</h2>
            <p class="text-fin-muted max-w-2xl mx-auto text-base leading-relaxed">
                {{ $landing['contact']['description'] }}
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-6 lg:gap-8 mb-12">
            @foreach ($orderedOfficeKeys as $officeKey)
                @php
                    $office = $offices[$officeKey] ?? null;
                @endphp
                @if ($office)
                    <article
                        class="fin-card rounded-3xl p-8 reveal {{ $officeKey === $market ? 'ring-2 ring-fin-primary/30' : '' }}"
                    >
                        <div class="flex items-start gap-4 mb-5">
                            <img
                                src="{{ public_asset('images/flags/'.$office['flag'].'.svg') }}"
                                alt="{{ $office['name'] }}"
                                width="40"
                                height="28"
                                class="h-7 w-10 shrink-0 rounded-[3px] shadow-sm ring-1 ring-black/10 object-cover"
                            >
                            <div>
                                <p class="text-xs font-bold uppercase tracking-widest text-fin-primary mb-1">
                                    Office
                                </p>
                                <h3 class="text-xl font-semibold text-fin-ink">{{ $office['name'] }}</h3>
                                <p class="text-sm text-fin-muted mt-1">{{ $office['city'] }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3 text-sm text-fin-muted leading-relaxed">
                            <i data-lucide="map-pin" class="w-5 h-5 text-fin-primary shrink-0 mt-0.5"></i>
                            <address class="not-italic">
                                @foreach ($office['lines'] as $line)
                                    <span class="block">{{ $line }}</span>
                                @endforeach
                            </address>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>

        <div class="fin-card rounded-3xl p-8 lg:p-10 reveal">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                <div>
                    <h3 class="text-lg font-semibold text-fin-ink mb-2">{{ $landing['contact']['social_title'] }}</h3>
                    <p class="text-sm text-fin-muted max-w-md">
                        {{ $landing['contact']['social_description'] }}
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                    @include('partials.social-links', [
                        'containerClass' => 'flex flex-wrap gap-3',
                        'linkClass' => 'inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white border border-fin-ink/8 shadow-sm hover:shadow-md hover:scale-105 transition-all',
                        'iconClass' => 'w-6 h-6',
                        'onDark' => false,
                        'showPlaceholder' => true,
                    ])
                    @if (filled($whatsappUrl))
                        <a
                            href="{{ $whatsappUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-xl bg-whatsapp px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-whatsapp/25 hover:scale-[1.02] transition-transform"
                        >
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            WhatsApp us
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
