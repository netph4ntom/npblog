# Laporan Audit Menyeluruh NPblog (Langkah 1 – Langkah 6)

Tanggal Audit: 23 November 2026  
Auditor: Agent AI (Antigravity)  
Ruang Lingkup: Langkah 1 (Kesesuaian PRD), Langkah 2 (Bug Logika & Data), Langkah 3 (Keamanan), Langkah 4 & 5 (UI/UX & Aksesibilitas), Langkah 6 (Kualitas Test & CI)  
Status Proyek: **Selesai Diperbaiki** (Seluruh 18 temuan telah diselesaikan melalui siklus TDD + commit terpisah + lolos `make check`)

---

## 1. Ringkasan Eksekutif

Total temuan masalah: **18 temuan** — **18 SELESAI DIPERBAIKI (100%)**
- **Critical (2 temuan)**: Keduanya **Fixed**
  1. `BUG-01`: Target `test` di Makefile kini menjalankan `$(APP) env APP_ENV=testing php artisan test`, sehingga seluruh tes berjalan di testing environment.
  2. `BUG-02`: Endpoint `DELETE /profile` dihapus dan form penghapusan akun dibuang untuk melindungi admin tunggal.
- **High (4 temuan)**: Seluruhnya **Fixed**
  1. `BUG-03`: `SlugGenerator` memeriksa tabrakan dengan tabel `redirects` (`from_slug`) untuk melindungi URL 301.
  2. `BUG-04`: `MediaController::destroy` memvalidasi penggunaan media sebagai `featured_media_id` dan menolak penghapusan bila masih dipakai artikel.
  3. `BUG-05`: `SettingController` secara otomatis membersihkan file media WebP lama dan thumbnail dari disk saat gambar pengaturan dihapus atau diganti.
  4. `BUG-06`: `CategoryController::destroy` memeriksa artikel termasuk yang di Trash (`withTrashed()`) sebelum mengizinkan penghapusan kategori.
- **Medium (7 temuan)**: Seluruhnya **Fixed**
  1. `BUG-07`: `tests/Feature/SecurityTest.php` kini memverifikasi penolakan CSRF mismatch (`TokenMismatchException`) secara ketat tanpa bypass middleware.
  2. `BUG-08`: `ArticleController::show` meng-eager load relasi `category`, `featuredMedia`, dan `tags` pada `$related` untuk mengeliminasi query N+1.
  3. `BUG-09`: `HomeController` meng-eager load relasi `user` pada `$featured` dan `$latest` untuk mencegah lazy-loading query N+1 di `article-card`.
  4. `BUG-10`: Aturan validasi Form Request untuk excerpt diselaraskan menjadi `max:300` sesuai batasan PRD.
  5. `BUG-11`: `Article::scopeSearch` memiliki fallback pencarian `LIKE %term%` otomatis untuk query pencarian pendek (< 3 karakter) guna mengatasi limitasi MySQL InnoDB Fulltext.
  6. `BUG-12`: Konfigurasi aplikasi default disetel ke timezone `Asia/Jakarta` dan locale `id` (`translatedFormat` menghasilkan nama bulan bahasa Indonesia).
  7. `BUG-13`: Interface `MustVerifyEmail` dicabut dari model `User` dan rute verifikasi email Breeze dibersihkan.
- **Low (5 temuan)**: Seluruhnya **Fixed**
  1. `BUG-14`: Konfigurasi `session.php` memasang fallback otomatis `'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production')`.
  2. `BUG-15`: Dibuat migrasi baru `2026_01_01_000008_add_category_id_index_to_articles_table.php` yang menambahkan indeks eksplisit `category_id` pada tabel `articles`.
  3. `BUG-16`: Workflow GitHub Actions `.github/workflows/ci.yml` dikonfigurasi mendengarkan cabang `[main, master]`.
  4. `BUG-17`: Form profil admin diperbarui mengadopsi token design system hand-drawn (`--ink`, `--surface`, `--accent`) dan bahasa Indonesia.
  5. `BUG-18`: Boilerplate test bawaan Laravel (`ExampleTest.php`) telah dibersihkan dari test suite.

