<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-[1.5px] text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-[#D4A017]"></i>
                    Pemesanan Saya
                </p>
                <h2 class="font-display font-bold text-2xl tracking-tight text-[#3F2B1B] md:text-3xl">
                    Riwayat & Status Pemesanan
                </h2>
            </div>
            <a href="{{ route('bookings.create') }}" 
               class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-sm text-white font-semibold shadow-md shadow-[#D4A017]/25 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300">
                <i class="fa-solid fa-calendar-plus"></i>
                Pesan Sekarang
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-6 px-0 sm:px-2 lg:px-4">
        
        {{-- Session Messages --}}
        @if(request('paid'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl shadow-sm">
                <div class="flex gap-3 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-lg"></i>
                    <p class="font-medium">Pembayaran berhasil diproses. Status pemesanan sudah diperbarui.</p>
                </div>
            </div>
        @endif

        @if(request('pending'))
            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-2xl shadow-sm">
                <div class="flex gap-3 text-amber-700">
                    <i class="fa-solid fa-hourglass-half text-amber-500 mt-0.5 text-lg"></i>
                    <p class="font-medium">Pembayaran masih menunggu penyelesaian. Silakan cek kembali status transaksi Anda.</p>
                </div>
            </div>
        @endif

        {{-- Daftar Pemesanan --}}
        <div class="space-y-4">
            @forelse($bookings as $booking)
                @php
                    $project = $booking->project;
                    $statusText = $project?->statusLabel() ?? 'Belum Dijadwalkan';
                    
                    $paidAmount = $booking->paidAmount();
                    $remainingAmount = $booking->remainingAmount();
                    $rawDriveUrl = $project?->raw_drive_url;
                    $finalDriveUrl = $project?->final_drive_url ?: $rawDriveUrl;
                    $rawDriveExpiresAt = $project?->raw_drive_uploaded_at?->copy()->addDays(3);
                    $finalDriveExpiresAt = $project?->final_drive_uploaded_at?->copy()->addDays(3);
                    $isRawDriveExpired = $project?->isRawDriveExpired() ?? false;
                    $isFinalDriveExpired = $project?->isFinalDriveExpired() ?? false;
                    $productionBlockMessage = $project?->productionBlockMessage();
                    $canContinueProduction = $project && $productionBlockMessage === null;
                    
                    $bookingStatus = $booking->statusLabel();
                    $processLabel = 'Pengajuan Ditinjau';
                    $processColor = 'bg-amber-100 text-amber-700';

                    if ($booking->status === 'CANCELLED') {
                        $processLabel = 'Pemesanan Dibatalkan';
                        $processColor = 'bg-rose-100 text-rose-700';
                    } elseif ($booking->isSubmitted()) {
                        $processLabel = 'Pengajuan Ditinjau';
                        $processColor = 'bg-amber-100 text-amber-700';
                    } elseif ($booking->isConfirmedAwaitingPayment()) {
                        $processLabel = 'Menunggu Pembayaran';
                        $processColor = 'bg-blue-100 text-blue-700';
                    } elseif ($project?->status === 'FINAL') {
                        $processLabel = 'Hasil Final Siap';
                        $processColor = 'bg-emerald-100 text-emerald-700';
                    } elseif ($project?->status === 'EDITING') {
                        $processLabel = 'Proses Editing';
                        $processColor = 'bg-orange-100 text-orange-700';
                    } elseif ($project?->status === 'SHOOT_DONE') {
                        $processLabel = 'Foto Mentah Tersedia';
                        $processColor = 'bg-purple-100 text-purple-700';
                    } elseif ($project?->status === 'SCHEDULED') {
                        $processLabel = 'Sesi Terjadwal';
                        $processColor = 'bg-sky-100 text-sky-700';
                    } elseif ($booking->status === 'DP_PAID') {
                        $processLabel = 'DP Dibayar, Menunggu Jadwal';
                        $processColor = 'bg-indigo-100 text-indigo-700';
                    } elseif ($booking->status === 'PAID') {
                        $processLabel = 'Lunas, Menunggu Jadwal';
                        $processColor = 'bg-emerald-100 text-emerald-700';
                    }
                @endphp

                <div class="bg-white rounded-2xl border border-[#EDE0D0] shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden">
                    
                    {{-- Header Card --}}
                    <div class="px-4 py-4 sm:px-5 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-camera text-[#D4A017] text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-display text-lg font-semibold text-[#3F2B1B] sm:text-xl">{{ $booking->package->name ?? '-' }}</h3>
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs sm:text-sm text-[#7A5B3A] mt-1.5">
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-calendar"></i>
                                            {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-location-dot"></i>
                                            {{ $booking->location }}
                                        </span>
                                        <span class="flex items-center gap-1.5">
                                            <i class="fa-solid fa-money-bill"></i>
                                            Rp {{ number_format($booking->total_price) }}
                                        </span>
                                    </div>
                                    
                                    @if(!empty($booking->selected_addons))
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            @foreach($booking->selected_addons as $addon)
                                                <span class="px-3 py-1 rounded-xl bg-white border border-[#EDE0D0] text-[11px] text-[#5C432C]">
                                                    {{ $addon['label'] ?? '-' }}
                                                    @if(!empty($addon['quantity'])) x{{ (int)$addon['quantity'] }} @endif
                                                    @if(!empty($addon['subtotal']))
                                                        (+Rp {{ number_format((int)$addon['subtotal']) }})
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="w-full sm:w-auto sm:text-right">
                                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                    <x-status-badge :status="$booking->status" :confirmed-at="$booking->confirmed_at" />
                                    <span class="inline-flex items-center gap-1.5 rounded-2xl px-3 py-1.5 text-xs font-medium {{ $processColor }}">
                                        <i class="fa-solid fa-route"></i>
                                        {{ $processLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Content Area --}}
                    <div class="p-4 sm:p-5 space-y-4">
                        
                        {{-- Payment Pending Alert --}}
                        @if($booking->isSubmitted())
                            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                            <i class="fa-solid fa-hourglass-half text-amber-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-amber-800">Pemesanan Sedang Ditinjau</p>
                                            <p class="text-sm text-amber-600">Admin akan meninjau jadwal yang Anda ajukan terlebih dahulu. Pembayaran akan dibuka setelah pemesanan dikonfirmasi.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($booking->isConfirmedAwaitingPayment())
                            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                                            <i class="fa-solid fa-clock text-amber-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-amber-800">Pemesanan Sudah Dikonfirmasi</p>
                                            <p class="text-sm text-amber-600">Lanjutkan pembayaran dalam waktu 30 menit setelah pemesanan dikonfirmasi admin atau manajer agar sesi foto dapat diproses.</p>
                                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium">
                                                <span class="inline-flex items-center gap-2 rounded-xl bg-white/80 px-3 py-1.5 text-amber-700 ring-1 ring-amber-200">
                                                    <i class="fa-solid fa-calendar-day"></i>
                                                    Link Drive berlaku 3 hari
                                                </span>
                                                <span class="inline-flex items-center gap-2 rounded-xl bg-white/80 px-3 py-1.5 text-amber-700 ring-1 ring-amber-200">
                                                    <i class="fa-solid fa-images"></i>
                                                    Maksimal 10 foto untuk diedit
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('bookings.pay', $booking) }}" 
                                       class="inline-flex min-h-[48px] w-full items-center justify-center rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] px-5 py-3 text-center text-sm font-semibold leading-snug text-white transition-all hover:brightness-110 sm:ml-auto sm:w-48">
                                        <span class="block w-full text-center">Lanjutkan Pembayaran</span>
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($booking->status === 'CANCELLED')
                            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center">
                                        <i class="fa-solid fa-circle-xmark text-rose-600 text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-rose-800">Pemesanan Dibatalkan</p>
                                        <p class="text-sm text-rose-600">{{ $productionBlockMessage ?? 'Proses bisnis untuk pemesanan ini sudah dihentikan.' }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($booking->status === 'DP_PAID' && $remainingAmount > 0)
                            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                            <i class="fa-solid fa-wallet text-blue-600 text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-blue-800">DP Sudah Diterima</p>
                                            <p class="text-sm text-blue-600">Sudah dibayar Rp {{ number_format($paidAmount) }}. Sisa pelunasan Rp {{ number_format($remainingAmount) }}.</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('bookings.pay', $booking) }}"
                                       class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-[#3B82F6] to-[#2563EB] px-5 py-3 text-sm font-semibold text-white transition-all hover:brightness-110 sm:ml-auto">
                                        Lunasi Sekarang
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- Drive Link & Edit Request --}}
                        @if($rawDriveUrl && $canContinueProduction)
                            <div class="border border-[#EDE0D0] rounded-2xl bg-[#FAF6F0] p-4 sm:p-5">
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-[#D4A017]/10 flex items-center justify-center">
                                            <i class="fa-brands fa-google-drive text-[#D4A017] text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-[#3F2B1B]">Link Drive Foto Mentah</h4>
                                            <p class="text-sm text-[#7A5B3A]">Buka Drive, catat kode foto, lalu kirim deskripsi permintaan edit.</p>
                                            <div class="mt-3 flex flex-wrap gap-2 text-xs font-medium">
                                                <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-1.5 text-[#7A5B3A] ring-1 ring-[#EDE0D0]">
                                                    <i class="fa-solid fa-calendar-day text-[#D4A017]"></i>
                                                    Link Drive berlaku 3 hari
                                                    @if($rawDriveExpiresAt)
                                                        sampai {{ $rawDriveExpiresAt->translatedFormat('d M Y') }}
                                                    @endif
                                                </span>
                                                <span class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-1.5 text-[#7A5B3A] ring-1 ring-[#EDE0D0]">
                                                    <i class="fa-solid fa-images text-[#D4A017]"></i>
                                                    Maksimal 10 foto dapat diajukan untuk edit
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($isRawDriveExpired)
                                        <span class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-medium text-rose-700">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                            Link Kedaluwarsa
                                        </span>
                                    @else
                                        <a href="{{ route('projects.raw.download', $project) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl border border-[#EDE0D0] text-sm hover:border-[#D4A017] hover:bg-white transition-all">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            Buka Drive
                                        </a>
                                    @endif
                                </div>

                                @if(!$project->hasEditRequest())
                                    @if($isRawDriveExpired)
                                        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                                            Masa akses link Drive foto mentah sudah kedaluwarsa setelah 3 hari. Hubungi Alter Studio jika masih memerlukan akses.
                                        </div>
                                    @else
                                    <form method="POST" action="{{ route('projects.edit-request.store', $project) }}" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label class="block text-sm font-medium text-[#5C432C] mb-2">Kode Foto yang Dipilih</label>
                                            <textarea name="edit_photo_codes" rows="3" required
                                                      class="w-full rounded-2xl border border-[#E1D3C5] bg-white px-4 py-3 text-sm focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20"
                                                      placeholder="Contoh: D001, D014, D027. Maksimal 10 kode foto.">{{ old('edit_photo_codes') }}</textarea>
                                            <p class="mt-2 text-xs text-[#8B7359]">Pisahkan kode dengan koma, spasi, atau baris baru. Maksimal 10 foto untuk diedit.</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-[#5C432C] mb-2">Deskripsi Permintaan Edit</label>
                                            <textarea name="edit_request_note" rows="5" required
                                                      class="w-full rounded-2xl border border-[#E1D3C5] bg-white px-4 py-3 text-sm focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20"
                                                      placeholder="Tuliskan arahan edit, tone warna, retouch, atau catatan khusus...">{{ old('edit_request_note') }}</textarea>
                                        </div>
                                        <div class="flex items-center justify-between gap-4 pt-2">
                                            <p class="text-sm text-[#7A5B3A]">
                                                <i class="fa-solid fa-lock mr-1"></i>
                                                Permintaan akan terkunci setelah dikirim ke editor.
                                            </p>
                                            <button class="px-5 py-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-sm font-semibold text-white shadow-md transition-all hover:shadow-lg">
                                                <i class="fa-solid fa-paper-plane mr-2"></i>
                                                Kirim ke Editor
                                            </button>
                                        </div>
                                    </form>
                                    @endif
                                @else
                                    <div class="space-y-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                        <p class="font-semibold text-emerald-800">
                                            <i class="fa-solid fa-circle-check mr-2"></i>
                                            Permintaan edit sudah dikirim ke editor.
                                        </p>
                                        <div class="grid gap-4 md:grid-cols-2">
                                            <div class="rounded-2xl bg-white px-4 py-3">
                                                <p class="text-xs uppercase tracking-wide text-[#8B7359]">Kode Foto</p>
                                                <p class="mt-1 whitespace-pre-line text-sm text-[#3F2B1B]">{{ $project->edit_photo_codes }}</p>
                                            </div>
                                            <div class="rounded-2xl bg-white px-4 py-3">
                                                <p class="text-xs uppercase tracking-wide text-[#8B7359]">Deskripsi Edit</p>
                                                <p class="mt-1 whitespace-pre-line text-sm text-[#3F2B1B]">{{ $project->edit_request_note }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- Final Drive --}}
                        @if($project?->hasFinalDelivery() && $canContinueProduction)
                            <div class="border border-emerald-200 bg-emerald-50 rounded-2xl p-4 sm:p-5">
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center">
                                            <i class="fa-solid fa-circle-check text-emerald-700 text-lg"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-emerald-800">Hasil Edit Tersedia di Drive</h4>
                                            @if($project->final_message)
                                                <p class="text-sm text-emerald-700">{{ $project->final_message }}</p>
                                            @endif
                                            <p class="mt-2 text-xs font-medium text-emerald-700">
                                                <i class="fa-solid fa-calendar-day mr-1"></i>
                                                Link Drive hasil berlaku 3 hari
                                                @if($finalDriveExpiresAt)
                                                    sampai {{ $finalDriveExpiresAt->translatedFormat('d M Y') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @if($finalDriveUrl && !$isFinalDriveExpired)
                                        <a href="{{ route('projects.final.download', $project) }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white border border-emerald-300 text-sm text-emerald-700 hover:border-emerald-500 hover:bg-emerald-50 transition-all">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                            Buka Drive Hasil
                                        </a>
                                    @elseif($isFinalDriveExpired)
                                        <span class="inline-flex items-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-medium text-rose-700">
                                            <i class="fa-solid fa-clock-rotate-left"></i>
                                            Link Hasil Kedaluwarsa
                                        </span>
                                    @endif
                                </div>

                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white/80 backdrop-blur-sm border border-[#EDE0D0] rounded-2xl">
                    <i class="fa-solid fa-calendar-xmark text-4xl text-[#8B7359] mb-4 opacity-40"></i>
                    <p class="text-[#3F2B1B] text-lg font-medium mb-2">Belum ada pemesanan</p>
                    <p class="text-[#7A5B3A] mb-8">Mulai abadikan momen berharga Anda sekarang</p>
                    <a href="{{ route('catalog.public') }}" 
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-sm text-white font-semibold">
                        <i class="fa-solid fa-camera"></i>
                        Lihat Katalog Paket
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($bookings->hasPages())
            <div class="mt-6 flex justify-center">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
