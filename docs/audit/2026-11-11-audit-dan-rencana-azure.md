# Audit Repository dan Rencana Proyek Komputasi Awan

- Tanggal audit: 11 November 2026.
- Repository: `nxtirfan/presensi-gps-selfie`.
- Revision sumber yang diaudit: `2c67a722fa044bc67c27c91434abfcf19447648a`.
- Status: audit statis dan usulan perencanaan, bukan hasil deployment.
- GeoHadir adalah nama usulan; TitikHadir, Tapak, SiniHadir, dan Hadiria merupakan alternatif diskusi, belum keputusan branding.
- Penyimpanan dokumen ini tidak berarti persetujuan untuk memperbaiki source code, mengubah database, mengganti branding, atau melakukan deployment.

## 1. Executive Summary

Project layak dilanjutkan untuk tugas akhir empat orang selama sekitar tiga minggu tanpa rewrite total, dengan syarat scope tetap terarah. Kode memiliki autentikasi tiga peran, CRUD siswa/angkatan, presensi GPS/selfie, izin, dan laporan. Namun, aplikasi belum layak langsung dipublikasikan sebelum perbaikan database, validasi, upload, dan konsistensi presensi.

### Bukti dan batas audit

Pemeriksaan mencakup routes, controllers, middleware, models, konfigurasi, Blade views, SQL schema, migrations, dependency manifests, dan tests. Lokasi file/baris di dokumen ini merujuk revision sumber di atas.

Yang dijalankan pada audit awal:
- Pemeriksaan sintaks PHP terhadap 61 file: lolos.
- `composer validate`: manifest valid.
- Pemeriksaan Git sebelum dan setelah audit: worktree bersih.

Yang belum dijalankan: aplikasi/browser, login/CRUD/GPS/kamera end-to-end, import database, migrations, PHPUnit, deployment, dan pemeriksaan subscription Azure. Folder `vendor/` dan `.env` tidak tersedia saat audit. Tidak ada dependency yang dipasang atau source yang diubah pada audit awal.

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
- `docs/`: laporan lama, ERD, screenshot historis.

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

Rencana produksi: APP_ENV production, APP_DEBUG false, APP_URL HTTPS, APP_KEY khusus environment, secure session cookie, user database non-root, document root public, dan larangan akses .env/source/dump/internal directory. `config/database.php:62` memiliki opsi CA MySQL, tetapi koneksi TLS Azure belum diuji.

Storage lokal cocok terbatas pada satu persistent VM; tidak otomatis cocok multi-instance. Azure disk/adapter belum didefinisikan dalam config/dependencies. Integrasi Blob butuh pemilihan adapter, validasi, penyesuaian path/URL, dan uji. Adapter komunitas yang diperiksa mendukung Laravel 10 tetapi versi saat audit memerlukan PHP ^8.2.

Logging Laravel sudah ada: default stack/single dan channel daily retensi 14 hari (`config/logging.php:54–72`), serta exception handler standar. Belum ada bukti log audit terstruktur, integrasi Monitor, alert, atau penanganan konsisten jika database sukses tetapi foto gagal disimpan.

Tests masih contoh. Feature test mengharapkan root 200, sedangkan route tamu mengarah login (`tests/Feature/ExampleTest.php:13–18`). Ini indikasi ekspektasi test perlu disesuaikan, bukan hasil PHPUnit gagal: tests belum dijalankan.

## 7. Kekurangan untuk memenuhi tugas akhir

Sebelum publik: migration/seed reproducible, sinkronisasi model/schema, perbaikan SQL dan validasi, upload aman, password/throttle/logout, route/redirect, duplikasi presensi, aturan izin/alpha, konfigurasi produksi, serta tests domain.

Bukti cloud yang harus dihasilkan: minimal tiga layanan benar-benar dipakai, HTTPS, NSG/firewall, backup snapshot dan uji restore, application/server log, monitoring, konfigurasi skalabilitas, laporan, video data dummy, dan AI Disclosure.

