<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-[#b58042]"></i>
                    Penggajian
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Rekap <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">Gaji & Insentif</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Filter & Actions --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/20 to-[#8b5b2e]/20 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                    <form method="GET" class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-[#6f5134] mb-1 flex items-center gap-1">
                                <i class="fa-solid fa-calendar text-[#b58042]"></i>
                                Periode Mulai
                            </label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-[#6f5134] mb-1 flex items-center gap-1">
                                <i class="fa-solid fa-calendar text-[#b58042]"></i>
                                Periode Akhir
                            </label>
                            <input type="date" name="date_to" value="{{ $dateTo }}" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                        </div>
                        <div class="flex-1 min-w-[220px]">
                            <label class="block text-xs font-medium text-[#6f5134] mb-1 flex items-center gap-1">
                                <i class="fa-solid fa-layer-group text-[#b58042]"></i>
                                Kategori Laporan
                            </label>
                            <select name="category_id"
                                    class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string)($categoryId ?? '') === (string)$category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="flex gap-2">
                            <button class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-2">
                                <i class="fa-solid fa-filter"></i>
                                Terapkan
                            </button>
                            <a href="{{ request()->fullUrlWithQuery(['download'=>'csv']) }}" 
                               class="px-6 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white/80 transition-all flex items-center gap-2">
                                <i class="fa-solid fa-file-csv"></i>
                                Unduh CSV
                            </a>
                            <button type="button" onclick="window.print()" 
                                    class="px-6 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white/80 transition-all flex items-center gap-2">
                                <i class="fa-solid fa-print"></i>
                                Print
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-5">
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-cyan-500/20 rounded-2xl blur-xl"></div>
                    <div class="relative p-6 rounded-2xl bg-white/70 backdrop-blur-xl border border-[#e3d5c4]">
                        <div class="flex items-center justify-between gap-5 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                                <i class="fa-solid fa-receipt text-white"></i>
                            </div>
                            <span class="min-w-0 text-right text-3xl font-light text-[#3f2b1b] leading-none tabular-nums">{{ $totalOrders }}</span>
                        </div>
                        <p class="text-sm text-[#8b7359]">Total Pemesanan</p>
                    </div>
                </div>

                <div class="relative group sm:col-span-2 lg:col-span-2">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 rounded-2xl blur-xl"></div>
                    <div class="relative p-6 rounded-2xl bg-white/70 backdrop-blur-xl border border-[#e3d5c4]">
                        <div class="flex items-center justify-between gap-5 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                                <i class="fa-solid fa-wallet text-white"></i>
                            </div>
                            <span class="inline-flex items-baseline gap-1 text-[#3f2b1b] whitespace-nowrap min-w-0">
                                <span class="text-base font-medium">Rp</span>
                                <span class="text-2xl font-light tabular-nums">{{ number_format($revenueTotal, 0, ',', '.') }}</span>
                            </span>
                        </div>
                        <p class="text-sm text-[#8b7359]">Total Pendapatan</p>
                    </div>
                </div>

                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-amber-500/20 to-orange-500/20 rounded-2xl blur-xl"></div>
                    <div class="relative p-6 rounded-2xl bg-white/70 backdrop-blur-xl border border-[#e3d5c4]">
                        <div class="flex items-center justify-between gap-5 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
                                <i class="fa-solid fa-pen-ruler text-white"></i>
                            </div>
                            <span class="min-w-0 text-right text-3xl font-light text-[#3f2b1b] leading-none tabular-nums">{{ $activeEditors }}</span>
                        </div>
                        <p class="text-sm text-[#8b7359]">Editor Aktif</p>
                    </div>
                </div>

                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-2xl blur-xl"></div>
                    <div class="relative p-6 rounded-2xl bg-white/70 backdrop-blur-xl border border-[#e3d5c4]">
                        <div class="flex items-center justify-between gap-5 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                                <i class="fa-solid fa-camera text-white"></i>
                            </div>
                            <span class="min-w-0 text-right text-3xl font-light text-[#3f2b1b] leading-none tabular-nums">{{ $activePhotographers }}</span>
                        </div>
                        <p class="text-sm text-[#8b7359]">Fotografer Aktif</p>
                    </div>
                </div>

                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-sky-500/20 to-indigo-500/20 rounded-2xl blur-xl"></div>
                    <div class="relative p-6 rounded-2xl bg-white/70 backdrop-blur-xl border border-[#e3d5c4]">
                        <div class="flex items-center justify-between gap-5 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-500 flex items-center justify-center">
                                <i class="fa-solid fa-users text-white"></i>
                            </div>
                            <span class="min-w-0 text-right text-3xl font-light text-[#3f2b1b] leading-none tabular-nums">{{ $activeClients }}</span>
                        </div>
                        <p class="text-sm text-[#8b7359]">Klien Aktif</p>
                    </div>
                </div>
            </div>

            {{-- Tabel Pemesanan --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/10 to-[#8b5b2e]/10 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                    <h3 class="text-xl font-light text-[#3f2b1b] mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-[#b58042]"></i>
                        Pemesanan dalam Periode
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-[#e3d5c4]">
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-[#3f2b1b]">ID</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-[#3f2b1b]">Paket</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-[#3f2b1b]">Klien</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-[#3f2b1b]">Tanggal</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-[#3f2b1b]">Status</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-[#3f2b1b]">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e4d6]">
                                @foreach($bookings as $b)
                                    <tr class="hover:bg-white/50 transition-colors">
                                        <td class="px-4 py-3 font-mono text-[#b58042]">#{{ $b->id }}</td>
                                        <td class="px-4 py-3">{{ $b->package->name ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $b->client->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text -center">{{ optional($b->booking_date)->format('d M Y') }}</td>
                                        @php
                                            $statusLabels = [
                                                'WAITING_PAYMENT' => 'Menunggu Pembayaran',
                                                'DP_PAID' => 'DP Dibayar',
                                                'PAID' => 'Lunas',
                                                'CANCELLED' => 'Dibatalkan',
                                                'EXPIRED' => 'Kedaluwarsa',
                                                'FAILED' => 'Gagal',
                                            ];
                                            $statusColors = [
                                                'WAITING_PAYMENT' => 'bg-amber-100 text-amber-700',
                                                'DP_PAID' => 'bg-blue-100 text-blue-700',
                                                'PAID' => 'bg-emerald-100 text-emerald-700',
                                                'CANCELLED' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$b->status] ?? 'bg-gray-100' }}">
                                                {{ $statusLabels[$b->status] ?? $b->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text- left">Rp {{ number_format($b->total_price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-right mt-4 pt-4 border-t border-[#e3d5c4]">
                        <p class="text-lg font-light text-[#3f2b1b]">
                            Total Pendapatan: 
                            <span class="font-semibold text-[#b58042]">Rp {{ number_format($revenueTotal, 0, ',', '.') }}</span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Kinerja Fotografer & Editor --}}
            <div class="grid lg:grid-cols-2 gap-6">
                {{-- Fotografer Performance --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 to-cyan-500/10 rounded-2xl blur-xl"></div>
                    <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                        <h3 class="text-xl font-light text-[#3f2b1b] mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-camera text-[#b58042]"></i>
                            Kinerja Fotografer
                        </h3>
                        
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[#e3d5c4]">
                                        <th class="px-3 py-2 text-center">Nama</th>
                                        <th class="px-3 py-2 text-center">Total Proyek</th>
                                        <th class="px-3 py-2 text-center">Detail Paket</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e4d6]">
                                    @foreach($photographerPerf as $p)
                                        <tr>
                                            <td class="px-3 py-2 font-medium">{{ $p['name'] }}</td>
                                            <td class="px-3 py-2">{{ $p['total'] }}</td>
                                            <td class="px-3 py-2">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($p['packages'] as $pkgName => $count)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#f1e5d8] text-xs text-[#5b422b]">
                                                            {{ $pkgName }} ({{ $count }})
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <canvas id="chartPhotographer" height="140"></canvas>
                    </div>
                </div>

                {{-- Editor Performance --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-amber-500/10 to-orange-500/10 rounded-2xl blur-xl"></div>
                    <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                        <h3 class="text-xl font-light text-[#3f2b1b] mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-pen-ruler text-[#b58042]"></i>
                            Kinerja Editor
                        </h3>
                        
                        <div class="overflow-x-auto mb-4">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[#e3d5c4]">
                                        <th class="px-3 py-2 text-center">Nama</th>
                                        <th class="px-3 py-2 text-center">Total Proyek</th>
                                        <th class="px-3 py-2 text-center">Detail Paket</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#f0e4d6]">
                                    @foreach($editorPerf as $e)
                                        <tr>
                                            <td class="px-3 py-2 font-medium">{{ $e['name'] }}</td>
                                            <td class="px-3 py-2">{{ $e['total'] }}</td>
                                            <td class="px-3 py-2">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($e['packages'] as $pkgName => $count)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-[#f1e5d8] text-xs text-[#5b422b]">
                                                            {{ $pkgName }} ({{ $count }})
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <canvas id="chartEditor" height="140"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Chart Fotografer
            const photogCtx = document.getElementById('chartPhotographer');
            if (photogCtx) {
                new Chart(photogCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($chart['photographers']['labels']) !!},
                        datasets: [{
                            label: 'Jumlah Proyek',
                            data: {!! json_encode($chart['photographers']['data']) !!},
                            backgroundColor: '#b58042',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(139, 115, 89, 0.1)'
                                }
                            }
                        }
                    }
                });
            }

            // Chart Editor
            const editorCtx = document.getElementById('chartEditor');
            if (editorCtx) {
                new Chart(editorCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($chart['editors']['labels']) !!},
                        datasets: [{
                            label: 'Jumlah Proyek',
                            data: {!! json_encode($chart['editors']['data']) !!},
                            backgroundColor: '#8b5b2e',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(139, 115, 89, 0.1)'
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
