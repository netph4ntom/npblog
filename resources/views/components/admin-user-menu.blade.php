@props([
    'isMobile' => false,
])

@php
    $userName = Auth::user()->name ?? 'Dapit';
    $userEmail = Auth::user()->email ?? 'admin@npblog.local';
    $userInitial = strtoupper(substr($userName, 0, 1));
@endphp

<div
    x-data="{ userMenuOpen: false }"
    @click.outside="userMenuOpen = false"
    @keydown.escape.window="userMenuOpen = false"
    class="relative shrink-0 border-t border-gray-800 font-ui text-sm select-none"
>
    <!-- Trigger Button -->
    <button
        type="button"
        @click="userMenuOpen = !userMenuOpen"
        class="w-full p-3 flex items-center transition-colors hover:bg-gray-800/80 focus-visible:outline-none text-left group min-h-[44px] {{ $isMobile ? 'justify-between rounded-lg' : 'sidebar-footer' }}"
        @if(!$isMobile)
            :class="sidebarCollapsed ? 'justify-center' : 'justify-between'"
            :title="sidebarCollapsed ? '{{ $userName }} (Menu Akun)' : ''"
        @endif
        :aria-expanded="userMenuOpen"
        aria-haspopup="true"
    >
        <div class="flex items-center space-x-3 min-w-0">
            <!-- User Avatar Initial -->
            <div class="w-9 h-9 rounded-full bg-accent/25 border border-accent/50 text-white font-bold flex items-center justify-center shrink-0 text-base">
                {{ $userInitial }}
            </div>

            <!-- User Info -->
            <div
                @if(!$isMobile) x-show="!sidebarCollapsed" x-cloak class="truncate min-w-0 sidebar-user" @else class="truncate min-w-0" @endif
            >
                <p class="font-bold text-white text-base truncate group-hover:text-gray-200 transition-colors">{{ $userName }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $userEmail }}</p>
            </div>
        </div>

        <!-- Chevron Icon -->
        <svg
            @if(!$isMobile) x-show="!sidebarCollapsed" x-cloak @endif
            class="w-4 h-4 text-gray-400 group-hover:text-white transition-transform duration-200 shrink-0 ml-1"
            :class="userMenuOpen ? 'rotate-180' : ''"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
        >
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
        </svg>
    </button>

    <!-- Dropdown Popover (Opens Upwards) -->
    <div
        x-show="userMenuOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute bottom-full mb-2 admin-user-popover bg-[#262825] border-2 border-ink text-white rounded-xl shadow-2xl py-2 z-50 overflow-hidden font-ui {{ $isMobile ? 'left-0 right-0' : '' }}"
        @if(!$isMobile)
            :class="sidebarCollapsed ? 'left-2 w-56' : 'left-3 right-3'"
        @endif
        style="bottom: calc(100% + 0.5rem);"
    >
        <!-- Popover Header -->
        <div class="px-4 py-2 border-b border-gray-700/60">
            <p class="text-xs uppercase tracking-wider text-gray-400 font-bold">Akun Administrator</p>
            <p class="font-bold text-white text-base truncate mt-0.5">{{ $userName }}</p>
            <p class="text-xs text-gray-300 truncate">{{ $userEmail }}</p>
        </div>

        <!-- Popover Links -->
        <div class="py-1">
            <a
                href="{{ url('/profile') }}"
                @if($isMobile) @click="userMenuOpen = false; adminDrawerOpen = false" @endif
                class="flex items-center px-4 py-2.5 min-h-[44px] text-base transition-colors {{ request()->is('profile*') ? 'bg-accent-dark text-on-accent font-bold' : 'text-gray-200 hover:text-white hover:bg-accent-dark/40' }}"
            >
                <svg class="w-5 h-5 mr-3 stroke-current {{ request()->is('profile*') ? 'text-on-accent' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Profile Setting
            </a>
        </div>

        <div class="border-t border-gray-700/60 pt-1">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full flex items-center px-4 py-2.5 min-h-[44px] text-base text-red-400 hover:text-red-300 hover:bg-red-950/30 transition-colors text-left"
                >
                    <svg class="w-5 h-5 mr-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar (Logout)
                </button>
            </form>
        </div>
    </div>
</div>

