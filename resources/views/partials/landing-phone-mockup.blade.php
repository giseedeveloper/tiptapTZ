{{-- TipTap Rafiki — premium phone mockup (real bot conversation) --}}
<div class="phone-mockup relative mx-auto w-[290px] sm:w-[310px] lg:w-[340px] phone-float">
    <div class="absolute -inset-8 rounded-[4rem] bg-linear-to-br from-[#8C71F6]/30 via-[#C6BDFA]/20 to-transparent blur-3xl pointer-events-none"></div>
    <div class="absolute -right-6 top-1/4 w-20 h-20 rounded-2xl bg-white/80 backdrop-blur border border-white shadow-lg hidden items-center justify-center sm:flex">
        <i data-lucide="qr-code" class="w-8 h-8 text-[#8C71F6]"></i>
    </div>
    <div class="absolute -left-4 bottom-1/3 w-16 h-16 rounded-full bg-whatsapp/15 border border-whatsapp/30 hidden items-center justify-center sm:flex">
        <i data-lucide="message-circle" class="w-7 h-7 text-whatsapp"></i>
    </div>
    <div class="relative rounded-[3rem] border-11 border-fin-ink bg-fin-ink shadow-[0_32px_64px_-12px_rgba(109,82,232,0.45),0_0_0_1px_rgba(255,255,255,0.06)_inset] overflow-hidden ring-1 ring-white/10">
        <div class="h-8 bg-fin-ink flex items-center justify-center">
            <div class="w-[88px] h-[22px] bg-black rounded-full"></div>
        </div>
        <div class="bg-[#E5DDD5] h-[540px] sm:h-[580px] flex flex-col overflow-hidden">
            <div class="bg-linear-to-r from-[#075E54] to-[#128C7E] px-3.5 py-3 flex items-center gap-3 shrink-0 shadow-md">
                <div class="w-10 h-10 rounded-full bg-white p-0.5 ring-2 ring-white/30 shrink-0 overflow-hidden">
                    <img src="{{ asset('images/logo-64.png') }}" alt="TAPTAP" width="40" height="40" class="w-full h-full object-contain rounded-full">
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-[15px] font-semibold leading-tight tracking-tight">TipTap Rafiki</p>
                    <p class="text-emerald-100/90 text-[10px] flex items-center gap-1.5 mt-0.5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-whatsapp opacity-60"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-whatsapp"></span>
                        </span>
                        online
                    </p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-2 py-2.5 space-y-1.5 chat-scroll bg-[url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23d4cdc4%22 fill-opacity=%220.15%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]">
                <div class="flex justify-center">
                    <span class="text-[8px] font-medium bg-[#E1F2FB] text-[#54656F] px-2 py-0.5 rounded-md shadow-sm">Today</span>
                </div>

                {{-- Bot: feedback prompt --}}
                <div class="chat-bubble" style="--delay: 0.1s">
                    <div class="max-w-[88%] bg-white rounded-lg rounded-tl-none px-2.5 py-1.5 shadow-sm text-[10px] text-[#111B21] leading-snug">
                        📝 Enter comment or type <b>END</b> to finish
                        <p class="text-[8px] text-[#667781] text-right mt-0.5">10:15</p>
                    </div>
                </div>

                {{-- User: comment --}}
                <div class="chat-bubble flex justify-end" style="--delay: 0.25s">
                    <div class="max-w-[72%] bg-[#D9FDD3] rounded-lg rounded-tr-none px-2.5 py-1.5 shadow-sm text-[10px] text-[#111B21]">
                        She is quick
                        <p class="text-[8px] text-[#667781] text-right mt-0.5">10:15 ✓✓</p>
                    </div>
                </div>

                {{-- Bot: thanks --}}
                <div class="chat-bubble" style="--delay: 0.4s">
                    <div class="max-w-[88%] bg-white rounded-lg rounded-tl-none px-2.5 py-1.5 shadow-sm text-[10px] text-[#111B21]">
                        🙏 Thanks for your feedback!
                        <p class="text-[8px] text-[#667781] text-right mt-0.5">10:15</p>
                    </div>
                </div>

                {{-- Bot: main menu --}}
                <div class="chat-bubble" style="--delay: 0.55s">
                    <div class="max-w-[95%] bg-white rounded-lg rounded-tl-none px-2.5 py-2 shadow-sm text-[9px] text-[#111B21] leading-[1.35]">
                        <p class="text-center text-[#667781] tracking-wider mb-1">━━━━━━ 🏠 ✨ ━━━━━━</p>
                        <p class="font-semibold mb-1">👋 Welcome <span class="font-bold">TipTap Grill</span> (Anna Doe)</p>
                        <p class="mb-0.5">Choose service:</p>
                        <p class="italic text-[#667781] mb-1.5">Type <b>0</b> anytime to go back here.</p>
                        <p class="font-bold mb-1">🍽️ MAIN SERVICES</p>
                        <div class="space-y-0.5 text-[8.5px]">
                            <p>1️⃣ 🍽️ View Our Menu</p>
                            <p>2️⃣ ⭐ Rate Service ANNA DOE</p>
                            <p>3️⃣ 💵 Pay Bill</p>
                            <p>4️⃣ 💵 Tip ANNA DOE</p>
                            <p>5️⃣ 🔔 Call Waiter</p>
                            <p>6️⃣ 📞 Customer Support</p>
                            <p>7️⃣ 🌐 Change language</p>
                            <p>8️⃣ ❌ Exit</p>
                        </div>
                        <p class="text-center text-[#667781] tracking-wider my-1">━━━━━━━━━━━━━━━</p>
                        <p class="font-semibold text-[#075E54]">✅ ReplyNumberToChoose</p>
                        <p class="text-[8px] text-[#667781] text-right mt-1">10:15</p>
                    </div>
                </div>

                {{-- User: selects 4 --}}
                <div class="chat-bubble flex justify-end" style="--delay: 0.75s">
                    <div class="max-w-[40%] bg-[#D9FDD3] rounded-lg rounded-tr-none px-3 py-1.5 shadow-sm text-[11px] font-medium text-[#111B21]">
                        4
                        <p class="text-[8px] text-[#667781] text-right mt-0.5">10:17 ✓✓</p>
                    </div>
                </div>

                {{-- Bot: tip prompt --}}
                <div class="chat-bubble" style="--delay: 0.9s">
                    <div class="max-w-[88%] bg-white rounded-lg rounded-tl-none px-2.5 py-1.5 shadow-sm text-[10px] text-[#111B21]">
                        💰 Tip ANNA DOE (Tsh):
                        <p class="text-[8px] text-[#667781] text-right mt-0.5">10:17</p>
                    </div>
                </div>
            </div>

            <div class="bg-[#F0F2F5] px-2.5 py-2.5 flex items-center gap-2 shrink-0">
                <div class="flex-1 bg-white rounded-full px-4 py-2 text-[11px] text-gray-400 shadow-inner">Message…</div>
                <div class="w-9 h-9 rounded-full bg-linear-to-br from-[#075E54] to-[#128C7E] flex items-center justify-center shadow-md">
                    <i data-lucide="mic" class="w-4 h-4 text-white"></i>
                </div>
            </div>
        </div>
    </div>
</div>
