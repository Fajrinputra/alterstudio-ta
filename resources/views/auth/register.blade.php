<x-guest-layout>
    <div class="relative bg-[#FAF6F0] min-h-screen flex items-center justify-center p-4 overflow-hidden">
        
        <!-- Background subtle pattern -->
        <div class="absolute inset-0 pointer-events-none opacity-30 bg-[radial-gradient(circle_at_20%_30%,rgba(212,160,23,0.08),transparent_50%),radial-gradient(circle_at_80%_70%,rgba(224,122,95,0.08),transparent_50%)]"></div>

        <div class="w-full max-w-lg">
            
            <!-- Header -->
            <div class="text-center mb-10">
                <div class="mx-auto mb-6 flex items-center justify-center">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017] to-[#E07A5F] rounded-3xl blur-xl opacity-40"></div>
                        <div class="relative h-20 w-20 rounded-3xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white shadow-2xl">
                            <i class="fa-solid fa-user-plus text-4xl"></i>
                        </div>
                    </div>
                </div>
                
                <h2 class="font-display text-4xl font-bold tracking-tight text-[#3F2B1B] mb-2">
                    Buat Akun Baru
                </h2>
                <p class="text-[#7A5B3A] text-lg">
                    Bergabunglah dan abadikan momen berharga bersama Alter Studio
                </p>
                
                <a href="/" 
                   class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 rounded-2xl border border-[#E1D3C5] text-[#5C432C] hover:border-[#D4A017] hover:text-[#D4A017] transition-all text-sm font-medium">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali ke Beranda
                </a>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl shadow-2xl border border-[#EDE0D0] p-8 sm:p-10 backdrop-blur-xl">
                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Name -->
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-[#5C432C]">Nama Lengkap</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                <i class="fa-solid fa-user"></i>
                            </span>
                            <x-text-input id="name"
                                class="block w-full pl-11 pr-4 py-3.5 bg-[#FAF6F0] border border-[#E1D3C5] rounded-2xl text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30 transition-all"
                                type="text"
                                name="name"
                                :value="old('name')"
                                required autofocus
                                placeholder="Masukkan nama lengkap Anda" />
                        </div>
                        <x-input-error :messages="$errors->get('name')" class="text-rose-500 text-sm" />
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="block text-sm font-medium text-[#5C432C]">Email Address</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <x-text-input id="email"
                                class="block w-full pl-11 pr-4 py-3.5 bg-[#FAF6F0] border border-[#E1D3C5] rounded-2xl text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30 transition-all"
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
                        <label for="password" class="block text-sm font-medium text-[#5C432C]">Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <x-text-input id="password"
                                class="block w-full pl-11 pr-12 py-3.5 bg-[#FAF6F0] border border-[#E1D3C5] rounded-2xl text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30 transition-all"
                                type="password"
                                name="password"
                                required autocomplete="new-password"
                                placeholder="Minimal 8 karakter" />
                            <button type="button" id="toggle-password"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[#8B7359] hover:text-[#5C432C] transition-colors">
                                <i id="toggle-password-icon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="text-rose-500 text-sm" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-[#5C432C]">Konfirmasi Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <x-text-input id="password_confirmation"
                                class="block w-full pl-11 pr-12 py-3.5 bg-[#FAF6F0] border border-[#E1D3C5] rounded-2xl text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30 transition-all"
                                type="password"
                                name="password_confirmation"
                                required
                                placeholder="Ketik ulang password" />
                            <button type="button" id="toggle-password-confirmation"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[#8B7359] hover:text-[#5C432C] transition-colors">
                                <i id="toggle-password-confirmation-icon" class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="text-rose-500 text-sm" />
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start gap-3 mt-2">
                        <input type="checkbox" id="terms" 
                               class="mt-1 w-5 h-5 rounded-lg border-[#E1D3C5] text-[#D4A017] focus:ring-[#D4A017]" required>
                        <label for="terms" class="text-sm text-[#6F5134] leading-relaxed">
                            Saya menyetujui 
                            <a href="#" class="text-[#D4A017] hover:underline font-medium">Syarat & Ketentuan</a> 
                            dan 
                            <a href="#" class="text-[#D4A017] hover:underline font-medium">Kebijakan Privasi</a> 
                            Alter Studio
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full mt-6 py-4 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold text-lg shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-3">
                        <i class="fa-solid fa-user-plus"></i>
                        Daftar Sekarang
                    </button>

                    <!-- Login Link -->
                    <p class="text-center text-sm text-[#7A5B3A] mt-6">
                        Sudah punya akun? 
                        <a href="{{ route('login') }}" 
                           class="text-[#D4A017] font-semibold hover:text-[#E07A5F] inline-flex items-center gap-1 transition-colors">
                            Masuk sekarang
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </p>
                </form>
            </div>

            <!-- Features -->
            <div class="grid grid-cols-2 gap-4 mt-10">
                <div class="bg-white/70 backdrop-blur-sm border border-[#EDE0D0] rounded-2xl p-5 text-center">
                    <i class="fa-solid fa-calendar-check text-2xl text-[#D4A017] mb-3"></i>
                    <p class="font-medium text-[#3F2B1B]">Pemesanan Mudah</p>
                </div>
                <div class="bg-white/70 backdrop-blur-sm border border-[#EDE0D0] rounded-2xl p-5 text-center">
                    <i class="fa-solid fa-shield-halved text-2xl text-[#D4A017] mb-3"></i>
                    <p class="font-medium text-[#3F2B1B]">Pembayaran Aman</p>
                </div>
                <div class="bg-white/70 backdrop-blur-sm border border-[#EDE0D0] rounded-2xl p-5 text-center">
                    <i class="fa-solid fa-camera text-2xl text-[#D4A017] mb-3"></i>
                    <p class="font-medium text-[#3F2B1B]">Hasil Profesional</p>
                </div>
                <div class="bg-white/70 backdrop-blur-sm border border-[#EDE0D0] rounded-2xl p-5 text-center">
                    <i class="fa-solid fa-download text-2xl text-[#D4A017] mb-3"></i>
                    <p class="font-medium text-[#3F2B1B]">Download Instant</p>
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
        });
    </script>
</x-guest-layout>