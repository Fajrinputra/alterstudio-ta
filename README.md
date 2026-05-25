# AlterStudio Workflow Management System

**Sistem Informasi Manajemen Alur Kerja Terintegrasi untuk Alter Studio**
Dibangun sebagai Tugas Akhir menggunakan Laravel 12 & MySQL.

---

## 🧩 Gambaran Umum Sistem

AlterStudio adalah sistem informasi berbasis web yang dirancang untuk **mengelola seluruh alur kerja bisnis studio foto** secara terintegrasi — mulai dari pemesanan oleh klien, pembayaran via Midtrans, penjadwalan kru fotografer/editor, hingga pengiriman hasil foto final kepada klien.

Sistem ini menyelesaikan masalah pengelolaan yang sebelumnya dilakukan secara manual (via Notion/catatan) menjadi sebuah platform digital terpusat yang dapat diakses oleh semua pihak sesuai peran masing-masing.

---

## 👥 Peran Pengguna (Multi-Role)

| Peran | Kemampuan Utama |
|---|---|
| **Owner** | Kelola semua pengguna, cabang, laporan lengkap |
| **Admin** | Konfirmasi booking, kelola jadwal kru, landing page |
| **Manager** | Lihat laporan, pantau booking & project, ekspor CSV/PDF |
| **Client** | Buat pemesanan, bayar via Midtrans, pilih foto, download hasil |
| **Photographer** | Lihat jadwal, upload link Google Drive foto mentah |
| **Editor** | Lihat jadwal, upload link Google Drive hasil edit final |

> Satu pengguna dapat memiliki **dua peran kru sekaligus** (contoh: Fotografer + Editor).

---

## ⚙️ Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend | PHP 8.2 + Laravel 12 |
| Database | MySQL 8.x |
| Autentikasi | Laravel Breeze |
| Frontend | Blade + Tailwind CSS + Vite |
| Payment Gateway | Midtrans Snap API |
| Storage | Google Drive (via link URL) |
| Testing | PHPUnit 11 + Xdebug |
| Version Control | Git + GitHub |

---

## 🗂️ Alur Kerja Utama Sistem

```
Klien membuat booking
        ↓
Admin konfirmasi booking
        ↓
Klien melakukan pembayaran DP/FULL via Midtrans
        ↓
Webhook Midtrans → status diperbarui otomatis
        ↓
Admin membuat jadwal (assign Fotografer + Editor + waktu)
        ↓
Notifikasi otomatis ke kru yang ditugaskan
        ↓
Fotografer upload link Google Drive (foto mentah)
        ↓
Klien memilih foto yang ingin diedit (+ catatan permintaan edit)
        ↓
Editor upload link Google Drive (hasil edit final)
        ↓
Klien download hasil final
        ↓
Manager/Owner lihat laporan kinerja & pendapatan
```

---

## 📁 Struktur Direktori Penting

```
alterstudio-ta/
├── app/
│   ├── Console/Commands/          # Scheduled commands (cleanup, cancel expired)
│   ├── Enums/Role.php             # Enum semua peran pengguna
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/             # Controller khusus admin/owner
│   │   │   ├── Auth/              # Controller autentikasi
│   │   │   ├── BookingController  # Alur pemesanan
│   │   │   ├── PaymentController  # Integrasi Midtrans
│   │   │   ├── ScheduleController # Penjadwalan kru
│   │   │   ├── MediaAssetController    # Upload/download Google Drive
│   │   │   ├── PhotoSelectionController # Pilih foto oleh klien
│   │   │   ├── ReportController   # Laporan kinerja kru
│   │   │   └── DashboardController     # Dashboard per role
│   │   └── Middleware/RoleMiddleware.php # Guard akses per peran
│   ├── Models/                    # Eloquent models
│   ├── Notifications/             # Email notifikasi otomatis
│   └── Support/BookingAvailability.php  # Cek ketersediaan jadwal
├── database/
│   ├── migrations/                # Semua migrasi tabel
│   ├── factories/                 # Data dummy untuk testing
│   └── seeders/                   # Data awal
├── routes/
│   ├── web.php                    # Semua route web (auth + role middleware)
│   └── auth.php                   # Route autentikasi Breeze
├── tests/Feature/                 # Feature tests (118 skenario)
├── resources/views/               # Blade templates
└── .env                           # Konfigurasi environment
```

---

## 🗄️ Struktur Database (11 Tabel Utama)

| Tabel | Fungsi |
|---|---|
| `users` | Data semua pengguna & peran |
| `password_reset_tokens` | Token reset password |
| `service_categories` | Kategori layanan foto |
| `service_packages` | Paket layanan beserta harga & fitur |
| `studio_locations` | Data cabang studio |
| `studio_rooms` | Ruangan per cabang |
| `bookings` | Data pemesanan klien |
| `payments` | Transaksi pembayaran (DP/FULL) |
| `projects` | Project pascaproduksi per booking |
| `media_assets` | Aset media (foto mentah/final) |
| `photo_selections` | Pilihan foto oleh klien |
| `landing_hero_slides` | Slide hero halaman utama |

