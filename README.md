# NPblog

Blog personal minimalis bergaya **hand-drawn** dengan CMS admin privat (single-admin), pipeline Markdown & media WebP, serta dukungan **Progressive Web App (PWA)**.

---

## Fitur Utama

### Publik & PWA
- **Desain Hand-Drawn Minimalis**: Bingkai dan garis sketsa (`2px --ink`), sudut organis CSS (`sketch-radius-a/b/c`), dark/light mode tanpa flash (`localStorage` + preferensi sistem), serta font self-hosted (`Caveat`, `Patrick Hand`, `DM Sans`, `JetBrains Mono`).
- **Progressive Web App (PWA)**:
  - Web App Manifest dinamis di `/manifest.webmanifest` mengikuti pengaturan nama & deskripsi blog.
  - Service Worker (`/sw.js`) dengan strategi *Network-First* untuk halaman publik, *Cache-First* untuk font/aset statis, *Stale-While-Revalidate* untuk media & manifest, serta *bypass* penuh untuk rute admin dan autentikasi.
  - Halaman fallback offline (`/offline`) bertema hand-drawn dengan dukungan dark mode.
- **Artikel & Navigasi**:
  - Beranda (`/`), daftar artikel (`/articles`) dengan dropdown filter kategori, pencarian `FULLTEXT` MySQL (dan fallback `LIKE` untuk kata kunci pendek), serta paginasi responsif.
  - Detail artikel (`/articles/{slug}`) dengan highlight sintaks kode + tombol salin, metadata waktu baca (`reading_time`), daftar tag, artikel terkait, dan redirect `301` otomatis dari tabel `redirects` saat slug artikel terbit berubah.
  - Halaman About (`/about`), `sitemap.xml`, `robots.txt`, serta halaman error `404` & `500` bertema senada.

### CMS Admin Privat
- **Single-Admin & Tanpa Registrasi Publik**: Tidak ada rute `/register` maupun reset password publik. Pembuatan dan reset kata sandi admin dilakukan via CLI Artisan.
- **Manajemen Artikel & Kategori**: Dashboard statistik & draf terbaru, editor Markdown dengan live *word count* & *reading time*, preview server-side (`POST /admin/articles/preview`), penjadwalan (`published_at`), serta CRUD kategori (mencegah penghapusan kategori yang masih terkait artikel).
- **Pipeline Media WebP**: Validasi MIME server-side (`JPEG`, `PNG`, `WebP` maks `5 MB`), pembersihan metadata EXIF + auto-rotasi orientasi kamera, resize proporsional maks `1600px`, konversi ke `WebP`, pembuatan thumbnail `400px` dan `800px`, serta nama file acak.
- **Pengaturan & Profil**: Kelola identitas blog, logo, favicon, hero banner, bio penulis, tautan sosial, konten halaman About, SEO default, serta pembaruan profil dan password admin.

### Keamanan & Aksesibilitas
- **Security Headers**: `Content-Security-Policy`, `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy`, `Permissions-Policy`, dan `Strict-Transport-Security` (HTTPS/produksi).
- **Sanitasi Konten**: Markdown GFM dirender dan disanitasi allowlist ketat (`symfony/html-sanitizer`) sebelum disimpan ke `body_html`.
- **Rate Limiting & Privasi**: Login ber-rate-limit dengan pesan error generik, halaman admin/login bertanda `noindex, nofollow`, tanpa cookie pelacak maupun analytics pihak ketiga.
- **Aksesibilitas**: Skip link, target sentuh minimal `44px`, focus ring solid, atribut ARIA, serta dukungan `prefers-reduced-motion` dan `prefers-contrast: more`.

---

## Tech Stack

- **Backend**: PHP 8.3, Laravel 12
- **Database**: MySQL 8.0 (SQLite in-memory untuk environment testing)
- **Frontend**: Blade Components, Alpine.js, Tailwind CSS, Vite 8
- **Container**: Docker / Docker Compose (PHP 8.3-FPM, Nginx 1.27 Alpine, MySQL 8.0, Node 22 Alpine)
- **Quality & Testing**: Laravel Pint, Larastan (`phpstan`), Pest, GitHub Actions CI

