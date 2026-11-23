<x-admin-layout header="Settings">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="font-heading text-4xl font-bold text-ink">Pengaturan Blog</h1>
                <p class="font-ui text-lg text-text-secondary">Kelola identitas situs, profil penulis, tautan sosial, dan SEO default.</p>
            </div>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            <!-- 1. Identitas Blog -->
            <x-sketch-box variant="a" class="p-6 bg-surface space-y-6">
                <div class="pb-3 border-b border-border">
                    <h2 class="font-heading text-2xl font-bold text-ink">Identitas & Visual Blog</h2>
                    <p class="font-ui text-sm text-text-secondary">Nama, deskripsi, logo, favicon, dan banner utama.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="blog_name" value="Nama Blog *" />
                        <x-text-input
                            id="blog_name"
                            name="blog_name"
                            type="text"
                            class="w-full"
                            value="{{ old('blog_name', $settings['blog_name']) }}"
                            required
                        />
                        <x-input-error :messages="$errors->get('blog_name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="blog_tagline" value="Tagline Blog" />
                        <x-text-input
                            id="blog_tagline"
                            name="blog_tagline"
                            type="text"
                            class="w-full"
                            value="{{ old('blog_tagline', $settings['blog_tagline']) }}"
                        />
                        <x-input-error :messages="$errors->get('blog_tagline')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="blog_description" value="Deskripsi Blog" />
                    <textarea
                        id="blog_description"
                        name="blog_description"
                        rows="3"
                        class="sketch-border sketch-radius-a bg-surface text-text font-body p-3 w-full"
                    >{{ old('blog_description', $settings['blog_description']) }}</textarea>
                    <x-input-error :messages="$errors->get('blog_description')" class="mt-1" />
                </div>

                <!-- Images Row: Logo, Favicon, Hero Banner -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-border">
                    <!-- Logo -->
                    <div class="space-y-3">
                        <x-input-label for="logo" value="Logo Blog" />
                        @if(!empty($settings['logo']))
                            <div class="p-2 border border-ink rounded bg-bg flex items-center justify-between">
                                <img src="{{ $settings['logo'] }}" alt="Logo saat ini" class="max-h-12 object-contain" />
                                <label class="flex items-center space-x-1.5 text-xs text-danger font-ui cursor-pointer">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-ink text-danger" />
                                    <span>Hapus</span>
                                </label>
                            </div>
                        @endif
                        <input
                            type="file"
                            id="logo"
                            name="logo"
                            accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs font-ui text-text file:mr-2 file:py-1.5 file:px-2.5 file:rounded file:border file:border-ink file:text-xs file:bg-bg file:cursor-pointer"
                        />
                        <x-input-error :messages="$errors->get('logo')" class="mt-1" />
                    </div>

                    <!-- Favicon -->
                    <div class="space-y-3">
                        <x-input-label for="favicon" value="Favicon" />
                        @if(!empty($settings['favicon']))
                            <div class="p-2 border border-ink rounded bg-bg flex items-center justify-between">
                                <img src="{{ $settings['favicon'] }}" alt="Favicon saat ini" class="w-8 h-8 object-contain" />
                                <label class="flex items-center space-x-1.5 text-xs text-danger font-ui cursor-pointer">
                                    <input type="checkbox" name="remove_favicon" value="1" class="rounded border-ink text-danger" />
                                    <span>Hapus</span>
                                </label>
                            </div>
                        @endif
                        <input
                            type="file"
                            id="favicon"
                            name="favicon"
                            accept=".ico,image/png,image/webp,image/x-icon"
                            class="w-full text-xs font-ui text-text file:mr-2 file:py-1.5 file:px-2.5 file:rounded file:border file:border-ink file:text-xs file:bg-bg file:cursor-pointer"
                        />
                        <x-input-error :messages="$errors->get('favicon')" class="mt-1" />
                    </div>

                    <!-- Hero Banner -->
                    <div class="space-y-3">
                        <x-input-label for="hero_image" value="Hero Banner" />
                        @if(!empty($settings['hero_image']))
                            <div class="p-2 border border-ink rounded bg-bg flex items-center justify-between">
                                <img src="{{ $settings['hero_image'] }}" alt="Hero saat ini" class="max-h-12 w-24 object-cover rounded" />
                                <label class="flex items-center space-x-1.5 text-xs text-danger font-ui cursor-pointer">
                                    <input type="checkbox" name="remove_hero_image" value="1" class="rounded border-ink text-danger" />
                                    <span>Hapus</span>
                                </label>
                            </div>
                        @endif
                        <input
                            type="file"
                            id="hero_image"
                            name="hero_image"
                            accept="image/jpeg,image/png,image/webp"
                            class="w-full text-xs font-ui text-text file:mr-2 file:py-1.5 file:px-2.5 file:rounded file:border file:border-ink file:text-xs file:bg-bg file:cursor-pointer"
                        />
                        <x-input-error :messages="$errors->get('hero_image')" class="mt-1" />
                    </div>
                </div>
            </x-sketch-box>

            <!-- 2. Profil Penulis & Kontak -->
            <x-sketch-box variant="b" class="p-6 bg-surface space-y-6">
                <div class="pb-3 border-b border-border">
                    <h2 class="font-heading text-2xl font-bold text-ink">Profil Penulis & Kontak</h2>
                    <p class="font-ui text-sm text-text-secondary">Informasi pemilik blog yang ditampilkan pada header dan footer.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="author_name" value="Nama Penulis" />
                        <x-text-input
                            id="author_name"
                            name="author_name"
                            type="text"
                            class="w-full"
                            value="{{ old('author_name', $settings['author_name']) }}"
                        />
                        <x-input-error :messages="$errors->get('author_name')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="contact_email" value="Email Kontak Publik" />
                        <x-text-input
                            id="contact_email"
                            name="contact_email"
                            type="email"
                            class="w-full"
                            value="{{ old('contact_email', $settings['contact_email']) }}"
                        />
                        <x-input-error :messages="$errors->get('contact_email')" class="mt-1" />
                    </div>
                </div>

                <div>
                    <x-input-label for="author_bio" value="Bio Singkat Penulis" />
                    <textarea
                        id="author_bio"
                        name="author_bio"
                        rows="3"
                        class="sketch-border sketch-radius-a bg-surface text-text font-body p-3 w-full"
                    >{{ old('author_bio', $settings['author_bio']) }}</textarea>
                    <x-input-error :messages="$errors->get('author_bio')" class="mt-1" />
                </div>

                <!-- Foto Profil -->
                <div class="pt-2">
                    <x-input-label for="profile_image" value="Foto Profil / Avatar" />
                    @if(!empty($settings['profile_image']))
                        <div class="mb-3 p-2 border border-ink rounded bg-bg inline-flex items-center space-x-3">
                            <img src="{{ $settings['profile_image'] }}" alt="Foto profil saat ini" class="w-12 h-12 rounded-full object-cover border border-ink" />
                            <label class="flex items-center space-x-1.5 text-xs text-danger font-ui cursor-pointer">
                                <input type="checkbox" name="remove_profile_image" value="1" class="rounded border-ink text-danger" />
                                <span>Hapus Foto</span>
                            </label>
                        </div>
                    @endif
                    <input
                        type="file"
                        id="profile_image"
                        name="profile_image"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full max-w-sm text-xs font-ui text-text file:mr-2 file:py-1.5 file:px-2.5 file:rounded file:border file:border-ink file:text-xs file:bg-bg file:cursor-pointer"
                    />
                    <x-input-error :messages="$errors->get('profile_image')" class="mt-1" />
                </div>
            </x-sketch-box>

            <!-- 3. Tautan Media Sosial -->
            <x-sketch-box variant="c" class="p-6 bg-surface space-y-6">
                <div class="pb-3 border-b border-border">
                    <h2 class="font-heading text-2xl font-bold text-ink">Tautan Media Sosial</h2>
                    <p class="font-ui text-sm text-text-secondary">Tautan profil yang ditampilkan pada halaman About dan footer situs.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="social_github" value="GitHub URL" />
                        <x-text-input
                            id="social_github"
                            name="social_links[github]"
                            type="url"
                            class="w-full text-sm font-mono"
                            placeholder="https://github.com/username"
                            value="{{ old('social_links.github', $settings['social_links']['github'] ?? '') }}"
                        />
                    </div>
                    <div>
                        <x-input-label for="social_twitter" value="X / Twitter URL" />
                        <x-text-input
                            id="social_twitter"
                            name="social_links[twitter]"
                            type="url"
                            class="w-full text-sm font-mono"
                            placeholder="https://x.com/username"
                            value="{{ old('social_links.twitter', $settings['social_links']['twitter'] ?? '') }}"
                        />
                    </div>
                    <div>
                        <x-input-label for="social_linkedin" value="LinkedIn URL" />
                        <x-text-input
                            id="social_linkedin"
                            name="social_links[linkedin]"
                            type="url"
                            class="w-full text-sm font-mono"
                            placeholder="https://linkedin.com/in/username"
                            value="{{ old('social_links.linkedin', $settings['social_links']['linkedin'] ?? '') }}"
                        />
                    </div>
                    <div>
                        <x-input-label for="social_youtube" value="YouTube URL" />
                        <x-text-input
                            id="social_youtube"
                            name="social_links[youtube]"
                            type="url"
                            class="w-full text-sm font-mono"
                            placeholder="https://youtube.com/@username"
                            value="{{ old('social_links.youtube', $settings['social_links']['youtube'] ?? '') }}"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="social_website" value="Situs Web Personal" />
                        <x-text-input
                            id="social_website"
                            name="social_links[website]"
                            type="url"
                            class="w-full text-sm font-mono"
                            placeholder="https://domainanda.com"
                            value="{{ old('social_links.website', $settings['social_links']['website'] ?? '') }}"
                        />
                    </div>
                </div>
            </x-sketch-box>

            <!-- 4. Halaman About & SEO Default -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- About Block -->
                <x-sketch-box variant="a" class="p-6 bg-surface space-y-4">
                    <div class="pb-2 border-b border-border">
                        <h2 class="font-heading text-2xl font-bold text-ink">Konten Halaman About</h2>
                        <p class="font-ui text-sm text-text-secondary">Tuliskan konten About me dalam format Markdown.</p>
                    </div>

                    <div>
                        <textarea
                            id="about_content"
                            name="about_content"
                            rows="10"
                            class="sketch-border sketch-radius-b bg-surface text-text font-mono text-sm p-4 w-full"
                            placeholder="Tuliskan cerita mengenai Anda, keahlian, dan blog ini..."
                        >{{ old('about_content', $settings['about_content']) }}</textarea>
                        <x-input-error :messages="$errors->get('about_content')" class="mt-1" />
                    </div>
                </x-sketch-box>

                <!-- SEO Default Block -->
                <x-sketch-box variant="b" class="p-6 bg-surface space-y-4">
                    <div class="pb-2 border-b border-border">
                        <h2 class="font-heading text-2xl font-bold text-ink">SEO Default</h2>
                        <p class="font-ui text-sm text-text-secondary">Metadata bawaan untuk halaman utama dan fallback Open Graph.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <x-input-label for="seo_default_title" value="Default Meta Title" />
                            <x-text-input
                                id="seo_default_title"
                                name="seo_default_title"
                                type="text"
                                class="w-full text-sm"
                                value="{{ old('seo_default_title', $settings['seo_default_title']) }}"
                            />
                            <x-input-error :messages="$errors->get('seo_default_title')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="seo_default_description" value="Default Meta Description" />
                            <textarea
                                id="seo_default_description"
                                name="seo_default_description"
                                rows="5"
                                class="sketch-border sketch-radius-a bg-surface text-text font-body text-sm p-3 w-full"
                            >{{ old('seo_default_description', $settings['seo_default_description']) }}</textarea>
                            <x-input-error :messages="$errors->get('seo_default_description')" class="mt-1" />
                        </div>
                    </div>
                </x-sketch-box>
            </div>

            <!-- Submit Action -->
            <div class="pt-4 flex items-center justify-end">
                <x-button type="submit" variant="primary" size="lg">
                    Simpan Semua Pengaturan
                </x-button>
            </div>
        </form>
    </div>
</x-admin-layout>

