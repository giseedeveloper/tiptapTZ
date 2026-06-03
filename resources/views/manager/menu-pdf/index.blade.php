<x-manager-layout>
    <x-slot name="header">Menu PDF</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="glass-card rounded-2xl p-8 mb-8">
            <div class="flex items-start gap-6">
                <div class="w-16 h-16 bg-gradient-to-br from-rose-600 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg shadow-rose-500/30">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M10 13H8"/><path d="M16 13h-2"/><path d="M10 17H8"/><path d="M16 17h-2"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white mb-2">WhatsApp Menu (PDF)</h2>
                    <p class="text-white/60 text-sm leading-relaxed">
                        Upload your full menu as one PDF. When customers tap <strong class="text-white">View Menu</strong> on WhatsApp, they receive the PDF to open on their phone.
                        After reading, they type what they want to order (e.g. <em>2 chips y kuku</em>).
                    </p>
                    <p class="text-white/40 text-xs mt-3">
                        For individual dishes and prices in the dashboard, use <a href="{{ route('manager.menu.index') }}" class="text-violet-400 hover:text-violet-300 font-semibold">Dishes</a>.
                    </p>
                </div>
            </div>
        </div>

        @if($restaurant && $restaurant->menu_pdf)
            <div class="glass-card rounded-2xl p-6 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                        <h3 class="text-lg font-bold text-white">Current Menu PDF</h3>
                    </div>
                    <span class="px-3 py-1.5 bg-emerald-500/10 text-emerald-400 text-xs font-bold rounded-full uppercase tracking-wider border border-emerald-500/20">
                        Active
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-5 bg-white/5 rounded-xl border border-white/10">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-xl bg-rose-500/20 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-400"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-white truncate">{{ $restaurant->menuPdfFilename() }}</p>
                            <p class="text-xs text-white/40 mt-1">Sent to customers on WhatsApp</p>
                        </div>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ $restaurant->menuPdfUrl() }}" target="_blank" class="px-4 py-2 glass rounded-lg text-sm font-semibold text-white/70 hover:text-white hover:bg-white/10 transition-all">
                            Open PDF
                        </a>
                        <form action="{{ route('manager.menu-pdf.destroy') }}" method="POST" onsubmit="return confirm('Remove this menu PDF?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 text-sm font-semibold border border-red-500/20 transition-all">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-3 h-3 bg-violet-500 rounded-full"></div>
                <h3 class="text-lg font-bold text-white">
                    {{ $restaurant && $restaurant->menu_pdf ? 'Replace Menu PDF' : 'Upload Menu PDF' }}
                </h3>
            </div>

            <form action="{{ route('manager.menu-pdf.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                @csrf

                <div
                    id="dropZone"
                    class="relative border-2 border-dashed border-white/10 hover:border-rose-500/50 rounded-xl p-12 text-center transition-all duration-300 cursor-pointer group"
                    onclick="document.getElementById('menu_pdf').click();"
                >
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center group-hover:bg-rose-500/20 transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white/40 group-hover:text-rose-400">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold mb-1">Click to upload or drag & drop</p>
                            <p class="text-white/40 text-sm">PDF only — max 15 MB</p>
                        </div>
                        <p id="fileName" class="text-rose-400 text-sm font-medium hidden"></p>
                    </div>
                    <input
                        type="file"
                        name="menu_pdf"
                        id="menu_pdf"
                        accept="application/pdf,.pdf"
                        class="hidden"
                        onchange="handleFileSelect(this)"
                    >
                </div>

                @error('menu_pdf')
                    <p class="mt-3 text-sm text-red-400 font-medium">{{ $message }}</p>
                @enderror

                <button type="submit" class="w-full mt-6 bg-gradient-to-r from-rose-600 to-orange-600 text-white py-3.5 rounded-xl font-semibold hover:shadow-lg hover:shadow-rose-500/25 transition-all">
                    Upload PDF
                </button>
            </form>
        </div>

        <div class="glass-card rounded-2xl p-6 mt-8 border border-white/5">
            <h3 class="text-sm font-bold text-white/40 uppercase tracking-wider mb-3">Bot API</h3>
            <code class="text-cyan-400 text-sm">GET /api/bot/restaurant/{restaurant_id}/menu-pdf</code>
        </div>
    </div>

    <script>
        function handleFileSelect(input) {
            const fileName = document.getElementById('fileName');
            if (input.files && input.files[0]) {
                fileName.textContent = input.files[0].name;
                fileName.classList.remove('hidden');
            }
        }
    </script>
</x-manager-layout>
