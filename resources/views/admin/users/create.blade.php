@php
    use App\Enums\Role;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#b58042]"></i>
                    Kelola Pengguna
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Tambah <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">Pengguna Baru</span>
                </h2>
            </div>
            <a href="{{ route('admin.users.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/20 to-[#8b5b2e]/20 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-8 shadow-lg">
                    <h3 class="text-xl font-light text-[#3f2b1b] mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-user-plus text-[#b58042]"></i>
                        Form Tambah Pengguna
                    </h3>

                    <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                        @csrf

                        {{-- Grid 2 kolom --}}
                        <div class="grid md:grid-cols-2 gap-5">
                            {{-- Nama --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-user text-[#b58042]"></i>
                                    Nama Lengkap
                                </label>
                                <input name="name" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                       placeholder="Masukkan nama lengkap">
                            </div>

                            {{-- Email --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-envelope text-[#b58042]"></i>
                                    Email
                                </label>
                                <input name="email" type="email" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                       placeholder="nama@email.com">
                            </div>

                            {{-- No. HP --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-phone text-[#b58042]"></i>
                                    No. HP
                                </label>
                                <input name="no_hp" type="text"
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                       placeholder="08xxxxxxxxxx">
                            </div>

                            {{-- Role --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-tag text-[#b58042]"></i>
                                    Role
                                </label>
                                <select name="role" class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                    @foreach(Role::cases() as $role)
                                        <option value="{{ $role->value }}">{{ $role->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                <i class="fa-solid fa-lock text-[#b58042]"></i>
                                Password
                            </label>
                            <input name="password" type="text" 
                                   placeholder="Kosongkan = 'password'"
                                   class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                            <p class="text-xs text-[#8b7359] flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Jika dikosongkan, password default adalah "password"
                            </p>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#e3d5c4]">
                            <a href="{{ route('admin.users.index') }}" 
                               class="px-6 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white/80 transition-all">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Simpan Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>