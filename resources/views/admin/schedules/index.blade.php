<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-calendar-days text-[#D4A017]"></i>
                    Penjadwalan
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Jadwalkan <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Fotografer & Editor</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
          
            {{-- Session Messages --}}
            @if(session('success'))
                <div class="flex items-center gap-3 p-5 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-sm">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 p-5 rounded-3xl bg-red-50 border border-red-200 text-red-700 shadow-sm">
                    <i class="fa-solid fa-circle-exclamation text-2xl"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Filter Form Premium --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#D4A017]/10 via-[#E07A5F]/10 to-[#D4A017]/10 rounded-3xl blur-2xl"></div>
                <div class="relative glass border border-[#EDE0D0] rounded-3xl p-8 shadow-xl backdrop-blur-2xl">
                    <form method="GET" class="flex flex-wrap items-end gap-6">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-box text-[#D4A017]"></i>
                                Filter Paket
                            </label>
                            <select name="package_id" 
                                    class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                <option value="">Semua Paket</option>
                                @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}" @selected(($packageFilter ?? null) == $pkg->id)>{{ $pkg->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 min-w-[220px]">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-calendar-check text-[#D4A017]"></i>
                                Status Jadwal
                            </label>
                            <select name="schedule_status" 
                                    class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                <option value="">Semua Pemesanan</option>
                                <option value="scheduled" @selected(($scheduleFilter ?? null) === 'scheduled')>Sudah terjadwal</option>
                                <option value="unscheduled" @selected(($scheduleFilter ?? null) === 'unscheduled')>Belum terjadwal</option>
                            </select>
                        </div>
                      
                        <div class="flex-1 min-w-[220px]">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-users-gear text-[#D4A017]"></i>
                                Peran Penugasan
                            </label>
                            <select name="assignment_role" 
                                    class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                <option value="">Semua Peran</option>
                                <option value="photographer" @selected(($assignmentRoleFilter ?? null) === 'photographer')>Tugas Fotografer</option>
                                <option value="editor" @selected(($assignmentRoleFilter ?? null) === 'editor')>Tugas Editor</option>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[240px]">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-user-check text-[#D4A017]"></i>
                                Filter Kru
                            </label>
                            <select name="crew_user_id" 
                                    class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                <option value="">Semua Kru</option>
                                @foreach($crewUsers as $crewUser)
                                    <option value="{{ $crewUser->id }}" @selected(($crewUserFilter ?? null) == $crewUser->id)>{{ $crewUser->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex w-full sm:w-auto">
                            <button class="w-full sm:w-auto h-14 px-8 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl shadow-[#D4A017]/30 hover:shadow-2xl hover:-translate-y-0.5 active:scale-[0.98] transition-all flex items-center justify-center gap-3 text-base">
                                <i class="fa-solid fa-filter"></i>
                                Terapkan Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Project Cards --}}
            <div class="space-y-8">
                @forelse($projects as $project)
                    @php
                        $disabledPhotographers = $unavailablePhotographers[$project->id] ?? [];
                        $disabledEditors = $unavailableEditors[$project->id] ?? [];
                        $currentUser    = auth()->user();
                        $isPhotographer = $project->schedule && $project->schedule->photographer_id === $currentUser->id;
                        $isEditor       = $project->schedule && $project->schedule->editor_id === $currentUser->id;
                        $canSchedule    = in_array($project->booking->status, ['DP_PAID','PAID']);
                        $duration       = $project->booking->package->duration_minutes ?? 60;
                        $dateOnly       = optional($project->booking->booking_date)->toDateString();
                        $timeOnly       = $project->booking->booking_time ?? '00:00';
                        $startCarbon    = $dateOnly ? \Carbon\Carbon::parse($dateOnly.' '.$timeOnly) : null;
                        $startText      = $startCarbon ? $startCarbon->format('d M Y H:i') : '-';
                        $endText        = $startCarbon ? $startCarbon->clone()->addMinutes($duration)->format('d M Y H:i') : '-';
                        
                        $statusBadge = [
                            'DRAFT'       => ['label' => 'Belum dijadwalkan', 'color' => 'bg-gray-100 text-gray-700'],
                            'SCHEDULED'   => ['label' => 'Terjadwal', 'color' => 'bg-blue-100 text-blue-700'],
                            'SHOOT_DONE'  => ['label' => 'Sesi Foto Selesai', 'color' => 'bg-purple-100 text-purple-700'],
                            'EDITING'     => ['label' => 'Permintaan edit', 'color' => 'bg-orange-100 text-orange-700'],
                            'FINAL'       => ['label' => 'Foto diunggah', 'color' => 'bg-emerald-100 text-emerald-700'],
                        ][$project->status] ?? ['label' => $project->status, 'color' => 'bg-gray-100'];
                        
                        $bookingStatus = [
                            'WAITING_PAYMENT' => 'Menunggu Pembayaran',
                            'DP_PAID'         => 'Pembayaran DP',
                            'PAID'            => 'Pembayaran LUNAS',
                            'CANCELLED'       => 'Dibatalkan',
                            'DRAFT'           => 'Draft',
                        ][$project->booking->status] ?? $project->booking->status;
                        
                        $finalAssets   = $project->mediaAssets->where('type','FINAL');
                        $rawAssets     = $project->mediaAssets->where('type','RAW')->sortBy('version');
                        $canManageSchedule = $project->schedule
                            && in_array($project->status, ['SCHEDULED', 'DRAFT'], true)
                            && !$project->selections_locked
                            && $project->mediaAssets->isEmpty();
                        
                        if ($canManageSchedule) {
                            $scheduleManageBadge = ['label' => 'Status Jadwal: Bisa Diubah', 'color' => 'bg-sky-100 text-sky-700'];
                        } elseif ($project->status === 'FINAL') {
                            $scheduleManageBadge = ['label' => 'Status Jadwal: Terkunci (Final)', 'color' => 'bg-emerald-100 text-emerald-700'];
                        } elseif ($project->status === 'EDITING' || $project->selections_locked) {
                            $scheduleManageBadge = ['label' => 'Status Jadwal: Terkunci (Editing)', 'color' => 'bg-orange-100 text-orange-700'];
                        } elseif ($project->status === 'SHOOT_DONE' || $project->mediaAssets->isNotEmpty()) {
                            $scheduleManageBadge = ['label' => 'Status Jadwal: Terkunci (Produksi)', 'color' => 'bg-purple-100 text-purple-700'];
                        } else {
                            $scheduleManageBadge = ['label' => 'Status Jadwal: Terkunci', 'color' => 'bg-slate-100 text-slate-700'];
                        }
                    @endphp

                    <div class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/5 via-[#E07A5F]/5 to-transparent rounded-3xl blur-3xl"></div>
                        <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-8 shadow-2xl hover:shadow-3xl transition-all duration-300">
                            
                            {{-- Header --}}
                            <div class="flex flex-wrap items-start justify-between gap-6 mb-8">
                                <div class="flex items-start gap-4">
                                    <div class="w-14 h-14 rounded-3xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center flex-shrink-0">
                                        <span class="font-mono text-[#D4A017] font-semibold text-xl">#{{ $project->booking_id }}</span>
                                    </div>
                                    <div>
                                        <h3 class="font-display text-2xl text-[#3F2B1B] leading-tight">{{ $project->booking->package->name ?? '-' }}</h3>
                                        <div class="flex flex-wrap items-center gap-4 text-sm text-[#7A5B3A] mt-2">
                                            <span class="flex items-center gap-2">
                                                <i class="fa-solid fa-user"></i>
                                                {{ $project->booking->client->name ?? '-' }}
                                            </span>
                                            <span class="flex items-center gap-2">
                                                <i class="fa-solid fa-location-dot"></i>
                                                {{ $project->booking->studioLocation->name ?? 'Cabang belum dipilih' }}
                                                @if($project->booking->studioRoom)
                                                    — {{ $project->booking->studioRoom->name }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3 flex-wrap justify-end">
                                    <span class="px-5 py-2 rounded-3xl text-sm font-medium {{ $statusBadge['color'] }}">
                                        {{ $statusBadge['label'] }}
                                    </span>
                                    @if($project->schedule)
                                        <span class="px-5 py-2 rounded-3xl text-sm font-medium bg-emerald-100 text-emerald-700 flex items-center gap-2">
                                            <i class="fa-solid fa-check"></i> Terjadwal
                                        </span>
                                        @if($isPhotographer)
                                            <span class="px-5 py-2 rounded-3xl text-sm font-medium bg-blue-100 text-blue-700 flex items-center gap-2">
                                                <i class="fa-solid fa-camera"></i> Fotografer
                                            </span>
                                        @endif
                                        @if($isEditor)
                                            <span class="px-5 py-2 rounded-3xl text-sm font-medium bg-orange-100 text-orange-700 flex items-center gap-2">
                                                <i class="fa-solid fa-pen-ruler"></i> Editor
                                            </span>
                                        @endif>
                                        @if(!(isset($readOnly) && $readOnly))
                                            <span class="px-5 py-2 rounded-3xl text-sm font-medium {{ $scheduleManageBadge['color'] }}">
                                                {{ $scheduleManageBadge['label'] }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- Schedule Info Box --}}
                            <div class="grid md:grid-cols-2 gap-6 mb-8 p-6 bg-[#FAF6F0] rounded-3xl border border-[#EDE0D0]">
                                <div>
                                    <p class="text-xs tracking-widest text-[#8B7359] mb-2">JADWAL PEMESANAN</p>
                                    <p class="text-[#3F2B1B] font-medium flex items-center gap-3">
                                        <i class="fa-solid fa-calendar text-[#D4A017]"></i>
                                        {{ $startText }} — {{ $endText }}
                                    </p>
                                    <p class="text-sm text-[#7A5B3A] mt-1">Durasi: {{ $duration }} menit</p>
                                </div>
                                <div>
                                    <p class="text-xs tracking-widest text-[#8B7359] mb-2">STATUS PEMBAYARAN</p>
                                    <p class="text-[#3F2B1B] font-medium">{{ $bookingStatus }}</p>
                                </div>
                            </div>

                            @if(isset($readOnly) && $readOnly)
                                {{-- Read Only View untuk Staff --}}
                                <div class="grid md:grid-cols-2 gap-6 mb-8 p-6 bg-[#FAF6F0] rounded-3xl border border-[#EDE0D0]">
                                    <div>
                                        <p class="text-xs tracking-widest text-[#8B7359] mb-2">FOTOGRAFER</p>
                                        <p class="text-[#3F2B1B] font-medium flex items-center gap-3">
                                            <i class="fa-solid fa-camera text-[#D4A017]"></i>
                                            {{ optional($project->schedule?->photographer)->name ?? '-' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs tracking-widest text-[#8B7359] mb-2">EDITOR</p>
                                        <p class="text-[#3F2B1B] font-medium flex items-center gap-3">
                                            <i class="fa-solid fa-pen-ruler text-[#D4A017]"></i>
                                            {{ optional($project->schedule?->editor)->name ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                                @if($project->schedule)
                                    <div class="border-t border-[#EDE0D0] pt-8">
                                        @if($isPhotographer)
                                            {{-- Upload RAW untuk Fotografer --}}
                                            <div class="space-y-6">
                                                <div class="inline-flex items-center gap-3 px-5 py-3 rounded-3xl bg-blue-50 text-blue-700 text-sm font-medium">
                                                    <i class="fa-solid fa-camera"></i>
                                                    Anda bertugas sebagai Fotografer
                                                </div>
                                                <h4 class="font-display text-xl text-[#3F2B1B] flex items-center gap-3">
                                                    <i class="fa-solid fa-cloud-upload text-[#D4A017]"></i>
                                                    Unggah Hasil Sesi Foto (RAW)
                                                </h4>
                                                
                                                @if($rawAssets->isEmpty())
                                                    <p class="text-[#7A5B3A]">Unggah banyak file sekaligus (maksimal 50). Status otomatis berubah setelah upload.</p>
                                                    <form method="POST" action="/projects/{{ $project->id }}/assets" enctype="multipart/form-data" class="mt-4">
                                                        @csrf
                                                        <input type="hidden" name="type" value="RAW">
                                                        <div class="flex flex-col sm:flex-row gap-4">
                                                            <input type="file" name="files[]" multiple accept="image/*"
                                                                   class="flex-1 text-sm file:mr-4 file:py-4 file:px-8 file:rounded-3xl file:border-0 file:bg-[#D4A017] file:text-white file:font-semibold hover:file:brightness-110">
                                                            <button class="px-10 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold hover:shadow-xl transition-all">
                                                                <i class="fa-solid fa-upload mr-2"></i> Unggah RAW
                                                            </button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-6">
                                                        <p class="flex items-center gap-2 text-emerald-700 font-medium">
                                                            <i class="fa-solid fa-circle-check"></i>
                                                            {{ $rawAssets->count() }} file RAW telah diunggah
                                                        </p>
                                                        <div class="flex flex-wrap gap-3 mt-4">
                                                            @foreach($rawAssets as $asset)
                                                                <a href="{{ Storage::url($asset->path) }}" target="_blank"
                                                                   class="inline-flex items-center gap-2 px-5 py-3 bg-white border border-[#EDE0D0] rounded-3xl text-sm hover:border-[#D4A017]">
                                                                    <i class="fa-solid fa-file-image text-[#D4A017]"></i>
                                                                    Versi {{ $asset->version }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @if($isEditor)
                                            {{-- Upload Final untuk Editor --}}
                                            <div class="space-y-6 mt-10">
                                                <div class="inline-flex items-center gap-3 px-5 py-3 rounded-3xl bg-orange-50 text-orange-700 text-sm font-medium">
                                                    <i class="fa-solid fa-pen-ruler"></i>
                                                    Anda bertugas sebagai Editor
                                                </div>
                                                <h4 class="font-display text-xl text-[#3F2B1B] flex items-center gap-3">
                                                    <i class="fa-solid fa-images text-[#D4A017]"></i>
                                                    Unggah Hasil Final
                                                </h4>
                                                
                                                @if($finalAssets->isEmpty())
                                                    <form method="POST" action="/projects/{{ $project->id }}/assets" enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="hidden" name="type" value="FINAL">
                                                        <div class="flex flex-col sm:flex-row gap-4">
                                                            <input type="file" name="files[]" multiple accept="image/*"
                                                                   class="flex-1 text-sm file:mr-4 file:py-4 file:px-8 file:rounded-3xl file:border-0 file:bg-[#D4A017] file:text-white file:font-semibold hover:file:brightness-110">
                                                            <button class="px-10 py-4 rounded-3xl bg-gradient-to-r from-emerald-600 to-emerald-700 text-white font-semibold hover:shadow-xl transition-all">
                                                                <i class="fa-solid fa-upload mr-2"></i> Unggah Final
                                                            </button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="grid sm:grid-cols-3 gap-6">
                                                        @foreach($finalAssets as $asset)
                                                            <a href="{{ Storage::url($asset->path) }}" target="_blank"
                                                               class="group relative rounded-3xl overflow-hidden border border-[#EDE0D0] hover:border-[#D4A017] transition-all">
                                                                <img src="{{ Storage::url($asset->path) }}" class="w-full h-48 object-cover" alt="Final">
                                                                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                                                                    <p class="text-white text-xs">Versi {{ $asset->version }}</p>
                                                                </div>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @else
                                {{-- Admin / Scheduling View --}}
                                @if(!$project->schedule)
                                    <form method="POST" action="/projects/{{ $project->id }}/schedule" 
                                          class="grid md:grid-cols-4 gap-6 items-end">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">
                                                <i class="fa-solid fa-camera text-[#D4A017]"></i> Fotografer
                                            </label>
                                            <select name="photographer_id" 
                                                    class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                                <option value="">Pilih Fotografer</option>
                                                @foreach($photographers as $p)
                                                    <option value="{{ $p->id }}" 
                                                            @selected(optional($project->schedule)->photographer_id == $p->id)
                                                            @disabled(in_array($p->id, $disabledPhotographers, true))>
                                                        {{ $p->name }}
                                                        @if(in_array($p->id, $disabledPhotographers, true)) (Sudah ada jadwal) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">
                                                <i class="fa-solid fa-pen-ruler text-[#D4A017]"></i> Editor
                                            </label>
                                            <select name="editor_id" 
                                                    class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                                <option value="">Pilih Editor</option>
                                                @foreach($editors as $e)
                                                    <option value="{{ $e->id }}" 
                                                            @selected(optional($project->schedule)->editor_id == $e->id)
                                                            @disabled(in_array($e->id, $disabledEditors, true))>
                                                        {{ $e->name }}
                                                        @if(in_array($e->id, $disabledEditors, true)) (Sudah ada jadwal) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">
                                                <i class="fa-solid fa-door-open text-[#D4A017]"></i> Ruangan
                                            </label>
                                            <select name="studio_room_id" required 
                                                    class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                                <option value="">Pilih Ruangan</option>
                                                @foreach(($project->booking->studioLocation->rooms ?? collect())->where('is_active', true) as $room)
                                                    <option value="{{ $room->id }}" 
                                                            @selected($project->booking->studio_room_id == $room->id)>
                                                        {{ $room->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <button class="w-full h-14 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3 text-base"
                                                    @if(!$canSchedule) disabled @endif>
                                                <i class="fa-solid fa-calendar-check"></i>
                                                {{ $canSchedule ? 'Simpan Jadwal' : 'Menunggu Pembayaran' }}
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    {{-- Jadwal sudah ada --}}
                                    <div class="bg-emerald-50 border border-emerald-200 rounded-3xl p-7">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center">
                                                <i class="fa-solid fa-check text-emerald-600 text-2xl"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-emerald-800">Jadwal Telah Tersimpan</p>
                                                <p class="text-emerald-700 text-sm">
                                                    Fotografer: <span class="font-medium">{{ $project->schedule->photographer->name ?? '-' }}</span> • 
                                                    Editor: <span class="font-medium">{{ $project->schedule->editor->name ?? '-' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($canManageSchedule)
                                        <div x-data="{ showDeleteSchedule: false }" class="mt-8 grid md:grid-cols-4 gap-6 items-end">
                                            <form method="POST" action="{{ route('projects.schedule.update', $project) }}" 
                                                  class="grid md:grid-cols-3 md:col-span-3 gap-6 items-end">
                                                @csrf
                                                @method('PUT')
                                                <!-- Photographer, Editor, Room fields (sama seperti atas, tapi disingkat untuk ruang) -->
                                                <div>
                                                    <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">Ubah Fotografer</label>
                                                    <select name="photographer_id" class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017]">
                                                        @foreach($photographers as $p)
                                                            <option value="{{ $p->id }}" @selected(optional($project->schedule)->photographer_id == $p->id) @disabled(in_array($p->id, $disabledPhotographers, true))>{{ $p->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">Ubah Editor</label>
                                                    <select name="editor_id" class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017]">
                                                        @foreach($editors as $e)
                                                            <option value="{{ $e->id }}" @selected(optional($project->schedule)->editor_id == $e->id) @disabled(in_array($e->id, $disabledEditors, true))>{{ $e->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">Ubah Ruangan</label>
                                                    <select name="studio_room_id" required class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017]">
                                                        @foreach(($project->booking->studioLocation->rooms ?? collect())->where('is_active', true) as $room)
                                                            <option value="{{ $room->id }}" @selected($project->booking->studio_room_id == $room->id)>{{ $room->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <button class="h-14 rounded-3xl bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold hover:shadow-xl transition-all flex items-center justify-center gap-3">
                                                    <i class="fa-solid fa-pen-to-square"></i> Simpan Perubahan
                                                </button>
                                            </form>
                                            
                                            <button type="button" @click="showDeleteSchedule = true"
                                                    class="h-14 rounded-3xl bg-red-600 text-white font-semibold hover:bg-red-700 transition-all flex items-center justify-center gap-3">
                                                <i class="fa-solid fa-trash"></i> Hapus Jadwal
                                            </button>
                                        </div>
                                    @else
                                        <p class="text-xs text-[#8B7359] mt-6 px-2">
                                            Jadwal tidak dapat diubah karena project sudah memasuki tahap produksi atau final.
                                        </p>
                                    @endif
                                @endif
                            @endif

                            @if($project->schedule)
                                <div class="mt-8 pt-6 border-t border-[#EDE0D0] text-sm text-[#7A5B3A]">
                                    <i class="fa-solid fa-clock text-[#D4A017]"></i>
                                    Terjadwal: {{ $project->schedule->start_at }} — {{ $project->schedule->end_at }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-20 bg-white/70 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl">
                        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-[#F4EDE4] flex items-center justify-center">
                            <i class="fa-solid fa-calendar-xmark text-5xl text-[#8B7359]"></i>
                        </div>
                        <p class="text-[#3F2B1B] text-xl font-medium">Belum ada project untuk dijadwalkan</p>
                        <p class="text-[#7A5B3A] mt-2">Project akan muncul setelah booking dikonfirmasi pembayarannya</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>