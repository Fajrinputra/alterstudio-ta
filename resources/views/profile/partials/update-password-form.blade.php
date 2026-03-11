<section class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-xl p-6">
    <header class="mb-6">
        <h2 class="text-xl font-display font-semibold text-[#3f2b1b] flex items-center gap-2">
            <i class="fa-solid fa-lock text-[#b58042]"></i>
            {{ __('Ganti Password') }}
        </h2>
        <p class="text-sm text-[#7a5b3a] mt-1">Gunakan password kuat untuk keamanan akun.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        {{-- Current Password --}}
        <div class="space-y-2">
            <x-input-label for="update_password_current_password" :value="__('Password Saat Ini')" class="text-sm font-medium text-[#6f5134]" />
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                    <i class="fa-solid fa-lock"></i>
                </span>
                <x-text-input id="update_password_current_password" 
                              name="current_password" 
                              type="password" 
                              class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] focus:border-[#b58042] focus:ring-[#b58042]" 
                              autocomplete="current-password"
                              placeholder="Masukkan password saat ini" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="text-rose-500 text-sm mt-1" />
        </div>

        {{-- New Password --}}
        <div class="space-y-2">
            <x-input-label for="update_password_password" :value="__('Password Baru')" class="text-sm font-medium text-[#6f5134]" />
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                    <i class="fa-solid fa-key"></i>
                </span>
                <x-text-input id="update_password_password" 
                              name="password" 
                              type="password" 
                              class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] focus:border-[#b58042] focus:ring-[#b58042]" 
                              autocomplete="new-password"
                              placeholder="Minimal 8 karakter" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="text-rose-500 text-sm mt-1" />
        </div>

        {{-- Confirm Password --}}
        <div class="space-y-2">
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Password')" class="text-sm font-medium text-[#6f5134]" />
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                    <i class="fa-solid fa-key"></i>
                </span>
                <x-text-input id="update_password_password_confirmation" 
                              name="password_confirmation" 
                              type="password" 
                              class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] focus:border-[#b58042] focus:ring-[#b58042]" 
                              autocomplete="new-password"
                              placeholder="Ketik ulang password baru" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="text-rose-500 text-sm mt-1" />
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center gap-4 pt-2">
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <i class="fa-solid fa-floppy-disk"></i>
                {{ __('Simpan Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" 
                   x-show="show" 
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)" 
                   class="text-sm text-emerald-600 flex items-center gap-1">
                    <i class="fa-solid fa-circle-check"></i>
                    Tersimpan.
                </p>
            @endif
        </div>
    </form>
</section>