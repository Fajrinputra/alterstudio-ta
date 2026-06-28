@php
    /** Halaman khusus ubah password */
    $user = $user ?? auth()->user();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-lock text-[#D4A017]"></i>
                    Pengaturan Profil
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B]">
                    Ubah Kata Sandi
                </h2>
            </div>
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center justify-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all">
                Kembali ke Profil
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('profile.partials.update-password-form')
        </div>
    </div>
</x-app-layout>
