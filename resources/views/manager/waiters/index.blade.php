<x-manager-layout>
    <x-slot name="header">Waiters & Staff</x-slot>

    @if (session('success') && !session('order_portal_password_generated'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 text-sm">{{ session('error') }}</div>
    @endif
    @if (session('order_portal_password_generated'))
        <div class="mb-6 p-4 rounded-xl bg-cyan-500/10 border border-cyan-500/20 text-cyan-300 text-sm">
            {{ session('success') }}
            <p class="mt-2 font-semibold">Order Portal password (show waiter once):</p>
            <p class="mt-1 font-mono text-lg tracking-wider bg-black/20 px-3 py-2 rounded-lg inline-block">{{ session('order_portal_password_generated') }}</p>
            <p class="mt-2 text-white/60">Waiter: <strong>{{ session('order_portal_waiter_name') }}</strong> · Number: <code>{{ session('order_portal_waiter_number') }}</code></p>
            <p class="mt-1 text-white/50 text-xs">Login: <a href="{{ $orderPortalLoginUrl ?? route('order-portal.login') }}" class="text-cyan-600 underline" target="_blank">{{ $orderPortalLoginUrl ?? url('/order-portal/login') }}</a></p>
        </div>
    @endif

    <!-- Link Waiter Card -->
    <div class="glass-card rounded-2xl p-6 mb-8 border border-white/10">
        <h3 class="text-lg font-bold text-white mb-1">Link Waiter</h3>
        <p class="text-sm text-white/50 mb-2">Waiters register on the web, then give you their unique number (TIPTAP-W-xxxxx). Search here and link them to your restaurant.</p>
        <p class="text-xs text-white/40 mb-4">Choose <strong class="text-white/60">Long-term</strong> (permanent) or <strong class="text-white/60">Show-time</strong> (fixed period – set an end date).</p>
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="searchCode" class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Waiter unique number</label>
                <input type="text" id="searchCode" placeholder="TIPTAP-W-00001"
                       class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl font-mono text-white placeholder-white/30 focus:ring-2 focus:ring-fin-primary focus:border-transparent">
            </div>
            <button type="button" onclick="searchWaiter()" class="px-6 py-3 bg-linear-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl font-semibold hover:shadow-lg hover:shadow-fin-primary/25 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                Search
            </button>
        </div>
        <div id="searchResult" class="mt-4 hidden"></div>
        <div id="searchError" class="mt-4 hidden p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 text-sm"></div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="glass-card rounded-xl p-4 mb-6 border border-white/10" x-data="{ filterOpen: false }">
        <div class="flex flex-wrap gap-3 items-center justify-between">
            <div class="flex-1 min-w-[250px] max-w-md">
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input type="text" id="waiterSearch" placeholder="Search by name or code..." 
                           class="w-full pl-10 pr-4 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white placeholder-white/30 focus:ring-2 focus:ring-fin-primary focus:border-transparent transition-all"
                           oninput="filterWaiters()">
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="filterOpen = !filterOpen" class="px-4 py-2 glass rounded-lg border border-white/10 text-white/60 hover:text-white hover:bg-white/10 transition-all text-sm font-medium flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Filter
                </button>
                <select id="sortBy" onchange="filterWaiters()" class="px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-sm text-white focus:ring-2 focus:ring-fin-primary focus:border-transparent">
                    <option value="name">Sort: Name</option>
                    <option value="orders">Sort: Orders</option>
                    <option value="recent">Sort: Recently Added</option>
                </select>
                <a href="{{ route('manager.waiters.history') }}" class="inline-flex items-center gap-2 px-4 py-2 glass rounded-lg border border-white/10 text-white/60 hover:text-white hover:bg-white/10 transition-all text-sm font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0 11 18 0z"/></svg>
                    History
                </a>
            </div>
        </div>
        
        <!-- Filter Options -->
        <div x-show="filterOpen" x-transition class="mt-4 pt-4 border-t border-white/10 flex flex-wrap gap-3">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="radio" name="statusFilter" value="all" checked onchange="filterWaiters()" class="text-violet-600 focus:ring-fin-primary">
                <span class="text-sm text-white/60">All Waiters</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="radio" name="statusFilter" value="online" onchange="filterWaiters()" class="text-violet-600 focus:ring-fin-primary">
                <span class="text-sm text-white/60">Online Only</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="radio" name="statusFilter" value="offline" onchange="filterWaiters()" class="text-sm text-violet-600 focus:ring-fin-primary">
                <span class="text-sm text-white/60">Offline</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="radio" name="statusFilter" value="permanent" onchange="filterWaiters()" class="text-violet-600 focus:ring-fin-primary">
                <span class="text-sm text-white/60">Permanent</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="radio" name="statusFilter" value="temporary" onchange="filterWaiters()" class="text-violet-600 focus:ring-fin-primary">
                <span class="text-sm text-white/60">Temporary</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="radio" name="statusFilter" value="tips_on" onchange="filterWaiters()" class="text-violet-600 focus:ring-fin-primary">
                <span class="text-sm text-white/60">Digital tips ON</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="radio" name="statusFilter" value="tips_off" onchange="filterWaiters()" class="text-violet-600 focus:ring-fin-primary">
                <span class="text-sm text-white/60">Digital tips OFF</span>
            </label>
        </div>
    </div>

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-white">Active Staff <span id="waiterCount" class="text-white/50 font-normal text-base"></span></h2>
    </div>

    <div id="waitersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($waiters as $waiter)
            <div class="waiter-card glass-card p-4 rounded-xl card-hover group transition-all" 
                 data-name="{{ strtolower($waiter->name) }}" 
                 data-code="{{ strtolower($waiter->global_waiter_number ?? '') }}" 
                 data-status="{{ $waiter->is_online ? 'online' : 'offline' }}" 
                 data-employment="{{ $waiter->employment_type }}" 
                 data-orders="{{ $waiter->orders_count }}"
                 data-tips="{{ $waiter->digital_tips_enabled ? 'on' : 'off' }}">
                <!-- Avatar & Name -->
                <div class="flex items-center gap-3 mb-3">
                    @php 
                        $waiterPhotoUrl = $waiter->profilePhotoUrl(); 
                        $initials = substr($waiter->name, 0, 1);
                        $colorHash = crc32($waiter->global_waiter_number ?? $waiter->name) % 6;
                        $colors = [
                            'from-fin-primary/15 to-purple-500/20 text-fin-primary',
                            'from-blue-500/15 to-cyan-500/10 text-blue-600',
                            'from-emerald-500/15 to-teal-500/10 text-emerald-600',
                            'from-amber-500/15 to-orange-500/10 text-amber-600',
                            'from-rose-500/15 to-pink-500/10 text-rose-600',
                            'from-indigo-500/20 to-blue-500/10 text-indigo-400',
                        ];
                        $avatarColor = $colors[$colorHash];
                    @endphp
                    @if($waiterPhotoUrl)
                        <img src="{{ $waiterPhotoUrl }}" alt="{{ $waiter->name }}" loading="lazy" class="w-12 h-12 rounded-lg object-cover border border-white/10 group-hover:scale-105 transition-transform shrink-0" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="waiter-fallback-avatar w-12 h-12 bg-linear-to-br {{ $avatarColor }} rounded-lg flex items-center justify-center font-bold text-lg border border-white/10 shrink-0 hidden">
                            {{ $initials }}
                        </div>
                    @else
                        <div class="w-12 h-12 bg-linear-to-br {{ $avatarColor }} rounded-lg flex items-center justify-center font-bold text-lg border border-white/10 group-hover:scale-105 transition-transform shrink-0">
                            {{ $initials }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <h4 class="text-sm font-bold text-white truncate">{{ $waiter->name }}</h4>
                        <p class="text-[10px] font-mono text-cyan-600/80">{{ $waiter->global_waiter_number ?? '—' }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            @if($waiter->is_online)
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-semibold bg-emerald-500/20 text-emerald-600 border border-emerald-500/30">
                                    <span class="w-1 h-1 bg-emerald-400 rounded-full animate-pulse"></span>
                                    Online
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-medium bg-white/5 text-white/40 border border-white/10">
                                    <span class="w-1 h-1 bg-surface-900/40 rounded-full"></span>
                                    Offline
                                </span>
                            @endif
                            @if($waiter->employment_type === 'temporary')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-500/20 text-amber-600 border border-amber-500/30">Temp</span>
                            @endif
                            @if($waiter->digital_tips_enabled)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/25">Tips ON</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mb-3 px-3 py-2.5 rounded-xl border {{ $waiter->digital_tips_enabled ? 'bg-amber-500/10 border-amber-500/25' : 'bg-white/5 border-white/10' }}">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] font-bold text-white">Digital tipping</p>
                            <p class="text-[10px] text-white/45 leading-snug">Allow customers to tip this barista/staff on WhatsApp</p>
                        </div>
                        <form action="{{ route('manager.waiters.digital-tips', $waiter) }}" method="POST" class="shrink-0">
                            @csrf
                            <input type="hidden" name="digital_tips_enabled" value="{{ $waiter->digital_tips_enabled ? 0 : 1 }}">
                            <button type="submit"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $waiter->digital_tips_enabled ? 'bg-amber-500' : 'bg-white/15' }}"
                                    title="{{ $waiter->digital_tips_enabled ? 'Disable digital tips' : 'Enable digital tips' }}"
                                    aria-pressed="{{ $waiter->digital_tips_enabled ? 'true' : 'false' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $waiter->digital_tips_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </form>
                    </div>
                </div>

                @if($waiter->waiter_code)
                <div class="mb-4 bg-white/5 rounded-xl overflow-hidden border border-white/10">
                    <div class="p-3 bg-white/5 border-b border-white/5 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-white/40 uppercase tracking-wider">Service Tag</span>
                        <div class="flex items-center gap-2">
                            <code class="text-sm font-mono font-bold text-cyan-600">{{ $waiter->waiter_code }}</code>
                            <button onclick="copyToClipboard('{{ $waiter->waiter_code }}', this)" class="text-white/40 hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-4 flex items-center gap-4">
                        <div class="bg-surface-900 p-2 rounded-lg shrink-0">
                            <img src="{{ whatsapp_branded_qr_url($waiter->waiter_qr_url, 150) }}" alt="QR" class="w-16 h-16">
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] text-white/40 mb-2 truncate">Scan to order with {{ explode(' ', $waiter->name)[0] }}</p>
                            <div class="flex gap-2">
                                <a href="{{ whatsapp_branded_qr_url($waiter->waiter_qr_url, 500) }}" download="waiter-{{ $waiter->waiter_code }}-qr.png" target="_blank" class="flex-1 px-3 py-2 bg-violet-600 hover:bg-violet-500 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg text-center transition-colors">Download</a>
                                <button onclick="copyToClipboard('{{ $waiter->waiter_qr_url }}', this)" class="px-3 py-2 glass hover:bg-white/10 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg transition-colors">Copy Link</button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Mini Stats -->
                <div class="grid grid-cols-3 gap-2 mb-3">
                    <div class="bg-white/5 p-2 rounded-lg border border-white/10">
                        <p class="text-[9px] font-bold text-white/40 uppercase tracking-wider mb-0.5">Orders</p>
                        <p class="text-lg font-bold text-white">{{ $waiter->orders_count }}</p>
                        <p class="text-[8px] text-white/30">All time</p>
                    </div>
                    <div class="bg-white/5 p-2 rounded-lg border border-white/10">
                        <p class="text-[9px] font-bold text-white/40 uppercase tracking-wider mb-0.5">Tips</p>
                        <p class="text-lg font-bold text-amber-600">{{ number_format($waiter->tips_sum_amount ?? 0) }}</p>
                        <p class="text-[8px] text-white/30">TSh</p>
                    </div>
                    <div class="bg-white/5 p-2 rounded-lg border border-white/10">
                        <p class="text-[9px] font-bold text-white/40 uppercase tracking-wider mb-0.5">Rating</p>
                        <p class="text-lg font-bold text-white flex items-center gap-0.5">
                            {{ number_format($waiter->feedback_avg_rating ?? 0, 1) }}
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="text-amber-600">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        </p>
                        <p class="text-[8px] text-white/30">{{ $waiter->feedback_count ?? 0 }} reviews</p>
                    </div>
                </div>

                @if(($waiter->feedback_avg_rating ?? 0) >= 4.5 && ($waiter->feedback_count ?? 0) >= 10)
                    <div class="mb-3 px-2 py-1 bg-linear-to-r from-amber-500/15 to-orange-500/10 border border-amber-500/30 rounded-lg flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="text-amber-600">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Top Performer 🏆</span>
                    </div>
                @endif

                <!-- Quick Actions -->
                <div class="flex gap-1.5">
                    @php
                        $waiterForModal = $waiter->only(['id','name','email','waiter_code','global_waiter_number','orders_count','created_at','employment_type','linked_until']);
                        $waiterForModal['profile_photo_url'] = $waiter->profilePhotoUrl();
                        $waiterForModal['tips_sum'] = $waiter->tips_sum_amount ?? 0;
                        $waiterForModal['rating'] = $waiter->feedback_avg_rating ?? 0;
                        $waiterForModal['rating_count'] = $waiter->feedback_count ?? 0;
                        $hasOrderPortal = in_array($waiter->id, $waiterIdsWithOrderPortal ?? []);
                    @endphp
                    <button onclick="openViewWaiterModal({{ json_encode($waiterForModal) }})" class="flex-1 px-3 py-2 glass rounded-lg font-medium text-white/60 hover:text-white hover:bg-violet-600 transition-all text-xs" title="View Profile">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                    <form action="{{ route('manager.waiters.generate-order-portal-password', $waiter) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 rounded-lg font-medium text-xs transition-all {{ $hasOrderPortal ? 'glass text-cyan-600 hover:bg-cyan-500/20' : 'bg-cyan-600 hover:bg-cyan-500 text-white' }}" title="{{ $hasOrderPortal ? 'Regenerate' : 'Generate Portal' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </button>
                    </form>
                    <form action="{{ route('manager.waiters.unlink', $waiter) }}" method="POST" onsubmit="return confirm('Unlink waiter huyu? History itabaki.');" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full px-3 py-2 glass text-rose-600 rounded-lg hover:bg-rose-500/20 transition-all text-xs" title="Unlink">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mx-auto">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full glass-card py-16 text-center rounded-2xl">
                <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-white/5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white/20">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No linked waiters</h3>
                <p class="text-white/40">Use "Link Waiter" above with the waiter's unique number.</p>
            </div>
        @endforelse
    </div>

    <!-- View Waiter Modal -->
    <div id="viewWaiterModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] hidden flex items-center justify-center p-6">
        <div class="bg-surface-900 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-white/10">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-white tracking-tight">Waiter Profile</h3>
                        <p class="text-sm font-medium text-white/40">Staff details and performance</p>
                    </div>
                    <button onclick="closeViewWaiterModal()" class="p-2 hover:bg-white/10 rounded-xl transition-all text-white/40 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>

                <div class="flex items-center gap-5 mb-6">
                    <div class="w-20 h-20 rounded-2xl border border-violet-500/20 overflow-hidden shrink-0" id="viewWaiterPhotoWrap">
                        <img id="viewWaiterPhoto" src="" alt="" class="w-full h-full object-cover hidden">
                        <div id="viewWaiterInitial" class="w-full h-full bg-linear-to-br from-fin-primary/15 to-cyan-500/10 flex items-center justify-center font-bold text-3xl text-fin-primary">—</div>
                    </div>
                    <div>
                        <h4 class="text-2xl font-bold text-white" id="viewWaiterName">—</h4>
                        <p class="text-sm text-white/40" id="viewWaiterEmail">—</p>
                        <p class="text-xs font-mono text-cyan-600 mt-1" id="viewWaiterGlobalCode">—</p>
                        <div class="mt-2 inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-xs font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse mr-2"></span>
                            Active Staff
                        </div>
                        <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider mt-2 mb-0.5">Link type</p>
                        <p class="text-xs text-white/60" id="viewWaiterEmployment">—</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                        <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider mb-1">Service Tag</p>
                        <p class="text-lg font-mono font-bold text-cyan-600" id="viewWaiterCode">—</p>
                    </div>
                    <div class="bg-white/5 p-4 rounded-xl border border-white/5">
                        <p class="text-[10px] font-bold text-white/40 uppercase tracking-wider mb-1">Joined Date</p>
                        <p class="text-lg font-bold text-white" id="viewWaiterJoined">—</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="bg-violet-500/10 p-3 rounded-xl border border-violet-500/20">
                        <p class="text-[9px] font-bold text-violet-300 uppercase tracking-wider mb-1">Orders</p>
                        <p class="text-xl font-bold text-white" id="viewWaiterOrders">0</p>
                    </div>
                    <div class="bg-amber-500/10 p-3 rounded-xl border border-amber-500/20">
                        <p class="text-[9px] font-bold text-amber-300 uppercase tracking-wider mb-1">Tips</p>
                        <p class="text-xl font-bold text-amber-600" id="viewWaiterTips">0</p>
                        <p class="text-[8px] text-white/40">TSh</p>
                    </div>
                    <div class="bg-emerald-500/10 p-3 rounded-xl border border-emerald-500/20">
                        <p class="text-[9px] font-bold text-emerald-300 uppercase tracking-wider mb-1">Rating</p>
                        <p class="text-xl font-bold text-white flex items-center gap-1" id="viewWaiterRating">0.0 ⭐</p>
                        <p class="text-[8px] text-white/40" id="viewWaiterRatingCount">0 reviews</p>
                    </div>
                </div>

                <button onclick="closeViewWaiterModal()" class="w-full bg-fin-mist text-white py-3.5 rounded-xl font-semibold hover:bg-surface-900/20 transition-all">Close Profile</button>
            </div>
        </div>
    </div>

    <script>
        function searchWaiter() {
            const q = document.getElementById('searchCode').value.trim();
            const resultEl = document.getElementById('searchResult');
            const errorEl = document.getElementById('searchError');
            resultEl.classList.add('hidden');
            resultEl.innerHTML = '';
            errorEl.classList.add('hidden');
            if (!q) {
                errorEl.textContent = 'Enter the unique number (TIPTAP-W-xxxxx).';
                errorEl.classList.remove('hidden');
                return;
            }
            resultEl.innerHTML = '<p class="text-white/50 text-sm py-3">Searching…</p>';
            resultEl.classList.remove('hidden');
            fetch('{{ route("manager.waiters.search") }}?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin',
            })
                .then(async function (r) {
                    let data = null;
                    try {
                        data = await r.json();
                    } catch (e) {
                        data = null;
                    }

                    if (!r.ok) {
                        resultEl.classList.add('hidden');
                        resultEl.innerHTML = '';
                        errorEl.textContent = (data && (data.message || data.error)) || (r.status === 401 ? 'Session expired. Please sign in again.' : 'Search failed. Please try again.');
                        errorEl.classList.remove('hidden');
                        return;
                    }

                    if (!data || !data.success) {
                        resultEl.classList.add('hidden');
                        resultEl.innerHTML = '';
                        errorEl.textContent = (data && data.message) || 'Waiter not found.';
                        errorEl.classList.remove('hidden');
                        return;
                    }
                    const w = data.waiter;
                    let html = '<div class="p-4 rounded-xl bg-white/5 border border-white/10 space-y-4">';
                    html += '<div class="flex items-start gap-4">';
                    if (w.profile_photo_url) {
                        html += '<img src="' + w.profile_photo_url + '" alt="" class="w-14 h-14 rounded-xl object-cover border border-violet-500/20 shrink-0">';
                    } else {
                        html += '<div class="w-14 h-14 rounded-xl bg-linear-to-br from-fin-primary/15 to-cyan-500/10 flex items-center justify-center font-bold text-xl text-fin-primary border border-violet-500/20 shrink-0">' + (w.name ? w.name.charAt(0) : '—') + '</div>';
                    }
                    html += '<div class="min-w-0 flex-1"><p class="font-bold text-white text-lg">' + (w.name || '—') + '</p>';
                    html += '<p class="text-sm text-white/60">' + (w.email || '') + '</p>';
                    html += '<p class="text-sm text-white/60">Phone: ' + (w.phone || '—') + '</p>';
                    if (w.location) html += '<p class="text-sm text-white/60">Location: ' + w.location + '</p>';
                    html += '<p class="text-sm font-mono text-cyan-600 mt-2">' + (w.global_waiter_number || '') + '</p>';
                    html += '<p class="text-xs text-white/40 mt-2">Orders: ' + (w.orders_count || 0) + ' · Ratings: ' + (w.feedback_count || 0) + '</p></div></div>';

                    if (w.work_history && w.work_history.length > 0) {
                        html += '<div class="pt-3 border-t border-white/10">';
                        html += '<p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2">Work history</p>';
                        html += '<ul class="space-y-2">';
                        w.work_history.forEach(function(h) {
                            const linkedDate = h.linked_at ? new Date(h.linked_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
                            const unlinkedDate = h.unlinked_at ? new Date(h.unlinked_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : null;
                            const typeLabel = h.employment_type === 'temporary' ? ' (Show-time)' : ' (Long-term)';
                            if (h.is_active) {
                                html += '<li class="flex items-start gap-2 text-sm"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mt-1.5 shrink-0 animate-pulse"></span><span class="text-white"><strong class="text-white">' + (h.restaurant_name || '—') + '</strong>' + typeLabel + ' — Working since ' + linkedDate + ' <span class="text-emerald-600 font-medium">(Active)</span></span></li>';
                            } else {
                                html += '<li class="flex items-start gap-2 text-sm"><span class="w-1.5 h-1.5 rounded-full bg-surface-900/30 mt-1.5 shrink-0"></span><span class="text-white/60">Worked at <strong class="text-white/90">' + (h.restaurant_name || '—') + '</strong>' + typeLabel + ' — ' + linkedDate + ' to ' + (unlinkedDate || '—') + '</span></li>';
                            }
                        });
                        html += '</ul></div>';
                    }

                    if (w.is_linked) {
                        if (w.is_linked_to_my_restaurant) {
                            html += '<p class="text-emerald-600 text-sm mt-2">Already linked to your restaurant. Check the waiters list above.</p>';
                        } else if (w.current_restaurant) {
                            html += '<p class="text-amber-600 text-sm mt-2">Already linked to: ' + (w.current_restaurant || '—') + '. That restaurant manager must unlink them first.</p>';
                        } else {
                            html += '<p class="text-amber-600 text-sm mt-2">This waiter is already linked to another restaurant.</p>';
                        }
                    } else {
                        var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
                        var linkUrl = '{{ url("manager/waiters") }}/' + w.id + '/link';
                        html += '<form action="' + linkUrl + '" method="POST" class="mt-4 space-y-4">';
                        html += '<input type="hidden" name="_token" value="' + token + '">';
                        html += '<p class="text-xs text-white/40 mb-2">Choose link type: long-term (stays until you unlink) or show-time (fixed period – set an end date).</p>';
                        html += '<div class="flex flex-wrap gap-4 items-end">';
                        html += '<div><label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-1 block">Link type</label>';
                        html += '<label class="inline-flex items-center gap-2 mr-4"><input type="radio" name="employment_type" value="permanent" checked class="rounded border-white/20" onchange="toggleLinkUntil(this)"> <span class="text-white text-sm">Long-term (Permanent)</span></label>';
                        html += '<label class="inline-flex items-center gap-2"><input type="radio" name="employment_type" value="temporary" class="rounded border-white/20" onchange="toggleLinkUntil(this)"> <span class="text-white text-sm">Fixed / Show-time</span></label></div>';
                        html += '<div id="linkUntilWrap" class="hidden"><label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-1 block">End date</label>';
                        html += '<input type="date" name="linked_until" id="linkUntilInput" class="px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm" min="' + new Date().toISOString().slice(0,10) + '"></div>';
                        html += '</div>';
                        html += '<button type="submit" class="px-4 py-2 bg-linear-to-r from-fin-primary to-fin-primary-dark text-white rounded-xl font-semibold hover:shadow-lg transition-all">Link Waiter</button>';
                        html += '</form>';
                    }
                    html += '</div>';
                    resultEl.innerHTML = html;
                    resultEl.classList.remove('hidden');
                })
                .catch(() => {
                    resultEl.classList.add('hidden');
                    resultEl.innerHTML = '';
                    errorEl.textContent = 'Network error. Please try again.';
                    errorEl.classList.remove('hidden');
                });
        }

        function openViewWaiterModal(waiter) {
            document.getElementById('viewWaiterName').textContent = waiter.name || '—';
            document.getElementById('viewWaiterEmail').textContent = waiter.email || '—';
            document.getElementById('viewWaiterCode').textContent = waiter.waiter_code || '—';
            document.getElementById('viewWaiterGlobalCode').textContent = waiter.global_waiter_number || '—';
            var photoEl = document.getElementById('viewWaiterPhoto');
            var initialEl = document.getElementById('viewWaiterInitial');
            if (waiter.profile_photo_url) {
                photoEl.src = waiter.profile_photo_url;
                photoEl.classList.remove('hidden');
                initialEl.classList.add('hidden');
            } else {
                photoEl.classList.add('hidden');
                initialEl.classList.remove('hidden');
                initialEl.textContent = (waiter.name && waiter.name.charAt(0)) || '—';
            }
            document.getElementById('viewWaiterOrders').textContent = waiter.orders_count ?? 0;
            document.getElementById('viewWaiterTips').textContent = new Intl.NumberFormat().format(waiter.tips_sum ?? 0);
            document.getElementById('viewWaiterRating').textContent = (waiter.rating ?? 0).toFixed(1) + ' ⭐';
            document.getElementById('viewWaiterRatingCount').textContent = (waiter.rating_count ?? 0) + ' reviews';
            const date = waiter.created_at ? new Date(waiter.created_at) : null;
            document.getElementById('viewWaiterJoined').textContent = date ? date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
            var emp = (waiter.employment_type === 'temporary' && waiter.linked_until)
                ? 'Show-time · until ' + new Date(waiter.linked_until).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
                : 'Long-term (Permanent)';
            document.getElementById('viewWaiterEmployment').textContent = emp;
            document.getElementById('viewWaiterModal').classList.remove('hidden');
            document.getElementById('viewWaiterModal').classList.add('flex');
        }

        function closeViewWaiterModal() {
            document.getElementById('viewWaiterModal').classList.add('hidden');
            document.getElementById('viewWaiterModal').classList.remove('flex');
        }

        function toggleLinkUntil(radio) {
            var wrap = document.getElementById('linkUntilWrap');
            var input = document.getElementById('linkUntilInput');
            if (radio && radio.value === 'temporary') {
                if (wrap) wrap.classList.remove('hidden');
                if (input) input.setAttribute('required', 'required');
            } else {
                if (wrap) wrap.classList.add('hidden');
                if (input) { input.removeAttribute('required'); input.value = ''; }
            }
        }

        async function copyToClipboard(text, button) {
            try {
                await navigator.clipboard.writeText(text);
                const orig = button.innerHTML;
                button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><path d="M20 6 9 17l-5-5"/></svg>';
                button.classList.add('bg-emerald-500/20', 'border-emerald-500/30');
                setTimeout(() => { button.innerHTML = orig; button.classList.remove('bg-emerald-500/20', 'border-emerald-500/30'); }, 2000);
            } catch (e) {
                alert('Copy failed');
            }
        }

        function filterWaiters() {
            const searchTerm = document.getElementById('waiterSearch').value.toLowerCase();
            const statusFilter = document.querySelector('input[name="statusFilter"]:checked')?.value || 'all';
            const sortBy = document.getElementById('sortBy').value;
            const cards = Array.from(document.querySelectorAll('.waiter-card'));
            
            let visibleCount = 0;
            cards.forEach(card => {
                const name = card.dataset.name || '';
                const code = card.dataset.code || '';
                const status = card.dataset.status || '';
                const employment = card.dataset.employment || '';
                const tips = card.dataset.tips || '';
                
                let matchesSearch = !searchTerm || name.includes(searchTerm) || code.includes(searchTerm);
                let matchesStatus = statusFilter === 'all' || 
                    (statusFilter === 'online' && status === 'online') ||
                    (statusFilter === 'offline' && status === 'offline') ||
                    (statusFilter === 'permanent' && employment === 'permanent') ||
                    (statusFilter === 'temporary' && employment === 'temporary') ||
                    (statusFilter === 'tips_on' && tips === 'on') ||
                    (statusFilter === 'tips_off' && tips === 'off');
                
                if (matchesSearch && matchesStatus) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Sort cards
            if (sortBy === 'orders') {
                cards.sort((a, b) => parseInt(b.dataset.orders || 0) - parseInt(a.dataset.orders || 0));
            } else if (sortBy === 'name') {
                cards.sort((a, b) => (a.dataset.name || '').localeCompare(b.dataset.name || ''));
            }
            
            const grid = document.getElementById('waitersGrid');
            cards.forEach(card => grid.appendChild(card));
            
            document.getElementById('waiterCount').textContent = `(${visibleCount})`;
        }

        // Initialize count on page load
        document.addEventListener('DOMContentLoaded', function() {
            filterWaiters();
        });
    </script>
</x-manager-layout>
