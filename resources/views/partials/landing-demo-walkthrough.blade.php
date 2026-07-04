{{-- Auto-playing interactive Rafiki walkthrough (fallback when no video URL) --}}
@php
    $demo = $landing['demo'];
    $market = $landing['market'] ?? config('tiptap.market', 'tz');
    $paySceneLine = $market === 'za'
        ? 'Pay with PayFast · Card / EFT'
        : 'Pay with M-Pesa / Tigo / Airtel';
    $currencySample = $market === 'za' ? 'R 215.00' : 'Tsh 21,500';
@endphp
<div class="demo-walkthrough relative mx-auto w-full max-w-[340px]" data-demo-walkthrough>
    <div class="absolute -inset-6 rounded-[3rem] bg-linear-to-br from-fin-primary/20 via-transparent to-whatsapp/10 blur-2xl pointer-events-none"></div>
    <div class="relative rounded-[2.75rem] border-10 border-fin-ink bg-fin-ink shadow-[0_28px_60px_-12px_rgba(109,82,232,0.45)] overflow-hidden">
        <div class="h-7 bg-fin-ink flex items-center justify-center">
            <div class="w-[72px] h-[18px] bg-black rounded-full"></div>
        </div>
        <div class="bg-[#E5DDD5] h-[420px] sm:h-[460px] flex flex-col overflow-hidden">
            <div class="bg-linear-to-r from-[#075E54] to-[#128C7E] px-3 py-2.5 flex items-center gap-2.5 shrink-0">
                <img src="{{ asset('images/logo-64.png') }}" alt="" width="32" height="32" loading="lazy" decoding="async" class="w-8 h-8 rounded-full bg-white p-0.5 object-contain">
                <div class="min-w-0">
                    <p class="text-white text-sm font-semibold leading-tight">TipTap Rafiki</p>
                    <p class="text-emerald-100/90 text-[9px]">demo · online</p>
                </div>
            </div>

            <div class="flex-1 relative overflow-hidden">
                <div class="demo-walkthrough-scene is-active absolute inset-0 flex flex-col items-center justify-center px-6 text-center bg-linear-to-b from-fin-mist to-[#E5DDD5]" data-scene="0">
                    <div class="w-28 h-28 rounded-3xl bg-white shadow-lg border-2 border-fin-primary/20 flex items-center justify-center mb-4">
                        <i data-lucide="qr-code" class="w-14 h-14 text-fin-primary"></i>
                    </div>
                    <p class="text-sm font-semibold text-fin-ink">Scan table QR</p>
                    <p class="text-[11px] text-fin-muted mt-1 leading-relaxed">Guest points camera at Table 7</p>
                </div>

                <div class="demo-walkthrough-scene absolute inset-0 px-2 py-3 space-y-2 opacity-0 pointer-events-none" data-scene="1">
                    <div class="max-w-[92%] bg-white rounded-lg rounded-tl-none px-2.5 py-2 shadow-sm text-[10px] text-[#111B21]">
                        <p class="font-semibold mb-1">👋 Welcome to TipTap Grill</p>
                        <p>1️⃣ View Our Menu</p>
                        <p>2️⃣ Pay Bill</p>
                        <p>3️⃣ Tip your waiter</p>
                    </div>
                    <div class="flex justify-end">
                        <div class="max-w-[40%] bg-[#D9FDD3] rounded-lg rounded-tr-none px-3 py-1.5 text-[11px] font-medium">1</div>
                    </div>
                    <div class="max-w-[88%] bg-white rounded-lg rounded-tl-none px-2.5 py-2 shadow-sm text-[10px]">
                        🍽️ <b>{{ $market === 'za' ? 'Grilled Ribeye' : 'Grilled Tilapia' }}</b> — {{ $market === 'za' ? 'R 189' : 'Tsh 18,000' }}<br>
                        🥤 {{ $market === 'za' ? 'Craft Lemonade' : 'Passion Juice' }} — {{ $market === 'za' ? 'R 35' : 'Tsh 3,500' }}
                    </div>
                </div>

                <div class="demo-walkthrough-scene absolute inset-0 px-2 py-3 space-y-2 opacity-0 pointer-events-none" data-scene="2">
                    <div class="flex justify-end">
                        <div class="max-w-[55%] bg-[#D9FDD3] rounded-lg rounded-tr-none px-2.5 py-1.5 text-[10px]">Confirm order ✓</div>
                    </div>
                    <div class="max-w-[92%] bg-white rounded-lg rounded-tl-none px-2.5 py-2 shadow-sm text-[10px]">
                        ✅ Order #1042 sent to kitchen<br>
                        <span class="text-[#667781]">Table 7 · ~15 min</span>
                    </div>
                    <div class="mx-auto mt-4 w-[90%] rounded-xl bg-fin-ink p-3 text-white font-mono text-[9px] shadow-lg">
                        <div class="flex justify-between border-b border-white/10 pb-2 mb-2">
                            <span class="text-fin-lavender font-bold">KDS</span>
                            <span class="text-amber-300">PREPARING</span>
                        </div>
                        <p>1x {{ $market === 'za' ? 'Grilled Ribeye' : 'Grilled Tilapia' }}</p>
                        <p class="opacity-70">1x {{ $market === 'za' ? 'Craft Lemonade' : 'Passion Juice' }}</p>
                    </div>
                </div>

                <div class="demo-walkthrough-scene absolute inset-0 px-2 py-3 space-y-2 opacity-0 pointer-events-none" data-scene="3">
                    <div class="max-w-[92%] bg-white rounded-lg rounded-tl-none px-2.5 py-2 shadow-sm text-[10px]">
                        💵 Bill: <b>{{ $currencySample }}</b><br>
                        {{ $paySceneLine }}
                    </div>
                    <div class="flex justify-end">
                        <div class="max-w-[50%] bg-[#D9FDD3] rounded-lg rounded-tr-none px-2.5 py-1.5 text-[10px]">Pay now</div>
                    </div>
                    <div class="max-w-[92%] bg-white rounded-lg rounded-tl-none px-2.5 py-2 shadow-sm text-[10px] border-l-4 border-whatsapp">
                        ✅ Payment received!<br>
                        <span class="text-[#667781]">Receipt sent · Thank you 🙏</span>
                    </div>
                </div>
            </div>

            <div class="bg-[#F0F2F5] px-3 py-2 flex items-center gap-2 shrink-0">
                <div class="flex-1 bg-white rounded-full px-3 py-1.5 text-[10px] text-gray-400">Message…</div>
                <div class="w-8 h-8 rounded-full bg-[#075E54] flex items-center justify-center">
                    <i data-lucide="send" class="w-3.5 h-3.5 text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 flex justify-center gap-2" data-demo-dots role="group" aria-label="Demo steps">
        @foreach ($demo['steps'] as $index => $step)
            <button type="button"
                    class="demo-walkthrough-dot h-2 rounded-full transition-all duration-300 {{ $index === 0 ? 'w-6 bg-fin-primary' : 'w-2 bg-fin-primary/25' }}"
                    data-demo-dot="{{ $index }}"
                    aria-label="{{ $step['title'] }}"
                    aria-current="{{ $index === 0 ? 'step' : 'false' }}"></button>
        @endforeach
    </div>
</div>
