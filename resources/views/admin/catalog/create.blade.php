@php use Illuminate\Support\Str; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-[#D4A017]"></i>
                    Katalog Layanan
                </p>
                <h2 class="text-4xl font-display font-bold tracking-tighter text-[#3F2B1B] mt-1">
                    Tambah <span class="bg-gradient-to-r from-[#D4A017] to-[#E07A5F] bg-clip-text text-transparent">Katalog & Paket</span>
                </h2>
            </div>
            <a href="{{ route('admin.catalog') }}"
               class="inline-flex items-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.catalog.store') }}" class="space-y-10" enctype="multipart/form-data">
                @csrf
                
                <!-- Data Katalog -->
                <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl p-8">
                    <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3 mb-6">
                        <i class="fa-solid fa-folder-open text-[#D4A017]"></i>
                        Data Katalog
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Nama Katalog -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                <i class="fa-solid fa-tag text-[#D4A017]"></i>
                                Nama Katalog
                            </label>
                            <input name="name" value="{{ old('name') }}" required
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                   placeholder="Contoh: Wedding Package">
                            @error('name')
                                <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Deskripsi -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                <i class="fa-solid fa-file-lines text-[#D4A017]"></i>
                                Deskripsi Katalog
                            </label>
                            <textarea name="description" rows="3"
                                      class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                      placeholder="Deskripsi singkat tentang kategori ini...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Paket dalam Katalog -->
                <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl p-8">
                    <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3 mb-6">
                        <i class="fa-solid fa-boxes text-[#D4A017]"></i>
                        Paket dalam Katalog
                    </h3>

                    <div id="packages-wrapper" class="space-y-8">
                        <!-- Template -->
                        <template id="package-template">
                            <div class="rounded-3xl border border-[#EDE0D0] bg-white p-8 space-y-6" data-package-card>
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-[#3F2B1B] flex items-center gap-3 text-lg">
                                        <i class="fa-solid fa-box text-[#D4A017]"></i>
                                        Paket Baru
                                    </h4>
                                    <button type="button" 
                                            onclick="this.closest('[data-package-card]').remove()"
                                            class="text-red-600 hover:text-red-700 text-sm flex items-center gap-1">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </div>

                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-[#5C432C]">Nama Paket</label>
                                        <input name="packages[__index__][name]" required
                                               class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-[#5C432C]">Harga (Rp)</label>
                                        <input type="number" name="packages[__index__][price]" min="0" required
                                               class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all">
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-[#5C432C]">Deskripsi</label>
                                    <textarea name="packages[__index__][description]" rows="3"
                                              class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"></textarea>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                        <i class="fa-solid fa-star text-[#D4A017]"></i>
                                        Yang Didapat (satu per baris)
                                    </label>
                                    <textarea name="packages[__index__][features]" rows="4"
                                              class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                              placeholder="4 Jam Sesi Foto&#10;1 Fotografer Professional&#10;200 Foto Edit"></textarea>
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                        <i class="fa-solid fa-plus-circle text-[#D4A017]"></i>
                                        Add-on (format: Nama|Harga, pisahkan dengan koma)
                                    </label>
                                    <input name="packages[__index__][addons]"
                                           class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                           placeholder="Album Cetak|50000, Video Cinematic|150000">
                                </div>

                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-[#5C432C]">Syarat & Ketentuan</label>
                                    <textarea name="packages[__index__][terms]" rows="3"
                                              class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"></textarea>
                                </div>

                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-[#5C432C]">Foto Overview</label>
                                        <input type="file" name="packages[__index__][overview_image]" accept="image/*"
                                               class="w-full text-sm file:mr-4 file:py-3 file:px-6 file:rounded-3xl file:border-0 file:bg-[#D4A017] file:text-white">
                                    </div>
                                    <div class="space-y-2">
                                        <label class="text-sm font-medium text-[#5C432C]">Galeri Foto (maks 20)</label>
                                        <input type="file" name="packages[__index__][gallery][]" multiple accept="image/*"
                                               class="w-full text-sm file:mr-4 file:py-3 file:px-6 file:rounded-3xl file:border-0 file:bg-[#D4A017] file:text-white">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Paket Pertama (Default) -->
                        <div class="rounded-3xl border border-[#EDE0D0] bg-white p-8 space-y-6" data-package-card>
                            <div class="flex items-center justify-between">
                                <h4 class="font-medium text-[#3F2B1B] flex items-center gap-3 text-lg">
                                    <i class="fa-solid fa-box text-[#D4A017]"></i>
                                    Paket 1
                                </h4>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-[#5C432C]">Nama Paket</label>
                                    <input name="packages[0][name]" required
                                           class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-[#5C432C]">Harga (Rp)</label>
                                    <input type="number" name="packages[0][price]" min="0" required
                                           class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-[#5C432C]">Deskripsi</label>
                                <textarea name="packages[0][description]" rows="3"
                                          class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"></textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                    <i class="fa-solid fa-star text-[#D4A017]"></i>
                                    Yang Didapat (satu per baris)
                                </label>
                                <textarea name="packages[0][features]" rows="4"
                                          class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                          placeholder="4 Jam Sesi Foto&#10;1 Fotografer Professional&#10;200 Foto Edit"></textarea>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                    <i class="fa-solid fa-plus-circle text-[#D4A017]"></i>
                                    Add-on (format: Nama|Harga, pisahkan dengan koma)
                                </label>
                                <input name="packages[0][addons]"
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                       placeholder="Album Cetak|50000, Video Cinematic|150000">
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium text-[#5C432C]">Syarat & Ketentuan</label>
                                <textarea name="packages[0][terms]" rows="3"
                                          class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"></textarea>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-[#5C432C]">Foto Overview</label>
                                    <input type="file" name="packages[0][overview_image]" accept="image/*"
                                           class="w-full text-sm file:mr-4 file:py-3 file:px-6 file:rounded-3xl file:border-0 file:bg-[#D4A017] file:text-white">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-[#5C432C]">Galeri Foto (maks 20)</label>
                                    <input type="file" name="packages[0][gallery][]" multiple accept="image/*"
                                           class="w-full text-sm file:mr-4 file:py-3 file:px-6 file:rounded-3xl file:border-0 file:bg-[#D4A017] file:text-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Tambah Paket -->
                    <div class="flex justify-center mt-8">
                        <button type="button"
                                onclick="addPackage()"
                                class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:shadow-md transition-all">
                            <i class="fa-solid fa-plus"></i>
                            Tambah Paket Lain
                        </button>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end gap-4 pt-6">
                    <a href="{{ route('admin.catalog') }}"
                       class="px-8 py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all">
                        Batal
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-3 px-10 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg hover:shadow-xl transition-all">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Katalog & Paket
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let packageIndex = 1;

        function addPackage() {
            const wrapper = document.getElementById('packages-wrapper');
            const template = document.getElementById('package-template').content.cloneNode(true);
            
            template.querySelectorAll('[name]').forEach(el => {
                if (el.name.includes('__index__')) {
                    el.name = el.name.replace('__index__', packageIndex);
                }
            });

            wrapper.appendChild(template);
            packageIndex++;
        }
    </script>
</x-app-layout>