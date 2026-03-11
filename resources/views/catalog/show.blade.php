<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#7a5b3a] flex items-center gap-2">
                    <i class="fa-solid fa-box text-[#b58042]"></i>
                    Detail Paket
                </p>
                <h2 class="font-display font-bold text-3xl text-[#3f2b1b]">{{ $servicePackage->name }}</h2>
                <p class="text-sm text-[#7a5b3a] mt-1">
                    <i class="fa-solid fa-folder-open mr-1"></i>
                    Kategori: {{ $servicePackage->category->name ?? '-' }}
                </p>
            </div>
            <a href="{{ route('catalog.public') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-xl overflow-hidden">
            {{-- Hero Image --}}
            @if($servicePackage->overview_image)
                <div class="relative h-80 w-full overflow-hidden">
                    <img src="{{ Storage::url($servicePackage->overview_image) }}" alt="Overview" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 text-white">
                        <span class="px-3 py-1 bg-[#b58042] rounded-full text-xs font-semibold">Paket Unggulan</span>
                    </div>
                </div>
            @endif

            <div class="p-8">
                {{-- Price & Action --}}
                <div class="flex flex-wrap items-start justify-between gap-4 mb-8">
                    <div>
                        <p class="text-sm text-[#6f5134] mb-1">Harga Paket</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-4xl font-bold text-[#b58042]">Rp {{ number_format($servicePackage->price) }}</p>
                            @if($servicePackage->max_people)
                                <p class="text-sm text-[#7a5b3a]">/ {{ $servicePackage->max_people }} orang</p>
                            @endif
                        </div>
                    </div>
                    
                    @if(auth()->user()->role === \App\Enums\Role::CLIENT)
                        <a href="{{ route('bookings.create', ['package_id'=>$servicePackage->id]) }}"
                           class="group inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-calendar-check"></i>
                            Pesan Sekarang
                            <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    @endif
                </div>

                <div class="grid lg:grid-cols-2 gap-8">
                    {{-- Left Column --}}
                    <div class="space-y-6">
                        {{-- Description --}}
                        <div class="space-y-3">
                            <h3 class="font-display font-semibold text-xl text-[#3f2b1b] flex items-center gap-2">
                                <i class="fa-solid fa-file-lines text-[#b58042]"></i>
                                Deskripsi Paket
                            </h3>
                            <p class="text-[#6f5134] leading-relaxed">{{ $servicePackage->description }}</p>
                        </div>

                        {{-- Features --}}
                        @if($servicePackage->features && count($servicePackage->features) > 0)
                            <div class="space-y-3">
                                <h3 class="font-display font-semibold text-xl text-[#3f2b1b] flex items-center gap-2">
                                    <i class="fa-solid fa-star text-[#b58042]"></i>
                                    Yang Didapat
                                </h3>
                                <ul class="grid sm:grid-cols-2 gap-3">
                                    @foreach($servicePackage->features as $feature)
                                        <li class="flex items-start gap-2 text-sm text-[#6f5134]">
                                            <i class="fa-solid fa-circle-check text-[#b58042] mt-0.5"></i>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Right Column --}}
                    <div class="space-y-6">
                        {{-- Add-ons --}}
                        @if($servicePackage->addons && count($servicePackage->addons) > 0)
                            <div class="space-y-3">
                                <h3 class="font-display font-semibold text-xl text-[#3f2b1b] flex items-center gap-2">
                                    <i class="fa-solid fa-plus-circle text-[#b58042]"></i>
                                    Add-on Tersedia
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($servicePackage->addons as $addon)
                                        @php
                                            $addonLabel = is_array($addon) ? ($addon['label'] ?? '-') : $addon;
                                            $addonPrice = is_array($addon) ? (int) ($addon['price'] ?? 0) : 0;
                                        @endphp
                                        <span class="px-3 py-1.5 rounded-full bg-[#f1e5d8] border border-[#e3d5c4] text-[#6b4a2d] text-sm">
                                            <i class="fa-solid fa-plus mr-1"></i>
                                            {{ $addonLabel }}
                                            @if($addonPrice > 0)
                                                (Rp {{ number_format($addonPrice, 0, ',', '.') }})
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Terms --}}
                        @if($servicePackage->terms)
                            <div class="space-y-3">
                                <h3 class="font-display font-semibold text-xl text-[#3f2b1b] flex items-center gap-2">
                                    <i class="fa-solid fa-file-contract text-[#b58042]"></i>
                                    Syarat & Ketentuan
                                </h3>
                                <p class="text-sm text-[#6f5134] bg-[#fcf7f1] p-4 rounded-xl border border-[#e3d5c4]">
                                    {{ $servicePackage->terms }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Gallery --}}
                @if($servicePackage->gallery && count($servicePackage->gallery) > 0)
                    <div class="mt-8 pt-8 border-t border-[#e3d5c4]">
                        <h3 class="font-display font-semibold text-xl text-[#3f2b1b] flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-images text-[#b58042]"></i>
                            Galeri
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($servicePackage->gallery as $path)
                                @if(is_string($path))
                                    <div class="group relative rounded-xl overflow-hidden border border-[#e3d5c4] aspect-square">
                                        <img src="{{ Storage::url($path) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" alt="Galeri">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <i class="fa-solid fa-magnifying-glass-plus text-white text-2xl"></i>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
