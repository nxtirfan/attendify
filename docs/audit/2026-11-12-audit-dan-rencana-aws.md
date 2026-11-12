# Audit Repository dan Rencana Proyek Komputasi Awan

- Audit awal: 11 November 2026. Revisi dokumen: 12 November 2026.
- Repository aktif: `nxtirfan/attendify`.
- Revision sumber yang diaudit: `2c67a722fa044bc67c27c91434abfcf19447648a`.
- Status: audit statis dan usulan perencanaan, bukan hasil deployment.
- Produk: Attendify: Sistem Presensi Berbasis GPS dan Selfie. Nama lama GeoHadir dan alternatif TitikHadir/Tapak/SiniHadir/Hadiria tidak digunakan lagi.
- Attendify merupakan pengembangan aplikasi semester sebelumnya (`nxtirfan/presensi-gps-selfie-lama`, arsip), bukan aplikasi baru dari nol. Fokus lingkungan pendidikan.
- Platform final kelompok: AWS. Keputusan ini diambil setelah evaluasi platform pada 12 November 2026. Jika pengumpulan Progress 1 telah dilakukan dengan platform lain, perubahan platform wajib dikonfirmasi ke dosen sebelum implementasi.
- Penyimpanan dokumen ini tidak berarti persetujuan untuk memperbaiki source code, mengubah database, mengubah branding, atau melakukan deployment.

## 1. Executive Summary

Project layak dilanjutkan untuk tugas akhir empat orang selama sekitar tiga minggu tanpa rewrite total, dengan syarat scope tetap terarah. Kode memiliki autentikasi tiga peran, CRUD siswa/angkatan, presensi GPS/selfie, izin, dan laporan. Namun, aplikasi belum layak langsung dipublikasikan sebelum perbaikan database, validasi, upload, dan konsistensi presensi.

### Bukti dan batas audit

Pemeriksaan mencakup routes, controllers, middleware, models, konfigurasi, Blade views, SQL schema, migrations, dependency manifests, dan tests. Lokasi file/baris di dokumen ini merujuk revision sumber di atas.

Yang dijalankan pada audit awal:
- Pemeriksaan sintaks PHP terhadap 61 file: lolos.
- `composer validate`: manifest valid.
- Pemeriksaan Git sebelum dan setelah audit: worktree bersih.

Yang belum dijalankan: aplikasi/browser, login/CRUD/GPS/kamera end-to-end, import database, migrations, PHPUnit, deployment, pemeriksaan benefit/saldo/kuota AWS, dan pemeriksaan regional availability. Folder `vendor/` dan `.env` tidak tersedia saat audit. Tidak ada dependency yang dipasang atau source yang diubah pada audit awal.

Istilah **terimplementasi di kode** tidak berarti **sudah terbukti berfungsi saat runtime**. Screenshot dan laporan lama bukan bukti runtime revision sekarang.

## 2. Identifikasi teknologi dan struktur repository

| Komponen | Fakta dari repository |
| --- | --- |
| Backend | PHP, Laravel 10; lockfile memuat `laravel/framework` 10.48.28 |
| PHP | Requirement `^8.1` pada `composer.json` |
| Frontend | Blade, JavaScript/jQuery, Bootstrap/Tabler |
| Peta | Leaflet dan tile OpenStreetMap |
| Kamera | WebcamJS; `resources/views/layouts/script.blade.php:17` |
| Build | Vite 5, Laravel Vite Plugin; `package.json`, `vite.config.js` |
| Database | MySQL-compatible; `config/database.php`, `presensigps.sql` |
| Auth | Laravel session authentication; `config/auth.php`, `AuthController.php` |
| Storage | Laravel Storage, default disk local; `config/filesystems.php:16` |
| Logging | File Laravel; `config/logging.php:54–72` |
| Scheduler | Alpha harian 23:45; `app/Console/Kernel.php:13–18` |

