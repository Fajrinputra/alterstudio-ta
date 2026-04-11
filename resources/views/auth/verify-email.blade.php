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
                    <div class="mx-auto mb-5 flex items-center justify-center h-16 w-16 rounded-2xl bg-gradient-to-br from-[#3F8CFF] to-[#5E6BFF] text-white">
                        <i class="fa-solid fa-envelope-circle-check text-3xl"></i>
                    </div>
                    <h2 class="font-display text-3xl font-semibold text-[#3F2B1B]">Verifikasi Email</h2>
                    <p class="text-[#7A5B3A] mt-3 text-[15px] leading-relaxed">
                        Terima kasih telah mendaftar! Sebelum melanjutkan, silakan verifikasi alamat email Anda dengan mengklik tautan yang kami kirimkan.
                    </p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-6 p-5 bg-emerald-50 border border-emerald-200 rounded-2xl">
                        <div class="flex gap-3 text-emerald-700">
                            <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                            <p class="text-sm">
                                Tautan verifikasi baru telah dikirim ke email Anda.
                            </p>
                        </div>
                    </div>
                @endif

                <div class="space-y-4">
                    <!-- Resend Verification -->
                    <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                        @csrf
                        <button type="submit"
                                class="w-full py-4 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold text-lg shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                            <i class="fa-solid fa-paper-plane"></i>
                            Kirim Ulang Email Verifikasi
                        </button>
                    </form>

                    <!-- Logout -->
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit"
                                class="w-full py-4 rounded-2xl border border-[#E1D3C5] text-[#5C432C] font-semibold hover:bg-[#F4EDE4] transition-all flex items-center justify-center gap-3">
                            <i class="fa-solid fa-sign-out"></i>
                            Keluar
                        </button>
                    </form>

                    <!-- Back to Home -->
                    <div class="text-center pt-4">
                        <a href="/" class="text-sm text-[#8B7359] hover:text-[#D4A017] transition-colors inline-flex items-center gap-2">
                            <i class="fa-solid fa-house"></i>
                            Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>