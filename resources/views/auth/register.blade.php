<x-guest-layout>
    <div class="relative bg-[#F8F1E7] text-[#4a301f] min-h-screen flex items-center justify-center p-4">
        <div class="absolute inset-0 pointer-events-none opacity-40 bg-[radial-gradient(circle_at_10%_20%,rgba(181,128,66,0.12),transparent_45%),radial-gradient(circle_at_90%_15%,rgba(139,91,46,0.12),transparent_40%),radial-gradient(circle_at_60%_75%,rgba(214,182,149,0.16),transparent_40%)]"></div>
        
        <div class="w-full max-w-2xl">
            <!-- Header -->
            <div class="text-center space-y-2 mb-6">
                <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] text-white font-black text-2xl shadow-lg shadow-[#b58042]/30 mx-auto">
                    <i class="fa-solid fa-pen-to-square text-2xl"></i>
                </div>
                <h2 class="text-3xl font-display font-semibold text-[#3f2b1b]">Daftar Akun Baru</h2>
                <p class="text-sm text-[#7a5b3a]">Buat akun untuk melakukan pemesanan layanan fotografi</p>
                <a href="/" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#d7c5b2] text-[#5c432c] font-semibold hover:bg-[#f7efe6] text-sm transition-all">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke Landing
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white/95 border border-[#e3d5c4] rounded-2xl p-6 sm:p-8 shadow-xl shadow-[#d7c5b2]/40 backdrop-blur">
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <!-- Name -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-[#6f5134]">Nama Lengkap</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <x-text-input id="name" 
                                class="block w-full pl-10 pr-4 py-3 bg-[#fdf8f2] border border-[#d7c5b2] rounded-xl text-[#1c2432] placeholder:text-[#b39b82] focus:border-[#b58042] focus:ring-[#b58042]" 
                                type="text" 
                                name="name" 
                                :value="old('name')" 
                                required autofocus 
                                placeholder="Masukkan nama lengkap" />
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="text-rose-500 text-sm" />
                    </div>

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
                                required 
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
                                required autocomplete="new-password"
                                placeholder="Minimal 8 karakter" />
                            <button type="button"
                                    id="toggle-password"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8b7359] hover:text-[#5c432c] transition-colors"
                                    aria-label="Tampilkan password">
                                <i id="toggle-password-icon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="text-rose-500 text-sm" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-[#6f5134]">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <x-text-input id="password_confirmation" 
                                class="block w-full pl-10 pr-12 py-3 bg-[#fdf8f2] border border-[#d7c5b2] rounded-xl text-[#1c2432] placeholder:text-[#b39b82] focus:border-[#b58042] focus:ring-[#b58042]"
                                type="password"
                                name="password_confirmation" 
                                required 
                                placeholder="Ketik ulang password" />
                            <button type="button"
                                    id="toggle-password-confirmation"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#8b7359] hover:text-[#5c432c] transition-colors"
                                    aria-label="Tampilkan konfirmasi password">
                                <i id="toggle-password-confirmation-icon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="text-rose-500 text-sm" />
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="flex items-start gap-2 mt-4">
                        <input type="checkbox" id="terms" class="mt-1 rounded bg-[#fdf8f2] border-[#d7c5b2] text-[#b58042] focus:ring-[#b58042]" required>
                        <label for="terms" class="text-sm text-[#6f5134]">
                            Saya setuju dengan <a href="#" class="text-[#b58042] hover:text-[#8b5b2e] font-medium">Syarat & Ketentuan</a> dan <a href="#" class="text-[#b58042] hover:text-[#8b5b2e] font-medium">Kebijakan Privasi</a> Alter Studio
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 mt-4">
                        <i class="fa-solid fa-user-plus"></i>
                        Daftar Sekarang
                    </button>

                    <!-- Login Link -->
                    <p class="text-center text-sm text-[#7a5b3a] mt-4">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-[#b58042] hover:text-[#8b5b2e] font-semibold inline-flex items-center gap-1">
                            Masuk
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </p>
                </form>
            </div>

            <!-- Features Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
                <div class="bg-white/50 backdrop-blur-sm rounded-xl p-3 text-center">
                    <i class="fa-solid fa-calendar-check text-[#b58042] text-lg mb-1"></i>
                    <p class="text-xs text-[#6f5134]">Pemesanan Mudah</p>
                </div>
                <div class="bg-white/50 backdrop-blur-sm rounded-xl p-3 text-center">
                    <i class="fa-solid fa-credit-card text-[#b58042] text-lg mb-1"></i>
                    <p class="text-xs text-[#6f5134]">Pembayaran Aman</p>
                </div>
                <div class="bg-white/50 backdrop-blur-sm rounded-xl p-3 text-center">
                    <i class="fa-solid fa-image text-[#b58042] text-lg mb-1"></i>
                    <p class="text-xs text-[#6f5134]">Foto Edit</p>
                </div>
                <div class="bg-white/50 backdrop-blur-sm rounded-xl p-3 text-center">
                    <i class="fa-solid fa-download text-[#b58042] text-lg mb-1"></i>
                    <p class="text-xs text-[#6f5134]">Final Download</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggles = [
                {
                    button: document.getElementById('toggle-password'),
                    input: document.getElementById('password'),
                    icon: document.getElementById('toggle-password-icon'),
                    showLabel: 'Tampilkan password',
                    hideLabel: 'Sembunyikan password'
                },
                {
                    button: document.getElementById('toggle-password-confirmation'),
                    input: document.getElementById('password_confirmation'),
                    icon: document.getElementById('toggle-password-confirmation-icon'),
                    showLabel: 'Tampilkan konfirmasi password',
                    hideLabel: 'Sembunyikan konfirmasi password'
                }
            ];

            toggles.forEach(function (item) {
                if (!item.button || !item.input || !item.icon) {
                    return;
                }

                item.button.addEventListener('click', function () {
                    const isHidden = item.input.type === 'password';
                    item.input.type = isHidden ? 'text' : 'password';
                    item.icon.className = isHidden ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                    item.button.setAttribute('aria-label', isHidden ? item.hideLabel : item.showLabel);
                });
            });
        });
    </script>
</x-guest-layout>
