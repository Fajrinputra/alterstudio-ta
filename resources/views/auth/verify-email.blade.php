<x-guest-layout>
    <div class="relative bg-[#F8F1E7] text-[#4a301f] min-h-screen flex items-center justify-center p-4">
        <div class="absolute inset-0 pointer-events-none opacity-40 bg-[radial-gradient(circle_at_20%_30%,rgba(181,128,66,0.12),transparent_45%),radial-gradient(circle_at_80%_70%,rgba(139,91,46,0.12),transparent_40%)]"></div>
        
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div class="text-center mb-6">
                <a href="/" class="inline-flex items-center gap-3">
                    <span class="h-12 w-12 rounded-2xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] flex items-center justify-center text-white font-black text-xl shadow-lg shadow-[#b58042]/30">A</span>
                    <div class="leading-tight text-left">
                        <p class="font-display text-lg text-[#3f2b1b]">Alter Studio</p>
                        <p class="text-xs text-[#8b7359]">Premium Photography</p>
                    </div>
                </a>
            </div>

            <!-- Card -->
            <div class="bg-white/95 border border-[#e3d5c4] rounded-2xl p-8 shadow-xl shadow-[#d7c5b2]/40 backdrop-blur">
                <div class="text-center space-y-2 mb-6">
                    <div class="inline-flex items-center justify-center h-14 w-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white mb-2">
                        <i class="fa-solid fa-envelope-circle-check text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-display font-semibold text-[#3f2b1b]">Verifikasi Email</h2>
                    <p class="text-sm text-[#7a5b3a]">
                        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}
                    </p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 rounded-lg">
                        <div class="flex gap-3 text-emerald-700">
                            <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                            <p class="text-sm">
                                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                            </p>
                        </div>
                    </div>
                @endif

                <div class="space-y-4">
                    <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                            <i class="fa-solid fa-paper-plane"></i>
                            {{ __('Resend Verification Email') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] bg-white hover:bg-[#fcf7f1] font-semibold transition-all duration-200">
                            <i class="fa-solid fa-sign-out"></i>
                            {{ __('Log Out') }}
                        </button>
                    </form>

                    <p class="text-center text-sm text-[#7a5b3a] mt-4">
                        <a href="/" class="text-[#b58042] hover:text-[#8b5b2e] font-medium inline-flex items-center gap-1">
                            <i class="fa-solid fa-arrow-left text-xs"></i>
                            Kembali ke Beranda
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>