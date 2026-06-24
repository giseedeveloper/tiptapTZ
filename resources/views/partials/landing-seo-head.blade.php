{{-- Geo-targeted SEO meta tags + Open Graph + JSON-LD schema.org --}}
@php
    $seo = $landing['seo'];
@endphp

<title>{{ $seo['title'] }}</title>
<meta name="description" content="{{ $seo['description'] }}">
@if (filled($seo['keywords']))
    <meta name="keywords" content="{{ $seo['keywords'] }}">
@endif
<meta name="robots" content="index, follow">
<meta name="geo.region" content="{{ $seo['geo_region'] }}">
<meta name="geo.placename" content="{{ $seo['geo_place_name'] }}">
<link rel="canonical" href="{{ $seo['canonical_url'] }}">

<meta property="og:type" content="{{ $seo['og_type'] }}">
<meta property="og:site_name" content="TipTap">
<meta property="og:title" content="{{ $seo['title'] }}">
<meta property="og:description" content="{{ $seo['description'] }}">
<meta property="og:url" content="{{ $seo['canonical_url'] }}">
<meta property="og:image" content="{{ $seo['og_image'] }}">
<meta property="og:locale" content="{{ $seo['locale'] }}">

<meta name="twitter:card" content="{{ $seo['twitter_card'] }}">
<meta name="twitter:title" content="{{ $seo['title'] }}">
<meta name="twitter:description" content="{{ $seo['description'] }}">
<meta name="twitter:image" content="{{ $seo['og_image'] }}">

<script type="application/ld+json">@json($seo['structured_data'])</script>
