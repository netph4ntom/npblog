<x-admin-layout header="Edit Artikel">
    <div
        class="space-y-6"
        x-data="{
            tab: 'edit',
            body: @js(old('body', $article->body)),
            previewHtml: '',
            loadingPreview: false,
            wordCount: 0,
            readingTime: 1,
            updateMetrics() {
                const text = (this.body || '').trim();
                const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
                this.wordCount = words;
                this.readingTime = Math.max(1, Math.ceil(words / 200));
            },
            insertToolbar(prefix, suffix = '') {
                const el = this.$refs.bodyTextarea;
                if (!el) return;
                const start = el.selectionStart;
                const end = el.selectionEnd;
                const sel = el.value.substring(start, end);
                const replace = prefix + sel + suffix;
                el.setRangeText(replace, start, end, 'select');
                this.body = el.value;
                this.updateMetrics();
                el.focus();
            },
            async fetchPreview() {
                this.tab = 'preview';
                this.loadingPreview = true;
                try {
                    const res = await fetch('{{ route('admin.articles.preview') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ body: this.body })
                    });
                    const data = await res.json();
                    this.previewHtml = data.html || '<p class=\"text-text-secondary italic\">Tidak ada konten untuk dipratinjau.</p>';
                } catch (e) {
                    this.previewHtml = '<p class=\"text-danger\">Gagal memuat pratinjau markdown server-side.</p>';
                } finally {
                    this.loadingPreview = false;
                }
            }
        }"
        x-init="updateMetrics()"
    >
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-border">
            <div>
                <h1 class="font-heading text-4xl font-bold text-ink">Edit Artikel</h1>
                <p class="font-ui text-base text-text-secondary">Perbarui artikel &ldquo;{{ $article->title }}&rdquo;.</p>
            </div>
            <div class="flex items-center space-x-4">
                @if($article->status === 'published')
                    <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="font-ui text-sm text-accent-dark hover:underline">
                        Lihat Artikel Publik &rarr;
                    </a>
                @endif
                <a href="{{ route('admin.articles.index') }}" class="font-ui text-sm text-text-secondary hover:text-ink">
                    &larr; Kembali ke Daftar
                </a>
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
                            class="w-full text-xl font-bold font-heading"
                            value="{{ old('title', $article->title) }}"
                            required
                        />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>

                    <!-- Slug Inline -->
                    <div>
                        <x-input-label for="slug" value="Slug URL" />
                        <div class="flex items-center">
                            <span class="font-mono text-sm text-text-secondary px-3 py-2 bg-surface border-y-2 border-l-2 border-ink rounded-l-lg select-none">
                                /articles/
                            </span>
                            <x-text-input
                                id="slug"
                                name="slug"
                                type="text"
                                class="w-full font-mono text-sm rounded-l-none"
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

                        <!-- Editor Tabs & Toolbar Container -->
                        <div class="bg-surface border-2 border-ink sketch-radius-b sketch-shadow overflow-hidden">
                            <!-- Header with Tabs and Toolbar -->
                            <div class="bg-bg border-b-2 border-ink p-2 flex flex-wrap items-center justify-between gap-2">
                                <!-- Mode Switcher (Edit / Preview) -->
                                <div class="inline-flex rounded-lg border border-ink p-0.5 bg-surface font-ui text-sm">
                                    <button
                                        type="button"
                                        @click="tab = 'edit'"
                                        class="px-3 py-1 rounded transition-colors font-semibold"
                                        :class="tab === 'edit' ? 'bg-accent-dark text-on-accent' : 'text-text-secondary hover:text-ink'"
                                    >
                                        Markdown Editor
                                    </button>
                                    <button
                                        type="button"
                                        @click="fetchPreview()"
                                        class="px-3 py-1 rounded transition-colors font-semibold"
                                        :class="tab === 'preview' ? 'bg-accent-dark text-on-accent' : 'text-text-secondary hover:text-ink'"
                                    >
                                        Pratinjau Server
                                    </button>
                                </div>

                                <!-- Minimalist Markdown Toolbar (Visible in Edit mode) -->
                                <div x-show="tab === 'edit'" class="flex items-center space-x-1 font-mono text-sm">
                                    <button
                                        type="button"
                                        @click="insertToolbar('**', '**')"
                                        title="Tebal (Bold)"
                                        class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface font-bold min-h-[32px] min-w-[32px]"
                                    >
                                        B
                                    </button>
                                    <button
                                        type="button"
                                        @click="insertToolbar('*', '*')"
                                        title="Miring (Italic)"
                                        class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface italic min-h-[32px] min-w-[32px]"
                                    >
                                        I
                                    </button>
                                    <button
                                        type="button"
                                        @click="insertToolbar('\n## ', '\n')"
                                        title="Heading 2"
                                        class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface font-bold min-h-[32px]"
                                    >
                                        H2
                                    </button>
                                    <button
                                        type="button"
                                        @click="insertToolbar('\n### ', '\n')"
                                        title="Heading 3"
                                        class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface font-bold min-h-[32px]"
                                    >
                                        H3
                                    </button>
                                    <button
                                        type="button"
                                        @click="insertToolbar('[', '](url)')"
                                        title="Tautan (Link)"
                                        class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[32px]"
                                    >
                                        Link
                                    </button>
                                    <button
                                        type="button"
                                        @click="insertToolbar('`', '`')"
                                        title="Inline Code"
                                        class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[32px]"
                                    >
                                        &lt;/&gt;
                                    </button>
                                    <button
                                        type="button"
                                        @click="insertToolbar('\n> ', '\n')"
                                        title="Kutipan (Quote)"
                                        class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[32px]"
                                    >
                                        &ldquo;
                                    </button>
                                    <button
                                        type="button"
                                        @click="insertToolbar('\n- ', '\n')"
                                        title="Daftar (List)"
                                        class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[32px]"
                                    >
                                        &bull; List
                                    </button>
                                </div>
                            </div>

                            <!-- Edit Area -->
                            <div x-show="tab === 'edit'">
                                <textarea
                                    id="body"
                                    name="body"
                                    x-ref="bodyTextarea"
                                    x-model="body"
                                    @input="updateMetrics()"
                                    rows="18"
                                    class="w-full p-4 font-mono text-sm bg-surface text-text focus:outline-none border-0 resize-y"
                                    required
                                >{{ old('body', $article->body) }}</textarea>
                            </div>

                            <!-- Preview Area -->
                            <div x-show="tab === 'preview'" class="p-6 bg-surface min-h-[400px] overflow-y-auto">
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
                                    class="prose prose-lg dark:prose-invert max-w-none font-body"
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
                            class="sketch-border sketch-radius-a bg-surface text-text font-body p-3 w-full"
                        >{{ old('excerpt', $article->excerpt) }}</textarea>
                        <x-input-error :messages="$errors->get('excerpt')" class="mt-1" />
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
                                class="sketch-border sketch-radius-a bg-surface text-text font-ui text-sm px-3 py-2 min-h-[44px] w-full"
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
                                class="sketch-border sketch-radius-a bg-surface text-text font-body text-sm px-3 py-2 min-h-[44px] w-full"
                            />
                            <x-input-error :messages="$errors->get('published_at')" class="mt-1" />
                        </div>

                        <!-- Category -->
                        <div>
                            <x-input-label for="category_id" value="Kategori" />
                            <select
                                id="category_id"
                                name="category_id"
                                class="sketch-border sketch-radius-a bg-surface text-text font-ui text-sm px-3 py-2 min-h-[44px] w-full"
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
                                class="w-full text-xs font-ui text-text file:mr-3 file:py-2 file:px-3 file:rounded file:border-2 file:border-ink file:text-xs file:font-bold file:bg-bg file:cursor-pointer"
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

