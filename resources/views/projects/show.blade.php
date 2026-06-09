@php
    use App\Enums\Role;

    $currentUser = auth()->user();
    $isCrewUser = $currentUser
        && $currentUser->isRole(Role::PHOTOGRAPHER, Role::EDITOR)
        && ! $currentUser->isRole(Role::OWNER, Role::ADMIN, Role::MANAGER, Role::CLIENT);
    $isPhotographerTask = $isCrewUser && $project->photographer_id === $currentUser->id;
    $isEditorTask = $isCrewUser && $project->editor_id === $currentUser->id;
    $driveUrl = $project->final_drive_url ?: $project->raw_drive_url;
    $rawDriveExpiresAt = $project->raw_drive_uploaded_at?->copy()->addDays(3);
    $finalDriveExpiresAt = $project->final_drive_uploaded_at?->copy()->addDays(3);
    $productionBlockMessage = $project->productionBlockMessage();
    $canContinueProduction = $productionBlockMessage === null;
    $schedule = $project->schedule;
    $assignedPhotographer = $schedule?->photographer ?? $project->photographer;
    $assignedEditor = $schedule?->editor ?? $project->editor;
    $selectedAddons = collect($project->booking->selected_addons ?? []);
    $statusColors = [
        'DRAFT' => 'bg-gray-100 text-gray-700',
        'SCHEDULED' => 'bg-blue-100 text-blue-700',
        'SHOOT_DONE' => 'bg-purple-100 text-purple-700',
        'EDITING' => 'bg-orange-100 text-orange-700',
        'FINAL' => 'bg-emerald-100 text-emerald-700',
    ];
    $statusColor = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-700';
@endphp

