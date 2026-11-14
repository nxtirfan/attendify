# Panduan Database Attendify: Migration, Seeder, dan Backup

- Tanggal: 15 November 2026.
- Untuk: seluruh anggota Kelompok 9 — Irfan, Isa, Febriana, Riski.
- Konteks: hasil Batch B membuat migration canonical; dokumen ini menjelaskan cara
  memakainya sehari-hari, isi tiap migration, serta cara backup/restore, supaya
  semua anggota bisa membangun ulang database yang identik dari repo saja.

## 1. Tiga Konsep yang Sering Tertukar

| Konsep | Menangani apa | Alat | Analogi |
| --- | --- | --- | --- |
| Migration | **Struktur** tabel (kolom, tipe, FK, index) | `php artisan migrate` | cetak cetak-blanko kosong |
| Seeder | **Isi data awal** (akun, angkatan) | `php artisan db:seed` | isi formulir contoh |
| Backup (mysqldump) | **Snapshot seluruh isi** pada satu titik waktu | `mysqldump.exe` | foto keadaan |

- Database Attendify **bisa dibangun ulang dari repo**: migration + seeder ter-commit,
  sehingga `migrate:fresh --seed` di komputer mana pun menghasilkan database identik.
- Migration **tidak menyentuh file foto** di `storage/app/public/uploads/`. Data
  transaksi (presensi, izin, foto) sengaja tidak dibuat seeder karena itu data demo.

## 2. Cara Manual: Reset Database ke State Awal

Prasyarat: MySQL Laragon jalan (port 3306, cek `D:\laragon\bin\mysql\...`),
terminal dibuka di folder project `D:\Dev\attendify`.

1. **Backup dulu** (kebiasaan wajib — `migrate:fresh` tanpa backup = data hilang permanen):
   ```
   D:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe -u root e_presensi > backup-sebelum-reset.sql
   ```
2. **Reset struktur + isi awal:**
   ```
   php artisan migrate:fresh --seed
   ```
   Arti per bagian: `migrate` = jalankan file migration yang belum jalan (buat tabel);
   `fresh` = drop semua tabel dulu, baru migration dari nol; `--seed` = jalankan
   `DatabaseSeeder` setelah struktur selesai. Boleh dipecah dua: `migrate:fresh`
   lalu `db:seed` — hasilnya sama.
3. **Hapus file foto sisa uji secara manual** (migration/seeder tidak menyentuh file):
   - `storage\app\public\uploads\siswa\` — hapus file sisa (mis. `24023.png`)
   - `storage\app\public\uploads\absensi\` — hapus isinya jika ada
   - via terminal: `del storage\app\public\uploads\siswa\24023.png`
4. **Verifikasi** di `http://127.0.0.1:8000`:
   - `admin1` / `admin123` (Admin)
   - `guru1` / `password1` (Guru)
   - siswa NIS `24023`, `23002`, `22018` / `password1` (username otomatis dari nama, tanpa spasi, huruf kecil)
   - Dashboard siswa menampilkan tombol **Presensi Masuk** (presensi kosong).

### Cheat-sheet perintah artisan

| Perintah | Kapan dipakai |
| --- | --- |
| `php artisan migrate:fresh --seed` | reset total ke state awal (persiapan demo) |
| `php artisan migrate` | menambah tabel/kolom baru saja — data lama tidak hilang |
| `php artisan db:seed` | mengisi ulang data seed tanpa menghapus apa pun |
| `php artisan migrate:status` | melihat migration mana sudah/belum jalan |
| `php artisan migrate:rollback` | membatalkan **batch terakhir** via method `down()` — beda konsep dengan fresh |
| `php artisan migrate:refresh` | rollback semua lalu migrate lagi (tanpa seed kecuali ditambah `--seed`) |

## 3. Isi Tiap Migration

Urutan file = urutan eksekusi (`database/migrations/`). Setiap file punya dua method:
`up()` (membangun) dan `down()` (membongkar, dipakai `rollback`).

### 3.1 `2024_01_01_000001_create_user_table.php` — tabel `user`
Pusat autentikasi; satu baris per akun.
- `user_id` — primary key auto increment.
- `username` — unik; login memakai username (siswa) atau kode admin/guru.
- `password` — hash bcrypt (tidak pernah plaintext).
- `role` — enum `Siswa` | `Guru` | `Admin`; menentukan middleware `cekrole` dan
  halaman dashboard yang dituju.
- `remember_token` — nullable; dipakai fitur "ingat saya" Laravel.

### 3.2 `2024_01_01_000002_create_angkatan_table.php` — tabel `angkatan`
- `angkatan_id` — primary key **diisi manual** oleh seeder (22/23/24), bukan auto
  increment, supaya seed deterministik.
- `nama_angkatan` — nama tampilan, mis. "Angkatan 2024".

