# Prompt Penjelasan Sistem — AlterStudio Workflow Management System

## Deskripsi Singkat (untuk penguji/dosen)

> **AlterStudio** adalah sistem informasi manajemen alur kerja terintegrasi berbasis web yang dirancang untuk mendigitalisasi seluruh proses operasional bisnis studio foto. Sistem ini menggantikan pencatatan manual (Notion, WhatsApp, catatan fisik) dengan platform terpusat yang dapat diakses secara real-time oleh seluruh pemangku kepentingan.

---

## Prompt Lengkap (untuk AI / dokumentasi teknis)

```
Sistem ini bernama AlterStudio Workflow Management System, dibangun menggunakan 
Laravel 12 (PHP 8.2) dan MySQL sebagai Tugas Akhir S1 Teknik Informatika.

DOMAIN: Manajemen alur kerja studio foto profesional.

MASALAH YANG DISELESAIKAN:
Alter Studio sebelumnya mengelola pemesanan, pembayaran, penjadwalan kru, dan 
pengiriman hasil foto secara manual menggunakan Notion dan komunikasi WhatsApp. 
Proses ini rentan terhadap kesalahan data, duplikasi penjadwalan, dan keterlambatan 
informasi antar tim.

FITUR UTAMA:
1. Sistem autentikasi multi-role (6 peran: Owner, Admin, Manager, Client, 
   Photographer, Editor) dengan Laravel Breeze
2. Alur pemesanan lengkap: pilih paket → pilih tanggal/lokasi → bayar DP/FULL 
   via Midtrans Snap API → konfirmasi admin
3. Penjadwalan kru dengan validasi tumpang tindih (overlap) jadwal fotografer 
   dan ruangan studio
4. Workflow pascaproduksi: upload link Google Drive foto mentah → pilih foto 
   oleh klien → upload hasil edit → download final
5. Notifikasi email otomatis di setiap tahap workflow
6. Laporan kinerja kru dengan filter tanggal dan ekspor CSV/PDF
7. Manajemen master data: kategori, paket, cabang studio, ruangan, hero slide

ARSITEKTUR TEKNIS:
- Backend: Laravel 12 (PHP 8.2), MVC pattern
- Database: MySQL dengan 12 tabel relasional
- Frontend: Blade Template + Tailwind CSS + Vite
- Payment: Midtrans Snap (DP dan pelunasan)
- Storage: Google Drive (link URL, bukan upload file langsung)
- Testing: PHPUnit 11 dengan Xdebug coverage, SQLite in-memory
- Scheduled Jobs: cancel booking expired, cleanup media, cleanup akun tidak aktif

PERAN PENGGUNA:
- Owner: akses penuh ke semua fitur + laporan lengkap + manajemen pengguna
- Admin: konfirmasi booking, buat jadwal kru, kelola landing page, kelola katalog
- Manager: pantau semua booking & project, lihat & ekspor laporan kinerja
- Client: buat booking, bayar, pilih foto, download hasil
- Photographer: lihat jadwal, upload link foto mentah Google Drive
- Editor: lihat jadwal, upload link hasil edit Google Drive

DATABASE UTAMA:
users, password_reset_tokens, service_categories, service_packages,
studio_locations, studio_rooms, bookings, payments, projects,
media_assets, photo_selections, landing_hero_slides

TESTING:
118 skenario test dengan 471 assertions menggunakan PHPUnit 11.
Test coverage ~70% lines (target 100% dengan Xdebug 3.5).
```

---

## Perintah Instalasi Lengkap (Copy-Paste Ready)

### Windows (PowerShell) — Step by step:

```powershell
# Step 0: Aktifkan eksekusi script PowerShell (jika belum)
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser

# Step 1: Clone project
git clone https://github.com/Fajrinputra/alterstudio-ta.git
cd alterstudio-ta

# Step 2: Install PHP dependencies
composer install

# Step 3: Konfigurasi environment
copy .env.example .env
php artisan key:generate

# Step 4: Buat database di MySQL terlebih dahulu, lalu jalankan migration
php artisan migrate

# Step 5: Install & build frontend
npm install
npm run build

# Step 6: Jalankan server
php artisan serve
```

### Satu perintah otomatis (via composer script):
```powershell
composer run setup
```

### Jalankan mode development (semua sekaligus):
```powershell
composer run dev
```

### Jalankan test:
```powershell
# Test biasa
php artisan test

# Test dengan coverage (butuh Xdebug)
php artisan test --coverage

# Test file tertentu
php artisan test --filter NamaTestClass
```

### Perintah database:
```powershell
# Cek status migration
php artisan migrate:status

# Reset semua data dan migration ulang
php artisan migrate:fresh

# Reset + isi data awal
php artisan migrate:fresh --seed
```

### Perintah maintenance:
```powershell
# Clear semua cache
php artisan optimize:clear

# Jalankan scheduled command manual
php artisan app:cancel-expired-bookings
php artisan app:cleanup-expired-media
php artisan app:cleanup-inactive-clients
```

---

## Konfigurasi .env yang Wajib Diisi

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alterstudio_db
DB_USERNAME=root
DB_PASSWORD=

# Mail (untuk notifikasi)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=app-password-gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=email@gmail.com
MAIL_FROM_NAME="AlterStudio"

# Midtrans Payment Gateway
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxx
MIDTRANS_IS_PRODUCTION=false
```

---

## Prasyarat Sistem

| Kebutuhan | Versi Minimum |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18 LTS (disarankan 22 LTS) |
| MySQL | 8.0+ |
| Xdebug (untuk coverage) | 3.x |
| Git | 2.x |

### Ekstensi PHP yang diperlukan:
`pdo`, `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
