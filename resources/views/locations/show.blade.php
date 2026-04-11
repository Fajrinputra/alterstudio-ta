@php use Illuminate\Support\Facades\Storage; @endphp
<x-guest-layout>
    <div class="min-h-screen bg-[#FAF6F0] py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            {{-- Back Button - Lebih Modern --}}
            <a href="{{ url('/') }}#studio" 
               class="inline-flex items-center gap-3 text-[#5C432C] hover:text-[#D4A017] mb-8 group transition-all">
                <div class="w-9 h-9 flex items-center justify-center rounded-2xl border border-[#E1D3C5] group-hover:border-[#D4A017] transition-colors">
                    <i class="fa-solid fa-arrow-left"></i>
                </div>
                <span class="font-medium">Kembali ke Beranda</span>
            </a>

            {{-- Hero Section - Lebih Dramatic --}}
            <div class="relative rounded-3xl overflow-hidden h-[420px] lg:h-[480px] shadow-2xl mb-10">
                @if($photos && count($photos) > 0)
                    <img src="{{ Storage::url($photos[0]) }}" 
                         alt="{{ $location->name }}" 
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-[#3F2B1B] to-[#5C432C] flex items-center justify-center">
                        <i class="fa-solid fa-store text-8xl text-white/20"></i>
                    </div>
                @endif
                
                <!-- Overlay Gradient -->
                <div class="absolute inset-0 bg-gradient-to-t from-[#3F2B1B]/80 via-[#3F2B1B]/40 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-[#3F2B1B]/60 via-transparent to-transparent"></div>

                <div class="absolute bottom-0 left-0 right-0 p-8 lg:p-12 text-white">
                    <div class="flex items-center gap-3 text-[#E7D9C2] mb-3">
                        <i class="fa-solid fa-location-dot text-xl"></i>
                        <span class="text-lg">{{ $location->address }}</span>
                    </div>
                    
                    <h1 class="font-display font-bold text-5xl lg:text-6xl tracking-tighter mb-4">
                        {{ $location->name }}
                    </h1>
                    
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="px-5 py-2 rounded-full text-sm font-medium flex items-center gap-2
                                   {{ $location->is_active ? 'bg-emerald-500/90' : 'bg-red-500/90' }}">
                            <i class="fa-solid fa-circle text-xs"></i>
                            {{ $location->is_active ? 'Sedang Aktif' : 'Tidak Aktif' }}
                        </span>
                        
                        @if($location->map_url)
                            <a href="{{ $location->map_url }}" target="_blank"
                               class="inline-flex items-center gap-3 px-6 py-3 rounded-3xl bg-white/20 backdrop-blur-md hover:bg-white/30 transition-all border border-white/30">
                                <i class="fa-solid fa-map"></i>
                                <span>Lihat di Google Maps</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-12 gap-8">
                
                {{-- Main Content - Left Side --}}
                <div class="lg:col-span-8 space-y-10">
                    
                    {{-- Description --}}
                    <div class="bg-white rounded-3xl shadow-xl border border-[#EDE0D0] p-8 lg:p-10">
                        <h2 class="font-display text-3xl text-[#3F2B1B] mb-6 flex items-center gap-3">
                            <i class="fa-solid fa-file-lines text-[#D4A017]"></i>
                            Tentang Studio
                        </h2>
                        <p class="text-[#5C432C] leading-relaxed text-[17px]">
                            {{ $location->description ?? 'Belum ada deskripsi untuk studio ini.' }}
                        </p>
                    </div>

                    {{-- Gallery - Lebih Cantik --}}
                    @if($photos && count($photos) > 0)
                        <div class="bg-white rounded-3xl shadow-xl border border-[#EDE0D0] p-8 lg:p-10">
                            <h2 class="font-display text-3xl text-[#3F2B1B] mb-8 flex items-center gap-3">
                                <i class="fa-solid fa-images text-[#D4A017]"></i>
                                Galeri Studio
                            </h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-5">
                                @foreach($photos as $photo)
                                    <div class="group relative rounded-2xl overflow-hidden border border-[#EDE0D0] aspect-square shadow-md hover:shadow-2xl transition-all duration-500">
                                        <img src="{{ Storage::url($photo) }}" 
                                             alt="Galeri {{ $location->name }}"
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-6">
                                            <i class="fa-solid fa-magnifying-glass-plus text-white text-4xl drop-shadow-lg"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar - Right Side --}}
                <div class="lg:col-span-4 space-y-8">
                    
                    {{-- Informasi Card --}}
                    <div class="bg-white rounded-3xl shadow-xl border border-[#EDE0D0] p-8">
                        <h3 class="font-display text-2xl text-[#3F2B1B] mb-6 flex items-center gap-3">
                            <i class="fa-solid fa-circle-info text-[#D4A017]"></i>
                            Informasi Kontak
                        </h3>
                        
                        <div class="space-y-6">
                            @if($location->phone)
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-[#FAF6F0] flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-phone text-2xl text-[#D4A017]"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-[#7A5B3A]">Telepon</p>
                                        <p class="font-semibold text-[#3F2B1B]">{{ $location->phone }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($location->email)
                                <div class="flex gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-[#FAF6F0] flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-envelope text-2xl text-[#D4A017]"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-[#7A5B3A]">Email</p>
                                        <p class="font-semibold text-[#3F2B1B]">{{ $location->email }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="flex gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-[#FAF6F0] flex items-center justify-center flex-shrink-0">
                                    <i class="fa-solid fa-clock text-2xl text-[#D4A017]"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-[#7A5B3A]">Jam Operasional</p>
                                    <p class="font-semibold text-[#3F2B1B]">
                                        {{ $location->operating_hours ?? '09:00 - 21:00' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Rooms Card --}}
                    @if($location->rooms && $location->rooms->count())
                        <div class="bg-white rounded-3xl shadow-xl border border-[#EDE0D0] p-8">
                            <h3 class="font-display text-2xl text-[#3F2B1B] mb-6 flex items-center gap-3">
                                <i class="fa-solid fa-door-open text-[#D4A017]"></i>
                                Ruangan Tersedia
                            </h3>
                            
                            <div class="space-y-4">
                                @foreach($location->rooms as $room)
                                    <div class="flex items-center justify-between p-5 bg-[#FAF6F0] rounded-2xl hover:bg-[#F4EDE4] transition-colors">
                                        <div>
                                            <p class="font-semibold text-[#3F2B1B]">{{ $room->name }}</p>
                                            @if($room->capacity)
                                                <p class="text-sm text-[#7A5B3A]">Kapasitas: {{ $room->capacity }} orang</p>
                                            @endif
                                        </div>
                                        <span class="px-4 py-1.5 text-xs font-medium bg-emerald-100 text-emerald-700 rounded-full">
                                            Tersedia
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Call to Action Card --}}
                    <div class="bg-gradient-to-br from-[#D4A017] via-[#E07A5F] to-[#B56D3E] rounded-3xl p-8 text-white shadow-xl">
                        <h3 class="font-display text-3xl mb-3">Siap Abadikan Momen?</h3>
                        <p class="text-white/90 mb-8 leading-relaxed">
                            Pilih paket favorit Anda dan booking sesi foto di studio ini sekarang.
                        </p>
                        <a href="{{ route('catalog.public') }}" 
                           class="block w-full py-4 text-center bg-white text-[#3F2B1B] font-semibold rounded-2xl hover:bg-[#F4EDE4] transition-all shadow-lg">
                            Lihat Semua Paket
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-guest-layout>