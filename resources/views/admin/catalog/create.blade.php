<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-[#b58042]"></i>
                    Katalog Layanan
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Tambah <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">Katalog & Paket</span>
                </h2>
            </div>
            <a href="{{ route('admin.catalog') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.catalog.store') }}" class="space-y-6" enctype="multipart/form-data">
                @csrf
                
                {{-- Data Katalog Card --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/20 to-[#8b5b2e]/20 rounded-2xl blur-xl"></div>
                    <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                        <h3 class="text-xl font-light text-[#3f2b1b] mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-folder-open text-[#b58042]"></i>
                            Data Katalog
                        </h3>
                        
                        <div class="space-y-4">
                            {{-- Nama Katalog --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-tag text-[#b58042]"></i>
                                    Nama Katalog
                                </label>
                                <input name="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                       placeholder="Contoh: Wedding Package">
                                @error('name') 
                                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Deskripsi --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-file-lines text-[#b58042]"></i>
                                    Deskripsi
                                </label>
                                <textarea name="description" rows="2" 
                                          class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                          placeholder="Deskripsi singkat tentang kategori ini">{{ old('description') }}</textarea>
                                @error('description') 
                                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Paket dalam Katalog Card --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/20 to-[#8b5b2e]/20 rounded-2xl blur-xl"></div>
                    <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                        <h3 class="text-xl font-light text-[#3f2b1b] mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-boxes text-[#b58042]"></i>
                            Paket dalam Katalog
                        </h3>

                        <div id="packages-wrapper" class="space-y-4">
                            {{-- Template untuk paket baru --}}
                            <template id="package-template">
                                <div class="rounded-xl border border-[#e3d5c4] bg-white/50 backdrop-blur-sm p-5 space-y-4" data-package-card>
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-medium text-[#3f2b1b] flex items-center gap-2">
                                            <i class="fa-solid fa-box text-[#b58042]"></i>
                                            Paket Baru
                                        </h4>
                                    </div>

                                    <div class="grid md:grid-cols-2 gap-4">
                                        {{-- Nama Paket --}}
                                        <div class="space-y-2">
                                            <label class="text-xs font-medium text-[#6f5134]">Nama Paket</label>
                                            <input name="packages[__index__][name]" data-name
                                                   class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                                   required>
                                        </div>

                                        {{-- Harga --}}
                                        <div class="space-y-2">
                                            <label class="text-xs font-medium text-[#6f5134]">Harga (Rp)</label>
                                            <input type="number" name="packages[__index__][price]" data-name min="0"
                                                   class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                                   required>
                                        </div>
                                    </div>

                                    {{-- Deskripsi --}}
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-[#6f5134]">Deskripsi</label>
                                        <textarea name="packages[__index__][description]" data-name rows="2"
                                                  class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"></textarea>
                                    </div>

                                    {{-- Features (yang didapat) --}}
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                            <i class="fa-solid fa-star text-[#b58042]"></i>
                                            Yang didapat (satu per baris)
                                        </label>
                                        <textarea name="packages[__index__][features]" data-name rows="3"
                                                  class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                                  placeholder="4 Jam Sesi Foto&#10;1 Fotografer Professional&#10;200 Foto Edit"></textarea>
                                    </div>

                                    {{-- Add-ons --}}
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                            <i class="fa-solid fa-plus-circle text-[#b58042]"></i>
                                            Add-on (pisahkan koma)
                                        </label>
                                        <input name="packages[__index__][addons]" data-name
                                               class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                               placeholder="Album Cetak, Video Cinematic, Makeup">
                                    </div>

                                    {{-- Terms --}}
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-[#6f5134]">Syarat & Ketentuan</label>
                                        <textarea name="packages[__index__][terms]" data-name rows="2"
                                                  class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"></textarea>
                                    </div>

                                    {{-- Overview Image --}}
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                            <i class="fa-solid fa-image text-[#b58042]"></i>
                                            Foto Overview (1 buah)
                                        </label>
                                        <input type="file" name="packages[__index__][overview_image]" data-name accept="image/*"
                                               class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer">
                                    </div>

                                    {{-- Gallery --}}
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                            <i class="fa-solid fa-images text-[#b58042]"></i>
                                            Galeri Foto (maks 20)
                                        </label>
                                        <input type="file" name="packages[__index__][gallery][]" data-name multiple accept="image/*"
                                               class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer">
                                    </div>

                                    {{-- Hapus Button --}}
                                    <div class="flex justify-end pt-2">
                                        <button type="button" 
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-red-200 text-red-600 text-xs hover:bg-red-50 transition-colors"
                                                onclick="this.closest('[data-package-card]').remove()">
                                            <i class="fa-solid fa-trash-can"></i>
                                            Hapus Paket
                                        </button>
                                    </div>
                                </div>
                            </template>

                            {{-- Paket pertama (default) --}}
                            <div class="rounded-xl border border-[#e3d5c4] bg-white/50 backdrop-blur-sm p-5 space-y-4" data-package-card>
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-[#3f2b1b] flex items-center gap-2">
                                        <i class="fa-solid fa-box text-[#b58042]"></i>
                                        Paket 1
                                    </h4>
                                </div>

                                <div class="grid md:grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-[#6f5134]">Nama Paket</label>
                                        <input name="packages[0][name]"
                                               class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                               required>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-xs font-medium text-[#6f5134]">Harga (Rp)</label>
                                        <input type="number" name="packages[0][price]" min="0"
                                               class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                               required>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-[#6f5134]">Deskripsi</label>
                                    <textarea name="packages[0][description]" rows="2"
                                              class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"></textarea>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                        <i class="fa-solid fa-star text-[#b58042]"></i>
                                        Yang didapat (satu per baris)
                                    </label>
                                    <textarea name="packages[0][features]" rows="3"
                                              class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                              placeholder="4 Jam Sesi Foto&#10;1 Fotografer Professional&#10;200 Foto Edit"></textarea>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                        <i class="fa-solid fa-plus-circle text-[#b58042]"></i>
                                        Add-on (pisahkan koma)
                                    </label>
                                    <input name="packages[0][addons]"
                                           class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                           placeholder="Album Cetak, Video Cinematic, Makeup">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-[#6f5134]">Syarat & Ketentuan</label>
                                    <textarea name="packages[0][terms]" rows="2"
                                              class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white/50 text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"></textarea>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                        <i class="fa-solid fa-image text-[#b58042]"></i>
                                        Foto Overview (1 buah)
                                    </label>
                                    <input type="file" name="packages[0][overview_image]" accept="image/*"
                                           class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-xs font-medium text-[#6f5134] flex items-center gap-1">
                                        <i class="fa-solid fa-images text-[#b58042]"></i>
                                        Galeri Foto (maks 20)
                                    </label>
                                    <input type="file" name="packages[0][gallery][]" multiple accept="image/*"
                                           class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer">
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Tambah Paket --}}
                        <div class="flex justify-center pt-2">
                            <button type="button"
                                    x-data
                                    x-on:click="
                                        const wrapper = document.getElementById('packages-wrapper');
                                        const tpl = document.getElementById('package-template').content.cloneNode(true);
                                        const current = wrapper.querySelectorAll('[data-package-card]').length;
                                        tpl.querySelectorAll('[data-name]').forEach((el)=>{
                                            el.name = el.name.replace('__index__', current);
                                        });
                                        wrapper.appendChild(tpl);
                                    "
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] bg-white hover:bg-[#fcf7f1] hover:shadow-md transition-all">
                                <i class="fa-solid fa-plus"></i>
                                Tambah Paket Lain
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.catalog') }}" 
                       class="px-6 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Katalog
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>