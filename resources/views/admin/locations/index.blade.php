@php use Illuminate\Support\Facades\Storage; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-store text-[#b58042]"></i>
                    Cabang Studio
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Kelola <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">Cabang</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Session Status --}}
            @if (session('status'))
                <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span class="text-sm font-medium">{{ session('status') }}</span>
                </div>
            @endif

            {{-- Grid Form + Daftar --}}
            <div class="grid lg:grid-cols-2 gap-6">
                
                {{-- Form Tambah/Edit Cabang --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/20 to-[#8b5b2e]/20 rounded-2xl blur-xl"></div>
                    <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                        @php $isEditing = isset($editing) && $editing; @endphp
                        
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-light text-[#3f2b1b] flex items-center gap-2">
                                <i class="fa-solid fa-{{ $isEditing ? 'pen-to-square' : 'plus' }} text-[#b58042]"></i>
                                {{ $isEditing ? 'Edit Cabang' : 'Tambah Cabang Baru' }}
                            </h3>
                            @if($isEditing)
                                <a href="{{ route('admin.locations.manage') }}" 
                                   class="text-sm text-[#b58042] hover:text-[#8b5b2e] transition-colors flex items-center gap-1">
                                    <i class="fa-solid fa-xmark"></i>
                                    Batal
                                </a>
                            @endif
                        </div>

                        <form method="POST"
                              action="{{ $isEditing ? url('/admin/locations/'.$editing->id) : url('/admin/locations') }}"
                              enctype="multipart/form-data"
                              class="space-y-4">
                            @csrf
                            @if($isEditing)
                                @method('PUT')
                            @endif

                            {{-- Nama Cabang --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134]">
                                    <i class="fa-solid fa-store mr-1 text-[#b58042]"></i>
                                    Nama Cabang
                                </label>
                                <input name="name" required
                                       value="{{ old('name', $isEditing ? $editing->name : '') }}"
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                       placeholder="Contoh: Studio Alam Sutera">
                            </div>

                            {{-- Alamat --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134]">
                                    <i class="fa-solid fa-location-dot mr-1 text-[#b58042]"></i>
                                    Alamat
                                </label>
                                <input name="address"
                                       value="{{ old('address', $isEditing ? $editing->address : '') }}"
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                       placeholder="Jalan, kota, kode pos">
                            </div>

                            {{-- Link Maps --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134]">
                                    <i class="fa-solid fa-map mr-1 text-[#b58042]"></i>
                                    Link Google Maps
                                </label>
                                <input name="map_url" type="url"
                                       value="{{ old('map_url', $isEditing ? $editing->map_url : '') }}"
                                       placeholder="https://maps.google.com/..."
                                       class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]">
                            </div>

                            {{-- Upload Foto --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134]">
                                    <i class="fa-solid fa-images mr-1 text-[#b58042]"></i>
                                    Foto Lokasi
                                </label>
                                <input type="file" name="photos[]" accept="image/*" multiple
                                       class="w-full text-sm text-[#6f5134] file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#b58042] file:text-white hover:file:bg-[#9b6a34] file:cursor-pointer file:transition-colors">
                                
                                @if($isEditing && ($editing->photo_gallery || $editing->photo_path))
                                    <div class="mt-3 space-y-2">
                                        <p class="text-xs text-[#8b7359]">Foto saat ini:</p>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach(($editing->photo_gallery ?? [$editing->photo_path]) as $photo)
                                                @if($photo)
                                                    <div class="relative group/photo">
                                                        <img src="{{ Storage::url($photo) }}" alt="Foto lokasi" 
                                                             class="h-16 w-24 object-cover rounded-lg border border-[#e3d5c4]">
                                                        <div class="absolute inset-0 bg-black/40 rounded-lg opacity-0 group-hover/photo:opacity-100 transition-opacity flex items-center justify-center">
                                                            <i class="fa-solid fa-trash-can text-white text-xs"></i>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                        <label class="flex items-center gap-2 text-xs text-[#6f5134] mt-2">
                                            <input type="checkbox" name="remove_photos" value="1" 
                                                   class="rounded border-[#d7c5b2] text-[#b58042] focus:ring-[#b58042]">
                                            <span>Hapus semua foto yang ada</span>
                                        </label>
                                    </div>
                                @endif
                            </div>

                            {{-- Deskripsi --}}
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-[#6f5134]">
                                    <i class="fa-solid fa-file-lines mr-1 text-[#b58042]"></i>
                                    Deskripsi
                                </label>
                                <textarea name="description" rows="3" 
                                          class="w-full px-4 py-2.5 rounded-xl border border-[#d7c5b2] bg-white/50 backdrop-blur-sm text-[#4a301f] focus:border-[#b58042] focus:ring-[#b58042]"
                                          placeholder="Deskripsi singkat tentang cabang...">{{ old('description', $isEditing ? $editing->description : '') }}</textarea>
                            </div>

                            {{-- Status Aktif --}}
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1"
                                       @checked(old('is_active', $isEditing ? $editing->is_active : true))
                                       class="rounded border-[#d7c5b2] text-[#b58042] focus:ring-[#b58042]">
                                <span class="text-sm text-[#6f5134]">Cabang aktif dan dapat dipilih</span>
                            </div>

                            {{-- Submit Button --}}
                            <div class="pt-4">
                                <button class="w-full md:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-{{ $isEditing ? 'pen-to-square' : 'floppy-disk' }}"></i>
                                    {{ $isEditing ? 'Perbarui Cabang' : 'Simpan Cabang' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Daftar Cabang --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/10 to-[#8b5b2e]/10 rounded-2xl blur-xl"></div>
                    <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg">
                        <h3 class="text-xl font-light text-[#3f2b1b] mb-6 flex items-center gap-2">
                            <i class="fa-solid fa-list text-[#b58042]"></i>
                            Daftar Cabang
                        </h3>

                        <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                            @forelse($locations as $loc)
                                <div class="group/item border border-[#e3d5c4] rounded-xl p-4 bg-white/50 hover:bg-white/80 transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#b58042]/20 to-[#8b5b2e]/20 flex items-center justify-center">
                                                    <i class="fa-solid fa-store text-[#b58042] text-sm"></i>
                                                </div>
                                                <h4 class="font-display font-semibold text-[#3f2b1b]">{{ $loc->name }}</h4>
                                            </div>
                                            
                                            @if($loc->address)
                                                <p class="text-sm text-[#6f5134] flex items-start gap-1 mb-1">
                                                    <i class="fa-solid fa-location-dot text-[#b58042] text-xs mt-1"></i>
                                                    <span>{{ $loc->address }}</span>
                                                </p>
                                            @endif
                                            
                                            @if($loc->description)
                                                <p class="text-xs text-[#8b7359] mt-1">{{ $loc->description }}</p>
                                            @endif

                                            @if($loc->map_url)
                                                <a href="{{ $loc->map_url }}" target="_blank" 
                                                   class="inline-flex items-center gap-1 text-xs text-[#b58042] hover:text-[#8b5b2e] mt-2">
                                                    <i class="fa-solid fa-map"></i>
                                                    Lihat di Maps
                                                </a>
                                            @endif

                                            {{-- Foto Gallery --}}
                                            @php
                                                $photos = $loc->photo_gallery ?? ($loc->photo_path ? [$loc->photo_path] : []);
                                            @endphp
                                            @if(!empty($photos))
                                                <div class="grid grid-cols-3 gap-2 mt-3">
                                                    @foreach(array_slice($photos, 0, 3) as $p)
                                                        <img src="{{ Storage::url($p) }}" alt="Foto {{ $loc->name }}"
                                                             class="w-full h-16 object-cover rounded-lg border border-[#e3d5c4]">
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex flex-col items-end gap-2">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $loc->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                            <a href="{{ route('admin.locations.manage', ['edit' => $loc->id]) }}"
                                               class="text-xs text-[#b58042] hover:text-[#8b5b2e] transition-colors flex items-center gap-1">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8">
                                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-[#f0e4d6] flex items-center justify-center">
                                        <i class="fa-solid fa-store-slash text-2xl text-[#8b7359]"></i>
                                    </div>
                                    <p class="text-[#6f5134]">Belum ada cabang</p>
                                    <p class="text-xs text-[#8b7359] mt-1">Tambahkan cabang pertama Anda</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>