<x-admin-layout header="Categories">
    <div class="space-y-6">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading text-4xl font-bold text-ink">Kategori Artikel</h1>
                <p class="font-ui text-lg text-text-secondary">Kelola kategori tulisan blog Anda.</p>
            </div>

            <x-button href="{{ route('admin.categories.create') }}" variant="primary">
                <svg class="w-5 h-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Kategori
            </x-button>
        </div>

        @if($categories->isNotEmpty())
            <!-- Desktop Table -->
            <div class="hidden md:block overflow-x-auto bg-surface border-2 border-ink sketch-radius-b sketch-shadow">
                <table class="w-full text-left font-ui">
                    <thead class="bg-bg border-b-2 border-ink text-xs uppercase tracking-wider text-text-secondary">
                        <tr>
                            <th class="px-6 py-4">Nama Kategori</th>
                            <th class="px-6 py-4">Slug</th>
                            <th class="px-6 py-4">Deskripsi</th>
                            <th class="px-6 py-4">Jumlah Artikel</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border text-sm">
                        @foreach($categories as $category)
                            <tr class="hover:bg-bg/50 transition-colors">
                                <td class="px-6 py-4 font-bold text-ink">
                                    {{ $category->name }}
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-text-secondary">
                                    /{{ $category->slug }}
                                </td>
                                <td class="px-6 py-4 text-text-secondary max-w-sm truncate">
                                    {{ $category->description ?? '—' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-ink">
                                    {{ $category->articles_count }}
                                </td>
                                <td class="px-6 py-4 text-right space-x-3 whitespace-nowrap">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="font-bold text-accent-dark hover:underline">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kategori ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="font-bold text-danger hover:underline"
                                            @if($category->articles_count > 0)
                                                title="Kategori memiliki {{ $category->articles_count }} artikel terhubung"
                                            @endif
                                        >
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
                @foreach($categories as $category)
                    <div class="bg-surface border-2 border-ink rounded-lg p-5 sketch-shadow space-y-3">
                        <div class="flex items-center justify-between">
                            <h2 class="font-heading text-2xl font-bold text-ink">{{ $category->name }}</h2>
                            <span class="font-mono text-xs px-2 py-1 bg-bg border border-ink rounded">
                                {{ $category->articles_count }} artikel
                            </span>
                        </div>
                        <p class="font-mono text-xs text-text-secondary">/{{ $category->slug }}</p>
                        @if($category->description)
                            <p class="font-body text-sm text-text-secondary">{{ $category->description }}</p>
                        @endif

                        <div class="pt-3 border-t border-border flex items-center justify-between">
                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="font-bold text-accent-dark font-ui text-sm">
                                Edit Kategori &rarr;
                            </a>

                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-bold text-danger font-ui text-sm">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state
                title="Belum Ada Kategori"
                description="Buat kategori pertama Anda untuk mengelompokkan tulisan artikel."
                action-label="Tambah Kategori"
                :action-url="route('admin.categories.create')"
            />
        @endif
    </div>
</x-admin-layout>