Struktur penting:
- `app/Http/Controllers/`: login, dashboard, presensi, siswa, angkatan, fungsi Guru.
- `app/Http/Middleware/CekRole.php`: otorisasi role.
- `app/Models/`: user, siswa, guru, angkatan, presensi, izin, jadwal.
- `routes/web.php`: route aplikasi web.
- `resources/views/`: UI Blade.
- `config/`: environment-backed configuration.
- `database/migrations/`: migration framework, belum schema domain aplikasi.
- `presensigps.sql`: schema enam tabel aplikasi dan contoh data.
- `storage/`: log, session, cache, upload lokal.
- `public/`: entry point dan aset.
- `tests/`: tests contoh.
- `docs/`: laporan lama, ERD, screenshot historis, audit.

Ada `public/.htaccess` untuk Apache. Konfigurasi Nginx/provisioning server siap deploy belum ditemukan pada pemeriksaan. Blade yang diperiksa menggunakan aset publik/CDN; pencarian tidak menemukan `@vite`, sehingga Vite tidak otomatis menjadi jalur build seluruh UI.

`.env.example` memakai mode lokal, debug aktif, URL localhost, database lokal, file cache/session, queue sync, dan disk local. Ini bukan konfigurasi produksi. `config/app.php` memakai timezone Asia/Jakarta dan locale id.

## 3. Inventarisasi fitur yang tersedia

Semua status berikut adalah bukti implementasi kode, belum hasil uji runtime.

| Fitur | Bukti | Catatan |
| --- | --- | --- |
| Login username/NIS | `AuthController::proseslogin()` | Mencari user langsung atau melalui siswa |
| Admin/Guru/Siswa | `CekRole.php`, `routes/web.php` | Ada middleware role |
| Dashboard | `DashboardController.php` | Redirect Guru perlu diperbaiki |
| CRUD siswa | `SiswaController.php:106–225` | Validasi, foto, dan integritas akun perlu diperbaiki |
| CRUD angkatan | `AngkatanController.php` | Memenuhi entitas CRUD kedua |
| Edit profil/password | `PresensiController.php:153–211` | Validasi perlu diperkuat |
| Presensi masuk/pulang | `PresensiController.php:26–115` | Check-out berulang dapat menimpa data |
| GPS/radius | Controller di atas; `presensi/create.blade.php:111–135` | Titik sekolah dan radius 20 meter hardcoded |
| Selfie | `presensi/create.blade.php:102–146` | WebcamJS, base64 ke server |
| Riwayat | `PresensiController.php:215–249` | Query bulan/tahun perlu diamankan |
| Izin/sakit | `PresensiController.php:252–286` | Pengajuan tersedia |
| Approval izin | Fungsi approval pada Presensi/GuruController | Belum menyinkronkan status presensi |
| Monitoring/peta | `getpresensi()`, `tampilkanpeta()` | Admin dan Guru |
| Laporan/rekap | `cetaklaporan()`, `cetakrekap()` | Admin dan Guru |
| Export Excel | `PresensiController.php:367–370` | HTML dengan header/ekstensi .xls, bukan workbook native |
| Alpha otomatis | `generateAlpha()`, Console Kernel | Butuh scheduler server; aturan izin/hari kegiatan belum lengkap |

Belum ditemukan implementasi lengkap CRUD lokasi, CRUD guru/admin melalui UI, atau manajemen jadwal. Model Jadwal saja tidak membuktikan fitur jadwal tersedia. Face recognition dan liveness tidak ditemukan.

### Alur GPS dan selfie

Browser menggunakan `navigator.geolocation.getCurrentPosition()`, lalu mengirim koordinat melalui request. Server menghitung jarak terhadap titik sekolah dan menolak jika hasil pembulatan melebihi 20 meter. Callback error GPS kosong dan submit belum ditangani lengkap ketika lokasi belum tersedia.

WebcamJS mengambil data URI/base64; server mendekode lalu menulis file .png ke `public/uploads/absensi/` melalui default disk local. Foto adalah dokumentasi kiriman pengguna, bukan verifikasi identitas otomatis.

