@php
    use App\Enums\Role;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-money-bill-wave text-[#D4A017]"></i>
                    Laporan Operasional
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Rekap <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Operasional Studio</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
          
            {{-- Filter & Actions --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#D4A017]/10 via-[#E07A5F]/10 rounded-3xl blur-2xl"></div>
                <div class="relative glass border border-[#EDE0D0] rounded-3xl p-8 shadow-xl backdrop-blur-2xl">
                    <form method="GET" class="flex flex-wrap items-end gap-6">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-calendar text-[#D4A017]"></i>
                                Periode Mulai
                            </label>
                            <input type="date" name="date_from" value="{{ $dateFrom }}"
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-calendar text-[#D4A017]"></i>
                                Periode Akhir
                            </label>
                            <input type="date" name="date_to" value="{{ $dateTo }}"
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                        </div>
                        <div class="flex-1 min-w-[220px]">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-[#D4A017]"></i>
                                Kategori Laporan
                            </label>
                            <select name="category_id"
                                    class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string)($categoryId ?? '') === (string)$category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="flex w-full sm:w-auto gap-3">
                            <button class="h-14 px-8 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                                <i class="fa-solid fa-filter"></i>
                                Terapkan Filter
                            </button>
                            <a href="{{ request()->fullUrlWithQuery(['download'=>'csv']) }}"
                               class="h-14 px-8 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all flex items-center justify-center gap-3">
                                <i class="fa-solid fa-file-csv"></i>
                                Unduh CSV
                            </a>
                            <button type="button" onclick="window.print()"
                                    class="h-14 px-8 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all flex items-center justify-center gap-3">
                                <i class="fa-solid fa-print"></i>
                                Print Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary Cards Premium --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6">
                {{-- Total Pemesanan --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-cyan-500/10 rounded-3xl blur-2xl"></div>
                    <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-7 shadow-xl">
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 rounded-3xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                                <i class="fa-solid fa-receipt text-white text-2xl"></i>
                            </div>
                            <span class="text-4xl font-light tabular-nums text-[#3F2B1B]">{{ $totalOrders }}</span>
                        </div>
                        <p class="text-[#7A5B3A]">Total Pemesanan</p>
                    </div>
                </div>

                {{-- Pendapatan Diterima --}}
                <div class="relative group sm:col-span-2 lg:col-span-2">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/10 to-teal-500/10 rounded-3xl blur-2xl"></div>
                    <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-7 shadow-xl">
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                                <i class="fa-solid fa-wallet text-white text-2xl"></i>
                            </div>
                            <div class="text-right">
                                <span class="text-4xl font-light tabular-nums text-[#3F2B1B]">Rp {{ number_format($revenueTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <p class="text-[#7A5B3A]">Pendapatan Diterima</p>
                    </div>
                </div>

                {{-- Editor Bertugas --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-500/10 rounded-3xl blur-2xl"></div>
                    <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-7 shadow-xl">
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 rounded-3xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center">
                                <i class="fa-solid fa-pen-ruler text-white text-2xl"></i>
                            </div>
                            <span class="text-4xl font-light tabular-nums text-[#3F2B1B]">{{ $assignedEditors }}</span>
                        </div>
                        <p class="text-[#7A5B3A]">Editor Bertugas</p>
                    </div>
                </div>

                {{-- Fotografer Bertugas --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-pink-500/10 rounded-3xl blur-2xl"></div>
                    <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-7 shadow-xl">
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 rounded-3xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                                <i class="fa-solid fa-camera text-white text-2xl"></i>
                            </div>
                            <span class="text-4xl font-light tabular-nums text-[#3F2B1B]">{{ $assignedPhotographers }}</span>
                        </div>
                        <p class="text-[#7A5B3A]">Fotografer Bertugas</p>
                    </div>
                </div>

                {{-- Klien Aktif --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-500/10 to-indigo-500/10 rounded-3xl blur-2xl"></div>
                    <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-7 shadow-xl">
                        <div class="flex items-center justify-between mb-6">
                            <div class="w-14 h-14 rounded-3xl bg-gradient-to-br from-sky-500 to-indigo-500 flex items-center justify-center">
                                <i class="fa-solid fa-users text-white text-2xl"></i>
                            </div>
                            <span class="text-4xl font-light tabular-nums text-[#3F2B1B]">{{ $activeClients }}</span>
                        </div>
                        <p class="text-[#7A5B3A]">Klien Aktif</p>
                    </div>
                </div>
            </div>

            {{-- Tabel Pemesanan --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#D4A017]/10 to-[#E07A5F]/10 rounded-3xl blur-2xl"></div>
                <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-8 shadow-2xl">
                    <h3 class="font-display text-2xl text-[#3F2B1B] mb-6 flex items-center gap-3">
                        <i class="fa-solid fa-receipt text-[#D4A017]"></i>
                        Pemesanan dalam Periode
                    </h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b border-[#EDE0D0]">
                                    <th class="px-6 py-5 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">ID</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Paket</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Klien</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Tanggal</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Status</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EDE0D0]">
                                @foreach($bookings as $b)
                                    <tr class="hover:bg-[#FAF6F0] transition-all">
                                        <td class="px-6 py-5 font-mono text-[#D4A017]">#{{ $b->id }}</td>
                                        <td class="px-6 py-5">{{ $b->package->name ?? '-' }}</td>
                                        <td class="px-6 py-5">{{ $b->client->name ?? '-' }}</td>
                                        <td class="px-6 py-5 text-center">{{ optional($b->booking_date)->format('d M Y') }}</td>
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
                                        <td class="px-6 py-5 text-center">
                                            <span class="inline-block px-5 py-2 rounded-3xl text-xs font-medium {{ $statusColors[$b->status] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $statusLabels[$b->status] ?? $b->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 font-medium text-[#3F2B1B]">
                                            Rp {{ number_format($b->total_price ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-right mt-8 pt-6 border-t border-[#EDE0D0]">
                        <p class="text-2xl font-light text-[#3F2B1B]">
                            Total Pendapatan: 
                            <span class="font-semibold text-[#D4A017]">Rp {{ number_format($revenueTotal, 0, ',', '.') }}</span>
                        </p>
                        <p class="text-xs text-[#7A5B3A] mt-2">
                            Nilai ini dihitung dari pembayaran yang berhasil pada periode yang dipilih.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Kinerja Fotografer & Editor --}}
            <div class="grid lg:grid-cols-2 gap-8">
                {{-- Fotografer Performance --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-cyan-500/5 rounded-3xl blur-3xl"></div>
                    <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-8 shadow-2xl">
                        <h3 class="font-display text-2xl text-[#3F2B1B] mb-6 flex items-center gap-3">
                            <i class="fa-solid fa-camera text-[#D4A017]"></i>
                            Kinerja Fotografer
                        </h3>
                        
                        <div class="overflow-x-auto mb-8">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[#EDE0D0]">
                                        <th class="px-6 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B]">Nama</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B]">Total Proyek</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B]">Paket yang Ditangani</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EDE0D0]">
                                    @foreach($photographerPerf as $p)
                                        <tr class="hover:bg-[#FAF6F0]">
                                            <td class="px-6 py-5 font-medium">{{ $p['name'] }}</td>
                                            <td class="px-6 py-5 text-center">{{ $p['total'] }}</td>
                                            <td class="px-6 py-5">
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($p['packages'] as $pkgName => $count)
                                                        <span class="px-4 py-1 rounded-3xl bg-white border border-[#EDE0D0] text-xs">
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

                        <div class="bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                                <div>
                                    <p class="font-medium text-[#3F2B1B]">Grafik Distribusi Proyek Fotografer</p>
                                    <p class="text-xs text-[#7A5B3A]">Perbandingan jumlah proyek per fotografer</p>
                                </div>
                                <select id="chartPhotographerType" 
                                        class="rounded-3xl border border-[#E1D3C5] bg-white px-6 py-3 text-sm focus:border-[#D4A017]">
                                    <option value="bar">Bar Chart (Peringkat)</option>
                                    <option value="doughnut">Donut Chart</option>
                                </select>
                            </div>
                            <div class="h-80">
                                <canvas id="chartPhotographer"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Editor Performance --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-500/5 rounded-3xl blur-3xl"></div>
                    <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-8 shadow-2xl">
                        <h3 class="font-display text-2xl text-[#3F2B1B] mb-6 flex items-center gap-3">
                            <i class="fa-solid fa-pen-ruler text-[#D4A017]"></i>
                            Kinerja Editor
                        </h3>
                        
                        <div class="overflow-x-auto mb-8">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-[#EDE0D0]">
                                        <th class="px-6 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B]">Nama</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B]">Total Proyek</th>
                                        <th class="px-6 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B]">Paket yang Ditangani</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EDE0D0]">
                                    @foreach($editorPerf as $e)
                                        <tr class="hover:bg-[#FAF6F0]">
                                            <td class="px-6 py-5 font-medium">{{ $e['name'] }}</td>
                                            <td class="px-6 py-5 text-center">{{ $e['total'] }}</td>
                                            <td class="px-6 py-5">
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($e['packages'] as $pkgName => $count)
                                                        <span class="px-4 py-1 rounded-3xl bg-white border border-[#EDE0D0] text-xs">
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

                        <div class="bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                                <div>
                                    <p class="font-medium text-[#3F2B1B]">Grafik Distribusi Proyek Editor</p>
                                    <p class="text-xs text-[#7A5B3A]">Perbandingan jumlah proyek per editor</p>
                                </div>
                                <select id="chartEditorType" 
                                        class="rounded-3xl border border-[#E1D3C5] bg-white px-6 py-3 text-sm focus:border-[#D4A017]">
                                    <option value="bar">Bar Chart (Peringkat)</option>
                                    <option value="doughnut">Donut Chart</option>
                                </select>
                            </div>
                            <div class="h-80">
                                <canvas id="chartEditor"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            // Chart configuration tetap sama seperti sebelumnya (dari kode lama Anda)
            const rankColors = ['#D4A017', '#9aa4b2', '#c47a44', '#b58042', '#d1a674', '#e4c8a3', '#f0ddc2'];

            function buildPerformanceChart(canvasId, controlId, labels, data, defaultColor, valueLabel) {
                const canvas = document.getElementById(canvasId);
                const control = document.getElementById(controlId);
                if (!canvas) return;

                const items = labels.map((label, i) => ({
                    label,
                    value: Number(data[i] ?? 0)
                })).sort((a, b) => b.value - a.value);

                if (!items.length) {
                    canvas.parentElement.innerHTML = `<div class="h-full flex items-center justify-center text-[#8B7359]">Belum ada data pada periode ini.</div>`;
                    return;
                }

                const backgroundColors = items.map((_, i) => rankColors[i] ?? defaultColor);

                const renderChart = (type) => {
                    if (window[`chart${canvasId}`]) window[`chart${canvasId}`].destroy();

                    const isDoughnut = type === 'doughnut';

                    window[`chart${canvasId}`] = new Chart(canvas, {
                        type: type,
                        data: {
                            labels: items.map(i => i.label),
                            datasets: [{
                                label: valueLabel,
                                data: items.map(i => i.value),
                                backgroundColor: isDoughnut ? backgroundColors : backgroundColors,
                                borderRadius: isDoughnut ? 0 : 8,
                                barThickness: isDoughnut ? undefined : 22
                            }]
                        },
                        options: isDoughnut ? {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, color: '#5C432C' } }
                            }
                        } : {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, grid: { color: 'rgba(139,115,89,0.1)' } },
                                y: { grid: { display: false } }
                            }
                        }
                    });
                };

                renderChart(control?.value || 'bar');

                if (control) {
                    control.addEventListener('change', e => renderChart(e.target.value));
                }
            }

            // Inisialisasi Chart
            buildPerformanceChart('chartPhotographer', 'chartPhotographerType', 
                {!! json_encode($chart['photographers']['labels'] ?? []) !!}, 
                {!! json_encode($chart['photographers']['data'] ?? []) !!}, 
                '#D4A017', 'Proyek');

            buildPerformanceChart('chartEditor', 'chartEditorType', 
                {!! json_encode($chart['editors']['labels'] ?? []) !!}, 
                {!! json_encode($chart['editors']['data'] ?? []) !!}, 
                '#8b5b2e', 'Proyek');
        </script>
    @endpush
</x-app-layout>