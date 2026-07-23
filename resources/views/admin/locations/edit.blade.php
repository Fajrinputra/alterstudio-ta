@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square text-[#D4A017]"></i>
                    Cabang Studio
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">Edit Cabang</h2>
            </div>
            <a href="{{ route('admin.locations.show', $studioLocation) }}" class="inline-flex items-center justify-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all">
                Kembali ke Detail
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <section class="grid xl:grid-cols-[minmax(0,1fr)_420px] gap-8">
                <form method="POST" action="{{ route('admin.locations.update', $studioLocation) }}" enctype="multipart/form-data" class="bg-white/85 border border-[#EDE0D0] rounded-3xl p-8 md:p-10 shadow-2xl space-y-7">
                    @csrf
                    @method('PUT')

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Nama Cabang</label>
                            <input name="name" required value="{{ old('name', $studioLocation->name) }}" class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017]">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Link Google Maps</label>
                            <input name="map_url" type="url" value="{{ old('map_url', $studioLocation->map_url) }}" class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017]">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Alamat Lengkap</label>
                        <input name="address" value="{{ old('address', $studioLocation->address) }}" class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017]">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Deskripsi</label>
                        <textarea name="description" rows="4" class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B] focus:border-[#D4A017]">{{ old('description', $studioLocation->description) }}</textarea>
                    </div>

                    @if(count($studioLocation->photo_gallery ?? []))
                        <div class="space-y-3">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Foto Galeri Saat Ini</label>
                            <div class="grid sm:grid-cols-2 gap-4">
                                @foreach($studioLocation->photo_gallery as $photoIndex => $photo)
                                    <div class="group relative rounded-3xl overflow-hidden border border-[#EDE0D0]">
                                        <img src="{{ Storage::url($photo) }}"
                                             alt="{{ $studioLocation->name }}"
                                             class="aspect-[16/10] w-full object-cover">
                                        {{-- Overlay tombol hapus --}}
                                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-all duration-200 flex items-center justify-center">
                                            <form method="POST"
                                                  action="{{ route('admin.locations.photo.destroy', $studioLocation) }}"
                                                  onsubmit="return confirm('Hapus foto ini?')"
                                                  class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="photo_index" value="{{ $photoIndex }}">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-red-600 text-white text-sm font-semibold shadow-lg hover:bg-red-700 transition-colors">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                    Hapus Foto
                                                </button>
                                            </form>
                                        </div>
                                        {{-- Badge nomor --}}
                                        <div class="absolute top-2 left-2 w-6 h-6 rounded-lg bg-black/60 backdrop-blur-sm flex items-center justify-center text-white text-xs font-bold">
                                            {{ $photoIndex + 1 }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Tambah Foto Lokasi</label>
                        <input type="file" name="photos[]" accept="{{ \App\Support\ImageUploadValidation::ACCEPT_ATTRIBUTE }}" multiple class="w-full text-sm file:mr-6 file:py-4 file:px-8 file:rounded-3xl file:border-0 file:bg-[#FAF6F0] file:text-[#3F2B1B] file:font-medium hover:file:bg-white">
                        <p class="text-xs text-[#8B7359]">Maksimal 10 foto lokasi, 20 MB per foto. Format JPG, JPEG, PNG, WEBP, atau GIF. Video tidak diperbolehkan.</p>
                    </div>

                    <label class="flex items-center gap-3 rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] px-5 py-4 text-[#3F2B1B]">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $studioLocation->is_active)) class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017] focus:ring-[#D4A017]">
                        <span class="font-medium">Cabang aktif</span>
                    </label>

                    <div class="flex justify-end gap-3 pt-4 border-t border-[#EDE0D0]">
                        <a href="{{ route('admin.locations.show', $studioLocation) }}" class="px-7 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-[#FAF6F0] transition-all">Batal</a>
                        <button class="inline-flex items-center gap-3 px-8 py-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl transition-all">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

                <aside class="space-y-6">
                    <div class="bg-white border border-[#EDE0D0] rounded-3xl shadow-xl p-6">
                        <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3">
                            <i class="fa-solid fa-door-open text-[#D4A017]"></i>
                            Ruangan Studio
                        </h3>

                        <div class="mt-5 space-y-4">
                            @forelse($studioLocation->rooms as $room)
                                <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-5 space-y-3">
                                    <form method="POST" action="{{ route('admin.locations.room.update', $room) }}" enctype="multipart/form-data" class="space-y-3">
                                        @csrf
                                        @method('PUT')
                                        @if($room->photo_path)
                                            <img src="{{ Storage::url($room->photo_path) }}" alt="{{ $room->name }}" class="aspect-[16/10] w-full rounded-3xl object-cover border border-[#EDE0D0]">
                                        @endif
                                        <input name="name" required value="{{ $room->name }}" class="w-full px-5 py-3 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B]">
                                        <textarea name="description" rows="2" class="w-full px-5 py-3 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B]" placeholder="Deskripsi ruangan">{{ $room->description }}</textarea>
                                        <input type="file" name="photo" accept="{{ \App\Support\ImageUploadValidation::ACCEPT_ATTRIBUTE }}" class="w-full text-xs file:mr-4 file:py-3 file:px-5 file:rounded-3xl file:border-0 file:bg-white file:text-[#3F2B1B] file:font-medium">
                                        <p class="text-xs text-[#8B7359]">Maksimal 20 MB. Format JPG, JPEG, PNG, WEBP, atau GIF. Video tidak diperbolehkan.</p>
                                        <div class="flex items-center justify-between gap-3">
                                            <label class="inline-flex items-center gap-2 text-sm text-[#3F2B1B]">
                                                <input type="checkbox" name="is_active" value="1" @checked($room->is_active) class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017]">
                                                Aktif
                                            </label>
                                            <button class="px-5 py-2.5 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-sm font-semibold text-white">Simpan</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('admin.locations.room.destroy', $room) }}" onsubmit="return confirm('Hapus ruangan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="w-full rounded-3xl border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus Ruangan</button>
                                    </form>
                                </div>
                            @empty
                                <p class="text-sm text-[#8B7359]">Belum ada ruangan.</p>
                            @endforelse
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.locations.room.store') }}" enctype="multipart/form-data" class="bg-white border border-[#EDE0D0] rounded-3xl shadow-xl p-6 space-y-4">
                        @csrf
                        <input type="hidden" name="studio_location_code" value="{{ $studioLocation->location_code }}">
                        <h3 class="font-display text-2xl font-semibold text-[#3F2B1B]">Tambah Ruangan</h3>
                        <input name="name" required class="w-full px-5 py-3 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B]" placeholder="Nama ruangan">
                        <textarea name="description" rows="2" class="w-full px-5 py-3 rounded-3xl border border-[#E1D3C5] bg-white text-[#3F2B1B]" placeholder="Deskripsi ruangan"></textarea>
                        <input type="file" name="photo" accept="{{ \App\Support\ImageUploadValidation::ACCEPT_ATTRIBUTE }}" class="w-full text-xs file:mr-4 file:py-3 file:px-5 file:rounded-3xl file:border-0 file:bg-[#FAF6F0] file:text-[#3F2B1B] file:font-medium">
                        <p class="text-xs text-[#8B7359]">Maksimal 20 MB. Format JPG, JPEG, PNG, WEBP, atau GIF. Video tidak diperbolehkan.</p>
                        <label class="inline-flex items-center gap-2 text-sm text-[#3F2B1B]">
                            <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017]">
                            Aktif
                        </label>
                        <button class="w-full inline-flex items-center justify-center gap-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] px-6 py-3 font-semibold text-white shadow-xl">
                            <i class="fa-solid fa-plus"></i>
                            Tambah Ruangan
                        </button>
                    </form>
                </aside>
            </section>
        </div>
    </div>
</x-app-layout>
