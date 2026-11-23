# Backlog — NPblog v1.0 (P0)

Kerjakan berurutan. Centang `[x]` setelah `make check` hijau. Tulis catatan singkat di bawah task bila ada keputusan.

## T01 — Bootstrap proyek
- [x] `make bootstrap`, `make up`, halaman Laravel tampil di http://localhost:8080
- [x] Pasang Pest, Pint, Larastan; `make check` berjalan
- [x] Pasang Breeze (Blade) untuk auth, lalu **hapus** route/view registrasi dan reset password
- [x] Tailwind + Vite berjalan lewat `make dev`

## T02 — Fondasi UI
- [x] Token warna sebagai CSS variable (light + dark) dan Tailwind memetakannya
- [x] Font self-host (Caveat, Patrick Hand, DM Sans, JetBrains Mono)
- [x] Layout publik (header, footer, mobile sheet) dan layout admin (sidebar, drawer mobile)
- [x] Theme toggle tanpa flash, tersimpan di localStorage, default mengikuti sistem
- [x] `<x-sketch-box>`, tombol, chip, badge, input, EmptyState, Toast, ConfirmDialog
- [x] Skip link, focus ring, `aria-label`, reduced-motion / contrast

## T03 — Data layer
- [x] Migration, model, factory, seeder: users, categories, tags, article_tag, articles, media, settings, redirects
- [x] Scope `Article::published()`; relasi; FULLTEXT index
- [x] Seeder demo: 1 admin, 4 kategori (Technology, Cybersecurity, Infrastructure, Personal), 12 artikel

## T04 — Services (unit test lengkap)
- [x] SlugGenerator (unik, akhiran numerik, kata terlarang)
- [x] ReadingTime (`ceil(words/200)`, min 1)
- [x] MarkdownRenderer (CommonMark + GFM, sanitasi allowlist, anchor heading, syntax highlight)
- [x] Excerpt fallback otomatis

## T05 — Halaman publik
- [x] Home, Articles (search + chip kategori + pagination yang menjaga query), Article detail, About, Search
- [x] Draft dan artikel terjadwal menghasilkan 404; halaman 404/500 bertema
- [x] Code block: label bahasa + tombol Copy
- [x] Responsif 360–1920px; Lighthouse mobile ≥ 90 untuk Home dan Article

## T06 — Autentikasi admin
- [x] `/login` dengan rate limiting dan pesan generik; session diregenerasi; logout
- [x] `php artisan npblog:create-admin` dan `npblog:reset-password`
- [x] `/admin/*` guest diarahkan ke login; tidak ada `/register`; halaman admin dan login `noindex`

## T07 — Admin artikel dan kategori
- [x] Overview: 4 stat, Recent Publications, Continue Writing
- [x] Daftar artikel (tabel desktop, kartu mobile), filter status/kategori, search, konfirmasi hapus
- [x] Editor Markdown: judul, slug inline, toolbar, word count, waktu baca, status, kategori, preview server-side
- [x] Draft/Published/Unpublish; `published_at` default sekarang
- [x] CRUD kategori; kategori terpakai tidak bisa dihapus tanpa memindahkan artikel

## T08 — Media
- [x] Upload gambar dengan pipeline: validasi MIME, hapus EXIF, resize 1600px, WebP + thumbnail, nama acak
- [x] Featured image dengan alt text; pembersihan file lama setelah update sukses
- [x] Test upload berbahaya (ekstensi ganda, MIME palsu, SVG)

## T09 — Settings dan profil
- [x] Settings: nama blog, deskripsi, logo, favicon, hero image, bio, foto profil, kontak, URL sosial, blok About, SEO default
- [x] Profil: nama, email unik, ganti password (butuh password lama)

## T10 — SEO dasar
- [ ] Title, meta description, canonical, Open Graph per artikel (fallback OG default)
- [ ] `sitemap.xml` dan `robots.txt` (tanpa draft); admin, login, search `noindex`

## T11 — Keamanan dan pengujian
- [ ] Header keamanan (CSP, nosniff, referrer, permissions, HSTS di produksi)
- [ ] Security tests: guest ke admin, CSRF, XSS di judul/body/alt, SQL injection, upload berbahaya, rate limit login
- [ ] Audit aksesibilitas otomatis (axe/Lighthouse) dan uji keyboard

## T12 — Deploy
- [ ] Dockerfile/compose produksi (tanpa mount source, `opcache` strict), HTTPS, cookie Secure
- [ ] Backup terjadwal (`scripts/backup.sh`) dan dokumen restore yang diuji
- [ ] GitHub Actions deploy via SSH dengan rollback ke release sebelumnya

## Pertanyaan terbuka (isi bila terblokir; tanya Dapit)
- Bahasa UI? Warna tombol primer? Soft/hard delete? Versi Tailwind? Domain/hosting? Font body artikel? Intensitas doodle?

