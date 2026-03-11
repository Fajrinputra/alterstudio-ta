<x-guest-layout>
    <div class="relative bg-[#F8F1E7] text-[#4a301f] min-h-screen flex items-center justify-center p-4">
        <div class="absolute inset-0 pointer-events-none opacity-40 bg-[radial-gradient(circle_at_15%_20%,rgba(181,128,66,0.12),transparent_45%),radial-gradient(circle_at_85%_10%,rgba(139,91,46,0.12),transparent_40%),radial-gradient(circle_at_60%_70%,rgba(214,182,149,0.16),transparent_40%)]"></div>

        <div class="w-full max-w-5xl">
            <div class="grid md:grid-cols-2 gap-8 items-center">
                <!-- Hero copy (hidden on small) -->
                <div class="hidden md:block bg-white/70 rounded-3xl border border-[#e3d5c4] shadow-xl shadow-[#d7c5b2]/40 p-8 backdrop-blur">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="h-14 w-14 rounded-2xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] flex items-center justify-center text-white font-black text-2xl">A</span>
                        <div>
                            <p class="font-display text-2xl text-[#3f2b1b]">Alter Studio</p>
                            <p class="text-sm text-[#8b7359]">Premium Photography</p>
                        </div>
                    </div>
                    
                    <h1 class="text-3xl font-display font-bold leading-tight text-[#3f2b1b] mb-4">Masuk ke Workflow Management</h1>
                    <p class="text-base text-[#6b4a2d] mb-6">
                        Kelola booking, jadwal kru, kolaborasi editing, dan pembayaran dalam satu portal.
                    </p>
                    
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 text-sm text-[#6b4a2d]">
                            <i class="fa-solid fa-circle-check text-[#b58042]"></i>
                            <span>Midtrans-ready dengan Snap & Webhook</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-[#6b4a2d]">
                            <i class="fa-solid fa-circle-check text-[#b58042]"></i>
                            <span>RBAC dengan 5 role berbeda</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-[#6b4a2d]">
                            <i class="fa-solid fa-circle-check text-[#b58042]"></i>
                            <span>Versioning aset & limit seleksi 5 foto</span>
                        </div>
                        <div class="flex items-center gap-3 text-sm text-[#6b4a2d]">
                            <i class="fa-solid fa-circle-check text-[#b58042]"></i>
                            <span>Jadwal anti-bentrok & payroll otomatis</span>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <div class="relative">
                    <div class="w-full bg-white/95 border border-[#e3d5c4] rounded-2xl p-6 sm:p-8 shadow-xl shadow-[#d7c5b2]/40 backdrop-blur">
                        <div class="text-center space-y-2 mb-6">
                            <div class="inline-flex items-center justify-center h-14 w-14 rounded-2xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] text-white font-black text-xl shadow-lg shadow-[#b58042]/30 mx-auto">
                                <i class="fa-solid fa-user text-2xl"></i>
                            </div>
                            <h2 class="text-2xl font-display font-semibold text-[#3f2b1b]">Selamat Datang</h2>
                            <p class="text-sm text-[#7a5b3a]">Masuk ke akun Alter Studio Anda</p>
                            <a href="/" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#d7c5b2] text-[#5c432c] font-semibold hover:bg-[#f7efe6] text-xs transition-all">
                                <i class="fa-solid fa-arrow-left"></i>
                                Kembali ke Landing
                            </a>
                        </div>

                        <x-auth-session-status class="mb-4 text-emerald-600 bg-emerald-50 p-3 rounded-lg text-sm" :status="session('status')" />

                        <form method="POST" action="{{ route('login') }}" class="space-y-4">
                            @csrf

                            <!-- Email -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-[#6f5134]">Email</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <x-text-input id="email" 
                                        class="block w-full pl-10 pr-4 py-3 bg-[#fdf8f2] border border-[#d7c5b2] rounded-xl text-[#1c2432] placeholder:text-[#b39b82] focus:border-[#b58042] focus:ring-[#b58042]" 
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
                                <label for="password" class="block text-sm font-medium text-[#6f5134]">Password</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                    <x-text-input id="password" 
                                        class="block w-full pl-10 pr-12 py-3 bg-[#fdf8f2] border border-[#d7c5b2] rounded-xl text-[#1c2432] placeholder:text-[#b39b82] focus:border-[#b58042] focus:ring-[#b58042]"
                                        type="password"
                                        name="password"
                                        required autocomplete="current-password"
                                        placeholder="********" />
                                    <button type="button"
                                            id="toggle-password"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8b7359] hover:text-[#5c432c] transition-colors"
                                            aria-label="Tampilkan password">
                                        <i id="toggle-password-icon" class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <x-input-error :messages="$errors->get('password')" class="text-rose-500 text-sm" />
                            </div>

                            <!-- Remember & Forgot -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
                                <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                                    <input id="remember_me" type="checkbox" 
                                        class="rounded bg-[#fdf8f2] border-[#d7c5b2] text-[#b58042] focus:ring-[#b58042] focus:ring-offset-0"
                                        name="remember">
                                    <span class="text-[#6f5134]">Ingat saya</span>
                                </label>
                                
                                @if (Route::has('password.request'))
                                    <a class="text-[#b58042] hover:text-[#8b5b2e] font-medium inline-flex items-center gap-1" href="{{ route('password.request') }}">
                                        <i class="fa-solid fa-circle-question"></i>
                                        Lupa password?
                                    </a>
                                @endif
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 mt-2">
                                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                                Masuk
                            </button>

                            <!-- Register Link -->
                            <p class="text-center text-sm text-[#7a5b3a] mt-4">
                                Belum punya akun?
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="text-[#b58042] hover:text-[#8b5b2e] font-semibold inline-flex items-center gap-1">
                                        Daftar sekarang
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </a>
                                @endif
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-password');
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggle-password-icon');

            if (!toggleBtn || !passwordInput || !toggleIcon) {
                return;
            }

            toggleBtn.addEventListener('click', function () {
                const isHidden = passwordInput.type === 'password';
                passwordInput.type = isHidden ? 'text' : 'password';
                toggleIcon.className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
            });
        });
    </script>
</x-guest-layout>
