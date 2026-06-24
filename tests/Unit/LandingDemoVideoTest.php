<?php

use App\Support\LandingDemoVideo;

it('embeds youtube watch urls', function () {
    $embed = LandingDemoVideo::embed('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    expect($embed)->not->toBeNull()
        ->and($embed['provider'])->toBe('youtube')
        ->and($embed['embed_url'])->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0&modestbranding=1')
        ->and($embed['poster'])->toContain('dQw4w9WgXcQ');
});

it('embeds youtube short links', function () {
    $embed = LandingDemoVideo::embed('https://youtu.be/dQw4w9WgXcQ');

    expect($embed)->not->toBeNull()
        ->and($embed['provider'])->toBe('youtube');
});

it('embeds vimeo urls', function () {
    $embed = LandingDemoVideo::embed('https://vimeo.com/123456789');

    expect($embed)->not->toBeNull()
        ->and($embed['provider'])->toBe('vimeo')
        ->and($embed['embed_url'])->toBe('https://player.vimeo.com/video/123456789?dnt=1');
});

it('embeds direct mp4 files', function () {
    $embed = LandingDemoVideo::embed('https://cdn.example.test/rafiki-demo.mp4');

    expect($embed)->not->toBeNull()
        ->and($embed['provider'])->toBe('file')
        ->and($embed['embed_url'])->toBe('https://cdn.example.test/rafiki-demo.mp4');
});

it('returns null for unsupported urls', function () {
    expect(LandingDemoVideo::embed('https://example.test/not-a-video'))->toBeNull();
    expect(LandingDemoVideo::embed(''))->toBeNull();
});
