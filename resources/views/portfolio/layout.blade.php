<!DOCTYPE html>
<html lang="{{ $locale->value }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#080D0C">
    <title>{{ $pageTitle ?? $site['name'] ?? 'Portfolio' }}</title>
    <meta name="description" content="{{ $pageDescription ?? $site['seo_description'] ?? '' }}">
    <meta property="og:title" content="{{ $pageTitle ?? $site['name'] ?? 'Portfolio' }}">
    <meta property="og:description" content="{{ $pageDescription ?? __('portfolio.og_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $locale->publicUrl($pageSlug ?? null) }}">
    <link rel="canonical" href="{{ $locale->publicUrl($pageSlug ?? null) }}">
    @foreach (\App\Enums\PortfolioLocale::cases() as $language)
        <link rel="alternate" hreflang="{{ $language->value }}" href="{{ $language->publicUrl($pageSlug ?? null) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ \App\Enums\PortfolioLocale::Uzbek->publicUrl($pageSlug ?? null) }}">
    <script>
        try {
            if (localStorage.getItem('portfolio-theme') === 'light') {
                document.documentElement.dataset.theme = 'light';
            }
        } catch {
            // Use the default theme when storage is unavailable.
        }
        if ('IntersectionObserver' in window) document.documentElement.classList.add('motion-enabled');
    </script>
    @vite(['resources/css/portfolio.css', 'resources/js/portfolio.js'])
    @if ($site['name'] ?? null)
        <script type="application/ld+json">{!! \Illuminate\Support\Js::encode([
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $site['name'],
            'jobTitle' => __('portfolio.job'),
            'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'UZ'],
            'url' => $locale->publicUrl(),
        ]) !!}</script>
    @endif
</head>
<body>
    @yield('content')
</body>
</html>