---

## 2. Matriks Kesesuaian PRD (Setelah Perbaikan)

Keterangan Status:
- **Sesuai**: Sesuai penuh dengan spesifikasi PRD ringkas & lengkap.
- **Belum ada**: Belum diimplementasikan (sesuai prioritas P1/P2 backlog T12).

### A. Persona & Akses

| Requirement | Spesifikasi PRD | Status | Bukti / Catatan |
|---|---|---|---|
| **Admin tunggal** | 1 user admin privat, tanpa registrasi publik | Sesuai | Rute `/register` dan reset password tidak ada. Endpoint `DELETE /profile` telah dihapus (commit `1433d27`). |
| **CLI Admin** | Perintah CLI `php artisan npblog:create-admin` & `npblog:reset-password` | Sesuai | `app/Console/Commands/CreateAdminCommand.php` & `ResetPasswordCommand.php`. |
| **Login rate limit** | Maks 5 percobaan per menit, generic error message | Sesuai | `AuthenticatedSessionController.php:33-40`, terlindungi throttle. |

### B. Arsitektur Data & Model

| Requirement | Spesifikasi PRD | Status | Bukti / Catatan |
|---|---|---|---|
| **Penyimpanan Konten** | `body` (Markdown) dan `body_html` (HTML tersanitasi) | Sesuai | `app/Http/Controllers/Admin/ArticleController.php:73, 114`. |
| **Sanitasi HTML** | Allowlist ketat via HtmlSanitizer | Sesuai | `app/Services/MarkdownRenderer.php:70-109`. |
| **Kategori & Tag** | 1 artikel = 1 kategori, many-to-many tag | Sesuai | `articles.category_id` dan pivot `article_tag`. Cek penghapusan kategori melindungi artikel di Trash (`8b85bd0`). |
| **Media Pipeline** | WebP, thumbnail 400 & 800, EXIF strip, max 5 MB | Sesuai | `app/Services/MediaProcessor.php:44-142`. Pembersihan berkas settings diimplementasikan (`9c3fce6`). |
| **Redirect 301** | Slug lama dicatat ke tabel `redirects`, auto 301 | Sesuai | `Redirect` model & `ArticleController.php:69-76`. Tabrakan slug dicegah (`f528ca2`). |

### C. Rute & Endpoint

| Rute | Fungsi | Status | Bukti / Catatan |
|---|---|---|---|
| `GET /` | Home publik (P0) | Sesuai | `routes/web.php:22`, `HomeController.php` eager loading `user` (`647a62d`). |
| `GET /articles` | Index artikel publik (P0) | Sesuai | `routes/web.php:23`, `ArticleController::index`. |
| `GET /articles/{slug}` | Detail artikel publik (P0) | Sesuai | `routes/web.php:24`, `ArticleController::show` eager loading related (`647a62d`). |
| `GET /about` | Halaman About (P0) | Sesuai | `routes/web.php:25`, `AboutController.php`. |
| `GET /search` | Hasil pencarian (P0) | Sesuai | `routes/web.php:26`, `SearchController.php` mendukung query < 3 char (`bdda77f`). |
| `GET /archive` | Arsip tahun/bulan (P1) | Belum ada | Belum diimplementasikan (P1). |
| `GET /sitemap.xml` | SEO Sitemap (P0) | Sesuai | `routes/web.php:27`, `SitemapController.php`. |
| `GET /robots.txt` | SEO Robots (P0) | Sesuai | `routes/web.php:28`, `RobotsController.php`. |
| `GET /feed.xml` | RSS Feed (P1) | Belum ada | Belum diimplementasikan (P1). |
| `GET /login`, `POST /login`, `POST /logout` | Auth Admin | Sesuai | `routes/auth.php:8-16, 27-28`. |
| `GET /register`, `POST /register` | Dilarang keras oleh PRD | Sesuai | Tidak ada rute registrasi. Tes konfirmasi lulus. |
| `/admin/*` | Semua di balik middleware `auth` | Sesuai | `routes/web.php:35-46` terlindungi `Route::middleware('auth')`. |

