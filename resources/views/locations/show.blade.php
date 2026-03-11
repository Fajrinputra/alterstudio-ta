@php use Illuminate\Support\Facades\Storage; @endphp
<x-guest-layout>
    <div class="min-h-screen bg-gradient-to-br from-[#F8F1E7] to-[#f0e4d5] py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Back Button --}}
            <a href="{{ url('/') }}#studio" class="inline-flex items-center gap-2 text-[#b58042] hover:text-[#8b5b2e] mb-6 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Beranda</span>
            </a>

            {{-- Hero Section --}}
            <div class="relative rounded-3xl overflow-hidden mb-8 h-80">
                @if($photos && count($photos) > 0)
                    <img src="{{ Storage::url($photos[0]) }}" alt="{{ $location->name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-gradient-to-br from-[#b58042]/30 to-[#8b5b2e]/30 flex items-center justify-center">
                        <i class="fa-solid fa-store text-6xl text-white/50"></i>
                    </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
                
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <div class="flex items-center gap-2 text-[#e9dac9] mb-2">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>{{ $location->address }}</span>
                    </div>
                    <h1 class="font-display font-bold text-4xl md:text-5xl mb-2">{{ $location->name }}</h1>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-sm {{ $location->is_active ? 'bg-emerald-500' : 'bg-red-500' }}">
                            {{ $location->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                        @if($location->map_url)
                            <a href="{{ $location->map_url }}" target="_blank" 
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-all">
                                <i class="fa-solid fa-map"></i>
                                Lihat di Google Maps
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Content Grid --}}
            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-8">
                    {{-- Description --}}
                    <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl p-6">
                        <h2 class="font-display text-2xl text-[#3f2b1b] mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-file-lines text-[#b58042]"></i>
                            Tentang Studio
                        </h2>
                        <p class="text-[#6f5134] leading-relaxed">
                            {{ $location->description ?? 'Belum ada deskripsi untuk studio ini.' }}
                        </p>
                    </div>

                    {{-- Gallery --}}
                    @if($photos && count($photos) > 0)
                        <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl p-6">
                            <h2 class="font-display text-2xl text-[#3f2b1b] mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-images text-[#b58042]"></i>
                                Galeri
                            </h2>
                            <div class="grid sm:grid-cols-2 gap-4">
                                @foreach($photos as $photo)
                                    <div class="group relative rounded-xl overflow-hidden border border-[#e3d5c4] aspect-square">
                                        <img src="{{ Storage::url($photo) }}" alt="Foto {{ $location->name }}" 
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <i class="fa-solid fa-magnifying-glass-plus text-white text-3xl"></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Info Card --}}
                    <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl p-6">
                        <h3 class="font-display text-xl text-[#3f2b1b] mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-circle-info text-[#b58042]"></i>
                            Informasi
                        </h3>
                        
                        <div class="space-y-4">
                            @if($location->phone)
                                <div class="flex items-center gap-3 text-[#6f5134]">
                                    <div class="w-8 h-8 rounded-lg bg-[#f0e4d6] flex items-center justify-center">
                                        <i class="fa-solid fa-phone text-[#b58042]"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-[#7a5b3a]">Telepon</p>
                                        <p class="font-medium">{{ $location->phone }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($location->email)
                                <div class="flex items-center gap-3 text-[#6f5134]">
                                    <div class="w-8 h-8 rounded-lg bg-[#f0e4d6] flex items-center justify-center">
                                        <i class="fa-solid fa-envelope text-[#b58042]"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-[#7a5b3a]">Email</p>
                                        <p class="font-medium">{{ $location->email }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center gap-3 text-[#6f5134]">
                                <div class="w-8 h-8 rounded-lg bg-[#f0e4d6] flex items-center justify-center">
                                    <i class="fa-solid fa-clock text-[#b58042]"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-[#7a5b3a]">Jam Operasional</p>
                                    <p class="font-medium">{{ $location->operating_hours ?? '09:00 - 21:00' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Rooms Card --}}
                    @if($location->rooms && $location->rooms->count())
                        <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl p-6">
                            <h3 class="font-display text-xl text-[#3f2b1b] mb-4 flex items-center gap-2">
                                <i class="fa-solid fa-door-open text-[#b58042]"></i>
                                Ruangan Tersedia
                            </h3>
                            
                            <div class="space-y-3">
                                @foreach($location->rooms as $room)
                                    <div class="flex items-center justify-between p-3 bg-[#fcf7f1] rounded-lg">
                                        <div>
                                            <p class="font-medium text-[#3f2b1b]">{{ $room->name }}</p>
                                            @if($room->capacity)
                                                <p class="text-xs text-[#7a5b3a]">Kapasitas: {{ $room->capacity }} orang</p>
                                            @endif
                                        </div>
                                        <span class="px-2 py-1 rounded-full text-xs bg-emerald-100 text-emerald-700">
                                            Tersedia
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Action Card --}}
                    <div class="bg-gradient-to-br from-[#b58042] to-[#8b5b2e] rounded-2xl p-6 text-white">
                        <h3 class="font-display text-xl mb-2">Siap Booking?</h3>
                        <p class="text-white/90 text-sm mb-4">Pilih paket favorit Anda dan booking sekarang!</p>
                        <a href="{{ route('catalog.public') }}" 
                           class="block w-full px-4 py-3 rounded-xl bg-white text-[#b58042] text-center font-semibold hover:bg-gray-100 transition-colors">
                            Lihat Katalog
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>