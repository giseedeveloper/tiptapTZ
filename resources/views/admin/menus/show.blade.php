<x-admin-layout>
    <x-slot name="header">{{ $restaurant->name }} — Menu</x-slot>
    <div class="mb-4">
        <a href="{{ route('admin.menus.index') }}" class="text-sm text-violet-400 hover:text-violet-300">← All menus</a>
    </div>
    <div class="glass-card rounded-2xl overflow-hidden border border-white/10">
        <div class="p-6 border-b border-white/5 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-black text-white">{{ $restaurant->name }}</h2>
                <p class="text-xs text-white/40 mt-1">{{ $menuItems->count() }} items · read-only</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/10 text-white/60 uppercase">View only</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead><tr class="bg-white/5">
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Item</th>
                    <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Category</th>
                    <th class="px-6 py-4 text-right text-[10px] font-black text-white/40 uppercase">Price</th>
                    <th class="px-6 py-4 text-center text-[10px] font-black text-white/40 uppercase">Available</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($menuItems as $item)
                        <tr class="hover:bg-white/5 {{ !$item->is_available ? 'opacity-50' : '' }}">
                            <td class="px-6 py-4">
                                <p class="text-white font-medium">{{ $item->name }}</p>
                                @if($item->description)<p class="text-xs text-white/40 mt-1">{{ Str::limit($item->description, 80) }}</p>@endif
                            </td>
                            <td class="px-6 py-4 text-sm text-white/60">{{ $item->category?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-right text-white font-semibold">{{ $currencySymbol }} {{ number_format($item->price) }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold {{ $item->is_available ? 'text-emerald-400' : 'text-rose-400' }}">{{ $item->is_available ? 'Yes' : 'No' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-white/40">No menu items</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
