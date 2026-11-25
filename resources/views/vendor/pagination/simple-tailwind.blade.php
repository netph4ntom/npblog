@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex items-center justify-between gap-2 font-ui">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center min-h-[44px] px-3 sm:px-4 py-2 text-sm font-bold text-text-secondary bg-surface border-2 border-border sketch-radius-a cursor-not-allowed opacity-60 select-none whitespace-nowrap">
                &larr; Sebelumnya
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center min-h-[44px] px-3 sm:px-4 py-2 text-sm font-bold text-text bg-surface border-2 border-ink sketch-radius-a hover:bg-bg hover:text-ink sketch-shadow-sm transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent whitespace-nowrap">
                &larr; Sebelumnya
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center min-h-[44px] px-3 sm:px-4 py-2 text-sm font-bold text-text bg-surface border-2 border-ink sketch-radius-a hover:bg-bg hover:text-ink sketch-shadow-sm transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent whitespace-nowrap">
                Selanjutnya &rarr;
            </a>
        @else
            <span class="inline-flex items-center justify-center min-h-[44px] px-3 sm:px-4 py-2 text-sm font-bold text-text-secondary bg-surface border-2 border-border sketch-radius-a cursor-not-allowed opacity-60 select-none whitespace-nowrap">
                Selanjutnya &rarr;
            </span>
        @endif
    </nav>
@endif