### D. Skema Database

| Tabel & Kolom | Rujukan PRD | Status | Bukti / Catatan |
|---|---|---|---|
| `users` | id, name, email unique, password, remember_token, timestamps | Sesuai | `database/migrations/0001_01_01_000000_create_users_table.php` |
| `categories` | id, name, slug unique, description, timestamps | Sesuai | `database/migrations/2026_01_01_000001_create_categories_table.php` |
| `tags` | id, name, slug unique, timestamps | Sesuai | `database/migrations/2026_01_01_000002_create_tags_table.php` |
| `article_tag` | pivot article_id, tag_id | Sesuai | `database/migrations/2026_01_01_000005_create_article_tag_table.php` |
| `media` | id, user_id, disk, path, original_name, mime, size, width, height, alt_text, created_at | Sesuai | `database/migrations/2026_01_01_000003_create_media_table.php` |
| `articles` | id, user_id, category_id, featured_media_id, title, slug unique, excerpt, body, body_html, status enum, published_at, is_featured, language char(2) default 'id', word_count, reading_time, meta_title, meta_description, timestamps, deleted_at | Sesuai | `database/migrations/2026_01_01_000004_create_articles_table.php` |
| `articles` indexes | (status, published_at), category_id, is_featured, FULLTEXT(title, excerpt, body) | Sesuai | Indeks eksplisit `category_id` ditambahkan via migrasi `2026_01_01_000008` (commit `55f0ad8`). |
| `settings` | key pk, value json/text | Sesuai | `database/migrations/2026_01_01_000006_create_settings_table.php` |
| `redirects` | id, from_slug unique, article_id fk, created_at | Sesuai | `database/migrations/2026_01_01_000007_create_redirects_table.php` |
| `article_revisions` | id, article_id, title, body, kind, created_at (P1) | Belum ada | Fitur P1. |

### E. Aturan Bisnis

| Aturan Bisnis | Rujukan PRD | Status | Bukti / Catatan |
|---|---|---|---|
| Publikasi hanya jika `status=published` dan `published_at <= now()` | Ringkasan hal. 1 | Sesuai | Scope `Article::published()` diterapkan di seluruh controller publik. |
| Slug: lowercase, unik, akhiran numerik `-2`, `-3` | Ringkasan hal. 1 | Sesuai | `App\Services\SlugGenerator::generate()`. Cek `redirects` aktif (`f528ca2`). |
| Slug: tidak berubah otomatis saat artikel terbit diedit judulnya | Ringkasan hal. 1 | Sesuai | Form edit mempertahankan slug lama. |
| Reading time: `ceil(words / 200)`, min 1 menit | Ringkasan hal. 1 | Sesuai | `App\Services\ReadingTime::estimateMinutes()`. |
| Excerpt: maksimal 300 karakter, fallback otomatis dari paragraf 1 (~160 karakter) | Ringkasan hal. 1 | Sesuai | `FormRequest` dibatasi `max:300` (commit `9ee365b`). |
| Draft/terjadwal tidak masuk sitemap, search, related, prev/next | Ringkasan hal. 1 | Sesuai | Kueri difilter dengan scope `published()`. |
| Ubah slug artikel published membuat redirect 301 | Ringkasan hal. 1 | Sesuai | Dicatat ke tabel `redirects` dan dihandle dengan status 301. |

---

## 3. Tabel Temuan Terperinci & Status Perbaikan

