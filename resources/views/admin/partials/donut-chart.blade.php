@props(['segments' => [], 'centerLabel' => '', 'centerSub' => '', 'size' => '11rem'])

@php
    $total = max(collect($segments)->sum('value'), 1);
    $gradientParts = [];
    $cursor = 0;
    foreach ($segments as $segment) {
        $pct = ($segment['value'] / $total) * 100;
        $end = min(100, $cursor + $pct);
        $gradientParts[] = ($segment['color'] ?? '#8C71F6').' '.$cursor.'% '.$end.'%';
        $cursor = $end;
    }
    $gradient = count($gradientParts) > 0
        ? 'conic-gradient(from -90deg, '.implode(', ', $gradientParts).')'
        : 'conic-gradient(from -90deg, rgba(255,255,255,0.08) 0% 100%)';
@endphp

<div class="flex flex-col items-center gap-5">
    <div class="relative shrink-0" style="width: {{ $size }}; height: {{ $size }};">
        <div class="w-full h-full rounded-full shadow-lg shadow-fin-primary/20" style="background: {{ $gradient }};"></div>
        <div class="absolute inset-[14%] rounded-full bg-[#12101c] border border-white/10 flex flex-col items-center justify-center text-center px-2">
            <span class="text-2xl font-black text-white leading-none">{{ $centerLabel }}</span>
            @if($centerSub)
                <span class="text-[9px] font-bold text-white/40 uppercase tracking-widest mt-1">{{ $centerSub }}</span>
            @endif
        </div>
    </div>
    <ul class="w-full space-y-2">
        @foreach($segments as $segment)
            <li class="flex items-center justify-between gap-2 text-xs">
                <span class="flex items-center gap-2 text-white/70 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $segment['color'] }}"></span>
                    <span class="truncate">{{ $segment['label'] }}</span>
                </span>
                <span class="font-bold text-white tabular-nums">{{ $segment['value'] }}</span>
            </li>
        @endforeach
    </ul>
</div>
