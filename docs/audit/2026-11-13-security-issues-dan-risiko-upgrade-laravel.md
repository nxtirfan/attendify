# Audit Keamanan Aplikasi dan Risiko Upgrade Laravel

- Tanggal dokumen: 13 November 2026.
- Revision sumber yang diaudit: `a986fa6` (ui: fase 3 identitas admin & guru).
- Status: audit statis lanjutan atas hasil pemeriksaan runtime tanggal 12–13 November 2026.
- Produk: Attendify — Sistem Presensi Berbasis GPS dan Selfie (Kelompok 9, Komputasi Awan, platform AWS).
- Konteks: dokumen ini menjawab pertanyaan "apakah aplikasi sudah aman untuk di-cloud-kan" dan
  memetakan risiko upgrade Laravel untuk menutup risiko security di tingkat framework.

## 1. Ringkasan Eksekutif

Aplikasi **layak dideploy ke cloud untuk kebutuhan tugas akhir** dengan syarat konfigurasi
production ditetapkan benar (APP_DEBUG=false, HTTPS, cookie secure, password DB kuat) dan
dua perbaikan kode ditutup lebih dulu: **whitelist tipe file upload** (risiko tertinggi)
dan **rate limit login**. Kerangka Laravel 10 dan PHP 8.1 lokal sudah **akhir masa
dukungan**; ada dua jalur mitigasi dengan tingkat risiko berbeda yang dibahas di Bagian 5:
patch dalam major yang sama (murah, direkomendasikan sekarang) dan upgrade major
(Laravel 11/12, berisiko terhadap dependency, ditunda setelah demo atau disepakati dulu).

Yang sudah tertutup sebelumnya (Batch A, commit `47686f3`):
- SQL injection `whereRaw` bulan/tahun diganti query binding.
- Endpoint debug yang membocorkan informasi dihapus.

## 2. Temuan yang Sudah Aman (Terverifikasi 13 November 2026)

| Area | Bukti | Status |
| --- | --- | --- |
| SQL Injection | `app/Http/Controllers/*.php` — semua `selectRaw` berisi SQL statis tanpa interpolasi input user; binding query dipakai untuk input dinamis | Aman |
| CSRF | `app/Http/Middleware/VerifyCsrfToken.php` — `$except` kosong; seluruh form Blade memuat `@csrf` | Aman |
| Mass assignment | Semua model (`app/Models/*.php`) mendeklarasikan `$fillable` | Aman |
| IDOR presensi | `PresensiController::store()` mengambil `siswa_id` dari `Auth::user()`, bukan dari input | Aman |
| Validasi jarak presensi | Dihitung ulang di server (`distance()` + `config('presensi.radius_m')`), tidak percaya input klien | Aman |
| Auth | `AuthController` memakai `Auth::attempt()` + hash bcrypt; form login divalidasi | Aman |
| Bocor kredensial | `.env` tidak ter-commit; `desktop.ini` dan mirror lokal dibersihkan | Aman |

Catatan: status "aman" berdasarkan pemeriksaan statis + uji runtime tanggal 12–13 November
(lihat dokumen audit AWS untuk metode). Bukan penjaminan formal.

## 3. Temuan Aktif (Belum Diperbaiki)

Skala: Tinggi / Sedang / Rendah / Info.

### V1 — Upload file tanpa whitelist tipe (Tinggi)
- Lokasi: `PresensiController.php:185,218` (foto profil) dan
  `SiswaController.php:142,161,189,209` (CRUD siswa).
- Nama file dibangun dari `getClientOriginalExtension()` — ekstensi apa pun yang dikirim
  user diterima (mis. `.php`, `.phtml`, `.phar`). Foto presensi (`store()`) menerima base64
  apa pun tanpa cek mime/ukuran, disimpan sebagai `.png`.
- Risiko: pada konfigurasi web server yang salah (PHP dieksekusi di bawah `storage/` atau
  `public/uploads/`), file `.php` yang diunggah dieksekusi → **remote code execution**.
  Pada konfigurasi benar (file hanya disajikan statis) risikonya turun menjadi upload
  file berbahaya + storage abuse.
- Rencana: validasi `mimes:jpg,jpeg,png|max:2048` di semua titik upload; ekstensi dipaksa
  dari whitelist server, bukan dari file klien; checklist nginx/Apache "deny php execution
  under storage/". Ini item pertama Batch E.

### V2 — APP_DEBUG=true untuk produksi (Tinggi)
- Lokasi: `.env.example` (`APP_DEBUG=true`). Halaman error Ignition yang aktif saat debug
  menampilkan SQL lengkap, path server, dan versi (pernah teramati runtime saat MySQL
  mati — lihat catatan sesi QA 13 November).
- Risiko: kebocoran informasi (kredensial internal, struktur DB, path) ke pengunjung mana pun
  yang memicu error.
- Rencana: `.env.production.example` dengan `APP_DEBUG=false`, `APP_ENV=production`,
  logging ke stderr; error page generik.

