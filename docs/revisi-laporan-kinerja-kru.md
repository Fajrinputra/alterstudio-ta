# Revisi Laporan Kinerja Kru

## BPMN Laporan Kinerja Kru Sedang Berjalan

Berdasarkan BPMN laporan kinerja kru yang sedang berjalan di Alter Studio, proses penyusunan laporan masih dilakukan secara manual dengan memanfaatkan data pemesanan, data pembayaran, dan catatan penugasan kru yang tersebar pada media pencatatan internal. Tahapan proses laporan kinerja kru yang sedang berjalan adalah sebagai berikut:

1. **Manajer: Mengumpulkan Data Pemesanan**
   Manajer mengumpulkan data pemesanan dari catatan internal atau Notion yang sebelumnya telah diinput oleh admin.

2. **Manajer: Mengumpulkan Data Pembayaran**
   Manajer memeriksa data pembayaran yang sudah tercatat untuk mengetahui pemesanan yang telah melakukan pembayaran.

3. **Manajer: Mengumpulkan Data Penugasan Kru**
   Manajer melihat data fotografer dan editor yang bertugas pada masing-masing pemesanan berdasarkan catatan operasional.

4. **Manajer: Memeriksa Kelengkapan Data**
   Manajer memeriksa apakah data pemesanan, pembayaran, fotografer, dan editor sudah lengkap.

5. **Gateway: Data Lengkap?**
   Jika data belum lengkap, manajer perlu menghubungi admin atau kru terkait untuk melengkapi data. Jika data lengkap, proses dilanjutkan ke penyusunan laporan.

6. **Manajer: Menghitung Total Pemesanan**
   Manajer menghitung jumlah pemesanan dalam periode laporan secara manual.

7. **Manajer: Menghitung Total Pendapatan**
   Manajer menghitung total pendapatan berdasarkan pembayaran yang berhasil tercatat.

8. **Manajer: Menghitung Kinerja Fotografer**
   Manajer menghitung jumlah project yang ditangani oleh masing-masing fotografer.

9. **Manajer: Menghitung Kinerja Editor**
   Manajer menghitung jumlah project yang ditangani oleh masing-masing editor.

10. **Manajer: Menyusun Laporan Manual**
    Manajer menyusun laporan kinerja kru dalam bentuk rekap manual menggunakan data yang telah dihitung.

11. **Owner: Menerima Laporan**
    Owner menerima laporan akhir dari manajer untuk melihat kondisi pemasukan dan kinerja kru.

12. **Owner: Meninjau Laporan**
    Owner meninjau laporan yang telah disusun untuk kebutuhan evaluasi dan pengambilan keputusan.

13. **Selesai**
    Proses laporan selesai setelah laporan diterima dan ditinjau oleh owner.

## BPMN Laporan Kinerja Kru Diusulkan

Berdasarkan BPMN laporan kinerja kru yang diusulkan, proses laporan dibuat lebih terintegrasi melalui sistem. Data pemesanan, pembayaran, project, fotografer, dan editor diambil langsung dari database sehingga manajer tidak perlu menghitung laporan secara manual. Pada sistem yang diusulkan, manajer berperan dalam melakukan filter, pengolahan, dan ekspor laporan, sedangkan owner berperan melihat laporan final/detail berdasarkan periode yang dipilih. Tahapan proses laporan kinerja kru yang diusulkan adalah sebagai berikut:

1. **Manajer: Membuka Menu Laporan**
   Manajer membuka menu laporan pada sistem untuk melihat data laporan operasional.

2. **Manajer: Memilih Filter Laporan**
   Manajer memilih periode laporan dan kategori layanan yang ingin ditampilkan.

3. **Sistem: Mengambil Data Pemesanan**
   Sistem mengambil data pemesanan berdasarkan periode dan kategori yang dipilih.

4. **Sistem: Mengambil Data Pembayaran Berhasil**
   Sistem mengambil data pembayaran dengan status berhasil sebagai dasar perhitungan pendapatan.

