@props([
    'faviconUrl' => '',
    'appName' => null,
])

@php
    $resolvedAppName = $appName ?: \App\Models\Setting::getString('blog_name', 'NPblog');
@endphp

<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" id="pwa-theme-color" content="#F6F6F3">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $resolvedAppName }}">
<link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">

@if(!empty($faviconUrl))
    <link rel="icon" href="{{ $faviconUrl }}">
@else
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
@endif

