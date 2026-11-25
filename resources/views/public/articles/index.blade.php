<x-public-layout title="Artikel">
    <div class="space-y-10 py-8">
        <!-- Header & Search Form -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-6 border-b border-border">
            <div>
                <h1 class="font-heading text-4xl sm:text-5xl font-bold text-ink">Semua Artikel</h1>
                <p class="font-ui text-lg text-text-secondary mt-1">Kumpulan tulisan seputar teknologi, keamanan, dan rekayasa perangkat lunak.</p>
            </div>

            <!-- Search Bar -->
            <form action="{{ route('articles.index') }}" method="GET" class="flex items-center gap-2 max-w-md w-full">
                @if($categorySlug)
                    <input type="hidden" name="category" value="{{ $categorySlug }}">
                @endif
                <div class="relative flex-1">
                    <x-text-input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari judul atau isi artikel..."
                        class="w-full pl-10 pr-4"
                    />
                    <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <x-button type="submit" variant="secondary">Cari</x-button>
            </form>
        </div>

        <!-- Category Dropdown Filter -->
        @php
            $activeCategory = $categories->firstWhere('slug', $categorySlug);
            $activeCategoryLabel = $activeCategory
                ? $activeCategory->name . ' (' . $activeCategory->articles_count . ')'
                : 'Semua Kategori';
        @endphp

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div
                x-data="{ open: false }"
                @click.outside="open = false"
                @keydown.escape.window="open = false"
                class="relative inline-block text-left font-ui"
            >
                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex items-center justify-between gap-3 px-4 py-2.5 min-h-[44px] min-w-[220px] bg-surface text-text hover:bg-bg border-2 border-ink sketch-radius-b sketch-shadow-sm transition-all focus-visible:outline-none"
                    :aria-expanded="open.toString()"
                    aria-haspopup="true"
                    aria-label="Filter kategori artikel"
                >
                    <span class="inline-flex items-center gap-2 text-base">
                        <svg class="w-4 h-4 text-accent-dark stroke-current shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span class="text-text-secondary">Kategori:</span>
                        <span class="font-bold text-ink">{{ $activeCategoryLabel }}</span>
                    </span>

                    <svg
                        class="w-4 h-4 text-text-secondary transition-transform duration-150 shrink-0"
                        :class="open ? 'rotate-180' : ''"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown Panel -->
                <div
                    x-show="open"
                    x-cloak
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
                    class="absolute left-0 mt-2 w-64 bg-surface border-2 border-ink sketch-radius-b sketch-shadow py-1.5 z-30 overflow-hidden"
                    role="menu"
                    aria-label="Daftar kategori"
                >
                    <a
                        href="{{ route('articles.index', request()->only('q')) }}"
                        @click="open = false"
                        role="menuitem"
                        class="flex items-center justify-between px-4 py-2.5 min-h-[44px] text-base transition-colors {{ !$categorySlug ? 'bg-accent-dark text-on-accent font-bold' : 'text-text hover:bg-bg hover:text-ink' }}"
                    >
                        <span>Semua Kategori</span>
                        @if(!$categorySlug)
                            <svg class="w-4 h-4 stroke-current shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </a>

                    <div class="my-1 border-t border-border"></div>

                    @foreach($categories as $cat)
                        <a
                            href="{{ route('articles.index', ['category' => $cat->slug] + request()->only('q')) }}"
                            @click="open = false"
                            role="menuitem"
                            class="flex items-center justify-between px-4 py-2.5 min-h-[44px] text-base transition-colors {{ $categorySlug === $cat->slug ? 'bg-accent-dark text-on-accent font-bold' : 'text-text hover:bg-bg hover:text-ink' }}"
                        >
                            <span class="truncate">{{ $cat->name }}</span>
                            <span class="ml-2 text-xs font-mono px-2 py-0.5 rounded {{ $categorySlug === $cat->slug ? 'bg-on-accent/15 text-on-accent' : 'bg-bg text-text-secondary border border-border' }}">
                                {{ $cat->articles_count }}
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>

            @if($categorySlug || request('q'))
                <a
                    href="{{ route('articles.index') }}"
                    class="inline-flex items-center gap-1.5 font-ui text-sm font-bold text-accent-dark hover:underline min-h-[44px] px-2"
                >
                    <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Reset Filter
                </a>
            @endif
        </div>

        <!-- Articles Grid -->
        @if($articles->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                @foreach($articles as $art)
                    <x-article-card :article="$art" variant="grid" :index="$loop->index" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-8">
                {{ $articles->links() }}
            </div>
        @else
            <x-empty-state
                title="Tidak Ada Artikel Ditemukan"
                description="Tidak ada artikel yang cocok dengan pencarian atau kategori yang dipilih."
                action-label="Reset Filter"
                :action-url="route('articles.index')"
            />
        @endif
    </div>
</x-public-layout>
