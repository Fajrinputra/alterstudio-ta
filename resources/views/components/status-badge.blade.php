@props(['status' => ''])

@php
    $map = [
        'WAITING_PAYMENT' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500', 'label' => 'Menunggu Pembayaran', 'icon' => 'fa-solid fa-clock'],
        'DP_PAID' => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200', 'dot' => 'bg-blue-500', 'label' => 'DP Dibayar', 'icon' => 'fa-solid fa-credit-card'],
        'PAID' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500', 'label' => 'Lunas', 'icon' => 'fa-solid fa-circle-check'],
        'CANCELLED' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'dot' => 'bg-rose-500', 'label' => 'Dibatalkan', 'icon' => 'fa-solid fa-circle-xmark'],
        'DRAFT' => ['bg' => 'bg-slate-50 text-slate-700 border-slate-200', 'dot' => 'bg-slate-500', 'label' => 'Draft', 'icon' => 'fa-solid fa-pen-to-square'],
        'PENDING' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'dot' => 'bg-amber-500', 'label' => 'Pending', 'icon' => 'fa-solid fa-hourglass-half'],
        'FAILED' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'dot' => 'bg-rose-500', 'label' => 'Gagal', 'icon' => 'fa-solid fa-circle-exclamation'],
        'EXPIRED' => ['bg' => 'bg-slate-50 text-slate-700 border-slate-200', 'dot' => 'bg-slate-500', 'label' => 'Kedaluwarsa', 'icon' => 'fa-solid fa-calendar-xmark'],
        'SCHEDULED' => ['bg' => 'bg-violet-50 text-violet-700 border-violet-200', 'dot' => 'bg-violet-500', 'label' => 'Terjadwal', 'icon' => 'fa-solid fa-calendar-check'],
        'SHOOT_DONE' => ['bg' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'dot' => 'bg-indigo-500', 'label' => 'Sesi Foto Selesai', 'icon' => 'fa-solid fa-camera'],
        'EDITING' => ['bg' => 'bg-orange-50 text-orange-700 border-orange-200', 'dot' => 'bg-orange-500', 'label' => 'Sedang Diedit', 'icon' => 'fa-solid fa-pen-ruler'],
        'FINAL' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'dot' => 'bg-emerald-500', 'label' => 'Final', 'icon' => 'fa-solid fa-circle-check'],
    ];

    $badge = $map[$status] ?? ['bg' => 'bg-slate-50 text-slate-700 border-slate-200', 'dot' => 'bg-slate-500', 'label' => $status, 'icon' => 'fa-solid fa-tag'];
@endphp

<span {{ $attributes->class("inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium border shadow-sm {$badge['bg']}") }}>
    <span class="h-2 w-2 rounded-full {{ $badge['dot'] }}"></span>
    <i class="{{ $badge['icon'] }} text-[10px] opacity-80"></i>
    {{ $badge['label'] }}
</span>
