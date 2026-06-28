<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Alter Studio') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Font Awesome --}}
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important;}</style>
</head>
<body class="antialiased bg-[#FAF6F0] text-[#3F2B1B] min-h-screen"
      style="font-family:'Plus Jakarta Sans',sans-serif;">

    {{-- Subtle Background Pattern --}}
    <div class="fixed inset-0 bg-[url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23D4A017%22 fill-opacity=%220.04%22%3E%3Cpath d=%22M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-40 pointer-events-none"></div>

    @php
        $user = Auth::user();
        $hasSidebar = (bool) $user;
        $isClient = $user && ($user->role === \App\Enums\Role::CLIENT || $user->role === 'CLIENT');
        $waAdminUrl = config('services.contact.whatsapp', 'https://wa.me/6281234567890');
    @endphp

    @if($hasSidebar)
        {{-- Layout dengan Sidebar --}}
        <div x-data="{ mobileSidebar: false }" 
             @toggle-sidebar.window="mobileSidebar = !mobileSidebar" 
             class="min-h-screen relative">

            @include('layouts.sidebar')

            {{-- Main Content Area --}}
            <div class="flex flex-col min-h-screen lg:ml-56 transition-all duration-300">
                
                @include('layouts.navigation')

                {{-- Header Slot --}}
                @isset($header)
                    <header class="bg-white/80 backdrop-blur-xl border-b border-[#EDE0D0] sticky top-12 z-30 shadow-sm">
                        <div class="max-w-[1360px] mx-auto py-2.5 px-4 sm:px-5 lg:px-6 [&_h2]:!text-2xl [&_h2]:md:!text-3xl [&_h2]:!leading-tight [&_p]:!text-xs [&_p]:!leading-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                {{-- Main Content --}}
                <main class="flex-1 pb-8">
                    <div class="max-w-[1360px] mx-auto px-4 sm:px-5 lg:px-6">
                        <x-system-feedback />
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

    @else
        {{-- Layout tanpa Sidebar (untuk Guest / Landing) --}}
        <div class="min-h-screen relative">
            
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white/80 backdrop-blur-xl border-b border-[#EDE0D0] sticky top-12 z-30 shadow-sm">
                    <div class="max-w-[1360px] mx-auto py-2.5 px-4 sm:px-5 lg:px-6 [&_h2]:!text-2xl [&_h2]:md:!text-3xl [&_h2]:!leading-tight [&_p]:!text-xs [&_p]:!leading-5">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="pb-8">
                <div class="max-w-[1360px] mx-auto px-4 sm:px-5 lg:px-6">
                    <x-system-feedback />
                    {{ $slot }}
                </div>
            </main>
        </div>
    @endif

    {{-- Floating WhatsApp Button (hanya untuk Client) --}}
    @if($isClient)
        <div x-data="{ faqOpen: false }" @keydown.escape.window="faqOpen = false">
            <button type="button"
                    @click="faqOpen = true"
                    aria-label="Buka rules pemesanan"
                    class="fixed bottom-24 right-4 z-50 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017] via-[#E0912F] to-[#E07A5F] text-white shadow-xl ring-4 ring-white/50 transition-all duration-300 hover:scale-105 active:scale-95 sm:right-6 sm:h-14 sm:w-14">
                <i class="fa-solid fa-circle-question text-xl sm:text-2xl"></i>
            </button>

            <div x-cloak x-show="faqOpen" x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center px-4 py-6">
                <button type="button"
                        class="absolute inset-0 bg-[#3F2B1B]/45 backdrop-blur-sm"
                        @click="faqOpen = false"
                        aria-label="Tutup panduan"></button>

                <section x-show="faqOpen"
                         x-transition
                         class="relative max-h-[88vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-[#EDE0D0] bg-white p-5 shadow-2xl shadow-[#3F2B1B]/25 sm:p-6">
                    <button type="button"
                            @click="faqOpen = false"
                            class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-xl text-[#8B7359] transition hover:bg-[#FAF6F0] hover:text-[#3F2B1B]"
                            aria-label="Tutup panduan">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>

                    <div class="pr-12">
                        <p class="inline-flex items-center gap-2 rounded-2xl bg-[#FAF6F0] px-4 py-2 text-xs font-semibold uppercase tracking-wider text-[#8B7359]">
                            <i class="fa-solid fa-circle-info text-[#D4A017]"></i>
                            Panduan
                        </p>
                        <h2 class="mt-4 font-display text-2xl font-semibold text-[#3F2B1B]">Tata Cara dan Persyaratan Pemesanan</h2>
                        <p class="mt-3 text-sm leading-6 text-[#7A5B3A]">
                            Panduan ini berisi langkah pemesanan dan syarat penting agar proses foto, pembayaran, dan pengambilan hasil berjalan lancar.
                        </p>
                    </div>

                    <div class="mt-6 space-y-4 text-sm leading-6 text-[#5C432C]">
                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-4">
                            <p class="font-semibold text-[#3F2B1B]">1. Pilih Layanan dan Jadwal</p>
                            <p class="mt-2">Pilih paket, cabang studio, ruangan, tanggal, jam, dan add-on yang dibutuhkan. Sistem hanya menampilkan slot yang masih tersedia sesuai durasi paket, tambahan waktu, jeda antar sesi, dan jam operasional studio.</p>
                        </div>

                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-4">
                            <p class="font-semibold text-[#3F2B1B]">2. Tunggu Konfirmasi</p>
                            <p class="mt-2">Pemesanan yang dikirim akan berstatus diajukan. Admin atau manajer akan memeriksa pemesanan terlebih dahulu sebelum pembayaran dibuka.</p>
                        </div>

                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-4">
                            <p class="font-semibold text-[#3F2B1B]">3. Lakukan Pembayaran</p>
                            <p class="mt-2">Setelah dikonfirmasi, Anda dapat membayar DP sebesar 10% atau langsung lunas. Jika memilih DP, sisa pembayaran wajib dilunasi sebelum proses pasca-produksi dan pembagian link foto dapat dilanjutkan.</p>
                        </div>

                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-4">
                            <p class="font-semibold text-[#3F2B1B]">4. Foto Mentah dan Permintaan Edit</p>
                            <p class="mt-2">Link Google Drive foto mentah hanya dapat dibuka setelah pembayaran lunas dan berlaku selama 3 hari. Pilih maksimal 10 kode foto, lalu tulis deskripsi edit dengan jelas agar editor memahami permintaan Anda.</p>
                        </div>

                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-4">
                            <p class="font-semibold text-[#3F2B1B]">5. Syarat Penting</p>
                            <p class="mt-2">Tanggal pemesanan maksimal 1 bulan dari hari ini dan tidak boleh memilih tanggal yang sudah lewat. Slot pada hari yang sama otomatis disembunyikan jika jamnya sudah berlalu.</p>
                        </div>
                    </div>
                </section>
            </div>

            <a href="{{ $waAdminUrl }}" 
               target="_blank" 
               rel="noopener noreferrer"
               aria-label="Hubungi admin melalui WhatsApp"
               class="fixed bottom-6 right-4 z-50 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-[#25D366] via-[#128C7E] to-[#0E7C6B] text-white shadow-xl ring-4 ring-white/50 transition-all duration-300 hover:scale-105 active:scale-95 sm:right-6 sm:h-14 sm:w-14">
                <i class="fa-brands fa-whatsapp text-xl sm:text-2xl"></i>
            </a>
        </div>
    @endif

    @stack('scripts')
</body>
</html>
