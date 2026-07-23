@php
    use App\Enums\Role;

    $currentUser = auth()->user();
    $isCrewUser = $currentUser
        && $currentUser->isRole(Role::PHOTOGRAPHER, Role::EDITOR)
        && ! $currentUser->isRole(Role::OWNER, Role::ADMIN, Role::MANAGER, Role::CLIENT);
    $isPhotographerTask = false;
    $isEditorTask = false;
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
            <section class="bg-white border border-[#EDE0D0] rounded-3xl p-5 shadow-xl sm:p-7">
                <h4 class="font-display text-2xl text-[#3F2B1B] mb-5">Tugas Anda</h4>

                {{-- Badge peran --}}
                <div class="flex flex-wrap gap-3 mb-6">
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

                @if(!$canContinueProduction)
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                        <p class="font-semibold flex items-center gap-2"><i class="fa-solid fa-circle-xmark"></i> Proses pasca-produksi belum dapat dilanjutkan</p>
                        <p class="mt-1">{{ $productionBlockMessage }}</p>
                    </div>
                @else
                    {{-- Form Fotografer: upload link Drive RAW --}}
                    @if($isPhotographerTask)
                        <div class="space-y-4 border-t border-[#EDE0D0] pt-5">
                            <h5 class="font-display text-lg text-[#3F2B1B] flex items-center gap-2">
                                <i class="fa-brands fa-google-drive text-[#D4A017]"></i>
                                Bagikan Link Drive Foto Mentah
                            </h5>
                            @if(!$project->hasRawDriveLink())
                                <p class="text-sm text-[#7A5B3A]">Upload foto mentah ke Google Drive, lalu tempel link folder di sini. Klien akan diberi tahu dan link berlaku <strong>3 hari</strong>.</p>
                                <form method="POST" action="{{ route('projects.drive-assets.store', $project) }}" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="type" value="RAW">
                                    <div class="flex flex-col gap-3 sm:flex-row">
                                        <input type="url" name="raw_drive_url" required placeholder="https://drive.google.com/..."
                                               class="flex-1 rounded-2xl border border-[#E1D3C5] bg-white px-4 py-3 text-sm focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                        <button type="submit"
                                                class="px-6 py-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-sm text-white font-semibold hover:shadow-xl transition-all whitespace-nowrap">
                                            <i class="fa-solid fa-paper-plane mr-2"></i>Simpan Link
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                    <p class="flex items-center gap-2 text-emerald-700 font-medium text-sm">
                                        <i class="fa-solid fa-circle-check"></i> Link Drive foto mentah telah dikirim ke klien.
                                    </p>
                                    <a href="{{ $project->raw_drive_url }}" target="_blank" rel="noopener"
                                       class="mt-3 inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-[#EDE0D0] rounded-3xl text-sm hover:border-[#D4A017] transition-all">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[#D4A017]"></i>Buka Drive
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Form Editor: lihat permintaan edit + upload final --}}
                    @if($isEditorTask)
                        <div class="space-y-5 border-t border-[#EDE0D0] pt-5">
                            <h5 class="font-display text-lg text-[#3F2B1B] flex items-center gap-2">
                                <i class="fa-solid fa-pen-ruler text-[#D4A017]"></i>
                                Permintaan Edit dari Klien
                            </h5>

                            @if($project->hasEditRequest())
                                <div class="space-y-3">
                                    <div class="rounded-2xl bg-[#FAF6F0] px-4 py-3">
                                        <p class="text-xs uppercase tracking-wide text-[#8B7359]">Kode Foto</p>
                                        <p class="mt-1 whitespace-pre-line text-sm text-[#3F2B1B]">{{ $project->edit_photo_codes }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-[#FAF6F0] px-4 py-3">
                                        <p class="text-xs uppercase tracking-wide text-[#8B7359]">Catatan Edit</p>
                                        <p class="mt-1 whitespace-pre-line text-sm text-[#3F2B1B]">{{ $project->edit_request_note }}</p>
                                    </div>
                                </div>

                                {{-- Form upload hasil final --}}
                                @if(!$project->hasFinalDelivery())
                                    <div class="border-t border-[#EDE0D0] pt-5">
                                        <h5 class="font-display text-lg text-[#3F2B1B] flex items-center gap-2 mb-3">
                                            <i class="fa-solid fa-star text-[#D4A017]"></i>Tandai Hasil Final Tersedia
                                        </h5>
                                        <p class="text-sm text-[#7A5B3A] mb-4">Tempel link Google Drive hasil final. Jika tidak diisi, link foto mentah akan dipakai.</p>
                                        <form method="POST" action="{{ route('projects.drive-assets.store', $project) }}">
                                            @csrf
                                            <input type="hidden" name="type" value="FINAL">
                                            <div class="space-y-3">
                                                <input type="url" name="final_drive_url" placeholder="https://drive.google.com/... (opsional)"
                                                       class="w-full rounded-2xl border border-[#E1D3C5] bg-white px-4 py-3 text-sm focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                                <textarea name="final_message" rows="2" placeholder="Pesan untuk klien (opsional, maks. 1000 karakter)" maxlength="1000"
                                                          class="w-full rounded-2xl border border-[#E1D3C5] bg-white px-4 py-3 text-sm focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"></textarea>
                                                <button type="submit"
                                                        class="px-6 py-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-sm text-white font-semibold hover:shadow-xl transition-all">
                                                    <i class="fa-solid fa-check mr-2"></i>Tandai Hasil Final Siap
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                        <p class="flex items-center gap-2 text-emerald-700 font-medium text-sm">
                                            <i class="fa-solid fa-circle-check"></i> Hasil final sudah ditandai tersedia untuk klien.
                                        </p>
                                        @if($project->final_drive_url)
                                            <a href="{{ $project->final_drive_url }}" target="_blank" rel="noopener"
                                               class="mt-3 inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-[#EDE0D0] rounded-3xl text-sm hover:border-[#D4A017] transition-all">
                                                <i class="fa-solid fa-arrow-up-right-from-square text-[#D4A017]"></i>Buka Drive Final
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                    <p class="font-semibold flex items-center gap-2"><i class="fa-solid fa-hourglass-half"></i> Menunggu Permintaan Edit Klien</p>
                                    <p class="mt-1">Klien belum mengirim kode foto dan deskripsi edit. Anda akan mendapat notifikasi setelah klien mengirim.</p>
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </section>
        @endif
    </div>
</x-app-layout>
