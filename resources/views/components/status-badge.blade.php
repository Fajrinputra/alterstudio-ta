@props(['status' => ''])

@php
    $map = [
        'WAITING_PAYMENT' => ['bg' => 'bg-amber-100 text-amber-700 border-amber-200', 'label' => 'Menunggu Pembayaran', 'icon' => 'fa-solid fa-clock'],
        'DP_PAID' => ['bg' => 'bg-blue-100 text-blue-700 border-blue-200', 'label' => 'DP Dibayar', 'icon' => 'fa-solid fa-credit-card'],
        'PAID' => ['bg' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'label' => 'Lunas', 'icon' => 'fa-solid fa-circle-check'],
        'CANCELLED' => ['bg' => 'bg-red-100 text-red-700 border-red-200', 'label' => 'Batal', 'icon' => 'fa-solid fa-circle-xmark'],
        'DRAFT' => ['bg' => 'bg-gray-100 text-gray-700 border-gray-200', 'label' => 'Draft', 'icon' => 'fa-solid fa-pen-to-square'],
        'PENDING' => ['bg' => 'bg-amber-100 text-amber-700 border-amber-200', 'label' => 'Pending', 'icon' => 'fa-solid fa-hourglass-half'],
        'FAILED' => ['bg' => 'bg-red-100 text-red-700 border-red-200', 'label' => 'Gagal', 'icon' => 'fa-solid fa-circle-exclamation'],
        'EXPIRED' => ['bg' => 'bg-gray-100 text-gray-700 border-gray-200', 'label' => 'Expired', 'icon' => 'fa-solid fa-calendar-xmark'],
        'SCHEDULED' => ['bg' => 'bg-purple-100 text-purple-700 border-purple-200', 'label' => 'Terjadwal', 'icon' => 'fa-solid fa-calendar-check'],
        'SHOOT_DONE' => ['bg' => 'bg-indigo-100 text-indigo-700 border-indigo-200', 'label' => 'Sesi Foto Selesai', 'icon' => 'fa-solid fa-camera'],
        'EDITING' => ['bg' => 'bg-orange-100 text-orange-700 border-orange-200', 'label' => 'Editing', 'icon' => 'fa-solid fa-pen-ruler'],
        'REVIEW' => ['bg' => 'bg-cyan-100 text-cyan-700 border-cyan-200', 'label' => 'Review', 'icon' => 'fa-solid fa-eye'],
        'FINAL' => ['bg' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'label' => 'Final', 'icon' => 'fa-solid fa-circle-check'],
    ];

    $badge = $map[$status] ?? ['bg' => 'bg-gray-100 text-gray-700 border-gray-200', 'label' => $status, 'icon' => 'fa-solid fa-tag'];
@endphp

<span {{ $attributes->class("inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {$badge['bg']}") }}>
    <i class="{{ $badge['icon'] }} text-xs"></i>
    {{ $badge['label'] }}
</span>