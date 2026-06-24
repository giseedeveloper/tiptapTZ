{{-- Pricing nurture note --}}
<div class="mt-12 text-center reveal">
    <p class="text-sm text-fin-muted mb-4">{{ $landing['nurture']['cta_secondary_hint'] }}</p>
    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        @include('partials.landing-book-demo-link', ['variant' => 'solid-light'])
        @include('partials.landing-chat-with-us-link', ['variant' => 'solid'])
    </div>
</div>
