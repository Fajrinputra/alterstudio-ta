<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-box text-[#D4A017]"></i>
                    Detail Paket
                </p>
                <h2 class="font-display font-bold text-4xl tracking-tighter text-[#3F2B1B]">
                    {{ $servicePackage->name }}
                </h2>
                <p class="text-sm text-[#7A5B3A] mt-1 flex items-center gap-2">
                    <i class="fa-solid fa-folder-open"></i>
                    Kategori: {{ $servicePackage->category->name ?? '-' }}
                </p>
            </div>
            <a href="{{ route('catalog.public') }}" 
               class="inline-flex items-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Katalog
            </a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-2xl overflow-hidden">
            
            {{-- Hero Image --}}
            @if($servicePackage->overview_image)
                <div class="relative h-[420px] w-full overflow-hidden">
                    <img src="{{ Storage::url($servicePackage->overview_image) }}" 
                         alt="{{ $servicePackage->name }}" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                    
                    <div class="absolute top-8 left-8">
                        <span class="px-6 py-2 bg-white/90 backdrop-blur-md text-[#3F2B1B] text-sm font-semibold rounded-3xl shadow">
                            Paket Unggulan
                        </span>
                    </div>
                    
                    <div class="absolute bottom-8 left-8 text-white">
                        <p class="text-4xl font-display font-bold tracking-tighter drop-shadow-md">
                            {{ $servicePackage->name }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="p-10 lg:p-12">
                
                <!-- Price & Action -->
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12">
                    <div>
                        <p class="text-sm uppercase tracking-widest text-[#8B7359]">Harga Paket</p>
                        <div class="flex items-baseline gap-3 mt-2">
                            <p class="text-5xl font-bold text-[#D4A017]">Rp {{ number_format($servicePackage->price) }}</p>
                            @if($servicePackage->max_people)
                                <p class="text-[#7A5B3A]">/ maks. {{ $servicePackage->max_people }} orang</p>
                            @endif
                        </div>
                    </div>
                    
                    @if(auth()->check() && auth()->user()->role === \App\Enums\Role::CLIENT)
                        <a href="{{ route('bookings.create', ['package_id' => $servicePackage->id]) }}"
                           class="group inline-flex items-center gap-4 px-10 py-5 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold text-lg shadow-xl shadow-[#D4A017]/40 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                            <i class="fa-solid fa-calendar-check"></i>
                            Pesan Sekarang
                            <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                        </a>
                    @endif
                </div>

                <div class="grid lg:grid-cols-2 gap-12">
                    
                    <!-- Left Column -->
                    <div class="space-y-10">
                        <!-- Description -->
                        <div>
                            <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3 mb-4">
                                <i class="fa-solid fa-file-lines text-[#D4A017]"></i>
                                Deskripsi Paket
                            </h3>
                            <p class="text-[#5C432C] leading-relaxed text-[17px]">
                                {{ $servicePackage->description }}
                            </p>
                        </div>

                        <!-- Features -->
                        @if($servicePackage->features && count($servicePackage->features) > 0)
                            <div>
                                <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3 mb-5">
                                    <i class="fa-solid fa-star text-[#D4A017]"></i>
                                    Yang Anda Dapatkan
                                </h3>
                                <ul class="grid sm:grid-cols-2 gap-y-4 gap-x-8">
                                    @foreach($servicePackage->features as $feature)
                                        <li class="flex items-start gap-3 text-[#5C432C]">
                                            <i class="fa-solid fa-circle-check text-[#D4A017] mt-1 flex-shrink-0"></i>
                                            <span class="leading-relaxed">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-10">
                        <!-- Add-ons -->
                        @if($servicePackage->addons && count($servicePackage->addons) > 0)
                            <div>
                                <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3 mb-5">
                                    <i class="fa-solid fa-plus-circle text-[#D4A017]"></i>
                                    Add-on Tersedia
                                </h3>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($servicePackage->addons as $addon)
                                        @php
                                            $addonLabel = is_array($addon) ? ($addon['label'] ?? '-') : $addon;
                                            $addonPrice = is_array($addon) ? (int) ($addon['price'] ?? 0) : 0;
                                            $addonUnit  = is_array($addon) ? trim((string) ($addon['unit'] ?? '')) : '';
                                        @endphp
                                        <div class="px-5 py-3 bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl text-sm text-[#5C432C]">
                                            <i class="fa-solid fa-plus mr-1 text-[#D4A017]"></i>
                                            {{ $addonLabel }}
                                            @if($addonPrice > 0)
                                                <span class="text-[#D4A017]"> (Rp {{ number_format($addonPrice) }})</span>
                                            @endif
                                            @if($addonUnit)
                                                <span class="text-[#8B7359]"> / {{ $addonUnit }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Terms -->
                        @if($servicePackage->terms)
                            <div>
                                <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3 mb-4">
                                    <i class="fa-solid fa-file-contract text-[#D4A017]"></i>
                                    Syarat & Ketentuan
                                </h3>
                                <div class="bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl p-7 text-[#5C432C] leading-relaxed">
                                    {{ $servicePackage->terms }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Gallery -->
                @if($servicePackage->gallery && count($servicePackage->gallery) > 0)
                    <div class="mt-16 pt-12 border-t border-[#EDE0D0]">
                        <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3 mb-8">
                            <i class="fa-solid fa-images text-[#D4A017]"></i>
                            Galeri Paket
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                            @foreach($servicePackage->gallery as $path)
                                @if(is_string($path) && $path)
                                    <div class="group relative aspect-square rounded-3xl overflow-hidden border border-[#EDE0D0] shadow-sm">
                                        <img src="{{ Storage::url($path) }}" 
                                             alt="Gallery" 
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-all flex items-end p-4">
                                            <i class="fa-solid fa-magnifying-glass-plus text-white text-xl"></i>
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