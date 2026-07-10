<x-manager-layout>
    <x-slot name="header">Create New Branch</x-slot>

    <div class="max-w-2xl">

        {{-- Back link --}}
        <div class="mb-6">
            <a href="{{ route('manager.branches.index') }}"
               class="inline-flex items-center gap-2 text-white/50 hover:text-white transition-colors text-sm font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Back to Branches
            </a>
        </div>

        <div class="glass-card rounded-2xl p-6 sm:p-8 relative overflow-hidden">
            {{-- Ambient orb --}}
            <div class="absolute -top-16 -right-16 w-48 h-48 bg-gradient-to-br from-violet-500/10 to-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-white tracking-tight">Branch Details</h2>
                    <p class="text-sm text-white/40 mt-1">Fill in the information for your new branch location.</p>
                </div>

                <form action="{{ route('manager.branches.store') }}" method="POST" x-data="{ selectedGroup: '{{ old('group_id', '') }}' }">
                    @csrf

                    <div class="space-y-5">

                        {{-- Restaurant Name --}}
                        <div>
                            <label for="name" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Restaurant Name <span class="text-rose-400">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                placeholder="e.g. Samaki Samaki"
                                class="w-full glass rounded-xl px-4 py-3 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/30 transition-all text-sm"
                            >
                            @error('name')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Branch Display Name --}}
                        <div>
                            <label for="branch_name" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Branch Display Name
                                <span class="normal-case font-normal text-white/30 ml-1">(optional)</span>
                            </label>
                            <input
                                type="text"
                                id="branch_name"
                                name="branch_name"
                                value="{{ old('branch_name') }}"
                                placeholder="e.g. CBD Branch, Airport Branch"
                                class="w-full glass rounded-xl px-4 py-3 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/30 transition-all text-sm"
                            >
                            <p class="mt-1.5 text-white/30 text-xs">If set, this name is shown to customers and staff instead of the restaurant name.</p>
                            @error('branch_name')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Location --}}
                        <div>
                            <label for="location" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Location
                                <span class="normal-case font-normal text-white/30 ml-1">(optional)</span>
                            </label>
                            <input
                                type="text"
                                id="location"
                                name="location"
                                value="{{ old('location') }}"
                                placeholder="e.g. Westlands, Nairobi"
                                class="w-full glass rounded-xl px-4 py-3 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/30 transition-all text-sm"
                            >
                            @error('location')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Phone
                                <span class="normal-case font-normal text-white/30 ml-1">(optional)</span>
                            </label>
                            <input
                                type="text"
                                id="phone"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="e.g. +254 700 000 000"
                                class="w-full glass rounded-xl px-4 py-3 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-violet-500/50 focus:border-violet-500/30 transition-all text-sm"
                            >
                            @error('phone')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Branch Group --}}
                        <div>
                            <label for="group_id" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                Branch Group
                                <span class="normal-case font-normal text-white/30 ml-1">(optional)</span>
                            </label>
                            <select
                                id="group_id"
                                name="group_id"
                                x-model="selectedGroup"
                                class="w-full glass rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-violet-500/50 transition-all text-sm appearance-none"
                                style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff40' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25em 1.25em; padding-right: 2.5rem;"
                            >
                                <option value="">No group</option>
                                <option value="__new__">+ Create new group</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <p class="mt-1.5 text-rose-400 text-xs">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- New Group Name (shown only when "__new__" is selected) --}}
                        <div x-show="selectedGroup === '__new__'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            <label for="group_name" class="block text-xs font-semibold text-white/70 uppercase tracking-wider mb-2">
                                New Group Name <span class="text-rose-400">*</span>
                            </label>
                            <input
                                type="text"
                                id="group_name"
                                name="group_name"
                                value="{{ old('group_name') }}"
                                placeholder="e.g. Nairobi Branches"
                                class="w-full glass rounded-xl px-4 py-3 text-white placeholder-white/25 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/30 transition-all text-sm"
                            >
                            <p class="mt-1.5 text-white/30 text-xs">All branches in a group can be managed together.</p>
                            @error('group_name')
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
                                <path d="M5 12h14"/><path d="M12 5v14"/>
                            </svg>
                            Create Branch
                        </button>
                        <a href="{{ route('manager.branches.index') }}"
                           class="glass px-6 py-3 rounded-xl font-semibold text-white/70 hover:text-white transition-all text-sm text-center">
                            Cancel
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

</x-manager-layout>