### Defek perilaku spesifik

- Presensi kedua dan berikutnya dianggap check-out; waktu/foto pulang dapat tertimpa (`PresensiController.php:50–110`).
- Approval memperbarui tabel izin, sedangkan alpha hanya memeriksa keberadaan presensi (`generateAlpha():118–135` dan fungsi approval).
- Alpha tidak memeriksa kalender kegiatan/libur.
- Root mengarahkan Guru ke `dashboard.guru`, sementara route bernama `guru` (`routes/web.php:41–55,143`).
- Nama route berulang: store/edit/update/delete serta monitoring/laporan dan lainnya (`routes/web.php:92–153`); risiko salah named URL dan route caching.
- `old_foto` tidak memiliki atribut name pada edit siswa, tetapi dibaca controller (`resources/views/siswa/edit.blade.php:82–83`, `SiswaController.php:181–210`).
- Selector `$("frmSiswa")` pada validasi UI salah (`resources/views/siswa/index.blade.php:391–396`).
- Path upload dan `Storage::url('public/uploads/...')` berpotensi tidak selaras dengan symlink standar; harus diuji, bukan dianggap sudah rusak atau sudah benar.

## 4. Audit database

### Entitas pada dump

| Tabel | Primary key | Foreign key |
| --- | --- | --- |
| USER | user_id | — |
| ANGKATAN | angkatan_id | — |
| SISWA | siswa_id | user_id → USER; angkatan_id → ANGKATAN |
| GURU | guru_id | user_id → USER |
| PRESENSI | presensi_id | siswa_id → SISWA |
| IZIN | izin_id | siswa_id → SISWA |

Bukti: `presensigps.sql:7–74`. NIS unique. Relasi user ke profil dimaksudkan satu-ke-satu pada model, tetapi user_id profil belum unique pada dump. Siswa memiliki banyak presensi dan izin; angkatan memiliki banyak siswa.

### Penghambat deployment

1. Migration membuat `users` dengan id/name/email, tetapi aplikasi memakai singular `user`, user_id, username (`2014_10_12_000000_create_users_table.php:14–22`, `User.php:13–25`). Migrate saja tidak membangun database aplikasi.
2. Dump diawali DROP DATABASE dan CREATE DATABASE (`presensigps.sql:2–4`). Jangan menjalankannya langsung pada database deployment.
3. Dump memakai nama tabel uppercase, model/query lowercase. Perilaku pada Linux bergantung konfigurasi MySQL; konsistenkan penamaan.
4. Nama database contoh environment dan dump berbeda. Tetapkan melalui environment dan migration, jangan mengandalkan dump destruktif.
5. Seeder tidak memiliki seed aktif (`DatabaseSeeder.php:13–21`).

### Ketidaksesuaian model/schema

- Presensi mencantumkan jadwal_id dan lokasi; dump tidak menyediakan jadwal_id dan memakai lokasi_in/lokasi_out (`Presensi.php:13–33`).
- Model Jadwal ada, tabelnya tidak ada pada dump (`Jadwal.php:9–22`).
- Angkatan dan Izin belum mengatur primary key/timestamps sesuai dump.
- User mencantumkan nama_lengkap fillable, kolomnya tidak ada pada USER.

Sebagian controller memakai query builder sehingga tidak semua mismatch pasti langsung mematahkan alur, tetapi model belum dapat dianggap benar.

### Integritas

- Username belum unique; belum ada unique `(siswa_id, tgl_presensi)`.
- Insert user dan siswa terpisah tanpa transaksi (`SiswaController.php:134–160`).
- Count-then-insert presensi rentan duplikasi request bersamaan (`PresensiController.php:50–58,94–107`).
- Delete siswa hanya menghapus siswa secara eksplisit; akun login dapat tertinggal (`SiswaController.php:218–219`). Cascade user→siswa tidak berlaku sebaliknya.

