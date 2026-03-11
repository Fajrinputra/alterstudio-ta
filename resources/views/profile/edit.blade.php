@php
    /** Halaman ringkasan profil (read-only) */
    $user = $user ?? auth()->user();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#7a5b3a] flex items-center gap-2">
                    <i class="fa-solid fa-user text-[#b58042]"></i>
                    Profil
                </p>
                <h2 class="font-display font-bold text-3xl text-[#3f2b1b]">Informasi Akun</h2>
            </div>
            <a href="{{ route('profile.form') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-pen-to-square"></i>
                Edit Profil
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Session Status --}}
            @if (session('status'))
                @php
                    $mapStatus = [
                        'profile-updated' => 'Profil berhasil diperbarui.',
                        'avatar-updated' => 'Avatar berhasil diperbarui.',
                    ];
                    $flashText = $mapStatus[session('status')] ?? session('status');
                @endphp
                <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span class="text-sm font-medium">{{ $flashText }}</span>
                </div>
            @endif

            {{-- Profile Card --}}
            <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-xl overflow-hidden">
                {{-- Header with gradient --}}
                <div class="h-24 bg-gradient-to-r from-[#b58042] to-[#8b5b2e] relative">
                    <div class="absolute -bottom-12 left-8">
                        @if($user->avatar_path)
                            <img src="{{ Storage::url($user->avatar_path) }}" class="h-24 w-24 rounded-2xl border-4 border-white shadow-xl object-cover" alt="Avatar">
                        @else
                            @php $initial = strtoupper(mb_substr($user->name,0,1)); @endphp
                            <div class="h-24 w-24 rounded-2xl border-4 border-white shadow-xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] flex items-center justify-center text-white text-3xl font-bold">
                                {{ $initial }}
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Content --}}
                <div class="pt-16 p-8">
                    <div class="grid md:grid-cols-2 gap-6">
                        {{-- Left Column --}}
                        <div class="space-y-4">
                            <div class="bg-[#fcf7f1] rounded-xl p-4">
                                <p class="text-xs text-[#7a5b3a] mb-1 flex items-center gap-1">
                                    <i class="fa-solid fa-user text-[#b58042]"></i>
                                    Nama Lengkap
                                </p>
                                <p class="font-semibold text-lg text-[#3f2b1b]">{{ $user->name }}</p>
                            </div>

                            <div class="bg-[#fcf7f1] rounded-xl p-4">
                                <p class="text-xs text-[#7a5b3a] mb-1 flex items-center gap-1">
                                    <i class="fa-solid fa-envelope text-[#b58042]"></i>
                                    Email
                                </p>
                                <p class="font-semibold text-[#3f2b1b]">{{ $user->email }}</p>
                            </div>
                        </div>

                        {{-- Right Column --}}
                        <div class="space-y-4">
                            <div class="bg-[#fcf7f1] rounded-xl p-4">
                                <p class="text-xs text-[#7a5b3a] mb-1 flex items-center gap-1">
                                    <i class="fa-solid fa-phone text-[#b58042]"></i>
                                    No. HP
                                </p>
                                <p class="font-semibold text-[#3f2b1b]">{{ $user->no_hp ?? '-' }}</p>
                            </div>

                            <div class="bg-[#fcf7f1] rounded-xl p-4">
                                <p class="text-xs text-[#7a5b3a] mb-1 flex items-center gap-1">
                                    <i class="fa-solid fa-tag text-[#b58042]"></i>
                                    Role
                                </p>
                                <p class="font-semibold text-[#3f2b1b]">
                                    <span class="px-3 py-1 rounded-full text-xs bg-[#b58042]/10 text-[#b58042] border border-[#b58042]/20">
                                        {{ $user->role }}
                                    </span>
                                </p>
                            </div>

                            <div class="bg-[#fcf7f1] rounded-xl p-4">
                                <p class="text-xs text-[#7a5b3a] mb-1 flex items-center gap-1">
                                    <i class="fa-solid fa-calendar text-[#b58042]"></i>
                                    Member Sejak
                                </p>
                                <p class="font-semibold text-[#3f2b1b]">{{ $user->created_at->format('d F Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Delete Account Section --}}
            @if($user->role !== \App\Enums\Role::MANAGER)
                <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-xl p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            @endif
        </div>
    </div>
</x-app-layout>