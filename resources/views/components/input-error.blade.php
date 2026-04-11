@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'mt-2 space-y-1.5 text-sm text-red-600']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-start gap-2 rounded-2xl border border-red-200 bg-red-50 px-3 py-2">
                <i class="fa-solid fa-circle-exclamation mt-0.5 text-xs"></i>
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
