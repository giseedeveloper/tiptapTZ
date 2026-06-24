{{-- FAQ accordion + WhatsApp chat fallback --}}
<section id="faq" class="py-20 lg:py-28 bg-fin-surface">
    <div class="max-w-2xl mx-auto px-5">
        <div class="text-center mb-12 reveal">
            <span class="section-label">FAQ</span>
            <h2 class="text-3xl font-light text-fin-ink mt-4">Frequently asked questions</h2>
        </div>
        <div class="space-y-3 reveal">
            @foreach ($landing['faq']['items'] as $item)
                <details class="fin-card rounded-2xl px-6 py-5 group bg-white">
                    <summary class="flex justify-between items-center gap-4 font-semibold text-fin-ink text-[0.95rem]">
                        {{ $item['question'] }}
                        <i data-lucide="plus" class="w-5 h-5 text-fin-muted group-open:rotate-45 transition-transform shrink-0"></i>
                    </summary>
                    <p class="text-sm text-fin-muted mt-4 leading-relaxed pr-8">{{ $item['answer'] }}</p>
                </details>
            @endforeach
        </div>
        <div class="mt-10 text-center reveal">
            <p class="text-sm text-fin-muted">
                {{ $landing['faq']['chat_label'] }}
                <a
                    href="{{ $landing['whatsapp_url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1.5 font-semibold text-fin-primary-dark hover:text-fin-primary transition-colors ml-1"
                >
                    {{ $landing['faq']['chat_cta'] }}
                    <i data-lucide="message-circle" class="w-4 h-4 text-whatsapp"></i>
                </a>
            </p>
        </div>
    </div>
</section>
