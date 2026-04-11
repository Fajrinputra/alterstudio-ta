@php
    /** Halaman form edit profil + password */
    $user = $user ?? auth()->user();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-[#D4A017]"></i>
                    Pengaturan Profil
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B]">
                    Edit Profil
                </h2>
            </div>
            <a href="{{ route('profile.edit') }}"
               class="inline-flex items-center justify-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Profil
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/10 via-[#E07A5F]/10 rounded-3xl blur-3xl"></div>
                <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl shadow-2xl overflow-hidden">
                    <div class="px-10 py-8 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                        <h3 class="font-display text-2xl text-[#3F2B1B] flex items-center gap-3">
                            <i class="fa-solid fa-circle-user text-[#D4A017]"></i>
                            Pengaturan Akun
                        </h3>
                        <p class="text-[#7A5B3A] mt-1">Perbarui informasi profil, foto, dan password akun Anda.</p>
                    </div>

                    <div class="p-8 md:p-10 space-y-8">
                        <section id="profile-info">
                            @include('profile.partials.update-profile-information-form')
                        </section>

                        <section id="password-form">
                            @include('profile.partials.update-password-form')
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
