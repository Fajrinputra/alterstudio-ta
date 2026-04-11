<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-[#D4A017]"></i>
                    Pemesanan Saya
                </p>
                <h2 class="font-display font-bold text-4xl tracking-tighter text-[#3F2B1B]">
                    Riwayat & Status Pemesanan
                </h2>
            </div>
            <a href="{{ route('bookings.create') }}" 
               class="inline-flex w-full sm:w-auto items-center justify-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                <i class="fa-solid fa-calendar-plus"></i>
                Pesan Sekarang
            </a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        {{-- Session Messages --}}
        @if(session('success'))
            <div class="mb-6 p-5 bg-emerald-50 border border-emerald-200 rounded-3xl shadow-sm">
                <div class="flex gap-3 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-xl"></i>
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-6 p-5 bg-red-50 border border-red-200 rounded-3xl shadow-sm">
                <div class="flex gap-3 text-red-700">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 text-xl"></i>
                    <p class="font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Booking List --}}
        <div class="space-y-8">
            @forelse($bookings as $booking)
                @php
                    $project = $booking->project;
                    $projectStatus = $project?->status ?? 'DRAFT';
                    $statusMap = [
                        'DRAFT' => 'Menunggu jadwal',
                        'SCHEDULED' => 'Terjadwal',
                        'SHOOT_DONE' => 'Sesi Foto Selesai',
                        'EDITING' => 'Permintaan edit dikirimkan',
                        'FINAL' => 'Foto hasil edit diunggah',
                    ];
                    $statusText = $statusMap[$projectStatus] ?? $projectStatus;
                    
                    $rawAssets = $project?->mediaAssets->where('type','RAW')->sortBy('version') ?? collect();
                    $finalAssets = $project?->mediaAssets->where('type','FINAL')->sortBy('version') ?? collect();
                    $selections = $project?->selections ?? collect();
                    $selectedIds = $selections->pluck('media_asset_id')->all();
                    $remaining = 5 - $selections->count();
                    $locked = $project?->selections_locked;
                    
                    $bookingStatus = [
                        'WAITING_PAYMENT' => 'Menunggu Pembayaran',
                        'DP_PAID' => 'Pembayaran DP',
                        'PAID' => 'Pembayaran LUNAS',
                        'CANCELLED' => 'Dibatalkan',
                    ][$booking->status] ?? $booking->status;
                    
                    $statusColors = [
                        'WAITING_PAYMENT' => 'bg-amber-100 text-amber-700 border-amber-200',
                        'DP_PAID' => 'bg-blue-100 text-blue-700 border-blue-200',
                        'PAID' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'CANCELLED' => 'bg-red-100 text-red-700 border-red-200',
                    ];
                    $statusColor = $statusColors[$booking->status] ?? 'bg-[#FAF6F0] text-[#5C432C] border-[#EDE0D0]';
                @endphp

                <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden">
                    
                    {{-- Header Card --}}
                    <div class="px-8 py-6 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                        <div class="flex flex-wrap items-start justify-between gap-6">
                            <div class="flex items-start gap-5">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-camera text-[#D4A017] text-3xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-display text-2xl font-semibold text-[#3F2B1B]">{{ $booking->package->name ?? '-' }}</h3>
                                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-[#7A5B3A] mt-2">
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
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @foreach($booking->selected_addons as $addon)
                                                <span class="px-4 py-1.5 rounded-2xl bg-white border border-[#EDE0D0] text-xs text-[#5C432C]">
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

                            <div class="text-right">
                                <span class="inline-block px-5 py-2 rounded-3xl text-sm font-semibold border {{ $statusColor }}">
                                    {{ $bookingStatus }}
                                </span>
                                @if($project)
                                    <p class="text-xs text-[#8B7359] mt-3">{{ $statusText }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Content Area --}}
                    <div class="p-8 space-y-8">
                        
                        {{-- Payment Pending Alert --}}
                        @if($booking->status === 'WAITING_PAYMENT')
                            <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6">
                                <div class="flex flex-col sm:flex-row sm:items-center gap-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center">
                                            <i class="fa-solid fa-clock text-amber-600 text-2xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-amber-800">Menunggu Pembayaran</p>
                                            <p class="text-sm text-amber-600">Segera selesaikan pembayaran agar sesi foto dapat dijadwalkan.</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('bookings.pay', $booking) }}" 
                                       class="sm:ml-auto px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold hover:brightness-110 transition-all">
                                        Bayar Sekarang
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- RAW Assets Selection --}}
                        @if($rawAssets->isNotEmpty())
                            <div class="border border-[#EDE0D0] rounded-3xl bg-[#FAF6F0] p-7">
                                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-2xl bg-[#D4A017]/10 flex items-center justify-center">
                                            <i class="fa-solid fa-image text-[#D4A017] text-2xl"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-[#3F2B1B]">Foto RAW dari Fotografer</h4>
                                            <p class="text-sm text-[#7A5B3A]">Pilih hingga 5 foto terbaik untuk diedit</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('projects.raw.download', $project) }}" 
                                       class="inline-flex items-center gap-2 px-6 py-3 rounded-3xl border border-[#EDE0D0] hover:border-[#D4A017] hover:bg-white transition-all">
                                        <i class="fa-solid fa-download"></i>
                                        Unduh Semua RAW
                                    </a>
                                </div>

                                <div class="flex flex-wrap gap-3 mb-8">
                                    @foreach($rawAssets as $asset)
                                        @php $code = 'D'.$asset->version; @endphp
                                        <form method="POST" action="/projects/{{ $project->id }}/selections" class="inline">
                                            @csrf
                                            <input type="hidden" name="media_asset_id" value="{{ $asset->id }}">
                                            <button type="submit"
                                                class="px-6 py-3 rounded-2xl border text-sm font-medium transition-all
                                                    {{ in_array($asset->id, $selectedIds)
                                                        ? 'bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white border-transparent shadow-md'
                                                        : 'bg-white border-[#EDE0D0] text-[#3F2B1B] hover:border-[#D4A017] hover:bg-white' }}
                                                    {{ ($locked || ($remaining <= 0 && !in_array($asset->id, $selectedIds))) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                @if($locked || ($remaining <= 0 && !in_array($asset->id, $selectedIds))) disabled @endif>
                                                {{ $code }}
                                                @if(in_array($asset->id, $selectedIds))
                                                    <i class="fa-solid fa-check ml-2"></i>
                                                @endif
                                            </button>
                                        </form>
                                    @endforeach
                                </div>

                                <div class="flex items-center justify-between pt-6 border-t border-[#EDE0D0]">
                                    <p class="text-sm text-[#7A5B3A]">
                                        <i class="fa-solid fa-lock mr-1"></i>
                                        Pilihan akan terkunci setelah dikirim ke editor
                                    </p>
                                    <form method="POST" action="{{ route('projects.selections.finalize', $project) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-8 py-4 rounded-3xl text-sm font-semibold transition-all
                                                {{ $locked || $selections->count() === 0 
                                                    ? 'bg-gray-200 text-gray-500 cursor-not-allowed' 
                                                    : 'bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white shadow-lg hover:shadow-xl hover:-translate-y-0.5' }}"
                                            @if($locked || $selections->count() === 0) disabled @endif>
                                            <i class="fa-solid fa-paper-plane mr-2"></i>
                                            {{ $locked ? 'Sudah Dikirim ke Editor' : 'Kirim ke Editor' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        {{-- Final Assets --}}
                        @if($finalAssets->isNotEmpty())
                            <div class="border border-emerald-200 bg-emerald-50 rounded-3xl p-7">
                                <div class="flex items-center gap-4 mb-6">
                                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 flex items-center justify-center">
                                        <i class="fa-solid fa-circle-check text-emerald-700 text-2xl"></i>
                                    </div>
                                    <h4 class="font-semibold text-emerald-800 text-lg">Hasil Edit Siap Diunduh</h4>
                                </div>
                                <div class="flex flex-wrap gap-4">
                                    @foreach($finalAssets as $asset)
                                        <a href="{{ Storage::url($asset->path) }}" download
                                           class="inline-flex items-center gap-3 px-6 py-4 rounded-3xl bg-white border border-emerald-300 text-emerald-700 hover:border-emerald-500 hover:bg-emerald-50 transition-all">
                                            <i class="fa-solid fa-download"></i>
                                            Unduh File {{ $loop->iteration }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-20 bg-white/80 backdrop-blur-sm border border-[#EDE0D0] rounded-3xl">
                    <i class="fa-solid fa-calendar-xmark text-6xl text-[#8B7359] mb-6 opacity-40"></i>
                    <p class="text-[#3F2B1B] text-xl font-medium mb-2">Belum ada pemesanan</p>
                    <p class="text-[#7A5B3A] mb-8">Mulai abadikan momen berharga Anda sekarang</p>
                    <a href="{{ route('catalog.public') }}" 
                       class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold">
                        <i class="fa-solid fa-camera"></i>
                        Lihat Katalog Paket
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($bookings->hasPages())
            <div class="mt-10 flex justify-center">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</x-app-layout>