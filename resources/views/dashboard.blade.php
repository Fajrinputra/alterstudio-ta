@php
    use App\Enums\Role;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-calendar text-[#D4A017]"></i>
                    {{ now()->isoFormat('dddd, D MMMM YYYY') }}
                </p>
                <h2 class="font-display font-bold text-4xl bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#B56D3E] bg-clip-text text-transparent tracking-tighter">
                    Dashboard Alter Studio
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="bg-[#FAF6F0] py-5 sm:py-8">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8 sm:space-y-8">

            {{-- ==================== CLIENT ==================== --}}
            @if($role === Role::CLIENT)
                @php
                    $metrics = $data['metrics'] ?? [];

                    $projectStatusLabels = [
                        'DRAFT' => 'Belum Terjadwal',
                        'SCHEDULED' => 'Sesi Terjadwal',
                        'SHOOT_DONE' => 'Foto Mentah Tersedia',
                        'EDITING' => 'Proses Editing',
                        'FINAL' => 'Hasil Final Siap',
                    ];

                    $projectStatusColors = [
                        'DRAFT' => 'bg-slate-100 text-slate-700 border-slate-200',
                        'SCHEDULED' => 'bg-sky-100 text-sky-700 border-sky-200',
                        'SHOOT_DONE' => 'bg-purple-100 text-purple-700 border-purple-200',
                        'EDITING' => 'bg-orange-100 text-orange-700 border-orange-200',
                        'FINAL' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    ];
                @endphp

                <section class="grid grid-cols-1 gap-5 lg:grid-cols-[1.15fr_0.85fr] lg:gap-8">
                    <div class="relative overflow-hidden rounded-3xl border border-[#EDE0D0] bg-gradient-to-br from-[#FFF8ED] via-white to-[#F8EFE2] p-6 shadow-xl sm:p-8">
                        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-[#D4A017]/20 blur-3xl"></div>
                        <div class="absolute -bottom-16 -left-16 h-40 w-40 rounded-full bg-[#E57B5F]/20 blur-3xl"></div>

                        <div class="relative z-10">
                            <p class="mb-2 text-sm font-medium uppercase tracking-[0.18em] text-[#8B7359]">
                                Selamat datang kembali!
                            </p>

                            <h3 class="font-display text-3xl font-semibold text-[#3F2B1B] sm:text-4xl">
                                Siap abadikan momen berikutnya?
                            </h3>

                            <p class="mt-3 max-w-xl text-sm leading-relaxed text-[#7A5B3A] sm:text-base">
                                Kelola pemesanan, lihat progress foto, dan akses hasil akhir di Drive dengan mudah.
                            </p>

                            <a href="{{ route('bookings.create') }}"
                            class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E57B5F] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#D4A017]/20 transition-all hover:-translate-y-0.5 hover:shadow-xl">
                                <i class="fa-solid fa-plus"></i>
                                Pemesanan Baru
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-1">
                        <x-stat-card label="Total Pemesanan" :value="$metrics['bookings'] ?? 0" icon="receipt" />
                        <x-stat-card label="Project Final" :value="$metrics['projects_final'] ?? 0" color="emerald" icon="circle-check" />
                        @php
                            $clientRevenue = number_format($metrics['revenue_received'] ?? 0, 0, ',', '.');
                            $clientDp = number_format($metrics['revenue_dp'] ?? 0, 0, ',', '.');
                            $clientFull = number_format($metrics['revenue_full'] ?? 0, 0, ',', '.');
                        @endphp
                        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 p-5 text-emerald-700 shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-sm">
                                    <i class="fa-solid fa-wallet text-base"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-medium uppercase tracking-[0.18em] opacity-80">Pembayaran Saya</p>
                                    <p class="mt-1 text-xl font-semibold leading-tight">Rp {{ $clientRevenue }}</p>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                                <div class="rounded-xl bg-white/80 px-3 py-2">
                                    <span class="block opacity-75">DP</span>
                                    <strong>Rp {{ $clientDp }}</strong>
                                </div>
                                <div class="rounded-xl bg-white/80 px-3 py-2">
                                    <span class="block opacity-75">Lunas</span>
                                    <strong>Rp {{ $clientFull }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Recent Bookings -->
                <section class="overflow-hidden rounded-3xl border border-[#EDE0D0] bg-white shadow-xl">
                    <div class="border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white px-5 py-4 sm:px-8 sm:py-6">
                        <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] sm:text-3xl">
                            Pemesanan Terbaru
                        </h3>
                        <p class="text-[#7A5B3A]">
                            5 pemesanan terakhir Anda
                        </p>
                    </div>

                    {{-- Tampilan mobile --}}
                    <div class="space-y-4 p-4 lg:hidden">
                        @forelse($data['latest'] ?? [] as $booking)
                            @php
                                $projectStatus = $booking->project?->status;
                                $projectLabel = $projectStatusLabels[$projectStatus] ?? '-';
                                $projectColor = $projectStatusColors[$projectStatus] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                            @endphp

                            <article class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] uppercase tracking-wide text-[#8B7359]">
                                            Pemesanan {{ $loop->iteration }}
                                        </p>
                                        <h4 class="mt-1 text-sm font-semibold text-[#3F2B1B]">
                                            {{ $booking->package->name ?? '-' }}
                                        </h4>
                                    </div>

                                    <x-status-badge :status="$booking->status" :confirmed-at="$booking->confirmed_at" />
                                </div>

                                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-2xl bg-white px-3 py-2.5">
                                        <dt class="text-[11px] uppercase tracking-wide text-[#8B7359]">
                                            Tanggal
                                        </dt>
                                        <dd class="mt-1 font-medium text-[#3F2B1B]">
                                            {{ $booking->booking_date->translatedFormat('d M Y') }}
                                        </dd>
                                    </div>

                                    <div class="rounded-2xl bg-white px-3 py-2.5">
                                        <dt class="text-[11px] uppercase tracking-wide text-[#8B7359]">
                                            Project
                                        </dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center justify-center rounded-full border px-3 py-1 text-xs font-semibold {{ $projectColor }}">
                                                {{ $projectLabel }}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>
                            </article>
                        @empty
                            <div class="px-4 py-12 text-center text-[#7A5B3A]">
                                <i class="fa-solid fa-folder-open mb-3 block text-5xl opacity-40"></i>
                                Belum ada pemesanan
                            </div>
                        @endforelse
                    </div>

                    {{-- Tampilan desktop --}}
                    <div class="hidden overflow-x-auto lg:block">
                        <table class="min-w-full table-fixed text-sm">
                            <thead>
                                <tr class="bg-[#FAF6F0] text-[#8B7359]">
                                    <th class="px-4 py-4 text-center font-bold">No</th>
                                    <th class="px-4 py-4 text-center font-bold">Tanggal</th>
                                    <th class="px-4 py-4 text-center font-bold">Paket</th>
                                    <th class="px-4 py-4 text-center font-bold">Status</th>
                                    <th class="px-4 py-4 text-center font-bold">Project</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-[#EDE0D0]">
                                @forelse($data['latest'] ?? [] as $booking)
                                    @php
                                        $projectStatus = $booking->project?->status;
                                        $projectLabel = $projectStatusLabels[$projectStatus] ?? '-';
                                        $projectColor = $projectStatusColors[$projectStatus] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                    @endphp

                                    <tr class="text-[#3F2B1B] transition-all hover:bg-[#FAF6F0]">
                                        <td class="px-4 py-4 text-center font-medium">
                                            {{ $loop->iteration }}
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            {{ $booking->booking_date->translatedFormat('d M Y') }}
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            {{ $booking->package->name ?? '-' }}
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            <x-status-badge :status="$booking->status" :confirmed-at="$booking->confirmed_at" />
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center justify-center rounded-full border px-3 py-1 text-xs font-semibold {{ $projectColor }}">
                                                {{ $projectLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-8 py-16 text-center text-[#7A5B3A]">
                                            <i class="fa-solid fa-folder-open mb-3 block text-5xl opacity-40"></i>
                                            Belum ada pemesanan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

            {{-- ==================== ADMIN / MANAGER / OWNER ==================== --}}
            @elseif($role === Role::ADMIN || $role === Role::MANAGER || $role === Role::OWNER)
                <section>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6">
                        <x-stat-card label="Total Pemesanan" :value="$data['metrics']['bookings'] ?? 0" icon="receipt" />
                        <x-stat-card label="Data Pengajuan" :value="$data['metrics']['submitted'] ?? 0" color="amber" icon="clipboard-list" />
                        <x-stat-card label="Data Pembayaran" :value="$data['metrics']['waiting_payment'] ?? 0" color="blue" icon="credit-card" />
                        <x-stat-card label="Project Final" :value="$data['metrics']['projects_final'] ?? 0" color="emerald" icon="circle-check" />
                        <x-stat-card label="Belum Terjadwal" :value="$data['metrics']['unscheduled'] ?? 0" color="red" icon="calendar-xmark" />
                        @if($role === Role::MANAGER || $role === Role::OWNER)
                            @php
                                $revenueReceived = number_format($data['metrics']['revenue_received'] ?? 0, 0, ',', '.');
                                $revenueDp = number_format($data['metrics']['revenue_dp'] ?? 0, 0, ',', '.');
                                $revenueFull = number_format($data['metrics']['revenue_full'] ?? 0, 0, ',', '.');
                            @endphp
                            <div class="relative min-w-0 overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-500/10 to-teal-600/10 p-5 text-emerald-700 shadow-lg sm:col-span-2">
                                <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full bg-emerald-500 opacity-10 blur-2xl"></div>
                                <div class="relative z-10 flex min-w-0 flex-col gap-4 sm:flex-row sm:items-center">
                                    <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-sm">
                                        <i class="fa-solid fa-wallet text-base"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium uppercase tracking-[0.18em] opacity-80">Pendapatan Diterima</p>
                                        <p class="mt-1 text-[clamp(1.1rem,1.5vw,1.6rem)] font-semibold leading-tight tabular-nums">Rp {{ $revenueReceived }}</p>
                                    </div>
                                    <div class="grid w-full grid-cols-2 gap-2 text-xs sm:w-auto sm:min-w-[260px]">
                                        <div class="rounded-xl bg-white/80 px-3 py-2">
                                            <span class="block opacity-75">DP diterima</span>
                                            <strong>Rp {{ $revenueDp }}</strong>
                                        </div>
                                        <div class="rounded-xl bg-white/80 px-3 py-2">
                                            <span class="block opacity-75">Lunas/pelunasan</span>
                                            <strong>Rp {{ $revenueFull }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:gap-8">
                    <!-- Status Pemesanan -->
                    <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl p-5 sm:p-8">
                        <h3 class="mb-4 flex items-center gap-3 font-display text-xl font-semibold text-[#3F2B1B] sm:mb-6 sm:text-2xl">
                            <i class="fa-solid fa-chart-pie text-[#D4A017]"></i>
                            Status Pemesanan
                        </h3>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
                            @foreach(['WAITING_PAYMENT','DP_PAID','PAID','CANCELLED'] as $status)
                                @php
                                    $labels = [
                                        'WAITING_PAYMENT' => 'Diajukan / Dikonfirmasi',
                                        'DP_PAID' => 'DP Dibayar',
                                        'PAID' => 'Lunas',
                                        'CANCELLED' => 'Dibatalkan',
                                    ];
                                @endphp
                                <div class="flex items-center justify-between rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] px-4 py-3 transition-colors hover:bg-white sm:px-6 sm:py-5">
                                    <span class="text-[#5C432C]">{{ $labels[$status] ?? $status }}</span>
                                    <span class="font-semibold text-lg bg-white px-5 py-1 rounded-3xl shadow-sm">{{ $data['statusCounts'][$status] ?? 0 }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @if($role === Role::ADMIN)
                        <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl p-5 sm:p-8">
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
                                            <span class="text-xs text-[#8B7359] ml-3">({{ $item->start_at->translatedFormat('d M H:i') }} - {{ $item->end_at->translatedFormat('H:i') }})</span>
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

                    @elseif($role === Role::MANAGER || $role === Role::OWNER)
                        <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl p-5 sm:p-8">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-display font-semibold text-[#3F2B1B] flex items-center gap-3">
                                    <i class="fa-solid fa-users-gear text-[#D4A017]"></i>
                                    Total Role Aktif
                                </h3>
                                <span class="text-xs px-6 py-2 bg-[#FAF6F0] rounded-3xl text-[#5C432C] font-medium">Aktif</span>
                            </div>

                            @php
                                $roleLabels = [
                                    Role::OWNER->value => 'Owner',
                                    Role::ADMIN->value => 'Admin',
                                    Role::MANAGER->value => 'Manajer',
                                    Role::CLIENT->value => 'Klien',
                                    Role::PHOTOGRAPHER->value => 'Fotografer',
                                    Role::EDITOR->value => 'Editor',
                                ];
                                $roleIcons = [
                                    Role::OWNER->value => 'fa-crown',
                                    Role::ADMIN->value => 'fa-user-shield',
                                    Role::MANAGER->value => 'fa-briefcase',
                                    Role::CLIENT->value => 'fa-user',
                                    Role::PHOTOGRAPHER->value => 'fa-camera',
                                    Role::EDITOR->value => 'fa-pen-ruler',
                                ];
                            @endphp

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($roleLabels as $roleValue => $roleLabel)
                                    <div class="flex items-center justify-between rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] px-4 py-3">
                                        <div class="flex items-center gap-2.5 text-[#5C432C]">
                                            <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-white text-[#D4A017] shadow-sm">
                                                <i class="fa-solid {{ $roleIcons[$roleValue] }}"></i>
                                            </span>
                                            <span class="text-sm font-medium">{{ $roleLabel }}</span>
                                        </div>
                                        <span class="rounded-2xl bg-white px-3 py-1 text-sm font-semibold text-[#3F2B1B] shadow-sm">
                                            {{ $data['roleCounts'][$roleValue] ?? 0 }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>

            {{-- ==================== PHOTOGRAPHER ONLY ==================== --}}
            @elseif($role === Role::PHOTOGRAPHER)
                <section>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        <x-stat-card label="Project Final" :value="$data['completed'] ?? 0" color="emerald" />
                        <x-stat-card label="Akan Datang" :value="$data['upcoming']->count() ?? 0" color="blue" />
                    </div>
                    <div class="bg-white rounded-2xl border border-[#EDE0D0] shadow-lg overflow-hidden">
                        <div class="px-5 py-4 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                            <h3 class="font-display text-2xl font-semibold text-[#3F2B1B]">Jadwal Mendatang</h3>
                        </div>
                        <div class="divide-y divide-[#EDE0D0]">
                            @forelse($data['upcoming'] ?? [] as $item)
                                <div class="px-5 py-4 hover:bg-[#FAF6F0] transition-all">
                                    <div class="flex gap-3">
                                        <div class="w-9 h-9 flex-shrink-0 bg-gradient-to-br from-[#D4A017] to-[#E07A5F] rounded-xl flex items-center justify-center text-white">
                                            <i class="fa-solid fa-camera"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-[#3F2B1B]">{{ $item->start_at->translatedFormat('d M H:i') }} - {{ $item->end_at->translatedFormat('H:i') }}</p>
                                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-[#7A5B3A]">
                                                <span>Lokasi: {{ $item->booking->location ?? '-' }}</span>
                                                <span>Klien: {{ $item->booking->client->name ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-10 text-center text-[#8B7359]">
                                    <i class="fa-solid fa-calendar-check text-4xl mb-3 opacity-30"></i>
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
                        <x-stat-card label="Final Ditandai" :value="$data['finalized'] ?? 0" color="emerald" />
                        <x-stat-card label="Antrian Edit" :value="$data['queue']->count() ?? 0" color="amber" />
                    </div>
                    <div class="bg-white rounded-2xl border border-[#EDE0D0] shadow-lg overflow-hidden">
                        <div class="px-5 py-4 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                            <h3 class="font-display text-2xl font-semibold text-[#3F2B1B]">Antrian Tugas</h3>
                        </div>
                        <div class="divide-y divide-[#EDE0D0]">
                            @forelse($data['queue'] ?? [] as $item)
                                <div class="px-5 py-4 hover:bg-[#FAF6F0] transition-all">
                                    <div class="flex gap-3">
                                        <div class="w-9 h-9 flex-shrink-0 bg-gradient-to-br from-[#6B4A2D] to-[#4C351F] rounded-xl flex items-center justify-center text-white">
                                            <i class="fa-solid fa-pen-ruler"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-semibold text-[#3F2B1B]">{{ $item->booking->location ?? 'N/A' }}</p>
                                            <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-[#7A5B3A]">
                                                <span>Mulai: {{ optional($item->start_at)?->translatedFormat('d M H:i') ?? '-' }}</span>
                                                <span>Klien: {{ $item->booking->client->name ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-10 text-center text-[#8B7359]">
                                    <i class="fa-solid fa-face-smile text-4xl mb-3 opacity-30"></i>
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
