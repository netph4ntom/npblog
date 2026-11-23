<x-admin-layout header="Dashboard">
    <div class="space-y-8">
        <!-- Quick Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading text-4xl font-bold text-ink">Ringkasan Blog</h1>
                <p class="font-ui text-lg text-text-secondary">Selamat datang kembali! Berikut ikhtisar publikasi dan draf tulisan Anda.</p>
            </div>

            <x-button href="{{ route('admin.articles.create') }}" variant="primary">
                <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tulis Artikel Baru
            </x-button>
        </div>

        <!-- 4 Stats Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-stat-card
                title="Total Artikel"
                :value="$totalArticles"
                variant="a"
                description="Semua tulisan yang dibuat"
            />
            <x-stat-card
                title="Artikel Terbit"
                :value="$publishedArticles"
                variant="b"
                description="Aktif dan tayang ke publik"
            />
            <x-stat-card
                title="Artikel Draf"
                :value="$draftArticles"
                variant="c"
                description="Belum dipublikasikan"
            />
            <x-stat-card
                title="Kategori"
                :value="$totalCategories"
                variant="a"
                description="Topik artikel terdaftar"
            />
        </div>

        <!-- Two Columns: Recent Publications & Continue Writing -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Continue Writing (Drafts) -->
            <x-sketch-box variant="b" class="p-6 bg-surface space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-warning-text"></span>
                        <h2 class="font-heading text-2xl font-bold text-ink">Lanjutkan Menulis (Draf)</h2>
                    </div>
                    <a href="{{ route('admin.articles.index', ['status' => 'draft']) }}" class="font-ui text-sm text-accent-dark hover:underline font-semibold">
                        Semua Draf &rarr;
                    </a>
                </div>

                @if($recentDrafts->isNotEmpty())
                    <div class="divide-y divide-border">
                        @foreach($recentDrafts as $draft)
                            <div class="py-3.5 flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-ui text-base font-bold text-ink truncate hover:text-accent-dark">
                                        <a href="{{ route('admin.articles.edit', $draft->id) }}">{{ $draft->title }}</a>
                                    </h3>
                                    <p class="font-ui text-xs text-text-secondary mt-0.5">
                                        Diperbarui {{ $draft->updated_at->diffForHumans() }}
                                        @if($draft->category)
                                            • {{ $draft->category->name }}
                                        @endif
                                    </p>
                                </div>
                                <a href="{{ route('admin.articles.edit', $draft->id) }}" class="font-ui text-sm font-bold text-accent-dark hover:underline whitespace-nowrap px-3 py-1.5 border border-ink rounded bg-bg">
                                    Lanjutkan Menulis
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="font-ui text-sm text-text-secondary py-6 text-center">Tidak ada draf yang sedang dikerjakan saat ini.</p>
                @endif
            </x-sketch-box>

            <!-- Recent Publications -->
            <x-sketch-box variant="a" class="p-6 bg-surface space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-border">
                    <div class="flex items-center space-x-2">
                        <span class="w-3 h-3 rounded-full bg-accent"></span>
                        <h2 class="font-heading text-2xl font-bold text-ink">Publikasi Terbaru</h2>
                    </div>
                    <a href="{{ route('admin.articles.index', ['status' => 'published']) }}" class="font-ui text-sm text-accent-dark hover:underline font-semibold">
                        Semua Terbit &rarr;
                    </a>
                </div>

                @if($recentPublished->isNotEmpty())
                    <div class="divide-y divide-border">
                        @foreach($recentPublished as $pub)
                            <div class="py-3.5 flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-ui text-base font-bold text-ink truncate hover:text-accent-dark">
                                        <a href="{{ route('admin.articles.edit', $pub->id) }}">{{ $pub->title }}</a>
                                    </h3>
                                    <p class="font-ui text-xs text-text-secondary mt-0.5">
                                        Terbit {{ $pub->published_at?->translatedFormat('d M Y') }}
                                        @if($pub->category)
                                            • {{ $pub->category->name }}
                                        @endif
                                    </p>
                                </div>
                                <a href="{{ route('articles.show', $pub->slug) }}" target="_blank" class="font-ui text-xs text-text-secondary hover:text-ink whitespace-nowrap p-1.5" title="Lihat di situs">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" y1="14" x2="21" y2="3"></line>
                                    </svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="font-ui text-sm text-text-secondary py-6 text-center">Belum ada artikel yang dipublikasikan.</p>
                @endif
            </x-sketch-box>
        </div>
    </div>
</x-admin-layout>

