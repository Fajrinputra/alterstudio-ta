@component('mail::message')
# Booking Diterima

Halo {{ $booking->client->name ?? 'Klien' }},

Terima kasih telah melakukan pemesanan.

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

@component('mail::button', ['url' => route('bookings.pay', $booking)])
Lanjutkan Pembayaran
@endcomponent

Jika status sudah DP/PAID, abaikan tombol di atas.

Terima kasih,<br>
Alter Studio
@endcomponent
