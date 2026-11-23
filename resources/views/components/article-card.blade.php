@props([
    'article',
    'variant' => 'grid', // 'grid', 'featured', 'compact'
    'index' => 0,
])

@php
    $radiusPreset = ['a', 'b', 'c'][$index % 3];
@endphp

@if($variant === 'featured')
    <article class="group relative bg-surface border-2 border-ink sketch-radius-{{ $radiusPreset }} sketch-shadow p-6 sm:p-8 hover:-translate-x-0.5 hover:-translate-y-0.5 transition-all duration-150">
        <div class="flex flex-col lg:flex-row lg:items-center gap-6 lg:gap-8">
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-3 mb-3">
                    @if($article->category)
                        <x-chip variant="accent">
                            {{ $article->category->name }}
                        </x-chip>
                    @endif
                    <span class="inline-flex items-center text-text-secondary font-ui text-sm">
                        <svg class="w-4 h-4 mr-1 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        {{ $article->reading_time }} min baca
                    </span>
                    <span class="text-text-secondary text-sm">•</span>
                    <time datetime="{{ $article->published_at?->toISOString() }}" class="text-text-secondary font-ui text-sm">
                        {{ $article->published_at?->translatedFormat('d M Y') }}
                    </time>
                </div>

                <h2 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-bold text-ink group-hover:text-accent-dark transition-colors leading-tight">
                    <a href="{{ route('articles.show', $article->slug) }}" class="focus-visible:outline-none focus-visible:underline">
                        {{ $article->title }}
                    </a>
                </h2>

                <p class="font-body text-text-secondary text-lg mt-3 line-clamp-3 leading-relaxed">
                    {{ $article->excerpt }}
                </p>

                <div class="mt-6 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="font-ui text-text font-medium text-base">Oleh {{ $article->user->name ?? 'Dapit' }}</span>
                    </div>

                    <a href="{{ route('articles.show', $article->slug) }}" class="inline-flex items-center font-ui text-lg font-bold text-accent-dark group-hover:underline">
                        Baca Selengkapnya
                        <svg class="w-5 h-5 ml-1 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </article>
@elseif($variant === 'compact')
    <article class="group bg-surface border-2 border-ink sketch-radius-{{ $radiusPreset }} sketch-shadow p-4 hover:-translate-x-0.5 hover:-translate-y-0.5 transition-all duration-150 flex items-center justify-between gap-4">
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2 mb-1">
                @if($article->category)
                    <span class="text-xs font-ui font-semibold text-accent-dark">{{ $article->category->name }}</span>
                    <span class="text-text-secondary text-xs">•</span>
                @endif
                <time datetime="{{ $article->published_at?->toISOString() }}" class="text-text-secondary font-ui text-xs">
                    {{ $article->published_at?->translatedFormat('d M Y') }}
                </time>
            </div>
            <h3 class="font-heading text-xl font-bold text-ink group-hover:text-accent-dark transition-colors truncate">
                <a href="{{ route('articles.show', $article->slug) }}">
                    {{ $article->title }}
                </a>
            </h3>
        </div>
        <span class="text-text-secondary font-ui text-xs whitespace-nowrap">{{ $article->reading_time }} min</span>
    </article>
@else
    {{-- Grid Card --}}
    <article class="group bg-surface border-2 border-ink sketch-radius-{{ $radiusPreset }} sketch-shadow p-6 flex flex-col justify-between hover:-translate-x-0.5 hover:-translate-y-0.5 transition-all duration-150">
        <div>
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                @if($article->category)
                    <x-chip variant="accent">
                        {{ $article->category->name }}
                    </x-chip>
                @endif
                <span class="text-text-secondary font-ui text-sm flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    {{ $article->reading_time }} min
                </span>
            </div>

            <h3 class="font-heading text-2xl sm:text-3xl font-bold text-ink group-hover:text-accent-dark transition-colors line-clamp-2 leading-snug">
                <a href="{{ route('articles.show', $article->slug) }}" class="focus-visible:outline-none focus-visible:underline">
                    {{ $article->title }}
                </a>
            </h3>

            <p class="font-body text-text-secondary text-base mt-2 line-clamp-3 leading-relaxed">
                {{ $article->excerpt }}
            </p>
        </div>

        <div class="mt-6 pt-4 border-t border-border flex items-center justify-between text-sm font-ui">
            <time datetime="{{ $article->published_at?->toISOString() }}" class="text-text-secondary">
                {{ $article->published_at?->translatedFormat('d M Y') }}
            </time>

            <a href="{{ route('articles.show', $article->slug) }}" class="font-bold text-accent-dark group-hover:underline inline-flex items-center">
                Baca
                <svg class="w-4 h-4 ml-0.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </article>
@endif

