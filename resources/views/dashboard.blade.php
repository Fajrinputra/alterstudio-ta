@php
    use App\Enums\Role;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#7a5b3a] flex items-center gap-2">
                    <i class="fa-solid fa-calendar text-[#b58042]"></i>
                    {{ now()->isoFormat('dddd, D MMMM YYYY') }}
                </p>
                <h2 class="font-display font-bold text-3xl text-[#3f2b1b] leading-tight">
                    Dashboard
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Role: CLIENT Dashboard --}}
            @if($role === Role::CLIENT)
                {{-- Welcome Banner (tetap sama struktur) --}}
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] p-8 text-white">
                    <div class="absolute right-0 top-0 -mt-10 -mr-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute left-0 bottom-0 -mb-10 -ml-10 w-40 h-40 bg-black/10 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10">
                        <h3 class="font-display text-2xl font-bold mb-2">Selamat datang kembali!</h3>
                        <p class="text-white/90 mb-4">Kelola semua pemesanan anda dengan mudah.</p>
                    </div>
                </div>

                {{-- Statistics Cards dengan styling lebih baik (struktur tetap) --}}
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        @php
                            $metrics = $data['metrics'] ?? [];
                        @endphp
                        <x-stat-card label="Total Pemesanan" :value="$metrics['bookings'] ?? 0" />
                        <x-stat-card label="Menunggu Pembayaran" :value="$metrics['waiting_payment'] ?? 0" color="amber" />
                        <x-stat-card label="Sedang Berjalan" :value="$metrics['in_progress'] ?? 0" color="blue" />
                        <x-stat-card label="Final Siap Unduh" :value="$metrics['final_ready'] ?? 0" color="emerald" />
                    </div>
                </section>

                {{-- Recent Bookings Table (struktur tabel tetap sama, hanya styling) --}}
                <section class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-lg overflow-hidden">
                    <div class="px-6 py-5 border-b border-[#e3d5c4] bg-gradient-to-r from-[#faf3eb] to-white">
                        <h3 class="text-xl font-display font-semibold text-[#3f2b1b]">
                            Pemesanan Terbaru
                        </h3>
                        <p class="text-sm text-[#7a5b3a] mt-1">5 pemesanan terakhir milik Anda</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-[#7a5b3a] bg-[#f9f1e7]/50">
                                    <th class="px-6 py-4 text-center">No</th>
                                    <th class="px-6 py-4 text-center">Tanggal</th>
                                    <th class="px-6 py-4 text-center">Paket</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Project</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e4d6]">
                                @forelse($data['latest'] ?? [] as $booking)
                                    <tr class="text-[#5b422b] hover:bg-[#fcf7f1] transition-colors">
                                        <td class="px-6 py-4 text-center font-medium">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4 text-center">{{ $booking->booking_date->translatedFormat('d M Y') }}</td>
                                        <td class="px-6 py-4 text-center">{{ $booking->package->name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <x-status-badge :status="$booking->status" />
                                        </td>
                                        <td class="px-6 py-4 text-center">{{ $booking->project?->status ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-[#7a5b3a]">
                                            <i class="fa-solid fa-folder-open text-4xl mb-2 block opacity-50"></i>
                                            Belum ada pemesanan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

            {{-- Role: ADMIN/MANAGER Dashboard (struktur tetap) --}}
            @elseif($role === Role::ADMIN || $role === Role::MANAGER)
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <x-stat-card label="Total Pemesanan" :value="$data['metrics']['bookings'] ?? 0" />
                        <x-stat-card label="Menunggu Pembayaran" :value="$data['metrics']['waiting_payment'] ?? 0" color="amber" />
                        <x-stat-card label="Project Final" :value="$data['metrics']['projects_final'] ?? 0" color="emerald" />
                    </div>
                </section>

                <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Status Pemesanan Card --}}
                    <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-display font-semibold text-[#3f2b1b] mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-[#b58042]"></i>
                            Status Pemesanan
                        </h3>
                        
                        <dl class="grid grid-cols-2 gap-4 text-sm text-[#5b422b]">
                            @foreach(['WAITING_PAYMENT','DP_PAID','PAID','CANCELLED','DRAFT'] as $status)
                                @php
                                    $labels = [
                                        'WAITING_PAYMENT' => 'Menunggu Pembayaran',
                                        'DP_PAID' => 'DP Dibayar',
                                        'PAID' => 'Lunas',
                                        'CANCELLED' => 'Dibatalkan',
                                        'DRAFT' => 'Draft',
                                    ];
                                @endphp
                                <div class="flex items-center justify-between p-3 bg-[#fcf7f1] rounded-lg">
                                    <span>{{ $labels[$status] ?? $status }}</span>
                                    <span class="font-semibold bg-white px-3 py-1 rounded-full">{{ $data['statusCounts'][$status] ?? 0 }}</span>
                                </div>
                            @endforeach
                        </dl>
                    </div>

                    {{-- Jadwal Terdekat Card --}}
                    <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-display font-semibold text-[#3f2b1b] flex items-center gap-2">
                                <i class="fa-solid fa-calendar-week text-[#b58042]"></i>
                                Jadwal Terdekat
                            </h3>
                            <span class="text-xs px-3 py-1 bg-[#f0e4d6] rounded-full text-[#6c4f32]">
                                Top 5
                            </span>
                        </div>
                        
                        <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
                            @forelse($data['schedules'] ?? [] as $item)
                                <div class="p-4 rounded-xl border border-[#e3d5c4] hover:bg-[#fcf7f1] transition-all">
                                    <p class="text-sm font-semibold text-[#3f2b1b] mb-2">
                                        {{ $item->project->booking->location ?? 'N/A' }}
                                        <span class="text-xs text-[#7a5b3a] ml-2">({{ $item->start_at->translatedFormat('d M H:i') }} - {{ $item->end_at->translatedFormat('H:i') }})</span>
                                    </p>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="flex items-center gap-1 text-[#7a5b3a]">
                                            <i class="fa-solid fa-camera text-[#b58042]"></i>
                                            {{ $item->photographer->name ?? '-' }}
                                        </div>
                                        <div class="flex items-center gap-1 text-[#7a5b3a]">
                                            <i class="fa-solid fa-pen-to-square text-[#b58042]"></i>
                                            {{ $item->editor->name ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center py-8 text-[#7a5b3a]">
                                    <i class="fa-solid fa-calendar-xmark text-4xl mb-2 opacity-50"></i>
                                    <br>Belum ada jadwal.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </section>

            {{-- Role: PHOTOGRAPHER Dashboard --}}
            @elseif($role === Role::PHOTOGRAPHER)
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                        <x-stat-card label="Project Final" :value="$data['completed'] ?? 0" color="emerald" />
                        <x-stat-card label="Akan Datang" :value="$data['upcoming']->count() ?? 0" color="blue" />
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-lg overflow-hidden">
                        <div class="px-6 py-5 border-b border-[#e3d5c4] bg-gradient-to-r from-[#faf3eb] to-white">
                            <h3 class="text-xl font-display font-semibold text-[#3f2b1b]">
                                Jadwal Mendatang
                            </h3>
                        </div>
                        
                        <div class="divide-y divide-[#f0e4d6]">
                            @forelse($data['upcoming'] ?? [] as $item)
                                <div class="px-6 py-4 hover:bg-[#fcf7f1] transition-colors">
                                    <div class="flex items-center gap-3 mb-1">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#b58042] to-[#8b5b2e] flex items-center justify-center text-white">
                                            <i class="fa-solid fa-camera text-sm"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-[#3f2b1b]">
                                            {{ $item->start_at->translatedFormat('d M H:i') }} - {{ $item->end_at->translatedFormat('H:i') }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-[#7a5b3a] ml-11">Lokasi: {{ $item->location }}</p>
                                    <p class="text-xs text-[#7a5b3a] ml-11">Klien: {{ $item->project->booking->client->name ?? '-' }}</p>
                                </div>
                            @empty
                                <p class="px-6 py-8 text-center text-[#7a5b3a]">
                                    <i class="fa-solid fa-calendar-check text-4xl mb-2 opacity-50"></i>
                                    <br>Tidak ada jadwal.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </section>

            {{-- Role: EDITOR Dashboard --}}
            @elseif($role === Role::EDITOR)
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                        <x-stat-card label="Selesai Final" :value="$data['finalized'] ?? 0" color="emerald" />
                        <x-stat-card label="Antrian Edit" :value="$data['queue']->count() ?? 0" color="amber" />
                    </div>

                    <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-lg overflow-hidden">
                        <div class="px-6 py-5 border-b border-[#e3d5c4] bg-gradient-to-r from-[#faf3eb] to-white">
                            <h3 class="text-xl font-display font-semibold text-[#3f2b1b]">
                                Antrian Tugas
                            </h3>
                        </div>

                        <div class="divide-y divide-[#f0e4d6]">
                            @forelse($data['queue'] ?? [] as $item)
                                <div class="px-6 py-4 hover:bg-[#fcf7f1] transition-colors">
                                    <div class="flex items-center gap-3 mb-1">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#6b4a2d] to-[#4c351f] flex items-center justify-center text-white">
                                            <i class="fa-solid fa-pen-ruler text-sm"></i>
                                        </div>
                                        <p class="text-sm font-semibold text-[#3f2b1b]">
                                            {{ $item->project->booking->location ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <p class="text-xs text-[#7a5b3a] ml-11">Mulai: {{ optional($item->start_at)?->translatedFormat('d M H:i') ?? '-' }}</p>
                                    <p class="text-xs text-[#7a5b3a] ml-11">Klien: {{ $item->project->booking->client->name ?? '-' }}</p>
                                </div>
                            @empty
                                <p class="px-6 py-8 text-center text-[#7a5b3a]">
                                    <i class="fa-solid fa-face-smile text-4xl mb-2 opacity-50"></i>
                                    <br>Belum ada tugas.
                                </p>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
