@component('mail::message')
# Booking Berhasil Dibuat

Halo {{ $isClientRecipient ? ($booking->client->name ?? 'Klien') : 'Tim Alter Studio' }},

@if($isClientRecipient)
Terima kasih telah melakukan pemesanan di Alter Studio. Berikut ringkasan booking Anda yang sudah tercatat di sistem.
@else
Terdapat booking baru yang masuk ke sistem Alter Studio. Berikut detail pemesanannya.
@endif

@component('mail::panel')
**ID Booking:** #{{ $booking->id }}  
**Paket:** {{ $packageName }}  
**Tanggal:** {{ $date ?? '-' }}  
**Cabang:** {{ $booking->studioLocation->name ?? '-' }}  
**Lokasi:** {{ $booking->location ?? '-' }}  
**Status:** {{
    [
        'WAITING_PAYMENT' => 'Menunggu Pembayaran',
        'DP_PAID' => 'DP Dibayar',
        'PAID' => 'Lunas',
        'CANCELLED' => 'Dibatalkan',
    ][$status] ?? $status
}}  
**Total:** Rp {{ $total }}
@endcomponent

@if($isClientRecipient)
Silakan lanjutkan pembayaran agar tim kami dapat memproses pemesanan Anda sesuai jadwal.

@component('mail::button', ['url' => route('bookings.pay', $booking)])
Lanjutkan Pembayaran
@endcomponent

Jika status sudah DP atau lunas, abaikan tombol di atas.
@else
Silakan buka dashboard untuk meninjau pembayaran dan menindaklanjuti pemesanan ini.
@endif

Terima kasih,<br>
Alter Studio
@endcomponent
