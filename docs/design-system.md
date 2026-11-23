# Design System — NPblog (hand-drawn)

Semua warna lewat CSS variable (`--bg`, `--surface`, `--text`, `--text-2`, `--border`, `--ink`, `--accent`, `--accent-dark`, ...).
Dark mode memakai class `dark` di `<html>`; skrip kecil inline di `<head>` memasangnya sebelum render (tanpa flash).

## Warna
| Token | Light | Dark |
|---|---|---|
| Background | `#F6F6F3` | `#222520` |
| Surface | `#FFFFFF` | `#2B2F29` |
| Text | `#20211F` | `#F4F4EF` |
| Text secondary | `#6B6C66` | `#A4A69D` |
| Border (dekoratif) | `#E5E5E0` | `#41453E` |
| Ink (garis sketsa) | `#20211F` | `#E9E9E2` |
| Accent | `#697B68` (fill/ikon, bukan teks kecil) | `#9BAF91` |
| Accent dark (tombol, link) | `#435343` | `#B4C5AB` (hover) |
| On accent | `#FFFFFF` | `#1B1E1A` |
| Warning (badge Draft) | `#8A5A00` di `#F8EBCB` | `#F0C36B` di `#3A3220` |
| Danger | `#B3261E` | `#F2B8B5` |
| Admin sidebar | `#1F211E` | `#1F211E` |

## Tipografi
| Peran | Font | Ukuran |
|---|---|---|
| Hero, H1, judul artikel, angka stat | Caveat 700 | Hero 48–64px (mobile 40), tidak di bawah 24px |
| Nav, tombol, label, chip, metadata, admin | Patrick Hand 400 | 17px dasar, metadata 15px |
| Body artikel | DM Sans | 18px / 1.85 |
| Kode | JetBrains Mono | 15px / 1.7 |
Self-host woff2, subset Latin, `font-display: swap`, preload maksimal 2 file per halaman. Ukuran dalam `rem`.

## Layout
Container 1200px; lebar baca artikel 740px; TOC 220px sticky (≥1024px). Spacing kelipatan 4px.
Breakpoint: <640 (1 kolom), 640–1023 (2 kolom), ≥1024, ≥1280 (grid artikel 3 kolom).
Rasio gambar: card 16:9, featured 4:3, thumbnail 3:2, hero artikel 2:1; selalu `width`/`height` atau `aspect-ratio`.

## Gaya hand-drawn
- Garis 2px `--ink`; sudut bergoyang lewat `border-radius` multi-nilai, 3 preset (a/b/c) dipilih dari id komponen:
  `255px 15px 225px 15px / 15px 225px 15px 255px` (a), variasi mirror untuk b dan c.
- Bayangan keras `3px 3px 0 var(--ink)`; hover card: bayangan + translate(-1px,-1px). Tombol primer ditekan: bayangan 0, turun 3px.
- Tekstur kertas SVG noise ≤ 2 KB opacity 4% hanya di latar halaman; tidak di belakang teks artikel.
- Highlight spidol: gradient miring Accent 35% di satu kata kunci per heading.
- Pemisah: garis putus-putus/bergelombang SVG. Ikon garis 2px ujung bulat 24px, target sentuh 44px.
- Foto: bingkai sketsa, rotasi -1° s.d. +1° tetap per artikel, hanya di card/hero; tidak di dalam isi artikel.
- Doodle maksimal 2 per halaman, `aria-hidden`, SVG inline < 2 KB.
- Admin: intensitas diturunkan, tanpa rotasi/doodle di area kerja, area menulis polos.
- Filter SVG (`feTurbulence` + `feDisplacementMap`) hanya untuk elemen dekoratif, tidak pernah untuk teks/gambar konten.

## Aksesibilitas
Kontras AA; focus ring solid 3px offset 3px; `aria-current` pada nav; `aria-label` pada tombol ikon;
skip link; `prefers-reduced-motion` mematikan animasi; `prefers-contrast: more` mematikan tekstur/filter dan garis 3px.

## Komponen
Header, Footer, Chip, ArticleCard (featured/compact/grid), Pagination, SearchOverlay, ShareBar, TOC, ProgressBar, CodeBlock,
Toast, EmptyState, StatusBadge, StatCard, ConfirmDialog, SketchBox (`<x-sketch-box variant="a|b|c">`).
