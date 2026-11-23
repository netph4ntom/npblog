Review perubahan yang belum di-commit (`git diff`) terhadap `AGENTS.md`:
- Pelanggaran keamanan (route admin tanpa auth, output tidak ter-escape, upload tidak divalidasi).
- Query publik yang tidak memakai scope `published()`.
- Hardcode warna/ukuran di luar token desain; masalah aksesibilitas (kontras, label, focus).
- Test yang hilang atau lemah, dan scope di luar PRD.
Keluarkan daftar temuan berurutan dari yang paling berat, lengkap dengan file dan baris.
