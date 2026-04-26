<x-app-layout>
    @php
        $requestedType = $booking->nextPaymentType();
        $payableAmount = $booking->nextPayableAmount();
        $paidAmount = $booking->paidAmount();
        $isSettlement = $booking->isAwaitingSettlement();
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-[#D4A017]"></i>
                    Pembayaran
                </p>
                <h2 class="font-display font-bold text-4xl tracking-tighter text-[#3F2B1B]">
                    Pemesanan #{{ $booking->id }}
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
            <div class="bg-gradient-to-br from-[#D4A017] to-[#E07A5F] px-8 py-10 text-white text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-white/20 backdrop-blur-md mb-6">
                    <i class="fa-solid fa-receipt text-4xl"></i>
                </div>
                <h3 class="font-display text-3xl font-semibold">Ringkasan Pembayaran</h3>
                <p class="text-white/90 mt-2">
                    {{ $isSettlement ? 'Selesaikan pelunasan untuk menuntaskan pemesanan Anda' : 'Selesaikan pembayaran untuk mengonfirmasi pemesanan Anda' }}
                </p>
            </div>

            <div class="p-8">
                <div class="bg-[#FAF6F0] rounded-3xl p-8 space-y-6">
                    <div class="flex flex-col gap-2 border-b border-[#EDE0D0] pb-4 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-[#5C432C]">Paket</span>
                        <span class="font-semibold text-[#3F2B1B] text-right">{{ $booking->package->name ?? '-' }}</span>
                    </div>

                    @if(!empty($booking->selected_addons))
                        <div class="flex flex-col gap-2 border-b border-[#EDE0D0] pb-4 sm:flex-row sm:items-start sm:justify-between">
                            <span class="text-[#5C432C]">Add-on</span>
                            <div class="text-right space-y-1">
                                @foreach($booking->selected_addons as $addon)
                                    <p class="text-sm text-[#7A5B3A]">
                                        {{ $addon['label'] ?? '-' }}
                                        @if(!empty($addon['quantity']) && (int) $addon['quantity'] > 1)
                                            x{{ (int) $addon['quantity'] }}
                                        @endif
                                        @if(!empty($addon['unit']))
                                            / {{ $addon['unit'] }}
                                        @endif
                                        @if(!empty($addon['subtotal']))
                                            (+Rp {{ number_format((int) $addon['subtotal']) }})
                                        @elseif(!empty($addon['price']))
                                            (+Rp {{ number_format((int) $addon['price']) }})
                                        @endif
                                    </p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col gap-2 border-b border-[#EDE0D0] pb-4 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-[#5C432C]">Tanggal Pemesanan</span>
                        <span class="font-semibold text-[#3F2B1B]">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</span>
                    </div>

                    <div class="flex flex-col gap-2 border-b border-[#EDE0D0] pb-4 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-[#5C432C]">Lokasi</span>
                        <span class="font-semibold text-[#3F2B1B]">{{ $booking->location }}</span>
                    </div>

                    <div class="flex flex-col gap-2 border-b border-[#EDE0D0] pb-4 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-[#5C432C]">Jenis Pembayaran</span>
                        <span class="inline-flex min-h-[34px] items-center rounded-3xl border border-blue-200 bg-blue-100 px-5 py-2 text-sm font-semibold text-blue-700">
                            {{ $isSettlement ? 'Pelunasan Sisa Pembayaran' : ($requestedType === 'DP' ? 'DP (Minimal Rp 100.000)' : 'Pembayaran Lunas') }}
                        </span>
                    </div>

                    @if($paidAmount > 0)
                        <div class="flex flex-col gap-2 border-b border-[#EDE0D0] pb-4 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-[#5C432C]">Sudah Dibayar</span>
                            <span class="font-semibold text-emerald-700">Rp {{ number_format($paidAmount) }}</span>
                        </div>
                    @endif

                    <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:items-center sm:justify-between">
                        <span class="text-lg font-semibold text-[#3F2B1B]">{{ $isSettlement ? 'Sisa yang harus dibayar' : 'Total yang harus dibayar' }}</span>
                        <span class="text-3xl font-bold text-[#D4A017] sm:text-4xl">Rp {{ number_format($payableAmount) }}</span>
                    </div>
                </div>

                <div class="mt-10 text-center">
                    @if($booking->payment_started_at)
                        <div class="mb-6 rounded-3xl border border-amber-200 bg-amber-50 px-6 py-5 text-left">
                            <div class="flex items-start gap-3 text-amber-700">
                                <i class="fa-solid fa-clock mt-1"></i>
                                <div>
                                    <p class="font-semibold">Batas waktu pembayaran sedang berjalan</p>
                                    <p class="text-sm mt-1">
                                        Waktu 30 menit dihitung sejak Anda membuka halaman pembayaran ini.
                                        Selesaikan pembayaran sebelum
                                        <span class="font-semibold">{{ optional($booking->paymentDeadlineAt())?->format('d M Y H:i') }}</span>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="mb-6 rounded-3xl border border-sky-200 bg-sky-50 px-6 py-5 text-left">
                            <div class="flex items-start gap-3 text-sky-700">
                                <i class="fa-solid fa-hourglass-start mt-1"></i>
                                <div>
                                    <p class="font-semibold">Batas waktu pembayaran akan dimulai setelah halaman ini dibuka</p>
                                    <p class="text-sm mt-1">
                                        Setelah Anda melanjutkan ke proses pembayaran, sistem memberi waktu 30 menit untuk menyelesaikan transaksi.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <button id="btn-pay"
                            class="inline-flex items-center justify-center gap-4 w-full sm:w-auto px-12 py-5 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold text-lg shadow-2xl shadow-[#D4A017]/40 hover:shadow-3xl hover:-translate-y-1 transition-all duration-300">
                        <i class="fa-solid fa-credit-card text-2xl"></i>
                        <span>{{ $isSettlement ? 'Lunasi Sekarang' : 'Bayar Sekarang' }}</span>
                    </button>

                    <div class="flex items-center justify-center gap-2 mt-6 text-xs text-[#8B7359]">
                        <i class="fa-solid fa-lock"></i>
                        <span>Pembayaran aman melalui Midtrans</span>
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-[#EDE0D0]">
                    <p class="text-center text-sm text-[#7A5B3A] mb-2">Metode Pembayaran yang Tersedia</p>
                    <p class="text-center text-xs text-[#A2876A] mb-5">Midtrans hanya akan menampilkan Virtual Account, GoPay, dan QRIS.</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <div class="px-6 py-3 bg-white border border-[#EDE0D0] rounded-2xl text-sm flex items-center gap-2">
                            <i class="fa-solid fa-building-columns text-[#D4A017]"></i>
                            Virtual Account
                        </div>
                        <div class="px-6 py-3 bg-white border border-[#EDE0D0] rounded-2xl text-sm flex items-center gap-2">
                            <i class="fa-solid fa-wallet text-[#D4A017]"></i>
                            GoPay
                        </div>
                        <div class="px-6 py-3 bg-white border border-[#EDE0D0] rounded-2xl text-sm flex items-center gap-2">
                            <i class="fa-solid fa-qrcode text-[#D4A017]"></i>
                            QRIS
                        </div>
                    </div>
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
                    body: JSON.stringify({ type: '{{ $requestedType }}' })
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'Gagal membuat transaksi pembayaran');
                }

                const data = await res.json();

                window.snap.pay(data.snap_token, {
                    onSuccess: async function() {
                        await confirmPaymentStatus();
                        window.location.href = '{{ route('bookings.index') }}?paid=1';
                    },
                    onPending: async function() {
                        await confirmPaymentStatus();
                        window.location.href = '{{ route('bookings.index') }}?pending=1';
                    },
                    onError: function() {
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
                {{ $isSettlement ? 'Lunasi Sekarang' : 'Bayar Sekarang' }}
            `;
        }
    </script>
</x-app-layout>
