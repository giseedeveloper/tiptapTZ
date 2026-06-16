@php
    $socialPlatforms = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'x' => 'X (Twitter)',
        'linkedin' => 'LinkedIn',
        'tiktok' => 'TikTok',
        'youtube' => 'YouTube',
    ];
    $socialLinks = collect($landing['social'] ?? config('tiptap.company.social', []))
        ->filter(fn (?string $url) => filled($url));
    $linkClass = $linkClass ?? 'inline-flex items-center justify-center rounded-xl bg-white/10 p-2.5 hover:bg-white/20 hover:scale-105 transition-all';
    $iconClass = $iconClass ?? 'w-5 h-5';
    $onDark = $onDark ?? false;
@endphp

@if ($socialLinks->isNotEmpty())
    <div class="{{ $containerClass ?? 'flex flex-wrap gap-3' }}">
        @foreach ($socialPlatforms as $key => $label)
            @if (filled($socialLinks->get($key)))
                <a
                    href="{{ $socialLinks->get($key) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="{{ $linkClass }}"
                    aria-label="{{ $label }}"
                >
                    @include('partials.social-platform-icon', [
                        'platform' => $key,
                        'class' => $iconClass,
                        'onDark' => $onDark,
                        'gradientSuffix' => $key.'-'.$loop->index,
                    ])
                </a>
            @endif
        @endforeach
    </div>
@elseif (! empty($showPlaceholder))
    <p class="text-sm text-slate-400 italic">Social links coming soon.</p>
@endif
