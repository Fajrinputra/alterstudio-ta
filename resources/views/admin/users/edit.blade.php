@php
    use App\Enums\Role;
    $primaryRole = $user->role instanceof Role ? $user->role : Role::from($user->role);
    $isOwnerAccount = $primaryRole === Role::OWNER;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-[#D4A017]"></i>
                    Kelola Pengguna
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Edit <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Akun Pengguna</span>
                </h2>
            </div>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] hover:shadow transition-all">
                Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/10 via-[#E07A5F]/10 rounded-3xl blur-3xl"></div>
                <div class="relative bg-white/85 border border-[#EDE0D0] rounded-3xl p-10 shadow-2xl backdrop-blur-2xl">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-3xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center">
                            <i class="fa-solid fa-user-gear text-[#D4A017] text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-display text-3xl text-[#3F2B1B]">Form Edit Pengguna</h3>
                            <p class="text-[#7A5B3A]">Perbarui identitas, role, dan status akun.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-8">
                        @csrf
                        @method('PUT')

                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-user text-[#D4A017]"></i>
                                    NAMA LENGKAP
                                </label>
                                <input name="name" required value="{{ old('name', $user->name) }}"
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                       placeholder="Nama lengkap pengguna">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-envelope text-[#D4A017]"></i>
                                    EMAIL
                                </label>
                                <input name="email" type="email" required value="{{ old('email', $user->email) }}"
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                       placeholder="nama@email.com">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-phone text-[#D4A017]"></i>
                                    NOMOR HP
                                </label>
                                <input name="no_hp" type="tel" inputmode="tel" autocomplete="tel" maxlength="20" value="{{ old('no_hp', $user->no_hp) }}"
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                       placeholder="08xxxxxxxxxx">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-tag text-[#D4A017]"></i>
                                    ROLE UTAMA
                                </label>
                                <select name="role"
                                        @disabled($isOwnerAccount)
                                        class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                    @foreach($roles as $roleValue)
                                        <option value="{{ $roleValue }}" @selected(old('role', $primaryRole->value) === $roleValue)>{{ ucfirst(strtolower($roleValue)) }}</option>
                                    @endforeach
                                </select>
                                @if($isOwnerAccount)
                                    <input type="hidden" name="role" value="{{ Role::OWNER->value }}">
                                    <p class="text-xs text-[#8B7359]">Role owner tidak dapat diubah.</p>
                                @endif
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest flex items-center gap-2">
                                    <i class="fa-solid fa-lock text-[#D4A017]"></i>
                                    PASSWORD BARU
                                </label>
                                <input name="password" type="text"
                                       placeholder="Kosongkan jika tidak diganti"
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                            </div>

                            <div class="space-y-2 rounded-3xl border border-[#EDE0D0] bg-white/70 px-6 py-5">
                                <label class="flex items-center gap-3 text-[#3F2B1B]">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) @disabled($isOwnerAccount)
                                           class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017] focus:ring-[#D4A017]">
                                    <span class="font-medium">Akun aktif</span>
                                </label>
                                @if($isOwnerAccount)
                                    <input type="hidden" name="is_active" value="1">
                                    <p class="text-xs text-[#8B7359]">Akun owner tidak dapat dinonaktifkan.</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-[#EDE0D0]">
                            <a href="{{ route('admin.users.index') }}"
                               class="px-8 py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] font-medium hover:bg-white hover:border-[#D4A017] transition-all">
                                Batal
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center gap-3 px-10 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all">
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
