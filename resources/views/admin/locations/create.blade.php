<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-building-circle-plus text-[#D4A017]"></i>
                    Cabang Studio
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">Tambah Cabang</h2>
            </div>
            <a href="{{ route('admin.locations.manage') }}"
               class="inline-flex items-center justify-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <p class="font-semibold">Data belum bisa disimpan.</p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.locations.store') }}" enctype="multipart/form-data" class="bg-white/85 border border-[#EDE0D0] rounded-3xl p-8 md:p-10 shadow-2xl space-y-7">
                @csrf

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Nama Cabang</label>
                        <input name="name" required value="{{ old('name') }}" class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017]" placeholder="Cabang 1">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Link Google Maps</label>
                        <input name="map_url" type="url" value="{{ old('map_url') }}" class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017]" placeholder="https://maps.google.com/...">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Alamat Lengkap</label>
                    <input name="address" value="{{ old('address') }}" class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017]" placeholder="Alamat cabang studio">
                </div>

                <div class="space-y-2">
                    <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Deskripsi</label>
                    <textarea name="description" rows="4" class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017]" placeholder="Deskripsi cabang">{{ old('description') }}</textarea>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Foto Lokasi</label>
                    <input type="file" name="photos[]" accept="image/*" multiple class="w-full text-sm file:mr-6 file:py-4 file:px-8 file:rounded-3xl file:border-0 file:bg-[#FAF6F0] file:text-[#3F2B1B] file:font-medium hover:file:bg-white">
                    <p class="text-xs text-[#8B7359]">Maksimal 10 foto lokasi.</p>
                </div>

                <label class="flex items-center gap-3 rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] px-5 py-4 text-[#3F2B1B]">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017] focus:ring-[#D4A017]">
                    <span class="font-medium">Cabang aktif dan dapat dipilih klien</span>
                </label>

                <div class="flex justify-end gap-3 pt-4 border-t border-[#EDE0D0]">
                    <a href="{{ route('admin.locations.manage') }}" class="px-7 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-[#FAF6F0] transition-all">Batal</a>
                    <button class="inline-flex items-center gap-3 px-8 py-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl transition-all">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Cabang
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
