# Attendify

Attendify: Sistem Presensi Berbasis GPS dan Selfie. Aplikasi web Laravel untuk lingkungan pendidikan dengan peran Administrator, Guru, dan Siswa.

Attendify merupakan kelanjutan pengembangan dari [PresensiGPS](https://github.com/nxtirfan/presensi-gps-selfie), sistem presensi berbasis GPS dan foto selfie.

## Fitur

- Login menggunakan username atau NIS dengan tiga peran: Admin, Guru, Siswa.
- CRUD siswa dan angkatan.
- Presensi masuk/pulang dengan koordinat GPS dan foto selfie.
- Validasi jarak terhadap titik lokasi presensi.
- Riwayat presensi per bulan.
- Pengajuan dan approval izin/sakit.
- Monitoring presensi pada peta.
- Laporan dan rekap presensi.
- Alpha (absen tanpa keterangan) dihitung otomatis oleh scheduler harian.

## Teknologi

| Komponen | Keterangan |
| --- | --- |
| Backend | PHP 8.1+, Laravel 10 |
| Database | MySQL |
| Frontend | Blade, Bootstrap/Tabler, jQuery, Leaflet/OpenStreetMap |
| Kamera | WebcamJS |
| Build | Vite 5 |

## Menjalankan di lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
# sesuaikan DB_DATABASE, DB_USERNAME, DB_PASSWORD pada .env
php artisan migrate --seed
php artisan serve
```

Catatan: `php artisan migrate` saja belum membangun seluruh schema aplikasi; migration canonical sedang disiapkan (lihat `docs/audit/`).

## Struktur dokumentasi

- `docs/audit/2026-11-12-audit-dan-rencana-aws.md`: audit repository dan rencana deployment AWS.
- `docs/plan/2026-11-12-rencana-rebrand.md`: rencana rebranding dan alur kerja.
- `docs/`: laporan lama, ERD, dan screenshot historis dari PresensiGPS.

## Tim

Kelompok 9, kelas SSD-24:

| Anggota | NIM | Peran |
| --- | --- | --- |
| M Irfan Al Hakim | 2404220023 | Ketua, Cloud/DevOps |
| Isa Fitri Ulul Firdaus | 2404220033 | Backend |
| Febriana Dwi Rahayu | 2404220042 | Database |
| Riski Cahya Alfiansyah | 2404220019 | Frontend & Dokumentasi |
