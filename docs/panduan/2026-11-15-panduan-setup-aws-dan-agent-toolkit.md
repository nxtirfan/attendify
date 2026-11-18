# Panduan Setup AWS CLI, Agent Toolkit, dan Cost Guard

- Tanggal: 15 November 2026.
- Untuk: Irfan (DevOps/owner) dan seluruh anggota Kelompok 9.
- Konteks: AWS **new experience** (login dengan social provider, sumber daya berada di dalam
  sebuah **project**), Region project **`ap-southeast-2`**, profil AWS CLI **`attendify`**.
- Status: setup sudah dieksekusi & diverifikasi di mesin Irfan; dokumen ini menjadi panduan
  reinstall, pemakaian harian, dan pencegahan biaya tak terduga.

## 1. Hasil Setup Saat Ini (terverifikasi 15 November 2026)

| Komponen | Nilai / Lokasi |
| --- | --- |
| AWS CLI v2 | **D:\AWSCLIV2** (folder default C dihapus; PATH user menunjuk ke D) |
| Versi CLI | 2.41.0 |
| Profil | `attendify` — Region `ap-southeast-2` |
| Identitas | Account `865609249386`, role `AccountFullAccessRole` |
| Agent Toolkit | 25 skill AWS di `C:\Users\Administrator\.agents\skills\` |
| AWS MCP Server | `C:\Users\Administrator\.config\opencode\opencode.json` — entri `aws-mcp` dengan `AWS_MCP_PROXY_PROFILES=attendify` |
| Aturan new experience | `C:\Users\Administrator\.config\opencode\AGENTS.md` (blok antara marker `BEGIN/END AWS Agent Toolkit rules`) |
| Paket akun | **FREE** — sisa kredit **$100 USD**, berlaku sampai **19 Mei 2027** |
| Resource aktif | **0 instance EC2**, biaya month-to-date **$0.00** |

## 2. Cara Pakai Sehari-hari

**AWS CLI** (PowerShell / terminal mana pun — PATH sudah permanen):
```
aws sts get-caller-identity --profile attendify
aws ec2 describe-instances --region ap-southeast-2 --profile attendify --output table
aws freetier get-account-plan-state --region us-east-1 --profile attendify
```
Catatan: di sesi PowerShell lama PATH mungkin belum termuat; kalau `aws` tidak dikenali,
pakai path penuh `D:\AWSCLIV2\aws.exe` atau buka terminal baru.

**Agent Toolkit / AI tool:**
1. Tutup sesi OpenCode, buka sesi baru supaya aturan di `AGENTS.md` dan skill aktif.
2. Ketik tugas AWS seperti biasa (mis. "deploy Attendify ke AWS") — skill terkait
   (`launch-with-aws`, `aws-serverless`, dll.) akan dipakai otomatis.
3. Skill & MCP wajib dipakai untuk semua pekerjaan AWS sesuai aturan di `AGENTS.md`.

**Kredensial login:** berlaku **12 jam**, dapat diperpanjang hingga **90 hari** tanpa
login browser ulang. Cara perbarui:
```
aws login --region ap-southeast-2 --profile attendify
```
Kalau perlu akun lain: `aws login --profile <nama>`, lalu tambahkan nama profil itu ke
daftar `AWS_MCP_PROXY_PROFILES` (dipisah spasi) di tiap file MCP, restart AI tool.

## 3. Instalasi Ulang (kalau pindah PC)

1. Pasang **uv** (prasyarat MCP): Windows → `irm https://astral.sh/uv/install.ps1 | iex`
2. Pasang AWS CLI v2: `irm 'https://awscli.amazonaws.com/v2/install.ps1' | iex`
   (MSI terpasang per-user di `%LOCALAPPDATA%\Programs\Amazon\AWSCLIV2`; di mesin ini
   sudah dipindah ke `D:\AWSCLIV2` + PATH user diperbarui)
3. `aws configure set region ap-southeast-2 --profile attendify`
4. `aws login --region ap-southeast-2 --profile attendify` → selesaikan login di browser
5. `aws sts get-caller-identity --profile attendify` → harus mengembalikan Account/Arn
6. `aws configure agent-toolkit --yes --region us-east-1 --profile attendify`
   — **Service Agent Toolkit hanya tersedia di `us-east-1`**, ini bukan pengganti Region project