## 8. Rekomendasi arsitektur Microsoft Azure

### Alternatif A

Azure VM (Nginx + PHP-FPM + Laravel + MySQL lokal) + Blob Storage + Azure Monitor.

Browser → HTTPS/Nginx → Laravel → MySQL localhost. Laravel menulis foto ke Blob privat dan mengirim log terpilih ke Monitor/Log Analytics. Tiga layanan harus benar-benar dikonfigurasi, bukan hanya disebut.

Kelebihan: jumlah komponen lebih sedikit, biaya dasar relatif rendah. Kekurangan: aplikasi/DB berbagi VM, maintenance dan konsistensi snapshot ditanggung tim, scale-out lebih sulit.

### Alternatif B — rekomendasi utama bersyarat

Azure VM (Nginx + PHP-FPM + Laravel) + Azure Database for MySQL Flexible Server + Blob Storage + Azure Monitor.

Browser → HTTPS/Nginx → Laravel → MySQL terkelola melalui koneksi dibatasi/TLS. Foto disimpan pada Blob privat. B dipilih jika layanan/quota/kredit tersedia; A menjadi fallback, bukan janji semua benefit pasti aktif.

| Aspek | A | B |
| --- | --- | --- |
| Biaya dasar | Lebih rendah pada contoh sizing | Sedikit lebih tinggi |
| Konfigurasi | Awal sederhana; backup lebih manual | Koneksi/TLS/network lebih banyak |
| Keamanan | Isolasi app–DB lebih rendah | Pemisahan komponen lebih baik |
| Maintenance | OS dan DB ditangani tim | OS DB dan backup dasar dikelola layanan |
| Skalabilitas | Vertical scaling realistis | Scale-out aplikasi lebih mudah |
| Snapshot | Disk snapshot perlu koordinasi DB | Backup snapshot otomatis layanan |
| Rubrik | Tiga layanan bila diterima dosen | Compute/database/storage lebih eksplisit |

### Alur foto dan autentikasi

Pertahankan session auth Laravel. Browser mengirim selfie via HTTPS; Laravel memvalidasi user/lokasi/gambar, menyimpan objek privat, lalu mencatat object key di DB. Foto ditampilkan lewat endpoint berotorisasi atau temporary URL jika adapter mendukung. Jangan simpan source aplikasi sebagai objek publik.

### HTTPS, NSG, firewall

Gunakan domain/subdomain dan sertifikat tervalidasi. Nginx terminasi TLS, redirect HTTP ke HTTPS. SSH dibatasi IP tim. PHP-FPM tidak publik. MySQL A bind lokal; B memakai private connectivity jika realistis atau firewall ketat untuk sumber aplikasi jika endpoint publik diperlukan. Jangan membuka DB ke internet secara luas.

### Backup dan pemulihan

A: tempatkan data MySQL pada disk jelas, stop/quiesce DB sebelum snapshot demo agar konsisten, tambahkan logical backup terenkripsi, dan uji restore pada resource terpisah. Snapshot sembarang saat DB aktif tidak otomatis application-consistent.

B: backup otomatis snapshot+transaction log, retensi awal tujuh hari, restore ke server baru dan verifikasi data. Backup DB tidak mencakup Blob; tentukan proteksi/retensi foto terpisah.

### Logging/monitoring

Pantau CPU, disk, memori melalui agent bila dibutuhkan, serta koneksi/error DB. Kumpulkan log Laravel/Nginx/PHP-FPM secara terpilih dan lakukan rotasi. Jangan log password/base64 selfie atau lokasi lengkap tanpa kebutuhan. Demonstrasikan penelusuran satu kejadian. Platform metrics dan Log Analytics memiliki model biaya berbeda.

### Skalabilitas dan Load Balancer

