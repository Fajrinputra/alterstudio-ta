@props([
    'label' => '',
    'value' => 0,
    'color' => 'primary',
    'icon' => null,
    'trend' => null,
])

@php
    $colors = [
        'primary' => [
            'card' => 'from-[#b58042]/10 to-[#8b5b2e]/10 border-[#e3d5c4] text-[#4a301f]',
            'icon' => 'bg-[#b58042]',
        ],
        'amber' => [
            'card' => 'from-amber-500/10 to-amber-600/10 border-amber-200 text-amber-700',
            'icon' => 'bg-amber-500',
        ],
        'emerald' => [
            'card' => 'from-emerald-500/10 to-emerald-600/10 border-emerald-200 text-emerald-700',
            'icon' => 'bg-emerald-500',
        ],
        'blue' => [
            'card' => 'from-blue-500/10 to-blue-600/10 border-blue-200 text-blue-700',
            'icon' => 'bg-blue-500',
        ],
        'red' => [
            'card' => 'from-red-500/10 to-red-600/10 border-red-200 text-red-700',
            'icon' => 'bg-red-500',
        ],
    ];

    $palette = $colors[$color] ?? $colors['primary'];
    $bgColor = $palette['card'];
    $iconColor = $palette['icon'];
@endphp

<div {{ $attributes->class("relative min-w-0 overflow-hidden rounded-2xl border bg-gradient-to-br p-4 shadow-lg sm:p-5 {$bgColor}") }}>
    <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full {{ $iconColor }} opacity-10 blur-2xl"></div>
    
    <div class="relative z-10 flex min-w-0 flex-wrap items-center gap-3 sm:flex-nowrap sm:gap-4">
        @if($icon || $trend)
            <div class="flex flex-shrink-0 items-center gap-3">
                @if($icon)
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $iconColor }} text-white shadow-sm">
                        <i class="fa-solid fa-{{ $icon }} text-base"></i>
                    </div>
                @endif

                @if($trend)
                    <span class="rounded-3xl bg-white px-2.5 py-1 text-[11px] {{ $trend > 0 ? 'text-emerald-600' : 'text-red-600' }} shadow-sm">
                        <i class="fa-solid fa-arrow-{{ $trend > 0 ? 'up' : 'down' }} mr-1"></i>
                        {{ abs($trend) }}%
                    </span>
                @endif
            </div>
        @endif

        <p class="min-w-0 flex-1 text-xs font-medium uppercase tracking-[0.16em] opacity-80 sm:tracking-[0.18em]">{{ $label }}</p>
        <p class="flex-shrink-0 text-right text-xl font-semibold leading-none tabular-nums sm:text-2xl">{{ number_format($value) }}</p>
    </div>
</div>
