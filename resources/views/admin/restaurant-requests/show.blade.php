<x-admin-layout>
    <x-slot name="header">Request · {{ $restaurant->name }}</x-slot>
    @include('admin.partials.flash')

    <div class="mb-6">
        <a href="{{ route('admin.restaurant-requests.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-fin-primary hover:text-fin-lavender transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            Back to requests
        </a>
    </div>

    @php
        $badge = match ($restaurant->approval_status) {
            'pending' => ['Pending review', 'bg-amber-500/15 text-amber-300 border-amber-500/30'],
            'approved' => ['Approved', 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'],
            'rejected' => ['Rejected', 'bg-rose-500/15 text-rose-300 border-rose-500/30'],
            'active' => ['Active', 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'],
            default => [$restaurant->approval_status, 'bg-white/10 text-white/60 border-white/20'],
        };
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Detail --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="glass-card rounded-2xl p-6 border border-white/10">
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-black text-white tracking-tight">{{ $restaurant->name }}</h2>
                        <p class="text-sm text-white/45 mt-1">Registered {{ $restaurant->created_at?->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="shrink-0 text-[10px] font-black uppercase tracking-wider px-3 py-1.5 rounded-full border {{ $badge[1] }}">{{ $badge[0] }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                        <p class="text-[9px] font-black text-white/40 uppercase tracking-wider mb-1">Location</p>
                        <p class="text-sm font-semibold text-white">{{ $restaurant->location ?: '—' }}</p>
                    </div>
                    <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                        <p class="text-[9px] font-black text-white/40 uppercase tracking-wider mb-1">Phone</p>
                        <p class="text-sm font-semibold text-white">{{ $restaurant->phone ?: '—' }}</p>
                    </div>
                    <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                        <p class="text-[9px] font-black text-white/40 uppercase tracking-wider mb-1">Tag prefix</p>
                        <p class="text-sm font-semibold text-white">{{ $restaurant->tag_prefix ?: '—' }}</p>
                    </div>
                    <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                        <p class="text-[9px] font-black text-white/40 uppercase tracking-wider mb-1">Selected plan</p>
                        <p class="text-sm font-semibold text-white">{{ $restaurant->subscriptionPackage?->name ?? 'Not chosen yet' }}</p>
                    </div>
                </div>
            </div>

            {{-- Manager account --}}
            <div class="glass-card rounded-2xl p-6 border border-white/10">
                <h3 class="text-sm font-black text-white uppercase tracking-widest mb-4">Manager account</h3>
                @if ($manager)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                            <p class="text-[9px] font-black text-white/40 uppercase tracking-wider mb-1">Full name</p>
                            <p class="text-sm font-semibold text-white">{{ $manager->name }}</p>
                        </div>
                        <div class="p-4 bg-white/5 rounded-xl border border-white/10">
                            <p class="text-[9px] font-black text-white/40 uppercase tracking-wider mb-1">Email</p>
                            <p class="text-sm font-semibold text-white break-all">{{ $manager->email }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-white/40">No manager account linked.</p>
                @endif
            </div>

            @if ($restaurant->isRejected() && $restaurant->rejection_reason)
                <div class="rounded-2xl p-6 border border-rose-500/30 bg-rose-500/10">
                    <h3 class="text-sm font-black text-rose-200 uppercase tracking-widest mb-2">Rejection reason</h3>
                    <p class="text-sm text-white/80">{{ $restaurant->rejection_reason }}</p>
                    @if ($restaurant->rejected_at)
                        <p class="text-[11px] text-white/40 mt-2">Rejected {{ $restaurant->rejected_at->diffForHumans() }}</p>
                    @endif
                </div>
            @endif

            @if ($restaurant->approved_at)
                <div class="rounded-2xl p-6 border border-emerald-500/30 bg-emerald-500/10">
                    <h3 class="text-sm font-black text-emerald-200 uppercase tracking-widest mb-1">Approved</h3>
                    <p class="text-sm text-white/70">By {{ $restaurant->approvedBy?->name ?? 'admin' }} · {{ $restaurant->approved_at->diffForHumans() }}</p>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="space-y-6">
            <div class="glass-card rounded-2xl p-6 border border-white/10 sticky top-6">
                <h3 class="text-sm font-black text-white uppercase tracking-widest mb-4">Decision</h3>

                @if ($restaurant->isPending() || $restaurant->isRejected())
                    <form method="POST" action="{{ route('admin.restaurant-requests.approve', $restaurant) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="w-full px-5 py-3 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-emerald-500/25 hover:scale-[1.02] transition flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            Approve restaurant
                        </button>
                    </form>
                @endif

                @if (! $restaurant->isRejected())
                    <button type="button" onclick="document.getElementById('reject-box').classList.toggle('hidden')" class="w-full px-5 py-3 bg-rose-500/15 border border-rose-500/30 text-rose-300 rounded-xl font-bold text-sm hover:bg-rose-500/25 transition">
                        Reject restaurant
                    </button>

                    <form method="POST" action="{{ route('admin.restaurant-requests.reject', $restaurant) }}" id="reject-box" class="hidden mt-4 space-y-3">
                        @csrf
                        <label class="text-[10px] font-bold uppercase text-white/40">Reason (shown to manager)</label>
                        <textarea name="rejection_reason" rows="3" required placeholder="e.g. We could not verify your restaurant details. Please update your phone number and re-apply."
                                  class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white focus:ring-2 focus:ring-rose-500/50">{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')<p class="text-rose-400 text-xs">{{ $message }}</p>@enderror
                        <button type="submit" class="w-full px-5 py-3 bg-rose-500 text-white rounded-xl font-bold text-sm hover:bg-rose-600 transition">Confirm rejection</button>
                    </form>
                @endif

                <a href="{{ route('admin.restaurants.show', $restaurant) }}" class="block text-center mt-4 px-5 py-2.5 bg-white/5 border border-white/10 text-white/70 rounded-xl font-bold text-xs hover:bg-white/10 transition">
                    Open full venue profile
                </a>
            </div>
        </div>
    </div>
</x-admin-layout>
