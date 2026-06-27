<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="flex items-center gap-2 text-xs font-medium uppercase tracking-[1.5px] text-[#8B7359]">
                    <i class="fa-solid fa-receipt text-[#D4A017]"></i>
                    Monitoring Pemesanan
                </p>
                <h2 class="mt-1 font-display text-3xl font-semibold tracking-tight text-[#3F2B1B] md:text-4xl">
                    Daftar <span class="bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text font-medium text-transparent">Pemesanan Masuk</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="bg-[#FAF6F0] py-5">
        <div class="mx-auto max-w-[1180px] space-y-5 px-0 sm:px-2 lg:px-4">
            <div class="relative group">
                <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-[#D4A017]/10 via-[#E07A5F]/10 to-[#D4A017]/10 blur-xl opacity-60 transition-all group-hover:opacity-80"></div>
                <div class="relative rounded-2xl border border-[#EDE0D0] bg-white/80 p-4 shadow-md backdrop-blur-2xl sm:p-5">
                    <form method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-[#7A5B3A]">
                                    <i class="fa-solid fa-credit-card text-[#D4A017]"></i>
                                    Status Pemesanan
                                </label>
                                <select name="status" class="w-full rounded-2xl border border-[#E1D3C5] bg-white/70 px-4 py-2.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                    <option value="">Semua Status</option>
                                    @php
                                        $statusLabels = [
                                            'SUBMITTED' => 'Diajukan',
                                            'WAITING_PAYMENT' => 'Dikonfirmasi',
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

                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-[#7A5B3A]">
                                    <i class="fa-solid fa-calendar text-[#D4A017]"></i>
                                    Status Jadwal
                                </label>
                                <select name="schedule_status" class="w-full rounded-2xl border border-[#E1D3C5] bg-white/70 px-4 py-2.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                    <option value="">Semua</option>
                                    <option value="scheduled" @selected(request('schedule_status') === 'scheduled')>Sudah dijadwalkan</option>
                                    <option value="unscheduled" @selected(request('schedule_status') === 'unscheduled')>Belum dijadwalkan</option>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-[#7A5B3A]">
                                    <i class="fa-solid fa-box text-[#D4A017]"></i>
                                    Paket
                                </label>
                                <select name="package_id" class="w-full rounded-2xl border border-[#E1D3C5] bg-white/70 px-4 py-2.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                    <option value="">Semua Paket</option>
                                    @foreach($packages ?? [] as $p)
                                        <option value="{{ $p->id }}" @selected(request('package_id') == $p->id)>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-[#7A5B3A]">
                                    <i class="fa-solid fa-calendar-range text-[#D4A017]"></i>
                                    Rentang Tanggal
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-2xl border border-[#E1D3C5] bg-white/70 px-3 py-2.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-2xl border border-[#E1D3C5] bg-white/70 px-3 py-2.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-4">
                            <div class="space-y-2 md:col-span-3">
                                <label class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-[#7A5B3A]">
                                    <i class="fa-solid fa-magnifying-glass text-[#D4A017]"></i>
                                    Cari (ID/Klien/Paket)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                        <i class="fa-solid fa-search"></i>
                                    </span>
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Misal: 5 atau 'Client Demo'" class="w-full rounded-2xl border border-[#E1D3C5] bg-white/70 py-2.5 pl-11 pr-4 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                </div>
                            </div>
                            <div>
                                <button class="flex h-[44px] w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-sm font-semibold text-white shadow-md shadow-[#D4A017]/25 transition-all hover:-translate-y-0.5 hover:shadow-lg active:scale-[0.98]">
                                    <i class="fa-solid fa-filter"></i>
                                    Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="relative group">
                <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-[#D4A017]/5 via-[#E07A5F]/5 to-[#FAF6F0] blur-2xl"></div>
                <div class="relative overflow-hidden rounded-2xl border border-[#EDE0D0] bg-white/80 shadow-lg backdrop-blur-2xl">
                    @if($bookings->isEmpty())
                        <div class="px-6 py-16 text-center text-[#8B7359]">
                            <i class="fa-solid fa-inbox mb-4 text-4xl text-[#D4A017]/60"></i>
                            <p class="text-lg font-medium text-[#3F2B1B]">Belum ada pemesanan yang sesuai filter.</p>
                        </div>
                    @else
                        <div class="space-y-3 p-3 lg:hidden">
                            @foreach($bookings as $b)
                                @php
                                    $displayStatusKey = $b->isSubmitted()
                                        ? 'SUBMITTED'
                                        : ($b->isConfirmedAwaitingPayment() ? 'WAITING_PAYMENT' : $b->status);
                                    $badge = [
                                        'SUBMITTED' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Diajukan'],
                                        'WAITING_PAYMENT' => ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'label' => 'Dikonfirmasi'],
                                        'DP_PAID' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'DP Dibayar'],
                                        'PAID' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Lunas'],
                                        'CANCELLED' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Dibatalkan'],
                                    ][$displayStatusKey] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => $displayStatusKey];
                                    $scheduleBadge = $b->project && $b->project->schedule
                                        ? ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Terjadwal']
                                        : ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Belum Dijadwalkan'];
                                    $canManageStatus = auth()->user()?->isRole(\App\Enums\Role::ADMIN, \App\Enums\Role::MANAGER) === true;
                                    $availableStatusTransitions = $canManageStatus
                                        ? match ($b->status) {
                                            'WAITING_PAYMENT' => $b->confirmed_at
                                                ? ['CANCELLED' => 'Batalkan Pemesanan']
                                                : ['WAITING_PAYMENT' => 'Konfirmasi Pemesanan', 'CANCELLED' => 'Tolak Pemesanan'],
                                            'DP_PAID' => ['PAID' => 'Lunas'],
                                            default => [],
                                        }
                                        : [];
                                    $canUpdatePayment = !empty($availableStatusTransitions);
                                    $addonCollection = collect($b->selected_addons);
                                    $addonPreview = $addonCollection->take(2);
                                    $remainingAddonCount = max(0, $addonCollection->count() - $addonPreview->count());
                                @endphp

                                <article x-data="{ statusActionOpen: false }" class="rounded-2xl border border-[#EDE0D0] bg-white px-4 py-3 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-mono text-sm font-semibold text-[#D4A017]">#{{ $b->id }}</p>
                                            <h3 class="mt-1 text-base font-semibold text-[#3F2B1B]">{{ $b->client->name ?? '-' }}</h3>
                                            <p class="text-sm text-[#8B7359]">{{ $b->package->name ?? '-' }}</p>
                                        </div>
                                        @if($canUpdatePayment)
                                            <button type="button" @click="statusActionOpen = true" class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-[11px] font-semibold transition-all hover:-translate-y-0.5 hover:ring-2 hover:ring-[#D4A017]/30 {{ $badge['bg'] }} {{ $badge['text'] }}">
                                                <span class="h-2 w-2 rounded-full bg-current"></span>
                                                {{ $badge['label'] }}
                                            </button>
                                        @else
                                            <span class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-[11px] font-semibold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                                <span class="h-2 w-2 rounded-full bg-current"></span>
                                                {{ $badge['label'] }}
                                            </span>
                                        @endif
                                    </div>

                                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                        <div class="rounded-2xl bg-[#FAF6F0] px-3 py-2.5">
                                            <dt class="text-[11px] uppercase tracking-wide text-[#8B7359]">Tanggal</dt>
                                            <dd class="mt-1 font-medium text-[#3F2B1B]">{{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}</dd>
                                        </div>
                                        <div class="rounded-2xl bg-[#FAF6F0] px-3 py-2.5">
                                            <dt class="text-[11px] uppercase tracking-wide text-[#8B7359]">Studio</dt>
                                            <dd class="mt-1 font-medium text-[#3F2B1B]">{{ $b->studioLocation->name ?? '-' }}</dd>
                                        </div>
                                    </dl>

                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-3xl px-3 py-1.5 text-[11px] font-semibold {{ $scheduleBadge['bg'] }} {{ $scheduleBadge['text'] }}">
                                            <span class="h-2 w-2 rounded-full bg-current"></span>
                                            {{ $scheduleBadge['label'] }}
                                        </span>
                                        @if($b->project)
                                            <a href="{{ route('projects.show', $b->project->id) }}" class="inline-flex items-center gap-2 rounded-3xl border border-[#EDE0D0] px-3 py-1.5 text-[11px] font-medium text-[#7A5B3A] transition hover:border-[#D4A017] hover:text-[#D4A017]">
                                                <i class="fa-solid fa-folder-open"></i>
                                                Lihat Project
                                            </a>
                                        @endif
                                    </div>

                                    @if($canUpdatePayment)
                                        <div x-cloak x-show="statusActionOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
                                            <button type="button" class="absolute inset-0 bg-[#3F2B1B]/40 backdrop-blur-sm" @click="statusActionOpen = false" aria-label="Tutup modal aksi status"></button>
                                            <div x-show="statusActionOpen" x-transition class="relative w-full max-w-sm rounded-3xl border border-[#EDE0D0] bg-white p-6 shadow-2xl shadow-[#3F2B1B]/20">
                                                <button type="button" @click="statusActionOpen = false" class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-2xl text-[#8B7359] transition hover:bg-[#FAF6F0] hover:text-[#3F2B1B]">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                                <div class="pr-10">
                                                    <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#8B7359]">
                                                        <i class="fa-solid fa-clipboard-check text-[#D4A017]"></i>
                                                        Aksi Pemesanan
                                                    </p>
                                                    <h3 class="mt-2 text-xl font-semibold text-[#3F2B1B]">Pemesanan #{{ $b->id }}</h3>
                                                    <p class="mt-1 text-sm text-[#7A5B3A]">{{ $b->client->name ?? '-' }} - {{ $b->package->name ?? '-' }}</p>
                                                </div>
                                                <div class="mt-6 grid gap-3">
                                                @foreach($availableStatusTransitions as $statusValue => $statusLabel)
                                                    @php
                                                        $isDestructiveAction = $statusValue === 'CANCELLED';
                                                        $buttonText = $statusLabel;
                                                        $buttonIcon = match ($statusValue) {
                                                            'WAITING_PAYMENT', 'PAID' => 'fa-solid fa-check',
                                                            'CANCELLED' => 'fa-solid fa-xmark',
                                                            default => 'fa-solid fa-arrow-right',
                                                        };
                                                    @endphp
                                                    <form method="POST" action="{{ route('admin.bookings.status', $b) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="{{ $statusValue }}">
                                                        <button class="inline-flex min-h-[48px] w-full items-center justify-center gap-3 rounded-2xl px-5 py-3 text-sm font-semibold transition-all hover:-translate-y-0.5 active:scale-[0.98]
                                                            {{ $isDestructiveAction
                                                                ? 'border border-red-200 bg-red-50 text-red-600 hover:bg-red-100'
                                                                : 'bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white shadow-lg shadow-[#D4A017]/25 hover:shadow-xl' }}">
                                                            <i class="{{ $buttonIcon }}"></i>
                                                            <span>{{ $buttonText }}</span>
                                                        </button>
                                                    </form>
                                                @endforeach
                                                    <button type="button" @click="statusActionOpen = false"
                                                            class="inline-flex min-h-[48px] w-full items-center justify-center gap-3 rounded-2xl border border-[#EDE0D0] bg-white px-5 py-3 text-sm font-semibold text-[#7A5B3A] transition-all hover:-translate-y-0.5 hover:border-[#D4A017] hover:text-[#3F2B1B] active:scale-[0.98]">
                                                        <i class="fa-solid fa-arrow-left"></i>
                                                        <span>Kembali</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>

                        <div class="hidden overflow-x-auto lg:block">
                            <table class="min-w-full table-fixed">
                                <colgroup>
                                    <col class="w-[6%]">
                                    <col class="w-[18%]">
                                    <col class="w-[22%]">
                                    <col class="w-[10%]">
                                    <col class="w-[13%]">
                                    <col class="w-[11%]">
                                    <col class="w-[13%]">
                                    <col class="w-[7%]">
                                </colgroup>
                                <thead>
                                    <tr class="border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] via-white to-[#FAF6F0]">
                                        <th class="px-3 py-4 text-center text-xs font-semibold uppercase tracking-widest text-[#3F2B1B]">ID</th>
                                        <th class="px-3 py-4 text-center text-xs font-semibold uppercase tracking-widest text-[#3F2B1B]">Klien</th>
                                        <th class="px-3 py-4 text-center text-xs font-semibold uppercase tracking-widest text-[#3F2B1B]">Paket</th>
                                        <th class="px-3 py-4 text-center text-xs font-semibold uppercase tracking-widest text-[#3F2B1B]">Tanggal</th>
                                        <th class="px-3 py-4 text-center text-xs font-semibold uppercase tracking-widest text-[#3F2B1B]">Studio</th>
                                        <th class="px-3 py-4 text-center text-xs font-semibold uppercase tracking-widest text-[#3F2B1B]">Jadwal</th>
                                        <th class="px-3 py-4 text-center text-xs font-semibold uppercase tracking-widest text-[#3F2B1B]">Status &amp; Aksi</th>
                                        <th class="px-3 py-4 text-center text-xs font-semibold uppercase tracking-widest text-[#3F2B1B]">Project</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EDE0D0]">
                                    @foreach($bookings as $b)
                                        @php
                                            $displayStatusKey = $b->isSubmitted()
                                                ? 'SUBMITTED'
                                                : ($b->isConfirmedAwaitingPayment() ? 'WAITING_PAYMENT' : $b->status);
                                            $badge = [
                                                'SUBMITTED' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Diajukan'],
                                                'WAITING_PAYMENT' => ['bg' => 'bg-sky-100', 'text' => 'text-sky-700', 'label' => 'Dikonfirmasi'],
                                                'DP_PAID' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'DP Dibayar'],
                                                'PAID' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Lunas'],
                                                'CANCELLED' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Dibatalkan'],
                                            ][$displayStatusKey] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'label' => $displayStatusKey];
                                            $scheduleBadge = $b->project && $b->project->schedule
                                                ? ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'label' => 'Terjadwal']
                                                : ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'label' => 'Belum Dijadwalkan'];
                                            $canManageStatus = auth()->user()?->isRole(\App\Enums\Role::ADMIN, \App\Enums\Role::MANAGER) === true;
                                            $availableStatusTransitions = $canManageStatus
                                                ? match ($b->status) {
                                                    'WAITING_PAYMENT' => $b->confirmed_at
                                                        ? ['CANCELLED' => 'Batalkan Pemesanan']
                                                        : ['WAITING_PAYMENT' => 'Konfirmasi Pemesanan', 'CANCELLED' => 'Tolak Pemesanan'],
                                                    'DP_PAID' => ['PAID' => 'Lunas'],
                                                    default => [],
                                                }
                                                : [];
                                            $canUpdatePayment = !empty($availableStatusTransitions);
                                            $addonCollection = collect($b->selected_addons);
                                            $addonPreview = $addonCollection->take(2);
                                            $remainingAddonCount = max(0, $addonCollection->count() - $addonPreview->count());
                                        @endphp
                                        <tr x-data="{ statusActionOpen: false }" class="transition-all duration-200 hover:bg-[#FAF6F0]">
                                            <td class="px-3 py-4 align-middle text-center">
                                                <span class="font-mono text-sm font-semibold text-[#D4A017]">#{{ $b->id }}</span>
                                            </td>
                                            <td class="px-3 py-4 align-middle text-left">
                                                <p class="truncate text-sm font-semibold text-[#3F2B1B]">{{ $b->client->name ?? '-' }}</p>
                                            </td>
                                            <td class="px-3 py-4 align-middle">
                                                <p class="text-sm font-medium text-[#3F2B1B]">{{ $b->package->name ?? '-' }}</p>
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center">
                                                <span class="whitespace-nowrap text-sm font-medium text-[#7A5B3A]">
                                                    {{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center text-sm text-[#7A5B3A]">
                                                <div>{{ $b->studioLocation->name ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center">
                                                <span class="inline-flex min-h-[34px] items-center gap-2 rounded-3xl px-3 py-1.5 text-[11px] font-semibold {{ $scheduleBadge['bg'] }} {{ $scheduleBadge['text'] }}">
                                                    <span class="h-2 w-2 rounded-full bg-current"></span>
                                                    {{ $scheduleBadge['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center">
                                                <div class="space-y-2">
                                                    @if($canUpdatePayment)
                                                        <button type="button" @click="statusActionOpen = true" class="inline-flex min-h-[34px] items-center gap-2 rounded-3xl px-3 py-1.5 text-[11px] font-semibold transition-all hover:-translate-y-0.5 hover:ring-2 hover:ring-[#D4A017]/30 {{ $badge['bg'] }} {{ $badge['text'] }}">
                                                            <span class="h-2 w-2 rounded-full bg-current"></span>
                                                            {{ $badge['label'] }}
                                                        </button>
                                                        <div x-cloak x-show="statusActionOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
                                                            <button type="button" class="absolute inset-0 bg-[#3F2B1B]/40 backdrop-blur-sm" @click="statusActionOpen = false" aria-label="Tutup modal aksi status"></button>
                                                            <div x-show="statusActionOpen" x-transition class="relative w-full max-w-sm rounded-3xl border border-[#EDE0D0] bg-white p-6 text-left shadow-2xl shadow-[#3F2B1B]/20">
                                                                <button type="button" @click="statusActionOpen = false" class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-2xl text-[#8B7359] transition hover:bg-[#FAF6F0] hover:text-[#3F2B1B]">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                                <div class="pr-10">
                                                                    <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-[#8B7359]">
                                                                        <i class="fa-solid fa-clipboard-check text-[#D4A017]"></i>
                                                                        Aksi Pemesanan
                                                                    </p>
                                                                    <h3 class="mt-2 text-xl font-semibold text-[#3F2B1B]">Pemesanan #{{ $b->id }}</h3>
                                                                    <p class="mt-1 text-sm text-[#7A5B3A]">{{ $b->client->name ?? '-' }} - {{ $b->package->name ?? '-' }}</p>
                                                                </div>
                                                                <div class="mt-6 grid gap-3">
                                                                @foreach($availableStatusTransitions as $statusValue => $statusLabel)
                                                                    @php
                                                                        $isDestructiveAction = $statusValue === 'CANCELLED';
                                                                        $buttonText = $statusLabel;
                                                                        $buttonIcon = match ($statusValue) {
                                                                            'WAITING_PAYMENT', 'PAID' => 'fa-solid fa-check',
                                                                            'CANCELLED' => 'fa-solid fa-xmark',
                                                                            default => 'fa-solid fa-arrow-right',
                                                                        };
                                                                    @endphp
                                                                    <form method="POST" action="{{ route('admin.bookings.status', $b) }}">
                                                                        @csrf
                                                                        <input type="hidden" name="status" value="{{ $statusValue }}">
                                                                        <button class="inline-flex min-h-[48px] w-full items-center justify-center gap-3 rounded-2xl px-5 py-3 text-sm font-semibold transition-all hover:-translate-y-0.5 active:scale-[0.98]
                                                                            {{ $isDestructiveAction
                                                                                ? 'border border-red-200 bg-red-50 text-red-600 hover:bg-red-100'
                                                                                : 'bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white shadow-md shadow-[#D4A017]/25 hover:shadow-lg' }}">
                                                                            <i class="{{ $buttonIcon }}"></i>
                                                                            <span>{{ $buttonText }}</span>
                                                                        </button>
                                                                    </form>
                                                                @endforeach
                                                                    <button type="button" @click="statusActionOpen = false"
                                                                            class="inline-flex min-h-[48px] w-full items-center justify-center gap-3 rounded-2xl border border-[#EDE0D0] bg-white px-5 py-3 text-sm font-semibold text-[#7A5B3A] transition-all hover:-translate-y-0.5 hover:border-[#D4A017] hover:text-[#3F2B1B] active:scale-[0.98]">
                                                                        <i class="fa-solid fa-arrow-left"></i>
                                                                        <span>Kembali</span>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="inline-flex min-h-[34px] items-center gap-2 rounded-3xl px-3 py-1.5 text-[11px] font-semibold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                                            <span class="h-2 w-2 rounded-full bg-current"></span>
                                                            {{ $badge['label'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center">
                                                @if($b->project)
                                                    <a href="{{ route('projects.show', $b->project->id) }}" class="inline-flex items-center gap-2 rounded-3xl border border-[#EDE0D0] px-3 py-1.5 text-[11px] font-medium text-[#7A5B3A] transition hover:border-[#D4A017] hover:text-[#D4A017]">
                                                        <i class="fa-solid fa-folder-open"></i>
                                                        Lihat
                                                    </a>
                                                @else
                                                    <span class="text-xs italic text-[#8B7359]">Belum ada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <div class="flex justify-center border-t border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white px-8 py-6">
                        {{ $bookings->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
