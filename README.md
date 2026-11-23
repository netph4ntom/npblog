# NPblog — Agent Environment Kit

Paket ini menyiapkan lingkungan agar AI coding agent (Claude Code, Cursor, dll.) bisa membangun NPblog dengan konsisten.

## Isi
- `AGENTS.md` / `CLAUDE.md` — aturan kerja, arsitektur, keamanan, desain, batasan agent
- `docs/` — ringkasan PRD, design system hand-drawn, backlog bertahap (T01–T12)
- `.claude/` — izin perintah (deny baca `.env`, deny `git push`) dan slash command `/task`, `/review`
- `Dockerfile`, `compose.yaml`, `docker/` — PHP 8.3-FPM, Nginx, MySQL 8, Node 22 (profil dev)
- `Makefile` — semua perintah lewat `make`
- `.github/workflows/ci.yml` — Pint, Larastan, Pest, build aset
- `scripts/backup.sh` — backup DB + storage

## Mulai
1. Taruh seluruh isi folder ini di repo kosong `npblog/` lalu `git init`.
2. `make bootstrap` (membuat skeleton Laravel 12 tanpa menimpa file kit), lalu `make up` dan `make migrate`.
3. Buka agent di folder ini, lalu jalankan `/task T01` (Claude Code) atau minta: "Kerjakan T01 di docs/BACKLOG.md sesuai AGENTS.md".
4. Lanjut task berikutnya satu per satu. Agent wajib menjalankan `make check` sebelum menyatakan selesai.

## Catatan
- Kit ini belum dijalankan di Docker sungguhan; YAML dan JSON sudah divalidasi sintaksnya. Bila `make bootstrap` gagal, periksa versi Docker Compose v2.
- `Larastan` dan `Pest` dipasang di T01; CI akan gagal sampai T01 selesai.
- Sesuaikan jawaban "Pertanyaan terbuka" di `docs/BACKLOG.md` sebelum agent menyentuh task yang bergantung padanya.
