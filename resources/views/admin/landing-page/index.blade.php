<x-admin-layout>
    <x-slot name="header">
        Landing Page Manager
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-white/50 max-w-xl">
                    Edit the public home page. Changes appear on the site immediately after you save.
                </p>
            </div>
            <a href="{{ url('/') }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white/80 hover:bg-white/10 transition-colors">
                Preview home page
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14 21 3"/></svg>
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-semibold text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.landing-page.update') }}" method="POST" class="space-y-8">
            @csrf

            <div class="glass-card rounded-2xl p-8">
                <div class="mb-8">
                    <h3 class="text-2xl font-black text-white tracking-tight">Hero section</h3>
                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Top of the home page</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Live badge</label>
                        <input type="text" name="hero_live_badge" value="{{ old('hero_live_badge', $content['hero_live_badge']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Badge subtitle</label>
                        <input type="text" name="hero_badge_text" value="{{ old('hero_badge_text', $content['hero_badge_text']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Headline line 1</label>
                        <input type="text" name="hero_title_line1" value="{{ old('hero_title_line1', $content['hero_title_line1']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Headline line 2</label>
                        <input type="text" name="hero_title_line2" value="{{ old('hero_title_line2', $content['hero_title_line2']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Description</label>
                        <textarea name="hero_description" rows="3"
                                  class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('hero_description', $content['hero_description']) }}</textarea>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Primary button</label>
                        <input type="text" name="hero_cta_primary" value="{{ old('hero_cta_primary', $content['hero_cta_primary']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Secondary button</label>
                        <input type="text" name="hero_cta_secondary" value="{{ old('hero_cta_secondary', $content['hero_cta_secondary']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-8">
                <div class="mb-8">
                    <h3 class="text-2xl font-black text-white tracking-tight">Contact &amp; offices</h3>
                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Addresses shown on the home page</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Section label</label>
                        <input type="text" name="contact_label" value="{{ old('contact_label', $content['contact_label']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Section title</label>
                        <input type="text" name="contact_title" value="{{ old('contact_title', $content['contact_title']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Section description</label>
                        <textarea name="contact_description" rows="2"
                                  class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('contact_description', $content['contact_description']) }}</textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="rounded-xl border border-white/10 bg-white/[0.02] p-6 space-y-4">
                        <h4 class="text-sm font-bold text-violet-300 uppercase tracking-wider">Tanzania office</h4>
                        <input type="text" name="office_tz_name" value="{{ old('office_tz_name', $content['office_tz_name']) }}" placeholder="Country name"
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                        <input type="text" name="office_tz_city" value="{{ old('office_tz_city', $content['office_tz_city']) }}" placeholder="City / area"
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                        <input type="text" name="office_tz_line1" value="{{ old('office_tz_line1', $content['office_tz_line1']) }}" placeholder="Address line 1"
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                        <input type="text" name="office_tz_line2" value="{{ old('office_tz_line2', $content['office_tz_line2']) }}" placeholder="Address line 2"
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[0.02] p-6 space-y-4">
                        <h4 class="text-sm font-bold text-violet-300 uppercase tracking-wider">South Africa office</h4>
                        <input type="text" name="office_za_name" value="{{ old('office_za_name', $content['office_za_name']) }}" placeholder="Country name"
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                        <input type="text" name="office_za_city" value="{{ old('office_za_city', $content['office_za_city']) }}" placeholder="City / area"
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                        <input type="text" name="office_za_line1" value="{{ old('office_za_line1', $content['office_za_line1']) }}" placeholder="Address line 1"
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                        <input type="text" name="office_za_line2" value="{{ old('office_za_line2', $content['office_za_line2']) }}" placeholder="Address line 2"
                               class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-8">
                <div class="mb-8">
                    <h3 class="text-2xl font-black text-white tracking-tight">Social media &amp; WhatsApp</h3>
                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Full URLs — leave blank to hide a platform</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Social block title</label>
                        <input type="text" name="contact_social_title" value="{{ old('contact_social_title', $content['contact_social_title']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">WhatsApp link</label>
                        <input type="url" name="whatsapp_url" value="{{ old('whatsapp_url', $content['whatsapp_url']) }}" placeholder="https://wa.me/..."
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Social block description</label>
                        <textarea name="contact_social_description" rows="2"
                                  class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('contact_social_description', $content['contact_social_description']) }}</textarea>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach (['facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X (Twitter)', 'linkedin' => 'LinkedIn', 'tiktok' => 'TikTok', 'youtube' => 'YouTube'] as $key => $label)
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">{{ $label }}</label>
                            <input type="url" name="social_{{ $key }}" value="{{ old('social_'.$key, $content['social_'.$key]) }}"
                                   placeholder="https://"
                                   class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                            @error('social_'.$key)
                                <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="glass-card rounded-2xl p-8">
                <div class="mb-8">
                    <h3 class="text-2xl font-black text-white tracking-tight">Call to action</h3>
                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Purple banner above the footer</p>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Title</label>
                        <input type="text" name="cta_title" value="{{ old('cta_title', $content['cta_title']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Description</label>
                        <textarea name="cta_description" rows="2"
                                  class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('cta_description', $content['cta_description']) }}</textarea>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Button text</label>
                        <input type="text" name="cta_button" value="{{ old('cta_button', $content['cta_button']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-8">
                <div class="mb-6">
                    <h3 class="text-2xl font-black text-white tracking-tight">Footer</h3>
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Tagline under logo</label>
                    <textarea name="footer_tagline" rows="2"
                              class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('footer_tagline', $content['footer_tagline']) }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded-xl bg-violet-600 px-8 py-4 text-sm font-bold text-white shadow-lg shadow-violet-600/30 hover:bg-violet-500 transition-colors">
                    Save landing page
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
