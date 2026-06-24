{{-- TipTap Rafiki warm intro in hero (below headline) --}}
@if (filled($landing['hero']['rafiki_intro']))
    <div class="hero-rafiki-intro mb-5 max-w-lg mx-auto lg:mx-0">
        <div class="flex items-start gap-3 rounded-2xl border border-fin-primary/15 bg-white/75 px-4 py-3.5 shadow-sm backdrop-blur-sm">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-fin-primary to-fin-primary-dark shadow-md shadow-fin-primary/20">
                <img src="{{ asset('images/logo.png') }}" alt="" class="h-6 w-6 rounded-md bg-white object-contain p-0.5" aria-hidden="true">
            </span>
            <div class="min-w-0 text-left">
                <p class="text-sm sm:text-[0.95rem] font-medium leading-relaxed text-fin-ink">
                    {!! str_replace('Rafiki', '<span class="text-fin-primary-dark font-semibold">Rafiki</span>', e($landing['hero']['rafiki_intro'])) !!}
                </p>
                @if (filled($landing['hero']['rafiki_meaning']))
                    <p class="mt-1.5 text-xs leading-relaxed text-fin-muted">
                        {{ $landing['hero']['rafiki_meaning'] }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endif
