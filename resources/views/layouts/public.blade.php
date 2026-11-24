@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'article' => null,
])

@php
    $blogName = \App\Models\Setting::get('blog_name', config('app.name', 'NPblog'));
    $defaultDescription = \App\Models\Setting::get('seo_default_description', \App\Models\Setting::get('blog_description', 'Blog personal minimalis bergaya hand-drawn oleh Dapit.'));
    $defaultOgImage = \App\Models\Setting::get('hero_image') ?: (\App\Models\Setting::get('logo') ?: url('/favicon.ico'));

    if ($article instanceof \App\Models\Article) {
        $pageTitle = $article->meta_title ?: ($article->title . ' — ' . $blogName);
        $metaDescription = $article->meta_description ?: ($article->excerpt ?: $defaultDescription);
        $canonicalUrl = route('articles.show', $article->slug);
        $ogType = 'article';
        $ogTitle = $article->meta_title ?: $article->title;
        $ogDescription = $article->meta_description ?: ($article->excerpt ?: $defaultDescription);
        $ogUrl = $canonicalUrl;
        $ogImage = $article->featuredMedia ? $article->featuredMedia->url : $defaultOgImage;
    } else {
        $pageTitle = $title ? ($title . ' — ' . $blogName) : (\App\Models\Setting::get('seo_default_title') ?: $blogName);
        $metaDescription = $description ?? $defaultDescription;
        $canonicalUrl = $canonical ?? url()->current();
        $ogType = 'website';
        $ogTitle = $title ?? $blogName;
        $ogDescription = $metaDescription;
        $ogUrl = $canonicalUrl;
        $ogImage = $defaultOgImage;
    }

    $faviconUrl = \App\Models\Setting::get('favicon');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">

    @if(!empty($faviconUrl))
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ $blogName }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:url" content="{{ $ogUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="id_ID">

    @if($article instanceof \App\Models\Article)
        @if($article->published_at)
            <meta property="article:published_time" content="{{ $article->published_at->toIso8601String() }}">
        @endif
        <meta property="article:author" content="{{ $article->user->name ?? \App\Models\Setting::get('author_name', 'Dapit') }}">
        @if($article->category)
            <meta property="article:section" content="{{ $article->category->name }}">
        @endif
        @foreach($article->tags as $t)
            <meta property="article:tag" content="{{ $t->name }}">
        @endforeach
    @endif

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{ $meta ?? '' }}

    <!-- Theme detector without flash -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <!-- Preload key self-hosted fonts (max 2 per page) -->
    <link rel="preload" href="/fonts/caveat-700.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/dm-sans-400.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-bg text-text min-h-screen flex flex-col paper-texture antialiased selection:bg-accent/30" x-data="{ mobileMenuOpen: false }">
    <x-skip-link target="#main-content" />

    <!-- Public Header -->
    <header
        x-data="{ isScrolled: false }"
        x-init="isScrolled = window.scrollY > 20"
        @scroll.window.passive="isScrolled = window.scrollY > 20"
        class="w-full sticky top-0 z-40 transition-all duration-300"
        :class="isScrolled
            ? 'bg-bg/95 backdrop-blur-md border-b border-border'
            : 'bg-transparent border-b border-transparent'"
    >
        <div class="max-w-container mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="group flex items-center space-x-2 text-decoration-none focus-visible:outline-none">
                <span class="font-heading text-4xl font-bold tracking-tight text-ink group-hover:text-accent-dark transition-colors">
                    NPblog<span class="text-accent text-5xl leading-none">.</span>
                </span>
            </a>

            <!-- Desktop Nav -->
            <nav class="hidden md:flex items-center space-x-8 font-ui text-xl">
                <a
                    href="{{ url('/') }}"
                    class="transition-colors hover:text-accent-dark min-h-[44px] inline-flex items-center {{ request()->is('/') ? 'text-accent-dark font-bold border-b-2 border-ink' : 'text-text-secondary' }}"
                    {{ request()->is('/') ? 'aria-current="page"' : '' }}
                >
                    Home
                </a>
                <a
                    href="{{ url('/articles') }}"
                    class="transition-colors hover:text-accent-dark min-h-[44px] inline-flex items-center {{ request()->is('articles*') ? 'text-accent-dark font-bold border-b-2 border-ink' : 'text-text-secondary' }}"
                    {{ request()->is('articles*') ? 'aria-current="page"' : '' }}
                >
                    Articles
                </a>
                <a
                    href="{{ url('/about') }}"
                    class="transition-colors hover:text-accent-dark min-h-[44px] inline-flex items-center {{ request()->is('about') ? 'text-accent-dark font-bold border-b-2 border-ink' : 'text-text-secondary' }}"
                    {{ request()->is('about') ? 'aria-current="page"' : '' }}
                >
                    About
                </a>
                <a
                    href="{{ url('/search') }}"
                    class="transition-colors hover:text-accent-dark min-h-[44px] inline-flex items-center {{ request()->is('search') ? 'text-accent-dark font-bold border-b-2 border-ink' : 'text-text-secondary' }}"
                    {{ request()->is('search') ? 'aria-current="page"' : '' }}
                    aria-label="Pencarian"
                >
                    <svg class="w-5 h-5 mr-1 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    Search
                </a>
            </nav>

            <!-- Action Controls -->
            <div class="flex items-center space-x-3">
                <x-theme-toggle />

                <!-- Mobile Menu Button -->
                <button
                    type="button"
                    class="md:hidden inline-flex items-center justify-center w-11 h-11 p-2 rounded-lg font-ui text-text hover:bg-surface border-2 border-transparent hover:border-ink transition-all sketch-shadow-hover"
                    @click="mobileMenuOpen = true"
                    aria-label="Buka menu navigasi"
                    :aria-expanded="mobileMenuOpen.toString()"
                >
                    <svg class="w-6 h-6 stroke-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="6" x2="20" y2="6"></line>
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                        <line x1="4" y1="18" x2="20" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Sheet / Drawer -->
    <div
        x-show="mobileMenuOpen"
        x-cloak
        class="fixed inset-0 z-50 md:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Menu navigasi mobile"
    >
        <!-- Backdrop -->
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-ink/50 backdrop-blur-xs"
            @click="mobileMenuOpen = false"
        ></div>

        <!-- Sheet Panel -->
        <div
            x-show="mobileMenuOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 w-full max-w-xs bg-surface p-6 shadow-xl border-l-2 border-ink flex flex-col justify-between"
            @keydown.escape.window="mobileMenuOpen = false"
        >
            <div>
                <div class="flex items-center justify-between pb-6 border-b border-border">
                    <span class="font-heading text-3xl font-bold text-ink">NPblog</span>
                    <button
                        type="button"
                        class="w-11 h-11 flex items-center justify-center rounded-lg border-2 border-transparent hover:border-ink"
                        @click="mobileMenuOpen = false"
                        aria-label="Tutup menu"
                    >
                        <svg class="w-6 h-6 stroke-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <nav class="mt-6 flex flex-col space-y-3 font-ui text-2xl">
                    <a
                        href="{{ url('/') }}"
                        class="px-4 py-3 rounded-lg min-h-[44px] flex items-center {{ request()->is('/') ? 'bg-bg font-bold border-2 border-ink' : 'text-text-secondary hover:text-text' }}"
                        {{ request()->is('/') ? 'aria-current="page"' : '' }}
                    >
                        Home
                    </a>
                    <a
                        href="{{ url('/articles') }}"
                        class="px-4 py-3 rounded-lg min-h-[44px] flex items-center {{ request()->is('articles*') ? 'bg-bg font-bold border-2 border-ink' : 'text-text-secondary' }}"
                        {{ request()->is('articles*') ? 'aria-current="page"' : '' }}
                    >
                        Articles
                    </a>
                    <a
                        href="{{ url('/about') }}"
                        class="px-4 py-3 rounded-lg min-h-[44px] flex items-center {{ request()->is('about') ? 'bg-bg font-bold border-2 border-ink' : 'text-text-secondary' }}"
                        {{ request()->is('about') ? 'aria-current="page"' : '' }}
                    >
                        About
                    </a>
                    <a
                        href="{{ url('/search') }}"
                        class="px-4 py-3 rounded-lg min-h-[44px] flex items-center {{ request()->is('search') ? 'bg-bg font-bold border-2 border-ink' : 'text-text-secondary' }}"
                        {{ request()->is('search') ? 'aria-current="page"' : '' }}
                    >
                        Search
                    </a>
                </nav>
            </div>

            <div class="pt-6 border-t border-border text-center font-ui text-sm text-text-secondary">
                <p>Personal blog by Dapit</p>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main id="main-content" class="flex-1 max-w-container w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    <!-- Public Footer -->
    <footer class="w-full border-t border-border mt-auto bg-surface/50">
        <div class="max-w-container mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row items-center justify-between text-sm text-text-secondary font-ui">
            <p class="mb-4 sm:mb-0">
                &copy; {{ date('Y') }} Dapit. Hand-drawn minimalism.
            </p>
            @auth
                <div class="flex items-center space-x-6">
                    <a href="{{ url('/dashboard') }}" class="hover:text-accent-dark transition-colors min-h-[44px] inline-flex items-center font-bold">Admin</a>
                </div>
            @endauth
        </div>
    </footer>

    <!-- Global Toast Notifications -->
    <x-toast />
</body>
</html>

