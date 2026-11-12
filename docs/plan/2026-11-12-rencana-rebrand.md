# Rencana Rebrand Attendify dan Alur Kerja

- Tanggal: 12 November 2026.
- Status rencana: disetujui, eksekusi berjalan.
- Repo aktif: `nxtirfan/attendify`. Checkout lokal: `D:\Dev\attendify`.
- PresensiGPS (`nxtirfan/presensi-gps-selfie`) sudah diarsipkan/deprecated: tidak dikembangkan lagi, dengan README yang mengarahkan kelanjutan ke Attendify.

## Keputusan yang sudah final

1. Platform cloud: AWS. Rincian di `docs/audit/2026-11-12-audit-dan-rencana-aws.md`.
2. Tanpa Hoplite. Pembagian kerja AI: Gemini Argon (akun aioremu, MCP Google Drive) mengedit file teks di folder dropzone Drive; OpenCode (saya) penulis spec, gerbang QA, integrator, pemilik git (commit/push); Irfan kurir sinkronisasi Drive dan pemberi persetujuan.
3. Database: nama `DB_DATABASE` dan dump `presensigps` ditunda ke fase migration Minggu 1. Fokus saat ini hanya rebrand UI/README/metadata.
4. `composer.json`: field `name` diganti `attendify/app` (sebelumnya `laravel/laravel`), description diganti deskripsi Attendify.
5. README.md ditulis ulang langsung oleh OpenCode di lokal, tidak lewat dropzone.
6. Checkout dipindah dari jalur sinkronisasi Google Drive ke `D:\Dev\attendify` agar steril dari sync. `D:\Kuliah` tetap tersinkron untuk file kuliah non-kode.
7. Jembatan transfer dengan Argon: `D:\Kuliah\2_Komputasi Awan\dropzone-attendify` (tersinkron Drive).

## Scope rebrand

- `README.md` (OpenCode langsung).
- `resources/views/presensi/create.blade.php:10`: "Presensi GPS" → "Attendify".
- `resources/views/layouts/presensi.blade.php:13`: `<title>Dashboard Presensi</title>` → `<title>Attendify</title>`.
- `resources/views/layouts/admin/tabler.blade.php:16`: `<title>Dashboard - Admin</title>` → `<title>Attendify - Dashboard Admin</title>`.
- `resources/views/layouts/guru/tabler.blade.php:16`: `<title>Dashboard - Guru</title>` → `<title>Attendify - Dashboard Guru</title>`.
- `composer.json`: name dan description.
- `.env.example`: `APP_NAME=Attendify`.
- Kata "presensi" sebagai istilah fitur (menu Monitoring Presensi, Rekap Presensi, teks halaman) TIDAK diganti.

## Urutan eksekusi

- [x] Fase 0: pindah checkout ke `D:\Dev\attendify`, verifikasi git (fsck, status, remote). Drive di-pause selama pemindahan, di-resume setelah dropzone siap.
- [x] Fase 1: buat dropzone berisi salinan file target (struktur folder mengikuti path repo) plus `SPEC-REBRAND.md`; tulis README Attendify.
- [ ] Fase 2: Argon mengedit file di dropzone, lalu Irfan mengabari OpenCode.
- [ ] Fase 3: OpenCode salin hasil dari dropzone ke checkout, review `git diff` hunk per hunk, grep string legacy habis, `php -l` file yang tersentuh, cek sampah sync (`desktop.ini`, BOM), commit `rebrand: Attendify UI, composer, dan metadata`, push ke `origin/main`.

## Aturan untuk Argon (ringkas; versi lengkap di dropzone)

- Hanya edit file yang disediakan di dropzone. Jangan menambah, menamai ulang, atau memindah file.
- Edit sebagai teks polos, bukan konversi Google Docs. UTF-8 tanpa BOM, line ending dipertahankan.
- Jangan mengubah logika kode, atribut, class, atau selector.

## Risiko yang pernah terjadi dan mitigasinya

- `desktop.ini` hasil Google Drive/Windows Explorer pernah masuk ke dalam `.git/refs` dan merusak referensi git. Sudah dibersihkan; `desktop.ini` ada di `.gitignore`. Setelah setiap operasi lintas Drive, cek `git fsck` dan grep file sampah.
- File hasil edit Drive bisa berubah encoding/BOM. Gerbang QA Fase 3 memeriksa diff per hunk sebelum commit.
