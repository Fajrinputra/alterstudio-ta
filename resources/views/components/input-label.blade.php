@props(['value', 'required' => false])

<label {{ $attributes->merge(['class' => 'mb-2 block text-xs font-medium uppercase tracking-[0.18em] text-[#7A5B3A]']) }}>
    {{ $value ?? $slot }}
    @if($required)
        <span class="ml-1 text-red-500">*</span>
    @endif
</label>
