<section x-data="{ open: false }" class="space-y-4">
    <div class="flex items-center justify-between">
        <div class="space-y-1">
            <h4 class="font-display text-xl text-[#3f2b1b] font-semibold flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                Hapus Akun
            </h4>
            <p class="text-sm text-[#6f5134]">Tindakan ini tidak dapat dibatalkan. Semua data akan dihapus permanen.</p>
        </div>
        <button @click="open = true" 
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold shadow-lg shadow-red-600/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
            <i class="fa-solid fa-trash-can"></i>
            Hapus Akun
        </button>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.outside="open = false" 
             class="bg-white rounded-2xl shadow-2xl border border-[#e3d5c4] max-w-md w-full p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            {{-- Modal Header --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl"></i>
                </div>
                <div>
                    <h5 class="font-display text-xl text-[#3f2b1b] font-bold">Konfirmasi Penghapusan</h5>
                    <p class="text-sm text-[#6f5134]">Tindakan ini permanen</p>
                </div>
            </div>

            {{-- Warning --}}
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                <p class="text-sm text-red-700 flex items-start gap-2">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                    <span>Semua data akun, booking, dan project akan dihapus permanen dan tidak dapat dikembalikan.</span>
                </p>
            </div>

            {{-- Form --}}
            <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4">
                @csrf
                @method('delete')
                
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-[#6f5134]">
                        <i class="fa-solid fa-lock mr-1"></i>
                        Masukkan Password untuk Konfirmasi
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#b58042]">
                            <i class="fa-solid fa-key"></i>
                        </span>
                        <input type="password" name="password" 
                               placeholder="••••••••" 
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-[#d7c5b2] bg-[#fdf8f2] text-[#4a301f] focus:border-red-500 focus:ring-red-500" 
                               required>
                    </div>
                    @if($errors->userDeletion->any())
                        <p class="text-sm text-red-600 flex items-center gap-1 mt-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ $errors->userDeletion->first('password') }}
                        </p>
                    @endif
                </div>

                {{-- Modal Actions --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-[#e3d5c4]">
                    <button type="button" 
                            @click="open = false" 
                            class="px-5 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-[#fcf7f1] transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold shadow-lg shadow-red-600/30 hover:shadow-xl transition-all">
                        <i class="fa-solid fa-trash-can"></i>
                        Hapus Permanen
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>