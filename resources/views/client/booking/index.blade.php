<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#7a5b3a] flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-[#b58042]"></i>
                    Pemesanan Saya
                </p>
                <h2 class="font-display font-bold text-3xl text-[#3f2b1b]">Riwayat & Status</h2>
            </div>
            <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#b58042] text-white hover:bg-[#9b6a34] transition-all shadow-md">
                Pesan Sekarang
            </a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Session Messages --}}
        @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                <div class="flex gap-3 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif
        
        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex gap-3 text-red-700">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5"></i>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- Booking List --}}
        <div class="space-y-4">
            @forelse($bookings as $booking)
                @php
                    $project = $booking->project;
                    $projectStatus = $project?->status ?? 'DRAFT';
                    $statusMap = [
                        'DRAFT' => 'Menunggu jadwal',
                        'SCHEDULED' => 'Terjadwal',
                        'SHOOT_DONE' => 'Sesi Foto Selesai',
                        'EDITING' => 'Permintaan edit dikirimkan',
                        'REVIEW' => 'Menunggu Review',
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
                        'DRAFT' => 'Draft',
                    ][$booking->status] ?? $booking->status;
                    
                    $statusColors = [
                        'WAITING_PAYMENT' => 'bg-amber-100 text-amber-700 border-amber-200',
                        'DP_PAID' => 'bg-blue-100 text-blue-700 border-blue-200',
                        'PAID' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'CANCELLED' => 'bg-red-100 text-red-700 border-red-200',
                        'DRAFT' => 'bg-gray-100 text-gray-700 border-gray-200',
                    ];
                    $statusColor = $statusColors[$booking->status] ?? 'bg-[#fdf8f2] text-[#6c4f32] border-[#e3d5c4]';
                @endphp

                <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-lg hover:shadow-xl transition-all overflow-hidden">
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-[#e3d5c4] bg-gradient-to-r from-[#faf3eb] to-white">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#b58042]/20 to-[#8b5b2e]/20 flex items-center justify-center">
                                    <i class="fa-solid fa-camera text-[#b58042] text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-display text-xl text-[#3f2b1b]">{{ $booking->package->name ?? '-' }}</h3>
                                    <div class="flex flex-wrap items-center gap-3 text-sm text-[#6f5134] mt-1">
                                        <span><i class="fa-solid fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</span>
                                        <span><i class="fa-solid fa-location-dot mr-1"></i>{{ $booking->location }}</span>
                                        <span><i class="fa-solid fa-money-bill mr-1"></i>Rp {{ number_format($booking->total_price) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $statusColor }}">
                                    {{ $bookingStatus }}
                                </span>
                                @if($project)
                                    <p class="text-xs text-[#7a5b3a] mt-1">{{ $statusText }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-6 space-y-4">
                        {{-- Payment Pending --}}
                        @if($booking->status === 'WAITING_PAYMENT')
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                                            <i class="fa-solid fa-clock text-amber-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-amber-800">Menunggu Pembayaran</p>
                                            <p class="text-sm text-amber-600">Silakan selesaikan pembayaran untuk melanjutkan</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('bookings.pay', $booking) }}" class="px-4 py-2 rounded-lg bg-[#b58042] text-white hover:bg-[#9b6a34] transition-colors">
                                        Bayar Sekarang
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- RAW Assets Selection --}}
                        @if($rawAssets->isNotEmpty())
                            <div class="border border-[#e3d5c4] rounded-xl bg-[#fcf7f1] p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-[#b58042]/20 flex items-center justify-center">
                                            <i class="fa-solid fa-image text-[#b58042]"></i>
                                        </div>
                                        <h4 class="font-semibold text-[#3f2b1b]">Foto RAW dari Fotografer</h4>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm text-[#6f5134]">
                                            <i class="fa-solid fa-circle-info mr-1"></i>
                                            Pilih hingga 5 foto untuk di kirim ke editor (sisa: {{ max(0, $remaining) }})
                                        </span>
                                        <a href="{{ route('projects.raw.download', $project) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white border border-[#e3d5c4] hover:border-[#b58042] text-sm transition-all">
                                            <i class="fa-solid fa-download"></i>
                                            Unduh Semua RAW
                                        </a>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-2 mb-3">
                                    @foreach($rawAssets as $asset)
                                        @php $code = 'D'.$asset->version; @endphp
                                        <form method="POST" action="/projects/{{ $project->id }}/selections" class="inline">
                                            @csrf
                                            <input type="hidden" name="media_asset_id" value="{{ $asset->id }}">
                                            <button type="submit"
                                                class="px-3 py-2 rounded-lg border text-sm transition-all
                                                    {{ in_array($asset->id, $selectedIds) 
                                                        ? 'bg-[#b58042] text-white border-[#b58042] shadow-md' 
                                                        : 'bg-white border-[#e3d5c4] text-[#3f2b1b] hover:border-[#b58042] hover:bg-[#fcf7f1]' }}
                                                    {{ ($locked || ($remaining <=0 && !in_array($asset->id,$selectedIds))) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                @if($locked || ($remaining <=0 && !in_array($asset->id,$selectedIds))) disabled @endif>
                                                {{ $code }}
                                                @if(in_array($asset->id, $selectedIds))
                                                    <i class="fa-solid fa-check ml-1"></i>
                                                @endif
                                            </button>
                                        </form>
                                    @endforeach
                                </div>

                                <div class="flex items-center justify-between pt-3 border-t border-[#e3d5c4]">
                                    <p class="text-sm text-[#6f5134]">
                                        <i class="fa-solid fa-lock mr-1"></i>
                                        Setelah klik "Kirim", pilihan akan terkunci
                                    </p>
                                    <form method="POST" action="{{ route('projects.selections.finalize', $project) }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition-all
                                                {{ $locked 
                                                    ? 'bg-gray-200 text-gray-500 cursor-not-allowed' 
                                                    : 'bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white shadow-md hover:shadow-lg hover:-translate-y-0.5' }}"
                                            @if($locked || $selections->count()===0) disabled @endif>
                                            <i class="fa-solid fa-paper-plane"></i>
                                            {{ $locked ? 'Sudah Dikirim' : 'Kirim ke Editor' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        {{-- Final Assets --}}
                        @if($finalAssets->isNotEmpty())
                            <div class="border border-emerald-200 rounded-xl bg-emerald-50 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-200 flex items-center justify-center">
                                            <i class="fa-solid fa-circle-check text-emerald-700"></i>
                                        </div>
                                        <h4 class="font-semibold text-emerald-800">Hasil Edit Siap Diunduh</h4>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($finalAssets as $asset)
                                        <a href="{{ Storage::url($asset->path) }}" download
                                           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white border border-emerald-300 text-emerald-700 hover:border-emerald-500 hover:bg-emerald-50 transition-all">
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
                <div class="text-center py-12 bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl">
                    <i class="fa-solid fa-calendar-xmark text-5xl text-[#8b7359] mb-4 opacity-50"></i>
                    <p class="text-[#6f5134] mb-2">Belum ada pemesanan</p>
                    <p class="text-sm text-[#7a5b3a] mb-4">Yuk, booking paket foto favorit Anda!</p>
                    <a href="{{ route('catalog.public') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#b58042] text-white">
                        <i class="fa-solid fa-camera"></i>
                        Lihat Katalog
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $bookings->links() }}
        </div>
    </div>
</x-app-layout>