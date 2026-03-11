<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-box text-[#b58042]"></i>
                    Katalog Layanan
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Edit Paket - <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">{{ $category->name }}</span>
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
                    <form method="POST" action="{{ route('admin.packages.update', $package) }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="category_id" value="{{ $category->id }}">

                        {{-- Grid 2 kolom untuk info dasar --}}
                        <div class="grid md:grid-cols-2 gap-5">
                            {{-- Nama Paket --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-box text-[#b58042]"></i>
                                    Nama Paket
                                </label>
                                <input name="name" value="{{ old('name', $package->name) }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                            </div>

                            {{-- Harga --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-money-bill text-[#b58042]"></i>
                                    Harga (Rp)
                                </label>
                                <input type="number" name="price" min="0" value="{{ old('price', $package->price) }}" required
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                            </div>

                            {{-- Durasi --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                    <i class="fa-solid fa-clock text-[#b58042]"></i>
                                    Durasi (menit)
                                </label>
                                <input type="number" name="duration_minutes" min="1" value="{{ old('duration_minutes', $package->duration_minutes) }}" required
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
                                <input type="number" name="max_people" min="1" value="{{ old('max_people', $package->max_people) }}"
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                <i class="fa-solid fa-file-lines text-[#b58042]"></i>
                                Deskripsi
                            </label>
                            <textarea name="description" rows="2" 
                                      class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">{{ old('description', $package->description) }}</textarea>
                        </div>

                        {{-- Status Aktif --}}
                        <div class="flex items-center gap-2 p-3 bg-white/50 rounded-xl border border-[#e3d5c4]">
                            <input type="checkbox" name="is_active" value="1" {{ $package->is_active ? 'checked' : '' }}
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
                                      class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">@if(old('features')){{ old('features') }}@else{{ is_array($package->features) ? implode("\n", $package->features) : $package->features }}@endif</textarea>
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
                                $existingAddons = old('addons');
                                if (!is_array($existingAddons)) {
                                    $existingAddons = collect($package->addons ?? [])
                                        ->map(function ($addon) {
                                            if (is_array($addon)) {
                                                return [
                                                    'label' => $addon['label'] ?? '',
                                                    'price' => $addon['price'] ?? 0,
                                                ];
                                            }
                                            if (is_string($addon)) {
                                                return ['label' => $addon, 'price' => 0];
                                            }
                                            return null;
                                        })
                                        ->filter()
                                        ->values()
                                        ->all();
                                }
                                if (count($existingAddons) === 0) {
                                    $existingAddons = [['label' => '', 'price' => '']];
                                }
                            @endphp
                            <div id="addons-wrapper" class="space-y-2">
                                @foreach($existingAddons as $index => $addon)
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
                                      class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">{{ old('terms', $package->terms) }}</textarea>
                        </div>

                        {{-- Foto Overview --}}
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                <i class="fa-solid fa-image text-[#b58042]"></i>
                                Foto Overview
                            </label>
                            
                            @if($package->overview_image)
                                <div class="flex items-start gap-4">
                                    <div class="w-32 h-32 rounded-lg overflow-hidden border border-[#e3d5c4] bg-white">
                                        <img src="{{ Storage::url($package->overview_image) }}" class="w-full h-full object-cover" alt="Overview">
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs text-[#8b7359] mb-2">Foto saat ini</p>
                                        <label class="inline-flex items-center gap-2 text-xs">
                                            <input type="checkbox" name="remove_overview" value="1" 
                                                   class="rounded border-[#d7c5b2] text-[#b58042] focus:ring-[#b58042]">
                                            <span class="text-[#6f5134]">Hapus foto ini</span>
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <input type="file" name="overview_image" accept="image/*"
                                   class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer">
                            @error('overview_image') 
                                <p class="text-xs text-red-600 flex items-center gap-1 mt-1">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Galeri --}}
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-[#6f5134] flex items-center gap-1">
                                <i class="fa-solid fa-images text-[#b58042]"></i>
                                Galeri Foto
                            </label>

                            @if(!empty($gallery))
                                <div>
                                    <p class="text-xs text-[#8b7359] mb-2">Galeri saat ini ({{ count($gallery) }} foto)</p>
                                    <div class="grid grid-cols-4 gap-2 mb-3">
                                        @foreach($gallery as $path)
                                            <div class="aspect-square rounded-lg overflow-hidden border border-[#e3d5c4] bg-white">
                                                <img src="{{ Storage::url($path) }}" class="w-full h-full object-cover" alt="foto paket">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <input type="file" name="gallery[]" multiple accept="image/*"
                                   class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer">
                            <p class="text-xs text-[#8b7359] flex items-center gap-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Upload foto baru untuk menambah ke galeri (maks 20 foto total)
                            </p>
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
                                <i class="fa-solid fa-pen-to-square"></i>
                                Update Paket
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