### V3 — Login tanpa rate limit (Sedang)
- Lokasi: `routes/web.php` — route `/proseslogin` tanpa middleware `throttle`.
- Risiko: brute force password tidak terbatas.
- Rencana: `throttle:5,1` pada route login (Batch E).

### V4 — Cookie sesi belum `secure` (Sedang)
- Lokasi: `config/session.php` / `.env` — `SESSION_SECURE_COOKIE` tidak diatur.
- Risiko: cookie sesi lewat koneksi tanpa TLS bisa disadap (juga prasyarat: Geolocation API
  menuntut HTTPS, jadi HTTPS sudah wajib karena alasan fitur).
- Rencana: `SESSION_SECURE_COOKIE=true`, `SESSION_SAMESITE=lax`, `SESSION_HTTPONLY=true`
  di konfigurasi produksi.

### V5 — Password default siswa = NIS (Sedang)
- Lokasi: `SiswaController.php:131` — `bcrypt($nis)`.
- Risiko: password default mudah ditebak (NIS bersifat publik/semipublik di lingkungan
  sekolah); hash bcrypt tetap aman, masalahnya di kekuatan awal.
- Rencana: dokumen mengharuskan siswa mengganti password lewat Ubah Profil; disebutkan di
  laporan; (opsional) force-change pada login pertama.

### V6 — Validasi input minim di controller non-auth (Rendah)
- Lokasi: `PresensiController` (store izin, update profil) dan `SiswaController`
  (store/update siswa) tidak memanggil `$request->validate()`; nilai ditulis langsung.
- Risiko: data kotor (string panjang, format salah) masuk DB; tidak langsung eksploitasi
  karena query tetap berbinding, tapi merusak integritas data.
- Rencana: tambah rules dasar (string max, format, angka) saat menyentuh controller di Batch E.

### V7 — Output tidak ter-escape `{!! !!}` (Rendah)
- Lokasi: `resources/views/guru/getpresensi.blade.php:32`,
  `resources/views/presensi/cetaklaporan.blade.php:167`.
- Ekspresinya hanya memilih antara jam dari DB (dibuat sistem) dan HTML statis, jadi risiko
  XSS riil rendah — tetap dicatat karena polanya salah jika dikopi ke data lain.
- Rencana: ganti ke `{{ }}` + badge lewat Blade `@if` (sampingan, bisa menyusul Batch E).

### V8 — Ukuran gambar base64 tidak dibatasi (Rendah)
- Lokasi: `PresensiController::store()` — `base64_decode` tanpa batas panjang.
- Risiko: payload besar memakan memori/disk (denial kecil).
- Rencana: batas ukuran sebelum decode.

### V9 — `explode(',', $lokasi)` tanpa guard (Info)
- Lokasi: `PresensiController::store()` — input kosong menghasilkan indeks `[1]` tidak ada.
- Risiko: error saat runtime, bukan keamanan. Ditutup bersama validasi V6.

### V10 — Basis data lokal root tanpa password (Info, hanya lingkungan dev)
- Laragon lokal memakai root kosong. Untuk AWS: password kuat, DB di security group
  tertutup, bukan publik. Masuk checklist deploy.

## 4. Status Versi PHP & Laravel (Konteks CVE)

Fakta per 13 November 2026:

| Komponen | Versi terpasang | Masa dukungan keamanan | Implikasi |
| --- | --- | --- | --- |
| PHP lokal (Laragon) | 8.1.10 (build Ag 2022) | PHP 8.1 selesai dukungan keamanan 31 Desember 2025 | Banyak CVE setelah tanggal itu tidak ditambal di 8.1; build lokal 2022 juga kehilangan ~4 tahun patch |
| PHP target di AWS | direkomendasikan 8.3.x atau 8.4.x terbaru | masih didukung penuh | wajib dipilih saat provisioning EC2 |
| laravel/framework | 10.48.28 | Laravel 10 selesai patch keamanan 4 Februari 2025 (EOL) | patch keamanan Laravel setelah itu tidak diterima |
| Patch dalam major | 10.50.4 tersedia (`composer show -l`) | — | satu-satunya jalur patch Laravel 10 yang tersisa |
| laravel/sanctum | 3.3.3 | naik ke 4.x bersama Laravel 11+ | ikut jadwal upgrade |
| guzzlehttp/guzzle | 7.9.2 (8.x tersedia) | 7.x masih dirawat | bukan penghalang upgrade |

Kesimpulan versi: aplikasi tidak sedang "terinfeksi" CVE tertentu hari ini, tetapi
**framework dan PHP berada di luar garis dukungan**, sehingga kerentanan baru apa pun
tidak akan menerima patch resmi. Risiko ini naik seiring waktu.

## 5. Risiko Upgrade Laravel terhadap Dependency dan Aplikasi

### 5.1 Dua jalur mitigasi

| Jalur | Isi | Risiko terhadap aplikasi | Estimasi usaha | Rekomendasi |
| --- | --- | --- | --- | --- |
| A. Patch dalam major (10.48.28 → 10.50.4) | menutup patch keamanan Laravel 10 terakhir; tidak ada perubahan API | sangat rendah; laravel/framework, guzzle, tinker masih dalam rentang `composer.json` | 15–30 menit + smoke test | **Lakukan sekarang** |
| B. Upgrade major (Laravel 10 → 11 → 12) | mendapat patch keamanan baru dari major aktif | sedang–tinggi (lihat 5.3) | 1–3 hari + pengujian menyeluruh | tunda sampai setelah demo TA / disepakati tim |

