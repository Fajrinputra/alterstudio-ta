# Alter Studio Workflow Management System

Sistem informasi manajemen alur kerja studio fotografi untuk Alter Studio. Aplikasi ini dibangun dengan Laravel 12, Blade, Tailwind CSS, MySQL/MariaDB, Midtrans Sandbox, dan Mailtrap Sandbox.

## Gambaran Sistem

Alter Studio membantu mengelola proses layanan fotografi dari awal sampai akhir:

1. Klien melihat katalog dan membuat pemesanan.
2. Admin atau Manajer mengonfirmasi/menolak pemesanan.
3. Klien melakukan pembayaran DP 10% atau lunas melalui Midtrans.
4. Admin menjadwalkan fotografer, editor, dan ruangan studio.
5. Fotografer menyimpan link Google Drive foto mentah.
6. Klien mengirim kode foto dan deskripsi permintaan edit.
7. Editor menyimpan link/pesan hasil final.
8. Manajer dan Owner melihat laporan operasional.

## Role dan Hak Akses

| Role | Fungsi Utama |
|---|---|
| Owner | Mengelola akun pengguna, cabang studio, ruangan studio, melihat dashboard, melihat laporan owner, dan mengelola profil. |
| Admin | Mengelola kategori/paket layanan, melihat pemesanan, mengonfirmasi/menolak pemesanan, menandai pelunasan di lokasi, menjadwalkan kru, memantau project, dan mengelola profil. |
| Manajer | Melihat dashboard operasional, mengelola laporan, mengekspor laporan, mengelola hero landing page, mengelola status pemesanan, dan mengelola profil. |
| Klien | Registrasi, login, membuat pemesanan, membayar melalui Midtrans, melihat status pemesanan, mengakses link Drive, mengirim permintaan edit, dan mengelola profil. |
| Fotografer | Melihat jadwal/project yang ditugaskan, menyimpan link Google Drive foto mentah, melihat status pekerjaan, dan mengelola profil. |
| Editor | Melihat jadwal/project yang ditugaskan, melihat permintaan edit klien, menyimpan link/pesan hasil final, melihat status pekerjaan, dan mengelola profil. |

## Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | PHP 8.2, Laravel 12 |
| Frontend | Blade, Tailwind CSS, Vite |
| Database | MySQL/MariaDB |
| Autentikasi | Laravel Breeze |
| Email | Mailtrap Sandbox / SMTP |
| Payment Gateway | Midtrans Snap API Sandbox |
| Testing | PHPUnit, Feature Test, Unit Test, Integration Test |
| Version Control | Git dan GitHub |

## Struktur Folder Penting

```text
app/
  Console/Commands/              Scheduled command pembersihan data
  Enums/Role.php                 Daftar role sistem
  Http/Controllers/              Controller utama sistem
  Http/Middleware/RoleMiddleware.php
  Models/                        Model Eloquent
  Notifications/                 Notifikasi email
  Support/BookingAvailability.php

database/
  migrations/                    Struktur tabel
  factories/                     Data factory untuk pengujian
  seeders/                       Data awal

resources/views/                 Halaman Blade
routes/web.php                   Route utama dan pembatasan role
routes/auth.php                  Route autentikasi
tests/                           Unit, feature, dan integration test
diagrams/sequence/               File PlantUML sequence diagram
```

## Database Utama

| Tabel | Fungsi |
|---|---|
| `users` | Data akun pengguna dan role. |
| `password_reset_tokens` | Token reset password. |
| `service_categories` | Kategori layanan fotografi. |
| `service_packages` | Paket layanan, harga, fitur, add-on, galeri. |
| `studio_locations` | Data cabang studio. |
| `studio_rooms` | Data ruangan per cabang. |
| `landing_hero_slides` | Slide hero landing page. |
| `bookings` | Data pemesanan klien. |
| `payments` | Data pembayaran DP/lunas. |
| `projects` | Data project produksi/pasca-produksi. |
| `project_schedules` | Jadwal project. |
| `project_schedule_users` | Fotografer/editor yang ditugaskan. |
| `media_assets` | Link media foto mentah/final. |
| `photo_selections` | Kode foto dan permintaan edit klien. |

## Notifikasi Email

| Event | Penerima |
|---|---|
| Pemesanan baru dibuat | Klien, Admin, Manajer |
| Pemesanan dikonfirmasi | Klien |
| Pembayaran berhasil | Klien |
| Jadwal kru dibuat/diubah | Fotografer, Editor |
| Link foto mentah tersedia | Klien |
| Permintaan edit dikirim | Editor |
| Hasil final tersedia | Klien |
| Akun klien tidak aktif akan/dihapus | Klien |

## Prasyarat Lokal

Pastikan perangkat sudah memiliki:

