<section x-data="{ open: {{ $errors->userDeletion->any() ? 'true' : 'false' }} }" class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <h4 class="font-display text-xl text-[#3F2B1B] font-semibold flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                Hapus Akun
            </h4>
            <p class="text-sm text-[#7A5B3A]">Akun hanya dapat dihapus setelah password dikonfirmasi.</p>
        </div>
        <button type="button"
                @click="open = true"
                class="inline-flex items-center justify-center gap-3 px-7 py-3.5 rounded-3xl border border-red-200 bg-red-50 text-red-600 font-semibold transition-all hover:border-red-300 hover:bg-red-100">
            <i class="fa-solid fa-trash-can"></i>
            Hapus Akun Saya
        </button>
    </div>

    <template x-teleport="body">
    <div x-show="open"
         x-cloak
         class="fixed inset-0 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" style="z-index: 99999;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="open = false">
        <div @click.outside="open = false"
             class="bg-white rounded-3xl shadow-2xl border border-[#EDE0D0] max-w-md w-full overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            <form method="POST" action="{{ route('profile.destroy') }}" class="p-8 space-y-6">
                @csrf
                @method('DELETE')

                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-100">
                        <i class="fa-solid fa-triangle-exclamation text-3xl text-red-600"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-display text-2xl font-bold text-[#3F2B1B]">Hapus Akun?</h3>
                        <p class="mt-1 text-sm text-[#7A5B3A]">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-red-200 bg-red-50 p-5">
                    <p class="flex items-start gap-3 text-sm text-red-700">
                        <i class="fa-solid fa-circle-exclamation mt-1"></i>
                        <span>Akun profil Anda akan dihapus permanen. Data yang masih terkait proses aktif dapat membuat penghapusan ditolak oleh sistem.</span>
                    </p>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-semibold uppercase tracking-widest text-[#7A5B3A]">
                        Password Konfirmasi
                    </label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-red-500">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password"
                               name="password"
                               required
                               placeholder="Masukkan password akun"
                               class="w-full rounded-3xl border border-red-200 bg-white py-4 pl-12 pr-6 text-[#3F2B1B] transition-all focus:border-red-500 focus:ring-red-500">
                    </div>
                    @if($errors->userDeletion->any())
                        <p class="mt-1 flex items-center gap-2 text-sm text-red-600">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ $errors->userDeletion->first() }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-[#EDE0D0] pt-5 sm:flex-row sm:justify-end">
                    <button type="button"
                            @click="open = false"
                            class="inline-flex items-center justify-center rounded-3xl border border-[#E1D3C5] px-7 py-3.5 font-medium text-[#5C432C] transition-all hover:bg-[#FAF6F0]">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-3 rounded-3xl bg-gradient-to-r from-red-600 to-red-700 px-7 py-3.5 font-semibold text-white transition-all hover:brightness-110">
                        <i class="fa-solid fa-trash-can"></i>
                        Hapus Permanen
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>
</section>
