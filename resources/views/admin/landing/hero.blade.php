<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide">Landing Page</p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Kelola <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">Hero Slider</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 bg-white/80 border border-[#e3d5c4] rounded-2xl p-5 shadow-lg">
                    <h3 class="text-lg font-semibold text-[#3f2b1b] mb-4">Tambah Slide Baru</h3>
                    <form method="POST" action="{{ route('admin.landing.hero.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm text-[#6f5134]">Judul Kecil</label>
                            <input type="text" name="eyebrow" value="{{ old('eyebrow') }}"
                                   class="w-full mt-1 px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white text-[#4a301f]">
                        </div>
                        <div>
                            <label class="text-sm text-[#6f5134]">Judul Besar</label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                   class="w-full mt-1 px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white text-[#4a301f]">
                        </div>
                        <div>
                            <label class="text-sm text-[#6f5134]">Subjudul</label>
                            <textarea name="subtitle" rows="3"
                                      class="w-full mt-1 px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white text-[#4a301f]">{{ old('subtitle') }}</textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-sm text-[#6f5134]">Urutan</label>
                                <input type="number" name="sort_order" min="1" value="{{ old('sort_order', 1) }}" required
                                       class="w-full mt-1 px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white text-[#4a301f]">
                            </div>
                            <div class="flex items-end">
                                <label class="inline-flex items-center gap-2 text-sm text-[#6f5134]">
                                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-[#d7c5b2] text-[#b58042]">
                                    Aktif
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm text-[#6f5134]">Foto Background</label>
                            <input type="file" name="image" accept="image/*" required
                                   class="w-full mt-1 text-sm text-[#6f5134] file:mr-3 file:px-3 file:py-2 file:rounded-lg file:border-0 file:bg-[#b58042] file:text-white">
                            <p class="text-xs text-[#8b7359] mt-1">Gunakan rasio 16:9 (minimal 1600x900) agar semua slide konsisten.</p>
                        </div>
                        <button class="w-full px-4 py-2.5 rounded-xl bg-[#b58042] text-white font-semibold hover:bg-[#9b6a34] transition-colors">
                            Simpan Slide
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2 bg-white/80 border border-[#e3d5c4] rounded-2xl p-5 shadow-lg">
                    <h3 class="text-lg font-semibold text-[#3f2b1b] mb-4">Daftar Slide Hero</h3>

                    <div class="space-y-4">
                        @forelse($slides as $slide)
                            <div class="border border-[#e3d5c4] rounded-xl p-4">
                                <div class="grid md:grid-cols-[160px_1fr] gap-4">
                                    <img src="{{ Storage::url($slide->image_path) }}" alt="{{ $slide->title }}"
                                         class="w-full h-32 object-cover rounded-lg border border-[#e3d5c4]">

                                    <div>
                                        <form method="POST" action="{{ route('admin.landing.hero.update', $slide) }}" enctype="multipart/form-data" class="space-y-3">
                                            @csrf
                                            @method('PUT')
                                            <div class="grid md:grid-cols-2 gap-3">
                                                <input type="text" name="eyebrow" value="{{ $slide->eyebrow }}"
                                                       class="px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white text-[#4a301f]"
                                                       placeholder="Judul kecil">
                                                <input type="text" name="title" value="{{ $slide->title }}" required
                                                       class="px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white text-[#4a301f]"
                                                       placeholder="Judul besar">
                                            </div>
                                            <textarea name="subtitle" rows="2"
                                                      class="w-full px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white text-[#4a301f]"
                                                      placeholder="Subjudul">{{ $slide->subtitle }}</textarea>
                                            <div class="grid md:grid-cols-3 gap-3 items-center">
                                                <input type="number" name="sort_order" min="1" value="{{ $slide->sort_order }}" required
                                                       class="px-3 py-2 rounded-lg border border-[#d7c5b2] bg-white text-[#4a301f]">
                                                <label class="inline-flex items-center gap-2 text-sm text-[#6f5134]">
                                                    <input type="checkbox" name="is_active" value="1" {{ $slide->is_active ? 'checked' : '' }}
                                                           class="rounded border-[#d7c5b2] text-[#b58042]">
                                                    Aktif
                                                </label>
                                                <input type="file" name="image" accept="image/*"
                                                       class="text-xs text-[#6f5134] file:mr-2 file:px-2 file:py-1.5 file:rounded file:border-0 file:bg-[#f0e4d6] file:text-[#6f5134]">
                                                <p class="text-xs text-[#8b7359]">16:9, minimal 1600x900</p>
                                            </div>
                                            <button class="px-4 py-2 rounded-lg bg-[#b58042] text-white text-sm font-semibold hover:bg-[#9b6a34]">
                                                Update
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.landing.hero.destroy', $slide) }}" class="mt-2" onsubmit="return confirm('Hapus slide ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-4 py-2 rounded-lg bg-red-500 text-white text-sm font-semibold hover:bg-red-600">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10 border border-dashed border-[#d7c5b2] rounded-xl text-[#8b7359]">
                                Belum ada slide hero. Tambahkan slide pertama.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