5. **Sistem: Mengambil Data Project dan Penugasan Kru**
   Sistem mengambil data project yang memiliki penugasan fotografer dan editor.

6. **Sistem: Menghitung Ringkasan Laporan**
   Sistem menghitung total pemesanan, pendapatan diterima, jumlah fotografer bertugas, jumlah editor bertugas, dan jumlah klien aktif.

7. **Sistem: Menghitung Kinerja Kru**
   Sistem menghitung kinerja fotografer dan editor berdasarkan jumlah project yang ditangani pada periode laporan.

8. **Sistem: Menampilkan Laporan**
   Sistem menampilkan laporan dalam bentuk ringkasan, tabel pemesanan, dan data kinerja kru.

9. **Manajer: Mengekspor Laporan**
   Manajer dapat mengekspor laporan dalam bentuk CSV atau PDF sebagai dokumen laporan operasional.

10. **Owner: Membuka Menu Laporan**
    Owner membuka menu laporan untuk melihat laporan akhir yang telah tersedia pada sistem.

11. **Owner: Memilih Periode Laporan**
    Owner hanya memilih periode laporan yang ingin dilihat tanpa melakukan ekspor CSV atau PDF.

12. **Sistem: Menampilkan Laporan Final Owner**
    Sistem menampilkan laporan final/detail untuk owner, meliputi pendapatan diterima, ringkasan pembayaran, status pemesanan, dan kinerja kru.

13. **Owner: Meninjau Laporan Final**
    Owner meninjau laporan sebagai dasar evaluasi bisnis dan pengambilan keputusan.

14. **Selesai**
    Proses laporan selesai setelah manajer dapat mengolah laporan dan owner dapat melihat laporan final melalui sistem.

## Revisi Use Case Scenario

### UCS - Melihat Laporan Kinerja Kru

| Komponen | Keterangan |
|---|---|
| Nama Use Case | Melihat Laporan Kinerja Kru |
| Aktor | Manajer, Owner |
| Tujuan | Menampilkan laporan pemesanan, pendapatan, dan kinerja kru berdasarkan periode tertentu. |
| Prasyarat | Aktor sudah login dan memiliki hak akses sebagai manajer atau owner. |
| Kondisi Awal | Data pemesanan, pembayaran, project, fotografer, dan editor sudah tersimpan dalam database. |
| Alur Utama | 1. Aktor membuka menu laporan. 2. Sistem menampilkan halaman laporan. 3. Manajer memilih periode dan kategori layanan, sedangkan owner memilih periode saja. 4. Sistem mengambil data pemesanan, pembayaran, project, dan kru sesuai filter. 5. Sistem menghitung ringkasan laporan dan kinerja kru. 6. Sistem menampilkan laporan kepada aktor. |
| Alur Alternatif | Jika data pada periode yang dipilih tidak tersedia, sistem menampilkan laporan kosong dengan informasi bahwa belum ada data pada periode tersebut. |
| Kondisi Akhir | Laporan kinerja kru tampil sesuai filter yang dipilih. |

### UCS - Ekspor Laporan

| Komponen | Keterangan |
|---|---|
| Nama Use Case | Ekspor Laporan |
| Aktor | Manajer |
| Tujuan | Mengunduh laporan operasional dalam bentuk CSV atau PDF. |
| Prasyarat | Manajer sudah login dan membuka menu laporan. |
| Kondisi Awal | Data laporan sudah tampil berdasarkan filter periode dan kategori yang dipilih. |
| Alur Utama | 1. Manajer memilih periode dan kategori laporan. 2. Sistem menampilkan laporan. 3. Manajer menekan tombol Unduh CSV atau Unduh PDF. 4. Sistem mengambil ulang data sesuai filter. 5. Sistem membuat file laporan. 6. Sistem mengunduh file laporan kepada manajer. |
| Alur Alternatif | Jika owner mencoba mengakses URL ekspor secara langsung, sistem menolak akses ekspor dan mengarahkan kembali ke halaman laporan. |
| Kondisi Akhir | File laporan berhasil diunduh oleh manajer. |

