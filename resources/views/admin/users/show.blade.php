<x-admin-layout>
    <x-slot name="header">{{ $user->name }}</x-slot>

    @include('admin.partials.page-styles')
    @include('admin.partials.flash')

    @php
        $role = $user->getRoleNames()->first();
        $roleBadge = match($role) {
            'super_admin' => 'bg-gradient-to-r from-fin-primary to-fin-primary-dark text-white border-0',
            'manager' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            'waiter' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
            default => 'bg-white/10 text-white/60 border-white/20',
        };
    @endphp

    <div class="admin-page-hero admin-page-hero--indigo rounded-3xl p-6 md:p-8 mb-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-violet-500/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center gap-6">
            <div class="flex items-center gap-5 min-w-0 flex-1">
                <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-violet-600 via-purple-600 to-cyan-500 flex items-center justify-center text-3xl font-black text-white shadow-xl shadow-violet-500/30 shrink-0 ring-2 ring-white/10">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] font-black text-cyan-400 uppercase tracking-[0.25em] mb-1">User profile</p>
                    <h1 class="text-2xl md:text-3xl font-black text-white truncate">{{ $user->name }}</h1>
                    <p class="text-sm text-white/50 mt-1">{{ $user->email }}</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $roleBadge }}">{{ str_replace('_', ' ', $role ?? 'user') }}</span>
                        @if($user->email_verified_at)
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border bg-emerald-500/15 text-emerald-400 border-emerald-500/30">Verified</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border bg-rose-500/15 text-rose-400 border-rose-500/30">Unverified</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white/70 text-xs font-semibold hover:bg-white/10 transition-all">← All users</a>
                <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2.5 rounded-xl bg-violet-600/30 border border-violet-500/40 text-violet-200 text-xs font-bold hover:bg-violet-600/50 transition-all flex items-center gap-1.5">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit
                </a>
                @if($user->hasRole('manager') && Auth::id() !== $user->id)
                    <form method="POST" action="{{ route('admin.impersonate.start', $user) }}" class="inline">@csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-cyan-600/30 border border-cyan-500/40 text-cyan-200 text-xs font-bold hover:bg-cyan-600/50 transition-all">Login as manager</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @include('admin.partials.stat-chip', ['label' => 'Member since', 'value' => $user->created_at->format('M Y'), 'tone' => 'violet'])
        @include('admin.partials.stat-chip', ['label' => 'User ID', 'value' => '#'.str_pad($user->id, 4, '0', STR_PAD_LEFT), 'tone' => 'cyan'])
        @include('admin.partials.stat-chip', ['label' => 'Restaurant', 'value' => $user->restaurant?->name ?? 'System', 'tone' => 'emerald'])
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="glass-card admin-data-panel rounded-3xl p-6 border border-violet-500/15">
            <h3 class="text-xs font-black text-violet-300 uppercase tracking-widest mb-5">Account</h3>
            <dl class="space-y-5">
                <div>
                    <dt class="text-[10px] font-black text-white/40 uppercase tracking-widest">Full name</dt>
                    <dd class="text-white font-bold mt-1">{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-black text-white/40 uppercase tracking-widest">Email</dt>
                    <dd class="text-white font-bold mt-1">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-[10px] font-black text-white/40 uppercase tracking-widest">Role</dt>
                    <dd class="mt-2"><span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border {{ $roleBadge }}">{{ str_replace('_', ' ', $role ?? '—') }}</span></dd>
                </div>
            </dl>
        </div>

        <div class="glass-card admin-data-panel rounded-3xl p-6 border border-cyan-500/15">
            <h3 class="text-xs font-black text-cyan-300 uppercase tracking-widest mb-5">Venue & access</h3>
            <dl class="space-y-5">
                <div>
                    <dt class="text-[10px] font-black text-white/40 uppercase tracking-widest">Restaurant</dt>
                    <dd class="mt-1">
                        @if($user->restaurant)
                            <a href="{{ route('admin.restaurants.show', $user->restaurant) }}" class="text-white font-bold hover:text-violet-400 transition-all inline-flex items-center gap-2">
                                {{ $user->restaurant->name }}
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        @else
                            <p class="text-white font-bold">Platform (no venue)</p>
                        @endif
                    </dd>
                </div>
                @if($user->hasRole('waiter') && $user->global_waiter_number)
                <div>
                    <dt class="text-[10px] font-black text-white/40 uppercase tracking-widest">Global waiter code</dt>
                    <dd class="mt-1"><code class="text-cyan-400 font-mono font-bold bg-white/5 px-2 py-1 rounded-lg">{{ $user->global_waiter_number }}</code></dd>
                </div>
                @endif
                <div>
                    <dt class="text-[10px] font-black text-white/40 uppercase tracking-widest">Joined</dt>
                    <dd class="text-white font-bold mt-1">{{ $user->created_at->format('F d, Y') }}</dd>
                    <dd class="text-[10px] text-white/40 mt-0.5">{{ $user->created_at->diffForHumans() }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-admin-layout>
