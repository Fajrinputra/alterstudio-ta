<x-guest-layout>
    <div class="relative bg-[#F8F1E7] text-[#4a301f] min-h-screen flex items-center justify-center p-4">
        <div class="absolute inset-0 pointer-events-none opacity-40 bg-[radial-gradient(circle_at_15%_20%,rgba(181,128,66,0.12),transparent_45%),radial-gradient(circle_at_85%_80%,rgba(139,91,46,0.12),transparent_40%)]"></div>
        
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
                    <div class="inline-flex items-center justify-center h-14 w-14 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white mb-2">
                        <i class="fa-solid fa-rotate-left text-xl"></i>
                    </div>
                    <h2 class="text-2xl font-display font-semibold text-[#3f2b1b]">Reset Password</h2>
                    <p class="text-sm text-[#7a5b3a]">Buat password baru untuk akun Anda</p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email Address -->
                    <div class="space-y-2">
                        <x-input-label for="email" :value="__('Email')" class="text-sm font-medium text-[#6f5134]" />
                        
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <x-text-input id="email" 
                                class="block w-full pl-10 pr-4 py-3 bg-[#fdf8f2] border border-[#d7c5b2] rounded-xl text-[#1c2432] placeholder:text-[#b39b82] focus:border-[#b58042] focus:ring-[#b58042]" 
                                type="email" 
                                name="email" 
                                :value="old('email', $request->email)" 
                                required autofocus autocomplete="username"
                                placeholder="contoh@email.com" />
                        </div>

                        <x-input-error :messages="$errors->get('email')" class="text-rose-500 text-sm mt-1" />
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <x-input-label for="password" :value="__('Password')" class="text-sm font-medium text-[#6f5134]" />
                        
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <x-text-input id="password" 
                                class="block w-full pl-10 pr-4 py-3 bg-[#fdf8f2] border border-[#d7c5b2] rounded-xl text-[#1c2432] placeholder:text-[#b39b82] focus:border-[#b58042] focus:ring-[#b58042]" 
                                type="password"
                                name="password"
                                required autocomplete="new-password"
                                placeholder="Minimal 8 karakter" />
                        </div>

                        <x-input-error :messages="$errors->get('password')" class="text-rose-500 text-sm mt-1" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2">
                        <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-sm font-medium text-[#6f5134]" />
                        
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <x-text-input id="password_confirmation" 
                                class="block w-full pl-10 pr-4 py-3 bg-[#fdf8f2] border border-[#d7c5b2] rounded-xl text-[#1c2432] placeholder:text-[#b39b82] focus:border-[#b58042] focus:ring-[#b58042]" 
                                type="password"
                                name="password_confirmation" 
                                required autocomplete="new-password"
                                placeholder="Ketik ulang password" />
                        </div>

                        <x-input-error :messages="$errors->get('password_confirmation')" class="text-rose-500 text-sm mt-1" />
                    </div>

                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200 mt-4">
                        <i class="fa-solid fa-circle-check"></i>
                        {{ __('Reset Password') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>