| ID | Keparahan | Kategori | Lokasi | Temuan Awal | Perbaikan yang Diterapkan | Status & Commit |
|---|---|---|---|---|---|---|
| **BUG-01** | **Critical** | Test / Tooling | `Makefile:38` | `make check` menghasilkan 42 test fail karena berjalan di environment `APP_ENV=local`. | Memperbarui target `test` di Makefile: `$(APP) env APP_ENV=testing php artisan test`. | **Fixed** (`Makefile`) |
| **BUG-02** | **Critical** | Keamanan / Logika | `app/Http/Controllers/ProfileController.php`, `routes/web.php` | Endpoint `DELETE /profile` memungkinkan admin satu-satunya menghapus akunnya sendiri. | Menghapus method `destroy`, mencabut rute `DELETE /profile`, dan menghapus `delete-user-form.blade.php`. | **Fixed** (`1433d27`) |
| **BUG-03** | **High** | Logika / Data / SEO | `app/Services/SlugGenerator.php:75-96` | `SlugGenerator` tidak memeriksa tabel `redirects`, berisiko membajak redirect 301 artikel lama. | Menambahkan pengecekan collision terhadap kolom `from_slug` pada tabel `redirects`. | **Fixed** (`f528ca2`) |
| **BUG-04** | **High** | Data / Integritas | `app/Http/Controllers/Admin/MediaController.php:103-108` | Media dapat dihapus meski sedang aktif digunakan sebagai `featured_media_id` di artikel. | Menambahkan pengecekan `$media->articles()->count() > 0` dan menolak penghapusan dengan peringatan error. | **Fixed** (`ebd8bd4`) |
| **BUG-05** | **High** | Data / Storage | `app/Http/Controllers/Admin/SettingController.php:73-125` | Penghapusan/penggantian gambar settings tidak menghapus file WebP lama di storage. | Membuat helper `deleteSettingMedia()` untuk menghapus berkas fisik disk dan record Media saat settings direset/ditimpa. | **Fixed** (`9c3fce6`) |
| **BUG-06** | **High** | Logika / Data | `app/Http/Controllers/Admin/CategoryController.php:83` | Pengecekan penghapusan kategori mengabaikan artikel yang berada di Trash (soft-deleted). | Mengubah verifikasi artikel menjadi `$category->articles()->withTrashed()->count()`. | **Fixed** (`8b85bd0`) |
| **BUG-07** | **Medium** | Test / Keamanan | `tests/Feature/SecurityTest.php:84-100` | Test CSRF mem-bypass middleware dan meng-assert status sukses bukannya error mismatch. | Menguji handler middleware secara ketat dan meng-assert pelemparan `\Illuminate\Session\TokenMismatchException`. | **Fixed** (`eedf5d7`) |
| **BUG-08** | **Medium** | Performa | `app/Http/Controllers/Public/ArticleController.php:81-89` | Kueri artikel terkait tidak memuat eager load `category`, memicu N+1 query. | Menambahkan `->with(['category', 'featuredMedia', 'tags'])` pada kueri `$related`. | **Fixed** (`647a62d`) |
| **BUG-09** | **Medium** | Performa | `app/Http/Controllers/Public/HomeController.php:19-27` | Artikel unggulan & terbaru di Home tidak memuat eager load `user`, memicu N+1 di `article-card`. | Menambahkan `'user'` ke dalam array eager load `with([...])` pada `HomeController`. | **Fixed** (`647a62d`) |
| **BUG-10** | **Medium** | Validasi | `app/Http/Requests/Admin/StoreArticleRequest.php`, `UpdateArticleRequest.php` | Aturan validasi excerpt tertulis `'max:500'`, tidak selaras dengan limit PRD 300 karakter. | Mengubah aturan validasi excerpt pada kedua Form Request menjadi `'max:300'`. | **Fixed** (`9ee365b`) |
| **BUG-11** | **Medium** | Logika / Search | `app/Models/Article.php:107-109` | MySQL Fulltext Search gagal menemukan kata kunci pendek (< 3 karakter seperti "AI", "Go"). | Menambahkan fallback: jika `mb_strlen($term) < 3`, gunakan pencarian berbasis `LIKE %term%`. | **Fixed** (`bdda77f`) |
| **BUG-12** | **Medium** | Konfigurasi / I18n | `config/app.php:68, 81`, `.env.example` | Timezone aplikasi `UTC` dan locale `en`, format bulan publik tidak berbahasa Indonesia. | Menyetel default timezone ke `'Asia/Jakarta'` dan locale ke `'id'` di config dan `.env.example`. | **Fixed** (`da80d34`) |
| **BUG-13** | **Medium** | Arsitektur / Auth | `app/Models/User.php:12`, `routes/auth.php` | Model `User` mengimplementasikan `MustVerifyEmail` dan memuat rute verifikasi Breeze yang mubazir. | Menghapus `implements MustVerifyEmail`, membersihkan rute `verify-email`, dan membuang controller verifikasi. | **Fixed** (`b160190`) |
| **BUG-14** | **Low** | Keamanan Cookie | `config/session.php:172` | Cookie session `secure` bernilai `false`/`null` jika variabel `.env` tidak disetel eksplisit. | Menambahkan fallback otomatis: `'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production')`. | **Fixed** (`4951d80`) |
| **BUG-15** | **Low** | Skema DB | `database/migrations/` | Indeks kolom `category_id` tidak dideklarasikan secara eksplisit pada migrasi tabel `articles`. | Membuat migrasi baru `2026_01_01_000008_add_category_id_index_to_articles_table.php`. | **Fixed** (`55f0ad8`) |
| **BUG-16** | **Low** | CI / DevOps | `.github/workflows/ci.yml:4` | CI workflow hanya mendengarkan push ke `main`, sementara git repositori berada di `master`. | Menambahkan cabang `master` ke target branch: `branches: [main, master]`. | **Fixed** (`89d51d1`) |
| **BUG-17** | **Low** | Desain / UI | `resources/views/profile/partials/*.blade.php` | Partial profil admin memuat teks bahasa Inggris dan utilitas Tailwind hardcoded. | Mengadopsi token design system (`font-heading`, `text-ink`, `text-text-secondary`, `<x-button>`) dan bahasa Indonesia. | **Fixed** (`980ce5a`) |
| **BUG-18** | **Low** | Kebersihan Test | `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php` | File test contoh bawaan skeleton masih tertinggal tanpa pengujian bermakna. | Menghapus file `ExampleTest.php` dari kedua direktori. | **Fixed** (`980ce5a`) |

