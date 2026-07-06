@props([
    'role' => 'manager',
    'intent' => 'login',
    'locale' => 'en',
])

@php
    use App\Support\SocialAuth;

    $providers = SocialAuth::visibleProviders();

    $heading = match (true) {
        $locale === 'sw' && $intent === 'register' => 'Jisajili kwa',
        $locale === 'sw' => 'Ingia kwa',
        $intent === 'register' => 'Sign up with',
        default => 'Sign in with',
    };
@endphp

<div {{ $attributes->merge(['class' => 'auth-social-block']) }}>
    @if (session('social_notice'))
        <div class="mb-4 rounded-xl border border-amber-200/80 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900" role="status">
            {{ session('social_notice') }}
        </div>
    @endif

    <p class="mb-3 text-center text-sm font-medium text-[#64708B]">{{ $heading }}</p>

    <div class="flex items-center justify-center gap-4">
        @foreach ($providers as $provider)
            @include('partials.auth-provider-circle', [
                'provider' => $provider,
                'role' => $role,
                'intent' => $intent,
            ])
        @endforeach
    </div>
</div>

<style>
    .auth-provider-circle {
        display: inline-flex;
        height: 3rem;
        width: 3rem;
        align-items: center;
        justify-content: center;
        border-radius: 9999px;
        border: 1px solid #E8E8ED;
        background: #fff;
        box-shadow: 0 1px 2px rgba(18, 20, 28, 0.06), 0 4px 12px rgba(18, 20, 28, 0.05);
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        cursor: pointer;
    }

    .auth-provider-circle:hover {
        transform: translateY(-1px);
        border-color: #D4D4DC;
        box-shadow: 0 4px 16px rgba(18, 20, 28, 0.1);
    }

    .auth-provider-circle:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(18, 20, 28, 0.06), 0 2px 8px rgba(18, 20, 28, 0.06);
    }

    button.auth-provider-circle {
        appearance: none;
        padding: 0;
        font: inherit;
        color: inherit;
    }
</style>