Jika dosen menerima vertical scaling, demonstrasikan perubahan ukuran VM; jangan menyebutnya autoscaling/load balancing. Jika LB wajib, gunakan Standard LB dan dua backend untuk bukti distribusi/failover. LB Layer 4, TLS tetap pada Nginx. Gunakan database bersama (B), Blob bersama, session/cache yang sesuai multi-instance, health probe, dan scheduler tunggal. Satu VM di belakang LB bukan bukti distribusi antarserver. Autoscaling VM Scale Set memerlukan konfigurasi/uji tersendiri, bukan otomatis tersedia karena ada LB.

## 9. Estimasi biaya dan risiko

Angka berikut adalah snapshot perencanaan saat audit, bukan penawaran harga, jaminan quota, atau billing aktual. Dasar: Azure Retail Prices API, region southeastasia, USD, konsumsi retail Linux, 730 jam/bulan dan 504 jam/21 hari, tanpa diskon/free allowance mahasiswa.

| Komponen | Harga retail yang diperoleh saat audit |
| --- | ---: |
| Linux VM B1ms | US$0,0264/jam |
| Linux VM B2s | US$0,0528/jam |
| MySQL Flexible Server B1MS compute | US$0,026/jam |
| MySQL Flexible Server storage | US$0,138/GB-bulan |
| Standard HDD managed disk S4 | US$1,536/bulan |
| Standard public IPv4 | US$0,005/jam |
| Blob Hot LRS, tier awal penyimpanan | US$0,02/GB-bulan |
| Standard LB, kelompok rule awal | US$0,025/jam + pemrosesan data |

Sumber query: `https://prices.azure.com/api/retail/prices`, filter priceType Consumption dan armRegionName southeastasia. Meter Standard LB didapat pada armRegionName Global. VM Linux mengecualikan Windows/Spot/Low Priority. Harga aktual subscription dan SKU availability wajib dicek ulang di portal/calculator sebelum provisioning.

- A: B2s untuk app+DB, satu disk S4, satu public IP, 1 GB Blob: sekitar US$30/21 hari atau US$44/730 jam.
- B: B1ms app, MySQL B1MS dengan storage 32 GB, satu disk S4, satu public IP, 1 GB Blob: sekitar US$33/21 hari atau US$48/730 jam.
- B dengan LB dan VM app kedua selama 21 hari: tambahan dasar sekitar US$27, sebelum trafik/komponen lain. Batasi durasi resource demo.

Subtotal belum mencakup snapshot/backup tambahan, transaksi storage, egress, Log Analytics, restore server sementara, domain, pajak, atau konfigurasi tambahan. Disk HDD hanya asumsi sizing untuk estimasi, bukan jaminan performa. Uji beban demo menentukan kebutuhan sebenarnya.

Azure for Students menyebut kredit US$100 untuk 12 bulan bagi mahasiswa eligible tanpa kartu kredit. Saldo, quota, benefit, dan akses layanan tim belum diperiksa. Harga konsumsi di atas merupakan dasar estimasi pengurangan kredit, bukan instruksi beralih ke Pay-As-You-Go. Jangan menganggap managed database otomatis gratis.

Gunakan resource group/tag, budget alerts, dan hapus resource sementara. Budget alert tidak menghentikan resource. Deallocated menghentikan biaya compute VM, tetapi disk/networking dapat tetap berbiaya. Mematikan OS saja tidak sama dengan deallocate.

## 10. Roadmap tiga minggu

Semua pekerjaan implementasi berikut menunggu persetujuan.

### Minggu 1 — database reproducible dan aplikasi siap staging

