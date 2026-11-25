<x-admin-layout header="Media">
    <div
        class="space-y-6"
        x-data="{
            copyToClipboard(text, message = 'Tautan berhasil disalin!') {
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(text);
                } else {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-999999px';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    document.execCommand('copy');
                    textArea.remove();
                }
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: message, type: 'success' }
                }));
            }
        }"
    >
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading text-4xl font-bold text-ink">Galeri Media</h1>
                <p class="font-ui text-lg text-text-secondary">Kelola aset gambar, optimasi WebP, dan thumbnail responsif.</p>
            </div>
        </div>

        <!-- Upload Panel & Search Bar -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Upload Box (1 col) -->
            <x-sketch-box variant="a" class="p-5 bg-surface space-y-4">
                <h2 class="font-heading text-xl font-bold text-ink pb-2 border-b border-border">Unggah Gambar Baru</h2>
                <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="image" value="File Gambar" />
                        <input
                            type="file"
                            id="image"
                            name="image"
                            accept="image/jpeg,image/png,image/webp"
                            required
                            class="w-full text-xs font-ui text-text file:mr-3 file:py-2 file:px-3 file:rounded file:border-2 file:border-ink file:text-xs file:font-bold file:text-ink file:bg-bg file:cursor-pointer"
                        />
                        <p class="font-ui text-xs text-text-secondary mt-1">JPEG, PNG, WebP maks 5 MB.</p>
                        <x-input-error :messages="$errors->get('image')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="alt_text" value="Alt Text (Deskripsi Gambar)" />
                        <x-text-input
                            id="alt_text"
                            name="alt_text"
                            type="text"
                            class="w-full text-sm"
                            placeholder="Deskripsi singkat untuk aksesibilitas..."
                            value="{{ old('alt_text') }}"
                        />
                        <x-input-error :messages="$errors->get('alt_text')" class="mt-1" />
                    </div>

                    <x-button type="submit" variant="primary" class="w-full">
                        Unggah & Konversi
                    </x-button>
                </form>
            </x-sketch-box>

            <!-- Search & Info (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Search Box -->
                <x-sketch-box variant="b" class="p-4 bg-surface">
                    <form action="{{ route('admin.media.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                        <div class="w-full sm:flex-1">
                            <x-text-input
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Cari berdasarkan nama file atau alt text..."
                                class="w-full"
                            />
                        </div>
                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <x-button type="submit" variant="secondary" class="w-full sm:w-auto">
                                Cari
                            </x-button>
                            @if(request('q'))
                                <a href="{{ route('admin.media.index') }}" class="font-ui text-sm text-text-secondary hover:text-ink px-2 py-1">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </x-sketch-box>

                <!-- Pipeline Feature Highlights -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 font-ui text-xs text-text-secondary">
                    <div class="p-3 bg-surface border border-ink sketch-radius-a">
                        <span class="font-bold text-ink block mb-0.5">🔒 Sanitasi & EXIF</span>
                        Metadata EXIF otomatis dibersihkan dan diputar sesuai orientasi kamera.
                    </div>
                    <div class="p-3 bg-surface border border-ink sketch-radius-b">
                        <span class="font-bold text-ink block mb-0.5">📐 Resize 1600px</span>
                        Gambar resolusi tinggi diperkecil secara proporsional maksimal 1600px.
                    </div>
                    <div class="p-3 bg-surface border border-ink sketch-radius-c">
                        <span class="font-bold text-ink block mb-0.5">⚡ WebP + Thumbnails</span>
                        Konversi ke WebP modern beserta thumbnail 400px dan 800px.
                    </div>
                </div>
            </div>
        </div>

        <!-- Media Grid -->
        @if($media->isNotEmpty())
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($media as $item)
                    <div class="bg-surface border-2 border-ink sketch-radius-b sketch-shadow flex flex-col justify-between overflow-hidden">
                        <!-- Preview Image -->
                        <div class="relative bg-bg border-b-2 border-ink aspect-video flex items-center justify-center overflow-hidden group">
                            <img
                                src="{{ $item->thumbnail_url }}"
                                alt="{{ $item->alt_text ?? $item->original_name }}"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                loading="lazy"
                            />
                            <div class="absolute top-2 right-2 flex space-x-1">
                                <span class="px-2 py-0.5 bg-ink/90 text-surface font-mono font-bold text-[10px] rounded">
                                    {{ $item->width }}x{{ $item->height }}
                                </span>
                            </div>
                        </div>

                        <!-- Info & Actions -->
                        <div class="p-4 space-y-3 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-ui text-sm font-bold text-ink truncate" title="{{ $item->original_name }}">
                                    {{ $item->original_name }}
                                </h3>

                                <p class="font-ui text-xs text-text-secondary mt-1 line-clamp-2" title="{{ $item->alt_text }}">
                                    Alt: {{ $item->alt_text ?: '— (tidak ada alt text)' }}
                                </p>

                                <div class="font-mono text-[11px] text-text-secondary mt-2 flex items-center justify-between">
                                    <span>{{ round($item->size / 1024) }} KB</span>
                                    <span>{{ $item->created_at?->translatedFormat('d M Y') }}</span>
                                </div>
                            </div>

                            <!-- Action Toolbar -->
                            <div class="pt-3 border-t border-border space-y-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        type="button"
                                        @click="copyToClipboard('![{{ addslashes($item->alt_text ?? 'gambar') }}]({{ $item->url }})', 'Sintaks Markdown disalin!')"
                                        class="px-2 py-1.5 font-ui text-xs font-semibold border border-ink rounded bg-bg hover:bg-surface text-ink transition-colors min-h-[36px]"
                                    >
                                        Salin MD
                                    </button>
                                    <button
                                        type="button"
                                        @click="copyToClipboard(new URL('{{ $item->url }}', window.location.origin).href, 'URL gambar disalin!')"
                                        class="px-2 py-1.5 font-ui text-xs font-semibold border border-ink rounded bg-bg hover:bg-surface text-ink transition-colors min-h-[36px]"
                                    >
                                        Salin URL
                                    </button>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <!-- Edit Alt Text Modal Trigger -->
                                    <div x-data="{ open: false }">
                                        <button
                                            type="button"
                                            @click="open = true"
                                            class="font-ui text-xs font-bold text-accent-dark hover:underline min-h-[44px] inline-flex items-center px-1"
                                        >
                                            Edit Alt Text
                                        </button>

                                        <!-- Simple Inline Modal -->
                                        <div
                                            x-show="open"
                                            x-cloak
                                            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-ink/50 backdrop-blur-sm"
                                        >
                                            <div
                                                @click.away="open = false"
                                                class="w-full max-w-sm p-5 bg-surface border-2 border-ink sketch-radius-a sketch-shadow space-y-4"
                                            >
                                                <h4 class="font-heading text-xl font-bold text-ink">Perbarui Alt Text</h4>
                                                <form action="{{ route('admin.media.update', $item->id) }}" method="POST" class="space-y-4">
                                                    @csrf
                                                    @method('PUT')

                                                    <div>
                                                        <x-input-label for="alt_{{ $item->id }}" value="Alt Text" />
                                                        <x-text-input
                                                            id="alt_{{ $item->id }}"
                                                            name="alt_text"
                                                            type="text"
                                                            class="w-full text-sm"
                                                            value="{{ $item->alt_text }}"
                                                        />
                                                    </div>

                                                    <div class="flex justify-end space-x-2 pt-2">
                                                        <button
                                                            type="button"
                                                            @click="open = false"
                                                            class="px-3 py-1.5 font-ui text-sm text-text-secondary hover:text-ink min-h-[44px]"
                                                        >
                                                            Batal
                                                        </button>
                                                        <x-button type="submit" variant="primary" size="sm">
                                                            Simpan
                                                        </x-button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delete Media -->
                                    <form
                                        action="{{ route('admin.media.destroy', $item->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Hapus gambar ini dari penyimpanan dan database?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-ui text-xs font-bold text-danger hover:underline min-h-[44px] inline-flex items-center px-2">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $media->links() }}
            </div>
        @else
            <x-empty-state
                title="Belum Ada Media"
                description="Unggah gambar pertama Anda untuk digunakan pada artikel atau galeri tulisan."
            />
        @endif
    </div>
</x-admin-layout>

