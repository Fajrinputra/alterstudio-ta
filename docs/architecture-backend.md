# Arsitektur Backend Alter Studio

Dokumen ini merangkum modul backend, relasi data utama, dan alur proses sesuai implementasi saat ini.

## 1) Modul Utama

- **Auth & RBAC**
  - Login, register, verifikasi email, reset password, profil.
  - Role: `ADMIN`, `MANAGER`, `CLIENT`, `PHOTOGRAPHER`, `EDITOR`.
  - Middleware role: `app/Http/Middleware/RoleMiddleware.php`.
- **Katalog Layanan**
  - Kategori dan paket layanan, termasuk fitur, add-on, durasi, galeri, dan status aktif/nonaktif.
  - Controller: `Admin/CatalogController`, `Admin/ServiceCategoryController`, `Admin/ServicePackageController`.
- **Booking & Pembayaran**
  - Client membuat booking, memilih cabang, memilih add-on, memilih slot berdasarkan durasi paket + add-on tambah waktu + jeda ruangan 15 menit, lalu membayar DP 10% dari total pemesanan atau FULL via Midtrans.
  - Controller: `BookingController`, `PaymentController`.
- **Operasional Produksi**
  - Project workflow: `DRAFT -> SCHEDULED -> SHOOT_DONE -> EDITING -> FINAL`.
  - Fotografer membagikan link Google Drive foto mentah, client mengirim kode foto dan deskripsi edit, editor menandai hasil final tersedia di Drive.
  - Controller: `ScheduleController`, `MediaAssetController`, `PhotoSelectionController`, `ProjectController`.
- **Lokasi & Landing**
  - Kelola cabang, ruangan, libur operasional, galeri lokasi, dan slide hero landing.
  - Controller: `Admin/StudioLocationController`, `Admin/LandingHeroController`, `LandingController`.
- **Laporan Manajer**
  - Filter periode + kategori, metrik pemesanan/pendapatan, performa kru, dan ekspor CSV.
  - Controller: `ReportController`.

## 2) Relasi Tabel Inti

- `users (1) -> (N) bookings` via `bookings.client_id`
- `service_categories (1) -> (N) service_packages` via `service_packages.category_id`
- `service_packages (1) -> (N) bookings` via `bookings.package_id`
- `studio_locations (1) -> (N) studio_rooms` via `studio_rooms.studio_location_id`
- `studio_locations (1) -> (N) bookings` via `bookings.studio_location_id`
- `studio_rooms (1) -> (N) bookings` via `bookings.studio_room_id`
- `bookings (1) -> (N) payments` via `payments.booking_id`
- `bookings (1) -> (1) projects` via `projects.booking_id`
- `users (1) -> (N) projects` via `projects.photographer_id` dan `projects.editor_id`
- `users (1) -> (N) projects` via `projects.raw_drive_uploaded_by`, `projects.final_drive_uploaded_by`
- `landing_hero_slides` audit ke `users` via `created_by`, `updated_by`
- `password_reset_tokens` relasional ke `users` via `user_id` dan `email`

Catatan: tabel `media_assets` dan `photo_selections` masih ada sebagai data historis/kompatibilitas, tetapi alur pasca-produksi aktif sudah memakai field Drive dan deskripsi langsung pada `projects`.

## 3) Alur BPMN Ringkas

### A. Booking & Pembayaran Klien
1. Klien login/register.
2. Klien memilih paket, cabang, tanggal, jam, dan add-on. Pilihan jam dihitung dari durasi paket, add-on tambah waktu 10 menit per kuantitas, jeda ruangan 15 menit, dan kapasitas ruangan aktif di cabang tersebut.
3. Sistem membuat `booking` dan `project` dengan status awal `DRAFT`.
4. Admin mengonfirmasi booking jika diperlukan.
5. Klien membayar DP 10% dari total pemesanan atau FULL via Midtrans.
6. Sistem menyinkronkan status pembayaran dari webhook/confirm, memperbarui status booking, dan mengirim notifikasi email.

### B. Penjadwalan Kru
1. Admin/manager menetapkan fotografer, editor, ruangan, dan jam kerja project.
2. Sistem mengecek bentrok jadwal fotografer/editor dan ketersediaan ruangan.
3. Jika valid, data jadwal disimpan di `projects`, status menjadi `SCHEDULED`, dan kru menerima notifikasi.
4. Jadwal terkunci setelah ada aktivitas pasca-produksi seperti link Drive foto mentah, permintaan edit, final.

### C. Pasca-Produksi Berbasis Drive
1. Pasca-produksi hanya berjalan jika booking sudah `PAID`/lunas, project sudah dijadwalkan admin (`SCHEDULED`), dan booking tidak berstatus `CANCELLED`.
2. Jika booking dibatalkan, seluruh proses lanjutan seperti link Drive, permintaan edit, final dihentikan.
3. Fotografer mengunggah foto mentah ke Google Drive di luar server aplikasi.
4. Fotografer menyimpan link folder Drive di sistem.
5. Sistem mengubah status project menjadi `SHOOT_DONE` dan mengirim notifikasi ke klien.
6. Klien membuka link Drive yang berlaku 7 hari, mencatat kode foto, lalu mengirim maksimal 10 kode foto dan deskripsi permintaan edit melalui sistem.
7. Sistem mengunci permintaan edit, mengubah status menjadi `EDITING`, dan mengirim notifikasi ke editor.
8. Editor mengerjakan edit di Drive, lalu menandai hasil final tersedia dengan link/pesan final.
9. Sistem mengubah status menjadi `FINAL` dan mengirim notifikasi ke klien.

### D. Laporan Operasional
1. Manager memilih periode dan filter kategori.
2. Sistem menghitung total pemesanan, pendapatan, status booking, dan performa kru.
3. Laporan tampil dalam tabel/grafik.
4. Manager dapat mengunduh laporan CSV.

## 4) Notifikasi Email

- Menggunakan channel Laravel Notification (`mail`) dan dikonfigurasi untuk sandbox/failover sesuai `.env`.
- Notifikasi utama:
  - Booking dibuat.
  - Jadwal ditugaskan ke kru.
  - Link Drive foto mentah tersedia untuk klien.
  - Permintaan edit dari klien masuk ke editor.
  - Hasil final tersedia di Drive.
  - Pembayaran terkonfirmasi.

## 5) Catatan Operasional

- Mode lokal saat ini menggunakan:
  - `SESSION_DRIVER=file`
  - `QUEUE_CONNECTION=sync`
  - `CACHE_STORE=file`
- Konsekuensi:
  - Runtime lokal tidak bergantung pada tabel `sessions`, `jobs`, atau `cache`.
  - Pengiriman notifikasi berjalan sinkron saat request diproses.
