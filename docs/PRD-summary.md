# NPblog — Ringkasan PRD v2.1.0 (untuk agent)

Prioritas: P0 = v1.0, P1 = v1.1–v1.2, P2 = nanti. Detail lengkap ada di PRD penuh.

## Rute publik
| Route | Fungsi | P |
|---|---|---|
| `/` | Home: hero, featured, latest, About preview | P0 |
| `/articles` | Daftar; `?q=`, `?category=`, `?page=`; 9 per halaman | P0 |
| `/articles/{slug}` | Detail; 404 untuk draft/terjadwal; 301 untuk slug lama | P0 |
| `/about` | Konten dari Settings | P0 |
| `/search` | Hasil pencarian (noindex) | P0 |
| `/archive` | Per tahun lalu bulan | P1 |
| `/feed.xml`, `/sitemap.xml`, `/robots.txt` | SEO | P0 sitemap/robots, P1 feed |

## Rute admin (semua di balik `auth`)
`/login` (GET/POST, rate limited), `/logout`, `/admin`, `/admin/articles` (+ create, store, edit, update, destroy, preview, autosave P1),
`/admin/categories` (resource), `/admin/media`, `/admin/settings`, `/admin/profile`. **Tidak ada `/register`.**

## Aturan bisnis
- Tampil publik hanya jika `status = published` dan `published_at <= now()`.
- Slug: lowercase, unik, akhiran `-2` bila bentrok, dapat diedit. Ubah slug artikel published membuat redirect 301.
- Reading time = `ceil(words / 200)`, minimum 1 menit; disimpan di `word_count` dan `reading_time`.
- Excerpt maksimal 300 karakter; kosong berarti otomatis dari paragraf pertama (~160 karakter).
- Autosave tidak pernah mengubah konten publik; hanya tombol Update.
- Draft tidak masuk sitemap, RSS, search, related, prev/next.

## Skema database
- `users`: id, name, email unique, password, remember_token, timestamps
- `categories`: id, name, slug unique, description, timestamps
- `tags`: id, name, slug unique, timestamps; pivot `article_tag`
- `media`: id, user_id, disk, path, original_name, mime, size, width, height, alt_text, created_at
- `articles`: id, user_id, category_id null, featured_media_id null, title, slug unique, excerpt null, body (Markdown),
  body_html (tersanitasi), status enum(draft,published), published_at null, is_featured bool, language char(2) default `id`,
  word_count, reading_time, meta_title null, meta_description null, timestamps, deleted_at null (soft delete, menunggu keputusan)
  - index: (status, published_at), category_id, is_featured, FULLTEXT(title, excerpt, body)
- `settings`: key pk, value json/text
- `redirects`: id, from_slug unique, article_id fk, created_at
- `article_revisions` (P1): id, article_id, title, body, kind, created_at

## Media
JPEG/PNG/WebP, maks 5 MB. Pipeline: validasi MIME, hapus EXIF, resize maks 1600px, simpan WebP + thumbnail 400/800px, nama acak.

## Non-fungsional
LCP < 2,5 s, CLS < 0,1, INP < 200 ms, Lighthouse mobile ≥ 90 (Home, Article). WCAG 2.1 AA.
Header keamanan: CSP, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, HSTS (produksi).
Backup harian DB + storage (`scripts/backup.sh`), retensi 7 harian / 4 mingguan, restore diuji.
