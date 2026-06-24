{{-- Rafiki video demo + live WhatsApp try --}}
<section id="demo" class="py-20 lg:py-28 bg-gradient-to-b from-white via-fin-mist/40 to-fin-surface relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 rounded-full bg-fin-primary/10 blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full bg-whatsapp/10 blur-3xl pointer-events-none"></div>

    <div class="max-w-6xl mx-auto px-5 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div class="order-2 lg:order-1">
                @include('partials.landing-demo-media')
            </div>

            <div class="order-1 lg:order-2 reveal">
                <span class="section-label mb-4">Live demo</span>
                <h2 class="text-3xl lg:text-[2.35rem] font-light text-fin-ink tracking-tight mt-4 leading-tight">
                    {{ $landing['demo']['title'] }}
                </h2>
                <p class="text-fin-muted mt-5 text-base leading-relaxed max-w-lg">
                    {{ $landing['demo']['subtitle'] }}
                </p>

                <ol class="mt-8 space-y-4" data-demo-steps>
                    @foreach ($landing['demo']['steps'] as $index => $step)
                        <li>
                            <button type="button"
                                    class="demo-step-btn group w-full flex items-start gap-4 text-left rounded-2xl border border-transparent px-3 py-2 -mx-3 transition-all hover:border-fin-primary/15 hover:bg-white/70"
                                    data-demo-step="{{ $index }}">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white border border-fin-primary/15 shadow-sm group-[.is-active]:bg-fin-primary group-[.is-active]:border-fin-primary group-[.is-active]:text-white text-fin-primary-dark transition-colors">
                                    <i data-lucide="{{ $step['icon'] }}" class="w-5 h-5"></i>
                                </span>
                                <span class="min-w-0 pt-0.5">
                                    <span class="block text-sm font-semibold text-fin-ink">{{ $step['title'] }}</span>
                                    <span class="block text-xs text-fin-muted mt-1 leading-relaxed">{{ $step['caption'] }}</span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ol>

                <div class="mt-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <a href="{{ $landing['demo']['try_rafiki_url'] }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2.5 rounded-2xl px-7 py-4 text-sm font-bold text-white bg-whatsapp shadow-[0_8px_28px_rgba(37,211,102,0.35)] hover:brightness-105 hover:-translate-y-0.5 transition-all">
                        <i data-lucide="message-circle" class="w-5 h-5"></i>
                        {{ $landing['demo']['try_rafiki_label'] }}
                    </a>
                    <a href="{{ route('restaurant.register') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-2xl px-7 py-4 text-sm font-semibold text-fin-ink bg-white border border-fin-ink/10 shadow-sm hover:border-fin-primary/30 transition-all">
                        Start free trial
                        <i data-lucide="arrow-right" class="w-4 h-4 text-fin-primary"></i>
                    </a>
                </div>
                @if (filled($landing['demo']['try_rafiki_hint']))
                    <p class="mt-4 text-xs text-fin-muted leading-relaxed max-w-md">
                        {{ $landing['demo']['try_rafiki_hint'] }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</section>

@if (! $landing['demo']['video_embed'])
<script>
(function () {
    const root = document.querySelector('[data-demo-walkthrough]');
    if (!root) return;

    const scenes = root.querySelectorAll('.demo-walkthrough-scene');
    const dots = root.querySelectorAll('[data-demo-dot]');
    const stepBtns = document.querySelectorAll('[data-demo-step]');
    let current = 0;
    let timer;

    function show(index) {
        current = index;
        scenes.forEach((scene, i) => {
            const active = i === index;
            scene.classList.toggle('is-active', active);
            scene.classList.toggle('opacity-0', !active);
            scene.classList.toggle('pointer-events-none', !active);
        });
        dots.forEach((dot, i) => {
            const active = i === index;
            dot.classList.toggle('w-6', active);
            dot.classList.toggle('w-2', !active);
            dot.classList.toggle('bg-fin-primary', active);
            dot.classList.toggle('bg-fin-primary/25', !active);
            dot.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        stepBtns.forEach((btn, i) => btn.classList.toggle('is-active', i === index));
    }

    function next() {
        show((current + 1) % scenes.length);
    }

    function restart() {
        clearInterval(timer);
        timer = setInterval(next, 3200);
    }

    dots.forEach(dot => dot.addEventListener('click', () => {
        show(Number(dot.dataset.demoDot));
        restart();
    }));

    stepBtns.forEach(btn => btn.addEventListener('click', () => {
        show(Number(btn.dataset.demoStep));
        restart();
    }));

    show(0);
    restart();
})();
</script>
@endif