@php
    $isPhotographerTask = $isCrewUser && (int) ($schedule?->photographer_id ?? $project->photographer_id) === (int) $currentUser->id;
    $isEditorTask = $isCrewUser && (int) ($schedule?->editor_id ?? $project->editor_id) === (int) $currentUser->id;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-folder-open text-[#D4A017]"></i>
                    Project #{{ $project->id }}
                </p>
                <h2 class="font-display text-3xl font-semibold tracking-tight text-[#3F2B1B] md:text-5xl md:tracking-[-1px]">
                    Detail Pasca-Produksi
                </h2>
            </div>
            @php
                $backUrl = $currentUser?->isRole(Role::CLIENT)
                    ? url('/bookings')
                    : ($isCrewUser ? url('/admin/schedules') : url('/admin/bookings'));
            @endphp
            <a href="{{ $backUrl }}"
               class="inline-flex w-full sm:w-auto items-center justify-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] hover:shadow transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-5 bg-[#FAF6F0] py-5 px-0 sm:px-6 sm:py-8 lg:px-8 sm:space-y-8">
        <section class="bg-white/85 border border-[#EDE0D0] rounded-3xl p-5 shadow-xl sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div>
                    <h3 class="font-display text-2xl text-[#3F2B1B]">{{ $project->booking->package->name ?? '-' }}</h3>
                    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-[#7A5B3A]">
                        <span><i class="fa-solid fa-calendar text-[#D4A017] mr-2"></i>{{ optional($project->booking->booking_date)->format('d M Y') }}</span>
                        <span><i class="fa-solid fa-user text-[#D4A017] mr-2"></i>{{ $project->booking->client->name ?? '-' }}</span>
                    </div>
                </div>
                <span class="rounded-3xl px-4 py-2 text-sm font-medium {{ $statusColor }}">{{ $project->statusLabel() }}</span>
            </div>
        </section>

        <section class="bg-white border border-[#EDE0D0] rounded-3xl p-5 shadow-xl sm:p-7">
            <h4 class="font-display text-xl text-[#3F2B1B] mb-4 sm:text-2xl sm:mb-5">Detail Paket</h4>
            <div class="grid gap-4 lg:grid-cols-[1fr_1.4fr]">
                <div class="rounded-2xl bg-[#FAF6F0] px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-widest text-[#8B7359]">Paket Dipilih</p>
                    <p class="mt-2 text-lg font-semibold text-[#3F2B1B]">{{ $project->booking->package->name ?? '-' }}</p>
                    <p class="mt-1 text-sm text-[#7A5B3A]">Total: Rp {{ number_format((int) ($project->booking->total_price ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-2xl bg-[#FAF6F0] px-5 py-4">
                    <p class="text-xs font-semibold uppercase tracking-widest text-[#8B7359]">Add-on Dipilih</p>
                    @if($selectedAddons->isNotEmpty())
                        <div class="mt-3 grid gap-2">
                            @foreach($selectedAddons as $addon)
                                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#EDE0D0] bg-white px-4 py-3 text-sm">
                                    <span class="font-medium text-[#3F2B1B]">
                                        {{ $addon['label'] ?? '-' }}
                                        @if(!empty($addon['quantity']))
                                            <span class="font-mono text-[#8B7359]">x{{ (int) $addon['quantity'] }}</span>
                                        @endif
                                    </span>
                                    <span class="font-semibold text-[#D4A017]">
                                        Rp {{ number_format((int) ($addon['subtotal'] ?? $addon['price'] ?? 0), 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2 text-sm text-[#7A5B3A]">Tidak ada add-on yang dipilih.</p>
                    @endif
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-[#EDE0D0] bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-[#8B7359]">Cabang Studio</p>
                <p class="mt-2 text-lg font-semibold text-[#3F2B1B]">{{ $project->booking->studioLocation->name ?? '-' }}</p>
            </div>
            <div class="rounded-3xl border border-[#EDE0D0] bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-[#8B7359]">Studio / Ruangan</p>
                <p class="mt-2 text-lg font-semibold text-[#3F2B1B]">{{ $project->booking->studioRoom->name ?? '-' }}</p>
            </div>
            <div class="rounded-3xl border border-[#EDE0D0] bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-[#8B7359]">Fotografer</p>
                <p class="mt-2 text-lg font-semibold text-[#3F2B1B]">{{ $assignedPhotographer->name ?? 'Belum ditugaskan' }}</p>
            </div>
            <div class="rounded-3xl border border-[#EDE0D0] bg-white p-4 shadow-sm sm:p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-[#8B7359]">Editor</p>
                <p class="mt-2 text-lg font-semibold text-[#3F2B1B]">{{ $assignedEditor->name ?? 'Belum ditugaskan' }}</p>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="bg-white border border-[#EDE0D0] rounded-3xl p-5 shadow-xl sm:p-7">
                <h4 class="font-display text-xl text-[#3F2B1B] mb-4 sm:text-2xl sm:mb-5">Link Drive Project</h4>
                @if($driveUrl && $canContinueProduction)
                    <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                       class="inline-flex w-full items-center justify-center gap-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] px-6 py-3.5 font-semibold text-white sm:w-auto sm:px-7 sm:py-4">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Buka Folder Drive
                    </a>
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <p class="font-semibold">
                            <i class="fa-solid fa-calendar-day mr-2"></i>
                            Link Drive berlaku selama 3 hari.
                        </p>
                        <p class="mt-1">
                            @if($project->final_drive_url && $finalDriveExpiresAt)
                                Link hasil final dapat diakses sampai {{ $finalDriveExpiresAt->translatedFormat('d M Y') }}.
                            @elseif($project->raw_drive_url && $rawDriveExpiresAt)
                                Link foto mentah dapat diakses sampai {{ $rawDriveExpiresAt->translatedFormat('d M Y') }}.
                            @else
                                Silakan segera buka dan unduh file sebelum masa berlaku berakhir.
                            @endif
                        </p>
                    </div>
                    <dl class="mt-6 space-y-3 text-sm">
                        <div class="rounded-2xl bg-[#FAF6F0] px-4 py-3">
                            <dt class="text-xs uppercase tracking-wide text-[#8B7359]">Link Foto Mentah</dt>
                            <dd class="mt-1 break-all text-[#3F2B1B]">{{ $project->raw_drive_url ?? '-' }}</dd>
                        </div>
                        <div class="rounded-2xl bg-[#FAF6F0] px-4 py-3">
                            <dt class="text-xs uppercase tracking-wide text-[#8B7359]">Link Hasil Final</dt>
                            <dd class="mt-1 break-all text-[#3F2B1B]">{{ $project->final_drive_url ?? '-' }}</dd>
                        </div>
                    </dl>
                @else
                    @if($driveUrl && !$canContinueProduction)
                        <div class="rounded-3xl border border-rose-200 bg-rose-50 p-6 text-rose-700">
                            <p class="font-semibold">Akses Drive dihentikan</p>
                            <p class="mt-2 text-sm">{{ $productionBlockMessage }}</p>
                        </div>
                    @else
                        <p class="text-[#7A5B3A]">Link Drive belum dikirim oleh fotografer.</p>
                    @endif
                @endif
            </div>

            <div class="bg-white border border-[#EDE0D0] rounded-3xl p-5 shadow-xl sm:p-7">
                <h4 class="font-display text-xl text-[#3F2B1B] mb-4 sm:text-2xl sm:mb-5">Permintaan Klien</h4>
                @if($project->hasEditRequest())
                    <div class="space-y-4">
                        <div class="rounded-2xl bg-[#FAF6F0] px-4 py-3">
                            <p class="text-xs uppercase tracking-wide text-[#8B7359]">Kode Foto</p>
                            <p class="mt-1 whitespace-pre-line text-sm text-[#3F2B1B]">{{ $project->edit_photo_codes }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#FAF6F0] px-4 py-3">
                            <p class="text-xs uppercase tracking-wide text-[#8B7359]">Deskripsi Edit</p>
                            <p class="mt-1 whitespace-pre-line text-sm text-[#3F2B1B]">{{ $project->edit_request_note }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-[#7A5B3A]">Klien belum mengirim kode foto dan deskripsi edit.</p>
                @endif
            </div>
        </section>


        @if($isPhotographerTask || $isEditorTask)
            <section class="bg-white border border-[#EDE0D0] rounded-3xl p-7 shadow-xl">
                <h4 class="font-display text-2xl text-[#3F2B1B] mb-3">Tugas Anda</h4>
                <div class="flex flex-wrap gap-3">
                    @if($isPhotographerTask)
                        <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-3xl bg-blue-100 text-blue-700 text-sm font-medium">
                            <i class="fa-solid fa-camera"></i> Fotografer
                        </span>
                    @endif
                    @if($isEditorTask)
                        <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-3xl bg-orange-100 text-orange-700 text-sm font-medium">
                            <i class="fa-solid fa-pen-ruler"></i> Editor
                        </span>
                    @endif
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