## Catatan keputusan
- T01: Mengunci platform PHP ke 8.3.37 di composer.json agar dependensi kompatibel dengan PHP 8.3 di container. Menyesuaikan phpunit.xml dengan force="true" dan target test pada Makefile dengan APP_ENV=testing. Breeze (Blade) dipasang dan rute/view registrasi serta reset password dihapus dengan aman sesuai spesifikasi keamanan blog personal single-admin.
- T02: Fondasi UI hand-drawn diimplementasikan sesuai `docs/design-system.md`: token warna CSS variable dipetakan di Tailwind, font woff2 di-self-host di `public/fonts/`, layout publik (header, footer, mobile sheet) dan admin (sidebar, drawer mobile), theme toggle anti-flash, komponen hand-drawn (`<x-sketch-box>`, `<x-button>`, `<x-chip>`, `<x-badge>`, `<x-text-input>`, `<x-empty-state>`, `<x-toast>`, `<x-confirm-dialog>`), serta aksesibilitas (skip link, focus ring 3px offset 3px, aria-label, media query prefers-reduced-motion dan prefers-contrast).
- T03: Skema data layer dibuat lengkap dengan migration, model, factory, dan seeder. Relasi Eloquent didefinisikan untuk semua entitas (User, Category, Tag, Media, Article, Setting, Redirect). Scope `Article::published()` menyaring `status = 'published'` dan `published_at <= now()`. Index FULLTEXT dibuat pada MySQL untuk pencarian artikel. Seeder demo mengisi 1 admin, 4 kategori, 8 tag, dan 12 artikel realistis.
- T04: Core services diimplementasikan dengan unit test lengkap: SlugGenerator menangani bentrok slug dengan akhiran numerik `-2`, memeriksa data soft-deleted, dan memfilter reserved route slugs. ReadingTime menghitung kata berbasis token Unicode setelah membersihkan markup dengan rumus `ceil(words / 200)` dan minimum 1 menit. ExcerptGenerator membatasi ringkasan eksplisit maksimal 300 karakter serta menyediakan fallback otomatis dari paragraf pertama (~160 karakter). MarkdownRenderer mengintegrasikan CommonMark + GFM (tabel, task list, strikethrough), HeadingPermalinkExtension untuk anchor heading ber-`id`, HighlightedCodeRenderer (berbasis scrivo/highlight.php) untuk syntax highlighting dengan kelas `hljs`, serta sanitasi allowlist aman via symfony/html-sanitizer.
- T05: Halaman publik dibangun lengkap dengan pemisahan controller di `Http/Controllers/Public/` (HomeController, ArticleController, AboutController, SearchController). Seluruh query publik menggunakan scope `Article::published()`. Rute artikel detail mendukung redirect 301 untuk slug lama dari tabel redirects jika artikel terbit, serta mengembalikan 404 jika artikel masih draft atau terjadwal. Pencarian artikel dioptimalkan dengan scope `Article::scopeSearch()` yang menggunakan MySQL FULLTEXT di lingkungan produksi dan fallback LIKE di pengujian SQLite/transaksi uncommitted. Halaman error 404 dan 500 dibuat bertema hand-drawn dengan sketch box dan tombol aksi. Blok kode dilengkapi wrapper hand-drawn, label bahasa, dan tombol Copy bertenaga Clipboard API.
- T06: Autentikasi admin diperkuat sesuai aturan keamanan AGENTS.md. Rute login menerapkan rate limiting (5 percobaan, throttle) dan pesan error generik yang identik untuk email yang tidak ada maupun password salah untuk mencegah enumerasi pengguna. Session diregenerasi saat login sukses. Perintah Artisan `npblog:create-admin` dan `npblog:reset-password` dibuat untuk manajemen akun administrator privat tanpa rute registrasi publik. Seluruh rute `/admin` dan `/admin/*` dilindungi middleware auth, dan meta tag `<meta name="robots" content="noindex, nofollow">` dipasang pada layout admin dan guest (login).
- T07: Modul admin artikel dan kategori selesai diimplementasikan. Dashboard menyajikan 4 kartu statistik (Total Artikel, Terbit, Draf, Kategori), daftar publikasi terbaru, dan draf untuk melanjutkan penulisan. Manajemen artikel menyediakan tabel desktop dan kartu mobile, filter status/kategori, pencarian judul, serta konfirmasi hapus (soft delete). Editor Markdown dilengkapi toolbar, slug inline otomatis, metrik dinamis (word count dan reading time), serta server-side preview via endpoint POST /admin/articles/preview agar sanitasi HTML dan syntax highlighting identik dengan tampilan publik. Alur draft <-> publish didukung penuh, termasuk pengalihan 301 otomatis ke tabel redirects saat slug artikel terbit diubah. CRUD kategori mendukung pembuatan slug otomatis dan memblokir penghapusan kategori bila masih memiliki relasi artikel terkait.
- T08: Layanan MediaProcessor diimplementasikan dengan pipeline lengkap: validasi ketat server-side MIME type via fileinfo (hanya JPEG, PNG, WebP diizinkan), pembersihan metadata EXIF dengan penyesuaian rotasi kamera otomatis, resize proporsional maksimal 1600px, konversi ke WebP, pembuatan thumbnail responsif 400px dan 800px, serta penyimpanan nama acak 40 karakter. Proteksi keamanan menghentikan berkas > 5 MB, berkas SVG/XSS, ekstensi ganda berbahaya (.php.png), dan MIME palsu. Fitur featured image terintegrasi pada pembuatan dan penyuntingan artikel, dengan otomatisasi pembersihan berkas lama saat diganti atau dihapus. Halaman admin galeri media (/admin/media) menyediakan unggah langsung, penyuntingan alt text, dan tombol penyalinan sintaks Markdown atau URL.
- T09: Modul Settings dan Profil admin selesai diimplementasikan. Halaman pengaturan (/admin/settings) mengelola identitas blog (nama, tagline, deskripsi, logo, favicon, hero banner), profil penulis (nama, bio, foto profil, email kontak), daftar tautan sosial (GitHub, X/Twitter, LinkedIn, YouTube, website), konten Markdown blok About, serta metadata SEO default (title dan description). Berkas gambar dioptimasi otomatis via MediaProcessor dan mendukung opsi penghapusan. Halaman profil (/profile dan /admin/profile) diperbarui menggunakan tema hand-drawn sketch box, dengan validasi email unik dan keharusan memasukkan kata sandi lama sebelum mengganti kata sandi.
