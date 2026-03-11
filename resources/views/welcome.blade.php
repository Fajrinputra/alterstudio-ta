<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    @php use Illuminate\Support\Facades\Storage; @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alter Studio � Abadikan Momen Berharga</title>
    
    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #F9F3EB; }
        .font-display { font-family: 'Playfair Display', serif; }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        .popular-badge {
            position: absolute;
            top: -12px;
            right: 20px;
            background: linear-gradient(135deg, #b58042, #8b5b2e);
            color: white;
            padding: 4px 20px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(181, 128, 66, 0.3);
            z-index: 10;
        }
        
        .package-card {
            transition: all 0.3s ease;
        }
        
        .package-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -10px rgba(92, 67, 44, 0.2);
        }
        
        .category-tab {
            transition: all 0.2s ease;
        }
        
        .category-tab.active {
            background: #b58042;
            color: white;
            border-color: #b58042;
        }

        .hero-slide-track {
            display: flex;
            height: 100%;
            transition: transform 900ms ease;
            will-change: transform;
        }

        .hero-slide-item {
            min-width: 100%;
            height: 100%;
            position: relative;
        }
    </style>
</head>
<body class="bg-[#F9F3EB] text-[#5c432c] antialiased">
    @php
        $waUrl = config('services.contact.whatsapp');
        $instagramUrl = config('services.contact.instagram');
        $tiktokUrl = config('services.contact.tiktok');
    @endphp
    <div class="min-h-screen flex flex-col">
        <!-- Nav -->
        <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-[#e6d7c7] shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-[#b58042] to-[#8b5b2e] rounded-xl blur-md opacity-50"></div>
                            <div class="relative h-10 w-10 rounded-xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] flex items-center justify-center text-white font-black">
                                A
                            </div>
                        </div>
                        <div>
                            <p class="font-display text-lg text-[#4c351f] leading-tight">Alter Studio</p>
                            <p class="text-xs text-[#8b7359]">Premium Photography</p>
                        </div>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-1">
                        @foreach(['Beranda', 'Tentang', 'Paket', 'Studio', 'Portofolio', 'Kontak'] as $menu)
                            <a href="#{{ strtolower($menu) }}" 
                               class="px-4 py-2 text-sm font-medium text-[#6d5336] hover:text-[#b58042] hover:bg-[#fcf7f1] rounded-xl transition-all">
                                {{ $menu }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Auth Buttons -->
                    <div class="flex items-center gap-3 text-sm">
                        @auth
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-[#6d5336]">{{ Auth::user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="px-4 py-2 rounded-full border border-[#d7c5b2] text-[#5c432c] hover:bg-white hover:shadow-md transition-all">
                                        Keluar
                                    </button>
                                </form>
                                <a href="{{ route('dashboard') }}" 
                                   class="px-5 py-2 rounded-full bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl transition-all">
                                    Dashboard
                                </a>
                            </div>
                        @else
                            <a href="{{ route('login') }}" 
                               class="px-4 py-2 rounded-full border border-[#d7c5b2] text-[#5c432c] hover:bg-white hover:shadow-md transition-all">
                                Masuk
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" 
                                   class="px-5 py-2 rounded-full bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl transition-all">
                                    Daftar
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero -->
        @php
            $heroSlidesCollection = collect($heroSlides ?? []);
            $defaultHero = (object) [
                'eyebrow' => 'CASA DE ALTER & SIGNATURE',
                'title' => 'Abadikan Momen Berharga Anda',
                'subtitle' => 'Sentuhan profesional dari booking, pembayaran, penjadwalan kru, hingga hasil akhir siap diunduh.',
                'image_path' => null,
            ];
            $heroFallbackImage = 'https://images.unsplash.com/photo-1516035052735-0ffcdf4edb5b?auto=format&fit=crop&w=2000&q=80';
            $heroCurrent = $heroSlidesCollection->first() ?: $defaultHero;
        @endphp

        <section id="hero" class="relative overflow-hidden h-[560px] md:h-[640px] lg:h-[700px] flex items-center">
            <div class="absolute inset-0">
                @if($heroSlidesCollection->isNotEmpty())
                    <div id="hero-slide-track" class="hero-slide-track">
                        @foreach($heroSlidesCollection as $slide)
                            <div class="hero-slide-item"
                                 data-hero-slide
                                 data-eyebrow="{{ $slide->eyebrow ?? '' }}"
                                 data-title="{{ $slide->title }}"
                                 data-subtitle="{{ $slide->subtitle ?? '' }}">
                                <img src="{{ Storage::url($slide->image_path) }}" alt="{{ $slide->title }}" class="w-full h-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @else
                    <img src="{{ $heroFallbackImage }}" alt="Studio lights" class="w-full h-full object-cover">
                @endif

                <div class="absolute inset-0 bg-gradient-to-r from-[#3f2b1b]/90 via-[#5c432c]/72 to-[#f9f3eb]/45"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#3f2b1b]/35 via-transparent to-white/10"></div>
                <div class="absolute inset-y-0 right-0 w-[38%] bg-white/12 backdrop-blur-[1px]"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="max-w-3xl text-white">
                    <p id="hero-eyebrow" class="text-sm uppercase tracking-[0.25em] mb-4 text-[#e9dac9] flex items-center gap-2">
                        <span class="w-12 h-px bg-[#b58042]"></span>
                        {{ $heroCurrent->eyebrow ?? 'CASA DE ALTER & SIGNATURE' }}
                    </p>

                    <h1 id="hero-title" class="font-display text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                        {{ $heroCurrent->title }}
                    </h1>

                    <p id="hero-subtitle" class="text-lg md:text-xl text-[#f1e5d8] max-w-3xl mb-8">
                        {{ $heroCurrent->subtitle }}
                    </p>

                    <div class="flex flex-wrap gap-3">
                        <a href="#portofolio"
                           class="group px-6 py-3 rounded-full bg-white text-[#5c432c] font-semibold hover:shadow-xl hover:shadow-white/30 transition-all flex items-center gap-2">
                            Lihat Portofolio
                            <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="#paket"
                           class="px-6 py-3 rounded-full border border-white/30 text-white hover:bg-white/10 transition-all">
                            Lihat Paket
                        </a>
                    </div>

                    <div class="flex flex-wrap gap-2 pt-8">
                        @foreach(['Pemesanan Mudah', 'Pembayaran Aman', 'Pengalaman Baru', 'Jadwal Anti-Bentrok'] as $feature)
                            <span class="px-4 py-2 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-sm">
                                <i class="fa-solid fa-circle-check text-[#b58042] mr-2"></i>
                                {{ $feature }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="absolute right-10 bottom-10 hidden lg:block floating">
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl p-4 shadow-2xl border border-white/50">
                    <p class="text-[#5c432c] font-semibold">5000+ Klien Puas</p>
                </div>
            </div>
        </section>

        <!-- Thin highlight bar -->
        <div class="bg-gradient-to-r from-[#e8dccd] to-[#dac9b7] text-[#715335] text-sm py-4">
            <div class="max-w-7xl mx-auto px-6 flex flex-wrap gap-4 justify-center items-center">
                <i class="fa-solid fa-circle-check text-[#b58042]"></i>
                <span>Di balik setiap foto, ada cerita yang menanti untuk diceritakan</span>
                <span class="hidden md:inline text-[#b58042]">�</span>
                <span>Pembayaran digital berbasis midtrans</span>
            </div>
        </div>

        <!-- About / Stats -->
        <section id="tentang" class="max-w-7xl mx-auto px-6 py-16 space-y-10">
            <div class="text-center space-y-3">
                <p class="font-display text-4xl text-[#4c351f]">Tentang Alter Studio</p>
                <p class="text-[#7a5b3a] max-w-3xl mx-auto">Rumah fotografi dengan dua cabang unggulan, siap melayani kebutuhan wedding, portrait, hingga komersial dengan tim profesional.</p>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
                @php
                    $stats = [
                        ['label' => 'Foto Tersimpan', 'value' => '10.000+', 'icon' => 'fa-solid fa-images'],
                        ['label' => 'Klien Puas', 'value' => '5.000+', 'icon' => 'fa-solid fa-face-smile'],
                        ['label' => 'Penghargaan', 'value' => '15+', 'icon' => 'fa-solid fa-trophy'],
                        ['label' => 'Dedikasi', 'value' => '100%', 'icon' => 'fa-solid fa-heart'],
                    ];
                @endphp
                @foreach($stats as $item)
                    <div class="group rounded-2xl border border-[#eadccf] bg-white shadow-lg hover:shadow-xl transition-all p-6 text-center hover:-translate-y-1">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-gradient-to-br from-[#b58042]/10 to-[#8b5b2e]/10 flex items-center justify-center group-hover:from-[#b58042] group-hover:to-[#8b5b2e] transition-all">
                            <i class="{{ $item['icon'] }} text-[#b58042] group-hover:text-white transition-all"></i>
                        </div>
                        <p class="font-display text-2xl text-[#6b4a2d]">{{ $item['value'] }}</p>
                        <p class="text-sm text-[#7a5b3a]">{{ $item['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Packages Section - Sesuai Screenshot -->
        <section id="paket" class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h2 class="font-display text-4xl font-bold text-[#4c351f]">Paket & Kategori Foto</h2>
                    <p class="text-[#7a5b3a] text-lg mt-2">Pilih paket yang sesuai dengan kebutuhan Anda</p>
                </div>

                @php
                    $allPackages = $categories->flatMap(function ($category) {
                        return $category->packages->map(function ($package) use ($category) {
                            $package->category_name = $category->name;
                            return $package;
                        });
                    })->values();
                @endphp

                <!-- Categories Filter -->
                <div class="flex flex-wrap justify-center gap-3 mb-12">
                    @foreach($categories as $category)
                        <button type="button" data-category-filter="{{ $category->id }}"
                                class="category-tab px-6 py-2 rounded-full border {{ $loop->first ? 'border-[#b58042] bg-[#b58042] text-white' : 'border-[#d7c5b2] bg-white text-[#5c432c]' }} hover:bg-[#b58042] hover:text-white hover:border-[#b58042] transition-all">
                            {{ $category->name }} ({{ $category->packages->count() }})
                        </button>
                    @endforeach
                </div>

                <!-- Packages Grid -->
                @if($allPackages->isNotEmpty())
                    <div id="landing-packages-grid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($allPackages as $package)
                            @php
                                $features = collect($package->features ?? [])->filter()->take(5)->values();
                                $isPopular = in_array($package->id, $mostPopularPackageIds ?? [], true) && (($package->bookings_count ?? 0) > 0);
                            @endphp
                            <div class="package-card relative bg-white rounded-3xl border {{ $isPopular ? 'border-2 border-[#b58042]' : 'border-[#e1d3c5]' }} p-8 shadow-lg"
                                 data-package-card
                                 data-category-id="{{ $package->category_id }}">
                                @if($isPopular)
                                    <div class="popular-badge">Paling Diminati</div>
                                @endif

                                <p class="text-xs uppercase tracking-wide text-[#8b7359] mb-2">{{ $package->category_name }}</p>
                                <h3 class="font-display text-2xl font-semibold text-[#4c351f] mb-3">{{ $package->name }}</h3>
                                <div class="text-3xl font-bold text-[#b58042] mb-4">Rp {{ number_format($package->price, 0, ',', '.') }}</div>

                                @if($package->description)
                                    <p class="text-sm text-[#7a5b3a] mb-4 line-clamp-2">{{ $package->description }}</p>
                                @endif

                                <ul class="space-y-2 mb-6 min-h-[120px]">
                                    @forelse($features as $feature)
                                        <li class="flex items-start gap-3 text-sm text-[#5c432c]">
                                            <i class="fa-solid fa-circle-check text-[#b58042] mt-0.5"></i>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-[#8b7359]">Fitur akan ditampilkan setelah paket dilengkapi.</li>
                                    @endforelse
                                </ul>

                                @auth
                                    @if(auth()->user()->role === \App\Enums\Role::CLIENT)
                                        <a href="{{ route('bookings.create', ['package_id' => $package->id]) }}"
                                           class="block w-full text-center px-6 py-3 rounded-full bg-[#b58042] text-white font-semibold hover:bg-[#8b5b2e] transition-all">
                                            Pilih Paket
                                        </a>
                                    @else
                                        <a href="{{ route('catalog.public') }}"
                                           class="block w-full text-center px-6 py-3 rounded-full border-2 border-[#b58042] text-[#b58042] font-semibold hover:bg-[#b58042] hover:text-white transition-all">
                                            Lihat Detail
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('register') }}"
                                       class="block w-full text-center px-6 py-3 rounded-full border-2 border-[#b58042] text-[#b58042] font-semibold hover:bg-[#b58042] hover:text-white transition-all">
                                        Daftar untuk Booking
                                    </a>
                                @endauth
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 bg-white border border-[#e3d5c4] rounded-3xl">
                        <i class="fa-solid fa-box-open text-5xl text-[#8b7359] mb-4 opacity-50"></i>
                        <p class="text-[#7a5b3a]">Belum ada paket aktif untuk ditampilkan.</p>
                    </div>
                @endif
            </div>
        </section>

        <!-- Portfolio Section -->
        <section id="portofolio" class="py-16 bg-[#F9F3EB]">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="font-display text-4xl font-bold text-[#4c351f]">Portofolio Kami</h2>
                    <p class="text-[#7a5b3a] text-lg mt-2">Koleksi foto terbaik dari seluruh paket yang sudah diunggah</p>
                </div>

                @php
                    $portfolioItems = collect($categories ?? [])
                        ->flatMap(function ($category) {
                            return collect($category->packages ?? [])
                                ->flatMap(function ($package) use ($category) {
                                    return collect($package->gallery ?? [])
                                        ->filter(fn ($path) => is_string($path) && $path !== '')
                                        ->map(function ($path) use ($category, $package) {
                                            return [
                                                'url' => Storage::url($path),
                                                'category_id' => (string) $category->id,
                                                'category_name' => $category->name,
                                                'package_name' => $package->name,
                                            ];
                                        });
                                });
                        })
                        ->values();
                    $portfolioCategoryIds = $portfolioItems->pluck('category_id')->unique()->values()->all();
                @endphp

                @if($portfolioItems->isNotEmpty())
                    <div class="flex flex-wrap justify-center gap-3 mb-10">
                        <button type="button" data-portfolio-filter="all"
                                class="portfolio-tab px-6 py-2 rounded-full border border-[#b58042] bg-[#b58042] text-white">
                            Semua Foto
                        </button>
                        @foreach($categories as $category)
                            @if(in_array((string) $category->id, $portfolioCategoryIds, true))
                                <button type="button" data-portfolio-filter="{{ $category->id }}"
                                        class="portfolio-tab px-6 py-2 rounded-full border border-[#d7c5b2] text-[#5c432c] bg-white hover:bg-[#b58042] hover:text-white hover:border-[#b58042] transition-all">
                                    {{ $category->name }}
                                </button>
                            @endif
                        @endforeach
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($portfolioItems as $photo)
                            <a href="{{ $photo['url'] }}" target="_blank"
                               data-portfolio-card
                               data-portfolio-category="{{ $photo['category_id'] }}"
                               data-portfolio-category-name="{{ $photo['category_name'] }}"
                               data-portfolio-package-name="{{ $photo['package_name'] }}"
                               class="group relative rounded-2xl overflow-hidden border border-[#e3d5c4] shadow-lg hover:shadow-2xl transition-all">
                                <div class="aspect-video">
                                    <img src="{{ $photo['url'] }}" alt="Portofolio Alter Studio" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                </div>
                                <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-between p-3">
                                    <div>
                                        <p class="text-[11px] text-white/80 uppercase tracking-wider">{{ $photo['category_name'] }}</p>
                                        <p class="text-sm text-white font-medium">{{ $photo['package_name'] }}</p>
                                    </div>
                                    <span class="w-10 h-10 rounded-full bg-white/90 backdrop-blur-sm flex items-center justify-center text-[#b58042]">
                                        <i class="fa-solid fa-expand"></i>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 bg-white border border-[#e3d5c4] rounded-3xl">
                        <i class="fa-solid fa-images text-5xl text-[#8b7359] mb-4 opacity-50"></i>
                        <p class="text-[#7a5b3a]">Belum ada foto portofolio yang diunggah.</p>
                    </div>
                @endif
            </div>
        </section>

        
        <!-- Studio Section -->
        <section id="studio" class="bg-white py-16">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-12">
                    <h2 class="font-display text-4xl font-bold text-[#4c351f]">Studio & Lokasi</h2>
                    <p class="text-[#7a5b3a] text-lg mt-2">Kunjungi cabang Alter Studio dan rasakan pengalamannya</p>
                </div>

                @if($locations->count())
                    <div class="grid md:grid-cols-2 gap-8">
                        @foreach($locations as $loc)
                            @php
                                $photo = $loc->photo_path ? Storage::url($loc->photo_path) : null;
                            @endphp
                            
                            <div class="group bg-white border border-[#e1d3c5] rounded-3xl shadow-lg hover:shadow-2xl transition-all overflow-hidden">
                                @if($photo)
                                    <div class="h-64 w-full overflow-hidden">
                                        <img src="{{ $photo }}" alt="{{ $loc->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                    </div>
                                @else
                                    <div class="h-64 w-full bg-gradient-to-br from-[#f2e6d8] to-[#d6c2ae] flex items-center justify-center">
                                        <span class="font-display text-3xl text-[#4c351f]">{{ $loc->name }}</span>
                                    </div>
                                @endif
                                
                                <div class="p-8">
                                    <h3 class="font-display text-2xl font-semibold text-[#4c351f] mb-2">{{ $loc->name }}</h3>
                                    @if($loc->address)
                                        <p class="text-[#7a5b3a] mb-4 flex items-start gap-2">
                                            <i class="fa-solid fa-location-dot text-[#b58042] mt-1"></i>
                                            <span>{{ $loc->address }}</span>
                                        </p>
                                    @endif
                                    
                                    @if($loc->description)
                                        <p class="text-[#6f5134] mb-6">{{ $loc->description }}</p>
                                    @endif
                                    
                                    <div class="flex gap-3">
                                        <a href="{{ route('locations.public.show', $loc) }}" 
                                           class="flex-1 text-center px-6 py-3 rounded-xl border-2 border-[#b58042] text-[#b58042] font-semibold hover:bg-[#b58042] hover:text-white transition-all">
                                            Detail Cabang
                                        </a>
                                        @if($loc->map_url)
                                            <a href="{{ $loc->map_url }}" target="_blank" 
                                               class="px-6 py-3 rounded-xl bg-[#b58042] text-white font-semibold hover:bg-[#8b5b2e] transition-all">
                                                <i class="fa-solid fa-map"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 bg-white border border-[#e3d5c4] rounded-3xl">
                        <i class="fa-solid fa-store text-5xl text-[#8b7359] mb-4 opacity-50"></i>
                        <p class="text-[#7a5b3a]">Lokasi akan segera hadir</p>
                    </div>
                @endif
            </div>
        </section>

        <!-- Contact Section - Background Putih Sesuai Screenshot -->
        <section id="kontak" class="bg-white py-16">
            <div class="max-w-4xl mx-auto px-6">
                <div class="bg-white rounded-3xl p-12 text-center">
                    <h2 class="font-display text-4xl font-bold text-[#4c351f] mb-4">Hubungi Kami</h2>
                    <p class="text-[#7a5b3a] text-lg mb-8 max-w-2xl mx-auto">
                        Ada pertanyaan? Kami siap membantu Anda mewujudkan momen spesial Anda.
                    </p>

                    <div class="flex flex-wrap justify-center gap-3">
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-[#25D366] text-white font-semibold hover:brightness-95 transition-all">
                            <i class="fa-brands fa-whatsapp"></i>
                            WhatsApp Admin
                        </a>
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gradient-to-r from-[#fd5949] via-[#d6249f] to-[#285AEB] text-white font-semibold hover:opacity-95 transition-all">
                            <i class="fa-brands fa-instagram"></i>
                            Instagram
                        </a>
                        <a href="{{ $tiktokUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-black text-white font-semibold hover:opacity-90 transition-all">
                            <i class="fa-brands fa-tiktok"></i>
                            TikTok
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-calendar-check"></i>
                            Pesan Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-[#f0e6da] border-t border-[#e1d3c5] py-8">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-[#7a5b3a]">
                    <p>� 2026 Alter Studio. Hak Cipta Dilindungi.</p>
                </div>
            </div>
        </footer>
    </div>

    <a href="{{ $waUrl }}" target="_blank" rel="noopener"
       class="fixed bottom-6 right-6 z-40 inline-flex items-center gap-2 px-4 py-3 rounded-full bg-[#25D366] text-white font-semibold shadow-xl hover:brightness-95 transition-all">
        <i class="fa-brands fa-whatsapp text-lg"></i>
        <span class="hidden sm:inline">WhatsApp Admin</span>
    </a>

    <div id="portfolio-lightbox" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" data-lightbox-close></div>
        <div class="relative z-10 w-full h-full flex items-center justify-center px-4">
            <button type="button" data-lightbox-close
                    class="absolute top-5 right-5 w-11 h-11 rounded-full bg-white/20 text-white hover:bg-white/30 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>

            <button type="button" id="lightbox-prev"
                    class="absolute left-4 md:left-8 w-11 h-11 rounded-full bg-white/20 text-white hover:bg-white/30 transition-colors">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <figure class="max-w-5xl w-full">
                <img id="lightbox-image" src="" alt="Preview portofolio" class="w-full max-h-[82vh] object-contain rounded-2xl">
                <figcaption id="lightbox-caption" class="text-center text-white/90 text-sm mt-3"></figcaption>
            </figure>

            <button type="button" id="lightbox-next"
                    class="absolute right-4 md:right-8 w-11 h-11 rounded-full bg-white/20 text-white hover:bg-white/30 transition-colors">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    {{-- AOS Animation Script --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // Category tabs functionality + filter package cards
        const tabs = document.querySelectorAll('[data-category-filter]');
        const packageCards = document.querySelectorAll('[data-package-card]');

        function filterPackageCards(targetCategory) {
            packageCards.forEach(card => {
                const cardCategory = card.dataset.categoryId;
                const visible = cardCategory === targetCategory;
                card.style.display = visible ? '' : 'none';
            });
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                const targetCategory = this.dataset.categoryFilter;

                tabs.forEach(t => {
                    t.classList.remove('bg-[#b58042]', 'text-white', 'border-[#b58042]');
                    t.classList.add('bg-white', 'text-[#5c432c]', 'border-[#d7c5b2]');
                });
                this.classList.add('bg-[#b58042]', 'text-white', 'border-[#b58042]');
                this.classList.remove('bg-white', 'text-[#5c432c]', 'border-[#d7c5b2]');

                filterPackageCards(targetCategory);
            });
        });

        const defaultCategoryTab = tabs[0];
        if (defaultCategoryTab) {
            filterPackageCards(defaultCategoryTab.dataset.categoryFilter);
        }

        const portfolioTabs = document.querySelectorAll('[data-portfolio-filter]');
        const portfolioCards = document.querySelectorAll('[data-portfolio-card]');

        portfolioTabs.forEach(tab => {
            tab.addEventListener('click', function () {
                const targetCategory = this.dataset.portfolioFilter;

                portfolioTabs.forEach(t => {
                    t.classList.remove('bg-[#b58042]', 'text-white', 'border-[#b58042]');
                    t.classList.add('bg-white', 'text-[#5c432c]', 'border-[#d7c5b2]');
                });
                this.classList.add('bg-[#b58042]', 'text-white', 'border-[#b58042]');
                this.classList.remove('bg-white', 'text-[#5c432c]', 'border-[#d7c5b2]');

                portfolioCards.forEach(card => {
                    const cardCategory = card.dataset.portfolioCategory;
                    const visible = targetCategory === 'all' || cardCategory === targetCategory;
                    card.style.display = visible ? '' : 'none';
                });
            });
        });

        const lightbox = document.getElementById('portfolio-lightbox');
        const lightboxImage = document.getElementById('lightbox-image');
        const lightboxCaption = document.getElementById('lightbox-caption');
        const lightboxPrev = document.getElementById('lightbox-prev');
        const lightboxNext = document.getElementById('lightbox-next');
        let activePortfolioIndex = -1;

        function getVisiblePortfolioCards() {
            return Array.from(document.querySelectorAll('[data-portfolio-card]'))
                .filter(card => card.style.display !== 'none');
        }

        function renderLightbox(index) {
            const cards = getVisiblePortfolioCards();
            if (!cards.length) return;

            if (index < 0) index = cards.length - 1;
            if (index >= cards.length) index = 0;
            activePortfolioIndex = index;

            const card = cards[index];
            const image = card.querySelector('img');
            const category = card.dataset.portfolioCategoryName || '';
            const title = card.dataset.portfolioPackageName || '';

            lightboxImage.src = card.href || image?.src || '';
            lightboxCaption.textContent = [category, title].filter(Boolean).join(' � ');
        }

        function openLightbox(index) {
            lightbox.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
            renderLightbox(index);
        }

        function closeLightbox() {
            lightbox.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            lightboxImage.src = '';
            activePortfolioIndex = -1;
        }

        document.querySelectorAll('[data-portfolio-card]').forEach((card) => {
            card.addEventListener('click', (event) => {
                event.preventDefault();
                const cards = getVisiblePortfolioCards();
                const index = cards.indexOf(card);
                openLightbox(index >= 0 ? index : 0);
            });
        });

        lightboxPrev?.addEventListener('click', () => renderLightbox(activePortfolioIndex - 1));
        lightboxNext?.addEventListener('click', () => renderLightbox(activePortfolioIndex + 1));

        document.querySelectorAll('[data-lightbox-close]').forEach((el) => {
            el.addEventListener('click', closeLightbox);
        });

        document.addEventListener('keydown', (event) => {
            if (lightbox.classList.contains('hidden')) return;
            if (event.key === 'Escape') closeLightbox();
            if (event.key === 'ArrowLeft') renderLightbox(activePortfolioIndex - 1);
            if (event.key === 'ArrowRight') renderLightbox(activePortfolioIndex + 1);
        });

        const heroTrack = document.getElementById('hero-slide-track');
        const heroSlides = document.querySelectorAll('[data-hero-slide]');
        const heroEyebrow = document.getElementById('hero-eyebrow');
        const heroTitle = document.getElementById('hero-title');
        const heroSubtitle = document.getElementById('hero-subtitle');

        if (heroTrack && heroSlides.length > 1) {
            let heroIndex = 0;

            function setHeroText(index) {
                const slide = heroSlides[index];
                if (!slide) return;
                if (heroEyebrow) {
                    heroEyebrow.innerHTML = '<span class="w-12 h-px bg-[#b58042]"></span>' + (slide.dataset.eyebrow || '');
                }
                if (heroTitle) {
                    heroTitle.textContent = slide.dataset.title || '';
                }
                if (heroSubtitle) {
                    heroSubtitle.textContent = slide.dataset.subtitle || '';
                }
            }

            setInterval(() => {
                heroIndex = (heroIndex + 1) % heroSlides.length;
                heroTrack.style.transform = `translateX(-${heroIndex * 100}%)`;
                setHeroText(heroIndex);
            }, 5000);
        }
    </script>
</body>
</html>
