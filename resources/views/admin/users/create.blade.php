@php
    use App\Enums\Role;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#D4A017]"></i>
                    Kelola Pengguna
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Tambah <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Pengguna Baru</span>
                </h2>
            </div>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] hover:shadow transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/10 via-[#E07A5F]/10 rounded-3xl blur-3xl"></div>
                <div class="relative glass border border-[#EDE0D0] rounded-3xl p-10 shadow-2xl backdrop-blur-2xl">
                    
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-3xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center">
                            <i class="fa-solid fa-user-plus text-[#D4A017] text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-display text-3xl text-[#3F2B1B]">Form Tambah Pengguna</h3>
                            <p class="text-[#7A5B3A]">Buat akun baru untuk tim atau klien</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-8">
                        @csrf
                        
                        {{-- Grid 2 Kolom --}}
                        <div class="grid md:grid-cols-2 gap-6">
                            {{-- Nama Lengkap --}}
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-user text-[#D4A017]"></i>
                                    NAMA LENGKAP
                                </label>
                                <input name="name" required
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                       placeholder="Nama lengkap pengguna">
                            </div>
                            
                            {{-- Email --}}
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-envelope text-[#D4A017]"></i>
                                    EMAIL
                                </label>
                                <input name="email" type="email" required
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                       placeholder="nama@email.com">
                            </div>
                            
                            {{-- No. HP --}}
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-phone text-[#D4A017]"></i>
                                    NOMOR HP
                                </label>
                                <input name="no_hp" type="tel" inputmode="tel" autocomplete="tel" maxlength="20"
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                       placeholder="08xxxxxxxxxx">
                            </div>
                            
                            {{-- Role Utama --}}
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-tag text-[#D4A017]"></i>
                                    ROLE UTAMA
                                </label>
                                <select name="role" 
                                        class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                    @foreach($roles as $roleValue)
                                        <option value="{{ $roleValue }}">{{ ucfirst(strtolower($roleValue)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                <i class="fa-solid fa-lock text-[#D4A017]"></i>
                                PASSWORD
                            </label>
                            <input name="password" type="password" required minlength="8" autocomplete="new-password"
                                   placeholder="Minimal 8 karakter"
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                            <p class="text-xs text-[#8B7359] flex items-center gap-2">
                                <i class="fa-solid fa-circle-info"></i>
                                Gunakan minimal 8 karakter dan berikan password kepada pemilik akun secara aman.
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-[#EDE0D0]">
                            <a href="{{ route('admin.users.index') }}"
                               class="px-8 py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] font-medium hover:bg-white hover:border-[#D4A017] transition-all">
                                Batal
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-3 px-10 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Simpan Pengguna Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
