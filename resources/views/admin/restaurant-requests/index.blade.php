<x-admin-layout>
    <x-slot name="header">Restaurant Requests</x-slot>
    @include('admin.partials.flash')

    <div class="mb-6">
        <h2 class="text-2xl font-black text-white tracking-tight">Restaurant join requests</h2>
        <p class="text-sm text-white/45 mt-1">Review venues that registered and approve or reject them.</p>
    </div>

    {{-- Status tabs --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @php
            $tabs = [
                'pending' => ['Pending', $counts['pending'], 'bg-amber-500/20 text-amber-300'],
                'approved' => ['Approved', $counts['approved'], 'bg-emerald-500/20 text-emerald-300'],
                'rejected' => ['Rejected', $counts['rejected'], 'bg-rose-500/20 text-rose-300'],
                'all' => ['All', null, 'bg-violet-500/20 text-violet-300'],
            ];
        @endphp
        @foreach ($tabs as $key => [$label, $count, $countClass])
            <a href="{{ route('admin.restaurant-requests.index', ['status' => $key]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition border {{ $status === $key ? 'bg-white/10 border-white/20 text-white' : 'bg-white/[0.03] border-white/10 text-white/50 hover:text-white' }}">
                {{ $label }}
                @if (!is_null($count) && $count > 0)
                    <span class="min-w-[18px] h-[18px] px-1 flex items-center justify-center rounded-full text-[10px] tabular-nums {{ $countClass }}">{{ $count }}</span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-6 flex gap-2">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, location, phone..."
               class="flex-1 max-w-md px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-fin-primary/50">
        <button type="submit" class="px-5 py-2.5 bg-white/5 border border-white/10 text-white rounded-xl font-bold text-sm hover:bg-white/10 transition">Search</button>
    </form>

    @if ($restaurants->isEmpty())
        <div class="glass-card rounded-2xl p-12 border border-white/10 text-center">
            <div class="text-5xl mb-4">📭</div>
            <p class="text-white font-bold text-lg">No {{ $status === 'all' ? '' : $status }} requests</p>
            <p class="text-white/45 text-sm mt-1">New restaurant registrations will show up here for review.</p>
        </div>
    @else
        <form method="POST" action="{{ route('admin.restaurant-requests.bulk-approve') }}" id="bulk-approve-form">
            @csrf
            @if ($status === 'pending')
                <div class="flex items-center justify-between gap-3 mb-4">
                    <label class="flex items-center gap-2 text-xs font-bold text-white/55 cursor-pointer">
                        <input type="checkbox" id="bulk-select-all" class="rounded border-white/20 text-violet-600 focus:ring-violet-500">
                        Select all
                    </label>
                    <button type="submit" onclick="return confirm('Approve all selected restaurants?')" class="px-5 py-2.5 bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 rounded-xl font-bold text-sm hover:bg-emerald-500/25 transition">
                        Approve selected
                    </button>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach ($restaurants as $restaurant)
                    <div class="glass-card rounded-2xl p-5 border border-white/10 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3 min-w-0">
                                @if ($status === 'pending')
                                    <input type="checkbox" name="ids[]" value="{{ $restaurant->id }}" class="bulk-checkbox rounded border-white/20 text-violet-600 focus:ring-violet-500 shrink-0">
                                @endif
                                <div class="min-w-0">
                                    <p class="text-base font-black text-white truncate">{{ $restaurant->name }}</p>
                                    <p class="text-[11px] text-white/45 truncate">{{ $restaurant->location ?: '—' }}</p>
                                </div>
                            </div>
                            @php
                                $badge = match ($restaurant->approval_status) {
                                    'pending' => ['Pending', 'bg-amber-500/15 text-amber-300 border-amber-500/30'],
                                    'approved' => ['Approved', 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'],
                                    'rejected' => ['Rejected', 'bg-rose-500/15 text-rose-300 border-rose-500/30'],
                                    'active' => ['Active', 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'],
                                    default => [$restaurant->approval_status, 'bg-white/10 text-white/60 border-white/20'],
                                };
                            @endphp
                            <span class="shrink-0 text-[9px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full border {{ $badge[1] }}">{{ $badge[0] }}</span>
                        </div>

                        <div class="space-y-1.5 text-xs text-white/55 mb-4 flex-1">
                            <p class="flex items-center gap-2"><span class="text-white/30">Phone</span> {{ $restaurant->phone ?: '—' }}</p>
                            <p class="flex items-center gap-2"><span class="text-white/30">Managers</span> {{ $restaurant->managers_count }}</p>
                            <p class="flex items-center gap-2"><span class="text-white/30">Registered</span> {{ $restaurant->created_at?->diffForHumans() }}</p>
                            @if ($restaurant->subscriptionPackage)
                                <p class="flex items-center gap-2"><span class="text-white/30">Plan</span> {{ $restaurant->subscriptionPackage->name }}</p>
                            @endif
                        </div>

                        <a href="{{ route('admin.restaurant-requests.show', $restaurant) }}" class="block text-center px-4 py-2.5 bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl font-bold text-xs hover:scale-[1.02] transition">
                            View details
                        </a>
                    </div>
                @endforeach
            </div>
        </form>

        <div class="mt-6">{{ $restaurants->links() }}</div>
    @endif

    <script>
    (function () {
        const all = document.getElementById('bulk-select-all');
        if (!all) return;
        all.addEventListener('change', function () {
            document.querySelectorAll('.bulk-checkbox').forEach(cb => { cb.checked = all.checked; });
        });
    })();
    </script>
</x-admin-layout>
