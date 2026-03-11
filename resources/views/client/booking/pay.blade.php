<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#7a5b3a] flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-[#b58042]"></i>
                    Pembayaran
                </p>
                <h2 class="font-display font-bold text-3xl text-[#3f2b1b]">Booking #{{ $booking->id }}</h2>
            </div>
            <a href="{{ route('bookings.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-xl p-8">
            {{-- Booking Summary --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] text-white mb-4">
                    <i class="fa-solid fa-receipt text-3xl"></i>
                </div>
                <h3 class="text-2xl font-display font-semibold text-[#3f2b1b] mb-2">Ringkasan Pembayaran</h3>
                <p class="text-[#7a5b3a]">Silakan selesaikan pembayaran untuk mengkonfirmasi booking Anda</p>
            </div>

            {{-- Booking Details --}}
            <div class="bg-[#fcf7f1] rounded-xl p-6 mb-6 space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-[#e3d5c4]">
                    <span class="text-[#6f5134]">Paket</span>
                    <span class="font-semibold text-[#3f2b1b]">{{ $booking->package->name ?? '-' }}</span>
                </div>

                @if(!empty($booking->selected_addons))
                    <div class="flex justify-between items-start">
                        <span class="text-[#6f5134]">Add-on</span>
                        <div class="text-right">
                            @foreach($booking->selected_addons as $addon)
                                <p class="text-xs text-[#6f5134]">{{ $addon['label'] ?? '-' }} @if(!empty($addon['price'])) (+Rp {{ number_format((int) $addon['price']) }}) @endif</p>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                <div class="flex justify-between items-center">
                    <span class="text-[#6f5134]">Tanggal Booking</span>
                    <span class="font-semibold text-[#3f2b1b]">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-[#6f5134]">Lokasi</span>
                    <span class="font-semibold text-[#3f2b1b]">{{ $booking->location }}</span>
                </div>
                
                <div class="flex justify-between items-center pb-3 border-b border-[#e3d5c4]">
                    <span class="text-[#6f5134]">Jenis Pembayaran</span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                        {{ $booking->payment_type === 'DP' ? 'DP (min Rp 100.000)' : 'Lunas' }}
                    </span>
                </div>
                
                <div class="flex justify-between items-center text-lg">
                    <span class="font-semibold text-[#3f2b1b]">Total Pembayaran</span>
                    <span class="text-2xl font-bold text-[#b58042]">Rp {{ number_format($booking->total_price) }}</span>
                </div>
            </div>

            {{-- Payment Button --}}
            <div class="text-center">
                <button id="btn-pay" 
                        class="inline-flex items-center gap-2 px-8 py-4 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold text-lg shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-credit-card"></i>
                    Bayar Sekarang
                </button>
                <p class="text-xs text-[#7a5b3a] mt-3">
                    <i class="fa-solid fa-lock mr-1"></i>
                    Pembayaran aman dengan Midtrans
                </p>
            </div>

            {{-- Payment Methods --}}
            <div class="mt-6 pt-6 border-t border-[#e3d5c4]">
                <p class="text-sm text-center text-[#6f5134] mb-3">Metode Pembayaran Tersedia:</p>
                <div class="flex flex-wrap justify-center gap-3">
                    <span class="px-3 py-1.5 bg-white border border-[#e3d5c4] rounded-lg text-sm">💳 Kartu Kredit</span>
                    <span class="px-3 py-1.5 bg-white border border-[#e3d5c4] rounded-lg text-sm">🏧 Transfer Bank</span>
                    <span class="px-3 py-1.5 bg-white border border-[#e3d5c4] rounded-lg text-sm">📱 E-Wallet</span>
                    <span class="px-3 py-1.5 bg-white border border-[#e3d5c4] rounded-lg text-sm">🏪 Indomaret</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    <script>
        async function confirmPaymentStatus() {
            try {
                await fetch('{{ route('bookings.pay.confirm', $booking) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({})
                });
            } catch (e) {
                console.error('confirm status failed', e);
            }
        }

        const btnPay = document.getElementById('btn-pay');
        btnPay?.addEventListener('click', async () => {
            btnPay.disabled = true;
            btnPay.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Menyiapkan pembayaran...';
            
            try {
                const res = await fetch('{{ route('bookings.pay.snap', $booking) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ type: '{{ $booking->payment_type }}' })
                });
                
                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal membuat pembayaran');
                }
                
                const data = await res.json();
                
                window.snap.pay(data.snap_token, {
                    onSuccess: async function(result) {
                        await confirmPaymentStatus();
                        window.location.href = '{{ route('bookings.index') }}?paid=1';
                    },
                    onPending: async function(result) {
                        await confirmPaymentStatus();
                        window.location.href = '{{ route('bookings.index') }}?pending=1';
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal, silakan coba lagi.');
                        btnPay.disabled = false;
                        btnPay.innerHTML = '<i class="fa-solid fa-credit-card mr-2"></i>Bayar Sekarang';
                    },
                    onClose: function() {
                        btnPay.disabled = false;
                        btnPay.innerHTML = '<i class="fa-solid fa-credit-card mr-2"></i>Bayar Sekarang';
                    }
                });
            } catch (e) {
                alert(e.message);
                btnPay.disabled = false;
                btnPay.innerHTML = '<i class="fa-solid fa-credit-card mr-2"></i>Bayar Sekarang';
            }
        });
    </script>
</x-app-layout>
