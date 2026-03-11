@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-center gap-2 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700']) }}>
        <i class="fa-solid fa-circle-check text-emerald-500"></i>
        <span class="text-sm font-medium">{{ $status }}</span>
    </div>
@endif