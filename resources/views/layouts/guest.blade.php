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
    </head>
    <body class="bg-gradient-to-br from-[#F8F1E7] to-[#f0e4d5] text-[#5b422b] antialiased min-h-screen" 
          style="font-family:'Plus Jakarta Sans',sans-serif;">
        
        {{-- Background Pattern --}}
        <div class="fixed inset-0 bg-[url('data:image/svg+xml,%3Csvg width="80" height="80" viewBox="0 0 80 80" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23b58042" fill-opacity="0.03"%3E%3Cpath d="M50 50v-5h-5v5h-5v5h5v5h5v-5h5v-5h-5zm0-40V5h-5v5h-5v5h5v5h5v-5h5V10h-5zM10 50v-5H5v5H0v5h5v5h5v-5h5v-5h-5zM10 10V5H5v5H0v5h5v5h5v-5h5V10h-5z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-50"></div>
        
        <div class="min-h-screen flex items-center justify-center p-4 relative">
            <div class="w-full">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>