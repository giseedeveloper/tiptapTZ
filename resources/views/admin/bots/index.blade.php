<x-admin-layout>
    <x-slot name="header">
        Bot Control Center
    </x-slot>

    @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-sm font-bold">
            {{ session('success') }}
        </div>
    @endif

    @if($newBotToken)
    <div class="mb-8 glass-card rounded-2xl p-8 border border-amber-500/30 overflow-hidden relative">
        <h3 class="text-lg font-black text-amber-300 tracking-tight mb-2">New bot token — copy now</h3>
        <p class="text-[10px] text-white/40 font-bold uppercase tracking-widest mb-6">Paste into your bot server <code class="text-amber-200">.env</code> as <code class="text-amber-200">BOT_TOKEN</code>. This is shown once per generation.</p>
        <div class="flex items-center gap-4 bg-white/5 p-4 rounded-xl border border-white/10">
            <code id="new-bot-token" class="flex-1 font-mono text-xs text-emerald-400 break-all">{{ $newBotToken }}</code>
            <button type="button" data-copy-target="new-bot-token" class="p-3 glass text-white rounded-xl hover:bg-white/10 transition-all border border-white/10">
                <i data-lucide="copy" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
    @elseif($botTokenConfigured)
    <div class="mb-8 glass-card rounded-2xl p-8 border border-emerald-500/20">
        <h3 class="text-lg font-black text-white tracking-tight mb-2">Bot API token configured</h3>
        <p class="text-[10px] text-white/40 font-bold uppercase tracking-widest">A token is set on the server (<code class="text-white/50">BOT_TOKEN</code>). It is not displayed here for security. Regenerate below if you need a new one.</p>
    </div>
    @endif

    @php
        $brandingSettings = is_array($brandingBot->settings) ? $brandingBot->settings : [];
        $brandingPreviewImage = filled($brandingSettings['welcome_image_path'] ?? null)
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($brandingSettings['welcome_image_path'])
            : ($defaultBranding['image_url'] ?? asset('images/icon-512.png'));
    @endphp

    <div class="mb-8 glass-card rounded-2xl p-8 border border-violet-500/20">
        <div class="flex items-start gap-4 mb-6">
            <div class="w-14 h-14 bg-gradient-to-br from-violet-600 to-cyan-500 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-violet-500/20 shrink-0">
                <i data-lucide="image" class="w-7 h-7"></i>
            </div>
            <div>
                <h3 class="text-xl font-black text-white tracking-tight">WhatsApp welcome card</h3>
                <p class="text-[10px] text-white/45 font-bold uppercase tracking-widest mt-1">Logo + message when customer says hi</p>
            </div>
        </div>

        <form action="{{ route('admin.bots.update-branding') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="bot_id" value="{{ $brandingBot->id }}">
            <div class="flex flex-col sm:flex-row items-start gap-4">
                <img src="{{ $brandingPreviewImage }}" alt="Welcome logo preview" class="w-24 h-24 rounded-xl bg-white object-contain p-2 border border-white/10 shrink-0">
                <p class="text-xs text-white/50 leading-relaxed">This image appears on top of the WhatsApp reply — same rich card style as marketing bots (logo, title, then message).</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label class="text-[9px] font-bold uppercase tracking-wider text-white/40 block">Welcome title</label>
                    <input type="text" name="welcome_title" value="{{ old('welcome_title', $brandingSettings['welcome_title'] ?? $defaultBranding['title'] ?? 'TipTap') }}" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-xs text-white placeholder-white/30 focus:ring-2 focus:ring-violet-500" placeholder="TipTap">
                </div>
                <div class="space-y-2">
                    <label class="text-[9px] font-bold uppercase tracking-wider text-white/40 block">Welcome logo image</label>
                    <input type="file" name="welcome_image" accept="image/png,image/jpeg,image/webp" class="w-full text-xs text-white/60 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-violet-600 file:text-white file:text-[10px] file:font-bold">
                    <p class="text-[9px] text-white/35">PNG/JPG/WEBP, max 2MB. Default: <code class="text-white/50">/images/icon-512.png</code></p>
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-[9px] font-bold uppercase tracking-wider text-white/40 block">Welcome message (optional)</label>
                <textarea name="welcome_body" rows="3" class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-xs text-white placeholder-white/30 focus:ring-2 focus:ring-violet-500" placeholder="Leave empty to use bot language (EN/SW) automatically">{{ old('welcome_body', $brandingSettings['welcome_body'] ?? '') }}</textarea>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <label class="flex items-center gap-2 text-[10px] text-white/50">
                    <input type="checkbox" name="remove_welcome_image" value="1" class="rounded border-white/20 bg-white/5 text-violet-500">
                    Reset to default logo
                </label>
                <button type="submit" class="sm:ml-auto px-8 py-3 bg-gradient-to-r from-violet-600 to-cyan-600 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest hover:shadow-lg transition-all">
                    Save welcome card
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($bots as $bot)
        <div class="glass-card rounded-2xl p-8 hover:border-violet-500/30 transition-all group">
            <div class="flex justify-between items-start mb-8">
                <div class="w-14 h-14 bg-gradient-to-br from-violet-600 to-cyan-500 rounded-2xl flex items-center justify-center text-white shadow-xl shadow-violet-500/20 group-hover:scale-110 transition-all">
                    <i data-lucide="bot" class="w-8 h-8"></i>
                </div>
                <div class="flex flex-col items-end">
                    @if($bot->status == 'active')
                        <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 text-[9px] font-black rounded-full uppercase tracking-widest border border-emerald-500/20 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span> Active
                        </span>
                    @else
                        <span class="px-3 py-1 bg-white/5 text-white/40 text-[9px] font-black rounded-full uppercase tracking-widest border border-white/10">Inactive</span>
                    @endif
                    <span class="text-[9px] text-white/40 font-bold uppercase tracking-widest mt-2">Last Ping: {{ $bot->last_ping ? $bot->last_ping->diffForHumans() : 'Never' }}</span>
                </div>
            </div>

            <h3 class="text-xl font-black text-white tracking-tight mb-2">{{ $bot->name }}</h3>
            <p class="text-[10px] text-white/40 font-bold uppercase tracking-widest mb-6">Automation & Integration Bot</p>

            <div class="space-y-4 pt-6 border-t border-white/5">
                <form action="{{ route('admin.bots.update-endpoint') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="bot_id" value="{{ $bot->id }}">
                    <div class="space-y-2">
                        <label class="text-[9px] font-bold uppercase tracking-wider text-white/40 block">Endpoint URL</label>
                        <div class="flex gap-2">
                            <input type="url" name="endpoint" value="{{ old('endpoint', $bot->endpoint) }}" class="flex-1 px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-xs font-mono text-white placeholder-white/30 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all" placeholder="https://api.bot.com/webhook">
                            <button type="submit" class="p-3 bg-gradient-to-r from-violet-600 to-cyan-600 text-white rounded-xl hover:shadow-lg transition-all">
                                <i data-lucide="save" class="w-4 h-4"></i>
                            </button>
                        </div>
                        @error('endpoint')
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </form>

                <div class="flex gap-2 pt-2">
                    <button type="button" class="flex-1 py-3 glass text-white/70 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-violet-600 hover:text-white transition-all">Restart Bot</button>
                    <button type="button" class="flex-1 py-3 glass text-white/70 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-violet-600 hover:text-white transition-all">View Logs</button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full glass-card rounded-2xl p-12 text-center">
            <div class="w-20 h-20 bg-white/5 rounded-full flex items-center justify-center text-white/20 mx-auto mb-6 border border-white/10">
                <i data-lucide="bot" class="w-10 h-10"></i>
            </div>
            <h3 class="text-2xl font-black text-white tracking-tight mb-2">No Bots Configured</h3>
            <p class="text-[10px] text-white/40 font-bold uppercase tracking-widest mb-8">System automation bots will appear here</p>
            <div class="flex flex-col gap-4 mt-8 max-w-md mx-auto">
                <button type="button" class="px-8 py-4 glass text-white rounded-xl font-bold text-sm hover:bg-white/10 transition-all">Register New Bot</button>

                <form action="{{ route('admin.bots.generate-token') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full px-8 py-4 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white rounded-xl font-bold text-sm hover:shadow-lg hover:shadow-emerald-500/25 transition-all flex items-center justify-center gap-2">
                        <i data-lucide="key" class="w-4 h-4"></i>
                        Generate Bot Token
                    </button>
                </form>
                <p class="text-[9px] text-white/40 text-center font-bold uppercase tracking-widest">Copy the token to your bot server — Laravel .env is not modified automatically</p>
            </div>
        </div>
        @endforelse
    </div>

    @if($bots->isNotEmpty())
    <div class="mt-8 max-w-md">
        <form action="{{ route('admin.bots.generate-token') }}" method="POST">
            @csrf
            <button type="submit" class="w-full px-8 py-4 bg-gradient-to-r from-emerald-500 to-cyan-500 text-white rounded-xl font-bold text-sm hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <i data-lucide="key" class="w-4 h-4"></i>
                Regenerate Bot Token
            </button>
        </form>
    </div>
    @endif

    <script>
        document.querySelectorAll('[data-copy-target]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const el = document.getElementById(btn.dataset.copyTarget);
                if (el) {
                    navigator.clipboard.writeText(el.textContent.trim());
                }
            });
        });
    </script>
</x-admin-layout>