- Irfan: cek saldo/benefit/quota/region dan estimasi; rancang Nginx/PHP-FPM, NSG, DNS/TLS; putuskan A/B/LB bersama kelompok.
- Riski: amankan whereRaw pada Presensi/GuruController; validasi auth/profil/upload/presensi; perbaiki routes/web.php dan check-out berulang; evaluasi dependency yang didukung.
- Isa: migration canonical dari presensigps.sql, seed dummy, sinkronisasi app/Models, constraint dan transaksi user/siswa.
- Febriana: perbaiki old_foto dan form siswa; tangani GPS/kamera gagal pada presensi/create.blade.php; matriks fitur dan draft laporan.
- Bukti target: bootstrap DB kosong, login tiga role, CRUD siswa/angkatan dan presensi dasar teruji dengan data dummy.

### Minggu 2 — staging cloud

- Irfan: provision resource yang disepakati; deploy document root public; HTTPS/NSG/cron/rotasi log.
- Riski: integrasi disk Blob dan akses foto berotorisasi; konsistensi izin/alpha; penanganan kegagalan file.
- Isa: integrasi DB/TLS, backup snapshot dan restore, pengujian relasi/transaksi/duplikasi.
- Febriana: uji perangkat nyata via HTTPS, histori/monitoring/laporan/foto; screenshot baru dan dokumentasi konfigurasi.
- Bukti target: URL HTTPS publik, file persisten, DB aktif, snapshot dan restore berhasil.

### Minggu 3 — operasional dan bukti akhir

- Irfan: Monitor/alert; vertical scaling atau LB dua backend sesuai rubrik; tinjau biaya/hapus resource sementara.
- Riski: tests role, input/upload invalid, request berulang, query laporan; perbaiki hasil uji tanpa memperluas scope.
- Isa: rekonsiliasi izin/alpha, verifikasi restore, finalisasi ERD/data dictionary.
- Febriana: laporan, bukti monitoring, video, dan koordinasi AI Disclosure.
- Bukti target: setiap syarat dosen memiliki konfigurasi dan hasil demonstrasi aktual.

## 11. Pembagian tugas anggota

Kelas SSD 2024. Nama kelompok belum ditentukan.

| Anggota | NIM | Peran awal / hasil utama |
| --- | --- | --- |
| M Irfan Al Hakim | 2404220023 | Ketua, Cloud/DevOps: arsitektur, Azure, HTTPS/NSG, monitoring, scaling, biaya |
| Riski Cahya Alfiansyah | 2404220019 | Backend: validasi/auth/presensi/laporan, Blob, tests |
| Isa Fitri Ulul Firdaus | 2404220033 | Database: migration/model/constraint/seed, backup/restore, ERD |
| Febriana Dwi Rahayu | 2404220042 | Frontend/Dokumentasi: GPS/kamera/form, UI testing, laporan/video |

Riski–Isa menyepakati schema lebih dahulu. Integrasi Blob menunggu keputusan kompatibilitas PHP/adapter. Bukti video harus berasal dari deployment yang diuji. Pembagian awal dapat disesuaikan.

## 12. Keputusan kelompok yang diperlukan

1. Nama kelompok dan nama produk final. GeoHadir maupun alternatif branding belum mengubah identitas aplikasi.
2. Scope lingkungan pendidikan: siswa, guru, angkatan; jangan menjanjikan produk lintas organisasi dulu.
3. Konfirmasi dosen: apakah LB wajib atau tiga layanan lain diterima; bukti snapshot/restore dan bentuk demonstrasi scaling.
4. B sebagai utama, A fallback berdasarkan quota/kredit; region, resource sizing, dan batas anggaran.
5. Domain/subdomain untuk HTTPS.
6. Titik demo/radius, terlambat, hari kegiatan, izin/alpha.
7. Upgrade terarah Laravel/PHP yang didukung, bukan rewrite.
8. Data dummy dan retensi foto; jangan publikasi data siswa lama.

Tidak perlu menambah mobile app, face recognition, CRUD lokasi, atau rewrite frontend tanpa kebutuhan kuat.

## 13. Draft Progress 1

Draft berikut harus disesuaikan setelah nama kelompok/produk disepakati. Ini rencana, bukan klaim layanan sudah aktif.

