<x-public-layout title="Beranda">
    <div class="space-y-16 sm:space-y-24 py-8 sm:py-12">
        <!-- Hero Section -->
        <section class="text-center max-w-3xl mx-auto space-y-6">
            <h1 class="font-heading text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-ink leading-tight">
                Catatan, Kode, & <span class="marker-highlight">Arsitektur</span>
            </h1>
            <p class="font-ui text-xl sm:text-2xl text-text-secondary leading-relaxed max-w-2xl mx-auto">
                {{ $blogTagline }}
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4 pt-4">
                <x-button href="{{ route('articles.index') }}" variant="primary">
                    Mulai Membaca
                </x-button>
                <x-button href="{{ route('about') }}" variant="secondary">
                    Tentang Penulis
                </x-button>
            </div>
        </section>

        <!-- Featured Article Section -->
        @if($featured)
            <section class="space-y-6">
                <div class="flex items-center space-x-3">
                    <span class="font-ui text-sm font-bold uppercase tracking-wider text-accent-dark px-2.5 py-1 bg-accent/20 rounded-md border border-ink">Pilihan</span>
                    <h2 class="font-heading text-3xl sm:text-4xl font-bold text-ink">Artikel Unggulan</h2>
                </div>

                <x-article-card :article="$featured" variant="featured" />
            </section>
        @endif

        <!-- Latest Articles Section -->
        <section class="space-y-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <h2 class="font-heading text-3xl sm:text-4xl font-bold text-ink">Tulisan Terbaru</h2>
                </div>

                <a href="{{ route('articles.index') }}" class="font-ui text-lg font-bold text-accent-dark hover:underline inline-flex items-center">
                    Lihat Semua
                    <svg class="w-5 h-5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>

            @if($latest->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($latest as $art)
                        <x-article-card :article="$art" variant="grid" :index="$loop->index" />
                    @endforeach
                </div>
            @else
                <x-empty-state
                    title="Belum Ada Tulisan"
                    description="Belum ada artikel terbaru yang dipublikasikan saat ini."
                />
            @endif
        </section>

        <!-- About Author Preview Section -->
        <section class="pt-8">
            <x-sketch-box variant="b" class="p-8 sm:p-10 flex flex-col sm:flex-row items-center gap-6 sm:gap-8 bg-surface">
                <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full border-2 border-ink flex items-center justify-center bg-accent/20 text-ink font-heading text-4xl sm:text-5xl font-bold shrink-0">
                    {{ substr($authorName, 0, 1) }}
                </div>
                <div class="space-y-3 text-center sm:text-left flex-1">
                    <h2 class="font-heading text-3xl font-bold text-ink">Halo, Saya {{ $authorName }}</h2>
                    <p class="font-body text-text-secondary text-lg leading-relaxed">
                        {{ $authorBio }}
                    </p>
                    <div class="pt-2">
                        <a href="{{ route('about') }}" class="inline-flex items-center font-ui text-lg font-bold text-accent-dark hover:underline">
                            Selengkapnya tentang saya &rarr;
                        </a>
                    </div>
                </div>
            </x-sketch-box>
        </section>
    </div>
</x-public-layout>
