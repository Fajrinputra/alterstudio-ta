@php use Illuminate\Support\Str; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-box text-[#D4A017]"></i>
                    Katalog Layanan
                </p>
                <h2 class="text-4xl font-display font-bold tracking-tighter text-[#3F2B1B] mt-1">
                    Tambah Paket - <span class="bg-gradient-to-r from-[#D4A017] to-[#E07A5F] bg-clip-text text-transparent">{{ $category->name }}</span>
                </h2>
            </div>
            <a href="{{ route('admin.catalog.packages', $category) }}"
               class="inline-flex items-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:shadow-md transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-2xl p-10">
                <form method="POST" action="{{ route('admin.catalog.packages.store', $category) }}" enctype="multipart/form-data" class="space-y-10">
                    @csrf

                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Nama Paket -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                <i class="fa-solid fa-box text-[#D4A017]"></i>
                                Nama Paket
                            </label>
                            <input name="name" value="{{ old('name') }}" required
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                   placeholder="Contoh: Basic Wedding">
                            @error('name')
                                <p class="text-xs text-red-600 flex items-center gap-1 mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Harga -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                <i class="fa-solid fa-money-bill text-[#D4A017]"></i>
                                Harga (Rp)
                            </label>
                            <input type="number" name="price" min="0" value="{{ old('price') }}" required
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                   placeholder="3500000">
                            @error('price')
                                <p class="text-xs text-red-600 flex items-center gap-1 mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Durasi -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                <i class="fa-solid fa-clock text-[#D4A017]"></i>
                                Durasi (menit)
                            </label>
                            <input type="number" name="duration_minutes" min="1" value="{{ old('duration_minutes', 60) }}" required
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                            @error('duration_minutes')
                                <p class="text-xs text-red-600 flex items-center gap-1 mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Max Orang -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                <i class="fa-solid fa-user text-[#D4A017]"></i>
                                Max Orang
                            </label>
                            <input type="number" name="max_people" min="1" value="{{ old('max_people') }}"
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                   placeholder="Kosongkan jika tidak terbatas">
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                            <i class="fa-solid fa-file-lines text-[#D4A017]"></i>
                            Deskripsi
                        </label>
                        <textarea name="description" rows="4"
                                  class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                  placeholder="Deskripsi singkat tentang paket...">{{ old('description') }}</textarea>
                    </div>

                    <!-- Status Aktif -->
                    <div class="flex items-center gap-3 p-5 bg-[#FAF6F0] rounded-3xl border border-[#EDE0D0]">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="w-5 h-5 rounded border-[#E1D3C5] text-[#D4A017] focus:ring-[#D4A017]">
                        <label class="text-[#5C432C]">Paket aktif dan dapat dipilih oleh klien</label>
                    </div>

                    <!-- Features -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                            <i class="fa-solid fa-star text-[#D4A017]"></i>
                            Yang Didapat (satu per baris)
                        </label>
                        <textarea name="features" rows="5"
                                  class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                  placeholder="4 Jam Sesi Foto&#10;1 Fotografer Professional&#10;200 Foto Edit (Soft File)">{{ old('features') }}</textarea>
                    </div>

                    <!-- Add-ons -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                                <i class="fa-solid fa-plus-circle text-[#D4A017]"></i>
                                Add-on Paket
                            </label>
                            <button type="button" id="add-addon-btn"
                                    class="px-6 py-3 rounded-3xl bg-[#D4A017] text-white text-sm font-semibold hover:bg-[#E07A5F] transition-all">
                                <i class="fa-solid fa-plus mr-2"></i>Tambah Add-on
                            </button>
                        </div>
                        <p class="text-xs text-[#8B7359]">Isi nama add-on, harga, dan satuan (opsional).</p>

                        <div id="addons-wrapper" class="space-y-4">
                            @php
                                $oldAddons = old('addons', [['label' => '', 'price' => '', 'unit' => '']]);
                                if (empty($oldAddons)) $oldAddons = [['label' => '', 'price' => '', 'unit' => '']];
                            @endphp
                            @foreach($oldAddons as $index => $addon)
                                <div class="addon-row grid md:grid-cols-[1fr_180px_180px_auto] gap-4 items-end">
                                    <div>
                                        <input name="addons[{{ $index }}][label]" value="{{ $addon['label'] ?? '' }}"
                                               class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                               placeholder="Nama add-on">
                                    </div>
                                    <div>
                                        <input type="number" name="addons[{{ $index }}][price]" min="0" value="{{ $addon['price'] ?? '' }}"
                                               class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                               placeholder="Harga">
                                    </div>
                                    <div>
                                        <input name="addons[{{ $index }}][unit]" value="{{ $addon['unit'] ?? '' }}"
                                               class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                               placeholder="Satuan (contoh: orang)">
                                    </div>
                                    <button type="button" class="addon-remove-btn px-6 py-4 rounded-3xl border border-red-200 text-red-600 hover:bg-red-50 transition-all">
                                        Hapus
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                            <i class="fa-solid fa-file-contract text-[#D4A017]"></i>
                            Syarat & Ketentuan
                        </label>
                        <textarea name="terms" rows="4"
                                  class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                  placeholder="Contoh: Pembayaran DP 50%, sisanya lunas H-7...">{{ old('terms') }}</textarea>
                    </div>

                    <!-- Overview Image -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                            <i class="fa-solid fa-image text-[#D4A017]"></i>
                            Foto Overview (1 buah, maks 20 MB)
                        </label>
                        <input type="file" name="overview_image" accept="image/*"
                               class="w-full text-sm file:mr-4 file:py-3 file:px-6 file:rounded-3xl file:border-0 file:bg-[#D4A017] file:text-white file:font-medium">
                        @error('overview_image')
                            <p class="text-xs text-red-600 flex items-center gap-1 mt-1"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gallery -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-[#5C432C] flex items-center gap-2">
                            <i class="fa-solid fa-images text-[#D4A017]"></i>
                            Galeri Foto (maks 20)
                        </label>
                        <input type="file" name="gallery[]" multiple accept="image/*"
                               class="w-full text-sm file:mr-4 file:py-3 file:px-6 file:rounded-3xl file:border-0 file:bg-[#D4A017] file:text-white file:font-medium">
                        @error('gallery')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-[#EDE0D0]">
                        <a href="{{ route('admin.catalog.packages', $category) }}"
                           class="px-8 py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all">
                            Batal
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-3 px-10 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg hover:shadow-xl transition-all">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Simpan Paket
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Add-on dynamic rows
        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.getElementById('addons-wrapper');
            const addBtn = document.getElementById('add-addon-btn');
            let index = {{ count($oldAddons) }};

            if (addBtn) {
                addBtn.addEventListener('click', () => {
                    const row = `
                        <div class="addon-row grid md:grid-cols-[1fr_180px_180px_auto] gap-4 items-end">
                            <input name="addons[${index}][label]" 
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                   placeholder="Nama add-on">
                            <input type="number" name="addons[${index}][price]" min="0"
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                   placeholder="Harga">
                            <input name="addons[${index}][unit]"
                                   class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] focus:border-[#D4A017] transition-all"
                                   placeholder="Satuan">
                            <button type="button" class="addon-remove-btn px-6 py-4 rounded-3xl border border-red-200 text-red-600 hover:bg-red-50 transition-all">
                                Hapus
                            </button>
                        </div>
                    `;
                    wrapper.insertAdjacentHTML('beforeend', row);
                    index++;
                });
            }

            // Remove handler
            wrapper.addEventListener('click', (e) => {
                if (e.target.classList.contains('addon-remove-btn')) {
                    if (wrapper.querySelectorAll('.addon-row').length > 1) {
                        e.target.closest('.addon-row').remove();
                    }
                }
            });
        });
    </script>
</x-app-layout>