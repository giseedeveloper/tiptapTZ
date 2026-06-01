@php
    $eyebrow = $eyebrow ?? 'Admin';
    $subtitle = $subtitle ?? null;
    $accent = $accent ?? 'violet';
@endphp

<div class="admin-page-hero admin-page-hero--{{ $accent }} rounded-3xl p-6 md:p-7 mb-6 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-56 h-56 bg-violet-500/10 rounded-full blur-3xl pointer-events-none -translate-y-1/2 translate-x-1/4"></div>
    <div class="relative z-10">
        <p class="text-[10px] font-black text-cyan-400/90 uppercase tracking-[0.28em] mb-1.5">{{ $eyebrow }}</p>
        <h2 class="text-xl md:text-2xl font-black text-white tracking-tight">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-sm text-white/45 mt-1.5 max-w-2xl">{{ $subtitle }}</p>
        @endif
    </div>
</div>
