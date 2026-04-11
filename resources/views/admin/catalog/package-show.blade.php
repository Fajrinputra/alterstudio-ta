@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017] via-[#E07A5F] to-[#B56D3E] rounded-2xl blur-xl opacity-30"></div>
                    <div class="relative h-10 w-10 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white font-black text-3xl shadow-inner">
                        A
                    </div>
                </div>
                <div>
                    <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase flex items-center gap-2 font-medium">
                        <i class="fa-solid fa-box text-[#D4A017]"></i>
                        KATALOG LAYANAN
                    </p>
                    <h2 class="font-display text-4xl tracking-tighter font-bold text-[#3F2B1B] mt-1">
                        Detail Paket — <span class="font-medium bg-gradient-to-r from-[#D4A017] to-[#E07A5F] bg-clip-text text-transparent">{{ $package->name }}</span>
                    </h2>
                    <p class="text-[#7A5B3A] text-lg -mt-1">{{ $category->name }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-[#FAF6F0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            
            {{-- Main Package Card - Premium Hero Style --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/10 via-[#E07A5F]/10 to-transparent rounded-3xl blur-3xl opacity-70 group-hover:opacity-90 transition-all"></div>
                <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-10 shadow-2xl">
                    
                    {{-- Header dengan status & price --}}
                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 mb-10">
                        <div class="flex-1">
                            <h1 class="font-display text-5xl md:text-6xl tracking-tighter font-semibold text-[#3F2B1B] leading-none mb-4">
                                {{ $package->name }}
                            </h1>
                            <div class="flex items-baseline gap-4">
                                <div class="text-5xl font-light text-[#D4A017] tracking-tighter">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                                @if($package->max_people)
                                    <span class="inline-flex items-center gap-2 px-5 py-2 rounded-3xl bg-[#FAF6F0] text-[#5C432C] text-sm font-medium border border-[#E1D3C5]">
                                        <i class="fa-solid fa-users text-[#D4A017]"></i>
                                        Maks. {{ $package->max_people }} orang
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex flex-col items-end gap-3">
                            <span class="px-6 py-2 rounded-3xl text-sm font-semibold tracking-widest uppercase
                                {{ $package->is_active 
                                    ? 'bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white shadow-lg shadow-[#D4A017]/30' 
                                    : 'bg-red-100 text-red-700 border border-red-200' }}">
                                {{ $package->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                            <div class="text-xs text-[#8B7359] flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check"></i>
                                Premium Alter Studio
                            </div>
                        </div>
                    </div>

                    {{-- Overview Image - Full Bleed Aesthetic --}}
                    @if($package->overview_image)
                        <div class="mb-10 rounded-3xl overflow-hidden border border-[#EDE0D0] shadow-inner bg-[#FAF6F0]">
                            <img src="{{ Storage::url($package->overview_image) }}" 
                                 class="w-full h-auto max-h-[520px] object-cover transition-all duration-700 group-hover:scale-105" 
                                 alt="Overview {{ $package->name }}">
                            <div class="absolute bottom-6 right-6 px-5 py-2 bg-white/90 backdrop-blur-md rounded-3xl text-xs font-medium text-[#3F2B1B] flex items-center gap-2 shadow-xl">
                                <i class="fa-solid fa-camera-retro text-[#D4A017]"></i>
                                MOMEN PREMIUM
                            </div>
                        </div>
                    @endif

                    {{-- Description --}}
                    @if($package->description)
                        <div class="mb-10">
                            <h3 class="text-xs uppercase tracking-[1.5px] font-semibold text-[#8B7359] mb-4 flex items-center gap-3">
                                <span class="flex-1 h-px bg-gradient-to-r from-[#D4A017]"></span>
                                CERITA DI BALIK PAKET
                                <span class="flex-1 h-px bg-gradient-to-l from-[#D4A017]"></span>
                            </h3>
                            <p class="text-[#5C432C] leading-relaxed text-lg max-w-3xl">{{ $package->description }}</p>
                        </div>
                    @endif

                    {{-- Features & Add-ons - Split Modern Grid --}}
                    <div class="grid lg:grid-cols-2 gap-10 mb-10">
                        {{-- Yang Didapat --}}
                        @if($package->features && count($package->features) > 0)
                            <div class="space-y-6">
                                <h3 class="flex items-center gap-3 text-sm font-semibold uppercase tracking-widest text-[#D4A017]">
                                    <i class="fa-solid fa-gem"></i>
                                    YANG ANDA DAPATKAN
                                </h3>
                                <ul class="space-y-4">
                                    @foreach($package->features as $feature)
                                        <li class="flex items-start gap-4 text-[#3F2B1B]">
                                            <div class="mt-1 w-5 h-5 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white flex-shrink-0">
                                                <i class="fa-solid fa-check text-xs"></i>
                                            </div>
                                            <span class="leading-tight">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Add-ons --}}
                        @if($package->addons && count($package->addons) > 0)
                            <div class="space-y-6">
                                <h3 class="flex items-center gap-3 text-sm font-semibold uppercase tracking-widest text-[#E07A5F]">
                                    <i class="fa-solid fa-plus"></i>
                                    ADD-ONS YANG BISA DITAMBAHKAN
                                </h3>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($package->addons as $addon)
                                        @php
                                            $addonLabel = is_array($addon) ? ($addon['label'] ?? '-') : $addon;
                                            $addonPrice = is_array($addon) ? (int) ($addon['price'] ?? 0) : 0;
                                            $addonUnit = is_array($addon) ? trim((string) ($addon['unit'] ?? '')) : '';
                                        @endphp
                                        <div class="px-6 py-3 bg-white border border-[#EDE0D0] rounded-3xl flex items-center gap-3 text-sm hover:border-[#D4A017] transition-all group/addon">
                                            <span class="font-medium text-[#3F2B1B]">{{ $addonLabel }}</span>
                                            @if($addonPrice > 0)
                                                <span class="text-[#D4A017] font-semibold">+ Rp {{ number_format($addonPrice, 0, ',', '.') }}</span>
                                            @endif
                                            @if($addonUnit !== '')
                                                <span class="text-[#8B7359] text-xs">/{{ $addonUnit }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Syarat & Ketentuan - Elegant Box --}}
                    @if($package->terms)
                        <div class="p-8 bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl">
                            <h3 class="flex items-center gap-3 text-sm font-semibold uppercase tracking-widest text-[#8B7359] mb-5">
                                <i class="fa-solid fa-file-signature text-[#D4A017]"></i>
                                SYARAT & KETENTUAN
                            </h3>
                            <p class="text-[#5C432C] leading-relaxed">{{ $package->terms }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Gallery Section - Portfolio Style --}}
            @if(!empty($gallery) && count($gallery) > 0)
                <div class="relative">
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="font-display text-3xl tracking-tight text-[#3F2B1B] flex items-center gap-4">
                            <i class="fa-solid fa-images text-[#D4A017]"></i>
                            Galeri Paket
                        </h3>
                        <div class="text-sm font-medium text-[#8B7359] flex items-center gap-2">
                            <div class="w-2 h-2 bg-[#D4A017] rounded-full animate-pulse"></div>
                            {{ count($gallery) }} MOMEN TERABADIKAN
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach($gallery as $path)
                            <div class="group relative rounded-3xl overflow-hidden border border-[#EDE0D0] shadow-xl hover:shadow-2xl transition-all duration-500">
                                <div class="aspect-square">
                                    <img src="{{ Storage::url($path) }}" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" 
                                         alt="Galeri {{ $package->name }}">
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-[#3F2B1B]/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all flex items-end p-6">
                                    <div class="flex-1">
                                        <p class="text-white text-sm font-medium">{{ $package->name }}</p>
                                        <p class="text-[#E7D9C2] text-xs">{{ $category->name }}</p>
                                    </div>
                                </div>
                                <div class="absolute top-4 right-4 w-9 h-9 rounded-2xl bg-white/90 backdrop-blur flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all translate-y-3 group-hover:translate-y-0 shadow-lg">
                                    <i class="fa-solid fa-expand text-[#D4A017]"></i>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Action Buttons - Modern Floating Style --}}
            <div class="flex justify-end gap-4 pt-6 border-t border-[#EDE0D0]">
                <a href="{{ route('admin.packages.edit', $package) }}"
                   class="group inline-flex items-center gap-3 px-8 py-4 rounded-3xl border-2 border-[#E1D3C5] text-[#5C432C] hover:border-[#D4A017] hover:bg-white transition-all duration-300">
                    <i class="fa-solid fa-pen-to-square group-hover:rotate-12 transition-transform"></i>
                    <span class="font-semibold">Edit Paket</span>
                </a>
                
                <a href="{{ route('admin.catalog.packages', $category) }}"
                   class="inline-flex items-center gap-3 px-10 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl shadow-[#D4A017]/30 hover:shadow-2xl hover:-translate-y-1 transition-all">
                    KEMBALI
                </a>
            </div>
        </div>
    </div>
</x-app-layout>