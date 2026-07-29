<x-guest-layout>
    <div class="relative flex h-screen w-full min-h-0 items-center justify-center overflow-hidden bg-cover bg-center p-3 sm:p-4"
         style="background-image: url('{{ asset('images/auth/bg-register.jpg') }}');">
        
        <!-- Dark overlay for readability -->
        <div class="absolute inset-0 bg-black/35 pointer-events-none"></div>

        <div class="relative z-10 w-full max-w-4xl">
            <div class="grid items-center gap-4 md:grid-cols-[0.85fr_1fr]">

                <!-- Info Side -->
                <div class="hidden md:flex h-full flex-col justify-center rounded-3xl border border-[#EDE0D0] bg-white p-5 text-center shadow-2xl">
                    <div class="mb-4 flex flex-col items-center gap-3">
                        <div>
                            <p class="font-display text-3xl font-bold tracking-tight text-[#3F2B1B]">Alter Studio</p>
                            <span class="mx-auto mt-2 block h-1 w-16 rounded-full bg-gradient-to-r from-[#D4A017] to-[#E07A5F]"></span>
                            <p class="text-xs text-[#8B7359]">Studio Fotografi Premium</p>
                        </div>
                    </div>

                    <h1 class="mb-3 font-display text-2xl font-semibold leading-tight text-[#3F2B1B]">
                        Buat Akun Baru
                    </h1>
                    <p class="mb-4 text-sm leading-6 text-[#5C432C]">
                        Daftar untuk memesan layanan fotografi, memantau pembayaran, dan mengakses link hasil foto Anda.
                    </p>

                    <a href="/" 
                       class="mx-auto mb-4 inline-flex items-center gap-2 rounded-2xl border border-[#E1D3C5] px-4 py-2 text-xs font-medium text-[#5C432C] transition-all hover:border-[#D4A017] hover:text-[#D4A017]">
                        <span>Kembali ke Beranda</span>
                    </a>

                    <div class="grid grid-cols-2 gap-2 text-left">
                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-3">
                            <i class="fa-solid fa-calendar-check mb-1.5 text-base text-[#D4A017]"></i>
                            <p class="text-xs font-medium text-[#3F2B1B]">Pemesanan Mudah</p>
                        </div>
                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-3">
                            <i class="fa-solid fa-shield-halved mb-1.5 text-base text-[#D4A017]"></i>
                            <p class="text-xs font-medium text-[#3F2B1B]">Pembayaran Aman</p>
                        </div>
                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-3">
                            <i class="fa-solid fa-camera mb-1.5 text-base text-[#D4A017]"></i>
                            <p class="text-xs font-medium text-[#3F2B1B]">Hasil Profesional</p>
                        </div>
                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FAF6F0] p-3">
                            <i class="fa-solid fa-download mb-1.5 text-base text-[#D4A017]"></i>
                            <p class="text-xs font-medium text-[#3F2B1B]">Akses Link Foto</p>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="rounded-3xl border border-[#EDE0D0] bg-white p-3.5 shadow-2xl sm:p-5">
                    <div class="mb-3 text-center">
                        <div class="flex min-w-0 items-center justify-center gap-3 text-center">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-white shadow-lg">
                                <i class="fa-solid fa-user-plus text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-display text-xl font-bold leading-tight tracking-tight text-[#3F2B1B]">Buat Akun Baru</h2>
                                <p class="mt-0.5 text-xs text-[#7A5B3A]">Bergabung bersama Alter Studio</p>
                            </div>
                        </div>

                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-1.5 sm:space-y-2">
                        @csrf

                        <div class="grid grid-cols-2 gap-2">
                            <!-- Name -->
                            <div class="min-w-0 space-y-1">
                                <label for="name" class="block text-xs font-medium text-[#5C432C]">Nama Lengkap</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                        <i class="fa-solid fa-user"></i>
                                    </span>
                                    <x-text-input id="name"
                                        class="block w-full rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] py-2 pl-9 pr-3 text-sm text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30"
                                        type="text"
                                        name="name"
                                        :value="old('name')"
                                        required autofocus autocomplete="name"
                                        placeholder="Nama lengkap" />
                                </div>
                                <x-input-error :messages="$errors->get('name')" class="text-rose-500 text-xs" />
                            </div>

                            <!-- Phone Number -->
                            <div class="min-w-0 space-y-1">
                                <label for="no_hp" class="block text-xs font-medium text-[#5C432C]">Nomor HP</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                        <i class="fa-solid fa-phone"></i>
                                    </span>
                                    <x-text-input id="no_hp"
                                        class="block w-full rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] py-2 pl-9 pr-3 text-sm text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30"
                                        type="tel"
                                        name="no_hp"
                                        :value="old('no_hp')"
                                        required inputmode="tel" autocomplete="tel" maxlength="20"
                                        placeholder="081234567890" />
                                </div>
                                <x-input-error :messages="$errors->get('no_hp')" class="text-rose-500 text-xs" />
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="space-y-1">
                            <label for="email" class="block text-xs font-medium text-[#5C432C]">Email</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                    <i class="fa-solid fa-envelope"></i>
                                </span>
                                <x-text-input id="email"
                                    class="block w-full rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] py-2 pl-11 pr-4 text-sm text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30"
                                    type="email"
                                    name="email"
                                    :value="old('email')"
                                    required
                                    placeholder="nama@email.com" />
                            </div>
                            <x-input-error :messages="$errors->get('email')" class="text-rose-500 text-xs" />
                        </div>

                        <!-- Password -->
                        <div class="space-y-1">
                            <label for="password" class="block text-xs font-medium text-[#5C432C]">Kata Sandi</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <x-text-input id="password"
                                    class="block w-full rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] py-2 pl-11 pr-12 text-sm text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30"
                                    type="password"
                                    name="password"
                                    required autocomplete="new-password"
                                    placeholder="Minimal 8 karakter" />
                                <button type="button" id="toggle-password"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[#8B7359] transition-colors hover:text-[#5C432C]">
                                    <i id="toggle-password-icon" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="text-rose-500 text-xs" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-1">
                            <label for="password_confirmation" class="block text-xs font-medium text-[#5C432C]">Konfirmasi Kata Sandi</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                <x-text-input id="password_confirmation"
                                    class="block w-full rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] py-2 pl-11 pr-12 text-sm text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    placeholder="Ketik ulang kata sandi" />
                                <button type="button" id="toggle-password-confirmation"
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[#8B7359] transition-colors hover:text-[#5C432C]">
                                    <i id="toggle-password-confirmation-icon" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="text-rose-500 text-xs" />
                        </div>

                        <!-- Terms -->
                        <div class="flex items-start gap-2">
                            <input type="checkbox" id="terms" name="terms"
                                   class="mt-0.5 h-4 w-4 rounded-lg border-[#E1D3C5] text-[#D4A017] focus:ring-[#D4A017]">
                            <label for="terms" class="text-xs leading-4 text-[#6F5134]">
                                Saya menyetujui 
                                <a href="#" class="font-medium text-[#D4A017] hover:underline">Syarat &amp; Ketentuan</a> 
                                dan 
                                <a href="#" class="font-medium text-[#D4A017] hover:underline">Kebijakan Privasi</a>.
                            </label>
                        </div>
                        <p id="terms-error" class="hidden text-xs text-red-500">
                            <i class="fa-solid fa-circle-exclamation mr-1"></i>
                            Anda harus menyetujui Syarat &amp; Ketentuan sebelum mendaftar.
                        </p>

                        <!-- Submit Button -->
                        <button type="submit"
                                class="flex w-full items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] py-2 text-sm font-semibold text-white shadow-lg shadow-[#D4A017]/30 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-xl">
                            <i class="fa-solid fa-user-plus"></i>
                            Daftar Sekarang
                        </button>

                        <!-- Login Link -->
                        <p class="text-center text-xs text-[#7A5B3A]">
                            Sudah punya akun? 
                            <a href="{{ route('login') }}" 
                               class="inline-flex items-center gap-1 font-semibold text-[#D4A017] transition-colors hover:text-[#E07A5F]">
                                Masuk sekarang
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggles = [
                {
                    button: document.getElementById('toggle-password'),
                    input: document.getElementById('password'),
                    icon: document.getElementById('toggle-password-icon')
                },
                {
                    button: document.getElementById('toggle-password-confirmation'),
                    input: document.getElementById('password_confirmation'),
                    icon: document.getElementById('toggle-password-confirmation-icon')
                }
            ];

            toggles.forEach(item => {
                if (!item.button || !item.input || !item.icon) return;

                item.button.addEventListener('click', function () {
                    const isPassword = item.input.type === 'password';
                    item.input.type = isPassword ? 'text' : 'password';
                    item.icon.classList.toggle('fa-eye', !isPassword);
                    item.icon.classList.toggle('fa-eye-slash', isPassword);
                });
            });
            // Validasi checkbox Syarat & Ketentuan dalam Bahasa Indonesia
            const registerForm  = document.querySelector('form[action*="register"]');
            const termsCheckbox = document.getElementById('terms');
            const termsError    = document.getElementById('terms-error');

            if (registerForm && termsCheckbox && termsError) {
                registerForm.addEventListener('submit', function (e) {
                    if (!termsCheckbox.checked) {
                        e.preventDefault();
                        termsError.classList.remove('hidden');
                        termsCheckbox.focus();
                        termsCheckbox.closest('.flex').classList.add('ring-1', 'ring-red-400', 'rounded-lg');
                    }
                });

                termsCheckbox.addEventListener('change', function () {
                    if (this.checked) {
                        termsError.classList.add('hidden');
                        this.closest('.flex').classList.remove('ring-1', 'ring-red-400', 'rounded-lg');
                    }
                });
            }
        });
    </script>
</x-guest-layout>
