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
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">TipTap Rafiki intro</label>
                        <input type="text" name="hero_rafiki_intro" value="{{ old('hero_rafiki_intro', $content['hero_rafiki_intro']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Rafiki meaning line</label>
                        <input type="text" name="hero_rafiki_meaning" value="{{ old('hero_rafiki_meaning', $content['hero_rafiki_meaning']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
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
                    <h3 class="text-2xl font-black text-white tracking-tight">Video demo &amp; live Rafiki</h3>
                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Homepage demo section — paste a YouTube/Vimeo link or leave blank for the interactive preview</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Section title</label>
                        <input type="text" name="demo_title" value="{{ old('demo_title', $content['demo_title']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Section subtitle</label>
                        <textarea name="demo_subtitle" rows="2"
                                  class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('demo_subtitle', $content['demo_subtitle']) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Demo video URL (YouTube, Vimeo, or .mp4)</label>
                        <input type="url" name="demo_video_url" value="{{ old('demo_video_url', $content['demo_video_url']) }}"
                               placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Try Rafiki button label</label>
                        <input type="text" name="demo_try_rafiki_label" value="{{ old('demo_try_rafiki_label', $content['demo_try_rafiki_label']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">WhatsApp prefill message</label>
                        <input type="text" name="demo_try_rafiki_message" value="{{ old('demo_try_rafiki_message', $content['demo_try_rafiki_message']) }}"
                               placeholder="Hi"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-8">
                <div class="mb-8">
                    <h3 class="text-2xl font-black text-white tracking-tight">Nurture CTAs &amp; lead magnet</h3>
                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Book-a-demo WhatsApp/Calendly and efficiency guide capture</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Book demo button label</label>
                        <input type="text" name="nurture_book_demo_label" value="{{ old('nurture_book_demo_label', $content['nurture_book_demo_label']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Chat with us label</label>
                        <input type="text" name="nurture_chat_with_us_label" value="{{ old('nurture_chat_with_us_label', $content['nurture_chat_with_us_label']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Calendly URL (optional — overrides WhatsApp booking)</label>
                        <input type="url" name="nurture_book_demo_calendly_url" value="{{ old('nurture_book_demo_calendly_url', $content['nurture_book_demo_calendly_url']) }}"
                               placeholder="https://calendly.com/tiptap/demo"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">WhatsApp book-demo prefill message</label>
                        <textarea name="nurture_book_demo_whatsapp_message" rows="2"
                                  class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('nurture_book_demo_whatsapp_message', $content['nurture_book_demo_whatsapp_message']) }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Lead magnet title</label>
                        <input type="text" name="nurture_lead_magnet_title" value="{{ old('nurture_lead_magnet_title', $content['nurture_lead_magnet_title']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Lead magnet subtitle</label>
                        <textarea name="nurture_lead_magnet_subtitle" rows="2"
                                  class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('nurture_lead_magnet_subtitle', $content['nurture_lead_magnet_subtitle']) }}</textarea>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Lead magnet button</label>
                        <input type="text" name="nurture_lead_magnet_button" value="{{ old('nurture_lead_magnet_button', $content['nurture_lead_magnet_button']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Lead magnet success message</label>
                        <input type="text" name="nurture_lead_magnet_success" value="{{ old('nurture_lead_magnet_success', $content['nurture_lead_magnet_success']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-8">
                <div class="mb-8">
                    <h3 class="text-2xl font-black text-white tracking-tight">SEO &amp; schema markup</h3>
                    <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Page title, meta description, and search/social previews</p>
                </div>
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">SEO title (geo + keywords)</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $content['seo_title']) }}"
                               class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm font-bold text-white">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Meta description</label>
                        <textarea name="seo_description" rows="3"
                                  class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('seo_description', $content['seo_description']) }}</textarea>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-2 block">Meta keywords (optional)</label>
                        <textarea name="seo_keywords" rows="2"
                                  class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-sm text-white">{{ old('seo_keywords', $content['seo_keywords']) }}</textarea>
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
