<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-[#b58042]"></i>
                    Monitoring Pemesanan
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Daftar <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">Pemesanan Masuk</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Filter Form dengan Glassmorphism --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/20 to-[#8b5b2e]/20 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                    <form method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            {{-- Status Pembayaran --}}
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-credit-card text-[#b58042]"></i>
                                    Status Pembayaran
                                </label>
                                <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042] transition-all">
                                    <option value="">Semua Status</option>
                                    @php
                                        $statusLabels = [
                                            'WAITING_PAYMENT' => 'Menunggu Pembayaran',
                                            'DP_PAID' => 'DP Dibayar',
                                            'PAID' => 'Lunas',
                                            'CANCELLED' => 'Dibatalkan',
                                        ];
                                    @endphp
                                    @foreach($statuses as $st)
                                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $statusLabels[$st] ?? $st }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Status Jadwal --}}
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-calendar text-[#b58042]"></i>
                                    Status Jadwal
                                </label>
                                <select name="schedule_status" class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                    <option value="">Semua</option>
                                    <option value="scheduled" @selected(request('schedule_status')==='scheduled')>Sudah dijadwalkan</option>
                                    <option value="unscheduled" @selected(request('schedule_status')==='unscheduled')>Belum dijadwalkan</option>
                                </select>
                            </div>

                            {{-- Paket --}}
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-box text-[#b58042]"></i>
                                    Paket
                                </label>
                                <select name="package_id" class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                    <option value="">Semua Paket</option>
                                    @foreach($packages ?? [] as $p)
                                        <option value="{{ $p->id }}" @selected(request('package_id')==$p->id)>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Rentang Tanggal --}}
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-calendar-range text-[#b58042]"></i>
                                    Rentang Tanggal
                                </label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                                        class="px-3 py-2 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                                        class="px-3 py-2 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                </div>
                            </div>
                        </div>

                        {{-- Search Bar --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div class="md:col-span-3 space-y-2">
                                <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-magnifying-glass text-[#b58042]"></i>
                                    Cari (ID/Klien/Paket)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                        <i class="fa-solid fa-search"></i>
                                    </span>
                                    <input type="text" name="q" value="{{ request('q') }}" 
                                           placeholder="Misal: 5 atau 'Client Demo'"
                                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                </div>
                            </div>
                            <div class="md:col-span-1 flex justify-end">
                                <button class="w-full md:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-filter"></i>
                                    Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabel Pemesanan --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/10 to-[#8b5b2e]/10 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-[#faf3eb] to-white border-b border-[#e3d5c4]">
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">ID</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Klien</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Paket</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Tanggal</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Status Jadwal</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Status Pembayaran</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Project</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Aksi Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e4d6]">
                                @foreach($bookings as $b)
                                    <tr class="hover:bg-white/80 transition-colors">
                                        <td class="px-6 py-4">
                                            <span class="font-mono text-[#b58042] font-medium">#{{ $b->id }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#b58042]/20 to-[#8b5b2e]/20 flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-[#5b422b]">{{ substr($b->client->name ?? 'U', 0, 1) }}</span>
                                                </div>
                                                <span class="font-medium text-[#3f2b1b]">{{ $b->client->name ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-[#6f5134]">{{ $b->package->name ?? '-' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="text-sm text-[#6f5134]">{{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}</span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $scheduleBadge = $b->project && $b->project->schedule
                                                    ? ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Terjadwal']
                                                    : ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Belum Dijadwalkan'];
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium {{ $scheduleBadge['bg'] }} {{ $scheduleBadge['text'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $scheduleBadge['text'] }} bg-current"></span>
                                                {{ $scheduleBadge['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @php
                                                $badge = [
                                                    'WAITING_PAYMENT' => ['bg'=>'bg-amber-100', 'text'=>'text-amber-700', 'label'=>'Menunggu Pembayaran'],
                                                    'DP_PAID'         => ['bg'=>'bg-blue-100', 'text'=>'text-blue-700', 'label'=>'DP Dibayar'],
                                                    'PAID'            => ['bg'=>'bg-emerald-100', 'text'=>'text-emerald-700', 'label'=>'Lunas'],
                                                    'CANCELLED'       => ['bg'=>'bg-red-100', 'text'=>'text-red-700', 'label'=>'Dibatalkan'],
                                                ][$b->status] ?? ['bg'=>'bg-gray-100', 'text'=>'text-gray-700', 'label'=>$b->status];
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium {{ $badge['bg'] }} {{ $badge['text'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $badge['text'] }} bg-current"></span>
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($b->project)
                                                <a href="{{ route('projects.show', $b->project->id) }}" 
                                                   class="text-[#b58042] hover:text-[#8b5b2e] transition-colors flex items-center gap-1">
                                                    <i class="fa-solid fa-folder-open"></i>
                                                    <span>Lihat Project</span>
                                                </a>
                                            @else
                                                <span class="text-[#8b7359] text-sm">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @php
                                                $canUpdatePayment = $b->status === 'DP_PAID';
                                                $paymentActionLabel = [
                                                    'WAITING_PAYMENT' => 'Menunggu Pembayaran',
                                                    'DP_PAID' => 'DP Dibayar',
                                                    'PAID' => 'Lunas',
                                                    'CANCELLED' => 'Dibatalkan',
                                                ][$b->status] ?? $b->status;
                                            @endphp

                                            @if($canUpdatePayment)
                                                <div class="space-y-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg border border-blue-200 bg-blue-50 text-sm text-blue-700">
                                                            DP Dibayar
                                                        </span>
                                                        <button type="button"
                                                                class="px-3 py-1.5 rounded-lg border border-[#d7c5b2] bg-white/60 text-xs font-semibold text-[#6f5134] hover:bg-white transition-colors"
                                                                onclick="document.getElementById('payment-action-{{ $b->id }}').classList.toggle('hidden')">
                                                            Ubah Status
                                                        </button>
                                                    </div>
                                                    <form id="payment-action-{{ $b->id }}" method="POST" action="{{ route('admin.bookings.status', $b) }}" class="hidden flex items-center gap-2">
                                                        @csrf
                                                        <select name="status" 
                                                                class="px-3 py-1.5 rounded-lg border border-[#d7c5b2] bg-white/50 text-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                                            <option value="PAID">Lunas</option>
                                                            <option value="CANCELLED">Dibatalkan</option>
                                                        </select>
                                                        <button class="px-3 py-1.5 rounded-lg bg-[#b58042] text-white text-xs font-semibold hover:bg-[#9b6a34] transition-colors">
                                                            <i class="fa-solid fa-check mr-1"></i>
                                                            Simpan
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                @php
                                                    $paymentActionStyle = [
                                                        'WAITING_PAYMENT' => 'border-amber-200 bg-amber-50 text-amber-700',
                                                        'PAID' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                                        'CANCELLED' => 'border-red-200 bg-red-50 text-red-700',
                                                    ][$b->status] ?? 'border-[#d7c5b2] bg-white/60 text-[#6f5134]';
                                                @endphp
                                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg border text-sm {{ $paymentActionStyle }}">
                                                    {{ $paymentActionLabel }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="px-6 py-4 border-t border-[#e3d5c4] bg-gradient-to-r from-[#faf3eb] to-white">
                        {{ $bookings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