### A. IDENTITAS KELOMPOK

Nama kelompok: [Belum ditentukan]
Kelas: SSD 2024

1. M Irfan Al Hakim – 2404220023 – Ketua, Cloud/DevOps.
2. Riski Cahya Alfiansyah – 2404220019 – Backend Developer.
3. Isa Fitri Ulul Firdaus – 2404220033 – Database Administrator.
4. Febriana Dwi Rahayu – 2404220042 – Frontend Developer dan Dokumentasi.

### B. TOPIK APLIKASI

Judul usulan: **GeoHadir: Sistem Presensi Berbasis GPS dan Selfie**.

Jenis: CRUD App / aplikasi web dinamis.

GeoHadir merupakan pengembangan aplikasi Laravel yang menggunakan koordinat GPS untuk validasi jarak terhadap lokasi presensi dan foto selfie sebagai dokumentasi kehadiran. Aplikasi ditujukan bagi lingkungan pendidikan dengan peran Administrator, Guru, dan Siswa, serta pengelolaan siswa/angkatan, presensi, izin, riwayat, dan laporan. Pengembangan difokuskan pada penyempurnaan aplikasi dan penerapan infrastruktur cloud agar dapat diakses melalui HTTPS.

### C. PLATFORM CLOUD

Platform: **Microsoft Azure**.

Azure menyediakan layanan yang sesuai kebutuhan, yaitu Virtual Machines, Database for MySQL Flexible Server, Blob Storage, dan Monitor. Azure for Students menjadi pertimbangan pembelajaran, dengan resource disesuaikan terhadap kredit, biaya, quota, dan ketersediaan subscription. Load Balancer dievaluasi berdasarkan rubrik dan anggaran, belum menjadi layanan aktif.

### Catatan pengumpulan dan AI Disclosure

Instruksi Progress 1 yang dibagikan meminta format A–C, deskripsi 2–3 kalimat, satu kiriman oleh ketua, serta topik tidak sama dengan kelompok lain. Perubahan topik/platform setelah pengiriman perlu konfirmasi dosen. Tanggal tenggat pada pengumuman: 11 November 2026 pukul 23.00; dokumen ini tidak membuktikan tugas sudah dikumpulkan.

AI digunakan untuk membantu audit statis, identifikasi risiko, penelitian dokumentasi Azure, serta penyusunan rencana/dokumentasi. Kelompok harus meninjau hasilnya dan memperbarui disclosure sesuai bantuan yang benar-benar digunakan. AI belum melakukan deployment atau membuktikan fitur runtime pada audit ini.

## Referensi eksternal

Referensi diperiksa pada audit 11 November 2026; kondisi/harga dapat berubah.

- Laravel 10 dan akhir dukungan keamanan (4 Februari 2025): https://laravel.com/docs/10.x/releases
- Azure for Students: https://azure.microsoft.com/en-us/free/students
- Azure Retail Prices API: https://learn.microsoft.com/en-us/rest/api/cost-management/retail-prices/azure-retail-prices
- Endpoint harga: https://prices.azure.com/api/retail/prices
- MySQL backup/restore snapshot dan transaction log: https://learn.microsoft.com/en-us/azure/mysql/flexible-server/concepts-backup-restore
- Azure Monitor cost: https://learn.microsoft.com/en-us/azure/azure-monitor/fundamentals/cost-usage
- Azure Load Balancer: https://learn.microsoft.com/en-us/azure/load-balancer/load-balancer-overview
- Budget bukan penghenti resource: https://learn.microsoft.com/en-us/azure/cost-management-billing/costs/tutorial-acm-create-budgets
- VM states/billing: https://learn.microsoft.com/en-us/azure/virtual-machines/states-billing
- Adapter Blob komunitas dan requirement: https://github.com/Azure-OSS/azure-storage-php-adapter-laravel/blob/main/composer.json
