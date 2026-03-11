<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        
        {{-- Fonts --}}
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        {{-- Font Awesome --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none!important;}</style>
    </head>
    <body class="antialiased bg-[#F8F1E7] text-[#5b422b]" style="font-family:'Plus Jakarta Sans',sans-serif;">
        @php
            $user = Auth::user();
            $hasSidebar = (bool) $user;
        @endphp
        
        {{-- Background Pattern --}}
        <div class="fixed inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23b58042" fill-opacity="0.03"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-50 pointer-events-none"></div>
        
        @if($hasSidebar)
            <div x-data="{ mobileSidebar: false }" @toggle-sidebar.window="mobileSidebar = !mobileSidebar" class="min-h-screen relative">
                @include('layouts.sidebar')
                
                {{-- Main Content dengan transisi --}}
                <div class="flex flex-col min-h-screen lg:ml-72 transition-all duration-300">
                    @include('layouts.navigation')
                    
                    @isset($header)
                        <header class="bg-white/70 backdrop-blur-md border-b border-[#e3d5c4]/50 sticky top-16 z-30 shadow-sm">
                            <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endisset
                    
                    <main class="flex-1">
                        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                            {{ $slot }}
                        </div>
                    </main>
                </div>
            </div>
        @else
            <div class="min-h-screen relative">
                @include('layouts.navigation')
                
                @isset($header)
                    <header class="bg-white/70 backdrop-blur-md border-b border-[#e3d5c4]/50 sticky top-16 z-30 shadow-sm">
                        <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset
                
                <main>
                    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        @endif
        
        @stack('scripts')
    </body>
</html>
