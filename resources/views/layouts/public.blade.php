@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'article' => null,
])

@php
    $blogName = \App\Models\Setting::getString('blog_name', 'NPblog');
    $blogDescription = \App\Models\Setting::getString('blog_description', 'Blog personal minimalis bergaya hand-drawn oleh Dapit.');
    $defaultDescription = \App\Models\Setting::getString('seo_default_description', $blogDescription);
    $logoUrl = \App\Models\Setting::getString('logo');
    $heroImageUrl = \App\Models\Setting::getString('hero_image');
    $defaultOgImage = $heroImageUrl !== '' ? $heroImageUrl : ($logoUrl !== '' ? $logoUrl : url('/favicon.ico'));
    $authorName = \App\Models\Setting::getString('author_name', 'Dapit');
    $faviconUrl = \App\Models\Setting::getString('favicon');
    $socialLinks = \App\Models\Setting::safeSocialLinks();
    $contactEmail = \App\Models\Setting::getString('contact_email');

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
        $pageTitle = $title ? ($title . ' — ' . $blogName) : \App\Models\Setting::getString('seo_default_title', $blogName);
        $metaDescription = $description ?? $defaultDescription;
        $canonicalUrl = $canonical ?? url()->current();
        $ogType = 'website';
        $ogTitle = $title ?? $blogName;
        $ogDescription = $metaDescription;
        $ogUrl = $canonicalUrl;
        $ogImage = $defaultOgImage;
    }
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
        <meta property="article:author" content="{{ $article->user->name ?? $authorName }}">
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
<body class="preload bg-bg text-text min-h-screen flex flex-col paper-texture antialiased selection:bg-accent/30" x-data="{ mobileMenuOpen: false }" x-effect="document.body.classList.toggle('overflow-hidden', mobileMenuOpen)">
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            requestAnimationFrame(() => {
                document.body.classList.remove('preload');
            });
        });
    </script>
    <x-skip-link target="#main-content" />

    <!-- Public Header -->
    <header
        x-data="{ isScrolled: false }"
        x-init="isScrolled = window.scrollY > 20"
        @scroll.window.passive="isScrolled = window.scrollY > 20"
        class="w-full sticky top-0 z-40 bg-transparent border-b border-transparent transition-colors duration-200"
        :class="isScrolled
            ? 'bg-bg/95 backdrop-blur-md border-border'
            : 'bg-transparent border-transparent'"
    >
        <div class="max-w-container mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-3">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="group flex items-center space-x-2.5 min-w-0 flex-1 md:flex-initial text-decoration-none focus-visible:outline-none">
                @if($logoUrl !== '')
                    <img src="{{ $logoUrl }}" alt="{{ $blogName }}" class="h-8 sm:h-9 w-auto max-w-[100px] sm:max-w-[140px] object-contain shrink-0" />
                @endif
                <span class="font-heading text-3xl sm:text-4xl font-bold tracking-tight text-ink group-hover:text-accent-dark transition-colors truncate">
                    {{ $blogName }}<span class="text-accent text-4xl sm:text-5xl leading-none">.</span>
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
            </nav>

            <!-- Action Controls -->
            <div class="flex items-center space-x-2 sm:space-x-3 shrink-0">
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
            class="fixed inset-0 bg-ink/50 backdrop-blur-sm"
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
            class="fixed inset-y-0 right-0 w-full max-w-xs bg-surface p-6 shadow-xl border-l-2 border-ink flex flex-col justify-between overflow-y-auto gap-6"
            @keydown.escape.window="mobileMenuOpen = false"
        >
            <div>
                <div class="flex items-center justify-between pb-6 border-b border-border">
                    <span class="font-heading text-3xl font-bold text-ink">{{ $blogName }}</span>
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
                </nav>
            </div>

            <div class="pt-6 border-t border-border text-center font-ui text-sm text-text-secondary">
                <p>Personal blog by {{ $authorName }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <main id="main-content" class="flex-1 max-w-container w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    <!-- Public Footer -->
    <footer class="w-full border-t border-border mt-auto bg-surface/50">
        <div class="max-w-container mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col items-center justify-center text-center text-sm text-text-secondary font-ui gap-2 relative">
            @if(!empty($socialLinks) || $contactEmail !== '')
                <div class="flex flex-wrap items-center justify-center gap-4">
                    @if($contactEmail !== '')
                        <a href="mailto:{{ $contactEmail }}" class="hover:text-accent-dark transition-colors min-h-[44px] inline-flex items-center">
                            Email
                        </a>
                    @endif
                    @foreach($socialLinks as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-accent-dark transition-colors min-h-[44px] inline-flex items-center">
                            {{ $social['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif
            <p>
                &copy; {{ date('Y') }} {{ $authorName }} &bull; {{ $blogName }}. Hand-drawn minimalism.
            </p>
            @auth
                <div class="mt-2 sm:mt-0 sm:absolute sm:right-6 lg:right-8 flex items-center">
                    <a href="{{ url('/dashboard') }}" class="hover:text-accent-dark transition-colors min-h-[44px] inline-flex items-center font-bold">Admin</a>
                </div>
            @endauth
        </div>
    </footer>

    <!-- Global Toast Notifications -->
    <x-toast />
</body>
</html>

