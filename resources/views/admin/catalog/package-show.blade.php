@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-box text-[#b58042]"></i>
                    Katalog Layanan
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Detail Paket - <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">{{ $package->name }}</span>
                </h2>
                <p class="text-sm text-[#8b7359] mt-1">{{ $category->name }}</p>
            </div>
            <a href="{{ route('admin.catalog.packages', $category) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Main Package Card --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/20 to-[#8b5b2e]/20 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-8 shadow-lg">
                    
                    {{-- Header dengan status --}}
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h1 class="font-display text-3xl text-[#3f2b1b] mb-2">{{ $package->name }}</h1>
                            <div class="flex items-center gap-3">
                                <span class="text-2xl font-light text-[#b58042]">Rp {{ number_format($package->price) }}</span>
                                @if($package->max_people)
                                    <span class="text-sm text-[#6f5134] flex items-center gap-1">
                                        <i class="fa-solid fa-user"></i>
                                        Max {{ $package->max_people }} orang
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-xs font-medium {{ $package->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    {{-- Overview Image --}}
                    @if($package->overview_image)
                        <div class="mb-6 rounded-xl overflow-hidden border border-[#e3d5c4] bg-[#fdf8f2]">
                            <img src="{{ Storage::url($package->overview_image) }}" class="w-full max-h-[500px] object-contain bg-[#fdf8f2]" alt="Overview paket">
                        </div>
                    @endif

                    {{-- Description --}}
                    @if($package->description)
                        <div class="mb-6">
                            <h3 class="text-sm font-medium text-[#8b7359] uppercase tracking-wider mb-2">Deskripsi</h3>
                            <p class="text-[#6f5134] leading-relaxed">{{ $package->description }}</p>
                        </div>
                    @endif

                    {{-- Features Grid --}}
                    <div class="grid lg:grid-cols-2 gap-6 mb-6">
                        {{-- Yang Didapat --}}
                        @if($package->features && count($package->features) > 0)
                            <div>
                                <h3 class="text-sm font-medium text-[#8b7359] uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-star text-[#b58042]"></i>
                                    Yang Didapat
                                </h3>
                                <ul class="space-y-2">
                                    @foreach($package->features as $feature)
                                        <li class="flex items-start gap-2 text-sm text-[#5b422b]">
                                            <i class="fa-solid fa-circle-check text-[#b58042] mt-0.5"></i>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Add-ons --}}
                        @if($package->addons && count($package->addons) > 0)
                            <div>
                                <h3 class="text-sm font-medium text-[#8b7359] uppercase tracking-wider mb-3 flex items-center gap-2">
                                    <i class="fa-solid fa-plus-circle text-[#b58042]"></i>
                                    Add-ons
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($package->addons as $addon)
                                        @php
                                            $addonLabel = is_array($addon) ? ($addon['label'] ?? '-') : $addon;
                                            $addonPrice = is_array($addon) ? (int) ($addon['price'] ?? 0) : 0;
                                        @endphp
                                        <span class="px-3 py-1.5 rounded-full bg-[#f1e5d8] border border-[#e3d5c4] text-[#6b4a2d] text-sm">
                                            {{ $addonLabel }}
                                            @if($addonPrice > 0)
                                                (Rp {{ number_format($addonPrice, 0, ',', '.') }})
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Syarat & Ketentuan --}}
                    @if($package->terms)
                        <div class="mb-6 p-4 bg-[#fcf7f1] rounded-xl border border-[#e3d5c4]">
                            <h3 class="text-sm font-medium text-[#8b7359] uppercase tracking-wider mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-file-contract text-[#b58042]"></i>
                                Syarat & Ketentuan
                            </h3>
                            <p class="text-sm text-[#6f5134]">{{ $package->terms }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Gallery Section --}}
            @if(!empty($gallery) && count($gallery) > 0)
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/10 to-[#8b5b2e]/10 rounded-2xl blur-xl"></div>
                    <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-8 shadow-lg">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-light text-[#3f2b1b] flex items-center gap-2">
                                <i class="fa-solid fa-images text-[#b58042]"></i>
                                Galeri Foto
                            </h3>
                            <p class="text-xs text-[#8b7359]">{{ count($gallery) }} foto</p>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($gallery as $path)
                                <div class="group/img relative aspect-square rounded-xl overflow-hidden border border-[#e3d5c4] bg-white shadow-md hover:shadow-lg transition-all">
                                    <img src="{{ Storage::url($path) }}" class="w-full h-full object-cover group-hover/img:scale-110 transition-transform duration-500" alt="Foto paket">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover/img:opacity-100 transition-opacity flex items-end justify-end p-2">
                                        <a href="{{ Storage::url($path) }}" target="_blank" 
                                           class="w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center text-[#b58042] hover:bg-white transition-colors">
                                            <i class="fa-solid fa-magnifying-glass-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.packages.edit', $package) }}" 
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Paket
                </a>
                <a href="{{ route('admin.catalog.packages', $category) }}" 
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-check"></i>
                    Selesai
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