---

## Struktur Proyek

```text
├── app/
│   ├── Console/Commands/       # Command Artisan admin (create-admin, reset-password)
│   ├── Http/
│   │   ├── Controllers/        # Dipisah ke Admin/, Public/, dan Auth/
│   │   ├── Middleware/         # SecurityHeadersMiddleware
│   │   └── Requests/           # Form Request validation
│   ├── Models/                 # Article, Category, Tag, Media, Setting, Redirect, User
│   ├── Policies/               # Otorisasi Admin
│   └── Services/               # MarkdownRenderer, MediaProcessor, SlugGenerator, ReadingTime, ExcerptGenerator
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/                # Seeder data demo (1 admin, 4 kategori, 12 artikel)
├── docker/                     # Konfigurasi Nginx & PHP container
├── lang/                       # Terjemahan aplikasi (id)
├── public/
│   ├── fonts/                  # Self-hosted woff2 fonts
│   ├── icons/                  # Ikon PWA & PNG favicons
│   └── sw.js                   # Service Worker PWA
├── resources/
│   ├── css/app.css             # CSS variables (light/dark) & typography styles
│   ├── js/app.js               # Alpine.js, code copy button, & registrasi SW
│   └── views/                  # Blade components, layouts, public & admin views
├── routes/                     # web.php, auth.php, console.php
├── scripts/backup.sh           # Skrip backup harian DB & public storage
└── tests/                      # Pest Unit & Feature tests
```

---

## Cara Menjalankan (Lokal dengan Docker / Podman)

### Prasyarat
- Docker + Docker Compose v2 (atau Podman + `podman-compose`)
- GNU `make`

### Langkah Setup
1. **Clone repository & masuk ke direktori proyek**:
   ```bash
   git clone https://github.com/netph4ntom/npblog.git
   cd npblog
   ```

2. **Siapkan `.env` dari `.env.example`** (sesuaikan kredensial `DB_PASSWORD` dan `DB_ROOT_PASSWORD` bila diperlukan):
   ```bash
   make env
   ```

3. **Jalankan container & install dependency PHP/Node**:
   ```bash
   make up
   make install
   ```

4. **Generate app key, buat symlink storage, dan build aset Vite**:
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan storage:link
   docker compose --profile dev run --rm node sh -c "npm run build"
   ```

5. **Jalankan migrasi database**:
   - Dengan data contoh (1 admin, 4 kategori, 12 artikel):
     ```bash
     make fresh
     ```
     > Akun bawaan seeder hanya untuk pengembangan lokal: `admin@npblog.local` / `password`. Segera ganti di produksi atau gunakan langkah tanpa seeder di bawah.
   - Atau database bersih + buat akun admin sendiri:
     ```bash
     make migrate
     make create-admin
     ```

6. **Akses aplikasi**:
   - Publik: `http://localhost:8080`
   - Login Admin: `http://localhost:8080/login`

Untuk pengembangan frontend dengan Vite HMR:
```bash
make dev
```

---

## Daftar Perintah Penting

Semua perintah dijalankan melalui container menggunakan `Makefile`:

| Perintah | Fungsi |
|---|---|
| `make up` | Salin `.env` (jika belum ada) dan jalankan container |
| `make down` | Hentikan seluruh container |
| `make install` | Jalankan `composer install` dan `npm install` |
| `make dev` | Jalankan Vite dev server (`localhost:5173`) |
| `make migrate` | Jalankan database migration |
| `make fresh` | Jalankan `migrate:fresh --seed` |
| `make create-admin` | Buat akun admin baru (`php artisan npblog:create-admin`) |
| `make test` | Jalankan seluruh test suite Pest |
| `make lint` | Format & cek style PHP dengan Laravel Pint |
| `make stan` | Analisis statis PHP dengan Larastan |
| `make check` | Jalankan `lint` + `stan` + `test` |
| `make shell` | Masuk shell container `app` |
| `make logs` | Lihat log seluruh service container |