### 3.3 `2024_01_01_000003_create_siswa_table.php` — tabel `siswa`
Data identitas siswa (terpisah dari akun login di `user`).
- `siswa_id` — primary key auto increment.
- `user_id` — FK ke `user`, `cascadeOnDelete`: hapus akun = data siswa ikut hilang.
- `nis` — unik; dipakai sebagai NIS login dan nama file foto.
- `nama_lengkap`, `kelas` (maks 10 karakter), `no_hp` (maks 20).
- `angkatan_id` — FK ke `angkatan`, `nullOnDelete`: angkatan dihapus → kolom jadi
  NULL, siswa tidak ikut terhapus.
- `foto` — nullable; **nama file** di `storage/app/public/uploads/siswa/`
  (bukan isi file). NULL berarti tampil avatar bawaan.
- `alamat`, `tanggal_lahir` — nullable, opsional.

### 3.4 `2024_01_01_000004_create_guru_table.php` — tabel `guru`
Sama polanya dengan siswa: `guru_id` PK, `user_id` FK cascade, `nama_lengkap`,
`no_hp`, `email`. Tidak punya kolom foto/kelas karena kebutuhannya beda.

### 3.5 `2024_01_01_000005_create_presensi_table.php` — tabel `presensi`
Satu baris = satu hari presensi satu siswa.
- `presensi_id` PK; `siswa_id` FK cascade.
- `tgl_presensi` (date), `jam_in`/`jam_out` (time, nullable — pulang belum dilakukan
  → `jam_out` masih NULL).
- `lokasi_in`/`lokasi_out` — koordinat "lat,lng" saat presensi (maks 255).
- `foto_in`/`foto_out` — nama file selfie di `storage/app/public/uploads/absensi/`.
- `status` — enum `hadir` | `terlambat` | `izin` | `sakit` | `alpha`. Nilai
  `hadir`/`terlambat` dihitung server dari `config('presensi.jam_masuk')`;
  `izin`/`sakit` diisi otomatis saat izin disetujui; `alpha` diisi tombol
  "Generate Alpha" admin.
- `created_at`/`updated_at` — diisi otomatis MySQL.
- **`unique(['siswa_id', 'tgl_presensi'])`** — kunci anti-duplikat: satu siswa
  maksimal satu baris per hari; percobaan presensi ganda ditolak di level DB
  (dan di controller, perbaikan Batch C).

### 3.6 `2024_01_01_000006_create_izin_table.php` — tabel `izin`
Pengajuan izin/sakit siswa.
- `izin_id` PK; `siswa_id` FK cascade; `tgl_izin` (date).
- `keterangan` — nullable, maks 255 karakter.
- `status` — enum `izin` | `sakit` (jenis pengajuan).
- `status_approved` — enum `pending` (default) | `disetujui` | `ditolak`; diubah
  guru lewat halaman Izin & Sakit.
- `unique(['siswa_id', 'tgl_izin'])` — satu pengajuan per siswa per tanggal.

Alur sinkron izin → presensi (fitur Batch C): saat guru menyetujui, controller
membuat/menimpa baris `presensi` tanggal tersebut dengan `status` = izin/sakit,
sehingga monitoring, rekap, dan laporan langsung konsisten.

## 4. Peta Relasi

```
user (1) ──cascade──> siswa (banyak) ──cascade──> presensi
  │                        │   └─cascade──> izin
  │                        └─nullOnDelete──> angkatan
  └──cascade──> guru
```

- `user.siswa`/`user.guru` diakses lewat relasi model (Batch B) sehingga controller
  mengambil identitas dari `Auth::user()`, bukan dari input — ini juga alasannya
  aman dari IDOR.
- Semua FK bertipe `unsignedInteger` mengikuti tipe PK tabel induknya
  (`increments()`).

## 5. Backup dan Restore (mysqldump)

Perintah mysqldump berasal dari MySQL Laragon (biner yang sama dipakai menjalankan
mysqld). Selalu jalankan dari folder project.

**Buat backup:**
```
D:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqldump.exe -u root e_presensi > backup-2026-11-15.sql
```

**Balikin (restore):**
```
D:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe -u root e_presensi < backup-2026-11-15.sql
```

Aturan penting:
1. Backup hanya berisi **SQL (isi data)** — file foto di `storage/` tidak ikut;
   foto harus diamankan dengan menyalin folder `storage/app/public/uploads/`.
2. Restore menimpa isi database dengan keadaan saat backup dibuat; jalankan
   `migrate:fresh --seed` dulu kalau strukturnya ingin disamakan penuh, lalu
   restore untuk mengisi data.
3. Backup sebelum `migrate:fresh`, sebelum deploy, dan sebelum demo — buat
   penamaan bertanggal.
4. Di AWS nanti (checklist Batch E): password DB kuat, bukan root, dan port 3306
   tidak dipublikasikan.

## 6. Catatan Deployment

1. Di server produksi struktur dibangun dengan `php artisan migrate` (bukan fresh!)
   — fresh hanya untuk lingkungan dev/demo.
2. Seeder produksi hanya boleh membuat akun admin awal; data siswa nyata diinput
   lewat UI admin.
3. Setelah deploy jalankan juga `storage:link`, `config:cache`, `route:cache`,
   `view:cache` (checklist Batch E, dokumen audit keamanan).
