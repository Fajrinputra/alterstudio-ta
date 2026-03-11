# Arsitektur Backend Alter Studio

Dokumen ini merangkum modul backend, relasi data utama, dan alur proses sesuai implementasi saat ini.

## 1) Modul Utama

- **Auth & RBAC**
  - Login, register, verifikasi email, reset password, profil.
  - Role: `ADMIN`, `MANAGER`, `CLIENT`, `PHOTOGRAPHER`, `EDITOR`.
  - Middleware role: `app/Http/Middleware/RoleMiddleware.php`.
- **Katalog Layanan**
  - Kategori dan paket layanan (fitur, add-on, durasi, galeri, aktif/nonaktif).
  - Controller: `Admin/CatalogController`, `Admin/ServiceCategoryController`, `Admin/ServicePackageController`.
- **Booking & Pembayaran**
  - Client membuat booking + memilih add-on.
  - Pembayaran DP/FULL via Midtrans.
  - Controller: `BookingController`, `PaymentController`.
- **Operasional Produksi**
  - Project workflow: draft -> scheduled -> shoot_done -> editing/review -> final.
  - Jadwal kru, upload media, seleksi foto, revisi pin.
  - Controller: `ScheduleController`, `MediaAssetController`, `PhotoSelectionController`, `RevisionPinController`, `ProjectController`.
- **Lokasi & Landing**
  - Kelola cabang, galeri lokasi, slide hero landing.
  - Controller: `Admin/StudioLocationController`, `Admin/LandingHeroController`, `LandingController`.
- **Laporan Manajer**
  - Filter periode + kategori, metrik pemesanan/pendapatan, performa kru, ekspor CSV.
  - Controller: `PayrollController`.

## 2) Relasi Tabel Inti

- `users (1) -> (N) bookings` via `bookings.client_id`
- `service_categories (1) -> (N) service_packages` via `service_packages.category_id`
- `service_packages (1) -> (N) bookings` via `bookings.package_id`
- `studio_locations (1) -> (N) bookings` via `bookings.studio_location_id`
- `bookings (1) -> (N) payments` via `payments.booking_id`
- `bookings (1) -> (1) projects` via `projects.booking_id`
- `projects (1) -> (1) schedules` via `schedules.project_id`
- `projects (1) -> (N) media_assets` via `media_assets.project_id`
- `projects (1) -> (N) photo_selections` via `photo_selections.project_id`
- `projects (1) -> (N) revision_pins` via `revision_pins.project_id`
- `media_assets (1) -> (N) revision_pins` via `revision_pins.media_asset_id`
- `users (1) -> (N) schedules` via `schedules.photographer_id` dan `schedules.editor_id`
- `landing_hero_slides` audit ke `users` via `created_by`, `updated_by`
- `password_reset_tokens` relasional ke `users` via `user_id` dan `email`

## 3) Alur BPMN (Ringkas)

### A. Booking & Pembayaran Klien
1. Klien login/register.
2. Klien pilih paket + isi form booking.
3. Sistem membuat `booking` + `project` status awal.
4. Klien bayar DP/FULL via Midtrans.
5. Sistem sinkron status pembayaran (webhook/confirm), update status booking, kirim notifikasi email.

### B. Penjadwalan Kru
1. Admin/manager set jadwal fotografer + editor.
2. Sistem cek bentrok jadwal kru.
3. Jika valid, jadwal disimpan dan notifikasi email dikirim ke kru.

### C. Produksi Foto & Seleksi
1. Fotografer upload RAW.
2. Sistem ubah status project + kirim notifikasi ke klien.
3. Klien pilih maksimal 5 foto lalu finalize.
4. Sistem kunci pilihan + kirim notifikasi ke editor.
5. Editor upload final, sistem kirim notifikasi final ke klien.

### D. Laporan Operasional
1. Manager pilih periode/filter kategori.
2. Sistem hitung total pemesanan, pendapatan, performa kru.
3. Laporan tampil dalam tabel/grafik.
4. Manager bisa unduh laporan CSV.

## 4) Notifikasi Email

- Menggunakan channel Laravel Notification (`mail`) dan saat ini dikonfigurasi untuk sandbox (Mailtrap) dengan failover `smtp -> log`.
- Notifikasi utama:
  - Booking dibuat
  - Jadwal ditugaskan
  - RAW diupload
  - Permintaan edit masuk
  - Final siap/diterima
  - Pembayaran terkonfirmasi

## 5) Catatan Operasional

- Mode strict saat ini menggunakan:
  - `SESSION_DRIVER=file`
  - `QUEUE_CONNECTION=sync`
  - `CACHE_STORE=file`
- Konsekuensi:
  - Tidak bergantung tabel `sessions/jobs/cache` untuk runtime lokal.
  - Pengiriman notifikasi tetap berjalan, dieksekusi sinkron.
