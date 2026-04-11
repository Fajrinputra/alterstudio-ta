<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-[#D4A017]"></i>
                    Monitoring Pemesanan
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Daftar <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Pemesanan Masuk</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
          
            {{-- Filter Form dengan Glassmorphism Premium --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#D4A017]/10 via-[#E07A5F]/10 to-[#D4A017]/10 rounded-3xl blur-2xl opacity-70 group-hover:opacity-90 transition-all"></div>
                <div class="relative glass border border-[#EDE0D0] rounded-3xl p-8 shadow-xl backdrop-blur-2xl">
                    <form method="GET" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            {{-- Status Pembayaran --}}
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-[#7A5B3A] flex items-center gap-2 tracking-widest">
                                    <i class="fa-solid fa-credit-card text-[#D4A017]"></i>
                                    Status Pembayaran
                                </label>
                                <select name="status" class="w-full px-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-sm">
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
                                <label class="text-xs font-medium text-[#7A5B3A] flex items-center gap-2 tracking-widest">
                                    <i class="fa-solid fa-calendar text-[#D4A017]"></i>
                                    Status Jadwal
                                </label>
                                <select name="schedule_status" class="w-full px-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-sm">
                                    <option value="">Semua</option>
                                    <option value="scheduled" @selected(request('schedule_status')==='scheduled')>Sudah dijadwalkan</option>
                                    <option value="unscheduled" @selected(request('schedule_status')==='unscheduled')>Belum dijadwalkan</option>
                                </select>
                            </div>
                            {{-- Paket --}}
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-[#7A5B3A] flex items-center gap-2 tracking-widest">
                                    <i class="fa-solid fa-box text-[#D4A017]"></i>
                                    Paket
                                </label>
                                <select name="package_id" class="w-full px-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-sm">
                                    <option value="">Semua Paket</option>
                                    @foreach($packages ?? [] as $p)
                                        <option value="{{ $p->id }}" @selected(request('package_id')==$p->id)>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Rentang Tanggal --}}
                            <div class="space-y-2">
                                <label class="text-xs font-medium text-[#7A5B3A] flex items-center gap-2 tracking-widest">
                                    <i class="fa-solid fa-calendar-range text-[#D4A017]"></i>
                                    Rentang Tanggal
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                                        class="px-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-sm">
                                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                                        class="px-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-sm">
                                </div>
                            </div>
                        </div>
                        {{-- Search Bar --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                            <div class="md:col-span-3 space-y-2">
                                <label class="text-xs font-medium text-[#7A5B3A] flex items-center gap-2 tracking-widest">
                                    <i class="fa-solid fa-magnifying-glass text-[#D4A017]"></i>
                                    Cari (ID/Klien/Paket)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                        <i class="fa-solid fa-search"></i>
                                    </span>
                                    <input type="text" name="q" value="{{ request('q') }}"
                                           placeholder="Misal: 5 atau 'Client Demo'"
                                           class="w-full pl-12 pr-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-sm">
                                </div>
                            </div>
                            <div class="md:col-span-1">
                                <button class="w-full h-[52px] rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl shadow-[#D4A017]/30 hover:shadow-2xl hover:-translate-y-0.5 active:scale-[0.98] transition-all flex items-center justify-center gap-3 text-base">
                                    <i class="fa-solid fa-filter"></i>
                                    Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabel Pemesanan Premium --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/5 via-[#E07A5F]/5 to-[#FAF6F0] rounded-3xl blur-3xl"></div>
                <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl shadow-2xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-[#FAF6F0] via-white to-[#FAF6F0] border-b border-[#EDE0D0]">
                                    <th class="px-6 py-5 text-center text-xs font-semibold text-[#3F2B1B] tracking-widest uppercase">ID</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold text-[#3F2B1B] tracking-widest uppercase">Klien</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold text-[#3F2B1B] tracking-widest uppercase">Paket</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold text-[#3F2B1B] tracking-widest uppercase">Tanggal</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold text-[#3F2B1B] tracking-widest uppercase">Studio</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold text-[#3F2B1B] tracking-widest uppercase">Status Jadwal</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold text-[#3F2B1B] tracking-widest uppercase">Status Pembayaran</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold text-[#3F2B1B] tracking-widest uppercase">Project</th>
                                    <th class="px-6 py-5 text-center text-xs font-semibold text-[#3F2B1B] tracking-widest uppercase">Aksi Pembayaran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EDE0D0]">
                                @foreach($bookings as $b)
                                    <tr class="hover:bg-[#FAF6F0] transition-all duration-300 group/row">
                                        <td class="px-6 py-5">
                                            <span class="font-mono text-[#D4A017] font-semibold text-base">#{{ $b->id }}</span>
                                        </td>
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center flex-shrink-0">
                                                    <span class="text-sm font-semibold text-[#3F2B1B]">{{ substr($b->client->name ?? 'U', 0, 1) }}</span>
                                                </div>
                                                <span class="font-medium text-[#3F2B1B]">{{ $b->client->name ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <div class="text-[#3F2B1B] font-medium">{{ $b->package->name ?? '-' }}</div>
                                            @if(!empty($b->selected_addons))
                                                <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                                                    @foreach($b->selected_addons as $addon)
                                                        <span class="px-3 py-1 rounded-3xl bg-white border border-[#EDE0D0] text-[#7A5B3A] text-xs flex items-center gap-1 shadow-sm">
                                                            {{ $addon['label'] ?? '-' }}
                                                            @if(!empty($addon['quantity']))
                                                                <span class="font-mono">×{{ (int) $addon['quantity'] }}</span>
                                                            @endif
                                                            @if(!empty($addon['unit']))
                                                                <span class="text-[#8B7359]">/{{ $addon['unit'] }}</span>
                                                            @endif
                                                            @if(!empty($addon['subtotal']) || !empty($addon['price']))
                                                                <span class="text-[#D4A017] font-medium">+Rp {{ number_format((int) ($addon['subtotal'] ?? $addon['price']), 0, ',', '.') }}</span>
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            <span class="text-sm text-[#7A5B3A]">{{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}</span>
                                        </td>
                                        <td class="px-6 py-5 text-center text-sm text-[#7A5B3A]">
                                            {{ $b->studioLocation->name ?? '-' }}
                                            @if($b->studioRoom)
                                                <span class="block text-xs text-[#8B7359] mt-px">{{ $b->studioRoom->name }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5">
                                            @php
                                                $scheduleBadge = $b->project && $b->project->schedule
                                                    ? ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Terjadwal']
                                                    : ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Belum Dijadwalkan'];
                                            @endphp
                                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-3xl text-xs font-semibold {{ $scheduleBadge['bg'] }} {{ $scheduleBadge['text'] }}">
                                                <span class="w-2 h-2 rounded-full {{ $scheduleBadge['text'] }} bg-current animate-pulse"></span>
                                                {{ $scheduleBadge['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @php
                                                $badge = [
                                                    'WAITING_PAYMENT' => ['bg'=>'bg-amber-100', 'text'=>'text-amber-700', 'label'=>'Menunggu Pembayaran'],
                                                    'DP_PAID'         => ['bg'=>'bg-blue-100', 'text'=>'text-blue-700', 'label'=>'DP Dibayar'],
                                                    'PAID'            => ['bg'=>'bg-emerald-100', 'text'=>'text-emerald-700', 'label'=>'Lunas'],
                                                    'CANCELLED'       => ['bg'=>'bg-red-100', 'text'=>'text-red-700', 'label'=>'Dibatalkan'],
                                                ][$b->status] ?? ['bg'=>'bg-gray-100', 'text'=>'text-gray-700', 'label'=>$b->status];
                                            @endphp
                                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-3xl text-xs font-semibold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                                <span class="w-2 h-2 rounded-full {{ $badge['text'] }} bg-current"></span>
                                                {{ $badge['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            @if($b->project)
                                                <a href="{{ route('projects.show', $b->project->id) }}"
                                                   class="inline-flex items-center gap-2 text-[#D4A017] hover:text-[#E07A5F] transition-colors font-medium">
                                                    <i class="fa-solid fa-folder-open"></i>
                                                    <span>Lihat Project</span>
                                                </a>
                                            @else
                                                <span class="text-[#8B7359] text-sm italic">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @php
                                                $isAdmin = auth()->user()?->role === \App\Enums\Role::ADMIN;
                                                $canUpdatePayment = $isAdmin && $b->status === 'DP_PAID';
                                                $paymentActionLabel = [
                                                    'WAITING_PAYMENT' => 'Menunggu Pembayaran',
                                                    'DP_PAID' => 'DP Dibayar',
                                                    'PAID' => 'Lunas',
                                                    'CANCELLED' => 'Dibatalkan',
                                                ][$b->status] ?? $b->status;
                                            @endphp
                                            @if($canUpdatePayment)
                                                <div class="space-y-3">
                                                    <div class="flex items-center gap-3 justify-center">
                                                        <span class="inline-flex items-center px-4 py-2 rounded-3xl border border-blue-200 bg-blue-50 text-sm text-blue-700 font-medium">
                                                            DP Dibayar
                                                        </span>
                                                        <button type="button"
                                                                class="px-5 py-2 rounded-3xl border border-[#E1D3C5] bg-white text-xs font-semibold text-[#5C432C] hover:bg-[#FAF6F0] hover:border-[#D4A017] transition-all flex items-center gap-2"
                                                                onclick="document.getElementById('payment-action-{{ $b->id }}').classList.toggle('hidden')">
                                                            <i class="fa-solid fa-pen"></i>
                                                            Ubah
                                                        </button>
                                                    </div>
                                                    <form id="payment-action-{{ $b->id }}" method="POST" action="{{ route('admin.bookings.status', $b) }}" class="hidden flex items-center justify-center gap-3">
                                                        @csrf
                                                        <select name="status"
                                                                class="px-5 py-2 rounded-3xl border border-[#E1D3C5] bg-white/70 text-sm text-[#3F2B1B] focus:border-[#D4A017]">
                                                            <option value="PAID">Lunas</option>
                                                            <option value="CANCELLED">Dibatalkan</option>
                                                        </select>
                                                        <button class="px-6 py-2 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white text-xs font-semibold hover:brightness-110 transition-all flex items-center gap-2">
                                                            <i class="fa-solid fa-check"></i>
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
                                                    ][$b->status] ?? 'border-[#E1D3C5] bg-white/60 text-[#7A5B3A]';
                                                @endphp
                                                <span class="inline-flex items-center px-5 py-2 rounded-3xl border text-sm font-medium {{ $paymentActionStyle }}">
                                                    {{ $paymentActionLabel }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination Premium --}}
                    <div class="px-8 py-6 border-t border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white flex justify-center">
                        {{ $bookings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>