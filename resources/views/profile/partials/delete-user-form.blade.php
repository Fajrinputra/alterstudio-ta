<section x-data="{ open: {{ $errors->userDeletion->any() ? 'true' : 'false' }} }" class="space-y-4">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <h4 class="font-display text-xl text-[#3F2B1B] font-semibold flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                Hapus Akun
            </h4>
            <p class="text-sm text-[#7A5B3A]">Akun dapat dihapus jika tidak memiliki proses pemesanan atau project aktif.</p>
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
                        <p class="mt-1 text-sm text-[#7A5B3A]">Tindakan ini akan mengeluarkan Anda dari sistem.</p>
                    </div>
                </div>

                <div class="rounded-3xl border border-red-200 bg-red-50 p-5">
                    <p class="flex items-start gap-3 text-sm text-red-700">
                        <i class="fa-solid fa-circle-exclamation mt-1"></i>
                        <span>Data pribadi akun akan dihapus. Jika akun memiliki riwayat transaksi yang sudah selesai, sistem akan menonaktifkan dan menganonimkan akun agar riwayat laporan tetap aman.</span>
                    </p>
                </div>

                @if($errors->userDeletion->any())
                    <p class="flex items-start gap-3 rounded-3xl border border-red-200 bg-white px-5 py-4 text-sm text-red-600">
                        <i class="fa-solid fa-circle-exclamation mt-1"></i>
                        <span>{{ $errors->userDeletion->first() }}</span>
                    </p>
                @endif

                <div class="flex flex-col-reverse gap-3 border-t border-[#EDE0D0] pt-5 sm:flex-row sm:justify-end">
                    <button type="button"
                            @click="open = false"
                            class="inline-flex items-center justify-center rounded-3xl border border-[#E1D3C5] px-7 py-3.5 font-medium text-[#5C432C] transition-all hover:bg-[#FAF6F0]">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-3 rounded-3xl bg-gradient-to-r from-red-600 to-red-700 px-7 py-3.5 font-semibold text-white transition-all hover:brightness-110">
                        <i class="fa-solid fa-trash-can"></i>
                        Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
    </template>
</section>
