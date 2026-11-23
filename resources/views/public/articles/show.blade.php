<x-public-layout :title="$article->title">
    <div class="max-w-[740px] mx-auto py-8 sm:py-12 space-y-8">
        <!-- Breadcrumb / Back Link -->
        <div class="flex items-center justify-between text-sm font-ui">
            <a href="{{ route('articles.index') }}" class="inline-flex items-center text-text-secondary hover:text-accent-dark">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Artikel
            </a>

            @if($article->category)
                <x-chip variant="accent">
                    {{ $article->category->name }}
                </x-chip>
            @endif
        </div>

        <!-- Article Header -->
        <header class="space-y-4">
            <h1 class="font-heading text-4xl sm:text-5xl lg:text-6xl font-bold text-ink leading-tight">
                {{ $article->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 text-sm font-ui text-text-secondary pt-2 border-b border-border pb-6">
                <span>Oleh <strong class="text-text font-semibold">{{ $article->user->name ?? 'Dapit' }}</strong></span>
                <span>•</span>
                <time datetime="{{ $article->published_at?->toISOString() }}">
                    {{ $article->published_at?->translatedFormat('d F Y') }}
                </time>
                <span>•</span>
                <span class="inline-flex items-center">
                    <svg class="w-4 h-4 mr-1 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    {{ $article->reading_time }} min baca
                </span>
                <span>•</span>
                <span>{{ $article->word_count }} kata</span>
            </div>
        </header>

        @if($article->featuredMedia)
            <figure class="my-6">
                <img
                    src="{{ $article->featuredMedia->url }}"
                    alt="{{ $article->featuredMedia->alt_text ?? $article->title }}"
                    width="{{ $article->featuredMedia->width }}"
                    height="{{ $article->featuredMedia->height }}"
                    class="w-full h-auto sketch-border sketch-radius-b sketch-shadow object-cover"
                />
                @if($article->featuredMedia->alt_text)
                    <figcaption class="mt-2 text-center text-sm font-ui text-text-secondary">
                        {{ $article->featuredMedia->alt_text }}
                    </figcaption>
                @endif
            </figure>
        @endif

        <!-- Article Body -->
        <article class="article-body font-body text-lg leading-relaxed text-text">
            {!! $article->body_html !!}
        </article>

        <!-- Article Tags -->
        @if($article->tags->isNotEmpty())
            <div class="pt-8 border-t border-border flex flex-wrap items-center gap-2">
                <span class="font-ui text-sm font-bold text-ink mr-2">Topik:</span>
                @foreach($article->tags as $tag)
                    <x-chip variant="default">
                        #{{ $tag->name }}
                    </x-chip>
                @endforeach
            </div>
        @endif

        <!-- Author Footer Card -->
        <div class="pt-6">
            <x-sketch-box variant="a" class="p-6 bg-surface flex items-center gap-5">
                <div class="w-16 h-16 rounded-full border-2 border-ink flex items-center justify-center bg-accent/20 text-ink font-heading text-3xl font-bold shrink-0">
                    {{ substr($article->user->name ?? 'D', 0, 1) }}
                </div>
                <div class="space-y-1">
                    <h3 class="font-heading text-2xl font-bold text-ink">{{ $article->user->name ?? 'Dapit' }}</h3>
                    <p class="font-ui text-sm text-text-secondary">Penulis di NPblog. Berbagi catatan teknis, rekayasa kode, dan arsitektur web.</p>
                </div>
            </x-sketch-box>
        </div>

        <!-- Prev / Next Navigation -->
        @if($prev || $next)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-border">
                @if($prev)
                    <a href="{{ route('articles.show', $prev->slug) }}" class="p-4 border-2 border-ink rounded-lg bg-surface sketch-shadow-hover flex flex-col justify-between">
                        <span class="font-ui text-xs text-text-secondary uppercase tracking-wider">&larr; Artikel Sebelumnya</span>
                        <span class="font-heading text-xl font-bold text-ink mt-1 line-clamp-1">{{ $prev->title }}</span>
                    </a>
                @else
                    <div></div>
                @endif

                @if($next)
                    <a href="{{ route('articles.show', $next->slug) }}" class="p-4 border-2 border-ink rounded-lg bg-surface sketch-shadow-hover flex flex-col justify-between text-right">
                        <span class="font-ui text-xs text-text-secondary uppercase tracking-wider">Artikel Selanjutnya &rarr;</span>
                        <span class="font-heading text-xl font-bold text-ink mt-1 line-clamp-1">{{ $next->title }}</span>
                    </a>
                @endif
            </div>
        @endif

        <!-- Related Articles -->
        @if($related->isNotEmpty())
            <div class="pt-10 border-t border-border space-y-6">
                <h3 class="font-heading text-3xl font-bold text-ink">Tulisan Terkait</h3>
                <div class="space-y-3">
                    @foreach($related as $rel)
                        <x-article-card :article="$rel" variant="compact" :index="$loop->index" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-public-layout>
