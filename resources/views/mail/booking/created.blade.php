@component('mail::message')
# Pemesanan Berhasil Dibuat

Halo {{ $isClientRecipient ? ($booking->client->name ?? 'Klien') : 'Tim Alter Studio' }},

@if($isClientRecipient)
Terima kasih telah melakukan pemesanan di Alter Studio. Berikut ringkasan pemesanan Anda yang sudah tercatat di sistem.
@else
Terdapat pemesanan baru yang masuk ke sistem Alter Studio. Berikut detailnya.
@endif

@component('mail::panel')
**ID Pemesanan:** #{{ $booking->id }}  
**Paket:** {{ $packageName }}  
**Tanggal:** {{ $date ?? '-' }}  
**Cabang:** {{ $booking->studioLocation->name ?? '-' }}  
**Lokasi:** {{ $booking->location ?? '-' }}  
**Status:** {{ $booking->statusLabel() }}  
**Total:** Rp {{ $total }}
@endcomponent

@if($isClientRecipient)
Pemesanan Anda sudah kami terima dan sedang menunggu konfirmasi admin atau manajer. Setelah pemesanan dikonfirmasi, Anda dapat melanjutkan pembayaran dari halaman pemesanan.

@component('mail::button', ['url' => route('bookings.index')])
Lihat Status Pemesanan
@endcomponent
@else
Silakan buka dashboard untuk meninjau pemesanan ini dan mengonfirmasi jadwal sebelum pembayaran dibuka ke klien.
@endif

Terima kasih,<br>
Alter Studio
@endcomponent
