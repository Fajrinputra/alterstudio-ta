<x-guest-layout>
    <div class="relative flex h-screen w-full items-center justify-center overflow-hidden bg-cover bg-center p-3 sm:p-4"
         style="background-image: url('{{ asset('images/auth/bg-login.jpg') }}');">
        
        <!-- Dark overlay for readability -->
        <div class="absolute inset-0 bg-black/35 pointer-events-none"></div>

        <div class="relative z-10 w-full max-w-4xl">
            <div class="grid items-center gap-4 md:grid-cols-2 md:gap-6">

                <!-- Left Side - Branding -->
                <div class="hidden md:flex flex-col justify-center bg-white border border-[#EDE0D0] rounded-3xl shadow-2xl p-7 h-full text-center">
                    <div class="mb-6 flex flex-col items-center gap-3">
                        <div>
                            <p class="font-display text-3xl font-bold tracking-tight text-[#3F2B1B]">Alter Studio</p>
                            <span class="mx-auto mt-2 block h-1 w-16 rounded-full bg-gradient-to-r from-[#D4A017] to-[#E07A5F]"></span>
                            <p class="text-xs text-[#8B7359]">Studio Fotografi Premium</p>
                        </div>
                    </div>

                    <h1 class="font-display text-3xl leading-tight font-semibold text-[#3F2B1B] mb-4">
                        Selamat Datang Kembali
                    </h1>
                    <p class="text-[#5C432C] text-base leading-7 mb-6">
                        Masuk untuk mengelola pemesanan, memantau pembayaran, melihat jadwal, dan mengakses hasil foto Anda.
                    </p>

                    <div class="mx-auto w-full max-w-sm space-y-3.5 text-left text-[#5C432C]">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-check text-[#D4A017] mt-1 text-base"></i>
                            <div>
                                <p class="text-sm font-medium">Pembayaran Terintegrasi</p>
                                <p class="text-xs text-[#7A5B3A]">DP atau lunas melalui Midtrans</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-check text-[#D4A017] mt-1 text-base"></i>
                            <div>
                                <p class="text-sm font-medium">Jadwal Studio Terkelola</p>
                                <p class="text-xs text-[#7A5B3A]">Cabang, ruangan, dan kru dicek otomatis</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-check text-[#D4A017] mt-1 text-base"></i>
                            <div>
                                <p class="text-sm font-medium">Foto via Google Drive</p>
                                <p class="text-xs text-[#7A5B3A]">Link foto mentah dan hasil final tersimpan rapi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Login Form -->
                <div class="relative">
                    <div class="bg-white rounded-3xl shadow-2xl border border-[#EDE0D0] p-5 sm:p-8">
                        
                        <!-- Form Header -->
                        <div class="text-center mb-4 sm:mb-5">
                            <div class="mx-auto mb-3 flex items-center justify-center">
                                <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white shadow-lg">
                                    <i class="fa-solid fa-user text-xl"></i>
                                </div>
                            </div>
                            <h2 class="font-display text-2xl font-semibold text-[#3F2B1B] tracking-tight sm:text-3xl">Selamat Datang Kembali</h2>
                            <p class="text-sm text-[#7A5B3A] mt-1">Masuk ke akun Alter Studio Anda</p>
                        </div>

                        <!-- Tombol Kembali ke Landing Page -->
                        <div class="flex justify-center mb-4 sm:mb-5">
                            <a href="/" 
                               class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-[#E1D3C5] px-4 py-2.5 text-sm font-medium text-[#5C432C] transition-all hover:border-[#D4A017] hover:bg-white hover:text-[#D4A017] sm:w-auto sm:px-5">
                                <span>Kembali ke Beranda</span>
                            </a>
                        </div>

                        <!-- Session Status -->
                        <x-auth-session-status class="mb-6 text-emerald-700 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl text-sm" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}" class="space-y-3.5 sm:space-y-4">
                            @csrf

                            <!-- Email -->
                            <div class="space-y-1.5">
                                <label for="email" class="block text-sm font-medium text-[#5C432C]">Email</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <x-text-input id="email"
                                        class="block w-full pl-11 pr-4 py-3 bg-[#FAF6F0] border border-[#E1D3C5] rounded-2xl text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30 transition-all"
                                        type="email"
                                        name="email"
                                        :value="old('email')"
                                        required autofocus
                                        placeholder="nama@email.com" />
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="text-rose-500 text-sm" />
                            </div>

                            <!-- Password -->
                            <div class="space-y-1.5">
                                <label for="password" class="block text-sm font-medium text-[#5C432C]">Kata Sandi</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                    <x-text-input id="password"
                                        class="block w-full pl-11 pr-12 py-3 bg-[#FAF6F0] border border-[#E1D3C5] rounded-2xl text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30 transition-all"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password"
                                        placeholder="Kata sandi Anda" />
                                    <button type="button" id="toggle-password"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-[#8B7359] hover:text-[#5C432C] transition-colors">
                                        <i id="toggle-password-icon" class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="text-rose-500 text-sm" />
                            </div>

                            <!-- Forgot Password -->
                            <div class="flex justify-end text-sm">
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" 
                                       class="text-[#D4A017] hover:text-[#E07A5F] font-medium flex items-center gap-1 transition-colors">
                                        <i class="fa-solid fa-key"></i>
                                        Lupa kata sandi?
                                    </a>
                                @endif
                            </div>

                            <!-- Login Button -->
                            <button type="submit"
                                    class="w-full mt-2 py-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold text-base shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                Masuk Sekarang
                            </button>

                            <!-- Register Link -->
                            <p class="text-center text-sm text-[#7A5B3A] mt-4">
                                Belum punya akun? 
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" 
                                       class="text-[#D4A017] font-semibold hover:text-[#E07A5F] transition-colors">
                                        Daftar sekarang
                                    </a>
                                @endif
                            </p>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Password Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggle-password-icon');

            if (!toggleBtn || !passwordInput || !toggleIcon) return;

            toggleBtn.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleIcon.classList.toggle('fa-eye', !isHidden);
                toggleIcon.classList.toggle('fa-eye-slash', isHidden);
            });
        });
    </script>
</x-guest-layout>
