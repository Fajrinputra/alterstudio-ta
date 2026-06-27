<x-guest-layout>
    <div class="relative flex h-screen w-full items-center justify-center overflow-hidden bg-cover bg-center p-4"
         style="background-image: url('{{ asset('images/auth/bg-forgot-password.jpg') }}');">

        <div class="absolute inset-0 bg-black/35 pointer-events-none"></div>

        <div class="relative z-10 w-full max-w-md">
            <div class="rounded-3xl border border-[#EDE0D0] bg-white p-6 shadow-2xl sm:p-8">
                <a href="/" class="mx-auto mb-6 block w-fit text-center leading-tight">
                    <div>
                        <p class="font-display text-3xl font-bold text-[#3F2B1B]">Alter Studio</p>
                        <span class="mx-auto mt-2 block h-1 w-16 rounded-full bg-gradient-to-r from-[#D4A017] to-[#E07A5F]"></span>
                        <p class="text-xs text-[#8B7359]">Studio Fotografi Premium</p>
                    </div>
                </a>

                <div class="mb-5 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] text-white shadow-lg">
                        <i class="fa-solid fa-rotate-left text-2xl"></i>
                    </div>
                    <h2 class="font-display text-3xl font-semibold text-[#3F2B1B]">Atur Ulang Kata Sandi</h2>
                    <p class="mt-2 text-sm text-[#7A5B3A]">Buat kata sandi baru untuk akun Anda</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

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
                                :value="old('email', $request->email)"
                                required autofocus autocomplete="username"
                                placeholder="nama@email.com" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="text-sm text-rose-500" />
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-sm font-medium text-[#5C432C]">Kata Sandi Baru</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <x-text-input id="password"
                                class="block w-full rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] py-3 pl-11 pr-4 text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30"
                                type="password"
                                name="password"
                                required autocomplete="new-password"
                                placeholder="Minimal 8 karakter" />
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="text-sm text-rose-500" />
                    </div>

                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-sm font-medium text-[#5C432C]">Konfirmasi Kata Sandi Baru</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-[#D4A017]">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <x-text-input id="password_confirmation"
                                class="block w-full rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] py-3 pl-11 pr-4 text-[#3F2B1B] placeholder:text-[#9C7C5E] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/30"
                                type="password"
                                name="password_confirmation"
                                required autocomplete="new-password"
                                placeholder="Ketik ulang kata sandi" />
                        </div>
                        <x-input-error :messages="$errors->get('password_confirmation')" class="text-sm text-rose-500" />
                    </div>

                    <button type="submit"
                            class="flex w-full items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] py-3 text-base font-semibold text-white shadow-lg shadow-[#D4A017]/30 transition-all hover:-translate-y-0.5 hover:shadow-xl">
                        <i class="fa-solid fa-circle-check"></i>
                        Simpan Kata Sandi Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
