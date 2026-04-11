@props(['disabled' => false, 'icon' => null])

<div class="relative">
    @if($icon)
        <span class="pointer-events-none absolute left-5 top-1/2 -translate-y-1/2 text-[#D4A017]">
            <i class="fa-solid fa-{{ $icon }}"></i>
        </span>
    @endif
    
    <input @disabled($disabled) 
           {{ $attributes->merge(['class' => 'w-full ' . ($icon ? 'pl-12' : 'px-6') . ' pr-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/80 backdrop-blur-sm text-[#3F2B1B] placeholder:text-[#A2876A] shadow-sm transition-all focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 focus:outline-none disabled:bg-[#F4EDE4] disabled:text-[#8B7359] disabled:cursor-not-allowed']) }}>
</div>
