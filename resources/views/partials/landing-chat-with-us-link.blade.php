@props([
    'variant' => 'ghost',
    'class' => '',
])

@php
    $nurture = $landing['nurture'];
    $baseClass = match ($variant) {
        'solid' => 'inline-flex items-center justify-center gap-2 rounded-2xl bg-whatsapp px-6 py-3.5 text-sm font-bold text-white shadow-md hover:brightness-105 transition-all',
        'nav' => 'inline-flex items-center gap-1.5 text-sm font-medium text-fin-muted hover:text-whatsapp transition-colors',
        default => 'inline-flex items-center gap-1.5 text-sm font-semibold text-whatsapp hover:brightness-110 transition-all',
    };
@endphp

<a href="{{ $nurture['chat_with_us_url'] }}"
   target="_blank"
   rel="noopener noreferrer"
   class="{{ trim($baseClass.' '.$class) }}">
    <i data-lucide="message-circle" class="w-4 h-4 shrink-0"></i>
    {{ $nurture['chat_with_us_label'] }}
</a>
