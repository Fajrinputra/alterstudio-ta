@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700 shadow-sm']) }}>
        <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-2xl bg-white/80 text-emerald-600">
            <i class="fa-solid fa-circle-check text-sm"></i>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-medium uppercase tracking-[0.18em] text-emerald-700/80">Status</p>
            <p class="mt-1 text-sm font-medium">{{ $status }}</p>
        </div>
    </div>
@endif
