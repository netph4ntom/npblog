<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ isset($title) ? $title . ' — Admin NPblog' : 'Admin NPblog' }}</title>

    @php
        $faviconUrl = \App\Models\Setting::getString('favicon');
    @endphp
    <x-pwa-meta :favicon-url="$faviconUrl" app-name="NPblog Admin" />

    <!-- Theme and sidebar collapsed state without flash -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.getElementById('pwa-theme-color')?.setAttribute('content', '#222520');
        } else {
            document.documentElement.classList.remove('dark');
            document.getElementById('pwa-theme-color')?.setAttribute('content', '#F6F6F3');
        }
        if (localStorage.getItem('adminSidebarCollapsed') === 'true') {
            document.documentElement.classList.add('admin-sidebar-collapsed');
        }
    </script>

    <!-- Preload key self-hosted fonts -->
    <link rel="preload" href="/fonts/caveat-700.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/patrick-hand-400.woff2" as="font" type="font/woff2" crossorigin>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    class="preload bg-bg text-text min-h-screen flex antialiased"
    x-data="{
        adminDrawerOpen: false,
        sidebarCollapsed: localStorage.getItem('adminSidebarCollapsed') === 'true',
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
            localStorage.setItem('adminSidebarCollapsed', this.sidebarCollapsed);
            document.documentElement.classList.toggle('admin-sidebar-collapsed', this.sidebarCollapsed);
        }
    }"
    x-effect="document.body.classList.toggle('overflow-hidden', adminDrawerOpen)"
