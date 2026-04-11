<x-guest-layout>
    <div class="relative bg-[#FAF6F0] min-h-screen flex items-center justify-center p-4">
        
        <!-- Background Pattern -->
        <div class="absolute inset-0 pointer-events-none opacity-30 bg-[radial-gradient(circle_at_20%_30%,rgba(212,160,23,0.08),transparent_50%),radial-gradient(circle_at_80%_70%,rgba(224,122,95,0.08),transparent_50%)]"></div>

        <div class="w-full max-w-md">
            
            <!-- Logo -->
            <div class="text-center mb-8">
                <a href="/" class="inline-flex items-center gap-3 mx-auto">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017] to-[#E07A5F] rounded-2xl blur-xl opacity-40"></div>
                        <div class="relative h-14 w-14 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white font-black text-2xl shadow-inner">
                            A
                        </div>
                    </div>
                    <div class="text-left leading-tight">
                        <p class="font-display text-2xl text-[#3F2B1B]">Alter Studio</p>
                        <p class="text-xs text-[#8B7359]">Premium Photography</p>
                    </div>
                </a>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-3xl shadow-2xl border border-[#EDE0D0] p-8 sm:p-10">
                <div class="text-center mb-8">
                    <div class="mx-auto mb-5 flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-white">
                        <i class="fa-solid fa-lock text-3xl"></i>
                    </div>
                    <h2 class="font-display text-3xl font-semibold text-[#3F2B1B]">Konfirmasi Password</h2>
                    <p class="text-[#7A5B3A] mt-3 text-[15px]">
                        Ini adalah area aman. Mohon konfirmasi password Anda sebelum melanjutkan.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
                    @csrf

                    <!-- Password -->
                    <div class="space-y-2">
                        <label for="password" class="block text-sm font-medium text-[#5C432C]">Password</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <x-text-input id="password"
                                class="block w-full pl-11 pr-4 py-4 bg-[#FAF6F0] border border-[#E1D3C5] rounded-2xl text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30 transition-all"
                                type="password"
                                name="password"
                                required autocomplete="current-password"
                                placeholder="Masukkan password Anda" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="text-rose-500 text-sm" />
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full py-4 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold text-lg shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                        <i class="fa-solid fa-circle-check"></i>
                        Konfirmasi Password
                    </button>
                </form>

                <div class="text-center mt-8">
                    <a href="/" 
                       class="text-sm text-[#8B7359] hover:text-[#D4A017] transition-colors inline-flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>