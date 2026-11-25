<x-admin-layout header="Tulis Artikel Baru">
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
            initialBody: @js(old('body', '')),
            initialTitle: @js(old('title', '')),
            initialSlug: @js(old('slug', '')),
            previewUrl: '{{ route('admin.articles.preview') }}',
            csrfToken: '{{ csrf_token() }}'
        })"
        x-init="updateMetrics()"
        x-effect="document.body.classList.toggle('overflow-hidden', isFullscreen)"
    >
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-border">
            <div>
                <h1 class="font-heading text-4xl font-bold text-ink">Tulis Artikel Baru</h1>
                <p class="font-ui text-base text-text-secondary">Buat artikel baru dengan editor Markdown.</p>
            </div>
            <a href="{{ route('admin.articles.index') }}" class="font-ui text-sm text-text-secondary hover:text-ink min-h-[44px] inline-flex items-center self-start sm:self-auto">
                &larr; Kembali ke Daftar Artikel
            </a>
        </div>

        <form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

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
                            placeholder="Tuliskan judul artikel yang menarik..."
                            value="{{ old('title') }}"
                            required
                            autofocus
                        />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>

                    <!-- Slug Inline -->
                    <div>
                        <x-input-label for="slug" value="Slug URL (opsional, otomatis dibuat bila kosong)" />
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
                                placeholder="judul-artikel-otomatis"
                                value="{{ old('slug') }}"
                            />
                        </div>
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
                            <!-- Header with Tabs and Toolbar -->
                            <div class="bg-bg border-b-2 border-ink p-2 flex flex-wrap items-center justify-between gap-2 shrink-0">
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
                                        class="px-3 py-1 rounded transition-colors font-semibold flex items-center space-x-1.5"
                                        :class="tab === 'preview' ? 'bg-accent-dark text-on-accent' : 'text-text-secondary hover:text-ink'"
                                        title="Lihat hasil format teks & layout artikel"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>Pratinjau Server</span>
                                    </button>
                                </div>

                                <!-- Minimalist Markdown Toolbar (Visible in Edit mode) -->
                                <div class="flex flex-wrap items-center gap-1 font-mono text-sm">
                                    <div x-show="tab === 'edit'" class="flex flex-wrap items-center gap-1">
                                        <button
                                            type="button"
                                            @click="insertToolbar('**', '**', 'teks tebal')"
                                            title="Tebal (Bold)"
                                            class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface font-bold min-h-[38px] min-w-[36px]"
                                        >
                                            B
                                        </button>
                                        <button
                                            type="button"
                                            @click="insertToolbar('*', '*', 'teks miring')"
                                            title="Miring (Italic)"
                                            class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface italic min-h-[38px] min-w-[36px]"
                                        >
                                            I
                                        </button>
                                        <button
                                            type="button"
                                            @click="insertHeading(1)"
                                            title="Heading 1"
                                            class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface font-bold min-h-[38px] min-w-[36px]"
                                        >
                                            H1
                                        </button>
                                        <button
                                            type="button"
                                            @click="insertHeading(2)"
                                            title="Heading 2"
                                            class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface font-bold min-h-[38px] min-w-[36px]"
                                        >
                                            H2
                                        </button>
                                        <button
                                            type="button"
                                            @click="insertHeading(3)"
                                            title="Heading 3"
                                            class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface font-bold min-h-[38px] min-w-[36px]"
                                        >
                                            H3
                                        </button>
                                        <button
                                            type="button"
                                            @click="insertLink()"
                                            title="Tautan (Link) [Teks](URL)"
                                            class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[38px]"
                                        >
                                            Link
                                        </button>
                                        <button
                                            type="button"
                                            @click="insertCode()"
                                            title="Kode (Inline / Block)"
                                            class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[38px]"
                                        >
                                            &lt;/&gt;
                                        </button>
                                        <button
                                            type="button"
                                            @click="insertToolbar('\n> ', '\n', 'Kutipan...')"
                                            title="Kutipan (Quote)"
                                            class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[38px] min-w-[36px]"
                                        >
                                            &ldquo;
                                        </button>
                                        <button
                                            type="button"
                                            @click="insertToolbar('\n- ', '\n', 'Item daftar')"
                                            title="Daftar (List)"
                                            class="px-2 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[38px]"
                                        >
                                            &bull; List
                                        </button>
                                    </div>

                                    <!-- Separator before Fullscreen -->
                                    <span class="hidden sm:inline-block w-px h-5 bg-border mx-1"></span>

                                    <!-- Fullscreen / Expand Button -->
                                    <button
                                        type="button"
                                        @click="toggleFullscreen()"
                                        :title="isFullscreen ? 'Perkecil Editor (Esc)' : 'Perbesar Layar Penuh (Fullscreen)'"
                                        class="px-2.5 py-1 rounded border border-border hover:border-ink hover:bg-surface min-h-[38px] flex items-center space-x-1.5 transition-colors"
                                        :class="isFullscreen ? 'bg-accent-dark text-on-accent font-bold' : 'text-text hover:text-ink'"
                                        aria-label="Perbesar layar penuh"
                                    >
                                        <svg x-show="!isFullscreen" class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                        </svg>
                                        <svg x-show="isFullscreen" x-cloak class="w-4 h-4 stroke-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9L4 4m0 0h4m-4 0v4m16 5l-5 5m5-5h-4m4 0v4M9 15l-5 5m0 0v-4m0 4h4m11-16l-5 5m5-5v4m0-4h-4" />
                                        </svg>
                                        <span class="font-ui text-xs" x-text="isFullscreen ? 'Perkecil' : 'Perbesar'"></span>
                                    </button>
                                </div>
                            </div>

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
                                    placeholder="Tuliskan isi artikel dalam format Markdown..."
                                    required
                                >{{ old('body') }}</textarea>
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
                            placeholder="Ringkasan singkat tulisan untuk kartu artikel dan SEO..."
                        >{{ old('excerpt') }}</textarea>
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
                                value="{{ old('meta_title') }}"
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
                            >{{ old('meta_description') }}</textarea>
                            <x-input-error :messages="$errors->get('meta_description')" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Meta & Sidebar Options (1 column) -->
                <div class="space-y-6">
                    <x-sketch-box variant="a" class="p-5 bg-surface space-y-4">
                        <h2 class="font-heading text-xl font-bold text-ink pb-2 border-b border-border">Publikasi</h2>

                        <!-- Status -->
                        <div>
                            <x-input-label for="status" value="Status" />
                            <select
                                id="status"
                                name="status"
                                class="sketch-border sketch-radius-a bg-surface text-text font-ui text-sm px-3 py-2 min-h-[44px] w-full focus:border-ink focus:ring-0"
                            >
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draf (Draft)</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Terbitkan Sekarang (Published)</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-1" />
                        </div>

                        <!-- Published At -->
                        <div>
                            <x-input-label for="published_at" value="Tanggal Publikasi (opsional)" />
                            <input
                                id="published_at"
                                name="published_at"
                                type="datetime-local"
                                value="{{ old('published_at') }}"
                                class="sketch-border sketch-radius-a bg-surface text-text font-body text-sm px-3 py-2 min-h-[44px] w-full focus:border-ink focus:ring-0"
                            />
                            <p class="font-ui text-xs text-text-secondary mt-1">Kosongkan untuk menggunakan waktu saat ini bila berstatus terbit.</p>
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
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (string) old('category_id') === (string) $cat->id ? 'selected' : '' }}>
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
                                    {{ old('is_featured') ? 'checked' : '' }}
                                    class="w-5 h-5 rounded border-2 border-ink text-accent-dark focus:ring-0"
                                />
                                <span class="font-ui text-sm font-bold text-ink">Artikel Unggulan (Featured)</span>
                            </label>
                            <x-input-error :messages="$errors->get('is_featured')" class="mt-1" />
                        </div>

                        <!-- Submit Buttons -->
                        <div class="pt-4 border-t border-border space-y-2">
                            <x-button type="submit" variant="primary" class="w-full">
                                Simpan Artikel
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

                        <div>
                            <x-input-label for="featured_image" value="Pilih File Gambar" />
                            <input
                                type="file"
                                id="featured_image"
                                name="featured_image"
                                accept="image/jpeg,image/png,image/webp"
                                class="w-full text-xs font-ui text-text file:mr-3 file:py-2 file:px-3 file:rounded file:border-2 file:border-ink file:text-xs file:font-bold file:text-ink file:bg-bg file:cursor-pointer"
                            />
                            <p class="font-ui text-xs text-text-secondary mt-1">
                                JPEG, PNG, WebP maks 5 MB. Otomatis dikonversi ke WebP dan di-resize maks 1600px.
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
                                value="{{ old('featured_alt_text') }}"
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

                        @if($tags->isNotEmpty())
                            <div class="flex flex-wrap gap-2 max-h-48 overflow-y-auto p-1">
                                @foreach($tags as $tag)
                                    <label class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-bg border border-ink sketch-radius-b text-xs font-ui cursor-pointer hover:bg-surface">
                                        <input
                                            type="checkbox"
                                            name="tags[]"
                                            value="{{ $tag->id }}"
                                            {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}
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

