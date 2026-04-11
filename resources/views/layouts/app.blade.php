<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Alter Studio') }}</title>
    
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
            <div class="flex flex-col min-h-screen lg:ml-72 transition-all duration-300">
                
                @include('layouts.navigation')

                {{-- Header Slot --}}
                @isset($header)
                    <header class="bg-white/80 backdrop-blur-xl border-b border-[#EDE0D0] sticky top-16 z-30 shadow-sm">
                        <div class="max-w-7xl mx-auto py-6 px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                {{-- Main Content --}}
                <main class="flex-1 pb-12">
                    <div class="max-w-7xl mx-auto px-6 lg:px-8">
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
                <header class="bg-white/80 backdrop-blur-xl border-b border-[#EDE0D0] sticky top-16 z-30 shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="pb-12">
                <div class="max-w-7xl mx-auto px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    @endif

    {{-- Floating WhatsApp Button (hanya untuk Client) --}}
    @if($isClient)
        <a href="{{ $waAdminUrl }}" 
           target="_blank" 
           rel="noopener noreferrer"
           class="fixed bottom-8 right-8 z-50 w-16 h-16 rounded-3xl bg-gradient-to-br from-[#25D366] via-[#128C7E] to-[#0E7C6B] text-white shadow-2xl hover:scale-110 active:scale-95 transition-all duration-300 flex items-center justify-center ring-4 ring-white/50">
            <i class="fa-brands fa-whatsapp text-3xl"></i>
        </a>
    @endif

    @stack('scripts')
</body>
</html>