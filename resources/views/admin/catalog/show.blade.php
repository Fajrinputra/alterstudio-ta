<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-box text-[#D4A017]"></i>
                    Detail Paket
                </p>
                <h2 class="font-display text-2xl font-bold tracking-tight text-[#3F2B1B] sm:text-4xl sm:tracking-tighter">
                    {{ $servicePackage->name }}
                </h2>
                <p class="text-sm text-[#7A5B3A] mt-1 flex items-center gap-2">
                    <i class="fa-solid fa-folder-open"></i>
                    Kategori: {{ $servicePackage->category->name ?? '-' }}
                </p>
            </div>
            <a href="{{ route('catalog.public') }}" 
               class="inline-flex w-full items-center justify-center gap-3 rounded-3xl border border-[#E1D3C5] px-5 py-3 text-[#5C432C] transition-all hover:bg-white hover:shadow-md sm:w-auto sm:px-6">
                Kembali ke Katalog
            </a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-5 px-0 sm:px-6 sm:py-10 lg:px-8">
        <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-2xl overflow-hidden">
            
            {{-- Hero Image --}}
            @if($servicePackage->overview_image)
                <div class="relative h-52 w-full overflow-hidden sm:h-[420px]">
                    <img src="{{ Storage::url($servicePackage->overview_image) }}" 
                         alt="{{ $servicePackage->name }}" 
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                    
                    <div class="absolute left-4 top-4 sm:left-8 sm:top-8">
                        <span class="rounded-3xl bg-white/90 px-4 py-2 text-xs font-semibold text-[#3F2B1B] shadow backdrop-blur-md sm:px-6 sm:text-sm">
                            Paket Unggulan
                        </span>
                    </div>
                    
                    <div class="absolute bottom-5 left-4 text-white sm:bottom-8 sm:left-8">
                        <p class="font-display text-2xl font-bold tracking-tight drop-shadow-md sm:text-4xl sm:tracking-tighter">
                            {{ $servicePackage->name }}
                        </p>
                    </div>
                </div>
            @endif

            <div class="p-4 sm:p-8 lg:p-12">
                
                <!-- Price & Action -->
                <div class="mb-8 flex flex-col justify-between gap-5 lg:mb-12 lg:flex-row lg:items-end lg:gap-6">
                    <div>
                        <p class="text-sm uppercase tracking-widest text-[#8B7359]">Harga Paket</p>
                        <div class="flex items-baseline gap-3 mt-2">
                            <p class="text-3xl font-bold text-[#D4A017] sm:text-5xl">Rp {{ number_format($servicePackage->price) }}</p>
                            @if($servicePackage->max_people)
                                <p class="text-[#7A5B3A]">/ maks. {{ $servicePackage->max_people }} orang</p>
                            @endif
                        </div>
                    </div>
                    
                    @if(auth()->check() && auth()->user()->role === \App\Enums\Role::CLIENT)
                        <a href="{{ route('bookings.create', ['package_id' => $servicePackage->id]) }}"
                           class="group inline-flex w-full items-center justify-center gap-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] px-7 py-3.5 text-base font-semibold text-white shadow-xl shadow-[#D4A017]/40 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl sm:w-auto sm:gap-4 sm:px-10 sm:py-5 sm:text-lg">
                            <i class="fa-solid fa-calendar-check"></i>
                            Pesan Sekarang
                            <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                        </a>
                    @endif
                </div>

                <div class="grid gap-8 lg:grid-cols-2 lg:gap-12">
                    
                    <!-- Left Column -->
                    <div class="space-y-7 sm:space-y-10">
                        <!-- Description -->
                        <div>
                            <h3 class="mb-3 flex items-center gap-3 font-display text-xl font-semibold text-[#3F2B1B] sm:mb-4 sm:text-2xl">
                                <i class="fa-solid fa-file-lines text-[#D4A017]"></i>
                                Deskripsi Paket
                            </h3>
                            <p class="text-sm leading-relaxed text-[#5C432C] sm:text-[17px]">
                                {{ $servicePackage->description }}
                            </p>
                        </div>

                        <!-- Features -->
                        @if($servicePackage->features && count($servicePackage->features) > 0)
                            <div>
                                <h3 class="mb-4 flex items-center gap-3 font-display text-xl font-semibold text-[#3F2B1B] sm:mb-5 sm:text-2xl">
                                    <i class="fa-solid fa-star text-[#D4A017]"></i>
                                    Yang Anda Dapatkan
                                </h3>
                                <ul class="grid gap-x-8 gap-y-2 text-sm sm:grid-cols-2 sm:gap-y-4 sm:text-base">
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
                    <div class="space-y-7 sm:space-y-10">
                        <!-- Add-ons -->
                        @if($servicePackage->addons && count($servicePackage->addons) > 0)
                            <div>
                                <h3 class="mb-4 flex items-center gap-3 font-display text-xl font-semibold text-[#3F2B1B] sm:mb-5 sm:text-2xl">
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
                                        <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] px-4 py-2.5 text-xs text-[#5C432C] sm:px-5 sm:py-3 sm:text-sm">
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
                                <h3 class="mb-3 flex items-center gap-3 font-display text-xl font-semibold text-[#3F2B1B] sm:mb-4 sm:text-2xl">
                                    <i class="fa-solid fa-file-contract text-[#D4A017]"></i>
                                    Syarat & Ketentuan
                                </h3>
                                <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-4 text-sm leading-relaxed text-[#5C432C] sm:p-7 sm:text-base">
                                    {{ $servicePackage->terms }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Gallery -->
                @if($servicePackage->gallery && count($servicePackage->gallery) > 0)
                    <div class="mt-8 border-t border-[#EDE0D0] pt-8 sm:mt-16 sm:pt-12">
                        <h3 class="mb-5 flex items-center gap-3 font-display text-xl font-semibold text-[#3F2B1B] sm:mb-8 sm:text-2xl">
                            <i class="fa-solid fa-images text-[#D4A017]"></i>
                            Galeri Paket
                        </h3>
                        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 sm:gap-6">
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
