@php
    use Illuminate\Support\Facades\Storage;
    $assets = $project->mediaAssets ?? collect();
    $selections = $project->selections ?? collect();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#7a5b3a] flex items-center gap-2">
                    <i class="fa-solid fa-images text-[#b58042]"></i>
                    Project #{{ $project->id }}
                </p>
                <h2 class="font-display font-bold text-3xl text-[#3f2b1b]">Galeri & Revisi</h2>
            </div>
            @php
                $backUrl = auth()->user()->role === \App\Enums\Role::CLIENT ? url('/bookings') : url('/admin/bookings');
            @endphp
            <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
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
            'REVIEW' => 'Menunggu Review Klien',
            'FINAL' => 'Foto hasil edit diunggah',
        ];
        $statusText = $statusMap[$project->status] ?? $project->status;
        
        $statusColors = [
            'DRAFT' => 'bg-gray-100 text-gray-700',
            'SCHEDULED' => 'bg-blue-100 text-blue-700',
            'SHOOT_DONE' => 'bg-purple-100 text-purple-700',
            'EDITING' => 'bg-orange-100 text-orange-700',
            'REVIEW' => 'bg-amber-100 text-amber-700',
            'FINAL' => 'bg-emerald-100 text-emerald-700',
        ];
        $statusColor = $statusColors[$project->status] ?? 'bg-gray-100 text-gray-700';
    @endphp

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Project Info Card --}}
        <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-lg p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#b58042]/20 to-[#8b5b2e]/20 flex items-center justify-center">
                        <i class="fa-solid fa-camera text-[#b58042] text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="font-display text-lg text-[#3f2b1b]">{{ $project->booking->package->name ?? '-' }}</h3>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                                {{ $statusText }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-4 text-sm text-[#6f5134]">
                            <span><i class="fa-solid fa-calendar mr-1"></i>{{ \Carbon\Carbon::parse($project->booking->booking_date)->format('d M Y') }}</span>
                            <span><i class="fa-solid fa-location-dot mr-1"></i>{{ $project->booking->location }}</span>
                            <span><i class="fa-solid fa-user mr-1"></i>Klien: {{ $project->booking->client->name ?? '-' }}</span>
                        </div>
                    </div>
                </div>
                
                @if($project->status === 'FINAL')
                    <div class="flex gap-2">
                        <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors">
                            <i class="fa-solid fa-download"></i>
                            Unduh Semua
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Gallery Grid --}}
        @if($assets->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($assets as $asset)
                    @php 
                        $url = Storage::url($asset->path);
                        $isSelected = $selections->pluck('media_asset_id')->contains($asset->id);
                        $assetTypeColors = [
                            'RAW' => 'border-amber-200 bg-amber-50',
                            'PREVIEW' => 'border-blue-200 bg-blue-50',
                            'WATERMARK' => 'border-purple-200 bg-purple-50',
                            'FINAL' => 'border-emerald-200 bg-emerald-50',
                        ];
                        $assetColor = $assetTypeColors[$asset->type] ?? 'border-gray-200 bg-gray-50';
                    @endphp
                    
                    <div class="group relative border {{ $assetColor }} rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all {{ $isSelected ? 'ring-2 ring-[#b58042]' : '' }}">
                        {{-- Asset Type Badge --}}
                        <div class="absolute top-2 left-2 z-10">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-white/90 backdrop-blur-sm shadow-sm">
                                {{ $asset->type }} v{{ $asset->version }}
                            </span>
                        </div>

                        {{-- Selected Badge --}}
                        @if($isSelected)
                            <div class="absolute top-2 right-2 z-10">
                                <span class="w-6 h-6 rounded-full bg-[#b58042] text-white flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            </div>
                        @endif

                        {{-- Image Preview --}}
                        @if(in_array($asset->type, ['PREVIEW','WATERMARK','FINAL']))
                            <div class="aspect-square w-full overflow-hidden">
                                <img src="{{ $url }}" alt="Preview" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            </div>
                        @else
                            <div class="aspect-square w-full bg-gradient-to-br from-[#f0e4d6] to-[#e3d5c4] flex items-center justify-center">
                                <i class="fa-solid fa-file-image text-4xl text-[#8b7359]"></i>
                            </div>
                        @endif

                        {{-- Actions Overlay --}}
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <a href="{{ $url }}" target="_blank" 
                               class="w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center text-[#b58042] hover:bg-white transition-colors">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ $url }}" download 
                               class="w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center text-[#b58042] hover:bg-white transition-colors">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </div>

                        {{-- Date Info --}}
                        <div class="p-3 text-xs text-[#6f5134] border-t border-inherit bg-white/50">
                            <i class="fa-solid fa-clock mr-1"></i>
                            {{ $asset->created_at->format('d M Y H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl">
                <i class="fa-solid fa-images text-5xl text-[#8b7359] mb-4 opacity-50"></i>
                <p class="text-[#6f5134] mb-2">Belum ada media dalam project ini</p>
                <p class="text-sm text-[#7a5b3a]">Media akan muncul setelah sesi foto selesai</p>
            </div>
        @endif

        {{-- Client Selection Form --}}
        @if(auth()->user()->role === \App\Enums\Role::CLIENT && $assets->isNotEmpty())
            <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-display text-xl text-[#3f2b1b] font-semibold flex items-center gap-2">
                            <i class="fa-solid fa-star text-[#b58042]"></i>
                            Seleksi Foto (Maksimal 5)
                        </h3>
                        <p class="text-sm text-[#7a5b3a] mt-1">Pilih foto favorit Anda untuk diedit</p>
                    </div>
                    <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $selections->count() >= 5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $selections->count() }}/5 Terpilih
                    </span>
                </div>

                <form method="POST" action="/projects/{{ $project->id }}/selections" class="flex flex-wrap gap-3">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <select name="media_asset_id" class="w-full px-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                            <option value="">Pilih foto...</option>
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ $selections->pluck('media_asset_id')->contains($asset->id) ? 'disabled' : '' }}>
                                    {{ $asset->type }} - Versi {{ $asset->version }} ({{ $asset->created_at->format('d M') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" 
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all"
                            {{ $selections->count() >= 5 ? 'disabled' : '' }}>
                        <i class="fa-solid fa-plus mr-2"></i>
                        Tambahkan ke Seleksi
                    </button>
                </form>

                @if($selections->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-[#e3d5c4]">
                        <p class="text-sm font-medium text-[#3f2b1b] mb-2">Foto Terpilih:</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($selections as $selection)
                                @php $asset = $assets->firstWhere('id', $selection->media_asset_id); @endphp
                                @if($asset)
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-[#b58042]/10 text-[#b58042] border border-[#b58042]/20 text-sm">
                                        {{ $asset->type }} v{{ $asset->version }}
                                        <form method="POST" action="/projects/{{ $project->id }}/selections/{{ $selection->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-1 text-[#b58042] hover:text-red-500">
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