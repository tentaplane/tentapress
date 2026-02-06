@php
    $manager = app(\TentaPress\Seo\Services\SeoManager::class);
    if (! isset($seo) || ! is_array($seo)) {
        if (isset($post) && is_object($post)) {
            $seo = $manager->forPost($post);
        } elseif (isset($page) && is_object($page)) {
            $seo = $manager->forPage($page);
        } else {
            $seo = $manager->forBlogIndex();
        }
    }
@endphp

@if (!empty($seo['title']))
    <title>{{ $seo['title'] }}</title>
@endif

@if (!empty($seo['description']))
    <meta name="description" content="{{ $seo['description'] }}" />
@endif

@if (!empty($seo['robots']))
    <meta name="robots" content="{{ $seo['robots'] }}" />
@endif

@if (!empty($seo['canonical']))
    <link rel="canonical" href="{{ $seo['canonical'] }}" />
@endif

@if (!empty($seo['og:title']))
    <meta property="og:title" content="{{ $seo['og:title'] }}" />
@endif

@if (!empty($seo['og:description']))
    <meta property="og:description" content="{{ $seo['og:description'] }}" />
@endif

@if (!empty($seo['og:image']))
    <meta property="og:image" content="{{ $seo['og:image'] }}" />
@endif

@if (!empty($seo['twitter:title']))
    <meta name="twitter:title" content="{{ $seo['twitter:title'] }}" />
@endif

@if (!empty($seo['twitter:description']))
    <meta name="twitter:description" content="{{ $seo['twitter:description'] }}" />
@endif

@if (!empty($seo['twitter:image']))
    <meta name="twitter:image" content="{{ $seo['twitter:image'] }}" />
@endif
