@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-4 font-ui">
        <!-- Mobile Navigation Buttons -->
        <div class="flex items-center justify-between w-full sm:hidden gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-bold text-text-secondary bg-surface border-2 border-border sketch-radius-a cursor-not-allowed opacity-60 select-none whitespace-nowrap">
                    &larr; Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-bold text-text bg-surface border-2 border-ink sketch-radius-a hover:bg-bg hover:text-ink sketch-shadow-sm transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent whitespace-nowrap">
                    &larr; Sebelumnya
                </a>
            @endif

            <span class="font-mono text-xs font-semibold text-text-secondary px-1.5 shrink-0 whitespace-nowrap">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-bold text-text bg-surface border-2 border-ink sketch-radius-a hover:bg-bg hover:text-ink sketch-shadow-sm transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent whitespace-nowrap">
                    Selanjutnya &rarr;
                </a>
            @else
                <span class="inline-flex items-center justify-center min-h-[44px] px-3 py-2 text-sm font-bold text-text-secondary bg-surface border-2 border-border sketch-radius-a cursor-not-allowed opacity-60 select-none whitespace-nowrap">
                    Selanjutnya &rarr;
                </span>
            @endif
        </div>

        <!-- Desktop Information Text -->
        <div class="hidden sm:block">
            <p class="text-sm text-text-secondary">
                Menampilkan
                @if ($paginator->firstItem())
                    <span class="font-bold text-ink">{{ $paginator->firstItem() }}</span>
                    sampai
                    <span class="font-bold text-ink">{{ $paginator->lastItem() }}</span>
                @else
                    {{ $paginator->count() }}
                @endif
                dari
                <span class="font-bold text-ink">{{ $paginator->total() }}</span>
                hasil
            </p>
        </div>

        <!-- Pagination Controls -->
        <div class="hidden sm:block">
            <div class="inline-flex items-center rounded-lg border-2 border-ink bg-surface sketch-shadow-sm p-1 gap-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Halaman sebelumnya">
                        <span class="min-w-[36px] h-9 px-2 inline-flex items-center justify-center text-text-secondary/40 cursor-not-allowed rounded" aria-hidden="true">
                            <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="min-w-[36px] h-9 px-2 inline-flex items-center justify-center text-text hover:text-ink hover:bg-bg rounded transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent" aria-label="Halaman sebelumnya">
                        <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true">
                            <span class="min-w-[28px] h-9 px-1 inline-flex items-center justify-center text-text-secondary font-bold select-none">{{ $element }}</span>
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">
                                    <span class="min-w-[36px] h-9 px-2.5 inline-flex items-center justify-center font-bold text-sm bg-accent-dark text-on-accent rounded shadow-sm select-none">
                                        {{ $page }}
                                    </span>
                                </span>
                            @else
                                <a href="{{ $url }}" class="min-w-[36px] h-9 px-2.5 inline-flex items-center justify-center font-semibold text-sm text-text hover:text-ink hover:bg-bg rounded transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent" aria-label="Ke halaman {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="min-w-[36px] h-9 px-2 inline-flex items-center justify-center text-text hover:text-ink hover:bg-bg rounded transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent" aria-label="Halaman berikutnya">
                        <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Halaman berikutnya">
                        <span class="min-w-[36px] h-9 px-2 inline-flex items-center justify-center text-text-secondary/40 cursor-not-allowed rounded" aria-hidden="true">
                            <svg class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
