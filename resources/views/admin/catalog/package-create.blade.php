<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-box text-[#b58042]"></i>
                    Katalog Layanan
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Tambah Paket - <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">{{ $category->name }}</span>
                </h2>
            </div>
            <a href="{{ route('admin.catalog.packages', $category) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/20 to-[#8b5b2e]/20 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-8 shadow-lg">
                    <form method="POST" action="{{ route('admin.catalog.packages.store', $category) }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        {{-- Grid 2 kolom untuk info dasar --}}
                        <div class="grid md:grid-cols-2 gap-5">
                            {{-- Nama Paket --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-box text-[#b58042]"></i>
                                    Nama Paket
                                </label>
                                <input name="name" value="{{ old('name') }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                       placeholder="Contoh: Basic Wedding">
                                @error('name') 
                                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Harga --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-money-bill text-[#b58042]"></i>
                                    Harga (Rp)
                                </label>
                                <input type="number" name="price" min="0" value="{{ old('price') }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                       placeholder="3500000">
                                @error('price') 
                                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Durasi --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-clock text-[#b58042]"></i>
                                    Durasi (menit)
                                </label>
                                <input type="number" name="duration_minutes" min="1" value="{{ old('duration_minutes', 60) }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                                @error('duration_minutes') 
                                    <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                        <i class="fa-solid fa-circle-exclamation"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Max Orang --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-user text-[#b58042]"></i>
                                    Max Orang
                                </label>
                                <input type="number" name="max_people" min="1" value="{{ old('max_people') }}"
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                       placeholder="Kosongkan jika tidak terbatas">
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                <i class="fa-solid fa-file-lines text-[#b58042]"></i>
                                Deskripsi
                            </label>
                            <textarea name="description" rows="2" 
                                      class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                      placeholder="Deskripsi singkat tentang paket">{{ old('description') }}</textarea>
                        </div>

                        {{-- Status Aktif --}}
                        <div class="flex items-center gap-2 p-3 bg-white/50 rounded-xl border border-[#e3d5c4]">
                            <input type="checkbox" name="is_active" value="1" checked
                                   class="w-4 h-4 rounded border-[#d7c5b2] text-[#b58042] focus:ring-[#b58042]">
                            <label class="text-sm text-[#6f5134]">Paket aktif dan dapat dipilih klien</label>
                        </div>

                        {{-- Features (yang didapat) --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                <i class="fa-solid fa-star text-[#b58042]"></i>
                                Yang Didapat (satu per baris)
                            </label>
                            <textarea name="features" rows="4" 
                                      class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                      placeholder="4 Jam Sesi Foto&#10;1 Fotografer Professional&#10;200 Foto Edit (Soft File)&#10;50 Foto Premium Edit&#10;Album 20x30 (20 Halaman)&#10;Free Konsultasi">{{ old('features') }}</textarea>
                        </div>

                        {{-- Add-ons --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-plus-circle text-[#b58042]"></i>
                                    Add-on Paket
                                </label>
                                <button type="button" data-addons-add
                                        class="px-3 py-1.5 rounded-lg bg-[#b58042] text-white text-xs font-semibold hover:bg-[#9b6a34] transition-colors">
                                    <i class="fa-solid fa-plus mr-1"></i>Tambah Add-on
                                </button>
                            </div>
                            <p class="text-xs text-[#8b7359]">Isi nama add-on dan harga agar total booking otomatis terhitung.</p>

                            @php
                                $oldAddons = old('addons', [['label' => '', 'price' => '']]);
                                if (!is_array($oldAddons) || count($oldAddons) === 0) {
                                    $oldAddons = [['label' => '', 'price' => '']];
                                }
                            @endphp
                            <div id="addons-wrapper" class="space-y-2">
                                @foreach($oldAddons as $index => $addon)
                                    <div class="grid md:grid-cols-[1fr_200px_auto] gap-2 items-center addon-row">
                                        <input name="addons[{{ $index }}][label]" value="{{ $addon['label'] ?? '' }}"
                                               class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                               placeholder="Nama add-on">
                                        <input type="number" name="addons[{{ $index }}][price]" min="0" value="{{ $addon['price'] ?? '' }}"
                                               class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                               placeholder="Harga">
                                        <button type="button" data-addons-remove
                                                class="px-3 py-2 rounded-lg border border-[#d7c5b2] text-[#6f5134] hover:bg-white transition-colors">
                                            Hapus
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            @error('addons')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @error('addons.*.label')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @error('addons.*.price')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Syarat & Ketentuan --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                <i class="fa-solid fa-file-contract text-[#b58042]"></i>
                                Syarat & Ketentuan
                            </label>
                            <textarea name="terms" rows="3" 
                                      class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                      placeholder="Contoh: Pembayaran DP 50%, sisanya lunas H-7">{{ old('terms') }}</textarea>
                        </div>

                        {{-- Foto Overview --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                <i class="fa-solid fa-image text-[#b58042]"></i>
                                Foto Overview (1 buah, maks 20 MB)
                            </label>
                            <input type="file" name="overview_image" accept="image/*"
                                   class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer">
                            <p class="text-xs text-[#8b7359] flex items-center gap-1 mt-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Foto ini akan ditampilkan sebagai gambar utama paket
                            </p>
                            @error('overview_image') 
                                <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Galeri --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                <i class="fa-solid fa-images text-[#b58042]"></i>
                                Galeri Foto (maks 20, jpg/png)
                            </label>
                            <input type="file" name="gallery[]" multiple accept="image/*"
                                   class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer">
                            @error('gallery') 
                                <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                            @error('gallery.*') 
                                <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#e3d5c4]">
                            <a href="{{ route('admin.catalog.packages', $category) }}" 
                               class="px-6 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Simpan Paket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    (function () {
        const wrapper = document.getElementById('addons-wrapper');
        const addBtn = document.querySelector('[data-addons-add]');
        if (!wrapper || !addBtn) return;

        let addonIndex = wrapper.querySelectorAll('.addon-row').length;

        function addonRow(index) {
            return `
                <div class="grid md:grid-cols-[1fr_200px_auto] gap-2 items-center addon-row">
                    <input name="addons[${index}][label]"
                           class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                           placeholder="Nama add-on">
                    <input type="number" name="addons[${index}][price]" min="0"
                           class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                           placeholder="Harga">
                    <button type="button" data-addons-remove
                            class="px-3 py-2 rounded-lg border border-[#d7c5b2] text-[#6f5134] hover:bg-white transition-colors">
                        Hapus
                    </button>
                </div>
            `;
        }

        addBtn.addEventListener('click', () => {
            wrapper.insertAdjacentHTML('beforeend', addonRow(addonIndex++));
        });

        wrapper.addEventListener('click', (event) => {
            if (!event.target.closest('[data-addons-remove]')) return;
            const rows = wrapper.querySelectorAll('.addon-row');
            if (rows.length <= 1) return;
            event.target.closest('.addon-row')?.remove();
        });
    })();
</script>
