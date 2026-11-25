<x-admin-layout header="Articles">
    <div class="space-y-6">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading text-4xl font-bold text-ink">Daftar Artikel</h1>
                <p class="font-ui text-lg text-text-secondary">Kelola semua artikel blog, status publikasi, dan kategori.</p>
            </div>

            <x-button href="{{ route('admin.articles.create') }}" variant="primary">
                <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tulis Artikel Baru
            </x-button>
        </div>

        <!-- Filter & Search Bar -->
        <x-sketch-box variant="a" class="p-4 bg-surface">
            <form action="{{ route('admin.articles.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Status & Category Filters -->
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()" class="font-ui text-sm border-2 border-ink rounded-lg bg-bg text-text px-3 py-2 min-h-[44px] w-full sm:w-auto focus:border-ink focus:ring-0">
                        <option value="">Semua Status</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Terbit (Published)</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draf (Draft)</option>
                    </select>

                    <!-- Category Filter -->
                    <select name="category_id" onchange="this.form.submit()" class="font-ui text-sm border-2 border-ink rounded-lg bg-bg text-text px-3 py-2 min-h-[44px] w-full sm:w-auto focus:border-ink focus:ring-0">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Input -->
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <x-text-input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari judul..."
                        class="w-full md:w-64"
                    />
                    <x-button type="submit" variant="secondary">Cari</x-button>
                    @if(request('status') || request('category_id') || request('q'))
                        <a href="{{ route('admin.articles.index') }}" class="font-ui text-sm text-text-secondary hover:text-ink px-2 py-1">Reset</a>
                    @endif
                </div>
            </form>
        </x-sketch-box>

        <!-- Desktop Table / Mobile Cards -->
        @if($articles->isNotEmpty())
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto bg-surface border-2 border-ink sketch-radius-b sketch-shadow">
                <table class="w-full text-left font-ui">
                    <thead class="bg-bg border-b-2 border-ink text-xs uppercase tracking-wider text-text-secondary">
                        <tr>
                            <th class="px-6 py-4">Judul Artikel</th>
                            <th class="px-6 py-4">Kategori</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Tanggal Publikasi</th>
                            <th class="px-6 py-4">Waktu Baca</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-sm">
                        @foreach($articles as $art)
                            <tr class="hover:bg-bg/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-ink max-w-xs truncate">
                                    <a href="{{ route('admin.articles.edit', $art->id) }}" class="hover:text-accent-dark">
                                        {{ $art->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-text-secondary">
                                    {{ $art->category->name ?? '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <x-badge :type="$art->status">
                                        {{ ucfirst($art->status) }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 text-text-secondary">
                                    {{ $art->published_at ? $art->published_at->translatedFormat('d M Y H:i') : '—' }}
                                </td>
                                <td class="px-6 py-4 text-text-secondary">
                                    {{ $art->reading_time }} min
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    <a href="{{ route('admin.articles.edit', $art->id) }}" class="font-bold text-accent-dark hover:underline">
                                        Edit
                                    </a>
                                    @if($art->status === 'published')
                                        <a href="{{ route('articles.show', $art->slug) }}" target="_blank" class="text-text-secondary hover:text-ink">
                                            Lihat
                                        </a>
                                    @endif
                                    <form action="{{ route('admin.articles.destroy', $art->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-bold text-danger hover:underline">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-4">
                @foreach($articles as $art)
                    <div class="bg-surface border-2 border-ink rounded-lg p-5 sketch-shadow space-y-3">
                        <div class="flex items-center justify-between">
                            <x-badge :type="$art->status">
                                {{ ucfirst($art->status) }}
                            </x-badge>
                            <span class="font-ui text-xs text-text-secondary">
                                {{ $art->published_at ? $art->published_at->translatedFormat('d M Y') : 'Draf' }}
                            </span>
                        </div>

                        <h3 class="font-heading text-2xl font-bold text-ink">
                            <a href="{{ route('admin.articles.edit', $art->id) }}">{{ $art->title }}</a>
                        </h3>

                        <p class="font-ui text-sm text-text-secondary">
                            Kategori: {{ $art->category->name ?? '—' }} • {{ $art->reading_time }} min baca
                        </p>

                        <div class="pt-3 border-t border-border flex items-center justify-between gap-2">
                            <a href="{{ route('admin.articles.edit', $art->id) }}" class="font-bold text-accent-dark font-ui text-sm inline-flex items-center min-h-[44px] px-1">
                                Edit Artikel &rarr;
                            </a>

                            <form action="{{ route('admin.articles.destroy', $art->id) }}" method="POST" onsubmit="return confirm('Hapus artikel ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-bold text-danger font-ui text-sm inline-flex items-center min-h-[44px] px-2">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $articles->links() }}
            </div>
        @else
            <x-empty-state
                title="Tidak Ada Artikel"
                description="Belum ada artikel yang cocok dengan filter atau kata kunci pencarian Anda."
                action-label="Tulis Artikel Baru"
                :action-url="route('admin.articles.create')"
            />
        @endif
    </div>
</x-admin-layout>