---

## 🔔 Notifikasi Otomatis (Email)

| Event | Dikirim ke |
|---|---|
| Booking baru dibuat | Klien + Admin/Owner |
| Booking dikonfirmasi | Klien |
| Pembayaran berhasil | Klien |
| Jadwal kru ditetapkan | Fotografer + Editor |
| Link foto mentah diupload | Klien |
| Permintaan edit dikirim | Editor |
| Hasil final siap | Klien |
| Akun klien tidak aktif dihapus | Klien |

---

## 📊 Laporan Kinerja (Manager/Owner)

- Filter berdasarkan rentang tanggal & cabang
- Metrik: total booking, pendapatan DP & FULL, jumlah project selesai
- Daftar performa kru (Fotografer & Editor) per periode
- Ekspor ke **CSV** (dengan letterhead metadata)
- Ekspor ke **PDF** (tampilan cetak)

---

## ⚙️ Perintah Instalasi Lengkap

### Prasyarat
Pastikan sudah terinstall:
- **PHP 8.2+** dengan ekstensi: `pdo_mysql`, `mbstring`, `gd`, `xdebug`
- **Composer 2.x**
- **Node.js 22 LTS** + npm
- **MySQL 8.x** (atau MariaDB 10.x)
- **Git**

---

### Langkah 1 — Clone Repository

```bash
git clone https://github.com/Fajrinputra/alterstudio-ta.git
cd alterstudio-ta
```

---

### Langkah 2 — Install Dependensi PHP

```bash
composer install
```

---

### Langkah 3 — Konfigurasi Environment

```bash
# Salin file contoh konfigurasi
copy .env.example .env

# Generate Application Key
php artisan key:generate
```

Lalu edit file `.env` dan sesuaikan konfigurasi berikut:

```env
APP_NAME="AlterStudio"
APP_URL=http://localhost:8000

# Koneksi Database MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alterstudio_db
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi Email (untuk notifikasi)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="AlterStudio"

# Konfigurasi Midtrans (Payment Gateway)
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false
```

---

### Langkah 4 — Buat Database MySQL

Buka MySQL dan jalankan:

```sql
CREATE DATABASE alterstudio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### Langkah 5 — Jalankan Migration & Seeder

```bash
# Jalankan semua migration (buat semua tabel)
php artisan migrate

# (Opsional) Isi data awal/dummy
php artisan db:seed
```

---

### Langkah 6 — Install Dependensi Frontend & Build Asset

```bash
npm install
npm run build
```

---

### Langkah 7 — Jalankan Aplikasi

```bash
# Jalankan server Laravel
php artisan serve
```

Akses di browser: **http://localhost:8000**

---

### (Opsional) Jalankan Semua Sekaligus (Mode Development)

```bash
composer run dev
```

Perintah ini akan menjalankan sekaligus:
- `php artisan serve` (server)
- `php artisan queue:listen` (antrian notifikasi)
- `php artisan pail` (log real-time)
- `npm run dev` (Vite hot-reload)

---

## 🧪 Perintah Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test dengan laporan coverage (butuh Xdebug)
php artisan test --coverage

# Jalankan test file tertentu saja
php artisan test --filter BookingFlowTest
```

---

## 🔧 Perintah Artisan Berguna Lainnya

```bash
# Reset & jalankan ulang semua migration (HAPUS semua data!)
php artisan migrate:fresh

# Reset + jalankan ulang + isi seeder
php artisan migrate:fresh --seed

# Cek status migration
php artisan migrate:status

# Clear semua cache
php artisan optimize:clear

# Jalankan scheduled commands secara manual
php artisan app:cancel-expired-bookings
php artisan app:cleanup-expired-media
php artisan app:cleanup-inactive-clients
```

---

## 🔐 Konfigurasi PowerShell (Windows)

Jika muncul error `running scripts is disabled` saat menjalankan `npm`:

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

---

## 📝 Catatan Penting

- File `.env` **tidak boleh** di-commit ke Git (sudah ada di `.gitignore`)
- Kolom `selected_addons`, `features`, `addons`, `gallery`, `photo_gallery`, `facilities`, `roles` disimpan dalam format **JSON** di kolom `longtext`
- Webhook Midtrans harus dapat diakses dari internet publik — gunakan **ngrok** saat development
- Scheduled command untuk cancel booking expired perlu dijadwalkan via **Task Scheduler** (Windows) atau **cron** (Linux):
  ```bash
  # crontab -e (Linux/Mac)
  * * * * * php /path/to/artisan schedule:run
  ```
