<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="flex items-center gap-2 text-sm font-medium uppercase tracking-[1.5px] text-[#8B7359]">
                    <i class="fa-solid fa-receipt text-[#D4A017]"></i>
                    Monitoring Pemesanan
                </p>
                <h2 class="mt-1 font-display text-4xl font-semibold tracking-[-1px] text-[#3F2B1B] md:text-5xl">
                    Daftar <span class="bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text font-medium text-transparent">Pemesanan Masuk</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="bg-[#FAF6F0] py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <div class="relative group">
                <div class="absolute inset-0 rounded-3xl bg-gradient-to-r from-[#D4A017]/10 via-[#E07A5F]/10 to-[#D4A017]/10 blur-2xl opacity-70 transition-all group-hover:opacity-90"></div>
                <div class="relative rounded-3xl border border-[#EDE0D0] bg-white/80 p-8 shadow-xl backdrop-blur-2xl">
                    <form method="GET" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                            <div class="space-y-2">
                                <label class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-[#7A5B3A]">
                                    <i class="fa-solid fa-credit-card text-[#D4A017]"></i>
                                    Status Pemesanan
                                </label>
                                <select name="status" class="w-full rounded-3xl border border-[#E1D3C5] bg-white/70 px-5 py-3.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
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
                                <select name="schedule_status" class="w-full rounded-3xl border border-[#E1D3C5] bg-white/70 px-5 py-3.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
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
                                <select name="package_id" class="w-full rounded-3xl border border-[#E1D3C5] bg-white/70 px-5 py-3.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
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
                                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-3xl border border-[#E1D3C5] bg-white/70 px-5 py-3.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-3xl border border-[#E1D3C5] bg-white/70 px-5 py-3.5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 items-end gap-6 md:grid-cols-4">
                            <div class="space-y-2 md:col-span-3">
                                <label class="flex items-center gap-2 text-xs font-medium uppercase tracking-widest text-[#7A5B3A]">
                                    <i class="fa-solid fa-magnifying-glass text-[#D4A017]"></i>
                                    Cari (ID/Klien/Paket)
                                </label>
                                <div class="relative">
                                    <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                        <i class="fa-solid fa-search"></i>
                                    </span>
                                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Misal: 5 atau 'Client Demo'" class="w-full rounded-3xl border border-[#E1D3C5] bg-white/70 py-3.5 pl-12 pr-5 text-sm text-[#3F2B1B] transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                </div>
                            </div>
                            <div>
                                <button class="flex h-[52px] w-full items-center justify-center gap-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-base font-semibold text-white shadow-xl shadow-[#D4A017]/30 transition-all hover:-translate-y-0.5 hover:shadow-2xl active:scale-[0.98]">
                                    <i class="fa-solid fa-filter"></i>
                                    Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="relative group">
                <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-[#D4A017]/5 via-[#E07A5F]/5 to-[#FAF6F0] blur-3xl"></div>
                <div class="relative overflow-hidden rounded-3xl border border-[#EDE0D0] bg-white/80 shadow-2xl backdrop-blur-2xl">
                    @if($bookings->isEmpty())
                        <div class="px-6 py-16 text-center text-[#8B7359]">
                            <i class="fa-solid fa-inbox mb-4 text-4xl text-[#D4A017]/60"></i>
                            <p class="text-lg font-medium text-[#3F2B1B]">Belum ada pemesanan yang sesuai filter.</p>
                        </div>
                    @else
                        <div class="space-y-4 p-4 lg:hidden">
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
                                    $isAdmin = auth()->user()?->role === \App\Enums\Role::ADMIN;
                                    $availableStatusTransitions = match ($b->status) {
                                        'WAITING_PAYMENT' => $b->confirmed_at
                                            ? ['CANCELLED' => 'Batalkan Pemesanan']
                                            : ['WAITING_PAYMENT' => 'Konfirmasi Pemesanan', 'CANCELLED' => 'Tolak Pemesanan'],
                                        'DP_PAID' => ['PAID' => 'Lunas', 'CANCELLED' => 'Batalkan Pemesanan'],
                                        default => [],
                                    };
                                    $canUpdatePayment = $isAdmin && !empty($availableStatusTransitions);
                                    $addonCollection = collect($b->selected_addons);
                                    $addonPreview = $addonCollection->take(2);
                                    $remainingAddonCount = max(0, $addonCollection->count() - $addonPreview->count());
                                @endphp

                                <article class="rounded-3xl border border-[#EDE0D0] bg-white px-4 py-4 shadow-sm">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-mono text-sm font-semibold text-[#D4A017]">#{{ $b->id }}</p>
                                            <h3 class="mt-1 text-base font-semibold text-[#3F2B1B]">{{ $b->client->name ?? '-' }}</h3>
                                            <p class="text-sm text-[#8B7359]">{{ $b->package->name ?? '-' }}</p>
                                        </div>
                                        <span class="inline-flex items-center gap-2 rounded-3xl px-3 py-1.5 text-[11px] font-semibold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                            <span class="h-2 w-2 rounded-full bg-current"></span>
                                            {{ $badge['label'] }}
                                        </span>
                                    </div>

                                    @if($addonCollection->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            @foreach($addonPreview as $addon)
                                                <span class="inline-flex items-center gap-1 rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] px-2.5 py-1 text-[11px] text-[#7A5B3A]">
                                                    {{ $addon['label'] ?? '-' }}
                                                    @if(!empty($addon['quantity']))
                                                        <span class="font-mono">x{{ (int) $addon['quantity'] }}</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if($remainingAddonCount > 0)
                                                <details class="text-[11px] text-[#7A5B3A]">
                                                    <summary class="cursor-pointer list-none rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] px-2.5 py-1">+{{ $remainingAddonCount }} add-on</summary>
                                                    <div class="mt-2 space-y-1 rounded-2xl border border-[#EDE0D0] bg-white p-2.5">
                                                        @foreach($addonCollection->slice(2) as $addon)
                                                            <div class="flex items-center justify-between gap-3">
                                                                <span>{{ $addon['label'] ?? '-' }}</span>
                                                                <span class="text-[#D4A017]">+Rp {{ number_format((int) ($addon['subtotal'] ?? $addon['price'] ?? 0), 0, ',', '.') }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </details>
                                            @endif
                                        </div>
                                    @endif

                                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                        <div class="rounded-2xl bg-[#FAF6F0] px-3 py-2.5">
                                            <dt class="text-[11px] uppercase tracking-wide text-[#8B7359]">Tanggal</dt>
                                            <dd class="mt-1 font-medium text-[#3F2B1B]">{{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}</dd>
                                        </div>
                                        <div class="rounded-2xl bg-[#FAF6F0] px-3 py-2.5">
                                            <dt class="text-[11px] uppercase tracking-wide text-[#8B7359]">Studio</dt>
                                            <dd class="mt-1 font-medium text-[#3F2B1B]">{{ $b->studioLocation->name ?? '-' }}</dd>
                                            @if($b->studioRoom)
                                                <div class="text-xs text-[#8B7359]">{{ $b->studioRoom->name }}</div>
                                            @endif
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
                                        <details class="mt-4 rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-3">
                                            <summary class="cursor-pointer list-none text-sm font-semibold text-[#3F2B1B]">Ubah status</summary>
                                            <form method="POST" action="{{ route('admin.bookings.status', $b) }}" class="mt-3 flex items-center gap-2">
                                                @csrf
                                                <select name="status" class="min-w-0 flex-1 rounded-2xl border border-[#E1D3C5] bg-white px-3 py-2 text-sm text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                                    @foreach($availableStatusTransitions as $statusValue => $statusLabel)
                                                        <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                                                    @endforeach
                                                </select>
                                                <button class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white transition hover:brightness-110">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        </details>
                                    @endif
                                </article>
                            @endforeach
                        </div>

                        <div class="hidden overflow-x-auto lg:block">
                            <table class="min-w-full table-fixed">
                                <colgroup>
                                    <col class="w-[7%]">
                                    <col class="w-[18%]">
                                    <col class="w-[24%]">
                                    <col class="w-[11%]">
                                    <col class="w-[14%]">
                                    <col class="w-[11%]">
                                    <col class="w-[11%]">
                                    <col class="w-[8%]">
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
                                            $isAdmin = auth()->user()?->role === \App\Enums\Role::ADMIN;
                                            $availableStatusTransitions = match ($b->status) {
                                                'WAITING_PAYMENT' => $b->confirmed_at
                                                    ? ['CANCELLED' => 'Batalkan Pemesanan']
                                                    : ['WAITING_PAYMENT' => 'Konfirmasi Pemesanan', 'CANCELLED' => 'Tolak Pemesanan'],
                                                'DP_PAID' => ['PAID' => 'Lunas', 'CANCELLED' => 'Batalkan Pemesanan'],
                                                default => [],
                                            };
                                            $canUpdatePayment = $isAdmin && !empty($availableStatusTransitions);
                                            $addonCollection = collect($b->selected_addons);
                                            $addonPreview = $addonCollection->take(2);
                                            $remainingAddonCount = max(0, $addonCollection->count() - $addonPreview->count());
                                        @endphp
                                        <tr class="transition-all duration-200 hover:bg-[#FAF6F0]">
                                            <td class="px-3 py-4 align-middle text-center">
                                                <span class="font-mono text-sm font-semibold text-[#D4A017]">#{{ $b->id }}</span>
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center">
                                                <div class="flex items-center justify-center gap-3">
                                                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 text-sm font-semibold text-[#3F2B1B]">
                                                        {{ substr($b->client->name ?? 'U', 0, 1) }}
                                                    </div>
                                                    <div class="min-w-0 text-left">
                                                        <p class="truncate text-sm font-semibold text-[#3F2B1B]">{{ $b->client->name ?? '-' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 align-middle">
                                                <p class="text-sm font-medium text-[#3F2B1B]">{{ $b->package->name ?? '-' }}</p>
                                                @if($addonCollection->isNotEmpty())
                                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                                        @foreach($addonPreview as $addon)
                                                            <span class="inline-flex items-center gap-1 rounded-3xl border border-[#EDE0D0] bg-white px-2 py-1 text-[11px] text-[#7A5B3A]">
                                                                {{ $addon['label'] ?? '-' }}
                                                                @if(!empty($addon['quantity']))
                                                                    <span class="font-mono">x{{ (int) $addon['quantity'] }}</span>
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                        @if($remainingAddonCount > 0)
                                                            <details class="text-[11px] text-[#7A5B3A]">
                                                                <summary class="cursor-pointer list-none rounded-3xl border border-[#EDE0D0] bg-white px-2 py-1">+{{ $remainingAddonCount }} add-on</summary>
                                                                <div class="mt-2 min-w-[220px] space-y-1 rounded-2xl border border-[#EDE0D0] bg-white p-2.5 shadow-lg">
                                                                    @foreach($addonCollection->slice(2) as $addon)
                                                                        <div class="flex items-center justify-between gap-3">
                                                                            <span>{{ $addon['label'] ?? '-' }}</span>
                                                                            <span class="text-[#D4A017]">+Rp {{ number_format((int) ($addon['subtotal'] ?? $addon['price'] ?? 0), 0, ',', '.') }}</span>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </details>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center">
                                                <span class="whitespace-nowrap text-sm font-medium text-[#7A5B3A]">
                                                    {{ \Carbon\Carbon::parse($b->booking_date)->format('d M Y') }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center text-sm text-[#7A5B3A]">
                                                <div>{{ $b->studioLocation->name ?? '-' }}</div>
                                                @if($b->studioRoom)
                                                    <div class="mt-1 text-xs text-[#8B7359]">{{ $b->studioRoom->name }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center">
                                                <span class="inline-flex min-h-[34px] items-center gap-2 rounded-3xl px-3 py-1.5 text-[11px] font-semibold {{ $scheduleBadge['bg'] }} {{ $scheduleBadge['text'] }}">
                                                    <span class="h-2 w-2 rounded-full bg-current"></span>
                                                    {{ $scheduleBadge['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-4 align-middle text-center">
                                                <div class="space-y-2">
                                                    <span class="inline-flex min-h-[34px] items-center gap-2 rounded-3xl px-3 py-1.5 text-[11px] font-semibold {{ $badge['bg'] }} {{ $badge['text'] }}">
                                                        <span class="h-2 w-2 rounded-full bg-current"></span>
                                                        {{ $badge['label'] }}
                                                    </span>

                                                    @if($canUpdatePayment)
                                                        <details class="mx-auto w-fit rounded-2xl border border-[#EDE0D0] bg-white p-2 shadow-sm">
                                                            <summary class="cursor-pointer list-none text-[11px] font-semibold text-[#3F2B1B]">Aksi</summary>
                                                            <form method="POST" action="{{ route('admin.bookings.status', $b) }}" class="mt-2 flex items-center gap-2">
                                                                @csrf
                                                                <select name="status" class="max-w-[180px] rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] px-3 py-2 text-[11px] text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20">
                                                                    @foreach($availableStatusTransitions as $statusValue => $statusLabel)
                                                                        <option value="{{ $statusValue }}">{{ $statusLabel }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button class="inline-flex h-9 w-9 items-center justify-center rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white transition hover:brightness-110">
                                                                    <i class="fa-solid fa-check text-[11px]"></i>
                                                                </button>
                                                            </form>
                                                        </details>
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
