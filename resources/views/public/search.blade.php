<x-public-layout title="Pencarian">
    <x-slot:meta>
        <meta name="robots" content="noindex, follow">
    </x-slot:meta>

    <div class="max-w-[740px] mx-auto py-8 sm:py-12 space-y-8">
        <!-- Search Header -->
        <header class="space-y-4">
            <h1 class="font-heading text-4xl sm:text-5xl font-bold text-ink">Pencarian Artikel</h1>
            <p class="font-ui text-lg text-text-secondary">Temukan catatan teknis dan artikel berdasarkan kata kunci.</p>

            <form action="{{ route('search') }}" method="GET" class="flex gap-2 pt-2">
                <div class="relative flex-1">
                    <x-text-input
                        type="search"
                        name="q"
                        value="{{ $searchTerm }}"
                        placeholder="Ketik kata kunci pencarian..."
                        class="w-full pl-10 pr-4"
                        autofocus
                    />
                    <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-text-secondary pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <x-button type="submit" variant="primary">Cari</x-button>
            </form>
        </header>

        <!-- Results Section -->
        @if($searchTerm !== '')
            <div class="space-y-6 pt-4 border-t border-border">
                @if($articles && $articles->isNotEmpty())
                    <p class="font-ui text-sm text-text-secondary">
                        Menampilkan hasil pencarian untuk "<strong class="text-text">{{ $searchTerm }}</strong>" ({{ $articles->total() }} hasil):
                    </p>

                    <div class="space-y-4">
                        @foreach($articles as $art)
                            <x-article-card :article="$art" variant="compact" :index="$loop->index" />
                        @endforeach
                    </div>

                    <div class="pt-6">
                        {{ $articles->links() }}
                    </div>
                @else
                    <x-empty-state
                        title="Tidak Ditemukan"
                        description="Tidak ada artikel yang cocok dengan kata kunci '{{ $searchTerm }}'."
                    />
                @endif
            </div>
        @else
            <div class="p-8 text-center bg-surface border-2 border-border sketch-radius-c text-text-secondary font-ui text-lg">
                Silakan masukkan kata kunci pada form di atas untuk memulai pencarian.
            </div>
        @endif
    </div>
</x-public-layout>
