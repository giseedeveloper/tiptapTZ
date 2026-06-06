<x-manager-layout>
    <x-slot name="header">Restaurant Wallet</x-slot>

    <div class="mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-white tracking-tight">Wallet</h2>
            <p class="text-sm font-medium text-white/40 uppercase tracking-wider">{{ $restaurant->name }} · payments collected via TIPTAP</p>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <a href="{{ route('manager.payments.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-400 hover:text-emerald-300 transition-colors">
                Full payment reports
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            <a href="{{ route('manager.wallet.export') }}" class="inline-flex items-center gap-2 text-sm font-bold text-white/70 hover:text-white transition-colors">
                Export wallet CSV
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-sm font-medium text-emerald-400">{{ session('success') }}</div>
    @endif

    @if($withdrawalAlerts->isNotEmpty())
        <div class="mb-6 p-4 rounded-xl border border-violet-500/20 bg-violet-500/10">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-3">
                <p class="text-sm font-bold text-violet-300">Withdrawal updates</p>
                <form action="{{ route('manager.wallet.notifications.read') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[10px] font-bold uppercase tracking-wider text-violet-300/80 hover:text-violet-200">Mark all read</button>
                </form>
            </div>
            <div class="space-y-2">
                @foreach($withdrawalAlerts as $alert)
                    <p class="text-sm text-white/80">{{ $alert->data['message'] ?? 'Withdrawal status updated.' }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        <div class="glass-card p-6 rounded-2xl">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Total received (gross)</p>
            <p class="text-2xl font-black text-white mt-2">{{ config('tiptap.currency_symbol') }} {{ number_format($summary['total_earned'], 0) }}</p>
        </div>
        <div class="glass-card p-6 rounded-2xl">
            <p class="text-[10px] font-bold uppercase tracking-widest text-rose-400/80">Platform fee ({{ number_format($summary['commission_rate'], 0) }}%)</p>
            <p class="text-2xl font-black text-rose-400/90 mt-2">− {{ config('tiptap.currency_symbol') }} {{ number_format($summary['platform_commission'], 0) }}</p>
        </div>
        <div class="glass-card p-6 rounded-2xl">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Net earnings</p>
            <p class="text-2xl font-black text-white mt-2">{{ config('tiptap.currency_symbol') }} {{ number_format($summary['net_earned'], 0) }}</p>
        </div>
        <div class="glass-card p-6 rounded-2xl">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Withdrawn</p>
            <p class="text-2xl font-black text-white/70 mt-2">{{ config('tiptap.currency_symbol') }} {{ number_format($summary['total_withdrawn'], 0) }}</p>
        </div>
        <div class="glass-card p-6 rounded-2xl">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Pending requests</p>
            <p class="text-2xl font-black text-amber-400 mt-2">{{ config('tiptap.currency_symbol') }} {{ number_format($summary['pending_withdrawals'], 0) }}</p>
        </div>
        <div class="glass-card p-6 rounded-2xl border border-emerald-500/20">
            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-400/80">Available balance</p>
            <p class="text-2xl font-black text-emerald-400 mt-2">{{ config('tiptap.currency_symbol') }} {{ number_format($summary['available_balance'], 0) }}</p>
        </div>
    </div>

    @if(! empty($breakdown['by_type']) || ! empty($breakdown['by_method']))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-white/5">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Earnings by type</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[280px]">
                        <thead><tr class="bg-white/5 text-[10px] font-black uppercase tracking-widest text-white/40">
                            <th class="px-5 py-3 text-left">Type</th>
                            <th class="px-5 py-3 text-right">Count</th>
                            <th class="px-5 py-3 text-right">Total</th>
                        </tr></thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($breakdown['by_type'] as $row)
                                <tr>
                                    <td class="px-5 py-3 text-sm text-white/80 capitalize">{{ $row['type'] }}</td>
                                    <td class="px-5 py-3 text-sm text-white/60 text-right">{{ $row['count'] }}</td>
                                    <td class="px-5 py-3 text-sm font-bold text-white text-right">{{ config('tiptap.currency_symbol') }} {{ number_format($row['total'], 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-6 text-center text-white/40 text-sm">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="glass-card rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-white/5">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Earnings by method</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[280px]">
                        <thead><tr class="bg-white/5 text-[10px] font-black uppercase tracking-widest text-white/40">
                            <th class="px-5 py-3 text-left">Method</th>
                            <th class="px-5 py-3 text-right">Count</th>
                            <th class="px-5 py-3 text-right">Total</th>
                        </tr></thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse($breakdown['by_method'] as $row)
                                <tr>
                                    <td class="px-5 py-3 text-sm text-white/80 capitalize">{{ $row['method'] }}</td>
                                    <td class="px-5 py-3 text-sm text-white/60 text-right">{{ $row['count'] }}</td>
                                    <td class="px-5 py-3 text-sm font-bold text-white text-right">{{ config('tiptap.currency_symbol') }} {{ number_format($row['total'], 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-6 text-center text-white/40 text-sm">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="space-y-8">
            <div class="glass-card p-8 rounded-2xl">
                <h3 class="text-lg font-bold text-white mb-2">Saved payout profile</h3>
                <p class="text-xs text-white/50 mb-6">Save your M-Pesa or bank details once, then reuse them when requesting withdrawals.</p>
                <form action="{{ route('manager.wallet.payout-profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Default payout method</label>
                        <select name="payout_method" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium [&>option]:bg-gray-900" required>
                            <option value="">Select…</option>
                            <option value="Mobile Money" {{ old('payout_method', $restaurant->payout_method) === 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
                            <option value="Bank Transfer" {{ old('payout_method', $restaurant->payout_method) === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                        @error('payout_method') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Account / phone details</label>
                        <textarea name="payout_details" rows="3" placeholder="M-Pesa number, bank account, account name…" required
                                  class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">{{ old('payout_details', $restaurant->payout_details) }}</textarea>
                        @error('payout_details') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full bg-white/10 hover:bg-white/15 text-white py-3 rounded-xl font-bold transition-colors">
                        Save payout profile
                    </button>
                </form>
            </div>

            <div class="glass-card p-8 rounded-2xl" x-data="{ useSaved: {{ ($useSavedPayoutDefault || old('use_saved_payout')) ? 'true' : 'false' }} }">
                <h3 class="text-lg font-bold text-white mb-6">Request withdrawal</h3>
                <p class="text-xs text-white/50 mb-6">Admin reviews and pays out manually. Minimum: {{ config('tiptap.currency_symbol') }} {{ number_format($minWithdrawal, 0) }}. Withdrawals use your <strong class="text-white/70">available balance</strong> (after platform fee).</p>
                <form action="{{ route('manager.wallet.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Amount ({{ config('tiptap.currency_symbol') }})</label>
                        <input type="number" name="amount" min="{{ $minWithdrawal }}" max="{{ $summary['available_balance'] }}" step="1" value="{{ old('amount') }}"
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-bold" required>
                        @error('amount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if($restaurant->hasPayoutProfile())
                        <label class="flex items-start gap-3 p-3 rounded-xl bg-white/5 border border-white/10 cursor-pointer">
                            <input type="checkbox" name="use_saved_payout" value="1" x-model="useSaved"
                                   class="mt-1 rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500"
                                   {{ ($useSavedPayoutDefault || old('use_saved_payout')) ? 'checked' : '' }}>
                            <span class="text-sm text-white/80">
                                <span class="font-bold text-white block">Use saved payout profile</span>
                                <span class="text-white/50 text-xs">{{ $restaurant->payout_method }} · {{ Str::limit($restaurant->payout_details, 60) }}</span>
                            </span>
                        </label>
                        @error('use_saved_payout') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    @endif

                    <div x-show="!useSaved" x-cloak class="space-y-4">
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Payout method</label>
                            <select name="payment_method" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium [&>option]:bg-gray-900" :required="!useSaved">
                                <option value="">Select…</option>
                                <option value="Mobile Money" {{ old('payment_method') === 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                            @error('payment_method') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Account / phone details</label>
                            <textarea name="payment_details" rows="3" placeholder="M-Pesa number, bank account, name…" :required="!useSaved"
                                      class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">{{ old('payment_details') }}</textarea>
                            @error('payment_details') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white py-3.5 rounded-xl font-bold hover:opacity-90" {{ $summary['available_balance'] <= 0 ? 'disabled' : '' }}>
                        Submit withdrawal request
                    </button>
                </form>
            </div>
        </div>

        <div class="glass-card p-8 rounded-2xl">
            <h3 class="text-lg font-bold text-white mb-4">Your withdrawal history</h3>
            <div class="space-y-3">
                @forelse($withdrawals as $w)
                    <div class="p-4 glass rounded-xl">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="font-bold text-white">{{ config('tiptap.currency_symbol') }} {{ number_format($w->amount, 0) }}</p>
                                <p class="text-[10px] text-white/40">{{ $w->created_at->format('M d, Y H:i') }} · {{ $w->payment_method }}</p>
                            </div>
                            @php
                                $statusClass = match ($w->status) {
                                    'approved', 'paid' => 'border-emerald-500/30 text-emerald-400 bg-emerald-500/10',
                                    'rejected' => 'border-rose-500/30 text-rose-400 bg-rose-500/10',
                                    'pending' => 'border-amber-500/30 text-amber-400 bg-amber-500/10',
                                    default => 'border-white/10 text-white/60',
                                };
                            @endphp
                            <span class="text-[9px] font-black uppercase px-2 py-1 rounded-full border shrink-0 {{ $statusClass }}">{{ $w->status }}</span>
                        </div>
                        @if($w->status === 'rejected' && filled($w->admin_note))
                            <p class="mt-2 text-xs text-rose-300/90 bg-rose-500/5 border border-rose-500/10 rounded-lg px-3 py-2">
                                <span class="font-bold">Admin note:</span> {{ $w->admin_note }}
                            </p>
                        @elseif(filled($w->admin_note) && in_array($w->status, ['approved', 'paid'], true))
                            <p class="mt-2 text-xs text-white/50">{{ $w->admin_note }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-white/40">No withdrawal requests yet.</p>
                @endforelse
            </div>
            @if($withdrawals->hasPages())
                <div class="mt-4">{{ $withdrawals->links() }}</div>
            @endif
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-white/5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="text-lg font-bold text-white">Recent payments (this restaurant only)</h3>
            <a href="{{ route('manager.payments.index') }}" class="text-xs font-bold uppercase tracking-wider text-emerald-400 hover:text-emerald-300">View all & export →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[500px]">
                <thead><tr class="bg-white/5 text-[10px] font-black uppercase tracking-widest text-white/40">
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Amount</th>
                    <th class="px-6 py-3 text-left">Type</th>
                    <th class="px-6 py-3 text-left">Method</th>
                    <th class="px-6 py-3 text-left">Reference</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentPayments as $p)
                    <tr>
                        <td class="px-6 py-4 text-sm text-white/70">{{ $p->created_at->format('M d, H:i') }}</td>
                        <td class="px-6 py-4 font-bold text-white">{{ config('tiptap.currency_symbol') }} {{ number_format($p->amount, 0) }}</td>
                        <td class="px-6 py-4 text-sm text-white/60 capitalize">{{ $p->payment_type ?? 'order' }}</td>
                        <td class="px-6 py-4 text-sm text-white/60">{{ $p->method ?? '—' }}</td>
                        <td class="px-6 py-4 text-xs font-mono text-white/40 truncate max-w-[140px]">{{ $p->transaction_reference ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-white/40 text-sm">No paid transactions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recentPayments->hasPages())
            <div class="p-4 border-t border-white/5">{{ $recentPayments->links() }}</div>
        @endif
    </div>
</x-manager-layout>
