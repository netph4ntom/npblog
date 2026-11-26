<x-admin-layout header="Edit Artikel">
    <script>
        function articleEditor(config = {}) {
            return {
                tab: 'edit',
                isFullscreen: false,
                body: config.initialBody || '',
                previewHtml: '',
                loadingPreview: false,
                wordCount: 0,
                readingTime: 1,
                title: config.initialTitle || '',
                slug: config.initialSlug || '',
                slugManuallyEdited: Boolean(config.initialSlug),
                toggleFullscreen() {
                    this.isFullscreen = !this.isFullscreen;
                },
                updateMetrics() {
                    const text = (this.body || '').trim();
                    const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
                    this.wordCount = words;
                    this.readingTime = Math.max(1, Math.ceil(words / 200));
                },
                generateSlug(text) {
                    return (text || '')
                        .toString()
                        .toLowerCase()
                        .trim()
                        .replace(/&/g, '-dan-')
                        .replace(/[\s\W-]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                },
                onTitleInput() {
                    if (!this.slugManuallyEdited) {
                        this.slug = this.generateSlug(this.title);
                    }
                },
                onSlugInput() {
                    this.slugManuallyEdited = true;
                },
                insertToolbar(prefix, suffix = '', defaultText = '') {
                    const el = this.$refs.bodyTextarea;
                    if (!el) return;
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    const sel = el.value.substring(start, end) || defaultText;
                    const replace = prefix + sel + suffix;
                    el.setRangeText(replace, start, end, 'select');
                    this.body = el.value;
                    this.updateMetrics();
                    el.focus();
                },
                insertHeading(level) {
                    const el = this.$refs.bodyTextarea;
                    if (!el) return;
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    const sel = el.value.substring(start, end) || `Judul Heading ${level}`;
                    const hashes = '#'.repeat(level);
                    const replace = `\n${hashes} ${sel}\n`;
                    el.setRangeText(replace, start, end, 'select');
                    this.body = el.value;
                    this.updateMetrics();
                    el.focus();
                },
                insertLink() {
                    const el = this.$refs.bodyTextarea;
                    if (!el) return;
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    const sel = el.value.substring(start, end).trim();

                    let text = 'Teks Tautan';
                    let url = 'https://';

                    if (sel) {
                        if (/^https?:\/\//i.test(sel)) {
                            url = sel;
                            text = 'Tautan';
                        } else {
                            text = sel;
                        }
                    }

                    const replace = `[${text}](${url})`;
                    el.setRangeText(replace, start, end, 'select');
                    this.body = el.value;
                    this.updateMetrics();
                    el.focus();
                },
                insertCode() {
                    const el = this.$refs.bodyTextarea;
                    if (!el) return;
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    const sel = el.value.substring(start, end);

                    if (sel.includes('\n') || sel.length > 50) {
                        const replace = `\n\`\`\`bash\n${sel || 'echo "Hello World"'}\n\`\`\`\n`;
                        el.setRangeText(replace, start, end, 'select');
                    } else {
                        const replace = `\`${sel || 'kode'}\``;
                        el.setRangeText(replace, start, end, 'select');
                    }
                    this.body = el.value;
                    this.updateMetrics();
                    el.focus();
                },
                async fetchPreview() {
                    this.tab = 'preview';
                    this.loadingPreview = true;
                    try {
                        const res = await fetch(config.previewUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': config.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ body: this.body })
                        });
                        const data = await res.json();
                        this.previewHtml = data.html || '<p class="text-text-secondary italic">Tidak ada konten untuk dipratinjau.</p>';
                        this.$nextTick(() => {
                            if (window.setupCodeBlocks) {
                                window.setupCodeBlocks();
                            }
                        });
                    } catch (e) {
                        this.previewHtml = '<p class="text-danger">Gagal memuat pratinjau markdown server-side.</p>';
                    } finally {
                        this.loadingPreview = false;
                    }
                }
            };
        }
    </script>

    <div
        class="space-y-6"
        x-data="articleEditor({
            initialBody: @js(old('body', $article->body)),
            initialTitle: @js(old('title', $article->title)),
            initialSlug: @js(old('slug', $article->slug)),
            previewUrl: '{{ route('admin.articles.preview') }}',
            csrfToken: '{{ csrf_token() }}'
        })"
        x-init="updateMetrics()"
        x-effect="document.body.classList.toggle('overflow-hidden', isFullscreen)"
    >
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-border">
            <div class="min-w-0">
                <h1 class="font-heading text-4xl font-bold text-ink">Edit Artikel</h1>
                <p class="font-ui text-base text-text-secondary truncate">Perbarui artikel &ldquo;{{ $article->title }}&rdquo;.</p>
            </div>
            <div class="flex flex-wrap items-center justify-between sm:justify-end gap-3 w-full sm:w-auto shrink-0">
                <a href="{{ route('admin.articles.index') }}" class="font-ui text-sm text-text-secondary hover:text-ink min-h-[44px] inline-flex items-center">
                    &larr; Kembali ke Daftar
                </a>
                @if($article->isPublished())
                    <x-button
                        href="{{ route('articles.show', $article->slug) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        variant="secondary"
                        size="sm"
                        class="min-h-[44px] gap-1.5"
                    >
                        <span>Lihat Artikel Publik</span>
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </x-button>
                @endif
            </div>
        </div>

        <form action="{{ route('admin.articles.update', $article->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Editor Area (2 columns) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div>
                        <x-input-label for="title" value="Judul Artikel" />
                        <x-text-input
                            id="title"
                            name="title"
                            type="text"
                            x-model="title"
                            @input="onTitleInput()"
                            class="w-full text-xl font-bold font-heading"
                            value="{{ old('title', $article->title) }}"
                            required
                        />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>

                    <!-- Slug Inline -->
                    <div>
                        <x-input-label for="slug" value="Slug URL" />
                        <div class="relative flex items-center">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none select-none z-10">
                                <span class="font-mono text-sm text-text-secondary flex items-center pr-2 border-r border-ink/40">
                                    /articles/
                                </span>
                            </div>
                            <x-text-input
                                id="slug"
                                name="slug"
                                type="text"
                                x-model="slug"
                                @input="onSlugInput()"
                                class="w-full font-mono text-sm pl-[124px]"
                                value="{{ old('slug', $article->slug) }}"
                            />
                        </div>
                        @if($article->status === 'published')
                            <p class="font-ui text-xs text-text-secondary mt-1">
                                Catatan: Mengubah slug artikel yang sudah terbit akan otomatis mencatat pengalihan 301 ke slug baru.
                            </p>
                        @endif
                        <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                    </div>

                    <!-- Markdown Body Editor -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <x-input-label for="body" value="Isi Artikel (Markdown)" />

                            <!-- Dynamic Metrics -->
                            <div class="font-mono text-xs text-text-secondary flex items-center space-x-3">
                                <span><span x-text="wordCount">0</span> kata</span>
                                <span>•</span>
                                <span><span x-text="readingTime">1</span> min baca</span>
                            </div>
                        </div>

                        <!-- Fullscreen Backdrop -->
                        <div
                            x-show="isFullscreen"
                            x-cloak
                            class="fixed inset-0 bg-ink/75 backdrop-blur-sm z-40 transition-opacity"
                            @click="isFullscreen = false"
                        ></div>

                        <!-- Editor Tabs & Toolbar Container -->
                        <div
                            class="bg-surface border-2 border-ink transition-all"
                            :class="isFullscreen ? 'fixed inset-2 sm:inset-6 z-50 flex flex-col sketch-radius-a shadow-2xl overflow-hidden' : 'sketch-radius-b sketch-shadow overflow-hidden'"
                            @keydown.escape.window="if (isFullscreen) isFullscreen = false"
                        >
                            <x-markdown-toolbar />

                            <!-- Edit Area -->
                            <div x-show="tab === 'edit'" :class="isFullscreen ? 'flex-1 flex flex-col overflow-hidden min-h-0' : ''">
                                <textarea
                                    id="body"
                                    name="body"
                                    x-ref="bodyTextarea"
                                    x-model="body"
                                    @input="updateMetrics()"
                                    :rows="isFullscreen ? 28 : 18"
                                    class="w-full p-4 font-mono text-sm bg-surface text-text focus:outline-none border-0"
                                    :class="isFullscreen ? 'flex-1 h-full resize-none overflow-y-auto' : 'resize-y min-h-[360px]'"
                                    required
                                >{{ old('body', $article->body) }}</textarea>
                            </div>

                            <!-- Preview Area -->
                            <div
                                x-show="tab === 'preview'"
                                class="p-6 bg-surface overflow-y-auto"
                                :class="isFullscreen ? 'flex-1 min-h-0' : 'min-h-[400px]'"
                            >
                                <template x-if="loadingPreview">
                                    <div class="py-12 text-center text-text-secondary font-ui">
                                        <svg class="animate-spin w-6 h-6 mx-auto mb-2 text-accent-dark" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        Memproses pratinjau markdown server-side...
                                    </div>
                                </template>
                                <div
                                    x-show="!loadingPreview"
                                    x-html="previewHtml"
                                    class="article-body font-body text-lg leading-relaxed text-text max-w-none"
                                ></div>
                            </div>
                        </div>

                        <x-input-error :messages="$errors->get('body')" class="mt-1" />
                    </div>

                    <!-- Excerpt -->
                    <div>
                        <x-input-label for="excerpt" value="Ringkasan / Excerpt (opsional, otomatis dari paragraf pertama jika kosong)" />
                        <textarea
                            id="excerpt"
                            name="excerpt"
                            rows="2"
                            class="sketch-border sketch-radius-a bg-surface text-text font-body p-3 w-full focus:border-ink focus:ring-0"
                        >{{ old('excerpt', $article->excerpt) }}</textarea>
                        <x-input-error :messages="$errors->get('excerpt')" class="mt-1" />
                    </div>

                    <!-- SEO Fields -->
                    <div class="p-4 bg-surface border border-ink sketch-radius-b space-y-4">
                        <div class="pb-2 border-b border-border">
                            <h3 class="font-heading text-lg font-bold text-ink">Optimasi Mesin Pencari (SEO)</h3>
                            <p class="font-ui text-xs text-text-secondary">Opsional. Kosongkan untuk menggunakan judul dan ringkasan artikel secara otomatis.</p>
                        </div>
                        <div>
                            <x-input-label for="meta_title" value="Judul SEO Khusus (Meta Title)" />
                            <x-text-input
                                id="meta_title"
                                name="meta_title"
                                type="text"
                                class="w-full text-sm"
                                placeholder="Judul yang tampil di mesin pencari..."
                                value="{{ old('meta_title', $article->meta_title) }}"
                            />
                            <x-input-error :messages="$errors->get('meta_title')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="meta_description" value="Deskripsi SEO Khusus (Meta Description)" />
                            <textarea
                                id="meta_description"
                                name="meta_description"
                                rows="2"
                                class="sketch-border sketch-radius-a bg-surface text-text font-body text-sm p-3 w-full focus:border-ink focus:ring-0"
                                placeholder="Deskripsi ringkas yang tampil di hasil pencarian..."
                            >{{ old('meta_description', $article->meta_description) }}</textarea>
                            <x-input-error :messages="$errors->get('meta_description')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Meta & Sidebar Options (1 column) -->
                <div class="space-y-6">
                    <x-sketch-box variant="a" class="p-5 bg-surface space-y-4">
                        <h2 class="font-heading text-xl font-bold text-ink pb-2 border-b border-border">Status & Penerbitan</h2>

                        <!-- Status -->
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select
                                id="status"
                                name="status"
                                class="sketch-border sketch-radius-a bg-surface text-text font-ui text-sm px-3 py-2 min-h-[44px] w-full focus:border-ink focus:ring-0"
                            >
                                <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Draf (Draft)</option>
                                <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Terbitkan (Published)</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-1" />
                        </div>

                        <!-- Published At -->
                        <div>
                            <x-input-label for="published_at" value="Tanggal Publikasi" />
                            <input
                                id="published_at"
                                name="published_at"
                                type="datetime-local"
                                value="{{ old('published_at', $article->published_at ? $article->published_at->format('Y-m-d\TH:i') : '') }}"
                                class="sketch-border sketch-radius-a bg-surface text-text font-body text-sm px-3 py-2 min-h-[44px] w-full focus:border-ink focus:ring-0"
                            />
                            <x-input-error :messages="$errors->get('published_at')" class="mt-1" />
                        </div>

                        <!-- Category -->
                        <div>
                            <x-input-label for="category_id" value="Kategori" />
                            <select
                                id="category_id"
                                name="category_id"
                                class="sketch-border sketch-radius-a bg-surface text-text font-ui text-sm px-3 py-2 min-h-[44px] w-full focus:border-ink focus:ring-0"
                            >
                                <option value="">-- Tanpa Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) old('category_id', $article->category_id) === (string) $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                        </div>

                        <!-- Featured Checkbox -->
                        <div class="pt-2">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="is_featured"
                                    value="1"
                                    {{ old('is_featured', $article->is_featured) ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-2 border-ink text-accent-dark focus:ring-0"
                                />
                                <span class="font-ui text-sm font-bold text-ink">Artikel Unggulan (Featured)</span>
                            </label>
                            <x-input-error :messages="$errors->get('is_featured')" class="mt-1" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="pt-4 border-t border-border space-y-2">
                            <x-button type="submit" variant="primary" class="w-full">
                                Perbarui Artikel
                            </x-button>
                            <a
                                href="{{ route('admin.articles.index') }}"
                                class="block text-center font-ui text-sm text-text-secondary hover:text-ink py-2"
                            >
                                Batal
                            </a>
                        </div>
                    </x-sketch-box>

                    <!-- Featured Image -->
                    <x-sketch-box variant="c" class="p-5 bg-surface space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-border">
                            <h2 class="font-heading text-xl font-bold text-ink">Gambar Utama</h2>
                            <span class="font-ui text-xs text-text-secondary">Featured</span>
                        </div>

                        @if($article->featuredMedia)
                            <div class="space-y-2 pb-3 border-b border-border">
                                <div class="overflow-hidden rounded border border-ink bg-bg">
                                    <img
                                        src="{{ $article->featuredMedia->thumbnail_url }}"
                                        alt="{{ $article->featuredMedia->alt_text }}"
                                        class="w-full h-36 object-cover"
                                    />
                                </div>
                                <div class="font-mono text-xs text-text-secondary flex justify-between">
                                    <span>{{ $article->featuredMedia->width }}x{{ $article->featuredMedia->height }}</span>
                                    <span>{{ round($article->featuredMedia->size / 1024) }} KB</span>
                                </div>

                                <label class="flex items-center space-x-2 pt-1 text-danger font-ui text-xs cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="remove_featured_image"
                                        value="1"
                                        class="rounded border-ink text-danger focus:ring-0"
                                    />
                                    <span class="font-bold">Hapus gambar utama saat ini</span>
                                </label>
                            </div>
                        @endif

                        <div>
                            <x-input-label for="featured_image" :value="$article->featuredMedia ? 'Ganti File Gambar' : 'Pilih File Gambar'" />
                            <input
                                type="file"
                                id="featured_image"
                                name="featured_image"
                                accept="image/jpeg,image/png,image/webp"
                                class="w-full text-xs font-ui text-text file:mr-3 file:py-2 file:px-3 file:rounded file:border-2 file:border-ink file:text-xs file:font-bold file:text-ink file:bg-bg file:cursor-pointer"
                            />
                            <p class="font-ui text-xs text-text-secondary mt-1">
                                JPEG, PNG, WebP maks 5 MB.
                            </p>
                            <x-input-error :messages="$errors->get('featured_image')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="featured_alt_text" value="Alt Text Gambar" />
                            <x-text-input
                                id="featured_alt_text"
                                name="featured_alt_text"
                                type="text"
                                class="w-full text-sm"
                                placeholder="Deskripsi gambar untuk aksesibilitas..."
                                value="{{ old('featured_alt_text', $article->featuredMedia?->alt_text) }}"
                            />
                            <x-input-error :messages="$errors->get('featured_alt_text')" class="mt-1" />
                        </div>
                    </x-sketch-box>

                    <!-- Tags -->
                    <x-sketch-box variant="b" class="p-5 bg-surface space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-border">
                            <h2 class="font-heading text-xl font-bold text-ink">Tag</h2>
                            <span class="font-ui text-xs text-text-secondary">Pilih tag terkait</span>
                        </div>

                        @php
                            $selectedTags = old('tags', $article->tags->pluck('id')->all());
                        @endphp

                        @if($tags->isNotEmpty())
                            <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-1">
                                @foreach($tags as $tag)
                                    <label class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-bg border border-ink sketch-radius-b text-xs font-ui cursor-pointer hover:bg-surface">
                                        <input
                                            type="checkbox"
                                            name="tags[]"
                                            value="{{ $tag->id }}"
                                            {{ in_array($tag->id, $selectedTags) ? 'checked' : '' }}
                                            class="rounded border-ink text-accent-dark focus:ring-0"
                                        />
                                        <span>#{{ $tag->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="font-ui text-xs text-text-secondary">Belum ada tag yang terdaftar.</p>
                        @endif
                        <x-input-error :messages="$errors->get('tags')" class="mt-1" />
                    </x-sketch-box>
                </div>
            </div>
        </form>
    </div>
</x-admin-layout>

