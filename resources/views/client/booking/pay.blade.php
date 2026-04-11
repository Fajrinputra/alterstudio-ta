<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-[#D4A017]"></i>
                    Pembayaran
                </p>
                <h2 class="font-display font-bold text-4xl tracking-tighter text-[#3F2B1B]">
                    Booking #{{ $booking->id }}
                </h2>
            </div>
            <a href="{{ route('bookings.index') }}" 
               class="inline-flex items-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Riwayat
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        
        <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-2xl overflow-hidden">
            
            <!-- Header Illustration -->
            <div class="bg-gradient-to-br from-[#D4A017] to-[#E07A5F] px-8 py-10 text-white text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-white/20 backdrop-blur-md mb-6">
                    <i class="fa-solid fa-receipt text-4xl"></i>
                </div>
                <h3 class="font-display text-3xl font-semibold">Ringkasan Pembayaran</h3>
                <p class="text-white/90 mt-2">Selesaikan pembayaran untuk mengonfirmasi booking Anda</p>
            </div>

            <!-- Booking Summary -->
            <div class="p-8">
                <div class="bg-[#FAF6F0] rounded-3xl p-8 space-y-6">
                    
                    <div class="flex justify-between items-center pb-4 border-b border-[#EDE0D0]">
                        <span class="text-[#5C432C]">Paket</span>
                        <span class="font-semibold text-[#3F2B1B] text-right">{{ $booking->package->name ?? '-' }}</span>
                    </div>

                    @if(!empty($booking->selected_addons))
                        <div class="flex justify-between items-start pb-4 border-b border-[#EDE0D0]">
                            <span class="text-[#5C432C]">Add-on</span>
                            <div class="text-right space-y-1">
                                @foreach($booking->selected_addons as $addon)
                                    <p class="text-sm text-[#7A5B3A]">
                                        {{ $addon['label'] ?? '-' }}
                                        @if(!empty($addon['quantity']) && (int)$addon['quantity'] > 1)
                                            ×{{ (int)$addon['quantity'] }}
                                        @endif
                                        @if(!empty($addon['unit']))
                                            / {{ $addon['unit'] }}
                                        @endif
                                        @if(!empty($addon['subtotal']))
                                            (+Rp {{ number_format((int)$addon['subtotal']) }})
                                        @elseif(!empty($addon['price']))
                                            (+Rp {{ number_format((int)$addon['price']) }})
                                        @endif
                                    </p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-between items-center pb-4 border-b border-[#EDE0D0]">
                        <span class="text-[#5C432C]">Tanggal Booking</span>
                        <span class="font-semibold text-[#3F2B1B]">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</span>
                    </div>

                    <div class="flex justify-between items-center pb-4 border-b border-[#EDE0D0]">
                        <span class="text-[#5C432C]">Lokasi</span>
                        <span class="font-semibold text-[#3F2B1B]">{{ $booking->location }}</span>
                    </div>

                    <div class="flex justify-between items-center pb-4 border-b border-[#EDE0D0]">
                        <span class="text-[#5C432C]">Jenis Pembayaran</span>
                        <span class="px-5 py-2 rounded-3xl text-sm font-semibold bg-blue-100 text-blue-700 border border-blue-200">
                            {{ $booking->payment_type === 'DP' ? 'DP (Minimal Rp 100.000)' : 'Pembayaran Lunas' }}
                        </span>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between items-center pt-4">
                        <span class="text-lg font-semibold text-[#3F2B1B]">Total yang harus dibayar</span>
                        <span class="text-4xl font-bold text-[#D4A017]">Rp {{ number_format($booking->total_price) }}</span>
                    </div>
                </div>

                <!-- Pay Button -->
                <div class="mt-10 text-center">
                    <button id="btn-pay"
                            class="inline-flex items-center justify-center gap-4 w-full sm:w-auto px-12 py-5 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold text-lg shadow-2xl shadow-[#D4A017]/40 hover:shadow-3xl hover:-translate-y-1 transition-all duration-300">
                        <i class="fa-solid fa-credit-card text-2xl"></i>
                        <span>Bayar Sekarang</span>
                    </button>
                    
                    <div class="flex items-center justify-center gap-2 mt-6 text-xs text-[#8B7359]">
                        <i class="fa-solid fa-lock"></i>
                        <span>Pembayaran aman melalui Midtrans</span>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="mt-12 pt-8 border-t border-[#EDE0D0]">
                    <p class="text-center text-sm text-[#7A5B3A] mb-5">Metode Pembayaran yang Tersedia</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <div class="px-6 py-3 bg-white border border-[#EDE0D0] rounded-2xl text-sm flex items-center gap-2">
                            💳 Kartu Kredit / Debit
                        </div>
                        <div class="px-6 py-3 bg-white border border-[#EDE0D0] rounded-2xl text-sm flex items-center gap-2">
                            🏦 Transfer Bank
                        </div>
                        <div class="px-6 py-3 bg-white border border-[#EDE0D0] rounded-2xl text-sm flex items-center gap-2">
                            📱 E-Wallet (GoPay, OVO, DANA, dll)
                        </div>
                        <div class="px-6 py-3 bg-white border border-[#EDE0D0] rounded-2xl text-sm flex items-center gap-2">
                            🏪 Indomaret / Alfamart
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Midtrans Snap Script -->
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
                    }
                });
            } catch (e) {
                console.error('Confirm status failed', e);
            }
        }

        const btnPay = document.getElementById('btn-pay');
        
        btnPay?.addEventListener('click', async () => {
            btnPay.disabled = true;
            btnPay.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin mr-3"></i>
                Menyiapkan pembayaran...
            `;

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
                    throw new Error(err.message || 'Gagal membuat transaksi pembayaran');
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
                        alert('Pembayaran gagal. Silakan coba lagi.');
                        resetButton();
                    },
                    onClose: function() {
                        resetButton();
                    }
                });
            } catch (e) {
                alert(e.message || 'Terjadi kesalahan saat memproses pembayaran');
                resetButton();
            }
        });

        function resetButton() {
            btnPay.disabled = false;
            btnPay.innerHTML = `
                <i class="fa-solid fa-credit-card mr-3"></i>
                Bayar Sekarang
            `;
        }
    </script>
</x-app-layout>