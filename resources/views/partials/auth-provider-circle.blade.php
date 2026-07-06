@php
    use App\Support\SocialAuth;

    $label = SocialAuth::providerLabel($provider);
    $actionLabel = $intent === 'register' ? 'Register with '.$label : 'Sign in with '.$label;
    $isGoogle = $provider === SocialAuth::PROVIDER_GOOGLE;
@endphp

@if ($isGoogle)
    <a href="{{ route('social.redirect', ['provider' => $provider, 'role' => $role, 'intent' => $intent]) }}"
       title="{{ $actionLabel }}"
       aria-label="{{ $actionLabel }}"
       class="auth-provider-circle">
        @include('partials.auth-provider-icon', ['provider' => $provider, 'size' => 'lg'])
    </a>
@else
    <button type="button"
            title="{{ $label }}"
            aria-label="{{ $label }}"
            class="auth-provider-circle"
            onclick="return false">
        @include('partials.auth-provider-icon', ['provider' => $provider, 'size' => 'lg'])
    </button>
@endif