## Revisi Sequence Diagram

### Sequence Diagram Melihat Laporan Kinerja Kru

```plantuml
@startuml SD-LAPORAN-KINERJA-KRU
title Sequence Diagram Melihat Laporan Kinerja Kru
skinparam backgroundColor #FFFFFF
skinparam sequenceArrowThickness 1
skinparam sequenceLifeLineBorderColor #8B7359
skinparam sequenceParticipantBorderColor #8B7359
skinparam sequenceParticipantBackgroundColor #FAF6F0
skinparam actorBorderColor #3F2B1B
skinparam actorBackgroundColor #FFFFFF
autonumber

actor "Manajer" as Manager
actor "Owner" as Owner
boundary "Halaman Laporan" as Page
boundary "Filter Laporan" as Filter
control "ReportController" as Controller
entity "Booking Model" as BookingModel
database "bookings" as Bookings
entity "Payment Model" as PaymentModel
database "payments" as Payments
entity "Project Model" as ProjectModel
database "projects" as Projects
entity "User Model" as UserModel
database "users" as Users

alt Manajer membuka laporan
    Manager -> Page: membuka menu laporan
else Owner membuka laporan
    Owner -> Page: membuka menu laporan
end

activate Page
Page -> Filter: tampilkan filter laporan
activate Filter

alt Manajer
    Manager -> Filter: pilih periode dan kategori
else Owner
    Owner -> Filter: pilih periode
end

Filter -> Controller: index(filter)
activate Controller

Controller -> BookingModel: ambil data pemesanan
activate BookingModel
BookingModel -> Bookings: SELECT bookings berdasarkan filter
activate Bookings
Bookings --> BookingModel: booking collection
deactivate Bookings
BookingModel --> Controller: booking collection
deactivate BookingModel

Controller -> PaymentModel: ambil pembayaran berhasil
activate PaymentModel
PaymentModel -> Payments: SELECT payments status PAID
activate Payments
Payments --> PaymentModel: payment collection
deactivate Payments
PaymentModel --> Controller: payment collection
deactivate PaymentModel

Controller -> ProjectModel: ambil data project dan penugasan kru
activate ProjectModel
ProjectModel -> Projects: SELECT projects dengan fotografer/editor
activate Projects
Projects --> ProjectModel: project collection
deactivate Projects
ProjectModel --> Controller: project collection
deactivate ProjectModel

Controller -> UserModel: ambil data kru
activate UserModel
UserModel -> Users: SELECT users role fotografer/editor
activate Users
Users --> UserModel: user collection
deactivate Users
UserModel --> Controller: user collection
deactivate UserModel

Controller -> Controller: hitung total pemesanan
Controller -> Controller: hitung pendapatan diterima
Controller -> Controller: hitung kinerja fotografer dan editor

alt Owner
    Controller -> Controller: hitung detail ringkasan pembayaran
    Controller -> Controller: hitung ringkasan status pemesanan
end

Controller --> Page: tampilkan laporan
deactivate Controller
Page --> Manager: laporan operasional tampil
Page --> Owner: laporan final/detail tampil
deactivate Filter
deactivate Page
@enduml
```

### Sequence Diagram Ekspor Laporan