Reset kata sandi admin via CLI:
```bash
docker compose exec app php artisan npblog:reset-password
```

---

## Pengujian & CI

Jalankan pemeriksaan lengkap (Pint, Larastan, dan Pest) sebelum commit:
```bash
make check
```

Workflow CI di [`.github/workflows/ci.yml`](.github/workflows/ci.yml) berjalan otomatis pada `push` dan `pull_request` ke branch `main` dan `master` (MySQL 8.0, build Vite, `pint --test`, `phpstan`, dan `php artisan test`).

---

## Deploy ke STB via Cloudflare Tunnel (CD)

Workflow deploy di [`.github/workflows/cd.yaml`](.github/workflows/cd.yaml) berjalan otomatis setelah workflow **CI** sukses pada branch `main`/`master` (atau manual lewat **Run workflow**). Aset Vite dibangun di GitHub runner, lalu dirilis ke STB melalui SSH over Cloudflare Tunnel (`cloudflared access ssh`) dengan *zero-downtime release directory*, *healthcheck* `/up`, serta *rollback* otomatis bila deploy gagal.

### 1. Persiapan Satu Kali di STB
- Pastikan Docker Engine + Compose v2 dan `cloudflared` tunnel (arah web `http://localhost:8080` + arah SSH `ssh://localhost:22`) berjalan di STB.
- Siapkan direktori aplikasi dan `.env` produksi di STB (mis. `/opt/npblog`):
  ```bash
  mkdir -p /opt/npblog/shared
  cp .env.example /opt/npblog/shared/.env
  # Edit /opt/npblog/shared/.env:
  # APP_ENV=production, APP_DEBUG=false, APP_URL=https://domain-anda.com,
  # SESSION_SECURE_COOKIE=true, dan password DB yang kuat, lalu isi APP_KEY (32-byte base64)
  ```
- Jika memakai Cloudflare Access Zero Trust untuk hostname SSH, buat **Service Token** (`CF-Access-Client-Id` & `CF-Access-Client-Secret`) dan tambahkan policy **Service Auth** di Cloudflare Zero Trust.

### 2. GitHub Repository Secrets
| Secret | Wajib | Keterangan |
|---|---|---|
| `CF_SSH_HOSTNAME` | Ya | Hostname SSH Cloudflare Tunnel (mis. `ssh.domain-anda.com`) |
| `STB_SSH_USER` | Ya | Username SSH pada STB (termasuk grup `docker`) |
| `STB_SSH_KEY` | Ya | Private key SSH untuk login ke STB |
| `STB_APP_DIR` | Ya | Path dasar di STB (mis. `/opt/npblog`) |
| `CF_ACCESS_CLIENT_ID` | Opsional* | Client ID Service Token Cloudflare Zero Trust |
| `CF_ACCESS_CLIENT_SECRET` | Opsional* | Client Secret Service Token Cloudflare Zero Trust |

> \* Wajib diisi bila aplikasi SSH di Cloudflare Tunnel diproteksi Cloudflare Access.

Setelah deploy pertama sukses, buat akun admin produksi langsung di STB:
```bash
cd /opt/npblog/current && docker compose -p npblog exec app php artisan npblog:create-admin
```

---

## Catatan Produksi & Backup

- **Environment Produksi**: Set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, dan `APP_URL` ke domain HTTPS Anda di `.env`.
- **Reverse Proxy / Cloudflare Tunnel**: Aplikasi sudah mempercayai header `X-Forwarded-*` dari proxy agar URL HTTPS, redirect, HSTS, dan rate limit IP pengunjung berjalan akurat.
- **PWA**: Service Worker dan fitur instalasi aplikasi (*Add to Home Screen*) memerlukan origin **HTTPS** (atau `localhost` saat lokal).
- **Backup**: Jadwalkan [`scripts/backup.sh`](scripts/backup.sh) via cron di `/opt/npblog/current` untuk mencadangkan database MySQL dan folder `storage/app/public` ke `./backups/` (retensi 7 hari untuk DB dan 28 hari untuk storage).