Rekomendasi: migration canonical, penamaan konsisten, seed dummy aman, sinkronisasi model, constraint sesuai aturan produk, dan transaksi. Putuskan apakah jadwal benar-benar diperlukan; jangan menambah scope hanya karena modelnya ada.

## 5. Audit keamanan

| Prioritas | Temuan | Bukti / tindakan rencana |
| --- | --- | --- |
| Tinggi | Input bulan/tahun dirangkai ke whereRaw | `PresensiController.php:235–244,362–363,449–450`; pola serupa GuruController. Validasi dan binding/whereMonth/whereYear |
| Tinggi | Upload belum tervalidasi memadai | SiswaController store/update, PresensiController updateprofile/store. Validasi isi/MIME, ukuran, dimensi, base64, nama server |
| Tinggi | Password awal siswa berasal dari NIS | `SiswaController.php:131`; ganti mekanisme yang tidak mudah ditebak |
| Tinggi | GPS dan gambar dapat dikirim melalui request buatan | PresensiController store; dokumentasikan batas anti-spoofing |
| Tinggi | Laravel 10 sudah berakhir dukungan keamanan | composer.lock; evaluasi upgrade terarah |
| Sedang | Login belum memiliki throttle khusus | Route login/AuthController |
| Sedang | Logout GET, invalidasi session/token belum lengkap | `routes/web.php:83`, `AuthController.php:69–72` |
| Sedang | Endpoint utilitas hash masih publik | `routes/web.php:63–68,156–158`; hapus/nonaktifkan pada tahap implementasi |
| Sedang | old_foto dari request dipakai untuk delete | `SiswaController.php:187–209`; ambil dari database |
| Sedang | Desain foto public berisiko akses tanpa otorisasi | Storage public dan view URL; pindahkan ke akses privat |

Tidak dilakukan eksploitasi. Tidak semua query builder rentan: masalah konkret adalah interpolasi request ke raw SQL.

Hal positif: password di-hash, role middleware tersedia, identitas siswa pada presensi diambil dari user login. Tidak adanya regenerate manual saat login sendiri bukan bukti session fixation karena Laravel guard juga melakukan migrasi session.

CSRF middleware web aktif tanpa pengecualian khusus; form/request yang diperiksa menyertakan token. Logout GET tetap perlu diperbaiki. Banyak output Blade escaped; ada raw output jam pulang/badge, tetapi jalur stored XSS yang dapat dieksploitasi belum dibuktikan.

### Secret dan privasi

Pemeriksaan teks tracked revision ini tidak menemukan bukti jelas cloud API key/private key aktif. `.env` tidak tersedia dan diabaikan Git. Ada literal password pada endpoint utilitas serta pola password default; nilainya sengaja tidak dicantumkan. Dump memuat contoh akun/data, jangan dijadikan akun publik.

Ini bukan sertifikasi bebas secret untuk seluruh history Git atau isi biner dokumen. Sebelum publikasi, lakukan scan historis dan tinjau dokumen lama tanpa menyalin nilai sensitif ke laporan.

HTTPS melindungi transport, bukan membuktikan lokasi fisik atau keaslian wajah. Scope realistis: validasi input, batas presensi, waktu server, log kejadian, dan disclosure keterbatasan. Tidak perlu menambah face recognition.

## 6. Analisis kesiapan deployment

Linux layak secara teknologi, belum dibuktikan runtime. Kebutuhan: Nginx, PHP-FPM, extension PHP sesuai lockfile (termasuk PDO MySQL, mbstring, XML, cURL, fileinfo), Composer, MySQL, cron scheduler, TLS, serta permission storage/bootstrap cache. Node hanya diperlukan jika pipeline aset memakainya.

Rencana produksi: APP_ENV production, APP_DEBUG false, APP_URL HTTPS, APP_KEY khusus environment, secure session cookie, user database non-root, document root public, dan larangan akses .env/source/dump/internal directory. `config/database.php:62` memiliki opsi CA MySQL, tetapi koneksi TLS ke RDS belum diuji.

