@php
    /** Halaman ringkasan profil (read-only) */
    $user = $user ?? auth()->user();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-user text-[#D4A017]"></i>
                    Profil
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B]">
                    Informasi Akun
                </h2>
            </div>
            <a href="{{ route('profile.form') }}" 
               class="inline-flex items-center justify-center gap-3 px-6 py-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all">
                <i class="fa-solid fa-pen-to-square"></i>
                Edit Profil
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
          
            {{-- Session Status --}}
            @if (session('status'))
                @php
                    $mapStatus = [
                        'profile-updated' => 'Profil berhasil diperbarui.',
                        'avatar-updated'  => 'Avatar berhasil diperbarui.',
                    ];
                    $flashText = $mapStatus[session('status')] ?? session('status');
                @endphp
                <div class="flex items-center gap-3 p-5 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-sm">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                    <span class="font-medium">{{ $flashText }}</span>
                </div>
            @endif

            {{-- Profile Card Premium --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/10 via-[#E07A5F]/10 rounded-3xl blur-3xl"></div>
                <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl shadow-2xl overflow-hidden">
                    
                    {{-- Header dengan Gradient + Avatar --}}
                    <div class="h-48 bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] relative">
                        <div class="absolute -bottom-12 left-8">
                            @if($user->avatar_path)
                                <img src="{{ Storage::url($user->avatar_path) }}" 
                                     class="h-28 w-28 rounded-3xl border-4 border-white shadow-2xl object-cover" alt="Avatar">
                            @else
                                @php $initial = strtoupper(mb_substr($user->name ?? '', 0, 1)); @endphp
                                <div class="h-28 w-28 rounded-3xl border-4 border-white shadow-2xl bg-gradient-to-br from-white to-[#F4EDE4] flex items-center justify-center text-[#D4A017] text-5xl font-bold">
                                    {{ $initial }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="pt-20 px-8 pb-10">
                        <div class="grid md:grid-cols-2 gap-6">
                            
                            {{-- Left Column --}}
                            <div class="space-y-6">
                                <div class="bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl p-6">
                                    <p class="text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">NAMA LENGKAP</p>
                                    <p class="text-xl font-semibold text-[#3F2B1B]">{{ $user->name }}</p>
                                </div>

                                <div class="bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl p-6">
                                    <p class="text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">EMAIL</p>
                                    <p class="text-lg text-[#3F2B1B]">{{ $user->email }}</p>
                                </div>
                            </div>

                            {{-- Right Column --}}
                            <div class="space-y-6">
                                <div class="bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl p-6">
                                    <p class="text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">NOMOR HP</p>
                                    <p class="text-lg text-[#3F2B1B]">{{ $user->no_hp ?? 'Belum diisi' }}</p>
                                </div>

                                <div class="bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl p-6">
                                    <p class="text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">ROLE AKUN</p>
                                    <span class="inline-block px-6 py-2 rounded-3xl bg-[#D4A017]/10 text-[#D4A017] border border-[#D4A017]/20 font-medium">
                                        {{ ucfirst($user->role?->value ?? $user->role) }}
                                    </span>
                                </div>

                                <div class="bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl p-6">
                                    <p class="text-xs font-medium text-[#7A5B3A] tracking-widest mb-2">MEMBER SEJAK</p>
                                    <p class="text-lg text-[#3F2B1B]">{{ $user->created_at->format('d F Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delete Account Section --}}
            @if($user->role !== \App\Enums\Role::MANAGER)
                <div class="bg-white/80 backdrop-blur-sm border border-[#EDE0D0] rounded-3xl shadow-xl p-8">
                    @include('profile.partials.delete-user-form')
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
