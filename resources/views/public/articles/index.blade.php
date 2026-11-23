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

        <!-- Category Chips Filter -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
            <a href="{{ route('articles.index', request()->only('q')) }}">
                <x-chip :variant="!$categorySlug ? 'accent' : 'default'">
                    Semua
                </x-chip>
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('articles.index', ['category' => $cat->slug] + request()->only('q')) }}">
                    <x-chip :variant="$categorySlug === $cat->slug ? 'accent' : 'default'">
                        {{ $cat->name }} ({{ $cat->articles_count }})
                    </x-chip>
                </a>
            @endforeach
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
