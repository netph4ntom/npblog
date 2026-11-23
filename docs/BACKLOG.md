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
- [ ] SlugGenerator (unik, akhiran numerik, kata terlarang)
- [ ] ReadingTime (`ceil(words/200)`, min 1)
- [ ] MarkdownRenderer (CommonMark + GFM, sanitasi allowlist, anchor heading, syntax highlight)
- [ ] Excerpt fallback otomatis

## T05 — Halaman publik
- [ ] Home, Articles (search + chip kategori + pagination yang menjaga query), Article detail, About, Search
- [ ] Draft dan artikel terjadwal menghasilkan 404; halaman 404/500 bertema
- [ ] Code block: label bahasa + tombol Copy
- [ ] Responsif 360–1920px; Lighthouse mobile ≥ 90 untuk Home dan Article

## T06 — Autentikasi admin
- [ ] `/login` dengan rate limiting dan pesan generik; session diregenerasi; logout
- [ ] `php artisan npblog:create-admin` dan `npblog:reset-password`
- [ ] `/admin/*` guest diarahkan ke login; tidak ada `/register`; halaman admin dan login `noindex`

## T07 — Admin artikel dan kategori
- [ ] Overview: 4 stat, Recent Publications, Continue Writing
- [ ] Daftar artikel (tabel desktop, kartu mobile), filter status/kategori, search, konfirmasi hapus
- [ ] Editor Markdown: judul, slug inline, toolbar, word count, waktu baca, status, kategori, preview server-side
- [ ] Draft/Published/Unpublish; `published_at` default sekarang
- [ ] CRUD kategori; kategori terpakai tidak bisa dihapus tanpa memindahkan artikel

## T08 — Media
- [ ] Upload gambar dengan pipeline: validasi MIME, hapus EXIF, resize 1600px, WebP + thumbnail, nama acak
- [ ] Featured image dengan alt text; pembersihan file lama setelah update sukses
- [ ] Test upload berbahaya (ekstensi ganda, MIME palsu, SVG)

## T09 — Settings dan profil
- [ ] Settings: nama blog, deskripsi, logo, favicon, hero image, bio, foto profil, kontak, URL sosial, blok About, SEO default
- [ ] Profil: nama, email unik, ganti password (butuh password lama)

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
