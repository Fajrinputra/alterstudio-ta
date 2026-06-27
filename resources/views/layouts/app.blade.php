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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
                        aria-label="Tutup rules"></button>

                <section x-show="faqOpen"
                         x-transition
                         class="relative max-h-[88vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-[#EDE0D0] bg-white p-5 shadow-2xl shadow-[#3F2B1B]/25 sm:p-6">
                    <button type="button"
                            @click="faqOpen = false"
                            class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-xl text-[#8B7359] transition hover:bg-[#FAF6F0] hover:text-[#3F2B1B]"
                            aria-label="Tutup rules">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>

                    <div class="pr-12">
                        <p class="inline-flex items-center gap-2 rounded-2xl bg-[#FAF6F0] px-4 py-2 text-xs font-semibold uppercase tracking-wider text-[#8B7359]">
                            <i class="fa-solid fa-circle-info text-[#D4A017]"></i>
                            Rules
                        </p>
                        <h2 class="mt-4 font-display text-2xl font-semibold text-[#3F2B1B]">Panduan Pemesanan Alter Studio</h2>
                        <p class="mt-3 text-sm leading-6 text-[#7A5B3A]">
                            Pemesanan akan masuk sebagai pengajuan terlebih dahulu dan perlu dikonfirmasi admin atau manajer sebelum pembayaran dibuka. Setelah dikonfirmasi, klien dapat memilih pembayaran DP sebesar 10% dari total pemesanan atau langsung lunas. Jika memilih DP, sisa pembayaran wajib dilunasi sebelum proses pasca-produksi dapat berjalan.
                        </p>
                    </div>

                    <div class="mt-6 space-y-4 text-sm leading-6 text-[#5C432C]">
                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-4">
                            <p class="font-semibold text-[#3F2B1B]">Aturan Jadwal</p>
                            <p class="mt-2">
                                Pilihan jam mengikuti durasi paket, add-on tambah waktu, jeda antar sesi, kapasitas ruangan cabang, dan jam operasional studio. Untuk pemesanan di hari yang sama, jam yang sudah lewat tidak dapat dipilih.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-4">
                            <p class="font-semibold text-[#3F2B1B]">Aturan Pembayaran</p>
                            <p class="mt-2">
                                Pembayaran DP menandakan pemesanan sudah diamankan, tetapi belum dianggap lunas. Admin dapat menandai pelunasan jika sisa pembayaran dibayar di lokasi. Pemesanan yang sudah DP tidak dapat dibatalkan dari aksi admin biasa.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-4">
                            <p class="font-semibold text-[#3F2B1B]">Aturan Pasca-Produksi</p>
                            <p class="mt-2">
                                Fotografer baru dapat membagikan link Google Drive foto mentah setelah pemesanan terjadwal dan pembayaran sudah lunas. Link Drive berlaku selama 3 hari, sehingga klien disarankan segera membuka folder dan mencatat kode foto yang ingin diedit.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-4">
                            <p class="font-semibold text-[#3F2B1B]">Aturan Edit</p>
                            <p class="mt-2">
                                Klien dapat mengirim maksimal 10 kode foto beserta deskripsi permintaan edit. Pastikan kode foto dan deskripsi edit ditulis dengan jelas karena permintaan akan diproses sebagai acuan hasil final.
                            </p>
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
