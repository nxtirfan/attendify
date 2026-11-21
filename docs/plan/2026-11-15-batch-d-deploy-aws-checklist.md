# Checklist Deployment Batch D — Attendify ke AWS

- Tanggal: 15 November 2026.
- Platform: AWS **new experience** (project), Region **wajib `ap-southeast-2`**.
- Profil CLI: `attendify`. Akun: **FREE**, kredit $100 (exp. 19 Mei 2027), resource aktif 0.
- Cara pakai: dicentang berurutan. Tidak ada perintah yang boleh dijalankan sebelum
  keputusan di Bagian 0 selesai.
- Dokumen terkait: `docs/audit/2026-11-12-audit-dan-rencana-aws.md` (arsitektur & estimasi
  biaya), `docs/audit/2026-11-13-security-issues-dan-risiko-upgrade-laravel.md` (checklist
  keamanan Batch E), `docs/panduan/2026-11-15-panduan-database-migration-seeder-backup.md`,
  `docs/panduan/2026-11-15-panduan-setup-aws-dan-agent-toolkit.md`.

## Status Persiapan (21 November 2026)

Sudah dibuat — **semua tanpa biaya, EC2/RDS TIDAK dijalankan**:

| Resource | ID / Nama | Fungsi |
| --- | --- | --- |
| IAM Role | `AttendifyEC2Role` + instance profile `AttendifyEC2Profile` | akses SSM Session Manager (tanpa SSH/port 22) |
| Security Group | `sg-0d13cb9856708663f` (`attendify-app-sg`) | inbound 80/443 publik, kelola via SSM |
| Launch Template | `lt-0eebe7ed393ef0d71` (`attendify-lt`) | t3.micro, AMI AL2023 `ami-09a2f74ce8538e414`, EBS 20 GB gp3, `CreditSpecification=standard` (anti CPU-credit boros), IMDSv2 `required`, tag `Project=Attendify`/`Env=demo` |
| Bootstrap | `deploy/user-data.sh` | PHP 8.3 + Nginx + Composer + clone repo + `storage:link` + cron `schedule:run`; deny PHP di `/storage` |
| Database lokal | `migrate:fresh --seed` berhasil | 4 siswa tim, 0 presensi, 0 izin |

Jumlah instance EC2 saat ini: **0**. Biaya: **$0**, kredit $100 utuh.

Belum bisa dilanjut tanpa keputusan: domain/HTTPS, kebutuhan LOAD Balancer dari dosen,
dan aktivasi layanan RDS (API masih `OptInRequired`).

## 0. Keputusan yang Harus Dikunci Dulu

- [ ] **Domain / subdomain + HTTPS** — wajib karena Geolocation & kamera hanya jalan di
      konteks aman. Pilih: domain sendiri + Let's Encrypt di EC2, atau sertifikat ACM di ALB
      (butuh domain terverifikasi). Tanpa domain, uji tetap pakai Cloudflare tunnel
      (`docs/panduan/2026-11-15-panduan-uji-live-cloudflare-tunnel.md`).
- [ ] **Topologi**: (A) satu EC2 semua-in-one (termasuk MySQL) — paling murah & cukup untuk
      demo; atau (B) sesuai dosen: ALB + 2 EC2 + RDS bersama, session bersama, scheduler
      hanya di 1 instance.
- [ ] **Database**: RDS MySQL `db.t3.micro` Single-AZ privat (layanan RDS perlu diaktifkan —
      saat ini API masih `OptInRequired`), atau MySQL di EC2 jika tetap topologi A.
- [ ] **Anggaran internal**: batas $50 (audit) / lebih rendah untuk demo; verify kartu
      pembayaran di **AWS Settings → Billing → Manage** sebelum upgrade ke paid.
- [ ] Konfirmasi Region project di **AWS Settings → View all projects → Overview →
      Additional Info → Region** = `ap-southeast-2`.

## 1. Preflight (5 menit)

- [ ] Refresh kredensial (berlaku 12 jam):
      `aws login --region ap-southeast-2 --profile attendify`
- [ ] `aws sts get-caller-identity --profile attendify` → Account `865609249386`
- [ ] `aws freetier get-account-plan-state --region us-east-1 --profile attendify` → kredit > 0
- [ ] Repo bersih & HEAD ter-push; commit terakhir `3d9cdb9`
- [ ] Buka sesi OpenCode baru (skill AWS & aturan new experience aktif)

## 2. Jaringan & Keamanan

- [ ] VPC + subnet di **`ap-southeast-2`** saja; tanpa NAT Gateway (~$30/bln — tidak dipakai)
- [ ] Security Group aplikasi: inbound SSH (22) **hanya dari IP tim**, HTTP 80 & HTTPS 443
      dari publik, outbound 3306 ke SG DB
