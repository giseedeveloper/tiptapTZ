@props([
    'variant' => 'outline',
    'class' => '',
])

@php
    $nurture = $landing['nurture'];
    $baseClass = match ($variant) {
        'nav' => 'inline-flex items-center justify-center gap-1.5 rounded-xl border border-fin-primary/25 bg-white px-4 py-2.5 text-sm font-semibold text-fin-primary-dark shadow-sm hover:border-fin-primary/40 hover:bg-fin-mist transition-all',
        'white' => 'inline-flex items-center justify-center gap-2 rounded-full bg-white/95 px-8 py-4 text-sm font-bold text-fin-primary-dark shadow-xl hover:scale-[1.02] transition-transform',
        'ghost' => 'inline-flex items-center gap-1.5 text-sm font-semibold text-fin-primary-dark hover:text-fin-primary transition-colors',
        'solid-light' => 'inline-flex items-center justify-center gap-2 rounded-2xl bg-white border border-fin-ink/10 px-6 py-3.5 text-sm font-semibold text-fin-ink shadow-sm hover:border-fin-primary/30 transition-all',
        'menu-item' => 'flex items-center gap-3 px-4 py-3 text-sm text-fin-muted hover:bg-fin-mist hover:text-fin-ink transition-colors',
        default => 'inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-fin-primary/30 bg-white px-6 py-3.5 text-sm font-semibold text-fin-primary-dark hover:bg-fin-mist transition-all',
    };
@endphp

<a href="{{ $nurture['book_demo_url'] }}"
   @if ($nurture['book_demo_opens_new_tab']) target="_blank" rel="noopener noreferrer" @endif
   class="{{ trim($baseClass.' '.$class) }}">
    <i data-lucide="calendar-clock" class="w-4 h-4 shrink-0"></i>
    {{ $nurture['book_demo_label'] }}
</a>
