# Ganti Jadwal Pemesanan

Dokumen ini berisi use case scenario dan class analysis untuk fitur ganti jadwal pemesanan dari sisi klien. Fitur ini digunakan sebagai pengganti tombol batalkan pesanan agar alur sistem tetap sederhana dan tidak perlu menangani refund pembayaran.

## Use Case Scenario

| Elemen | Deskripsi |
|---|---|
| Use Case Name | Ganti Jadwal Pemesanan |
| Actor | Klien |
| Entry Condition | Klien telah login, memiliki pemesanan miliknya sendiri, pemesanan masih berstatus diajukan/menunggu konfirmasi, belum dikonfirmasi admin/manajer, belum masuk proses pembayaran, dan belum dibatalkan. |
| Exit Condition | Jadwal pemesanan berhasil diperbarui dan pemesanan tetap berada pada kondisi menunggu konfirmasi admin atau manajer. |

| Actor | Sistem |
|---|---|
| 1. Klien membuka menu pemesanan atau riwayat pemesanan. | 2. Sistem menampilkan daftar pemesanan milik klien. |
| 3. Klien memilih pemesanan yang masih menunggu konfirmasi dan menekan tombol Ganti Jadwal. | 4. Sistem memvalidasi bahwa pemesanan milik klien, belum dikonfirmasi, belum dibayar, belum masuk payment window, dan belum dibatalkan. |
|  | 5. Sistem menampilkan form ganti jadwal dengan paket yang sama, daftar cabang aktif, add-on paket, dan batas tanggal maksimal satu bulan. |
| 6. Klien memilih cabang, tanggal, waktu, add-on, jenis pembayaran, dan catatan baru. | 7. Sistem menampilkan slot waktu tersedia berdasarkan cabang, tanggal, ruangan aktif, durasi paket, add-on waktu, jeda antar sesi, dan jam operasional. |
| 8. Klien menekan tombol simpan perubahan. | 9. Sistem memvalidasi ulang data perubahan. |
|  | 10. Sistem mengunci data booking, paket, dan ruangan aktif untuk mencegah dua proses memilih slot yang sama secara bersamaan. |
|  | 11. Sistem memvalidasi ulang ketersediaan slot dengan mengabaikan booking lama milik klien tersebut. |
|  | 12. Sistem menghitung ulang add-on dan total biaya pemesanan. |
|  | 13. Sistem memperbarui cabang, ruangan, tanggal, waktu, add-on, jenis pembayaran, catatan, dan total harga pemesanan. |
|  | 14. Sistem menampilkan pesan bahwa jadwal berhasil diperbarui dan pemesanan tetap menunggu konfirmasi admin atau manajer. |

## Alternative Scenario

| Kode | Kondisi Alternatif | Respon Sistem |
|---|---|---|
| A1 | Pemesanan bukan milik klien yang sedang login. | Sistem menolak akses perubahan jadwal. |
| A2 | Pemesanan sudah dikonfirmasi oleh admin/manajer. | Sistem menolak perubahan dan menampilkan pesan bahwa pemesanan hanya dapat diubah sebelum dikonfirmasi. |
| A3 | Pemesanan sudah masuk proses pembayaran atau payment window sudah dimulai. | Sistem menolak perubahan jadwal. |
| A4 | Pemesanan sudah DP, lunas, atau dibatalkan. | Sistem menolak perubahan jadwal. |
| A5 | Paket pada pemesanan sudah tidak aktif atau sudah dihapus. | Sistem menampilkan pesan bahwa jadwal tidak dapat diubah dan klien diminta menghubungi admin. |
| A6 | Tanggal yang dipilih sudah lewat atau lebih dari satu bulan. | Sistem menampilkan pesan validasi tanggal. |
| A7 | Cabang yang dipilih tidak memiliki ruangan aktif. | Sistem tidak menampilkan slot dan meminta klien memilih cabang/tanggal lain. |
| A8 | Slot waktu sudah terisi atau bentrok dengan pemesanan lain. | Sistem menampilkan pilihan waktu lain yang masih tersedia. |
| A9 | Data input tidak lengkap atau tidak valid. | Sistem menampilkan pesan kesalahan validasi dan data belum diperbarui. |
| A10 | Terjadi proses bersamaan dan slot sudah diambil klien lain saat disimpan. | Sistem menolak penyimpanan dan meminta klien memilih slot lain. |

## Class Analysis

