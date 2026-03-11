<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-calendar-clock text-[#b58042]"></i>
                    Penjadwalan
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Jadwalkan <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">Fotografer & Editor</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Session Messages --}}
            @if(session('success'))
                <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                    <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Filter Form --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/20 to-[#8b5b2e]/20 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                    <form method="GET" class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-[#6f5134] mb-1 flex items-center gap-1">
                                <i class="fa-solid fa-box text-[#b58042]"></i>
                                Filter Paket
                            </label>
                            <select name="package_id" class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                <option value="">Semua Paket</option>
                                @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}" @selected(($packageFilter ?? null) == $pkg->id)>{{ $pkg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="flex gap-2">
                            <button class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center gap-2">
                                <i class="fa-solid fa-filter"></i>
                                Terapkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Project Cards --}}
            <div class="space-y-4">
                @forelse($projects as $project)
                    @php
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
                            'REVIEW'      => ['label' => 'Review Klien', 'color' => 'bg-amber-100 text-amber-700'],
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
                        <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/10 to-[#8b5b2e]/10 rounded-2xl blur-xl"></div>
                        <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all">
                            {{-- Header --}}
                            <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#b58042]/20 to-[#8b5b2e]/20 flex items-center justify-center">
                                        <span class="font-mono text-[#b58042] font-semibold">#{{ $project->booking_id }}</span>
                                    </div>
                                    <div>
                                        <h3 class="font-display text-xl text-[#3f2b1b]">{{ $project->booking->package->name ?? '-' }}</h3>
                                        <div class="flex flex-wrap items-center gap-3 text-sm text-[#6f5134] mt-1">
                                            <span><i class="fa-solid fa-user mr-1"></i>{{ $project->booking->client->name ?? '-' }}</span>
                                            <span>
                                                <i class="fa-solid fa-location-dot mr-1"></i>
                                                Cabang {{ $project->booking->studio_location_id ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ $statusBadge['color'] }}">
                                        {{ $statusBadge['label'] }}
                                    </span>
                                    @if($project->schedule)
                                        <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                            <i class="fa-solid fa-check mr-1"></i>Terjadwal
                                        </span>
                                        @if(!(isset($readOnly) && $readOnly))
                                            <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ $scheduleManageBadge['color'] }}">
                                                {{ $scheduleManageBadge['label'] }}
                                            </span>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- Schedule Info --}}
                            <div class="grid md:grid-cols-2 gap-4 mb-4 p-4 bg-white/50 rounded-xl">
                                <div>
                                    <p class="text-xs text-[#8b7359] mb-1">Jadwal Pemesanan</p>
                                    <p class="text-sm text-[#3f2b1b]">
                                        <i class="fa-solid fa-calendar mr-2 text-[#b58042]"></i>
                                        {{ $startText }} s/d {{ $endText }}
                                    </p>
                                    <p class="text-xs text-[#6f5134] mt-1">Durasi: {{ $duration }} menit</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#8b7359] mb-1">Status Pembayaran    </p>
                                    <p class="text-sm text-[#3f2b1b]">{{ $bookingStatus }}</p>
                                </div>
                            </div>

                            @if(isset($readOnly) && $readOnly)
                                {{-- Read Only View untuk Staff --}}
                                <div class="grid md:grid-cols-2 gap-4 mb-4 p-4 bg-white/50 rounded-xl">
                                    <div>
                                        <p class="text-xs text-[#8b7359] mb-1">Fotografer</p>
                                        <p class="text-sm text-[#3f2b1b] font-medium">
                                            <i class="fa-solid fa-camera mr-2 text-[#b58042]"></i>
                                            {{ optional($project->schedule?->photographer)->name ?? '-' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-[#8b7359] mb-1">Editor</p>
                                        <p class="text-sm text-[#3f2b1b] font-medium">
                                            <i class="fa-solid fa-pen-ruler mr-2 text-[#b58042]"></i>
                                            {{ optional($project->schedule?->editor)->name ?? '-' }}
                                        </p>
                                    </div>
                                </div>

                                @if($project->schedule)
                                    <div class="border-t border-[#e3d5c4] pt-4 mt-4">
                                        @if($isPhotographer)
                                            {{-- Upload RAW untuk Fotografer --}}
                                            <div class="space-y-3">
                                                <h4 class="font-medium text-[#3f2b1b] flex items-center gap-2">
                                                    <i class="fa-solid fa-cloud-upload text-[#b58042]"></i>
                                                    Unggah Hasil Sesi Foto
                                                </h4>
                                                
                                                @if($rawAssets->isEmpty())
                                                    <p class="text-sm text-[#6f5134]">Bisa unggah banyak file (max 50). Status akan berubah ke “Sesi Foto Selesai”.</p>
                                                    <form method="POST" action="/projects/{{ $project->id }}/assets" enctype="multipart/form-data" class="mt-3">
                                                        @csrf
                                                        <input type="hidden" name="type" value="RAW">
                                                        
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex-1">
                                                                <input type="file" name="files[]" multiple accept="image/*" 
                                                                       class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer"
                                                                       required>
                                                            </div>
                                                            <button class="px-4 py-2 rounded-lg bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white text-sm font-semibold hover:shadow-lg transition-all">
                                                                <i class="fa-solid fa-upload mr-1"></i>
                                                                Unggah
                                                            </button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <div class="space-y-2">
                                                        <p class="text-sm text-emerald-600 flex items-center gap-1">
                                                            <i class="fa-solid fa-circle-check"></i>
                                                            RAW sudah diunggah ({{ $rawAssets->count() }} file)
                                                        </p>
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($rawAssets as $asset)
                                                                <a href="{{ Storage::url($asset->path) }}" target="_blank"
                                                                   class="px-3 py-1.5 rounded-lg bg-white border border-[#e3d5c4] hover:border-[#b58042] text-xs text-[#3f2b1b] flex items-center gap-1">
                                                                    <i class="fa-solid fa-file-image text-[#b58042]"></i>
                                                                    D{{ $asset->version }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        @if($isEditor)
                                            {{-- Upload Final untuk Editor --}}
                                            <div class="space-y-4">
                                                <div>
                                                    <h4 class="font-medium text-[#3f2b1b] flex items-center gap-2 mb-2">
                                                        <i class="fa-solid fa-images text-[#b58042]"></i>
                                                        Permintaan Edit
                                                    </h4>
                                                    @php
                                                        $selected = $project->selections->map(function($sel){
                                                            if($sel->mediaAsset && $sel->mediaAsset->type === 'RAW'){
                                                                return 'D'.$sel->mediaAsset->version;
                                                            }
                                                            return null;
                                                        })->filter()->values();
                                                    @endphp
                                                    <p class="text-sm text-[#6f5134] bg-white/50 p-3 rounded-lg">
                                                        <span class="font-medium">Kode foto terpilih:</span>
                                                        {{ $selected->isNotEmpty() ? $selected->join(', ') : 'Belum ada pilihan klien.' }}
                                                    </p>
                                                </div>

                                                @if($finalAssets->isEmpty())
                                                    <div class="space-y-2">
                                                        <h4 class="font-medium text-[#3f2b1b] flex items-center gap-2">
                                                            <i class="fa-solid fa-cloud-upload text-[#b58042]"></i>
                                                            Unggah Hasil Akhir
                                                        </h4>
                                                        <p class="text-sm text-[#6f5134]">Bisa unggah banyak file sekaligus.</p>
                                                        <form method="POST" action="/projects/{{ $project->id }}/assets" enctype="multipart/form-data">
                                                            @csrf
                                                            <input type="hidden" name="type" value="FINAL">
                                                            
                                                            <div class="flex items-center gap-3">
                                                                <div class="flex-1">
                                                                    <input type="file" name="files[]" multiple accept="image/*" 
                                                                           class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer"
                                                                           required>
                                                                </div>
                                                                <button class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-600 to-emerald-700 text-white text-sm font-semibold hover:shadow-lg transition-all">
                                                                    <i class="fa-solid fa-upload mr-1"></i>
                                                                    Unggah Final
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                @else
                                                    <div class="space-y-2">
                                                        <h4 class="font-medium text-[#3f2b1b] flex items-center gap-2">
                                                            <i class="fa-solid fa-check-circle text-emerald-600"></i>
                                                            Foto Final Terkirim
                                                        </h4>
                                                        <div class="grid sm:grid-cols-3 gap-3">
                                                            @foreach($finalAssets as $asset)
                                                                <a href="{{ Storage::url($asset->path) }}" target="_blank"
                                                                   class="block border border-[#e3d5c4] rounded-xl overflow-hidden hover:shadow-lg transition-all">
                                                                    <img src="{{ Storage::url($asset->path) }}" class="w-full h-32 object-cover" alt="Final">
                                                                    <div class="px-3 py-2 text-xs text-[#5b422b] bg-white/50">
                                                                        Versi {{ $asset->version }}
                                                                    </div>
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <p class="text-xs text-[#8b7359] mt-4 flex items-center gap-1">
                                            <i class="fa-solid fa-circle-info"></i>
                                            File otomatis terhapus setelah 5 hari untuk menghemat storage.
                                        </p>
                                    </div>
                                @endif

                            @else
                                {{-- Admin View --}}
                                @if(!$project->schedule)
                                    <form method="POST" action="/projects/{{ $project->id }}/schedule" class="grid md:grid-cols-3 gap-4 items-end">
                                        @csrf
                                        <div>
                                            <label class="block text-xs font-medium text-[#6f5134] mb-1">
                                                <i class="fa-solid fa-camera mr-1 text-[#b58042]"></i>
                                                Fotografer
                                            </label>
                                            <select name="photographer_id" class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                                <option value="">Pilih Fotografer</option>
                                                @foreach($photographers as $p)
                                                    <option value="{{ $p->id }}" @selected(optional($project->schedule)->photographer_id == $p->id)>{{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-[#6f5134] mb-1">
                                                <i class="fa-solid fa-pen-ruler mr-1 text-[#b58042]"></i>
                                                Editor
                                            </label>
                                            <select name="editor_id" class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                                <option value="">Pilih Editor</option>
                                                @foreach($editors as $e)
                                                    <option value="{{ $e->id }}" @selected(optional($project->schedule)->editor_id == $e->id)>{{ $e->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <button class="w-full px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2"
                                                    @if(!$canSchedule) disabled @endif>
                                                <i class="fa-solid fa-calendar-check"></i>
                                                {{ $canSchedule ? 'Simpan Jadwal' : 'Tunggu Pembayaran' }}
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="space-y-3">
                                        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-emerald-200 flex items-center justify-center">
                                                    <i class="fa-solid fa-check text-emerald-700"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-emerald-800">Jadwal Sudah Tersimpan</p>
                                                    <p class="text-xs text-emerald-600">
                                                        Fotografer: {{ $project->schedule->photographer->name ?? '-' }} |
                                                        Editor: {{ $project->schedule->editor->name ?? '-' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        @if($canManageSchedule)
                                            <div class="grid md:grid-cols-4 gap-4 items-end">
                                                <form method="POST" action="{{ route('projects.schedule.update', $project) }}" class="grid md:grid-cols-3 md:col-span-3 gap-4 items-end">
                                                    @csrf
                                                    @method('PUT')
                                                    <div>
                                                        <label class="block text-xs font-medium text-[#6f5134] mb-1">
                                                            <i class="fa-solid fa-camera mr-1 text-[#b58042]"></i>
                                                            Ubah Fotografer
                                                        </label>
                                                        <select name="photographer_id" class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                                            @foreach($photographers as $p)
                                                                <option value="{{ $p->id }}" @selected(optional($project->schedule)->photographer_id == $p->id)>{{ $p->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-medium text-[#6f5134] mb-1">
                                                            <i class="fa-solid fa-pen-ruler mr-1 text-[#b58042]"></i>
                                                            Ubah Editor
                                                        </label>
                                                        <select name="editor_id" class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                                            @foreach($editors as $e)
                                                                <option value="{{ $e->id }}" @selected(optional($project->schedule)->editor_id == $e->id)>{{ $e->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <button class="w-full px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold hover:shadow-lg transition-all flex items-center justify-center gap-2">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                            Simpan Perubahan
                                                        </button>
                                                    </div>
                                                </form>
                                                <form method="POST" action="{{ route('projects.schedule.destroy', $project) }}" onsubmit="return confirm('Hapus jadwal ini? Hanya bisa jika project belum berjalan.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="w-full px-6 py-2.5 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition-all flex items-center justify-center gap-2">
                                                        <i class="fa-solid fa-trash"></i>
                                                        Hapus Jadwal
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <p class="text-xs text-[#8b7359] px-1">
                                                Jadwal tidak dapat diubah/hapus karena project sudah berjalan atau sudah ada aset yang diunggah.
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            @if($project->schedule)
                                <p class="text-sm text-[#6f5134] mt-3 pt-3 border-t border-[#e3d5c4]">
                                    <i class="fa-solid fa-clock mr-1 text-[#b58042]"></i>
                                    Terjadwal: {{ $project->schedule->start_at }} s/d {{ $project->schedule->end_at }}
                                </p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl">
                        <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-[#f0e4d6] flex items-center justify-center">
                            <i class="fa-solid fa-calendar-xmark text-3xl text-[#8b7359]"></i>
                        </div>
                        <p class="text-[#6f5134] text-lg">Belum ada project untuk dijadwalkan</p>
                        <p class="text-sm text-[#8b7359] mt-1">Project akan muncul setelah booking dikonfirmasi</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
