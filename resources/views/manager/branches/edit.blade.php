<x-manager-layout>
    <x-slot name="header">Edit Branch — {{ $branch->displayName() }}</x-slot>

    <div class="max-w-2xl">

        {{-- Back link --}}
        <div class="mb-6">
            <a href="{{ route('manager.branches.show', $branch) }}"
               class="inline-flex items-center gap-2 text-white/50 hover:text-white transition-colors text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Back to Branch
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                <p class="text-sm font-medium text-emerald-100/90">{{ session('success') }}</p>
            </div>
        @endif

        <div class="glass-card rounded-2xl p-6 sm:p-8 relative overflow-hidden">
            <div class="absolute -top-16 -right-16 w-48 h-48 bg-gradient-to-br from-indigo-500/10 to-violet-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-white tracking-tight">Branch Settings</h2>
                    <p class="text-sm text-white/40 mt-1">Update the details for <span class="text-white/70">{{ $branch->displayName() }}</span>.</p>
                </div>

                <form action="{{ route('manager.branches.update', $branch) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-5">

                        {{-- Branch Display Name --}}
                        <div>
                            <label for="branch_name" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Branch Display Name
                            </label>
                            <input
                                type="text"
                                id="branch_name"
                                name="branch_name"
                                value="{{ old('branch_name', $branch->branch_name) }}"
                                placeholder="e.g. CBD Branch, Airport Branch"
                                class="w-full glass rounded-xl px-4 py-3 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all text-sm"
                            >
                            <p class="mt-1.5 text-white/30 text-xs">Shown to customers and staff. Leave blank to use the restaurant name.</p>
                            @error('branch_name')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Location --}}
                        <div>
                            <label for="location" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Location
                            </label>
                            <input
                                type="text"
                                id="location"
                                name="location"
                                value="{{ old('location', $branch->location) }}"
                                placeholder="e.g. Westlands, Nairobi"
                                class="w-full glass rounded-xl px-4 py-3 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all text-sm"
                            >
                            @error('location')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Phone
                            </label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                value="{{ old('phone', $branch->phone) }}"
                                placeholder="e.g. +254 700 000 000"
                                class="w-full glass rounded-xl px-4 py-3 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all text-sm"
                            >
                            @error('phone')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label for="branch_sort_order" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Sort Order
                            </label>
                            <input
                                type="number"
                                id="branch_sort_order"
                                name="branch_sort_order"
                                value="{{ old('branch_sort_order', $branch->branch_sort_order) }}"
                                min="0"
                                placeholder="0"
                                class="w-full glass rounded-xl px-4 py-3 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all text-sm"
                            >
                            <p class="mt-1.5 text-white/30 text-xs">Lower numbers appear first in lists. Use 0 for default ordering.</p>
                            @error('branch_sort_order')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- Divider --}}
                    <div class="border-t border-white/5 my-8"></div>

                    {{-- Actions --}}
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button
                            type="submit"
                            class="bg-gradient-to-r from-violet-600 to-cyan-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg hover:shadow-violet-500/25 transition-all flex items-center justify-center gap-2 text-sm"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                            </svg>
                            Save Changes
                        </button>
                        <a href="{{ route('manager.branches.show', $branch) }}"
                           class="glass px-6 py-3 rounded-xl font-semibold text-white/70 hover:text-white transition-all text-sm text-center">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>

        {{-- Danger Zone --}}
        <div class="glass-card rounded-2xl p-6 mt-6 border border-rose-500/10">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-rose-500/10 rounded-xl flex items-center justify-center shrink-0 border border-rose-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-400">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-rose-400 uppercase tracking-wider mb-1">Danger Zone</h3>
                    <p class="text-xs text-white/50">Deleting a branch is permanent. All associated data, waiters, and settings will be removed.</p>
                    <form method="POST" action="{{ route('manager.branches.destroy', $branch) }}" class="mt-4"
                          onsubmit="return confirm('Are you sure you want to delete this branch? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg text-xs font-bold uppercase tracking-wider border border-rose-500/20 transition-colors">
                            Delete Branch
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</x-manager-layout>