| Class | Stereotype | Tanggung Jawab |
|---|---|---|
| Halaman Pemesanan Saya | Boundary | Menampilkan riwayat pemesanan klien dan tombol Ganti Jadwal pada pemesanan yang masih diajukan. |
| Form Ganti Jadwal | Boundary | Menampilkan form perubahan jadwal, pilihan cabang, tanggal, waktu, add-on, jenis pembayaran, dan catatan. |
| BookingController | Control | Mengatur akses form ganti jadwal, validasi kepemilikan/status pemesanan, validasi input, transaksi update booking, dan redirect hasil proses. |
| BookingAvailability | Control | Menghitung slot tersedia, memvalidasi jam operasional, durasi paket, add-on waktu, jeda antar sesi, ruangan aktif, dan bentrok jadwal. |
| Bookings Model | Entity | Menyimpan data pemesanan, status pemesanan, jadwal, cabang, ruangan, add-on, jenis pembayaran, dan total harga. |
| ServicePackages Model | Entity | Menyediakan data paket yang digunakan dalam pemesanan dan add-on yang dapat dipilih. |
| StudioLocations Model | Entity | Menyediakan daftar cabang aktif yang dapat dipilih klien. |
| StudioRooms Model | Entity | Menyediakan ruangan aktif pada cabang yang dipilih dan menjadi dasar pengecekan slot. |

## Operasi Penting

| Class | Method/Operasi | Fungsi |
|---|---|---|
| BookingController | `edit(Booking $booking)` | Membuka form ganti jadwal setelah memastikan booking masih boleh diubah. |
| BookingController | `update(Request $request, Booking $booking)` | Menyimpan perubahan jadwal, add-on, jenis pembayaran, catatan, ruangan, dan total biaya. |
| BookingController | `availability(Request $request)` | Mengirim daftar slot tersedia ke form berdasarkan cabang, tanggal, add-on, dan booking yang sedang diedit. |
| BookingController | `canClientReschedule(Booking $booking)` | Memastikan hanya klien pemilik booking yang dapat mengubah jadwal sebelum booking dikonfirmasi atau dibayar. |
| BookingController | `ignoreBookingIdForAvailability(Request $request)` | Mengabaikan booking yang sedang diedit agar slot lamanya tidak dianggap bentrok dengan dirinya sendiri. |
| BookingAvailability | `availableSlots(...)` | Menghasilkan daftar jam tersedia. |
| BookingAvailability | `availableRoomForSlot(...)` | Menentukan ruangan aktif yang tersedia untuk slot pilihan. |
| BookingAvailability | `isClosedDate(...)` | Menolak tanggal tutup atau tanggal yang tidak tersedia. |
| Bookings Model | `isSubmitted()` | Menandai bahwa booking masih berada pada tahap diajukan dan belum dikonfirmasi. |
| Bookings Model | `extraDurationMinutesFromAddons(...)` | Menghitung tambahan durasi dari add-on waktu. |

## Relasi Class

| Relasi | Jenis | Keterangan |
|---|---|---|
| Klien/User - Booking | Asosiasi | Satu klien dapat memiliki banyak pemesanan. |
| Booking - ServicePackage | Asosiasi | Satu booking menggunakan satu paket layanan. |
| Booking - StudioLocation | Asosiasi | Satu booking memilih satu cabang studio. |
| Booking - StudioRoom | Asosiasi | Satu booking ditempatkan pada satu ruangan studio yang tersedia. |
| StudioLocation - StudioRoom | Agregasi | Satu cabang memiliki banyak ruangan; ruangan berada dalam konteks cabang. |
| BookingController - BookingAvailability | Dependency | Controller memakai service availability untuk menghitung dan memvalidasi slot. |
| BookingController - Booking | Dependency | Controller membaca dan memperbarui data booking. |
| BookingAvailability - Booking/StudioRoom | Dependency | Availability memakai data booking aktif dan ruangan aktif untuk mengecek bentrok. |

## Catatan Batasan

- Paket tidak boleh diganti saat ganti jadwal. Hal ini menjaga konsistensi harga, durasi, dan alur persetujuan admin/manajer.
- Ganti jadwal hanya boleh dilakukan sebelum pemesanan dikonfirmasi.
- Setelah dikonfirmasi, masuk proses pembayaran, DP dibayar, lunas, atau dibatalkan, jadwal tidak dapat diubah oleh klien.
- Sistem tetap menghitung ulang add-on dan total biaya karena klien masih dapat mengubah add-on dan jenis pembayaran sebelum pemesanan dikonfirmasi.
