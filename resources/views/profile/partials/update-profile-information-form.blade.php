<section class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-xl p-6">
    <header class="mb-6">
        <h2 class="text-xl font-display font-semibold text-[#3f2b1b] flex items-center gap-2">
            <i class="fa-solid fa-circle-user text-[#b58042]"></i>
            {{ __('Info Profil & Kontak') }}
        </h2>
        <p class="text-sm text-[#7a5b3a] mt-1">Perbarui nama, email, dan avatar.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('patch')

        {{-- Nama --}}
        <div class="space-y-2">
            <x-input-label for="name" :value="__('Nama')" class="text-sm font-medium text-[#6f5134]" />
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                    <i class="fa-solid fa-user"></i>
                </span>
                <x-text-input id="name" 
                              name="name" 
                              type="text" 
                              class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] focus:border-[#b58042] focus:ring-[#b58042]" 
                              :value="old('name', $user->name)" 
                              required autofocus 
                              autocomplete="name"
                              placeholder="Nama lengkap" />
            </div>
            <x-input-error class="mt-1 text-rose-500" :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div class="space-y-2">
            <x-input-label for="email" :value="__('Email')" class="text-sm font-medium text-[#6f5134]" />
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                    <i class="fa-solid fa-envelope"></i>
                </span>
                <x-text-input id="email" 
                              name="email" 
                              type="email" 
                              class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] focus:border-[#b58042] focus:ring-[#b58042]" 
                              :value="old('email', $user->email)" 
                              required 
                              autocomplete="username"
                              placeholder="nama@email.com" />
            </div>
            <x-input-error class="mt-1 text-rose-500" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                    <p class="text-xs text-amber-700 flex items-center gap-1">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Email belum terverifikasi.
                        <button form="send-verification" 
                                class="ml-1 text-amber-800 font-semibold underline hover:text-amber-900">
                            Kirim ulang verifikasi
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 text-xs text-emerald-600 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i>
                            Link verifikasi baru telah dikirim.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Avatar Upload --}}
        <div class="space-y-3 pt-2">
            <x-input-label for="avatar" :value="__('Foto Profil (opsional)')" class="text-sm font-medium text-[#6f5134]" />
            
            <div class="flex items-start gap-4">
                {{-- Preview --}}
                <div class="flex-shrink-0">
                    @if($user->avatar_path)
                        <img src="{{ Storage::url($user->avatar_path) }}" class="h-16 w-16 rounded-xl border-2 border-[#e3d5c4] object-cover shadow-md" alt="Avatar">
                    @else
                        <div class="h-16 w-16 rounded-xl border-2 border-[#e3d5c4] bg-gradient-to-br from-[#b58042]/10 to-[#8b5b2e]/10 flex items-center justify-center">
                            <i class="fa-solid fa-user text-2xl text-[#b58042]"></i>
                        </div>
                    @endif
                </div>

                {{-- Upload Input --}}
                <div class="flex-1">
                    <input id="avatar" 
                           name="avatar" 
                           type="file" 
                           accept="image/*" 
                           class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer file:transition-colors">
                    <p class="text-xs text-[#7a5b3a] mt-2">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Format: JPG, PNG. Maksimal 2MB
                    </p>
                </div>
            </div>
            <x-input-error class="mt-1 text-rose-500" :messages="$errors->get('avatar')" />
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center gap-4 pt-4 border-t border-[#e3d5c4]">
            <button type="submit" 
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <i class="fa-solid fa-floppy-disk"></i>
                {{ __('Simpan Perubahan') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" 
                   x-show="show" 
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)" 
                   class="text-sm text-emerald-600 flex items-center gap-1">
                    <i class="fa-solid fa-circle-check"></i>
                    Tersimpan.
                </p>
            @endif
        </div>
    </form>
</section>