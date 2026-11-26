@php
    $formatButtons = [
        [
            'label' => 'B',
            'title' => 'Tebal (Bold)',
            'action' => "insertToolbar('**', '**', 'teks tebal')",
            'class' => 'font-bold sm:min-w-[36px]',
            'raw' => false,
        ],
        [
            'label' => 'I',
            'title' => 'Miring (Italic)',
            'action' => "insertToolbar('*', '*', 'teks miring')",
            'class' => 'italic sm:min-w-[36px]',
            'raw' => false,
        ],
        [
            'label' => 'H1',
            'title' => 'Heading 1',
            'action' => 'insertHeading(1)',
            'class' => 'font-bold sm:min-w-[36px]',
            'raw' => false,
        ],
        [
            'label' => 'H2',
            'title' => 'Heading 2',
            'action' => 'insertHeading(2)',
            'class' => 'font-bold sm:min-w-[36px]',
            'raw' => false,
        ],
        [
            'label' => 'H3',
            'title' => 'Heading 3',
            'action' => 'insertHeading(3)',
            'class' => 'font-bold sm:min-w-[36px]',
            'raw' => false,
        ],
        [
            'label' => 'Link',
            'title' => 'Tautan (Link) [Teks](URL)',
            'action' => 'insertLink()',
            'class' => '',
            'raw' => false,
        ],
        [
            'label' => '&lt;/&gt;',
            'title' => 'Kode (Inline / Block)',
            'action' => 'insertCode()',
            'class' => '',
            'raw' => true,
        ],
        [
            'label' => '&ldquo;',
            'title' => 'Kutipan (Quote)',
            'action' => "insertToolbar('\\n> ', '\\n', 'Kutipan...')",
            'class' => 'sm:min-w-[36px]',
            'raw' => true,
        ],
        [
            'label' => '&bull; List',
            'title' => 'Daftar (List)',
            'action' => "insertToolbar('\\n- ', '\\n', 'Item daftar')",
            'class' => 'col-span-2 sm:col-span-1 whitespace-nowrap',
            'raw' => true,
        ],
    ];
@endphp

<!-- Header with Tabs, Fullscreen Toggle, and Responsive Formatting Toolbar -->
<div class="bg-bg border-b-2 border-ink p-2.5 sm:p-2 flex flex-wrap items-center justify-between gap-2 shrink-0">
    <!-- Mode Switcher (Edit / Preview) -->
    <div class="order-1 inline-flex rounded-lg border border-ink p-0.5 bg-surface font-ui text-xs sm:text-sm min-w-0">
        <button
            type="button"
            @click="tab = 'edit'"
            class="px-2 sm:px-3 py-1 rounded transition-colors font-semibold min-h-[38px] whitespace-nowrap"
            :class="tab === 'edit' ? 'bg-accent-dark text-on-accent' : 'text-text-secondary hover:text-ink'"
        >
            Markdown Editor
        </button>
        <button
            type="button"
            @click="fetchPreview()"
            class="px-2 sm:px-3 py-1 rounded transition-colors font-semibold inline-flex items-center space-x-1 sm:space-x-1.5 min-h-[38px] whitespace-nowrap"
            :class="tab === 'preview' ? 'bg-accent-dark text-on-accent' : 'text-text-secondary hover:text-ink'"
            title="Lihat hasil format teks & layout artikel"
        >
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span>Pratinjau Server</span>
        </button>
    </div>

    <!-- Fullscreen / Expand Button -->
    <div class="order-2 flex items-center shrink-0">
        <button
            type="button"
            @click="toggleFullscreen()"
            :title="isFullscreen ? 'Perkecil Editor (Esc)' : 'Perbesar Layar Penuh (Fullscreen)'"
            class="px-2 sm:px-2.5 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[40px] sm:min-h-[38px] min-w-[40px] sm:min-w-0 inline-flex items-center justify-center sm:space-x-1.5 transition-colors"
            :class="isFullscreen ? 'bg-accent-dark text-on-accent font-bold border-ink' : 'text-text hover:text-ink'"
            :aria-label="isFullscreen ? 'Perkecil editor' : 'Perbesar layar penuh'"
        >
            <svg x-show="!isFullscreen" class="w-4 h-4 stroke-current shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
            </svg>
            <svg x-show="isFullscreen" x-cloak class="w-4 h-4 stroke-current shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9L4 4m0 0h4m-4 0v4m16 5l-5 5m5-5h-4m4 0v4M9 15l-5 5m0 0v-4m0 4h4m11-16l-5 5m5-5v4m0-4h-4" />
            </svg>
            <span class="hidden sm:inline font-ui text-xs" x-text="isFullscreen ? 'Perkecil' : 'Perbesar'"></span>
        </button>
    </div>

    <!-- Minimalist Markdown Toolbar (5-col balanced grid on mobile, single inline row on tablet/desktop) -->
    <div
        x-show="tab === 'edit'"
        class="order-3 basis-full w-full pt-2 border-t border-border/60 grid grid-cols-5 sm:flex sm:flex-wrap items-center gap-1.5 sm:gap-1 font-mono text-sm"
    >
        @foreach($formatButtons as $btn)
            <button
                type="button"
                @click="{{ $btn['action'] }}"
                title="{{ $btn['title'] }}"
                aria-label="{{ $btn['title'] }}"
                class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[44px] sm:min-h-[38px] inline-flex items-center justify-center transition-colors {{ $btn['class'] }}"
            >
                @if($btn['raw'])
                    {!! $btn['label'] !!}
                @else
                    {{ $btn['label'] }}
                @endif
            </button>
        @endforeach
    </div>
</div>

