<x-admin-layout>
    <x-slot name="header">Payment Integration</x-slot>

    <div class="max-w-3xl mx-auto space-y-8">
        <div class="glass-card rounded-2xl p-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-white/40">Platform · {{ config('tiptap.payment_gateway') }}</p>
                    <h2 class="text-2xl font-black text-white tracking-tight mt-1">Mobile money integration</h2>
                    <p class="text-sm text-white/50 mt-2 max-w-xl">One gateway for all restaurants. USSD push uses these credentials; each payment is still tagged to the correct restaurant.</p>
                </div>
                <span class="px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest border {{ $configured ? 'border-emerald-500/30 text-emerald-400 bg-emerald-500/10' : 'border-amber-500/30 text-amber-400 bg-amber-500/10' }}">
                    {{ $configured ? 'Configured' : 'Not configured' }}
                </span>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-sm font-medium text-emerald-400">{{ session('success') }}</div>
            @endif

            <form action="{{ route('admin.payment-integration.update') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Vendor ID</label>
                    <input type="text" name="payment_vendor_id" value="{{ old('payment_vendor_id', $values['vendor_id']) }}" placeholder="e.g. TILL60917564"
                           class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white focus:ring-2 focus:ring-violet-500">
                    @error('payment_vendor_id') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">API Key</label>
                    <input type="password" name="payment_api_key" value="{{ old('payment_api_key', $values['api_key']) }}"
                           class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white focus:ring-2 focus:ring-violet-500">
                    @error('payment_api_key') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">API Secret</label>
                    <input type="password" name="payment_api_secret" value="{{ old('payment_api_secret', $values['api_secret']) }}"
                           class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white focus:ring-2 focus:ring-violet-500">
                    @error('payment_api_secret') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="flex items-center justify-between p-4 glass rounded-xl">
                    <div>
                        <p class="text-sm font-semibold text-white">Live mode</p>
                        <p class="text-[10px] text-white/40">Production payments (off = sandbox)</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="payment_is_live" value="1" {{ old('payment_is_live', $values['is_live']) === '1' ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-white/10 rounded-full peer peer-checked:bg-violet-500 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                    <button type="submit" class="w-full bg-gradient-to-r from-violet-600 to-cyan-600 text-white py-3.5 rounded-xl font-bold hover:opacity-90 transition-opacity">Save for all restaurants</button>
                    @if($configured)
                    <button type="button" id="test-payment-btn" class="w-full glass border border-cyan-500/30 text-cyan-400 py-3.5 rounded-xl font-bold hover:bg-cyan-500/10 transition-all">Test connection</button>
                    @endif
                </div>
                <div id="test-result" class="hidden text-sm rounded-xl p-4"></div>
            </form>
        </div>
    </div>

    @if($configured)
    <script>
        document.getElementById('test-payment-btn')?.addEventListener('click', async function () {
            const box = document.getElementById('test-result');
            box.classList.remove('hidden');
            box.textContent = 'Testing…';
            try {
                const res = await fetch('{{ route('admin.payment-integration.test') }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await res.json();
                box.className = 'text-sm rounded-xl p-4 ' + (data.success ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20');
                box.textContent = data.message || (data.success ? 'OK' : 'Failed');
            } catch (e) {
                box.className = 'text-sm rounded-xl p-4 bg-rose-500/10 text-rose-400';
                box.textContent = 'Request failed';
            }
        });
    </script>
    @endif
</x-admin-layout>
