<x-manager-layout>
    <x-slot name="header">Restaurant Wallet</x-slot>

    <div class="mb-8">
        <h2 class="text-3xl font-bold text-white tracking-tight">Wallet</h2>
        <p class="text-sm font-medium text-white/40 uppercase tracking-wider">{{ $restaurant->name }} · payments collected via TIPTAP</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-sm font-medium text-emerald-400">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="glass-card p-6 rounded-2xl">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Total received</p>
            <p class="text-2xl font-black text-white mt-2">{{ config('tiptap.currency_symbol') }} {{ number_format($summary['total_earned'], 0) }}</p>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="glass-card p-8 rounded-2xl">
            <h3 class="text-lg font-bold text-white mb-6">Request withdrawal</h3>
            <p class="text-xs text-white/50 mb-6">Admin reviews and pays out manually. Minimum: {{ config('tiptap.currency_symbol') }} {{ number_format($minWithdrawal, 0) }}.</p>
            <form action="{{ route('manager.wallet.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Amount ({{ config('tiptap.currency_symbol') }})</label>
                    <input type="number" name="amount" min="{{ $minWithdrawal }}" max="{{ $summary['available_balance'] }}" step="1" value="{{ old('amount') }}"
                           class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-bold" required>
                    @error('amount') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Payout method</label>
                    <select name="payment_method" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white font-medium [&>option]:bg-gray-900" required>
                        <option value="">Select…</option>
                        <option value="Mobile Money" {{ old('payment_method') === 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
                        <option value="Bank Transfer" {{ old('payment_method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                    @error('payment_method') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Account / phone details</label>
                    <textarea name="payment_details" rows="3" placeholder="M-Pesa number, bank account, name…" required
                              class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white text-sm">{{ old('payment_details') }}</textarea>
                    @error('payment_details') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white py-3.5 rounded-xl font-bold hover:opacity-90" {{ $summary['available_balance'] <= 0 ? 'disabled' : '' }}>
                    Submit withdrawal request
                </button>
            </form>
        </div>

        <div class="glass-card p-8 rounded-2xl">
            <h3 class="text-lg font-bold text-white mb-4">Your withdrawal history</h3>
            <div class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
                @forelse($withdrawals as $w)
                    <div class="flex items-center justify-between p-4 glass rounded-xl">
                        <div>
                            <p class="font-bold text-white">{{ config('tiptap.currency_symbol') }} {{ number_format($w->amount, 0) }}</p>
                            <p class="text-[10px] text-white/40">{{ $w->created_at->format('M d, Y H:i') }} · {{ $w->payment_method }}</p>
                        </div>
                        <span class="text-[9px] font-black uppercase px-2 py-1 rounded-full border border-white/10 text-white/60">{{ $w->status }}</span>
                    </div>
                @empty
                    <p class="text-sm text-white/40">No withdrawal requests yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="glass-card rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-white/5">
            <h3 class="text-lg font-bold text-white">Recent payments (this restaurant only)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[500px]">
                <thead><tr class="bg-white/5 text-[10px] font-black uppercase tracking-widest text-white/40">
                    <th class="px-6 py-3 text-left">Date</th>
                    <th class="px-6 py-3 text-left">Amount</th>
                    <th class="px-6 py-3 text-left">Method</th>
                    <th class="px-6 py-3 text-left">Reference</th>
                </tr></thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentPayments as $p)
                    <tr>
                        <td class="px-6 py-4 text-sm text-white/70">{{ $p->created_at->format('M d, H:i') }}</td>
                        <td class="px-6 py-4 font-bold text-white">{{ config('tiptap.currency_symbol') }} {{ number_format($p->amount, 0) }}</td>
                        <td class="px-6 py-4 text-sm text-white/60">{{ $p->method ?? '—' }}</td>
                        <td class="px-6 py-4 text-xs font-mono text-white/40 truncate max-w-[140px]">{{ $p->transaction_reference ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-white/40 text-sm">No paid transactions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-manager-layout>
