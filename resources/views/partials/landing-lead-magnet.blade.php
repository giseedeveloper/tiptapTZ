{{-- Lead magnet email capture — nurture list --}}
@php
    $nurture = $landing['nurture'];
    $submitted = session('lead_magnet_success');
@endphp

<section id="lead-magnet" class="py-16 lg:py-20 px-5 bg-gradient-to-b from-fin-surface to-white">
    <div class="max-w-4xl mx-auto reveal">
        <div class="rounded-[2rem] border border-fin-primary/15 bg-white shadow-[0_16px_50px_-12px_rgba(109,82,232,0.18)] overflow-hidden">
            <div class="grid lg:grid-cols-5">
                <div class="lg:col-span-2 bg-gradient-to-br from-fin-primary/10 via-fin-mist to-white p-8 lg:p-10 flex flex-col justify-center">
                    @if (filled($nurture['lead_magnet_eyebrow']))
                        <span class="section-label mb-4 w-fit">{{ $nurture['lead_magnet_eyebrow'] }}</span>
                    @endif
                    <h2 class="text-2xl lg:text-[1.65rem] font-light text-fin-ink tracking-tight leading-snug">
                        {{ $nurture['lead_magnet_title'] }}
                    </h2>
                    @if (filled($nurture['lead_magnet_subtitle']))
                        <p class="text-sm text-fin-muted mt-4 leading-relaxed">{{ $nurture['lead_magnet_subtitle'] }}</p>
                    @endif
                </div>

                <div class="lg:col-span-3 p-8 lg:p-10 flex flex-col justify-center border-t lg:border-t-0 lg:border-l border-fin-ink/6">
                    @if ($submitted)
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-6 text-center" role="status">
                            <i data-lucide="check-circle-2" class="w-10 h-10 text-emerald-600 mx-auto mb-3"></i>
                            <p class="text-sm font-semibold text-emerald-900">{{ $nurture['lead_magnet_success'] }}</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('landing.lead-magnet') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="lead-magnet-email" class="sr-only">Email address</label>
                                <input
                                    id="lead-magnet-email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    placeholder="you@restaurant.com"
                                    class="w-full rounded-2xl border border-fin-ink/10 bg-fin-surface/50 px-5 py-4 text-sm text-fin-ink placeholder:text-fin-muted/70 focus:border-fin-primary/40 focus:ring-2 focus:ring-fin-primary/15 outline-none transition-all @error('email') border-red-400 @enderror"
                                >
                                @error('email')
                                    <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="btn-glow w-full inline-flex items-center justify-center gap-2 rounded-2xl px-6 py-4 text-sm font-bold text-white">
                                {{ $nurture['lead_magnet_button'] }}
                                <i data-lucide="mail" class="w-4 h-4"></i>
                            </button>
                        </form>
                    @endif

                    @if (filled($nurture['lead_magnet_privacy']))
                        <p class="text-[11px] text-fin-muted mt-4 leading-relaxed text-center lg:text-left">{{ $nurture['lead_magnet_privacy'] }}</p>
                    @endif

                    <div class="mt-6 pt-6 border-t border-fin-ink/8 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        @include('partials.landing-book-demo-link', ['variant' => 'ghost'])
                        @include('partials.landing-chat-with-us-link', ['variant' => 'ghost'])
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