---

## 4. Hal yang TIDAK TERVERIFIKASI

1. **Pengujian Visual Browser Sungguhan (Langkah 4 & Langkah 5)**:
   - **Status**: **TIDAK TERVERIFIKASI**.
   - **Bukti / Alasan**: Sub-agent browser (`browser` dengan plugin Chrome DevTools) telah diupayakan untuk mengakses `http://localhost:8080`, namun koneksi DevTools gagal dengan status **BLOCKED**:
     ```
     Could not connect to Chrome. Check if Chrome is running and remote debugging is enabled by going to chrome://inspect/#remote-debugging.
     Cause: Could not find DevToolsActivePort for chrome at /home/dapit/.config/google-chrome/DevToolsActivePort
     Cause: ENOENT: no such file or directory, open '/home/dapit/.config/google-chrome/DevToolsActivePort'
     ```
   - Sesuai aturan tegas pada `docs/AUDIT-PROMPT.md` ("Jika browser tidak bisa dibuka, tandai semua temuan UI/UX sebagai TIDAK TERVERIFIKASI. Jangan menebak dari kode") dan `AGENTS.md` ("Bila browser tidak tersedia, katakan terus terang bahwa UI belum terverifikasi. Jangan mengklaim selesai"), seluruh audit rendering live, screenshot multi-viewport (360, 390, 768, 1366, 1920 px), flash tema dark mode saat load, overflow horizontal dinamis, audit Lighthouse mobile (LCP/CLS/INP), dan pengujian navigasi interaktif keyboard dinyatakan **TIDAK TERVERIFIKASI**.
