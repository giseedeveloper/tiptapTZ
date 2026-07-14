<x-manager-layout>
    <x-slot name="header">Kitchen Tip Pool</x-slot>

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-sm">{{ session('error') }}</div>
    @endif

    <div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white tracking-tight">Digital tipping</h2>
            <p class="text-sm text-white/45 mt-1">Configure tip categories, suggestions, visibility and the kitchen pool.</p>
        </div>
        <a href="{{ route('manager.tips.reports') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
            Tip Reports
        </a>
    </div>

    {{-- Tip settings --}}
    <div class="glass-card rounded-2xl p-6 border border-white/10 mb-8">
        <h3 class="text-lg font-bold text-white mb-1">Tip settings</h3>
        <p class="text-xs text-white/45 mb-5">Control which tip categories customers see, default suggestions, and tip-value visibility.</p>

        <form action="{{ route('manager.tips.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @php
                    $cats = [
                        'waiter' => ['label' => 'Waiter tips', 'field' => 'category_waiter'],
                        'barista' => ['label' => 'Barista tips', 'field' => 'category_barista'],
                        'kitchen' => ['label' => 'Kitchen tips', 'field' => 'category_kitchen'],
                    ];
                @endphp
                @foreach($cats as $key => $cat)
                    <label class="flex items-center justify-between gap-3 glass p-4 rounded-xl border border-white/10 cursor-pointer">
                        <span class="text-sm font-semibold text-white">{{ $cat['label'] }}</span>
                        <input type="hidden" name="{{ $cat['field'] }}" value="0">
                        <input type="checkbox" name="{{ $cat['field'] }}" value="1" class="w-5 h-5 rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500"
                               @checked($tipSettings['categories'][$key])>
                    </label>
                @endforeach
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Suggestion mode</label>
                    <select name="suggestion_mode" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white [&>option]:text-black">
                        <option value="percent" @selected($tipSettings['suggestion_mode'] === 'percent')>Percentage of bill</option>
                        <option value="fixed" @selected($tipSettings['suggestion_mode'] === 'fixed')>Fixed amounts</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Percentages (%)</label>
                    <input type="text" name="percentages" value="{{ implode(', ', $tipSettings['percentages']) }}" placeholder="5, 10, 15"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Fixed amounts</label>
                    <input type="text" name="fixed_amounts" value="{{ implode(', ', $tipSettings['fixed_amounts']) }}" placeholder="500, 1000, 2000"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                </div>
            </div>

            <label class="flex items-center justify-between gap-4 glass p-4 rounded-xl border border-white/10 cursor-pointer">
                <div>
                    <p class="text-sm font-bold text-white">Show tip values on dashboard</p>
                    <p class="text-[11px] text-white/45">When OFF, tip amounts are hidden in reports (counts still visible). No code changes needed.</p>
                </div>
                <input type="hidden" name="value_visible" value="0">
                <input type="checkbox" name="value_visible" value="1" class="w-5 h-5 rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-500"
                       @checked($tipSettings['value_visible'])>
            </label>

            <button type="submit" class="px-6 py-3 rounded-xl bg-linear-to-r from-fin-primary to-fin-primary-dark text-white font-semibold">
                Save tip settings
            </button>
        </form>
    </div>

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white tracking-tight">Pooled digital tipping</h2>
        <p class="text-sm text-white/45 mt-1">Enable a shared tip pool for kitchen staff and choose how each tip is split.</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <div class="xl:col-span-2 glass-card rounded-2xl p-6 border border-white/10">
            <h3 class="text-lg font-bold text-white mb-1">Pool settings</h3>
            <p class="text-xs text-white/45 mb-5">Customers tip the pool once; TipTap splits the amount using your rule.</p>

            <form action="{{ route('manager.tips.pool.update') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Pool name</label>
                    <input type="text" name="name" value="{{ old('name', $pool->name) }}" required
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-fin-primary">
                </div>

                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Distribution rule</label>
                    <select name="distribution_method" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white focus:ring-2 focus:ring-fin-primary [&>option]:text-black">
                        @foreach($methods as $key => $label)
                            <option value="{{ $key }}" @selected(old('distribution_method', $pool->distribution_method) === $key)>{{ ucfirst($key) }} — {{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <label class="flex items-center justify-between gap-4 glass p-4 rounded-xl border border-white/10 cursor-pointer">
                    <div>
                        <p class="text-sm font-bold text-white">Enable kitchen tip pool</p>
                        <p class="text-[11px] text-white/45">When on, WhatsApp customers can tip this pool (needs ≥1 active member).</p>
                    </div>
                    <input type="hidden" name="is_enabled" value="0">
                    <input type="checkbox" name="is_enabled" value="1" class="w-5 h-5 rounded border-white/20 bg-white/5 text-amber-500 focus:ring-amber-500"
                           @checked(old('is_enabled', $pool->is_enabled))>
                </label>

                <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-linear-to-r from-fin-primary to-fin-primary-dark text-white font-semibold">
                    Save pool settings
                </button>
            </form>
        </div>

        <div class="glass-card rounded-2xl p-6 border border-white/10">
            <h3 class="text-lg font-bold text-white mb-4">Status</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <span class="text-white/50">Enabled</span>
                    <span class="font-semibold {{ $pool->is_enabled ? 'text-emerald-400' : 'text-white/40' }}">{{ $pool->is_enabled ? 'Yes' : 'No' }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-white/50">Rule</span>
                    <span class="font-semibold text-white capitalize">{{ $pool->distribution_method }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-white/50">Active members</span>
                    <span class="font-semibold text-white">{{ $pool->members->where('is_active', true)->count() }}</span>
                </div>
                <div class="flex justify-between gap-3">
                    <span class="text-white/50">Visible to customers</span>
                    <span class="font-semibold {{ $pool->isTippable() ? 'text-amber-400' : 'text-white/40' }}">
                        {{ $pool->isTippable() ? 'Yes' : 'Not yet' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <div class="glass-card rounded-2xl p-6 border border-white/10">
            <h3 class="text-lg font-bold text-white mb-1">Kitchen pool members</h3>
            <p class="text-xs text-white/45 mb-5">Add staff who should share kitchen tips. Weights apply only for the weighted rule.</p>

            <div class="space-y-3 mb-6">
                @forelse($pool->members->sortByDesc('is_active') as $member)
                    <div class="glass p-4 rounded-xl border border-white/10 space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $member->user?->name ?? 'Staff' }}</p>
                                <p class="text-[10px] font-mono text-cyan-500/80">{{ $member->user?->global_waiter_number ?? '—' }}</p>
                            </div>
                            <span class="text-[10px] font-bold uppercase px-2 py-1 rounded-full {{ $member->is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-white/10 text-white/40' }}">
                                {{ $member->is_active ? 'Active' : 'Paused' }}
                            </span>
                        </div>
                        <form action="{{ route('manager.tips.members.update', $member) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-1 block">Weight</label>
                                    <input type="number" name="weight" min="1" max="1000" value="{{ $member->weight }}"
                                           class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm">
                                </div>
                                <label class="flex items-end gap-2 pb-2 cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="rounded border-white/20 bg-white/5 text-amber-500" @checked($member->is_active)>
                                    <span class="text-xs text-white/70">Active in pool</span>
                                </label>
                            </div>
                            <button type="submit" class="w-full px-3 py-2 rounded-lg bg-violet-600 hover:bg-violet-500 text-white text-xs font-semibold">Update</button>
                        </form>
                        <form action="{{ route('manager.tips.members.destroy', $member) }}" method="POST" onsubmit="return confirm('Remove from kitchen tip pool?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-3 py-2 rounded-lg glass text-rose-400 hover:bg-rose-500/10 text-xs font-semibold">Remove</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-white/40 text-center py-8">No kitchen staff in the pool yet.</p>
                @endforelse
            </div>

            <form action="{{ route('manager.tips.members.store') }}" method="POST" class="space-y-3 border-t border-white/10 pt-5">
                @csrf
                <p class="text-[10px] font-bold uppercase tracking-wider text-white/40">Add staff</p>
                <div class="flex flex-wrap gap-3">
                    <select name="user_id" required class="flex-1 min-w-[180px] px-3 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm [&>option]:text-black">
                        <option value="">Select staff…</option>
                        @foreach($availableStaff as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->global_waiter_number }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="weight" min="1" max="1000" value="1" placeholder="Weight"
                           class="w-24 px-3 py-2.5 bg-white/5 border border-white/10 rounded-xl text-white text-sm">
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-black text-sm font-bold" @disabled($availableStaff->isEmpty())>
                        Add
                    </button>
                </div>
                @if($availableStaff->isEmpty())
                    <p class="text-[11px] text-white/35">Link waiters/baristas under Waiters & Staff first.</p>
                @endif
            </form>
        </div>

        <div class="glass-card rounded-2xl p-6 border border-white/10">
            <h3 class="text-lg font-bold text-white mb-1">Recent pool tips</h3>
            <p class="text-xs text-white/45 mb-5">Each contribution shows how it was split to members.</p>

            <div class="space-y-3 max-h-[560px] overflow-y-auto pr-1">
                @forelse($recentContributions as $contribution)
                    <div class="glass p-4 rounded-xl border border-white/10">
                        <div class="flex justify-between gap-3 mb-2">
                            <div>
                                <p class="text-sm font-bold text-white">{{ $currencySymbol }} {{ number_format((float) $contribution->amount, 2) }}</p>
                                <p class="text-[10px] text-white/40">{{ $contribution->created_at?->diffForHumans() }} · {{ $contribution->distribution_method }}</p>
                            </div>
                        </div>
                        <div class="space-y-1.5">
                            @foreach($contribution->allocations as $alloc)
                                <div class="flex justify-between text-xs">
                                    <span class="text-white/70">{{ $alloc->user?->name ?? 'Staff' }}
                                        @if($contribution->distribution_method === 'weighted')
                                            <span class="text-white/35">(w{{ $alloc->weight_used }})</span>
                                        @endif
                                    </span>
                                    <span class="text-amber-400 font-semibold">{{ $currencySymbol }} {{ number_format((float) $alloc->amount, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-white/40 text-center py-10">No pool tips yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-manager-layout>
