@php
    use App\Enums\Role;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-calendar text-[#D4A017]"></i>
                    {{ now()->isoFormat('dddd, D MMMM YYYY') }}
                </p>
                <h2 class="font-display font-bold text-4xl bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#B56D3E] bg-clip-text text-transparent tracking-tighter">
                    Dashboard Alter Studio
                </h2>
                @if($hasBothCrewRoles ?? false)
                    <p class="mt-3 inline-flex items-center gap-2 rounded-3xl border border-[#E1D3C5] bg-white/70 px-4 py-1.5 text-xs font-medium text-[#5C432C] shadow-sm">
                        <i class="fa-solid fa-users-gear text-[#D4A017]"></i>
                        Mode Kru Ganda: Fotografer & Editor
                    </p>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-[#FAF6F0]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">

            {{-- ==================== CLIENT ==================== --}}
            @if($role === Role::CLIENT)
                <!-- Welcome Banner -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#D4A017] via-[#E07A5F] to-[#B56D3E] p-10 text-white shadow-2xl">
                    <div class="absolute -right-10 -top-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute -left-12 bottom-6 w-56 h-56 bg-black/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10 flex flex-col lg:flex-row lg:items-end gap-6">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-md px-5 py-2 rounded-3xl text-sm mb-4">
                                <i class="fa-solid fa-sparkles"></i>
                                Selamat datang kembali!
                            </div>
                            <h3 class="font-display text-5xl font-bold leading-none tracking-tighter mb-3">
                                Siap abadikan momen berikutnya?
                            </h3>
                            <p class="text-white/90 text-xl max-w-md">
                                Kelola pemesanan, lihat progress foto, dan unduh hasil akhir dengan mudah.
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('bookings.create') }}" 
                               class="inline-flex items-center gap-3 px-8 py-4 bg-white text-[#3F2B1B] font-semibold rounded-3xl hover:scale-105 transition-transform shadow-xl">
                                <i class="fa-solid fa-calendar-plus"></i>
                                Pemesanan Baru
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        @php $metrics = $data['metrics'] ?? []; @endphp
                        <x-stat-card label="Total Pemesanan" :value="$metrics['bookings'] ?? 0" />
                        <x-stat-card label="Menunggu Pembayaran" :value="$metrics['waiting_payment'] ?? 0" color="amber" />
                        <x-stat-card label="Sedang Berjalan" :value="$metrics['in_progress'] ?? 0" color="blue" />
                        <x-stat-card label="Final Siap Unduh" :value="$metrics['final_ready'] ?? 0" color="emerald" />
                    </div>
                </section>

                <!-- Recent Bookings -->
                <section class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl overflow-hidden">
                    <div class="px-8 py-6 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                        <h3 class="font-display text-3xl font-semibold text-[#3F2B1B]">Pemesanan Terbaru</h3>
                        <p class="text-[#7A5B3A]">5 pemesanan terakhir Anda</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-[#8B7359] bg-[#FAF6F0]">
                                    <th class="px-8 py-5 text-center font-medium">No</th>
                                    <th class="px-8 py-5 text-center font-medium">Tanggal</th>
                                    <th class="px-8 py-5 text-center font-medium">Paket</th>
                                    <th class="px-8 py-5 text-center font-medium">Status</th>
                                    <th class="px-8 py-5 text-center font-medium">Project</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EDE0D0]">
                                @forelse($data['latest'] ?? [] as $booking)
                                    <tr class="text-[#3F2B1B] hover:bg-[#FAF6F0] transition-all">
                                        <td class="px-8 py-5 text-center font-medium">{{ $loop->iteration }}</td>
                                        <td class="px-8 py-5 text-center">{{ $booking->booking_date->translatedFormat('d M Y') }}</td>
                                        <td class="px-8 py-5 text-center">{{ $booking->package->name ?? '-' }}</td>
                                        <td class="px-8 py-5 text-center">
                                            <x-status-badge :status="$booking->status" />
                                        </td>
                                        <td class="px-8 py-5 text-center">{{ $booking->project?->status ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-8 py-16 text-center text-[#7A5B3A]">
                                            <i class="fa-solid fa-folder-open text-5xl mb-3 block opacity-40"></i>
                                            Belum ada pemesanan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

            {{-- ==================== ADMIN / MANAGER ==================== --}}
            @elseif($role === Role::ADMIN || $role === Role::MANAGER)
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <x-stat-card label="Total Pemesanan" :value="$data['metrics']['bookings'] ?? 0" />
                        <x-stat-card label="Menunggu Pembayaran" :value="$data['metrics']['waiting_payment'] ?? 0" color="amber" />
                        <x-stat-card label="Project Final" :value="$data['metrics']['projects_final'] ?? 0" color="emerald" />
                    </div>
                </section>

                <section class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Status Pemesanan -->
                    <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl p-8">
                        <h3 class="text-2xl font-display font-semibold text-[#3F2B1B] mb-6 flex items-center gap-3">
                            <i class="fa-solid fa-chart-pie text-[#D4A017]"></i>
                            Status Pemesanan
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach(['WAITING_PAYMENT','DP_PAID','PAID','CANCELLED'] as $status)
                                @php
                                    $labels = [
                                        'WAITING_PAYMENT' => 'Menunggu Pembayaran',
                                        'DP_PAID' => 'DP Dibayar',
                                        'PAID' => 'Lunas',
                                        'CANCELLED' => 'Dibatalkan',
                                    ];
                                @endphp
                                <div class="flex justify-between items-center bg-[#FAF6F0] hover:bg-white transition-colors px-6 py-5 rounded-2xl border border-[#EDE0D0]">
                                    <span class="text-[#5C432C]">{{ $labels[$status] ?? $status }}</span>
                                    <span class="font-semibold text-lg bg-white px-5 py-1 rounded-3xl shadow-sm">{{ $data['statusCounts'][$status] ?? 0 }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Jadwal Terdekat -->
                    <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-2xl font-display font-semibold text-[#3F2B1B] flex items-center gap-3">
                                <i class="fa-solid fa-calendar-week text-[#D4A017]"></i>
                                Jadwal Terdekat
                            </h3>
                            <span class="text-xs px-6 py-2 bg-[#FAF6F0] rounded-3xl text-[#5C432C] font-medium">Top 5</span>
                        </div>
                        
                        <div class="space-y-4 max-h-[420px] overflow-y-auto pr-2">
                            @forelse($data['schedules'] ?? [] as $item)
                                <div class="p-6 rounded-2xl border border-[#EDE0D0] hover:border-[#D4A017]/30 hover:shadow-md transition-all bg-white">
                                    <p class="font-semibold text-[#3F2B1B]">{{ $item->booking->location ?? 'N/A' }}
                                        <span class="text-xs text-[#8B7359] ml-3">({{ $item->start_at->translatedFormat('d M H:i') }} – {{ $item->end_at->translatedFormat('H:i') }})</span>
                                    </p>
                                    <div class="grid grid-cols-2 gap-4 text-sm mt-4">
                                        <div class="flex items-center gap-2 text-[#5C432C]">
                                            <i class="fa-solid fa-camera text-[#D4A017]"></i>
                                            {{ $item->photographer->name ?? '-' }}
                                        </div>
                                        <div class="flex items-center gap-2 text-[#5C432C]">
                                            <i class="fa-solid fa-pen-to-square text-[#D4A017]"></i>
                                            {{ $item->editor->name ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-12 text-[#8B7359]">
                                    <i class="fa-solid fa-calendar-xmark text-6xl mb-4 opacity-30"></i>
                                    <p>Belum ada jadwal</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

            {{-- ==================== PHOTOGRAPHER + EDITOR (BOTH) ==================== --}}
            @elseif($hasBothCrewRoles ?? false)
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
                        <x-stat-card label="Project Foto Final" :value="$data['completed'] ?? 0" color="emerald" />
                        <x-stat-card label="Jadwal Foto Mendatang" :value="$data['upcoming']->count() ?? 0" color="blue" />
                        <x-stat-card label="Final Edit Selesai" :value="$data['finalized'] ?? 0" color="emerald" />
                        <x-stat-card label="Antrian Edit" :value="$data['queue']->count() ?? 0" color="amber" />
                    </div>

                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <!-- Tugas Fotografer -->
                        <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl overflow-hidden">
                            <div class="px-8 py-6 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                                <h3 class="font-display text-3xl font-semibold text-[#3F2B1B]">Tugas Fotografer</h3>
                            </div>
                            <div class="divide-y divide-[#EDE0D0]">
                                @forelse($data['upcoming'] ?? [] as $item)
                                    <div class="px-8 py-6 hover:bg-[#FAF6F0] transition-all">
                                        <div class="flex gap-4">
                                            <div class="w-10 h-10 flex-shrink-0 bg-gradient-to-br from-[#D4A017] to-[#E07A5F] rounded-2xl flex items-center justify-center text-white">
                                                <i class="fa-solid fa-camera"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-semibold text-[#3F2B1B]">{{ $item->start_at->translatedFormat('d M H:i') }} – {{ $item->end_at->translatedFormat('H:i') }}</p>
                                                <p class="text-sm text-[#7A5B3A]">Lokasi: {{ $item->booking->location ?? '-' }}</p>
                                                <p class="text-sm text-[#7A5B3A]">Klien: {{ $item->booking->client->name ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-8 py-16 text-center text-[#8B7359]">
                                        <i class="fa-solid fa-calendar-check text-6xl mb-4 opacity-30"></i>
                                        <p>Tidak ada tugas fotografer saat ini</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Tugas Editor -->
                        <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl overflow-hidden">
                            <div class="px-8 py-6 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                                <h3 class="font-display text-3xl font-semibold text-[#3F2B1B]">Tugas Editor</h3>
                            </div>
                            <div class="divide-y divide-[#EDE0D0]">
                                @forelse($data['queue'] ?? [] as $item)
                                    <div class="px-8 py-6 hover:bg-[#FAF6F0] transition-all">
                                        <div class="flex gap-4">
                                            <div class="w-10 h-10 flex-shrink-0 bg-gradient-to-br from-[#6B4A2D] to-[#4C351F] rounded-2xl flex items-center justify-center text-white">
                                                <i class="fa-solid fa-pen-ruler"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-semibold text-[#3F2B1B]">{{ $item->booking->location ?? 'N/A' }}</p>
                                                <p class="text-sm text-[#7A5B3A]">Mulai: {{ optional($item->start_at)?->translatedFormat('d M H:i') ?? '-' }}</p>
                                                <p class="text-sm text-[#7A5B3A]">Klien: {{ $item->booking->client->name ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-8 py-16 text-center text-[#8B7359]">
                                        <i class="fa-solid fa-face-smile text-6xl mb-4 opacity-30"></i>
                                        <p>Tidak ada antrian edit</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

            {{-- ==================== PHOTOGRAPHER ONLY ==================== --}}
            @elseif($role === Role::PHOTOGRAPHER)
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        <x-stat-card label="Project Final" :value="$data['completed'] ?? 0" color="emerald" />
                        <x-stat-card label="Akan Datang" :value="$data['upcoming']->count() ?? 0" color="blue" />
                    </div>
                    <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl overflow-hidden">
                        <div class="px-8 py-6 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                            <h3 class="font-display text-3xl font-semibold text-[#3F2B1B]">Jadwal Mendatang</h3>
                        </div>
                        <div class="divide-y divide-[#EDE0D0]">
                            @forelse($data['upcoming'] ?? [] as $item)
                                <div class="px-8 py-6 hover:bg-[#FAF6F0] transition-all">
                                    <div class="flex gap-4">
                                        <div class="w-10 h-10 flex-shrink-0 bg-gradient-to-br from-[#D4A017] to-[#E07A5F] rounded-2xl flex items-center justify-center text-white">
                                            <i class="fa-solid fa-camera"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-semibold text-[#3F2B1B]">{{ $item->start_at->translatedFormat('d M H:i') }} – {{ $item->end_at->translatedFormat('H:i') }}</p>
                                            <p class="text-sm text-[#7A5B3A]">Lokasi: {{ $item->booking->location ?? '-' }}</p>
                                            <p class="text-sm text-[#7A5B3A]">Klien: {{ $item->booking->client->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-8 py-16 text-center text-[#8B7359]">
                                    <i class="fa-solid fa-calendar-check text-6xl mb-4 opacity-30"></i>
                                    <p>Tidak ada jadwal</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

            {{-- ==================== EDITOR ONLY ==================== --}}
            @elseif($role === Role::EDITOR)
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        <x-stat-card label="Selesai Final" :value="$data['finalized'] ?? 0" color="emerald" />
                        <x-stat-card label="Antrian Edit" :value="$data['queue']->count() ?? 0" color="amber" />
                    </div>
                    <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl overflow-hidden">
                        <div class="px-8 py-6 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                            <h3 class="font-display text-3xl font-semibold text-[#3F2B1B]">Antrian Tugas</h3>
                        </div>
                        <div class="divide-y divide-[#EDE0D0]">
                            @forelse($data['queue'] ?? [] as $item)
                                <div class="px-8 py-6 hover:bg-[#FAF6F0] transition-all">
                                    <div class="flex gap-4">
                                        <div class="w-10 h-10 flex-shrink-0 bg-gradient-to-br from-[#6B4A2D] to-[#4C351F] rounded-2xl flex items-center justify-center text-white">
                                            <i class="fa-solid fa-pen-ruler"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-semibold text-[#3F2B1B]">{{ $item->booking->location ?? 'N/A' }}</p>
                                            <p class="text-sm text-[#7A5B3A]">Mulai: {{ optional($item->start_at)?->translatedFormat('d M H:i') ?? '-' }}</p>
                                            <p class="text-sm text-[#7A5B3A]">Klien: {{ $item->booking->client->name ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-8 py-16 text-center text-[#8B7359]">
                                    <i class="fa-solid fa-face-smile text-6xl mb-4 opacity-30"></i>
                                    <p>Belum ada tugas</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endif

        </div>
    </div>
</x-app-layout>