### 5.2 Batas versi yang ikut bergeser (jalur B)

| Paket | Sekarang | Laravel 11 | Laravel 12 | Dampak |
| --- | --- | --- | --- | --- |
| PHP | ^8.1 | wajib ≥ 8.2 | wajib ≥ 8.2 | PHP lokal Laragon harus dinaikkan dulu |
| laravel/framework | ^10.10 | ^11.0 | ^12.0 | core upgrade |
| laravel/sanctum | ^3.3 | ^4.0 | ^4.0 | API token tidak dipakai app; dampak rendah |
| laravel/tinker | ^2.8 | ^5.0 | ^5.0 | hanya alat dev |
| nunomaduro/collision | ^7.0 | ^8.0 | ^8.0 | dev |
| phpunit/phpunit | ^10.1 | ^11.0 | ^11.0 | dev; suite test app nyaris kosong |
| spatie/laravel-ignition | ^2.0 | tidak perlu di Laravel 11 (error page bawaan baru) | — | dilepas |
| guzzlehttp/guzzle | ^7.2 | ^7.8.2 masih didukung | ^7.8.2 / ^8 | boleh tetap 7.x, opsional naik |
| laravel/pint, sail, faker, mockery | ^1.x / ^1.9 | dev-only, versi terbaru | sama | rendah |

### 5.3 Perubahan struktural Laravel 11/12 yang menyentuh aplikasi ini

1. **Skeleton dipangkas** — `app/Http/Kernel.php` dihapus di Laravel 11+; middleware
   pendaftaran (alias `cekrole`, `ThrottleRequests`, CSRF) pindah ke `bootstrap/app.php`
   (`->withMiddleware()`). Aplikasi ini punya middleware custom `cekrole:Siswa|Admin|Guru`
   di tiga group route → wajib di-port, kalau tidak semua halaman 403/404.
2. **Konfigurasi slim** — banyak file `config/` jadi opsional (di-publish via
   `config:publish`); `config/presensi.php` kita adalah config custom dan tidak terdampak.
3. **Routes sama** — sintaks `Route::middleware([...])->group()` tetap; file `routes/web.php`
   hampir tidak berubah.
4. **Blade, Eloquent, session, hash** — API yang dipakai aplikasi (query builder, `Auth::attempt`,
   `Storage`, `Hash::make`, `@csrf`, pagination) tidak berubah perilaku pada 11/12.
5. **Dependency frontend** (MobileKit, Tabler, Leaflet, WebcamJS, jQuery) statis dan tidak
   terkait composer → tidak terdampak.
6. **Skema database** — migration kita canonical dan self-contained; Laravel 11/12 tidak
   menuntut perubahan skema. `2019_12_14_000000_create_users_table` default yang tidak
   kita pakai sudah dibersihkan lebih dulu (Batch B).

### 5.4 Risiko fungsional yang harus diuji ulang setelah upgrade (jalur B)

1. Login tiga peran (siswa/guru/admin) + redirect peran.
2. Middleware `cekrole` di bootstrap/app.php (regressi paling mungkin).
3. Upload foto profil + presensi base64 (untuk V1 setelah diperbaiki).
4. Sinkronisasi izin → presensi (`approveizinsakit`).
5. Cetak laporan/rekap (query besar `selectRaw` 31 kolom).
6. Sesi & CSRF (cookie name/format sama; `php artisan storage:link` ulang di server baru).

### 5.5 Keputusan yang perlu tim ambil

- Jalur A dieksekusi tanpa perlu persetujuan lebih (perubahan minor, sudah dihormati
  `composer.json`).
- Jalur B menunggu keputusan: dilakukan **setelah demo TA** (aman) atau **sebelum deploy
  final** (lebih berat, butuh jadwal pengujian ulang). Tidak dianjurkan mengerjakan jalur B
  di tengah persiapan Batch D karena menyentuh infrastruktur middleware yang sama.

## 6. Urutan Pengerjaan yang Disarankan (Batch E)

1. **E1 (sekarang):** `composer update laravel/framework` dalam rentang 10.x → 10.50.4,
   jalankan smoke test (login, presensi, cetak).
2. **E2:** tutup V1 (whitelist upload) dan V3 (throttle login) di kode; tambah validasi V6/V8
   seadanya.
3. **E3:** siapkan `.env.production.example` + checklist deploy AWS (V2, V4, V10, deny-php
   di storage, `storage:link`, HTTPS wajib untuk Geolocation).
4. **E4:** pilih PHP 8.3/8.4 terbaru saat provisioning EC2 (bukan PHP 8.1 milik Laragon).
5. **E5 (keputusan terpisah):** upgrade Laravel 11/12 — setelah demo TA atau sebelum rilis
   final, dengan daftar uji 5.4.