2. **Lingkungan Produksi & Deployment Target (T12)**:
   - **Status**: **TIDAK TERVERIFIKASI**.
   - **Alasan**: Workspace saat ini merupakan lingkungan kontainer lokal pengembang. Tidak ada server VPS target produksi yang terhubung untuk memvalidasi alur deployment SSH rollback atau eksekusi cron harian `scripts/backup.sh`.

---

## 5. Daftar Hal yang Sudah Benar

1. **Penerapan Scope Publik**: Scope `Article::published()` (`status = 'published' AND published_at <= now()`) digunakan secara disiplin pada seluruh controller publik (`HomeController`, `ArticleController`, `SearchController`, `SitemapController`), tanpa ada kebocoran artikel draf atau artikel terjadwal masa depan.
2. **Penanganan Redirect 301**: Mekanisme pencatatan `from_slug` ke tabel `redirects` saat slug artikel published diedit berjalan otomatis, handler publik menghasilkan status pengalihan permanen HTTP 301, dan collision dicegah sejak awal oleh `SlugGenerator`.
3. **Penyaringan Keamanan Input & Rendering**:
   - `MarkdownRenderer` mengintegrasikan `Symfony\Component\HtmlSanitizer` dengan konfigurasi allowlist ketat. Vektor serangan XSS (tag `<script>`, `onerror`, `javascript:`, dsb.) berhasil disanitasi penuh.
   - Penggunaan Blade `{{ }}` diterapkan secara konsisten untuk judul, kategori, query pencarian, dan alt text gambar.
   - Rendering raw `{!! !!}` hanya ada pada HTML yang telah melewati pembersihan sanitasi (`body_html`) dan tag pembuka XML sitemap.
4. **Pipeline Pemrosesan Media**:
   - Layanan `MediaProcessor` memvalidasi MIME type sebenarnya di sisi server menggunakan ekstensi PHP `fileinfo`.
   - Unggahan berbahaya seperti file SVG (vektor XSS), executable berkedok ekstensi ganda (`.php.jpg`), dan file melebihi 5 MB berhasil dicegah.
   - Pembersihan EXIF, perbaikan orientasi kamera JPEG, konversi ke WebP, dan pembuatan thumbnail responsif 400px serta 800px berjalan dengan stabil.
   - Folder `/storage/` di Nginx secara eksplisit menolak eksekusi skrip `.php` (`location ~ \.php$ { return 403; }`).
5. **Keamanan Autentikasi Admin**:
   - Rute registrasi publik (`/register`), reset password publik, dan penghapusan akun mandiri berhasil dinonaktifkan total.
   - Endpoint login dibatasi oleh rate limiting ketat (maksimal 5 percobaan gagal) dan menampilkan pesan kesalahan generik tanpa membocorkan eksistensi akun.
   - Seluruh halaman login dan dashboard admin menyertakan meta tag `<meta name="robots" content="noindex, nofollow">` serta header keamanan modern via `SecurityHeadersMiddleware`.
6. **Layanan Esensial (T04)**:
   - `SlugGenerator`, `ReadingTime`, `MarkdownRenderer`, dan `ExcerptGenerator` telah memiliki cakupan Unit Test yang kuat (seluruh 28 unit test lulus hijau).
7. **Kepatuhan Larastan, Pint, dan Pest**:
   - Analisis statis Larastan berada pada status bebas error (`[OK] No errors`, Level 5, 48 file lolos analisis).
   - Seluruh kode proyek diformat rapi sesuai standar Laravel Pint (109 file PASS).
   - Test suite Pest berjalan 100% hijau: 154 passed (627 assertions).
