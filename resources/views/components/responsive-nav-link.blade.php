@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2.5 border-l-4 border-[#b58042] text-start text-base font-medium text-[#b58042] bg-[#fcf7f1] focus:outline-none focus:text-[#8b5b2e] focus:bg-[#fcf7f1] focus:border-[#8b5b2e] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2.5 border-l-4 border-transparent text-start text-base font-medium text-[#6f5134] hover:text-[#b58042] hover:bg-[#fcf7f1] hover:border-[#b58042]/50 focus:outline-none focus:text-[#b58042] focus:bg-[#fcf7f1] focus:border-[#b58042]/50 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>