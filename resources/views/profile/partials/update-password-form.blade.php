<section class="bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl shadow-xl p-8">
    <header class="mb-8">
        <h2 class="font-display text-2xl text-[#3F2B1B] font-semibold flex items-center gap-3">
            <i class="fa-solid fa-lock text-[#D4A017]"></i>
            {{ __('Ganti Password') }}
        </h2>
        <p class="text-sm text-[#7A5B3A] mt-2">Gunakan password yang kuat untuk menjaga keamanan akun Anda.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        @method('put')

        {{-- Current Password --}}
        <div class="space-y-2">
            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Password Saat Ini</label>
            <div class="relative">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[#D4A017]">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <x-text-input id="update_password_current_password"
                              name="current_password"
                              type="password"
                              class="w-full pl-12 pr-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20"
                              autocomplete="current-password"
                              placeholder="Masukkan password saat ini" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="text-red-600 text-sm" />
        </div>

        {{-- New Password --}}
        <div class="space-y-2">
            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Password Baru</label>
            <div class="relative">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[#D4A017]">
                    <i class="fa-solid fa-key"></i>
                </span>
                <x-text-input id="update_password_password"
                              name="password"
                              type="password"
                              class="w-full pl-12 pr-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20"
                              autocomplete="new-password"
                              placeholder="Minimal 8 karakter" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="text-red-600 text-sm" />
        </div>

        {{-- Confirm Password --}}
        <div class="space-y-2">
            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Konfirmasi Password Baru</label>
            <div class="relative">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[#D4A017]">
                    <i class="fa-solid fa-key"></i>
                </span>
                <x-text-input id="update_password_password_confirmation"
                              name="password_confirmation"
                              type="password"
                              class="w-full pl-12 pr-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20"
                              autocomplete="new-password"
                              placeholder="Ketik ulang password baru" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="text-red-600 text-sm" />
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center gap-4 pt-4">
            <button type="submit"
                    class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all">
                <i class="fa-solid fa-floppy-disk"></i>
                {{ __('Simpan Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm text-emerald-600 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    Password berhasil diperbarui.
                </p>
            @endif
        </div>
    </form>
</section>