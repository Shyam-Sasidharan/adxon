<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $pageTitle ?? $seo['title'] }}</title>
<meta name="description" content="{{ $pageDescription ?? $seo['description'] }}">
<meta name="keywords" content="{{ $seo['keywords'] }}">
<meta property="og:title" content="{{ $pageTitle ?? $seo['title'] }}">
<meta property="og:description" content="{{ $pageDescription ?? $seo['description'] }}">
<meta property="og:type" content="website">
<link rel="icon" href="{{ asset('assets/brand/adxon-mark-dark.png') }}">
<link rel="stylesheet" href="{{ asset('assets/platform.css') }}">
<style>
    :root {
        font-family: {!! \App\Support\AdxonContent::FONT_FAMILIES[$settings['font_family']] ?? \App\Support\AdxonContent::FONT_FAMILIES['default'] !!};
        font-size: {{ max(12, min(24, (int) $settings['font_size'])) }}px;
        font-style: {{ $settings['font_style'] === 'italic' ? 'italic' : 'normal' }};
    }
</style>
<script src="{{ asset('assets/vendor/lucide.js') }}" defer></script>
<script src="{{ asset('assets/platform.js') }}" defer></script>
