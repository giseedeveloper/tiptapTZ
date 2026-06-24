{{-- Video embed or interactive walkthrough --}}
@php
    $demo = $landing['demo'];
    $embed = $demo['video_embed'];
    $poster = filled($embed['poster'] ?? null) ? $embed['poster'] : asset($demo['video_poster']);
@endphp

<div class="demo-media reveal">
    @if ($embed)
        <div class="relative rounded-3xl overflow-hidden border border-fin-ink/8 bg-fin-ink shadow-[0_24px_60px_-12px_rgba(109,82,232,0.35)]">
            <div class="aspect-video w-full bg-fin-ink">
                @if ($embed['provider'] === 'file')
                    <video class="w-full h-full object-cover" controls playsinline preload="metadata" poster="{{ $poster }}">
                        <source src="{{ $embed['embed_url'] }}" type="video/mp4">
                    </video>
                @else
                    <iframe
                        src="{{ $embed['embed_url'] }}"
                        title="{{ $demo['title'] }}"
                        class="w-full h-full"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                    ></iframe>
                @endif
            </div>
        </div>
    @else
        <div class="relative">
            <span class="absolute top-4 left-4 z-10 inline-flex items-center gap-1.5 rounded-full bg-white/90 backdrop-blur px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-fin-primary-dark shadow-sm border border-white">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-fin-primary opacity-60"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-fin-primary"></span>
                </span>
                {{ $demo['walkthrough_label'] }}
            </span>
            @include('partials.landing-demo-walkthrough')
        </div>
    @endif
</div>
