@php
    use App\Enums\Role;
    use Illuminate\Support\Facades\Storage;
    $assets = $project->mediaAssets ?? collect();
    $selections = $project->selections ?? collect();
    $currentUser = auth()->user();
    $isCrewUser = $currentUser && $currentUser->isRole(Role::PHOTOGRAPHER, Role::EDITOR) && ! $currentUser->isRole(Role::ADMIN, Role::MANAGER, Role::CLIENT);
    $isPhotographerTask = $isCrewUser && $project->photographer_id === $currentUser->id;
    $isEditorTask = $isCrewUser && $project->editor_id === $currentUser->id;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-images text-[#D4A017]"></i>
                    Project #{{ $project->id }}
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B]">
                    Galeri &amp; Revisi
                </h2>
            </div>
            @php
                if ($currentUser?->isRole(\App\Enums\Role::CLIENT)) {
                    $backUrl = url('/bookings');
                } elseif ($isCrewUser) {
                    $backUrl = url('/admin/schedules');
                } else {
                    $backUrl = url('/admin/bookings');
                }
            @endphp
            <a href="{{ $backUrl }}" 
               class="inline-flex w-full sm:w-auto items-center justify-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] hover:shadow transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    @php
        $statusMap = [
            'DRAFT' => 'Belum dijadwalkan',
            'SCHEDULED' => 'Terjadwal',
            'SHOOT_DONE' => 'Sesi Foto Selesai',
            'EDITING' => 'Permintaan edit dikirimkan',
            'FINAL' => 'Foto hasil edit diunggah',
        ];
        $statusText = $statusMap[$project->status] ?? $project->status;
        
        $statusColors = [
            'DRAFT' => 'bg-gray-100 text-gray-700',
            'SCHEDULED' => 'bg-blue-100 text-blue-700',
            'SHOOT_DONE' => 'bg-purple-100 text-purple-700',
            'EDITING' => 'bg-orange-100 text-orange-700',
            'FINAL' => 'bg-emerald-100 text-emerald-700',
        ];
        $statusColor = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-700';
    @endphp

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-10 bg-[#FAF6F0]">
        
        {{-- Project Info Card Premium --}}
        <div class="relative group">
            <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/5 via-[#E07A5F]/5 rounded-3xl blur-3xl"></div>
            <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-8 shadow-xl">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div class="flex items-center gap-5">
                        <div class="w-16 h-16 rounded-3xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-camera text-[#D4A017] text-3xl"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-4 mb-3">
                                <h3 class="font-display text-2xl text-[#3F2B1B]">{{ $project->booking->package->name ?? '-' }}</h3>
                                <span class="px-5 py-2 rounded-3xl text-sm font-medium {{ $statusColor }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-[#7A5B3A]">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-calendar text-[#D4A017]"></i>
                                    {{ \Carbon\Carbon::parse($project->booking->booking_date)->format('d M Y') }}
                                </span>
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-location-dot text-[#D4A017]"></i>
                                    {{ $project->booking->location }}
                                </span>
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-store text-[#D4A017]"></i>
                                    {{ $project->booking->studioLocation->name ?? 'Cabang belum dipilih' }}
                                    @if($project->booking->studioRoom)
                                        — {{ $project->booking->studioRoom->name }}
                                    @endif
                                </span>
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-user text-[#D4A017]"></i>
                                    Klien: {{ $project->booking->client->name ?? '-' }}
                                </span>
                            </div>
                            
                            @if($isPhotographerTask || $isEditorTask)
                                <div class="flex flex-wrap gap-3 mt-5">
                                    @if($isPhotographerTask)
                                        <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-3xl bg-blue-100 text-blue-700 text-sm font-medium">
                                            <i class="fa-solid fa-camera"></i>
                                            Tugas Anda: Fotografer
                                        </span>
                                    @endif
                                    @if($isEditorTask)
                                        <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-3xl bg-orange-100 text-orange-700 text-sm font-medium">
                                            <i class="fa-solid fa-pen-ruler"></i>
                                            Tugas Anda: Editor
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($project->status === 'FINAL')
                        <a href="#" 
                           class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-emerald-500 to-emerald-600 text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-download"></i>
                            Unduh Semua Foto
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Gallery Grid Premium --}}
        @if($assets->isNotEmpty())
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-display text-2xl text-[#3F2B1B]">Galeri Media</h3>
                    <p class="text-sm text-[#7A5B3A]">{{ $assets->count() }} foto</p>
                </div>
                
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($assets as $asset)
                        @php
                            $url = Storage::url($asset->path);
                            $isSelected = $selections->pluck('media_asset_id')->contains($asset->id);
                            $assetTypeColors = [
                                'RAW' => 'border-amber-200 bg-amber-50',
                                'FINAL' => 'border-emerald-200 bg-emerald-50',
                            ];
                            $assetColor = $assetTypeColors[$asset->type] ?? 'border-gray-200 bg-gray-50';
                        @endphp
                        
                        <div class="group relative border {{ $assetColor }} rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 {{ $isSelected ? 'ring-2 ring-[#D4A017]' : '' }}">
                            {{-- Type & Version Badge --}}
                            <div class="absolute top-4 left-4 z-20">
                                <span class="px-4 py-1.5 text-xs font-semibold bg-white/95 backdrop-blur-md rounded-3xl shadow-sm border border-white">
                                    {{ $asset->type }} • v{{ $asset->version }}
                                </span>
                            </div>
                            
                            {{-- Selected Badge --}}
                            @if($isSelected)
                                <div class="absolute top-4 right-4 z-20">
                                    <div class="w-8 h-8 rounded-2xl bg-[#D4A017] text-white flex items-center justify-center shadow">
                                        <i class="fa-solid fa-check text-sm"></i>
                                    </div>
                                </div>
                            @endif
                            
                            {{-- Image --}}
                            @if(in_array($asset->type, ['RAW','FINAL']))
                                <div class="aspect-square w-full overflow-hidden">
                                    <img src="{{ $url }}" alt="Preview" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            @else
                                <div class="aspect-square w-full bg-gradient-to-br from-[#FAF6F0] to-[#E7D9C2] flex items-center justify-center">
                                    <i class="fa-solid fa-file-image text-6xl text-[#8B7359]/40"></i>
                                </div>
                            @endif
                            
                            {{-- Hover Actions --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center gap-4">
                                <a href="{{ $url }}" target="_blank"
                                   class="w-12 h-12 rounded-2xl bg-white/90 backdrop-blur flex items-center justify-center text-[#D4A017] hover:bg-white hover:scale-110 transition-all">
                                    <i class="fa-solid fa-eye text-xl"></i>
                                </a>
                                <a href="{{ $url }}" download
                                   class="w-12 h-12 rounded-2xl bg-white/90 backdrop-blur flex items-center justify-center text-[#D4A017] hover:bg-white hover:scale-110 transition-all">
                                    <i class="fa-solid fa-download text-xl"></i>
                                </a>
                            </div>
                            
                            {{-- Footer Info --}}
                            <div class="p-5 text-xs text-[#7A5B3A] bg-white/70 backdrop-blur border-t border-inherit flex items-center gap-2">
                                <i class="fa-solid fa-clock"></i>
                                {{ $asset->created_at->format('d M Y • H:i') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="text-center py-20 bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl">
                <div class="w-28 h-28 mx-auto mb-6 rounded-full bg-[#F4EDE4] flex items-center justify-center">
                    <i class="fa-solid fa-images text-6xl text-[#8B7359]/40"></i>
                </div>
                <p class="text-[#3F2B1B] text-xl font-medium">Belum ada media dalam project ini</p>
                <p class="text-[#7A5B3A] mt-2 max-w-md mx-auto">Media akan muncul setelah fotografer mengunggah hasil sesi foto</p>
            </div>
        @endif

        {{-- Client Selection Section --}}
        @if(auth()->user()->role === \App\Enums\Role::CLIENT && $assets->isNotEmpty())
            <div class="bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-8 shadow-xl">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                    <div>
                        <h3 class="font-display text-2xl text-[#3F2B1B] flex items-center gap-3">
                            <i class="fa-solid fa-star text-[#D4A017]"></i>
                            Seleksi Foto Favorit
                        </h3>
                        <p class="text-[#7A5B3A] mt-1">Pilih maksimal 5 foto terbaik untuk proses editing</p>
                    </div>
                    <div class="px-6 py-3 rounded-3xl text-sm font-semibold {{ $selections->count() >= 5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $selections->count() }}/5 Terpilih
                    </div>
                </div>

                <form method="POST" action="/projects/{{ $project->id }}/selections" class="flex flex-wrap gap-4">
                    @csrf
                    <div class="flex-1 min-w-[280px]">
                        <select name="media_asset_id" 
                                class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                            <option value="">Pilih foto untuk diseleksi...</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" 
                                        {{ $selections->pluck('media_asset_id')->contains($asset->id) ? 'disabled' : '' }}>
                                    {{ $asset->type }} — Versi {{ $asset->version }} ({{ $asset->created_at->format('d M') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="h-14 px-10 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all flex items-center gap-3"
                            {{ $selections->count() >= 5 ? 'disabled' : '' }}>
                        <i class="fa-solid fa-plus"></i>
                        Tambahkan ke Seleksi
                    </button>
                </form>

                @if($selections->isNotEmpty())
                    <div class="mt-10 pt-8 border-t border-[#EDE0D0]">
                        <p class="text-[#3F2B1B] font-medium mb-4">Foto yang sudah Anda pilih:</p>
                        <div class="flex flex-wrap gap-3">
                            @foreach($selections as $selection)
                                @php $asset = $assets->firstWhere('id', $selection->media_asset_id); @endphp
                                @if($asset)
                                    <span class="inline-flex items-center gap-3 px-6 py-3 rounded-3xl bg-[#D4A017]/10 border border-[#D4A017]/20 text-[#D4A017] font-medium">
                                        {{ $asset->type }} v{{ $asset->version }}
                                        <form method="POST" action="/projects/{{ $project->id }}/selections/{{ $selection->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-2 text-red-500 hover:text-red-600 transition-colors">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </form>
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>