@props(['disabled' => false, 'icon' => null])

<div class="relative">
    @if($icon)
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
            <i class="fa-solid fa-{{ $icon }}"></i>
        </span>
    @endif
    
    <input @disabled($disabled) 
           {{ $attributes->merge(['class' => 'w-full ' . ($icon ? 'pl-10' : 'px-4') . ' pr-4 py-2.5 bg-[#fdf8f2] border border-[#d7c5b2] rounded-xl text-[#1c2432] placeholder:text-[#b39b82] focus:border-[#b58042] focus:ring-[#b58042] focus:ring-1 focus:outline-none transition-colors']) }}>
</div>