Storage lokal cocok terbatas pada satu persistent instance; tidak otomatis cocok multi-instance. Repository memuat definisi disk `s3` pada `config/filesystems.php`, tetapi package driver S3 (`league/flysystem-aws-s3-v3`) belum tercantum pada `composer.json`, jadi integrasinya belum aktif. Mengganti `FILESYSTEM_DISK` saja tidak cukup: perlu driver, IAM role/izin, perbaikan alur penulisan dan pembacaan foto, serta uji temporary URL/endpoint berotorisasi.

Logging Laravel sudah ada: default stack/single dan channel daily retensi 14 hari (`config/logging.php:54–72`), serta exception handler standar. Belum ada bukti log audit terstruktur, integrasi CloudWatch, alert, atau penanganan konsisten jika database sukses tetapi foto gagal disimpan. Log Laravel tidak otomatis mengalir ke CloudWatch; perlu CloudWatch Agent atau pipeline terpisah.

Tests masih contoh. Feature test mengharapkan root 200, sedangkan route tamu mengarah login (`tests/Feature/ExampleTest.php:13–18`). Ini indikasi ekspektasi test perlu disesuaikan, bukan hasil PHPUnit gagal: tests belum dijalankan.

## 7. Kekurangan untuk memenuhi tugas akhir

Sebelum publik: migration/seed reproducible, sinkronisasi model/schema, perbaikan SQL dan validasi, upload aman, password/throttle/logout, route/redirect, duplikasi presensi, aturan izin/alpha, konfigurasi produksi, serta tests domain.

Bukti cloud yang harus dihasilkan: minimal tiga layanan benar-benar dipakai, HTTPS, security group/firewall, backup snapshot dan uji restore, application/server log, monitoring, konfigurasi skalabilitas, laporan, video data dummy, dan AI Disclosure.

## 8. Rancangan arsitektur AWS

Kombinasi terpilih (dasar rencana):

Browser
   │ HTTPS
   ▼
EC2 Linux: Nginx → PHP-FPM → Laravel
   ├── RDS MySQL privat
   ├── S3 bucket privat (foto selfie)
   └── CloudWatch Logs/Metrics

- EC2 pada public subnet dengan satu public IP; SSH dibatasi IP tim.
- RDS tidak publicly accessible; DB subnet group sesuai, instance Single-AZ untuk demo.
- Security Group RDS menerima MySQL hanya dari Security Group aplikasi.
- EC2 memakai IAM role untuk akses S3, bukan access key permanen dalam repository.
- Bucket memblokir akses publik; foto ditampilkan lewat endpoint berotorisasi atau temporary URL.
- HTTPS ditangani Nginx dengan domain/subdomain dan sertifikat tervalidasi; redirect HTTP ke HTTPS; PHP-FPM tidak publik.
- Tidak membutuhkan NAT Gateway untuk rancangan dasar ini.
- Tiga kategori layanan yang dipakai nyata: compute (EC2), database (RDS), object storage (S3). CloudWatch menjadi kategori tambahan untuk logging/monitoring. Security Group tidak dihitung sebagai layanan ketiga.

### Alur foto dan autentikasi

Pertahankan session auth Laravel. Browser mengirim selfie via HTTPS; Laravel memvalidasi user/lokasi/gambar, menyimpan objek privat ke S3, lalu mencatat object key di DB. Foto ditampilkan lewat route berotorisasi atau temporary URL S3. Jangan buat bucket publik dan jangan simpan source aplikasi sebagai objek.

### Backup dan pemulihan

RDS automated backup dan manual snapshot untuk bukti. Tentukan retensi awal (misal tujuh hari), restore ke instance terpisah, lalu verifikasi data. Backup database tidak mencakup foto di S3; tetapkan retensi/proteksi foto terpisah. Uji restore wajib didokumentasikan sebagai bukti tugas.

### Logging dan monitoring

