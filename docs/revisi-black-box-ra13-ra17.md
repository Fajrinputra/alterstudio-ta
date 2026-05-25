# Revisi Bukti Black Box RA13-RA17

Dokumen ini merangkum penyesuaian bukti pengujian black box berdasarkan catatan dosen.

## RA13 - Pembatasan Akses Halaman Pemesanan Admin

- Bagian laporan: 5.2.2.1 Pengujian Pemesanan Layanan, Tabel 5.8 dan Gambar 5.23.
- Screenshot yang perlu digunakan: akun klien membuka URL `/admin/bookings`.
- Bukti yang harus terlihat: halaman `403 Forbidden` atau pesan akses ditolak.
- Penekanan pada narasi: klien hanya dapat mengakses `/bookings`, sedangkan halaman `/admin/bookings` dibatasi oleh middleware role.

## RA14 - Validasi Jadwal Bentrok

- Bagian laporan: 5.2.2.2 Pengujian Penjadwalan Kru, Tabel 5.11 dan Gambar 5.27.
- Screenshot yang perlu digunakan: admin menyimpan jadwal dengan fotografer/editor atau ruangan yang sudah dipakai pada waktu yang sama.
- Bukti yang harus terlihat:
  - `Jadwal bentrok: fotografer atau editor yang dipilih sudah memiliki jadwal pada waktu tersebut.`
  - atau `Jadwal bentrok: ruangan yang dipilih sudah memiliki jadwal pada waktu tersebut.`
- Catatan sistem: pesan validasi pada `ScheduleController` sudah dibuat eksplisit agar bukti screenshot tidak ambigu.

## RA15 - Caption Gambar 5.28

- Bagian laporan: Gambar 5.28 Hasil Pengujian Pembatasan Akses Menu Penjadwalan.
- Perbaikan layout di Word:
  - pastikan screenshot dan caption berada dalam satu halaman;
  - aktifkan `Keep with next` pada paragraf gambar jika caption berada tepat di bawah gambar;
  - aktifkan `Keep lines together` pada caption;
  - hindari page break di antara gambar dan caption.

## RA16 - Caption Gambar 5.29

- Bagian laporan: Gambar 5.29 Hasil Pengujian Menolak Penjadwalan Project yang Belum Memenuhi Syarat.
- Perbaikan layout di Word:
  - pastikan screenshot dan caption berada dalam satu halaman;
  - aktifkan `Keep with next` pada paragraf gambar jika caption berada tepat di bawah gambar;
  - aktifkan `Keep lines together` pada caption;
  - hindari page break di antara gambar dan caption.

## RA17 - Validasi Lebih dari 10 Kode Foto

- Bagian laporan: 5.2.2.3 Pengujian Kolaborasi Pasca-Produksi, Tabel 5.15 dan Gambar 5.31.
- Screenshot yang perlu digunakan: klien mengisi lebih dari 10 kode foto lalu menekan tombol kirim permintaan edit.
- Bukti yang harus terlihat: `Maksimal 10 foto dapat diajukan untuk diedit.`
- Penekanan pada narasi: sistem menolak permintaan edit dan data tidak tersimpan karena jumlah kode foto melebihi ketentuan.