```plantuml
@startuml SD-EKSPOR-LAPORAN
title Sequence Diagram Ekspor Laporan
skinparam backgroundColor #FFFFFF
skinparam sequenceArrowThickness 1
skinparam sequenceLifeLineBorderColor #8B7359
skinparam sequenceParticipantBorderColor #8B7359
skinparam sequenceParticipantBackgroundColor #FAF6F0
skinparam actorBorderColor #3F2B1B
skinparam actorBackgroundColor #FFFFFF
autonumber

actor "Manajer" as Manager
actor "Owner" as Owner
boundary "Halaman Laporan" as Page
boundary "Tombol Ekspor" as ExportButton
control "ReportController" as Controller
entity "Booking Model" as BookingModel
database "bookings" as Bookings
entity "Payment Model" as PaymentModel
database "payments" as Payments
entity "Project Model" as ProjectModel
database "projects" as Projects

alt Manajer mengekspor laporan
    Manager -> Page: membuka laporan hasil filter
    activate Page
    Manager -> ExportButton: klik Unduh CSV/PDF
    activate ExportButton
    ExportButton -> Controller: index(filter, download)
    activate Controller
    Controller -> BookingModel: ambil ulang data pemesanan
    activate BookingModel
    BookingModel -> Bookings: SELECT bookings by filter
    activate Bookings
    Bookings --> BookingModel: booking collection
    deactivate Bookings
    BookingModel --> Controller: booking collection
    deactivate BookingModel
    Controller -> PaymentModel: ambil pembayaran berhasil
    activate PaymentModel
    PaymentModel -> Payments: SELECT payments PAID
    activate Payments
    Payments --> PaymentModel: payment collection
    deactivate Payments
    PaymentModel --> Controller: payment collection
    deactivate PaymentModel
    Controller -> ProjectModel: ambil data kinerja kru
    activate ProjectModel
    ProjectModel -> Projects: SELECT projects with crew
    activate Projects
    Projects --> ProjectModel: project collection
    deactivate Projects
    ProjectModel --> Controller: project collection
    deactivate ProjectModel
    Controller -> Controller: generate file CSV/PDF
    Controller --> Page: file laporan terunduh
    deactivate Controller
    Page --> Manager: download laporan
    deactivate ExportButton
    deactivate Page
else Owner mencoba ekspor
    Owner -> Page: akses URL download
    activate Page
    Page -> Controller: index(download)
    activate Controller
    Controller -> Controller: validasi role owner tidak boleh ekspor
    Controller --> Page: redirect ke laporan periode
    deactivate Controller
    Page --> Owner: laporan tampil tanpa ekspor
    deactivate Page
end
@enduml
```

## Revisi Class Analysis Diagram

### Class Analysis Melihat Laporan Kinerja Kru

| Komponen | Isi |
|---|---|
| Aktor | Manajer, Owner |
| Boundary Class | Menu Laporan, Halaman Laporan, Filter Laporan, Tampilan Ringkasan Laporan, Tampilan Kinerja Kru, Tampilan Detail Owner |
| Control Class | ReportController |
| Entity Class | Booking, Payment, Project, User, ServiceCategory |
| Hubungan Garis | Manajer -- Menu Laporan -- Halaman Laporan -- Filter Laporan -- ReportController -- Booking / Payment / Project / User / ServiceCategory; Owner -- Menu Laporan -- Halaman Laporan -- Filter Laporan -- ReportController -- Booking / Payment / Project / User |

### Class Analysis Ekspor Laporan

| Komponen | Isi |
|---|---|
| Aktor | Manajer |
| Boundary Class | Halaman Laporan, Filter Laporan, Tombol Ekspor CSV, Tombol Ekspor PDF |
| Control Class | ReportController |
| Entity Class | Booking, Payment, Project, User, ServiceCategory |
| Hubungan Garis | Manajer -- Halaman Laporan -- Filter Laporan -- Tombol Ekspor CSV/PDF -- ReportController -- Booking / Payment / Project / User / ServiceCategory |

## Catatan Revisi Diagram

- Pada use case diagram, relasi **Ekspor Laporan** hanya dihubungkan dengan aktor **Manajer**.
- Aktor **Owner** hanya dihubungkan dengan use case **Melihat Laporan Kinerja Kru** atau **Melihat Laporan Final**.
- Pada sequence diagram, proses ekspor tidak dilakukan oleh owner.
- Pada class analysis, boundary ekspor CSV/PDF hanya muncul pada class analysis Ekspor Laporan dengan aktor manajer.
- Pada BPMN usulan, owner tidak melakukan ekspor, melainkan hanya memilih periode dan melihat laporan final/detail.
