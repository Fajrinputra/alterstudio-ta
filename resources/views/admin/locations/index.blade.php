@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-store text-[#D4A017]"></i>
                    Cabang Studio
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Kelola <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Cabang</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
          
            @if (session('status'))
                <div class="flex items-center gap-3 p-5 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-sm">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif

            <div class="grid lg:grid-cols-2 gap-8">
              
                {{-- Form Tambah/Edit Cabang --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/10 via-[#E07A5F]/10 rounded-3xl blur-3xl"></div>
                    <div class="relative glass border border-[#EDE0D0] rounded-3xl p-9 shadow-2xl backdrop-blur-2xl">
                        @php $isEditing = isset($editing) && $editing; @endphp
                        
                        <div class="flex items-center gap-4 mb-8">
                            <div class="w-12 h-12 rounded-3xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center">
                                <i class="fa-solid fa-{{ $isEditing ? 'pen-to-square' : 'plus' }} text-[#D4A017] text-3xl"></i>
                            </div>
                            <h3 class="font-display text-3xl text-[#3F2B1B]">
                                {{ $isEditing ? 'Edit Cabang' : 'Tambah Cabang Baru' }}
                            </h3>
                        </div>

                        <form method="POST" 
                              action="{{ $isEditing ? url('/admin/locations/'.$editing->id) : url('/admin/locations') }}"
                              enctype="multipart/form-data" class="space-y-7">
                            @csrf
                            @if($isEditing) @method('PUT') @endif

                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Nama Cabang</label>
                                <input name="name" required value="{{ old('name', $isEditing ? $editing->name : '') }}"
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017]">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Alamat Lengkap</label>
                                <input name="address" value="{{ old('address', $isEditing ? $editing->address : '') }}"
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017]">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Link Google Maps</label>
                                <input name="map_url" type="url" value="{{ old('map_url', $isEditing ? $editing->map_url : '') }}"
                                       class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017]">
                            </div>

                            <div class="space-y-3">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Foto Lokasi</label>
                                <input type="file" name="photos[]" accept="image/*" multiple
                                       class="w-full text-sm file:mr-6 file:py-4 file:px-8 file:rounded-3xl file:border-0 file:bg-[#FAF6F0] file:text-[#3F2B1B]">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest">Deskripsi</label>
                                <textarea name="description" rows="3"
                                          class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B]">{{ old('description', $isEditing ? $editing->description : '') }}</textarea>
                            </div>

                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="is_active" value="1"
                                       @checked(old('is_active', $isEditing ? $editing->is_active : true))
                                       class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017]">
                                <span class="text-[#3F2B1B]">Cabang aktif dan dapat dipilih</span>
                            </div>

                            <button type="submit"
                                    class="w-full py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl transition-all">
                                {{ $isEditing ? 'Perbarui Cabang' : 'Simpan Cabang Baru' }}
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Daftar Cabang + Ruangan --}}
                <div class="relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 rounded-3xl blur-3xl"></div>
                    <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-8 shadow-2xl overflow-hidden">
                        <h3 class="font-display text-2xl text-[#3F2B1B] mb-6">Daftar Cabang</h3>
                        
                        <div class="space-y-8">
                            @forelse($locations as $loc)
                                <div class="bg-white border border-[#EDE0D0] rounded-3xl p-7">
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center">
                                                <i class="fa-solid fa-store text-[#D4A017]"></i>
                                            </div>
                                            <h4 class="font-display text-xl text-[#3F2B1B]">{{ $loc->name }}</h4>
                                        </div>
                                        <a href="{{ route('admin.locations.manage', ['edit' => $loc->id]) }}"
                                           class="text-sm text-[#D4A017] hover:text-[#E07A5F]">Edit Cabang</a>
                                    </div>

                                    @if($loc->address)
                                        <p class="text-sm text-[#7A5B3A] mb-6">{{ $loc->address }}</p>
                                    @endif

                                    <!-- Ruangan Studio -->
                                    <div>
                                        <p class="text-sm font-medium text-[#7A5B3A] mb-4">Ruangan Studio</p>
                                        <div class="space-y-4">
                                            @forelse($loc->rooms as $room)
                                                <div x-data="{ showDeleteRoom: false }" 
                                                     class="bg-[#FAF6F0] border border-[#EDE0D0] rounded-3xl p-5">
                                                    <form method="POST" action="{{ route('admin.locations.room.update', $room) }}" 
                                                          class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto_auto] gap-3 items-center">
                                                        @csrf
                                                        @method('PUT')
                                                        
                                                        <input type="text" name="name" required value="{{ $room->name }}"
                                                               class="min-w-0 w-full px-5 py-3 rounded-full border border-[#E1D3C5] bg-white text-sm">

                                                        <input type="text" name="description" value="{{ $room->description ?? '' }}"
                                                               class="min-w-0 w-full px-5 py-3 rounded-full border border-[#E1D3C5] bg-white text-sm"
                                                               placeholder="Kapasitas / Deskripsi">

                                                        <label class="flex items-center justify-start gap-2 whitespace-nowrap text-sm px-3 md:col-span-1 2xl:justify-center">
                                                            <input type="checkbox" name="is_active" value="1" @checked($room->is_active)
                                                                   class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017]">
                                                            <span>Aktif</span>
                                                        </label>

                                                        <div class="flex flex-col sm:flex-row gap-2 md:col-span-2 2xl:col-span-1 2xl:justify-end">
                                                            <button type="submit"
                                                                    class="w-full sm:w-auto px-7 py-3 rounded-full bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white text-sm font-semibold hover:brightness-110 transition-all whitespace-nowrap">
                                                                Simpan
                                                            </button>
                                                            <button type="button" @click="showDeleteRoom = true"
                                                                    class="w-full sm:w-auto px-7 py-3 rounded-full border-2 border-red-400 text-red-600 hover:bg-red-50 transition-all text-sm font-semibold whitespace-nowrap">
                                                                Hapus
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @empty
                                                <p class="text-xs text-[#8B7359]">Belum ada ruangan di cabang ini.</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    <!-- Form Tambah Ruangan -->
                                    <form method="POST" action="{{ route('admin.locations.room.store') }}" 
                                          class="mt-8 bg-white border border-[#EDE0D0] rounded-3xl p-6 grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] gap-4 items-end">
                                        @csrf
                                        <input type="hidden" name="studio_location_id" value="{{ $loc->id }}">
                                        <div class="min-w-0">
                                            <input type="text" name="name" required
                                                   class="w-full px-5 py-3 rounded-full border border-[#E1D3C5] bg-white text-sm"
                                                   placeholder="Nama ruangan">
                                        </div>
                                        <div class="min-w-0">
                                            <input type="text" name="description"
                                                   class="w-full px-5 py-3 rounded-full border border-[#E1D3C5] bg-white text-sm"
                                                   placeholder="Deskripsi / Kapasitas">
                                        </div>
                                        <div class="md:col-span-2 2xl:col-span-1 2xl:min-w-[180px]">
                                            <button type="submit"
                                                    class="w-full px-6 py-3 rounded-full bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold hover:brightness-110 transition-all whitespace-nowrap">
                                                + Tambah Ruang
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @empty
                                <div class="text-center py-20 bg-white/70 border border-[#EDE0D0] rounded-3xl">
                                    <i class="fa-solid fa-store-slash text-6xl text-[#D4A017]/30"></i>
                                    <p class="mt-4 text-[#3F2B1B]">Belum ada cabang studio</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