Kumpulkan log Laravel (daily), Nginx error/access, dan metrik resource (CloudWatch Agent). Batasi retensi dan hindari password/base64 selfie dalam log. Demonstrasikan penelusuran satu kejadian end-to-end. CloudWatch ingestion memiliki tarif per GB; batasi volume agar estimasi biaya tidak meleset.

### Skalabilitas dan Load Balancer

Awali satu aplikasi + satu managed DB. Vertical scaling (mengubah ukuran EC2/RDS) berbeda dari autoscaling/load balancing; jangan menyamakan keduanya dalam laporan. Jika dosen mewajibkan Load Balancer: gunakan dua backend EC2 untuk bukti distribusi/failover, database dan S3 bersama, session/cache yang sesuai multi-instance, health probe, TLS tetap pada Nginx, dan scheduler tunggal agar alpha tidak digandakan. Biaya dan konfigurasi LB belum termasuk estimasi dasar.

## 9. Estimasi biaya AWS

Angka berikut adalah snapshot perencanaan 11 November 2026, bukan penawaran harga, jaminan quota, atau billing aktual. Sumber: AWS Price List resmi region ap-southeast-1 (Singapura), retail, tanpa diskon/free allowance.

| Komponen | Tarif yang diperoleh saat audit |
| --- | ---: |
| EC2 t3.small | US$0,0264/jam |
| RDS MySQL db.t3.micro, Single-AZ | US$0,026/jam |
| RDS storage GP3 20 GB | US$0,138/GB-bulan |
| EBS GP3 30 GB (disk aplikasi) | US$0,096/GB-bulan |
| Public IPv4 | US$0,005/jam |
| S3 Standard 1 GB | US$0,025/GB-bulan |

Asumsi: infrastruktur aktif 504 jam (21 hari), tambahan 72 jam demonstrasi, prorata storage 730 jam/bulan, foto 1 GB.

| Durasi | Estimasi |
| --- | ---: |
| Pengerjaan 21 hari | US$32,84 |
| Tambahan demonstrasi 3 hari | US$4,69 |
| Total 24 hari | US$37,53 |

Estimasi belum mencakup: request object storage, transfer data berbayar, CloudWatch ingestion di luar allowance (referensi tarif Standard ingestion Singapura saat audit: US$0,70/GB), alarm/metrik tambahan, snapshot/backup berlebih, server sementara hasil restore, CPU credit surplus pada instance burstable, domain, dan pajak. Untuk demo kecil, batasi log, retensi, dan trafik; jadikan US$50 batas anggaran internal awal, bukan jaminan tagihan.

Program Free Tier saat audit: akun baru mendapat US$100 awal, dapat memperoleh hingga US$100 tambahan melalui aktivitas eligible (tidak otomatis), dan Free Plan berlaku hingga enam bulan atau kredit habis. Jangan gunakan asumsi program lama EC2/RDS gratis 12 bulan untuk akun baru. Tanggal kedaluwarsa dan saldo aktual wajib dicek di Billing Console; saldo tidak membuktikan semua SKU tersedia.

Yang sebaiknya tidak ditambahkan dulu: NAT Gateway tanpa kebutuhan jelas, Load Balancer ke satu server, HA database untuk demo kecil, bastion berbayar, layanan analytics/AI yang tidak dipakai.

Gunakan tag/budget alert dan hapus resource sementara. Budget alert tidak menghentikan resource; instance yang di-stop tidak selalu menghentikan seluruh biaya (EBS dan IPv4 tetap ditagih bila disiapkan).

## 10. Roadmap tiga minggu

Semua pekerjaan implementasi berikut menunggu persetujuan.

### Minggu 1 — database reproducible dan aplikasi siap staging

