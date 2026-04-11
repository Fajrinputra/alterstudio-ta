<section x-data="{ open: {{ $errors->userDeletion->any() ? 'true' : 'false' }} }" class="space-y-4">
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <h4 class="font-display text-xl text-[#3F2B1B] font-semibold flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                Hapus Akun
            </h4>
            <p class="text-sm text-[#7A5B3A]">Tindakan ini tidak dapat dibatalkan. Semua data akan dihapus permanen.</p>
        </div>
        <button @click="open = true"
                class="inline-flex items-center gap-3 px-7 py-3.5 rounded-3xl bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all">
            <i class="fa-solid fa-trash-can"></i>
            Hapus Akun Saya
        </button>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.outside="open = false"
             class="bg-white rounded-3xl shadow-2xl border border-[#EDE0D0] max-w-md w-full overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="px-8 pt-8 pb-4 flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-3xl"></i>
                </div>
                <div>
                    <h5 class="font-display text-2xl font-bold text-[#3F2B1B]">Hapus Akun Permanen?</h5>
                    <p class="text-[#7A5B3A] text-sm">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>

            <div class="mx-8 bg-red-50 border border-red-200 rounded-3xl p-6 mb-6">
                <p class="text-sm text-red-700 flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-1"></i>
                    <span>Semua data akun, booking, project, dan foto akan dihapus secara permanen dan tidak dapat dikembalikan.</span>
                </p>
            </div>

            <form method="POST" action="{{ route('profile.destroy') }}" class="px-8 pb-8 space-y-6">
                @csrf
                @method('DELETE')
                
                <div class="space-y-2">
                    <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">
                        Masukkan Password untuk Konfirmasi
                    </label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-red-500">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" required
                               placeholder="••••••••"
                               class="w-full pl-12 pr-6 py-4 rounded-3xl border border-red-200 bg-white focus:border-red-500 focus:ring-red-500 transition-all">
                    </div>
                    @if($errors->userDeletion->any())
                        <p class="text-sm text-red-600 flex items-center gap-1 mt-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ $errors->userDeletion->first() }}
                        </p>
                    @endif
                </div>

                <div class="flex justify-end gap-4 pt-4 border-t border-[#EDE0D0]">
                    <button type="button"
                            @click="open = false"
                            class="px-8 py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] font-medium hover:bg-white hover:border-[#D4A017] transition-all">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold shadow-lg hover:shadow-xl hover:brightness-110 transition-all">
                        <i class="fa-solid fa-trash-can"></i>
                        Hapus Akun Permanen
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>