7. Tambahkan `env.AWS_MCP_PROXY_PROFILES = "attendify"` pada entri `aws-mcp` di file
   konfigurasi tiap AI tool (OpenCode: `~/.config/opencode/opencode.json`). **Jangan**
   mengubah `command`/`args` yang dihasilkan toolkit.
8. Salin aturan new experience ke rules file AI tool (OpenCode: `~/.config/opencode/AGENTS.md`)
   di dalam marker `<!-- BEGIN AWS Agent Toolkit rules -->` … `<!-- END AWS Agent Toolkit rules -->`
   (idempoten — ganti isi di antara marker, jangan duplikat)
9. Verifikasi katalog: `aws agent-toolkit list-available-skills --region us-east-1 --profile attendify`

## 4. Cost Guard — Kenapa Tidak Ada Menu "Spend Limit"

Berdasarkan halaman **AWS Settings → Billing** (19 November 2026):

- Akun masih **FREE**: tertulis *"No charges while on the free plan"* dan
  *"All usage is covered by your AWS Free Tier credits"*.
- Karena tidak ada tagihan, **kontrol spend limit tidak tersedia / tidak diperlukan** sampai
  akun di-upgrade ke paid. Pengaturannya (kalau ada) ada di **Projects → project → Settings**,
  dan hanya **project owner** yang bisa mengubahnya.
- Banner merah *"default payment method needs verification"* = kartu Visa •••3962 belum
  diverifikasi. **Tidak mengganggu saat FREE**, tapi wajib diverifikasi lewat **Manage**
  sebelum upgrade ke paid agar layanan tidak terputus.
- Batas nyata saat ini adalah **$100 kredit**; jika habis, project **di-pause otomatis**.
- Cost Explorer / RDS API menolak (`SubscriptionRequiredException` / `OptInRequired`) karena
  layanan belum diaktifkan di akun baru — bukan error kredensial, normal.

## 5. Kebiasaan Anti-Boncos

**Pantau harian (30 detik):**
1. Buka **AWS Settings → Billing** — lihat *Credits remaining*, *Cost by project*, forecast.
2. `aws freetier get-account-plan-state --region us-east-1 --profile attendify`
3. Setelah deploy, cek instance:
   `aws ec2 describe-instances --region ap-southeast-2 --profile attendify --query 'Reservations[*].Instances[*].[InstanceId,State.Name]' --output table`

**Aturan deploy (Batch D nanti):**
- Semua resource regional **wajib** di `ap-southeast-2` — aturan new experience melarang region lain.
- Pakai Free Tier: EC2 `t3.micro`/`t2.micro` (750 jam/bln), RDS `db.t2.micro`; **tanpa**
  Multi-AZ.
- Hindari biaya diam yang sering bikin kaget: **NAT Gateway (~$30/bln)**, ALB idle,
  Elastic IP tidak terpakai, backup/storage besar.
- Tag semua resource: `Project=Attendify`, `Env=demo`.
- **Matikan atau hapus resource segera setelah demo** — jangan biarkan instance jalan 24/7.
- Mintalah perkiraan biaya dari skill AWS sebelum mengeksekusi deploy apa pun.

**Kalau tiba-tiba "Access Denied" pada operasi yang dulu jalan:** jangan langsung debug —
cek dulu status kredit/spend limit di Billing; project bisa saja di-pause karena kredit habis
atau limit terlampaui (aturan toolkit).

## 6. Istilah New Experience (dipakai konsisten di dokumen & percakapan)

- **project**, bukan "account", untuk wadah AWS kelompok
- **team member**, bukan "IAM user" (diundang lewat email)
- **AWS Settings** untuk manajemen project/billing/team: `https://settings.aws.com/`
- **selected Region**, bukan "home Region"
- Tidak boleh: Lambda@Edge, CloudFormation StackSets, aksi lintas-region
- CloudFront (global) boleh dikelola di `us-east-1`, tapi Lambda/API Gateway harus tetap
  di `ap-southeast-2`