- Irfan: cek benefit/saldo/kuota/region AWS dan estimasi; rancang Nginx/PHP-FPM, Security Group, DNS/TLS; konfirmasi keputusan LB ke dosen.
- Isa: amankan whereRaw pada Presensi/GuruController; validasi auth/profil/upload/presensi; perbaiki routes/web.php dan check-out berulang.
- Febriana: migration canonical dari presensigps.sql, seed dummy, sinkronisasi app/Models, constraint dan transaksi user/siswa.
- Riski: perbaiki old_foto dan form siswa; tangani GPS/kamera gagal pada presensi/create.blade.php; matriks fitur dan draft laporan.
- Bukti target: bootstrap DB kosong, login tiga role, CRUD siswa/angkatan dan presensi dasar teruji dengan data dummy.

### Minggu 2 — staging cloud AWS

- Irfan: provision EC2/RDS/S3; deploy document root public; HTTPS/security group/cron/rotasi log; IAM role untuk S3.
- Isa: integrasi disk S3 (driver, alur foto, akses berotorisasi/temporary URL); konsistensi izin/alpha; penanganan kegagalan file.
- Febriana: integrasi DB/TLS, backup snapshot dan restore, pengujian relasi/transaksi/duplikasi.
- Riski: uji perangkat nyata via HTTPS, histori/monitoring/laporan/foto; screenshot baru dan dokumentasi konfigurasi.
- Bukti target: URL HTTPS publik, foto persisten di S3 privat, DB aktif, snapshot dan restore berhasil.

### Minggu 3 — operasional dan bukti akhir

- Irfan: CloudWatch agent/alert; vertical scaling atau LB dua backend sesuai rubrik; tinjau biaya/hapus resource sementara.
- Isa: tests role, input/upload invalid, request berulang, query laporan; perbaiki hasil uji tanpa memperluas scope.
- Febriana: rekonsiliasi izin/alpha, verifikasi restore, finalisasi ERD/data dictionary.
- Riski: laporan, bukti monitoring, video, dan koordinasi AI Disclosure.
- Bukti target: setiap syarat dosen memiliki konfigurasi dan hasil demonstrasi aktual.

## 11. Pembagian tugas anggota

Nama kelompok: Kelompok 9. Kelas: SSD-24.

| Anggota | NIM | Peran / hasil utama |
| --- | --- | --- |
| M Irfan Al Hakim | 2404220023 | Ketua, Cloud/DevOps: arsitektur AWS, EC2/RDS/S3, security group, monitoring, scaling, biaya |
| Isa Fitri Ulul Firdaus | 2404220033 | Backend: validasi/auth/presensi/laporan, integrasi S3, tests |
| Febriana Dwi Rahayu | 2404220042 | Database: migration/model/constraint/seed, backup/restore, ERD |
| Riski Cahya Alfiansyah | 2404220019 | Frontend & Dokumentasi: GPS/kamera/form, UI testing, laporan/video |

Pembagian ini pembagian terbaru dan menggantikan pembagian pada dokumen audit lama. Integrasi S3 menunggu penambahan driver dan uji. Bukti video harus berasal dari deployment yang diuji.

## 12. Keputusan kelompok yang diperlukan

1. Platform final: AWS. Jika Progress 1 sudah dikumpulkan dengan platform lain, konfirmasi perubahan ke dosen sebelum provisioning.
2. Konfirmasi dosen: apakah LB wajib atau tiga layanan lain diterima; bentuk bukti snapshot/restore dan demonstrasi scaling.
3. Region (misal ap-southeast-1), sizing resource, dan batas anggaran.
4. Domain/subdomain untuk HTTPS.
5. Titik demo/radius, terlambat, hari kegiatan, izin/alpha.
6. Upgrade terarah Laravel/PHP yang didukung, bukan rewrite.
7. Data dummy dan retensi foto; jangan publikasi data siswa lama.

Tidak perlu menambah mobile app, face recognition, CRUD lokasi, atau rewrite frontend tanpa kebutuhan kuat.

## 13. Draft Progress 1

Draft berikut sudah memakai Attendify dan AWS. Ini rencana, bukan klaim layanan sudah aktif.

### A. IDENTITAS KELOMPOK

Nama kelompok: Kelompok 9
Kelas: SSD-24

