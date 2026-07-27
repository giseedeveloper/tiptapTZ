@props(['href', 'active' => false, 'icon', 'label', 'badge' => null])

<a href="{{ $href }}" 
   class="flex items-center gap-4 px-4 py-3 rounded-xl font-bold transition-all duration-300 group {{ $active ? 'bg-fin-primary text-white shadow-lg shadow-fin-primary/20' : 'text-[#64708B] hover:text-fin-primary-deep hover:bg-fin-mist' }}">
    <div class="flex-shrink-0">
        <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $active ? 'text-white' : 'group-hover:text-fin-primary' }} transition-colors"></i>
    </div>
    <span class="sidebar-text transition-opacity duration-300 whitespace-nowrap flex-1">{{ $label }}</span>
    @if($badge)
        <span class="sidebar-text ml-auto bg-fin-primary-dark text-[10px] px-2 py-0.5 rounded-full text-white transition-opacity duration-300">{{ $badge }}</span>
    @endif
</a>
