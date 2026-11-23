<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ config('app.name', 'NPblog') }}</title>

    <!-- Theme detector without flash -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Preload key self-hosted fonts -->
    <link rel="preload" href="/fonts/caveat-700.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/patrick-hand-400.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg text-text min-h-screen flex flex-col justify-center items-center p-4 paper-texture antialiased">
    <div class="w-full max-w-md flex flex-col items-center mb-6">
        <a href="{{ url('/') }}" class="font-heading text-4xl font-bold tracking-tight text-ink hover:text-accent-dark transition-colors mb-2">
            NPblog<span class="text-accent text-5xl leading-none">.</span>
        </a>
    </div>

    <div class="w-full max-w-md">
        <x-sketch-box variant="a" class="p-6 sm:p-8">
            {{ $slot }}
        </x-sketch-box>
    </div>

    <div class="mt-8">
        <x-theme-toggle />
    </div>

    <x-toast />
</body>
</html>
