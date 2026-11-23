# AGENTS.md — NPblog

Instruksi untuk AI coding agent. Baca seluruh file ini sebelum mengubah apa pun.

## Proyek
NPblog: blog personal minimalis bergaya **hand-drawn** dengan CMS admin privat. Pemilik: Dapit.
Satu admin, tanpa registrasi publik. Sumber kebenaran: `docs/PRD-summary.md` (ringkasan) dan PRD v2.1.0 lengkap
(https://claude.ai/artifact/1uG7Dtf9qEp7mx2m2WcYKW). Jika dua sumber berbeda, PRD lengkap menang; laporkan selisihnya.

## Stack
Laravel 12, PHP 8.3, MySQL 8, Blade + Alpine.js, Tailwind CSS, Vite, Pest, Laravel Pint, Larastan.
Semua perintah dijalankan **di dalam container** lewat `make` (lihat Perintah). Jangan install PHP/Node di host.

## Perintah
- `make up` / `make down` — jalankan / hentikan layanan
- `make bootstrap` — pertama kali saja: membuat skeleton Laravel di folder ini
- `make install` — composer install + npm install
- `make dev` — Vite dev server (profil `dev`)
- `make migrate`, `make fresh` — migrate / migrate:fresh --seed
- `make test` — Pest; `make lint` — Pint (perbaiki); `make stan` — Larastan
- `make shell` — shell di container app; `make logs`
- `make check` — lint + stan + test (wajib hijau sebelum menyatakan selesai)

## Cara kerja
1. Ambil **satu** task dari `docs/BACKLOG.md` (urut dari atas, jangan lompat). Baca acceptance criteria-nya.
2. Tulis test dulu (feature/unit), lalu implementasi minimal yang membuatnya lulus.
3. Jalankan `make check`. Jangan tandai selesai bila ada yang merah.
4. Centang task di `docs/BACKLOG.md`, tulis catatan singkat bila ada keputusan atau penyimpangan.
5. Commit kecil dengan format Conventional Commits (`feat:`, `fix:`, `test:`, `docs:`, `chore:`).

## Aturan arsitektur
- MVC Laravel. Controller tipis; logika di `app/Services/` (SlugGenerator, ReadingTime, MarkdownRenderer, MediaProcessor).
- Validasi lewat Form Request. Otorisasi lewat middleware `auth` + Policy.
- Controller dipisah: `Http/Controllers/Public/` dan `Http/Controllers/Admin/`.
- View: Blade Components di `resources/views/components/`. Satu komponen = satu tanggung jawab.
- Query lewat Eloquent; gunakan scope `Article::published()` (status = published DAN published_at <= now()) di **semua** query publik.
- Satu artikel = satu kategori (`articles.category_id`); tag many-to-many.
- Konten artikel disimpan Markdown (`body`) dan HTML tersanitasi (`body_html`), dirender saat simpan.

## Keamanan (tidak boleh dilanggar)
- Tidak ada route `/register`. Admin dibuat via `php artisan npblog:create-admin`.
- Semua route `/admin/*` di balik `auth`. Login ber-rate-limit. Pesan gagal login generik.
- Jangan render input mentah; Blade `{{ }}` untuk teks, HTML hanya dari `body_html` yang sudah disanitasi allowlist.
- Upload: validasi MIME di server, nama acak, hapus EXIF, tanpa SVG/executable, maksimal 5 MB, simpan WebP.
- Jangan membaca, mencetak, atau commit `.env` dan secret. Pakai `.env.example` sebagai referensi.
- Tidak ada cookie pelacak dan tidak ada analytics pihak ketiga.

## Desain (hand-drawn)
Detail ada di `docs/design-system.md`. Ringkasnya:
- Karakter tangan ada di bingkai dan aksen, **bukan di bacaan**. Isi artikel, kode, dan form tetap lurus.
- Font: Caveat (heading), Patrick Hand (UI), DM Sans (body artikel), JetBrains Mono (kode). Self-host.
- Garis 2px Ink, sudut bergoyang via CSS (preset a/b/c), bayangan keras `3px 3px 0`. Tanpa library runtime.
- Warna hanya dari token (CSS variable). Jangan hardcode hex di komponen.
- Wajib: kontras WCAG AA, focus ring solid, `aria-label` pada tombol ikon, target sentuh 44px, dark mode tanpa flash,
  hormati `prefers-reduced-motion` dan `prefers-contrast`.

## Performa
LCP < 2,5 s, CLS < 0,1. Gambar WebP dengan `width`/`height`, lazy-load kecuali kandidat LCP. JavaScript seminimal mungkin.

## Hal yang HARUS ditanyakan ke Dapit (jangan putuskan sendiri)
Bahasa UI, warna tombol primer, soft vs hard delete, versi Tailwind, domain/hosting, font body artikel, intensitas doodle.
Bila terhambat, catat di bagian "Pertanyaan terbuka" di `docs/BACKLOG.md` lalu lanjut ke task yang tidak terblokir.

## Jangan
- Menambah fitur di luar PRD (komentar, newsletter, multi-author, analytics) tanpa persetujuan.
- Menambah dependency besar tanpa alasan tertulis di pesan commit.
- Mengubah migration yang sudah di-commit; buat migration baru.
- Menyatakan selesai tanpa menjalankan test.
