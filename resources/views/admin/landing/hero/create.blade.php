<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-image text-[#D4A017]"></i>
                    Slide Hero
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Tambah <span class="font-medium bg-gradient-to-r from-[#D4A017] to-[#E07A5F] bg-clip-text text-transparent">Slide Baru</span>
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

                <form method="POST" action="{{ route('manager.landing.hero.store') }}"
                      enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Eyebrow --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-[#7A5B3A] tracking-widest uppercase">Judul Kecil (Eyebrow) <span class="text-[#8B7359] font-normal normal-case tracking-normal">— opsional</span></label>
                        <input type="text" name="eyebrow" value="{{ old('eyebrow') }}"
                               class="w-full px-5 py-4 rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] focus:bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B] text-sm"
                               placeholder="Contoh: CASA DE ALTER">
                    </div>

                    {{-- Title --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-[#7A5B3A] tracking-widest uppercase">Judul Utama <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full px-5 py-4 rounded-2xl border @error('title') border-red-400 bg-red-50 @else border-[#E1D3C5] bg-[#FAF6F0] @enderror focus:bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B]"
                               placeholder="Abadikan Momen Berharga Anda">
                        @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>

                    {{-- Subtitle --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-[#7A5B3A] tracking-widest uppercase">Subjudul <span class="text-[#8B7359] font-normal normal-case tracking-normal">— opsional</span></label>
                        <textarea name="subtitle" rows="3"
                                  class="w-full px-5 py-4 rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] focus:bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B] text-sm resize-y"
                                  placeholder="Sentuhan profesional dari pemesanan hingga hasil akhir...">{{ old('subtitle') }}</textarea>
                    </div>

                    {{-- Status Aktif --}}
                    <div x-data="{ active: {{ old('is_active') ? 'true' : 'false' }} }">
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

                    {{-- Upload Foto --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-[#7A5B3A] tracking-widest uppercase">Foto Background Hero <span class="text-red-500">*</span></label>

                        {{-- Preview area --}}
                        <div class="relative rounded-3xl overflow-hidden border-2 border-dashed border-[#E1D3C5] hover:border-[#D4A017] transition-colors cursor-pointer"
                             :class="previewSrc ? 'border-solid border-[#D4A017]' : ''"
                             @click="$refs.imgInput.click()">
                            {{-- Placeholder --}}
                            <div x-show="!previewSrc" class="flex flex-col items-center justify-center py-14 gap-3">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center">
                                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-[#D4A017]"></i>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-semibold text-[#3F2B1B]">Klik untuk pilih foto</p>
                                    <p class="text-xs text-[#8B7359] mt-1">JPG, JPEG, PNG, WEBP — Maks. 20 MB</p>
                                </div>
                            </div>
                            {{-- Preview --}}
                            <div x-show="previewSrc" class="relative">
                                <img :src="previewSrc" alt="preview" class="w-full h-56 object-cover">
                                <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                    <p class="text-white text-sm font-medium"><i class="fa-solid fa-camera mr-2"></i>Ganti Foto</p>
                                </div>
                            </div>
                        </div>

                        <input type="file" name="image" id="hero-image" accept="{{ \App\Support\ImageUploadValidation::ACCEPT_ATTRIBUTE }}"
                               class="hidden" x-ref="imgInput" required @change="handleFile($event)">

                        <p x-show="fileName" x-text="'✓ ' + fileName" class="text-xs text-green-700 font-medium"></p>
                        @error('image')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full h-14 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl shadow-[#D4A017]/30 hover:shadow-2xl hover:-translate-y-0.5 active:scale-[0.98] transition-all flex items-center justify-center gap-3 text-base">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Slide Baru
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
