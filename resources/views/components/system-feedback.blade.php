@php
    $statusMessages = [
        'profile-updated' => 'Profil berhasil diperbarui.',
        'avatar-updated' => 'Foto profil berhasil diperbarui.',
        'password-updated' => 'Password berhasil diperbarui.',
    ];
    $successMessage = session('success')
        ?? session('user_status')
        ?? (session('status') ? ($statusMessages[session('status')] ?? session('status')) : null);
@endphp

<div class="space-y-3 pt-4" aria-live="polite">
    @if ($errors->any())
        <div role="alert" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
                <div class="min-w-0">
                    <p class="font-semibold">Data belum dapat diproses.</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 shadow-sm">
            <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
            <p class="font-medium">{{ session('error') }}</p>
        </div>
    @elseif ($successMessage)
        <div role="status" class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
            <i class="fa-solid fa-circle-check mt-0.5 shrink-0"></i>
            <p class="font-medium">{{ $successMessage }}</p>
        </div>
    @endif
</div>
