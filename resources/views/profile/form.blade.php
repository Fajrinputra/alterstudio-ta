@php
    /** Halaman form edit profil */
    $user = $user ?? auth()->user();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#7a5b3a] flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-[#b58042]"></i>
                    Profil
                </p>
                <h2 class="font-display font-bold text-3xl text-[#3f2b1b]">Edit Profil</h2>
            </div>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-xl overflow-hidden">
                {{-- Header --}}
                <div class="px-8 py-6 border-b border-[#e3d5c4] bg-gradient-to-r from-[#faf3eb] to-white">
                    <h3 class="font-display text-xl text-[#3f2b1b] font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-circle-user text-[#b58042]"></i>
                        Form Edit Profil
                    </h3>
                    <p class="text-sm text-[#7a5b3a] mt-1">Perbarui informasi akun Anda</p>
                </div>

                {{-- Form --}}
                <div class="p-8">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        {{-- Grid 2 kolom --}}
                        <div class="grid md:grid-cols-2 gap-6">
                            {{-- Nama --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134]">
                                    <i class="fa-solid fa-user mr-1"></i>
                                    Nama Lengkap
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <input name="name" value="{{ old('name', $user->name) }}" 
                                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]" 
                                           required>
                                </div>
                                @error('name') 
                                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134]">
                                    <i class="fa-solid fa-envelope mr-1"></i>
                                    Email
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input name="email" type="email" value="{{ old('email', $user->email) }}" 
                                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]" 
                                           required>
                                </div>
                                @error('email') 
                                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134]">
                                    <i class="fa-solid fa-phone mr-1"></i>
                                    No. HP
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                        <i class="fa-solid fa-phone"></i>
                                    </span>
                                    <input name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" 
                                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                           placeholder="08xxxxxxxxxx">
                                </div>
                                @error('no_hp') 
                                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Role (disabled) --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134]">
                                    <i class="fa-solid fa-tag mr-1"></i>
                                    Role
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                        <i class="fa-solid fa-tag"></i>
                                    </span>
                                    <input value="{{ $user->role }}" disabled 
                                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#f5ecdf] text-[#8a6b47] cursor-not-allowed">
                                </div>
                            </div>
                        </div>

                        {{-- Avatar Upload --}}
                        <div class="space-y-3 pt-4 border-t border-[#e3d5c4]">
                            <label class="block text-sm font-medium text-[#6f5134]">
                                <i class="fa-solid fa-image mr-1"></i>
                                Avatar
                            </label>
                            
                            <div class="flex items-start gap-6">
                                {{-- Preview --}}
                                <div class="flex-shrink-0">
                                    @if($user->avatar_path)
                                        <img src="{{ Storage::url($user->avatar_path) }}" class="h-20 w-20 rounded-xl border-2 border-[#e3d5c4] object-cover shadow-md" alt="Avatar">
                                    @else
                                        <div class="h-20 w-20 rounded-xl border-2 border-[#e3d5c4] bg-gradient-to-br from-[#b58042]/10 to-[#8b5b2e]/10 flex items-center justify-center">
                                            <i class="fa-solid fa-user text-3xl text-[#b58042]"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Upload --}}
                                <div class="flex-1">
                                    <input type="file" name="avatar" accept="image/*" 
                                           class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer file:transition-colors">
                                    <p class="text-xs text-[#7a5b3a] mt-2">
                                        <i class="fa-solid fa-circle-info mr-1"></i>
                                        Format: JPG, PNG. Maksimal 2MB
                                    </p>
                                    @error('avatar') 
                                        <p class="text-xs text-red-600 flex items-center gap-1 mt-2">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Delete Avatar Button --}}
                            @if($user->avatar_path)
                                <div class="flex justify-end">
                                    <form method="POST" action="{{ route('profile.avatar.delete') }}" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-red-200 text-red-600 hover:bg-red-50 transition-colors text-sm">
                                            <i class="fa-solid fa-trash-can"></i>
                                            Hapus Avatar
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#e3d5c4]">
                            <a href="{{ route('profile.edit') }}" 
                               class="px-6 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-[#fcf7f1] transition-colors">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>