<x-guest-layout>
    <div class="relative flex h-screen w-full items-center justify-center overflow-hidden bg-cover bg-center p-4"
         style="background-image: url('{{ asset('images/auth/bg-forgot-password.jpg') }}');">

        <div class="absolute inset-0 bg-black/35 pointer-events-none"></div>

        <div class="relative z-10 w-full max-w-md">
            <div class="rounded-3xl border border-[#EDE0D0] bg-white p-6 shadow-2xl sm:p-8">
                <a href="/" class="mx-auto mb-7 block w-fit text-center leading-tight">
                    <div>
                        <p class="font-display text-3xl font-bold text-[#3F2B1B]">Alter Studio</p>
                        <span class="mx-auto mt-2 block h-1 w-16 rounded-full bg-gradient-to-r from-[#D4A017] to-[#E07A5F]"></span>
                        <p class="text-xs text-[#8B7359]">Studio Fotografi Premium</p>
                    </div>
                </a>

                <div class="mb-6 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-white shadow-lg">
                        <i class="fa-solid fa-envelope text-2xl"></i>
                    </div>
                    <h2 class="font-display text-3xl font-semibold text-[#3F2B1B]">Lupa Kata Sandi?</h2>
                    <p class="mt-2 text-sm leading-6 text-[#7A5B3A]">
                        Masukkan alamat email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
                    </p>
                </div>

                <x-auth-session-status class="mb-5 rounded-2xl border border-emerald-100 bg-emerald-50 p-3 text-sm text-emerald-700" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-medium text-[#5C432C]">Alamat Email</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <x-text-input id="email"
                                class="block w-full rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] py-3 pl-11 pr-4 text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required autofocus
                                placeholder="nama@email.com" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="text-sm text-rose-500" />
                    </div>

                    <button type="submit"
                            class="flex w-full items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] py-3 text-base font-semibold text-white shadow-lg shadow-[#D4A017]/30 transition-all hover:-translate-y-0.5 hover:shadow-xl">
                        <i class="fa-solid fa-paper-plane"></i>
                        Kirim Tautan Atur Ulang
                    </button>
                </form>

                <div class="mt-6 space-y-3 text-center">
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center justify-center gap-2 text-sm font-medium text-[#D4A017] transition-colors hover:text-[#E07A5F]">
                        Kembali ke Halaman Login
                    </a>

                    <p class="text-sm text-[#7A5B3A]">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="font-semibold text-[#D4A017] hover:text-[#E07A5F]">Daftar sekarang</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
