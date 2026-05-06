@php
    use App\Enums\Role;

    $currentUser = auth()->user();
    $isCrewUser = $currentUser
        && $currentUser->isRole(Role::PHOTOGRAPHER, Role::EDITOR)
        && ! $currentUser->isRole(Role::ADMIN, Role::MANAGER, Role::CLIENT);
    $isPhotographerTask = $isCrewUser && $project->photographer_id === $currentUser->id;
    $isEditorTask = $isCrewUser && $project->editor_id === $currentUser->id;
    $driveUrl = $project->final_drive_url ?: $project->raw_drive_url;
    $productionBlockMessage = $project->productionBlockMessage();
    $canContinueProduction = $productionBlockMessage === null;
    $statusColors = [
        'DRAFT' => 'bg-gray-100 text-gray-700',
        'SCHEDULED' => 'bg-blue-100 text-blue-700',
        'SHOOT_DONE' => 'bg-purple-100 text-purple-700',
        'EDITING' => 'bg-orange-100 text-orange-700',
        'FINAL' => 'bg-emerald-100 text-emerald-700',
    ];
    $statusColor = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-700';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-folder-open text-[#D4A017]"></i>
                    Project #{{ $project->id }}
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B]">
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

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8 bg-[#FAF6F0]">
        <section class="bg-white/85 border border-[#EDE0D0] rounded-3xl p-8 shadow-xl">
            <div class="flex flex-wrap items-start justify-between gap-6">
                <div>
                    <h3 class="font-display text-2xl text-[#3F2B1B]">{{ $project->booking->package->name ?? '-' }}</h3>
                    <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm text-[#7A5B3A]">
                        <span><i class="fa-solid fa-calendar text-[#D4A017] mr-2"></i>{{ optional($project->booking->booking_date)->format('d M Y') }}</span>
                        <span><i class="fa-solid fa-location-dot text-[#D4A017] mr-2"></i>{{ $project->booking->location }}</span>
                        <span><i class="fa-solid fa-user text-[#D4A017] mr-2"></i>{{ $project->booking->client->name ?? '-' }}</span>
                    </div>
                </div>
                <span class="px-5 py-2 rounded-3xl text-sm font-medium {{ $statusColor }}">{{ $project->statusLabel() }}</span>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="bg-white border border-[#EDE0D0] rounded-3xl p-7 shadow-xl">
                <h4 class="font-display text-2xl text-[#3F2B1B] mb-5">Link Drive Project</h4>
                @if($driveUrl && $canContinueProduction)
                    <a href="{{ $driveUrl }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] px-7 py-4 font-semibold text-white">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Buka Folder Drive
                    </a>
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

            <div class="bg-white border border-[#EDE0D0] rounded-3xl p-7 shadow-xl">
                <h4 class="font-display text-2xl text-[#3F2B1B] mb-5">Permintaan Klien</h4>
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
