# Panduan Uji Live Attendify Tanpa Deploy (Cloudflare Tunnel)

- Tanggal: 15 November 2026.
- Untuk: seluruh anggota Kelompok 9 — uji fitur presensi (GPS + kamera) dari HP atau
  komputer lain **tanpa deploy ke AWS**.
- Prasyarat: PHP + MySQL Laragon jalan (lihat panduan database), folder project
  `D:\Dev\attendify`, internet aktif.

## 1. Kenapa Tidak Bisa Langsung dari HP

Geolocation API dan akses kamera (`getUserMedia`) adalah **fitur konteks aman**:
browser hanya mengizinkannya pada `https://` atau `localhost`. Halaman login dan
dashboard bisa dibuka lewat `http://<IP-PC>:8000` dari HP di WiFi yang sama, tapi
tombol presensi (GPS + selfie) tidak akan berfungsi di situ.

Solusi: **tunnel**. Aplikasi `cloudflared` membuat koneksi terenkripsi dari PC ke
jaringan Cloudflare dan memberi URL publik `https://xxxx.trycloudflare.com` dengan
sertifikat HTTPS asli milik Cloudflare:

```
HP / teman / dosen  --HTTPS-->  Cloudflare  --tunnel-->  PC kamu (:8000)
```

Semua syarat konteks aman terpenuhi tanpa deploy, tanpa domain, tanpa akun.

## 2. Peran TrustProxies (penting dipahami)

Laravel di belakang proxy perlu tahu bahwa request datang lewat HTTPS. Tanpa itu
Laravel menganggap koneksi `http://` sehingga URL internal dan CSRF jadi salah.
File `app/Http/Middleware/TrustProxies.php` di-repo sudah diatur:

```php
protected $proxies = '*';
```

Artinya: percayai header `X-Forwarded-*` dari tunnel/Load Balancer. Pengaturan ini
**sama yang dibutuhkan saat produksi di AWS ALB** — jadi bukan konfigurasi sementara.

## 3. Langkah Manual (2 Terminal)

**Terminal 1 — server Laravel:**
```
cd D:\Dev\attendify
php artisan serve --host=0.0.0.0 --port=8000
```
(`--host=0.0.0.0` sekalian membuka akses LAN; untuk tunnel saja localhost cukup.)

**Terminal 2 — tunnel:**
```
D:\Dev\attendify\tools\cloudflared.exe tunnel --url http://localhost:8000
```

Tunggu beberapa detik, cari baris seperti:
```
https://acak-kata.trycloudflare.com
```
Itu URL live. Buka di HP/laptop lain, login, dan coba presensi — browser akan
meminta izin **lokasi** dan **kamera** (tanda HTTPS bekerja).

### Verifikasi yang disarankan

1. Ada ikon gembok di address bar.
2. Login akun demo berfungsi (panduan database, bagian 2 langkah 4).
3. Halaman presensi siswa memunculkan prompt izin lokasi → izinkan → jarak < 20 m
   (koordinat sekolah di `PRESENSI_SEKOLAH_LAT/LNG` pada `.env`; saat uji jauh dari
   sekolah, emulator/lokasi palsu perlu diaktifkan di HP dev).
4. Kamera terbuka dan selfie tersimpan ke `storage/app/public/uploads/absensi/`.

## 4. Batasan dan Risiko yang Harus Dipahami

| Hal | Penjelasan |
| --- | --- |
| URL berubah setiap jalan | Quick tunnel tanpa akun selalu dapat URL acak baru; menutup terminal 2 = link mati. Alternatif permanen: akun ngrok (butuh daftar) atau named tunnel Cloudflare + domain sendiri. |
| Siapa pun dengan URL bisa mengakses | Perlindungan hanya login aplikasi. Gunakan untuk uji jangka pendek; jangan biarkan berhari-hari. |
| Database-nya nyata | Data presensi/izin orang lain yang mengakses URL masuk ke MySQL lokal. Setelah uji bersama, reset dengan `migrate:fresh --seed` (panduan database). |
| Kecepatan | Bergantung upload internet; foto selfie 1–2 MB per presensi. |
| APP_DEBUG | Jangan biarkan lama; error page membocorkan detail server (checklist Batch E). |

## 5. Alternatif LAN Saja (Tanpa Internet)

```
php artisan serve --host=0.0.0.0 --port=8000
```
Cek IP PC dengan `ipconfig`, lalu dari HP di WiFi yang sama buka
`http://<IP-PC>:8000`. Cocok untuk demo login, dashboard, monitoring, laporan —
**tanpa GPS/kamera** (HTTP tidak aman). Tunnel adalah satu-satunya cara menguji
fitur presensi dari HP tanpa deploy.

## 6. Catatan Teknis

1. `tools/cloudflared.exe` (±53 MB) tidak ikut git (ada di `.gitignore`). Anggota
   lain mengunduhnya sendiri:
   `https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe`
2. `ngrok` bawaan Laragon butuh akun/authtoken — tidak dipakai; quick tunnel
   cloudflared dipilih karena tanpa pendaftaran.
3. Backup database sebelum uji bersama bila ada data penting (panduan database,
   bagian 5).