1. M Irfan Al Hakim – 2404220023 – Ketua, Cloud/DevOps.
2. Isa Fitri Ulul Firdaus – 2404220033 – Backend Developer.
3. Febriana Dwi Rahayu – 2404220042 – Database Administrator.
4. Riski Cahya Alfiansyah – 2404220019 – Frontend Developer dan Dokumentasi.

### B. TOPIK APLIKASI

Judul: **Attendify: Sistem Presensi Berbasis GPS dan Selfie**.

Jenis: CRUD App / aplikasi web dinamis.

Attendify merupakan pengembangan aplikasi Laravel yang menggunakan koordinat GPS untuk validasi jarak terhadap lokasi presensi dan foto selfie sebagai dokumentasi kehadiran. Aplikasi ditujukan bagi lingkungan pendidikan dengan peran Administrator, Guru, dan Siswa, serta pengelolaan siswa/angkatan, presensi, izin, riwayat, dan laporan. Pengembangan difokuskan pada penyempurnaan aplikasi dan penerapan infrastruktur cloud agar dapat diakses melalui HTTPS.

### C. PLATFORM CLOUD

Platform: **Amazon Web Services (AWS)**.

AWS dipilih karena sesuai dengan teknologi Attendify yang menggunakan Laravel dan MySQL. Amazon EC2 digunakan untuk menjalankan aplikasi, Amazon RDS for MySQL untuk database terkelola beserta backup dan pemulihan, Amazon S3 untuk penyimpanan foto privat, serta Amazon CloudWatch untuk logging dan monitoring. Integrasi S3 didukung driver yang didokumentasikan Laravel sehingga tidak memerlukan adapter khusus, sementara IAM role memungkinkan pembatasan akses aplikasi tanpa menyimpan kredensial permanen dalam repository. Pemilihan resource disesuaikan dengan kebutuhan demonstrasi, ketersediaan layanan pada Free Plan, serta anggaran.

### Catatan pengumpulan dan AI Disclosure

Instruksi Progress 1 meminta format A–C, deskripsi 2–3 kalimat, satu kiriman oleh ketua, serta topik tidak sama dengan kelompok lain. Perubahan platform setelah pengiriman perlu konfirmasi dosen; jika kiriman pertama memakai platform lain, sampaikan perubahan ke AWS pada konfirmasi tersebut.

AI digunakan untuk membantu audit statis, identifikasi risiko, penelitian dokumentasi AWS, serta penyusunan rencana/dokumentasi. Kelompok harus meninjau hasilnya dan memperbarui disclosure sesuai bantuan yang benar-benar digunakan. AI belum melakukan deployment atau membuktikan fitur runtime pada audit ini.

## Referensi eksternal

Referensi diperiksa pada audit 11 November 2026; kondisi/harga dapat berubah.

- Laravel filesystem/S3: https://laravel.com/docs/10.x/filesystem
- Laravel 10 dan akhir dukungan keamanan (4 Februari 2025): https://laravel.com/docs/10.x/releases
- AWS Free Tier: https://aws.amazon.com/free/
- AWS Free Tier FAQ: https://aws.amazon.com/free/free-tier-faqs/
- VPC pricing (public IPv4, NAT): https://aws.amazon.com/vpc/pricing/
- RDS automated backup: https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/USER_WorkingWithAutomatedBackups.html
- AWS Price List EC2 ap-southeast-1: https://pricing.us-east-1.amazonaws.com/offers/v1.0/aws/AmazonEC2/current/ap-southeast-1/index.csv
- AWS Price List RDS ap-southeast-1: https://pricing.us-east-1.amazonaws.com/offers/v1.0/aws/AmazonRDS/current/ap-southeast-1/index.csv
- AWS Price List S3 ap-southeast-1: https://pricing.us-east-1.amazonaws.com/offers/v1.0/aws/AmazonS3/current/ap-southeast-1/index.csv
- CloudWatch pricing: https://aws.amazon.com/cloudwatch/pricing/
