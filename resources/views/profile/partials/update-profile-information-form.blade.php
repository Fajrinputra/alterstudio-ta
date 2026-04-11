<section class="bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl shadow-xl p-8">
    <header class="mb-8">
        <h2 class="font-display text-2xl text-[#3F2B1B] font-semibold flex items-center gap-3">
            <i class="fa-solid fa-circle-user text-[#D4A017]"></i>
            {{ __('Info Profil & Kontak') }}
        </h2>
        <p class="text-sm text-[#7A5B3A] mt-1">Perbarui nama, email, dan avatar Anda.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-7">
        @csrf
        @method('patch')

        {{-- Nama --}}
        <div class="space-y-2">
            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Nama Lengkap</label>
            <div class="relative">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[#D4A017]">
                    <i class="fa-solid fa-user"></i>
                </span>
                <x-text-input id="name"
                              name="name"
                              type="text"
                              class="w-full pl-12 pr-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20"
                              :value="old('name', $user->name)"
                              required autofocus
                              autocomplete="name"
                              placeholder="Nama lengkap" />
            </div>
            <x-input-error class="mt-1 text-red-600" :messages="$errors->get('name')" />
        </div>

        {{-- Email --}}
        <div class="space-y-2">
            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Email</label>
            <div class="relative">
                <span class="absolute left-5 top-1/2 -translate-y-1/2 text-[#D4A017]">
                    <i class="fa-solid fa-envelope"></i>
                </span>
                <x-text-input id="email"
                              name="email"
                              type="email"
                              class="w-full pl-12 pr-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20"
                              :value="old('email', $user->email)"
                              required
                              autocomplete="username"
                              placeholder="nama@email.com" />
            </div>
            <x-input-error class="mt-1 text-red-600" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-3xl">
                    <p class="text-xs text-amber-700 flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        Email Anda belum terverifikasi.
                        <button form="send-verification"
                                class="ml-auto text-amber-800 font-medium underline hover:text-amber-900">
                            Kirim ulang verifikasi
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-xs text-emerald-600 flex items-center gap-1">
                            <i class="fa-solid fa-circle-check"></i>
                            Link verifikasi baru telah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Avatar Upload --}}
        <div class="space-y-4 pt-4 border-t border-[#EDE0D0]">
            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Foto Profil (opsional)</label>
            
            <div class="flex items-start gap-6">
                <div class="flex-shrink-0">
                    @if($user->avatar_path)
                        <img src="{{ Storage::url($user->avatar_path) }}" 
                             class="h-20 w-20 rounded-3xl border-4 border-white shadow-md object-cover" alt="Avatar">
                    @else
                        <div class="h-20 w-20 rounded-3xl border-4 border-white bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center">
                            <i class="fa-solid fa-user text-4xl text-[#D4A017]/40"></i>
                        </div>
                    @endif
                </div>

                <div class="flex-1">
                    <input id="avatar" name="avatar" type="file" accept="image/*"
                           class="w-full text-sm file:mr-6 file:py-4 file:px-8 file:rounded-3xl file:border-0 file:bg-[#FAF6F0] file:text-[#3F2B1B] file:font-medium hover:file:bg-white">
                    <p class="text-xs text-[#7A5B3A] mt-3">
                        <i class="fa-solid fa-circle-info"></i>
                        Format: JPG, PNG • Maksimal 2 MB
                    </p>
                </div>
            </div>
            <x-input-error class="mt-1 text-red-600" :messages="$errors->get('avatar')" />
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center gap-4 pt-6 border-t border-[#EDE0D0]">
            <button type="submit"
                    class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all">
                <i class="fa-solid fa-floppy-disk"></i>
                {{ __('Simpan Perubahan') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm text-emerald-600 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check"></i>
                    Perubahan berhasil disimpan.
                </p>
            @endif
        </div>
    </form>
</section>