- PHP 8.2 atau lebih baru
- Composer 2.x
- Node.js 22/24 dan npm
- MySQL/MariaDB, misalnya dari XAMPP
- Git
- Ekstensi PHP umum: `pdo_mysql`, `mbstring`, `openssl`, `zip`, `fileinfo`, `gd`, `curl`
- Xdebug atau PCOV hanya diperlukan jika ingin menjalankan test coverage

## Instalasi Lokal

```powershell
git clone https://github.com/Fajrinputra/alterstudio-ta.git
cd alterstudio-ta
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Buat database:

```sql
CREATE DATABASE alterstudio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sesuaikan `.env`:

```env
APP_NAME="Alter Studio"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=alterstudio_db
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=failover
MAIL_SCHEME=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=isi_dari_mailtrap
MAIL_PASSWORD=isi_dari_mailtrap
MAIL_FROM_ADDRESS="noreply@alterstudio.test"
MAIL_FROM_NAME="${APP_NAME}"

MIDTRANS_SERVER_KEY=isi_dari_midtrans
MIDTRANS_CLIENT_KEY=isi_dari_midtrans
MIDTRANS_MERCHANT_ID=isi_dari_midtrans
MIDTRANS_SANDBOX=true
```

Jalankan migrasi dan seeder:

```powershell
php artisan migrate --seed
```

Build aset frontend:

```powershell
npm run build
```

Jalankan aplikasi:

```powershell
php artisan serve
```

Akses aplikasi di:

```text
http://127.0.0.1:8000
```

Untuk development dengan Vite hot reload:

```powershell
npm run dev
```

## Testing

Jalankan seluruh pengujian:

```powershell
php artisan test
```

Jalankan integration test alur pemesanan sampai final:

```powershell
php artisan test tests/Feature/Integration/BookingServiceLifecycleIntegrationTest.php
```

Jalankan test dengan coverage:

```powershell
php -d xdebug.mode=coverage artisan test --coverage
```

Buat laporan coverage HTML:

```powershell
php -d xdebug.mode=coverage artisan test --coverage-html coverage
```

Catatan hasil pengujian terakhir:

| Komponen | Hasil |
|---|---|
| Unit, feature, dan integration test | Lulus |
| Integration lifecycle booking sampai final | Lulus |
| Mailtrap SMTP Sandbox | Berhasil diuji |
| Midtrans Snap Sandbox | Berhasil diuji |

## Midtrans Sandbox

Konfigurasi Midtrans berada di `.env`:

```env
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_MERCHANT_ID=
MIDTRANS_SANDBOX=true
```

Route webhook Midtrans:

```text
/midtrans/webhook
```

Saat hosting, isi webhook di dashboard Midtrans dengan:

```text
https://domain-kamu.com/midtrans/webhook
```

Sistem sudah memvalidasi:

- signature webhook,
- nominal pembayaran,
- status transaksi,
- status booking sebelum pembayaran,
- batas waktu pembayaran 30 menit,
- pembayaran DP dan pelunasan.

## Mailtrap Sandbox

Email menggunakan Mailtrap untuk sandbox:

```env
MAIL_MAILER=failover
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
```

Mailer `failover` akan mencoba SMTP terlebih dahulu, lalu jatuh ke log jika SMTP gagal. Untuk hosting/demo yang membutuhkan email benar-benar sampai ke inbox pengguna, gunakan layanan email production seperti Mailtrap Email Sending, Resend, Mailgun, SMTP hosting, atau Gmail SMTP dengan App Password.

## Catatan Hosting

Sebelum upload ke hosting, pastikan:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-kamu.com
```

Lalu jalankan:

```powershell
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Pastikan juga:

- folder `storage` dan `bootstrap/cache` writable,
- `.env` tidak di-commit ke GitHub,
- webhook Midtrans memakai domain publik,
- email production sudah memakai credential yang valid,
- scheduled command dijalankan melalui cron/Task Scheduler.

Contoh cron Laravel:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## Perintah Artisan Berguna

```powershell
# Clear cache aplikasi
php artisan optimize:clear

# Cek status migration
php artisan migrate:status

# Membatalkan booking yang kedaluwarsa
php artisan app:cancel-expired-bookings

# Membersihkan media yang sudah kedaluwarsa
php artisan app:cleanup-expired-media

# Membersihkan akun klien tidak aktif sesuai aturan sistem
php artisan app:cleanup-inactive-clients
```

## Catatan Keamanan

- Jangan commit file `.env`.
- Jangan menulis Midtrans key atau SMTP password langsung di kode.
- Gunakan `APP_DEBUG=false` saat production.
- Gunakan HTTPS saat hosting.
- Gunakan webhook Midtrans yang dapat diakses publik.
- Gunakan akun email production jika aplikasi dipakai user asli.

