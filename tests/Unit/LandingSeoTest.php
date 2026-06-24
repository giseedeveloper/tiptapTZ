<?php

use App\Support\LandingSeo;

it('builds schema.org graph with organization software and faq', function () {
    $graph = LandingSeo::structuredData([
        'market' => 'tz',
        'title' => 'TipTap Tanzania',
        'description' => 'QR and WhatsApp ordering for restaurants in Tanzania.',
        'canonical_url' => 'https://tiptapafrica.co.tz',
        'logo_url' => 'https://tiptapafrica.co.tz/images/logo.png',
        'locale' => 'en_TZ',
        'geo_region' => 'TZ',
        'geo_place_name' => 'Tanzania',
        'whatsapp_url' => 'https://wa.me/255791070771',
        'social' => ['instagram' => 'https://instagram.com/tiptapafrica'],
        'offices' => [
            'tz' => [
                'name' => 'Tanzania',
                'city' => 'Dar es Salaam',
                'lines' => ['Tanzanite Park', '13th Floor'],
            ],
        ],
        'faq' => [
            'items' => [
                [
                    'question' => 'What is TipTap?',
                    'answer' => 'A restaurant operating system for Tanzania.',
                ],
            ],
        ],
        'currency_code' => 'TZS',
    ]);

    expect($graph['@context'])->toBe('https://schema.org')
        ->and($graph['@graph'])->toHaveCount(4);

    $types = collect($graph['@graph'])->pluck('@type')->all();

    expect($types)->toContain('Organization')
        ->and($types)->toContain('WebSite')
        ->and($types)->toContain('SoftwareApplication')
        ->and($types)->toContain('FAQPage');
});

it('omits faq page node when there are no faq items', function () {
    $graph = LandingSeo::structuredData([
        'market' => 'za',
        'title' => 'TipTap South Africa',
        'description' => 'QR ordering in South Africa.',
        'canonical_url' => 'https://tiptapafrica.co.za',
        'logo_url' => 'https://tiptapafrica.co.za/images/logo.png',
        'locale' => 'en_ZA',
        'geo_region' => 'ZA',
        'geo_place_name' => 'South Africa',
        'whatsapp_url' => 'https://wa.me/27000000000',
        'social' => [],
        'offices' => [],
        'faq' => ['items' => []],
        'currency_code' => 'ZAR',
    ]);

    expect($graph['@graph'])->toHaveCount(3);
});
