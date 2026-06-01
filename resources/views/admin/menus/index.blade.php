<x-admin-layout>
    <x-slot name="header">Menus (Read-only)</x-slot>
    <div class="glass-card rounded-2xl overflow-hidden border border-white/10">
        <div class="p-6 border-b border-white/5">
            <h2 class="text-xl font-black text-white">Restaurant Menus</h2>
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">View-only — managers edit menus in their portal</p>
        </div>
        <table class="w-full">
            <thead><tr class="bg-white/5">
                <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Restaurant</th>
                <th class="px-6 py-4 text-left text-[10px] font-black text-white/40 uppercase">Items</th>
                <th class="px-6 py-4 text-right text-[10px] font-black text-white/40 uppercase">Action</th>
            </tr></thead>
            <tbody class="divide-y divide-white/5">
                @forelse($restaurants as $restaurant)
                    <tr class="hover:bg-white/5">
                        <td class="px-6 py-4 text-white font-semibold">{{ $restaurant->name }}</td>
                        <td class="px-6 py-4 text-white/60">{{ $restaurant->menu_items_count ?? 0 }} items</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.menus.show', $restaurant) }}" class="text-sm font-semibold text-violet-400 hover:text-violet-300">View menu →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-12 text-center text-white/40">No restaurants</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
