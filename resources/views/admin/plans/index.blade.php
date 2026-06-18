<x-admin-layout>
    <x-slot name="header">Subscription Plans</x-slot>
    @include('admin.partials.flash')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Plans &amp; Pricing</h2>
            <p class="text-sm text-white/45 mt-1">Manage the subscription packages restaurants choose after approval.</p>
        </div>
        <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl font-bold text-sm shadow-lg shadow-violet-500/25 hover:scale-[1.02] transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            New Plan
        </a>
    </div>

    @if ($packages->isEmpty())
        <div class="glass-card rounded-2xl p-12 border border-white/10 text-center">
            <div class="text-5xl mb-4">🏷️</div>
            <p class="text-white font-bold text-lg">No plans yet</p>
            <p class="text-white/45 text-sm mt-1 mb-6">Create your first subscription plan to get started.</p>
            <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl font-bold text-sm">Create plan</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($packages as $package)
                <div class="relative glass-card rounded-2xl p-6 border {{ $package->is_featured ? 'border-fin-primary/50' : 'border-white/10' }} flex flex-col">
                    @if ($package->is_featured)
                        <span class="absolute -top-3 left-6 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white text-[9px] font-black uppercase tracking-widest px-3 py-1 rounded-full shadow-lg">Most popular</span>
                    @endif

                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div>
                            <h3 class="text-lg font-black text-white">{{ $package->name }}</h3>
                            @if ($package->tagline)
                                <p class="text-[11px] text-white/45 mt-0.5">{{ $package->tagline }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-[9px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full {{ $package->is_active ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-white/5 text-white/40 border border-white/10' }}">
                            {{ $package->is_active ? 'Active' : 'Hidden' }}
                        </span>
                    </div>

                    <div class="mb-4">
                        <span class="text-3xl font-black text-white tabular-nums">{{ $package->priceLabel() }}</span>
                        <span class="text-sm text-white/40 ml-1">{{ $package->periodLabel() }}</span>
                    </div>

                    <ul class="space-y-2 mb-5 flex-1">
                        <li class="flex items-center gap-2 text-xs text-white/60">
                            <span class="w-4 h-4 rounded-full bg-fin-primary/15 flex items-center justify-center shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-fin-primary"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            {{ $package->tableLimitLabel() }}
                        </li>
                        @foreach (($package->features ?? []) as $feature)
                            <li class="flex items-center gap-2 text-xs text-white/60">
                                <span class="w-4 h-4 rounded-full bg-fin-primary/15 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="text-fin-primary"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                                {{ $feature }}
                            </li>
                        @endforeach
                    </ul>

                    <div class="flex items-center justify-between gap-3 pt-4 border-t border-white/10">
                        <span class="text-[10px] font-bold text-white/35 uppercase tracking-wider">{{ $package->restaurants_count }} venue(s)</span>
                        <a href="{{ route('admin.plans.edit', $package) }}" class="px-4 py-2 bg-white/5 border border-white/10 text-white rounded-xl font-bold text-xs hover:bg-white/10 transition">Edit</a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-admin-layout>
