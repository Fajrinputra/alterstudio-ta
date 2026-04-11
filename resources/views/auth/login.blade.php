<x-guest-layout>
    <div class="relative bg-[#FAF6F0] min-h-screen flex items-center justify-center p-4 overflow-hidden">
        
        <!-- Subtle Background Pattern -->
        <div class="absolute inset-0 pointer-events-none opacity-30 bg-[radial-gradient(circle_at_20%_30%,rgba(212,160,23,0.08),transparent_50%),radial-gradient(circle_at_80%_70%,rgba(224,122,95,0.08),transparent_50%)]"></div>

        <div class="w-full max-w-5xl">
            <div class="grid md:grid-cols-2 gap-10 items-center">

                <!-- Left Side - Branding -->
                <div class="hidden md:flex flex-col justify-center bg-white/70 backdrop-blur-xl border border-[#EDE0D0] rounded-3xl shadow-2xl p-10 h-full">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017] to-[#E07A5F] rounded-2xl blur-xl opacity-40"></div>
                            <div class="relative h-16 w-16 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white font-black text-3xl shadow-inner">
                                A
                            </div>
                        </div>
                        <div>
                            <p class="font-display text-3xl tracking-tight text-[#3F2B1B]">Alter Studio</p>
                            <p class="text-sm text-[#8B7359]">Premium Photography</p>
                        </div>
                    </div>

                    <h1 class="font-display text-4xl leading-tight font-semibold text-[#3F2B1B] mb-6">
                        Selamat Datang Kembali
                    </h1>
                    <p class="text-[#5C432C] text-lg mb-8">
                        Masuk ke akun Anda untuk mengelola pemesanan, melihat portofolio, dan mengakses fitur studio.
                    </p>

                    <div class="space-y-5 text-[#5C432C]">
                        <div class="flex items-start gap-4">
                            <i class="fa-solid fa-circle-check text-[#D4A017] mt-1 text-xl"></i>
                            <div>
                                <p class="font-medium">Midtrans Integration</p>
                                <p class="text-sm text-[#7A5B3A]">Pembayaran cepat & aman</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <i class="fa-solid fa-circle-check text-[#D4A017] mt-1 text-xl"></i>
                            <div>
                                <p class="font-medium">Jadwal Anti-Bentrok</p>
                                <p class="text-sm text-[#7A5B3A]">Booking kru & studio real-time</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <i class="fa-solid fa-circle-check text-[#D4A017] mt-1 text-xl"></i>
                            <div>
                                <p class="font-medium">Portofolio & Download</p>
                                <p class="text-sm text-[#7A5B3A]">Hasil foto siap diunduh kapan saja</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Login Form -->
                <div class="relative">
                    <div class="bg-white rounded-3xl shadow-2xl border border-[#EDE0D0] p-8 sm:p-12">
                        
                        <!-- Form Header -->
                        <div class="text-center mb-8">
                            <div class="mx-auto mb-5 flex items-center justify-center">
                                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white shadow-lg">
                                    <i class="fa-solid fa-user text-3xl"></i>
                                </div>
                            </div>
                            <h2 class="font-display text-4xl font-semibold text-[#3F2B1B] tracking-tight">Selamat Datang Kembali</h2>
                            <p class="text-[#7A5B3A] mt-2">Masuk ke akun Alter Studio Anda</p>
                        </div>

                        <!-- Tombol Kembali ke Landing Page -->
                        <div class="flex justify-center mb-8">
                            <a href="/" 
                               class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl border border-[#E1D3C5] text-[#5C432C] hover:border-[#D4A017] hover:text-[#D4A017] hover:bg-white transition-all text-sm font-medium">
                                <i class="fa-solid fa-arrow-left"></i>
                                <span>Kembali ke Landing Page</span>
                            </a>
                        </div>

                        <!-- Session Status -->
                        <x-auth-session-status class="mb-6 text-emerald-700 bg-emerald-50 border border-emerald-100 p-4 rounded-2xl text-sm" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}" class="space-y-6">
                            @csrf

                            <!-- Email -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-[#5C432C]">Email</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <x-text-input id="email"
                                        class="block w-full pl-11 pr-4 py-4 bg-[#FAF6F0] border border-[#E1D3C5] rounded-2xl text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30 transition-all"
                                        type="email"
                                        name="email"
                                        :value="old('email')"
                                        required autofocus
                                        placeholder="nama@email.com" />
                                </div>
                                <x-input-error :messages="$errors->get('email')" class="text-rose-500 text-sm" />
                            </div>

                            <!-- Password -->
                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-[#5C432C]">Password</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                    <x-text-input id="password"
                                        class="block w-full pl-11 pr-12 py-4 bg-[#FAF6F0] border border-[#E1D3C5] rounded-2xl text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30 transition-all"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password"
                                        placeholder="••••••••" />
                                    <button type="button" id="toggle-password"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-[#8B7359] hover:text-[#5C432C] transition-colors">
                                        <i id="toggle-password-icon" class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="text-rose-500 text-sm" />
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 text-sm">
                                <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                                    <input id="remember_me" type="checkbox" 
                                           class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017] focus:ring-[#D4A017]"
                                           name="remember">
                                    <span class="text-[#5C432C]">Ingat saya</span>
                                </label>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" 
                                       class="text-[#D4A017] hover:text-[#E07A5F] font-medium flex items-center gap-1 transition-colors">
                                        <i class="fa-solid fa-key"></i>
                                        Lupa password?
                                    </a>
                                @endif
                            </div>

                            <!-- Login Button -->
                            <button type="submit"
                                    class="w-full mt-4 py-4 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold text-lg shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                Masuk Sekarang
                            </button>

                            <!-- Register Link -->
                            <p class="text-center text-sm text-[#7A5B3A] mt-6">
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