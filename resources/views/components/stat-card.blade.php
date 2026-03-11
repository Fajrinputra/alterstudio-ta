@props([
    'label' => '',
    'value' => 0,
    'color' => 'primary',
    'icon' => null,
    'trend' => null,
])

@php
    $colors = [
        'primary' => 'from-[#b58042]/10 to-[#8b5b2e]/10 border-[#e3d5c4] text-[#4a301f]',
        'amber' => 'from-amber-500/10 to-amber-600/10 border-amber-200 text-amber-700',
        'emerald' => 'from-emerald-500/10 to-emerald-600/10 border-emerald-200 text-emerald-700',
        'blue' => 'from-blue-500/10 to-blue-600/10 border-blue-200 text-blue-700',
        'red' => 'from-red-500/10 to-red-600/10 border-red-200 text-red-700',
    ];
    
    $bgColor = $colors[$color] ?? $colors['primary'];
    
    $iconColors = [
        'primary' => 'bg-[#b58042]',
        'amber' => 'bg-amber-500',
        'emerald' => 'bg-emerald-500',
        'blue' => 'bg-blue-500',
        'red' => 'bg-red-500',
    ];
    
    $iconColor = $iconColors[$color] ?? $iconColors['primary'];
@endphp

<div {{ $attributes->class("relative overflow-hidden rounded-xl border bg-gradient-to-br p-5 {$bgColor}") }}>
    <div class="absolute right-0 top-0 -mt-5 -mr-5 w-24 h-24 {{ $iconColor }} opacity-10 rounded-full blur-2xl"></div>
    
    <div class="relative z-10">
        <div class="flex items-start justify-between">
            @if($icon)
                <div class="w-10 h-10 rounded-lg {{ $iconColor }} bg-opacity-20 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-{{ $icon }} {{ $iconColor }} text-white text-lg bg-{{ $color }}-500 w-5 h-5 flex items-center justify-center rounded-md"></i>
                </div>
            @endif
            
            @if($trend)
                <span class="text-xs {{ $trend > 0 ? 'text-emerald-600' : 'text-red-600' }} bg-white px-2 py-1 rounded-full shadow-sm">
                    <i class="fa-solid fa-arrow-{{ $trend > 0 ? 'up' : 'down' }} mr-1"></i>
                    {{ abs($trend) }}%
                </span>
            @endif
        </div>
        
        <p class="text-sm opacity-80 mb-1">{{ $label }}</p>
        <p class="text-2xl font-bold">{{ number_format($value) }}</p>
    </div>
</div>