- [ ] Security Group DB: MySQL 3306 **hanya dari SG aplikasi**, DB tidak publicly accessible
- [ ] IAM role EC2 untuk akses S3 (jangan simpan access key di `.env`/repo)
- [ ] Tag semua resource: `Project=Attendify`, `Env=demo`

## 3. Kompute & Web Server

- [ ] EC2 `t3.micro`/`t3.small` (Free Tier / audit: t3.small ~$0.0264/jam), AMI Linux
- [ ] Instal PHP **8.3/8.4 terbaru** (bukan 8.1 Laragon) + ekstensi: pdo_mysql, mbstring,
      xml, curl, gd, zip, intl, bcmath
- [ ] Instal Nginx (atau Apache), PHP-FPM, Composer; document root = `public/`
- [ ] `git clone https://github.com/nxtirfan/attendify.git`
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] Permission: `storage/` & `bootstrap/cache/` writable oleh user web server
- [ ] Cron server (BARIS INI WAJIB): `* * * * * cd /path/attendify && php artisan schedule:run >> /dev/null 2>&1`
      — menjalankan `generateAlpha` harian 23:45 (`app/Console/Kernel.php`); kalau topologi B,
      pasang cron **hanya di satu EC2** supaya alpha tidak digandakan

## 4. Database

- [ ] Aktifkan layanan RDS bila memilih DB managed; instance `db.t3.micro`, Single-AZ,
      storage 20 GB, backup retention 7 hari, subnet privat
- [ ] Buat user DB non-root dengan password kuat (simpan di AWS Secrets Manager atau `.env`
      server, **jangan** di git)
- [ ] Buat skema **dari migration** — `php artisan migrate` (JANGAN `migrate:fresh` di
      produksi, JANGAN jalankan `presensigps.sql` yang berisi DROP DATABASE)
- [ ] Seeder hanya akun admin awal bila perlu; data siswa nyata diinput via UI admin
- [ ] Snapshot/manual backup RDS + uji restore ke instance terpisang → bukti untuk laporan
- [ ] Pastikan backup SQL tidak menimpa file foto (foto dipantau terpisah)

## 5. Konfigurasi Aplikasi & HTTPS

- [ ] `.env` produksi dari `.env.production.example`: `APP_ENV=production`,
      `APP_DEBUG=false`, `APP_URL=https://<domain>`, `SESSION_SECURE_COOKIE=true`,
      `LOG_CHANNEL=stderr`, DB host RDS
- [ ] `php artisan key:generate`
- [ ] `php artisan storage:link`
- [ ] `php artisan config:cache && php artisan route:cache && php artisan view:cache`
- [ ] Nginx/Apache: **deny eksekusi PHP di bawah `/storage`** (lihat checklist Batch E poin 6)
- [ ] sertifikat HTTPS terpasang + redirect HTTP→HTTPS; uji `https://<domain>` muncul gembok
- [ ] Verifikasi halaman cetak (logo kop sudah pakai `favicon.png`)

## 6. Uji Fungsi Setelah Deploy (QA Akhir)

- [ ] Login 3 peran: `admin1/admin123`, `guru1/password1`, siswa `24023/password1`
- [ ] Presensi siswa: prompt lokasi muncul, jarak valid, selfie tersimpan di storage/S3,
      entri `presensi` muncul di monitoring & dashboard
- [ ] Izin siswa → guru setujui → baris presensi status izin/sakit muncul (sinkronisasi)
- [ ] Admin: CRUD siswa/angkatan, laporan & rekap cetak PDF (A4, logo, tanpa rubuh)
- [ ] Cek thumbnail presensi 42×42 & CTA presensi kondisional masih benar di desktop
- [ ] Cek `storage` URL foto profil bisa diakses lewat HTTPS

## 7. Cost Guard & Penutupan

- [ ] Cek harian: Billing page + `aws freetier get-account-plan-state ...`
- [ ] Cek instance: `aws ec2 describe-instances --region ap-southeast-2 --profile attendify --query 'Reservations[*].Instances[*].[InstanceId,State.Name]' --output table`
- [ ] Tidak ada: NAT Gateway, ALB idle, Elastic IP menganggur, Multi-AZ, snapshot berlebih
- [ ] **Matikan/hapus resource setelah demo** — jangan biarkan EC2 jalan 24/7
- [ ] Jika tiba-tiba `Access Denied` pada operasi yang dulu jalan → cek kredit/spend limit
      dulu (project bisa di-pause), sesuai aturan Agent Toolkit
- [ ] Simpan bukti: screenshot arsitektur, tagihan $0/rendah, snapshot restore, urutan demo

## 8. Urutan Demo yang Disarankan

1. Login siswa (24023) → presensi masuk → pulang (tampil CTA kondisional)
2. Buat izin → login guru → approve → cek sinkronisasi presensi
3. Login admin → monitoring → laporan/rekap cetak → CRUD siswa
4. Tunjukkan backup/restore & sandbox AWS di laporan
