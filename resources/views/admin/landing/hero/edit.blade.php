<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-[#D4A017]"></i>
                    Slide Hero
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Edit <span class="font-medium bg-gradient-to-r from-[#D4A017] to-[#E07A5F] bg-clip-text text-transparent">Slide #{{ $slide->sort_order }}</span>
                </h2>
            </div>
            <a href="{{ route('manager.landing.hero') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-5">
                    <p class="text-red-700 font-medium mb-2">Harap perbaiki:</p>
                    <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-2xl px-5 py-4">
                    <i class="fa-solid fa-circle-check text-green-500"></i>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white/85 border border-[#EDE0D0] rounded-3xl p-8 shadow-2xl"
                 x-data="{
                     previewSrc: null,
                     fileName: '',
                     handleFile(e) {
                         const f = e.target.files[0];
                         if (!f) return;
                         this.fileName = f.name;
                         const reader = new FileReader();
                         reader.onload = ev => this.previewSrc = ev.target.result;
                         reader.readAsDataURL(f);
                     }
                 }">

                {{-- Info nomor urut (read-only) --}}
                <div class="flex items-center gap-3 bg-[#FAF6F0] border border-[#EDE0D0] rounded-2xl px-5 py-3 mb-6">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        {{ $slide->sort_order }}
                    </div>
                    <p class="text-sm text-[#7A5B3A]">Nomor urut slide. Urutan dikelola otomatis oleh sistem.</p>
                </div>

                <form method="POST" action="{{ route('manager.landing.hero.update', $slide) }}"
                      enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Eyebrow --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-[#7A5B3A] tracking-widest uppercase">Judul Kecil (Eyebrow) <span class="text-[#8B7359] font-normal normal-case tracking-normal">— opsional</span></label>
                        <input type="text" name="eyebrow" value="{{ old('eyebrow', $slide->eyebrow) }}"
                               class="w-full px-5 py-4 rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] focus:bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B] text-sm"
                               placeholder="Contoh: CASA DE ALTER">
                    </div>

                    {{-- Title --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-[#7A5B3A] tracking-widest uppercase">Judul Utama <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $slide->title) }}" required
                               class="w-full px-5 py-4 rounded-2xl border @error('title') border-red-400 bg-red-50 @else border-[#E1D3C5] bg-[#FAF6F0] @enderror focus:bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B]">
                        @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Subtitle --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-[#7A5B3A] tracking-widest uppercase">Subjudul <span class="text-[#8B7359] font-normal normal-case tracking-normal">— opsional</span></label>
                        <textarea name="subtitle" rows="3"
                                  class="w-full px-5 py-4 rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] focus:bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B] text-sm resize-y">{{ old('subtitle', $slide->subtitle) }}</textarea>
                    </div>

                    {{-- Status Aktif --}}
                    <div x-data="{ active: {{ old('is_active', $slide->is_active) ? 'true' : 'false' }} }">
                        <input type="hidden" name="is_active" :value="active ? 1 : 0">
                        <label class="flex items-center gap-4 cursor-pointer rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] px-5 py-4 hover:border-[#D4A017] transition-all">
                            <input type="checkbox" x-model="active" class="sr-only">
                            <span class="relative flex-shrink-0 w-12 h-6 rounded-full transition-colors duration-300"
                                  :class="active ? 'bg-[#D4A017]' : 'bg-[#E1D3C5]'">
                                <span class="absolute top-1 left-1 w-4 h-4 rounded-full bg-white shadow transition-transform duration-300"
                                      :class="active ? 'translate-x-6' : 'translate-x-0'"></span>
                            </span>
                            <span class="text-sm font-medium text-[#3F2B1B]" x-text="active ? 'Tampilkan di Hero Landing Page' : 'Sembunyikan dari Hero'"></span>
                        </label>
                    </div>

                    {{-- Foto saat ini + Ganti Foto --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-[#7A5B3A] tracking-widest uppercase">Foto Background</label>

                        {{-- Current photo --}}
                        @if($slide->image_path)
                            <div class="relative rounded-2xl overflow-hidden border border-[#EDE0D0]">
                                <img src="{{ Storage::url($slide->image_path) }}"
                                     alt="{{ $slide->title }}"
                                     class="w-full h-48 object-cover"
                                     x-show="!previewSrc">
                                <div class="absolute top-2 left-2 text-xs bg-black/60 text-white px-3 py-1 rounded-full backdrop-blur-sm"
                                     x-show="!previewSrc">Foto saat ini</div>
                            </div>
                        @endif

                        {{-- Preview foto baru --}}
                        <div x-show="previewSrc" class="relative rounded-2xl overflow-hidden border border-[#D4A017]">
                            <img :src="previewSrc" alt="preview baru" class="w-full h-48 object-cover">
                            <div class="absolute top-2 left-2 text-xs bg-[#D4A017] text-white px-3 py-1 rounded-full">
                                Foto baru
                            </div>
                        </div>

                        {{-- Upload area --}}
                        <div class="flex items-center gap-3">
                            <button type="button"
                                    @click="$refs.imgEdit.click()"
                                    class="flex-1 py-3.5 rounded-2xl border-2 border-dashed border-[#E1D3C5] hover:border-[#D4A017] text-sm text-[#7A5B3A] hover:text-[#3F2B1B] transition-all font-medium flex items-center justify-center gap-2">
                                <i class="fa-solid fa-cloud-arrow-up text-[#D4A017]"></i>
                                <span x-text="fileName || 'Ganti foto (opsional)'"></span>
                            </button>
                        </div>
                        <input type="file" name="image" accept="{{ \App\Support\ImageUploadValidation::ACCEPT_ATTRIBUTE }}"
                               class="hidden" x-ref="imgEdit" @change="handleFile($event)">
                        <p class="text-xs text-[#8B7359]">JPG, JPEG, PNG, WEBP — Maks. 20 MB. Kosongkan jika tidak ingin mengganti foto.</p>
                        @error('image')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex gap-3 pt-4 border-t border-[#EDE0D0]">
                        <a href="{{ route('manager.landing.hero') }}"
                           class="flex-1 h-12 rounded-2xl border border-[#E1D3C5] text-[#5C432C] font-medium hover:bg-[#FAF6F0] transition-all flex items-center justify-center">
                            Batal
                        </a>
                        <button type="submit"
                                class="flex-[2] h-12 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
