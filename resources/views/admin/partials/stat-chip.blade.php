@props(['label', 'value', 'tone' => 'violet', 'id' => null])

@php
    $valueClass = match ($tone) {
        'cyan' => 'text-cyan-400',
        'emerald' => 'text-emerald-400',
        'amber' => 'text-amber-400',
        'rose' => 'text-rose-400',
        'blue' => 'text-blue-400',
        'pink' => 'text-pink-400',
        'blue' => 'text-blue-400',
        'white' => 'text-white',
        default => 'text-violet-400',
    };
@endphp

<div class="admin-stat-chip">
    <p class="text-[9px] font-black text-white/40 uppercase tracking-wider">{{ $label }}</p>
    <p @if($id) id="{{ $id }}" @endif class="text-lg font-black mt-1 tabular-nums {{ $valueClass }}">{{ $value }}</p>
</div>