>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            requestAnimationFrame(() => {
                document.body.classList.remove('preload');
            });
        });
    </script>

    <x-skip-link target="#main-content" />

    <!-- Desktop Sidebar (Fixed sticky h-screen with collapse toggle) -->
    <aside
        class="w-64 admin-desktop-sidebar bg-admin-sidebar text-gray-200 hidden lg:flex flex-col flex-shrink-0 sticky top-0 h-screen border-r border-ink z-30 transition-[width] duration-200 ease-in-out select-none"
        :class="sidebarCollapsed ? 'w-20' : 'w-64'"
    >
        <!-- Sidebar Brand & Toggle -->
        <div
            class="h-20 flex items-center border-b border-gray-800 sidebar-header"
            :class="sidebarCollapsed ? 'justify-center px-2' : 'justify-between px-6'"
        >
            <a href="{{ url('/dashboard') }}" x-show="!sidebarCollapsed" x-cloak class="sidebar-brand flex items-center space-x-2 overflow-hidden focus-visible:outline-none">
                <span class="font-heading text-3xl font-bold tracking-tight text-white whitespace-nowrap">
                    NPblog Admin
                </span>
            </a>

            <button
                type="button"
                @click="toggleSidebar()"
                class="hidden lg:flex items-center justify-center w-9 h-9 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 border border-transparent hover:border-gray-700 transition-colors focus-visible:outline-none"
                :aria-label="sidebarCollapsed ? 'Perluas sidebar' : 'Kecilkan sidebar'"
                :title="sidebarCollapsed ? 'Perluas sidebar' : 'Kecilkan sidebar'"
            >
                <svg
                    class="w-5 h-5 transition-transform duration-200"
                    :class="sidebarCollapsed ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 px-3 py-6 space-y-1.5 font-ui text-lg overflow-y-auto">
            <a
                href="{{ url('/dashboard') }}"
                class="sidebar-link flex items-center rounded-lg transition-colors min-h-[44px] {{ request()->is('dashboard') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'px-3 py-2.5'"
                :title="sidebarCollapsed ? 'Dashboard' : ''"
                aria-label="Dashboard"
                {{ request()->is('dashboard') ? 'aria-current="page"' : '' }}
            >
                <svg class="sidebar-icon w-5 h-5 flex-shrink-0 stroke-current" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="sidebar-label whitespace-nowrap">Dashboard</span>
            </a>

            <a
                href="{{ url('/admin/articles') }}"
                class="sidebar-link flex items-center rounded-lg transition-colors min-h-[44px] {{ request()->is('admin/articles*') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'px-3 py-2.5'"
                :title="sidebarCollapsed ? 'Articles' : ''"
                aria-label="Articles"
                {{ request()->is('admin/articles*') ? 'aria-current="page"' : '' }}
            >
                <svg class="sidebar-icon w-5 h-5 flex-shrink-0 stroke-current" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="sidebar-label whitespace-nowrap">Articles</span>
            </a>

            <a
                href="{{ url('/admin/categories') }}"
                class="sidebar-link flex items-center rounded-lg transition-colors min-h-[44px] {{ request()->is('admin/categories*') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'px-3 py-2.5'"
                :title="sidebarCollapsed ? 'Categories' : ''"
                aria-label="Categories"
                {{ request()->is('admin/categories*') ? 'aria-current="page"' : '' }}
            >
                <svg class="sidebar-icon w-5 h-5 flex-shrink-0 stroke-current" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="sidebar-label whitespace-nowrap">Categories</span>
            </a>

            <a
                href="{{ url('/admin/media') }}"
                class="sidebar-link flex items-center rounded-lg transition-colors min-h-[44px] {{ request()->is('admin/media*') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'px-3 py-2.5'"
                :title="sidebarCollapsed ? 'Media' : ''"
                aria-label="Media"
                {{ request()->is('admin/media*') ? 'aria-current="page"' : '' }}
            >
                <svg class="sidebar-icon w-5 h-5 flex-shrink-0 stroke-current" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                    <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="sidebar-label whitespace-nowrap">Media</span>
            </a>

            <a
                href="{{ url('/admin/settings') }}"
                class="sidebar-link flex items-center rounded-lg transition-colors min-h-[44px] {{ request()->is('admin/settings*') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                :class="sidebarCollapsed ? 'justify-center px-2 py-2.5' : 'px-3 py-2.5'"
                :title="sidebarCollapsed ? 'Settings' : ''"
                aria-label="Settings"
                {{ request()->is('admin/settings*') ? 'aria-current="page"' : '' }}
            >
                <svg class="sidebar-icon w-5 h-5 flex-shrink-0 stroke-current" :class="sidebarCollapsed ? 'mr-0' : 'mr-3'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span x-show="!sidebarCollapsed" x-cloak class="sidebar-label whitespace-nowrap">Settings</span>
            </a>

        </nav>

        <!-- Sidebar User Dropdown Menu -->
        <x-admin-user-menu />
    </aside>

    <!-- Mobile Drawer -->
    <div
        x-show="adminDrawerOpen"
        x-cloak
        class="fixed inset-0 z-50 lg:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="Admin Navigation Drawer"
    >
        <!-- Backdrop -->
        <div
            x-show="adminDrawerOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-ink/50 backdrop-blur-sm"
            @click="adminDrawerOpen = false"
        ></div>

        <!-- Drawer Content -->
        <div
            x-show="adminDrawerOpen"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 w-full max-w-xs bg-admin-sidebar text-gray-200 p-6 shadow-xl flex flex-col justify-between gap-4"
            @keydown.escape.window="adminDrawerOpen = false"
        >
            <div class="flex flex-col flex-1 min-h-0">
                <div class="flex items-center justify-between pb-6 border-b border-gray-800 shrink-0">
                    <span class="font-heading text-3xl font-bold text-white">NPblog Admin</span>
                    <button
                        type="button"
                        class="w-11 h-11 flex items-center justify-center rounded-lg text-gray-300 hover:text-white"
                        @click="adminDrawerOpen = false"
                        aria-label="Tutup menu"
                    >
                        <svg class="w-6 h-6 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <nav class="mt-6 flex-1 flex flex-col space-y-2 font-ui text-xl overflow-y-auto min-h-0">
                    <a href="{{ url('/dashboard') }}" class="px-3 py-2.5 rounded-lg min-h-[44px] flex items-center {{ request()->is('dashboard') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">Dashboard</a>
                    <a href="{{ url('/admin/articles') }}" class="px-3 py-2.5 rounded-lg min-h-[44px] flex items-center {{ request()->is('admin/articles*') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">Articles</a>
                    <a href="{{ url('/admin/categories') }}" class="px-3 py-2.5 rounded-lg min-h-[44px] flex items-center {{ request()->is('admin/categories*') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">Categories</a>
                    <a href="{{ url('/admin/media') }}" class="px-3 py-2.5 rounded-lg min-h-[44px] flex items-center {{ request()->is('admin/media*') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">Media</a>
                    <a href="{{ url('/admin/settings') }}" class="px-3 py-2.5 rounded-lg min-h-[44px] flex items-center {{ request()->is('admin/settings*') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">Settings</a>
                </nav>
            </div>

            <x-admin-user-menu :is-mobile="true" />
        </div>
    </div>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Admin Topbar -->
        <header class="h-20 bg-surface border-b border-border px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center space-x-4">
                <button
                    type="button"
                    class="lg:hidden inline-flex items-center justify-center w-11 h-11 p-2 rounded-lg font-ui text-text hover:bg-bg border-2 border-transparent hover:border-ink transition-all"
                    @click="adminDrawerOpen = true"
                    aria-label="Buka menu admin"
                >
                    <svg class="w-6 h-6 stroke-ink" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="4" y1="6" x2="20" y2="6"></line>
                        <line x1="4" y1="12" x2="20" y2="12"></line>
                        <line x1="4" y1="18" x2="20" y2="18"></line>
                    </svg>
                </button>

                @if (isset($header))
                    <div class="font-heading text-2xl sm:text-3xl font-bold text-ink">
                        {{ $header }}
                    </div>
                @endif
            </div>

            <!-- Topbar Actions -->
            <div class="flex items-center space-x-4">
                <a
                    href="{{ url('/') }}"
                    target="_blank"
                    class="hidden sm:inline-flex items-center px-3 py-1.5 font-ui text-base text-text-secondary hover:text-text border border-border hover:border-ink rounded-lg min-h-[44px] transition-colors"
                >
                    Lihat Blog
                    <svg class="w-4 h-4 ml-1.5 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                </a>

                <x-theme-toggle />
            </div>
        </header>

        <!-- Main Body -->
        <main id="main-content" class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            {{ $slot }}
        </main>
    </div>

    <!-- Global Toast & Confirm Dialog -->
    <x-toast />
    <x-confirm-dialog />
</body>
</html>

