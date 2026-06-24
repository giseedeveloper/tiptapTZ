{{-- Named testimonials with restaurant, city, optional photo, and data-backed highlights --}}
<section class="py-16 lg:py-20 bg-fin-mist/50 border-y border-fin-primary/5">
    <div class="max-w-6xl mx-auto px-5">
        <div class="text-center mb-12 reveal">
            <span class="section-label">Reviews</span>
            <h2 class="text-2xl lg:text-3xl font-light text-fin-ink mt-4">{{ $landing['testimonials']['title'] }}</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-5">
            @foreach ($landing['testimonials']['items'] as $testimonial)
                @php
                    $nameParts = preg_split('/\s+/', trim($testimonial['name'])) ?: [];
                    $initials = strtoupper(collect($nameParts)->take(2)->map(fn (string $part) => substr($part, 0, 1))->implode(''));
                @endphp
                <blockquote class="testimonial-card reveal flex flex-col h-full">
                    @if (! empty($testimonial['highlight']))
                        <span class="testimonial-highlight inline-flex self-start items-center gap-1.5 rounded-full bg-fin-mist border border-fin-primary/15 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-fin-primary-dark mb-4">
                            <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                            {{ $testimonial['highlight'] }}
                        </span>
                    @endif

                    <p class="text-fin-ink text-[0.95rem] leading-relaxed font-medium flex-1">
                        &ldquo;{{ $testimonial['quote'] }}&rdquo;
                    </p>

                    <footer class="mt-6 pt-5 border-t border-fin-ink/6 flex items-center gap-3">
                        @if (! empty($testimonial['photo']))
                            <img
                                src="{{ asset($testimonial['photo']) }}"
                                alt="{{ $testimonial['name'] }}"
                                width="48"
                                height="48"
                                class="testimonial-avatar-photo h-12 w-12 rounded-full object-cover ring-2 ring-white shadow-sm"
                                loading="lazy"
                                decoding="async"
                            >
                        @else
                            <div class="testimonial-avatar h-12 w-12 rounded-full flex items-center justify-center text-sm font-bold text-white shadow-sm ring-2 ring-white shrink-0" aria-hidden="true">
                                {{ $initials }}
                            </div>
                        @endif
                        <div class="min-w-0 text-left">
                            <p class="text-sm font-semibold text-fin-ink leading-tight">{{ $testimonial['name'] }}</p>
                            <p class="text-xs text-fin-muted mt-0.5">{{ $testimonial['role'] }}, {{ $testimonial['restaurant'] }}</p>
                            <p class="text-[11px] font-medium text-fin-primary uppercase tracking-wide mt-1">{{ $testimonial['city'] }}</p>
                        </div>
                    </footer>
                </blockquote>
            @endforeach
        </div>
    </div>
</section>
