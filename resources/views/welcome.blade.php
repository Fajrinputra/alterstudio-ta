<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    @php use Illuminate\Support\Facades\Storage; @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alter Studio • Abadikan Momen Berharga</title>
   
    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
   
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   
    @vite(['resources/css/app.css', 'resources/js/app.js'])
   
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #FAF6F0; 
        }
        .font-display { font-family: 'Playfair Display', serif; }
       
        .floating {
            animation: floating 4s ease-in-out infinite;
        }
       
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-18px); }
        }

        .glass {
            background: rgba(255, 255, 255, 0.78);
            backdrop-filter: blur(16px);
        }

        .popular-badge {
            position: absolute;
            top: -14px;
            right: 24px;
            background: linear-gradient(135deg, #D4A017, #E07A5F);
            color: white;
            padding: 6px 24px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.75px;
            box-shadow: 0 10px 15px -3px rgba(212, 160, 23, 0.35);
            z-index: 10;
        }
       
        .package-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
       
        .package-card:hover {
            transform: translateY(-14px) scale(1.03);
            box-shadow: 0 30px 60px -15px rgba(92, 67, 44, 0.28);
        }
       
        .category-tab {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
       
        .category-tab.active {
            background: linear-gradient(135deg, #D4A017, #E07A5F);
            color: white;
            border-color: transparent;
            box-shadow: 0 6px 20px rgba(212, 160, 23, 0.35);
        }

        .hero-slide-track {
            display: flex;
            height: 100%;
            transition: transform 1000ms cubic-bezier(0.32, 0.72, 0, 1);
            will-change: transform;
        }
        .hero-slide-item {
            min-width: 100%;
            height: 100%;
            position: relative;
        }

        /* Particle Canvas */
        #hero-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
            opacity: 0.75;
        }
    </style>
</head>
<body class="bg-[#FAF6F0] text-[#3F2B1B] antialiased">
    @php
        $waUrl = config('services.contact.whatsapp');
        $instagramUrl = config('services.contact.instagram');
        $tiktokUrl = config('services.contact.tiktok');
    @endphp

    <div class="min-h-screen flex flex-col">
        <!-- Nav -->
        <header class="sticky top-0 z-30 glass border-b border-white/60 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017] via-[#E07A5F] to-[#B56D3E] rounded-2xl blur-xl opacity-40"></div>
                            <div class="relative h-11 w-11 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#B56D3E] flex items-center justify-center text-white font-black text-2xl shadow-inner">
                                A
                            </div>
                        </div>
                        <div>
                            <p class="font-display text-2xl tracking-tight text-[#3F2B1B]">Alter Studio</p>
                            <p class="text-[10px] uppercase tracking-[1px] text-[#8B7359] -mt-1">Premium Moments</p>
                        </div>
                    </div>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-2">
                        @foreach(['Beranda', 'Tentang', 'Paket', 'Portofolio', 'Studio', 'Kontak'] as $menu)
                            <a href="#{{ strtolower($menu) }}" 
                               class="px-5 py-2.5 text-sm font-medium text-[#5C432C] hover:text-[#D4A017] rounded-2xl hover:bg-white/70 transition-all duration-300">
                                {{ $menu }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Auth Buttons -->
                    <div class="flex items-center gap-3 text-sm">
                        @auth
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-[#5C432C] font-medium">{{ Auth::user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="px-5 py-2 rounded-2xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all">
                                        Keluar
                                    </button>
                                </form>
                                <a href="{{ route('dashboard') }}" 
                                   class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                                    Dashboard
                                </a>
                            </div>
                        @else
                            <a href="{{ route('login') }}" 
                               class="px-5 py-2.5 rounded-2xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:shadow transition-all">
                                Masuk
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" 
                                   class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                                    Daftar
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero dengan Particle Subtle -->
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

        <section id="hero" class="relative overflow-hidden h-[620px] md:h-[720px] lg:h-[780px] flex items-center">
            <!-- Background Image -->
            <div class="absolute inset-0">
                @if($heroSlidesCollection->isNotEmpty())
                    <div id="hero-slide-track" class="hero-slide-track">
                        @foreach($heroSlidesCollection as $slide)
                            <div class="hero-slide-item" data-hero-slide
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
                
                <div class="absolute inset-0 bg-gradient-to-br from-[#3F2B1B]/85 via-[#5C432C]/65 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#3F2B1B]/75 via-transparent to-[#FAF6F0]/15"></div>
            </div>

            <!-- Subtle Particle Canvas -->
            <canvas id="hero-particles"></canvas>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 z-10">
                <div class="max-w-3xl">
                    <p id="hero-eyebrow" class="text-xs md:text-sm uppercase tracking-[3px] mb-6 text-[#E7D9C2] flex items-center gap-3">
                        <span class="flex-1 h-px bg-gradient-to-r from-[#D4A017] to-transparent"></span>
                        {{ $heroCurrent->eyebrow ?? 'CASA DE ALTER & SIGNATURE' }}
                        <span class="flex-1 h-px bg-gradient-to-l from-[#D4A017] to-transparent"></span>
                    </p>
                    
                    <h1 id="hero-title" class="font-display text-5xl md:text-6xl lg:text-7xl font-bold leading-[1.05] tracking-tighter text-white mb-6">
                        {{ $heroCurrent->title }}
                    </h1>
                    
                    <p id="hero-subtitle" class="text-lg md:text-xl text-[#E7D9C2] max-w-xl mb-10">
                        {{ $heroCurrent->subtitle }}
                    </p>

                    <div class="flex flex-wrap gap-4">
                        <a href="#portofolio" 
                           class="group px-8 py-4 rounded-3xl bg-white text-[#3F2B1B] font-semibold flex items-center gap-3 hover:shadow-2xl hover:shadow-white/40 transition-all duration-300">
                            Lihat Portofolio
                            <i class="fa-solid fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                        </a>
                        <a href="#paket" 
                           class="px-8 py-4 rounded-3xl border-2 border-white/60 text-white hover:bg-white/10 hover:border-white transition-all duration-300">
                            Lihat Paket
                        </a>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-12">
                        @foreach(['Pemesanan Mudah', 'Pembayaran Aman', 'Pengalaman Baru', 'Jadwal Anti-Bentrok'] as $feature)
                            <span class="px-5 py-2.5 rounded-3xl bg-white/10 backdrop-blur-md border border-white/30 text-sm text-white flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-[#D4A017]"></i>
                                {{ $feature }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Trust badge -->
            <div class="absolute right-8 bottom-12 hidden lg:block floating">
                <div class="glass px-7 py-5 rounded-3xl shadow-2xl border border-white/40 text-center">
                    <p class="font-semibold text-[#3F2B1B] text-lg">5000+ Klien Puas</p>
                    <p class="text-xs text-[#8B7359]">Momen yang diabadikan selamanya</p>
                </div>
            </div>
        </section>

        <!-- Thin highlight bar -->
        <div class="bg-gradient-to-r from-[#E7D9C2] via-[#D4C3A8] to-[#E7D9C2] text-[#5C432C] text-sm py-5 border-b border-[#D4C3A8]/50">
            <div class="max-w-7xl mx-auto px-6 flex flex-wrap gap-6 justify-center items-center">
                <span>Fotografi adalah seni menghentikan waktu, mengubah momen fana menjadi kenangan abadi melalui permainan cahaya dan rasa</span>
            </div>
        </div>

        <!-- About / Stats -->
        <section id="tentang" class="max-w-7xl mx-auto px-6 py-20 space-y-12">
            <div class="text-center space-y-4">
                <p class="font-display text-5xl tracking-tight text-[#3F2B1B]">Tentang Alter Studio</p>
                <p class="text-[#7A5B3A] max-w-2xl mx-auto text-lg">Rumah fotografi dengan dua cabang unggulan, siap melayani wedding, portrait, hingga komersial dengan tim profesional.</p>
            </div>
           
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
                @php
                    $stats = [
                        ['label' => 'Foto Tersimpan', 'value' => '10.000+', 'icon' => 'fa-solid fa-images'],
                        ['label' => 'Klien Puas', 'value' => '5.000+', 'icon' => 'fa-solid fa-face-smile'],
                        ['label' => 'Penghargaan', 'value' => '15+', 'icon' => 'fa-solid fa-trophy'],
                        ['label' => 'Dedikasi', 'value' => '100%', 'icon' => 'fa-solid fa-heart'],
                    ];
                @endphp
                @foreach($stats as $item)
                    <div class="group rounded-3xl bg-white border border-[#EDE0D0] p-8 text-center hover:border-[#D4A017]/30 hover:shadow-xl transition-all duration-300">
                        <div class="w-14 h-14 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="{{ $item['icon'] }} text-2xl text-[#D4A017] group-hover:text-[#E07A5F]"></i>
                        </div>
                        <p class="font-display text-4xl font-semibold text-[#3F2B1B]">{{ $item['value'] }}</p>
                        <p class="text-sm text-[#7A5B3A] mt-1">{{ $item['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Packages Section -->
        <section id="paket" class="bg-white py-20">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-14">
                    <h2 class="font-display text-5xl font-bold tracking-tight text-[#3F2B1B]">Paket & Kategori Foto</h2>
                    <p class="text-[#7A5B3A] text-xl mt-3">Pilih paket yang sesuai dengan cerita Anda</p>
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
                <div class="flex flex-wrap justify-center gap-3 mb-14">
                    @foreach($categories as $category)
                        <button type="button" 
                                data-category-filter="{{ $category->id }}"
                                class="category-tab px-8 py-3 rounded-3xl border 
                                    border-[#E1D3C5] bg-white text-[#5C432C] 
                                    hover:border-[#D4A017] hover:bg-[#D4A017] hover:text-white transition-all">
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
                            <div class="package-card relative bg-white rounded-3xl border {{ $isPopular ? 'border-2 border-[#D4A017]' : 'border-[#EDE0D0]' }} p-9 shadow-lg"
                                 data-package-card data-category-id="{{ $package->category_id }}">
                                @if($isPopular)
                                    <div class="popular-badge">Paling Diminati 🔥</div>
                                @endif
                                <p class="text-xs uppercase tracking-widest text-[#8B7359] mb-2">{{ $package->category_name }}</p>
                                <h3 class="font-display text-3xl font-semibold text-[#3F2B1B] mb-4">{{ $package->name }}</h3>
                                <div class="text-4xl font-bold text-[#D4A017] mb-6">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                                @if($package->description)
                                    <p class="text-[#7A5B3A] mb-6 line-clamp-2">{{ $package->description }}</p>
                                @endif
                                <ul class="space-y-3 mb-8 min-h-[140px]">
                                    @forelse($features as $feature)
                                        <li class="flex items-start gap-3 text-sm text-[#3F2B1B]">
                                            <i class="fa-solid fa-circle-check text-[#D4A017] mt-0.5"></i>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @empty
                                        <li class="text-sm text-[#8B7359]">Fitur akan ditampilkan setelah paket dilengkapi.</li>
                                    @endforelse
                                </ul>
                                @auth
                                    @if(auth()->user()->role === \App\Enums\Role::CLIENT)
                                        <a href="{{ route('bookings.create', ['package_id' => $package->id]) }}" 
                                           class="block w-full text-center py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold hover:brightness-110 transition-all">
                                            Pilih Paket
                                        </a>
                                    @else
                                        <a href="{{ route('catalog.public') }}" 
                                           class="block w-full text-center py-4 rounded-3xl border-2 border-[#D4A017] text-[#D4A017] font-semibold hover:bg-[#D4A017] hover:text-white transition-all">
                                            Lihat Detail
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('register') }}" 
                                       class="block w-full text-center py-4 rounded-3xl border-2 border-[#D4A017] text-[#D4A017] font-semibold hover:bg-[#D4A017] hover:text-white transition-all">
                                        Daftar untuk Booking
                                    </a>
                                @endauth
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 bg-white border border-[#EDE0D0] rounded-3xl">
                        <i class="fa-solid fa-box-open text-5xl text-[#8B7359] mb-4 opacity-50"></i>
                        <p class="text-[#7A5B3A]">Belum ada paket aktif untuk ditampilkan.</p>
                    </div>
                @endif
            </div>
        </section>

       <!-- Portfolio Section -->
<section id="portofolio" class="py-20 bg-[#FAF6F0]">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-14">
            <h2 class="font-display text-5xl font-bold tracking-tight text-[#3F2B1B]">Portofolio Kami</h2>
            <p class="text-[#7A5B3A] text-xl mt-3">Koleksi momen terbaik yang telah kami abadikan</p>
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
            <div class="flex flex-wrap justify-center gap-3 mb-12">
                <!-- Button "Semua Foto" tanpa class active -->
                <button type="button" data-portfolio-filter="all"
                        class="portfolio-tab px-8 py-3 rounded-3xl border border-[#E1D3C5] bg-white text-[#5C432C] hover:bg-[#D4A017] hover:text-white hover:border-[#D4A017] transition-all">
                    Semua Foto
                </button>

                @foreach($categories as $category)
                    @if(in_array((string) $category->id, $portfolioCategoryIds, true))
                        <button type="button" data-portfolio-filter="{{ $category->id }}"
                                class="portfolio-tab px-8 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] bg-white hover:bg-[#D4A017] hover:text-white hover:border-[#D4A017] transition-all">
                            {{ $category->name }}
                        </button>
                    @endif
                @endforeach
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($portfolioItems as $photo)
                    <a href="{{ $photo['url'] }}" target="_blank"
                       data-portfolio-card
                       data-portfolio-category="{{ $photo['category_id'] }}"
                       data-portfolio-category-name="{{ $photo['category_name'] }}"
                       data-portfolio-package-name="{{ $photo['package_name'] }}"
                       class="group relative rounded-3xl overflow-hidden border border-[#EDE0D0] shadow-xl hover:shadow-2xl transition-all duration-500">
                        <div class="aspect-video">
                            <img src="{{ $photo['url'] }}" alt="Portofolio Alter Studio" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-6">
                            <div>
                                <p class="text-xs text-white/80 uppercase tracking-widest">{{ $photo['category_name'] }}</p>
                                <p class="text-white font-medium">{{ $photo['package_name'] }}</p>
                            </div>
                        </div>
                        <div class="absolute top-4 right-4 w-9 h-9 rounded-2xl bg-white/90 backdrop-blur flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
                            <i class="fa-solid fa-expand text-[#D4A017]"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 bg-white border border-[#EDE0D0] rounded-3xl">
                <i class="fa-solid fa-images text-5xl text-[#8B7359] mb-4 opacity-50"></i>
                <p class="text-[#7A5B3A]">Belum ada foto portofolio yang diunggah.</p>
            </div>
        @endif
    </div>
</section>

        <!-- Studio Section -->
        <section id="studio" class="bg-white py-20">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center mb-14">
                    <h2 class="font-display text-5xl font-bold tracking-tight text-[#3F2B1B]">Studio & Lokasi</h2>
                    <p class="text-[#7A5B3A] text-xl mt-3">Kunjungi cabang kami dan rasakan pengalamannya</p>
                </div>
                @if($locations->count())
                    <div class="grid md:grid-cols-2 gap-8">
                        @foreach($locations as $loc)
                            @php
                                $photo = $loc->photo_path ? Storage::url($loc->photo_path) : null;
                            @endphp
                           
                            <div class="group bg-white border border-[#EDE0D0] rounded-3xl shadow-xl overflow-hidden hover:shadow-2xl transition-all">
                                @if($photo)
                                    <div class="h-72 w-full overflow-hidden">
                                        <img src="{{ $photo }}" alt="{{ $loc->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                    </div>
                                @else
                                    <div class="h-72 w-full bg-gradient-to-br from-[#FAF6F0] to-[#E7D9C2] flex items-center justify-center">
                                        <span class="font-display text-4xl text-[#3F2B1B]">{{ $loc->name }}</span>
                                    </div>
                                @endif
                               
                                <div class="p-9">
                                    <h3 class="font-display text-3xl font-semibold text-[#3F2B1B] mb-3">{{ $loc->name }}</h3>
                                    @if($loc->address)
                                        <p class="text-[#7A5B3A] mb-5 flex items-start gap-3">
                                            <i class="fa-solid fa-location-dot text-[#D4A017] mt-1"></i>
                                            <span>{{ $loc->address }}</span>
                                        </p>
                                    @endif
                                    @if($loc->description)
                                        <p class="text-[#5C432C] mb-8">{{ $loc->description }}</p>
                                    @endif
                                   
                                    <div class="flex gap-4">
                                        <a href="{{ route('locations.public.show', $loc) }}" 
                                           class="flex-1 text-center py-4 rounded-3xl border-2 border-[#D4A017] text-[#D4A017] font-semibold hover:bg-[#D4A017] hover:text-white transition-all">
                                            Detail Cabang
                                        </a>
                                        @if($loc->map_url)
                                            <a href="{{ $loc->map_url }}" target="_blank"
                                               class="px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold hover:brightness-110 transition-all">
                                                <i class="fa-solid fa-map"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16 bg-white border border-[#EDE0D0] rounded-3xl">
                        <i class="fa-solid fa-store text-5xl text-[#8B7359] mb-4 opacity-50"></i>
                        <p class="text-[#7A5B3A]">Lokasi akan segera hadir</p>
                    </div>
                @endif
            </div>
        </section>

        <!-- Contact Section -->
        <section id="kontak" class="bg-white py-20">
            <div class="max-w-4xl mx-auto px-6">
                <div class="bg-gradient-to-br from-[#FAF6F0] to-white rounded-3xl p-14 text-center border border-[#EDE0D0]">
                    <h2 class="font-display text-5xl font-bold text-[#3F2B1B] mb-6">Hubungi Kami</h2>
                    <p class="text-[#7A5B3A] text-xl max-w-xl mx-auto mb-12">
                        Ada pertanyaan? Kami siap membantu mewujudkan momen spesial Anda.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-[#25D366] text-white font-semibold hover:brightness-95 transition-all">
                            <i class="fa-brands fa-whatsapp text-xl"></i>
                            WhatsApp Admin
                        </a>
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-[#fd5949] via-[#d6249f] to-[#285AEB] text-white font-semibold hover:opacity-95 transition-all">
                            <i class="fa-brands fa-instagram text-xl"></i>
                            Instagram
                        </a>
                        <a href="{{ $tiktokUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-black text-white font-semibold hover:opacity-90 transition-all">
                            <i class="fa-brands fa-tiktok text-xl"></i>
                            TikTok
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                            <i class="fa-solid fa-calendar-check"></i>
                            Pesan Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-[#F4EDE4] border-t border-[#EDE0D0] py-10">
            <div class="max-w-7xl mx-auto px-6">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-[#7A5B3A]">
                    <p>© 2026 Alter Studio. Hak Cipta Dilindungi.</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Floating WA -->
    <a href="{{ $waUrl }}" target="_blank" rel="noopener"
       class="fixed bottom-8 right-8 z-50 inline-flex items-center gap-3 px-6 py-4 rounded-3xl bg-[#25D366] text-white font-semibold shadow-2xl hover:brightness-95 transition-all">
        <i class="fa-brands fa-whatsapp text-2xl"></i>
        <span class="hidden sm:inline">WhatsApp Admin</span>
    </a>

    <!-- Lightbox -->
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

    {{-- AOS + Full JavaScript --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true,
            offset: 100
        });

        // === PARTICLE SUBTLE DI HERO ===
        function createParticles() {
            const canvas = document.getElementById('hero-particles');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            
            function resizeCanvas() {
                canvas.width = window.innerWidth;
                canvas.height = document.getElementById('hero').offsetHeight;
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            let particles = [];
            class Particle {
                constructor() {
                    this.x = Math.random() * canvas.width;
                    this.y = Math.random() * canvas.height;
                    this.size = Math.random() * 2.5 + 0.8;
                    this.speedX = Math.random() * 0.4 - 0.2;
                    this.speedY = Math.random() * 0.6 + 0.3;
                    this.opacity = Math.random() * 0.6 + 0.3;
                }
                update() {
                    this.x += this.speedX;
                    this.y += this.speedY;
                    if (this.y > canvas.height) this.y = 0;
                    if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
                }
                draw() {
                    ctx.fillStyle = `rgba(212, 160, 23, ${this.opacity})`;
                    ctx.beginPath();
                    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                    ctx.fill();
                }
            }

            function init() {
                particles = [];
                for (let i = 0; i < 80; i++) {
                    particles.push(new Particle());
                }
            }

            function animate() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                particles.forEach(p => {
                    p.update();
                    p.draw();
                });
                requestAnimationFrame(animate);
            }

            init();
            animate();
        }

        // Jalankan particle setelah halaman load
        window.addEventListener('load', () => {
            createParticles();
        });

        // === JS ASLI KAMU (tidak diubah sama sekali) ===
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

        // Portfolio tabs
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

        // Lightbox
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
            lightboxCaption.textContent = [category, title].filter(Boolean).join(' • ');
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

        // Hero Slider
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
                    heroEyebrow.innerHTML = '<span class="w-12 h-px bg-[#D4A017]"></span>' + (slide.dataset.eyebrow || '');
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