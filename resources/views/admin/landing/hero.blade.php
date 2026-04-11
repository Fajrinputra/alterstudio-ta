<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-image text-[#D4A017]"></i>
                    Landing Page Management
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Kelola <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Hero Slider</span>
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]" x-data="{ showDelete: false, deleteUrl: '', deleteTitle: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            @if(session('success'))
                <div class="p-5 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-xl"></i>
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-8">
                {{-- Form Tambah Slide Baru - Modern Card --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <div class="bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-8 shadow-xl">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center">
                                    <i class="fa-solid fa-plus text-[#D4A017] text-xl"></i>
                                </div>
                                <h3 class="font-display text-2xl font-semibold text-[#3F2B1B]">Tambah Slide Baru</h3>
                            </div>
                            
                            <form method="POST" action="{{ route('admin.landing.hero.store') }}" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                <div>
                                    <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">JUDUL KECIL (EYEBROW)</label>
                                    <input type="text" name="eyebrow" value="{{ old('eyebrow') }}"
                                           class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B]"
                                           placeholder="CASA DE ALTER & SIGNATURE">
                                </div>
                                
                                <div>
                                    <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">JUDUL BESAR</label>
                                    <input type="text" name="title" value="{{ old('title') }}" required
                                           class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B]"
                                           placeholder="Abadikan Momen Berharga Anda">
                                </div>
                                
                                <div>
                                    <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">SUBJUDUL</label>
                                    <textarea name="subtitle" rows="3"
                                              class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B] resize-y"
                                              placeholder="Sentuhan profesional dari booking hingga hasil akhir...">{{ old('subtitle') }}</textarea>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">URUTAN</label>
                                        <input type="number" name="sort_order" min="1" value="{{ old('sort_order', 1) }}" required
                                               class="w-full px-5 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all text-[#3F2B1B]">
                                    </div>
                                    <div class="flex items-end">
                                        <label class="inline-flex items-center gap-3 text-sm text-[#7A5B3A]">
                                            <input type="checkbox" name="is_active" value="1" checked 
                                                   class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017] focus:ring-[#D4A017]">
                                            <span class="font-medium">Aktif di Slider</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">FOTO BACKGROUND HERO</label>
                                    <div class="mt-1 border-2 border-dashed border-[#E1D3C5] rounded-3xl p-8 text-center hover:border-[#D4A017] transition-colors">
                                        <input type="file" name="image" accept="image/*" required
                                               class="hidden" id="hero-image">
                                        <label for="hero-image" class="cursor-pointer block">
                                            <i class="fa-solid fa-cloud-arrow-up text-4xl text-[#D4A017] mb-3"></i>
                                            <p class="text-sm font-medium text-[#3F2B1B]">Klik untuk upload foto</p>
                                            <p class="text-xs text-[#8B7359] mt-1">Rasio 16:9 • Minimal 1600×900px</p>
                                        </label>
                                    </div>
                                </div>
                                
                                <button type="submit"
                                        class="w-full h-14 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl shadow-[#D4A017]/30 hover:shadow-2xl hover:-translate-y-0.5 active:scale-[0.98] transition-all flex items-center justify-center gap-3 text-base">
                                    <i class="fa-solid fa-plus"></i>
                                    Simpan Slide Baru
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Daftar Slide Hero --}}
                <div class="lg:col-span-2">
                    <div class="bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl p-8 shadow-xl">
                        <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] mb-6 flex items-center gap-3">
                            <i class="fa-solid fa-list"></i>
                            Daftar Slide Hero
                        </h3>
                        
                        <div class="space-y-6">
                            @forelse($slides as $slide)
                                <div class="group bg-white border border-[#EDE0D0] rounded-3xl overflow-hidden hover:shadow-xl transition-all duration-300">
                                    <div class="grid md:grid-cols-[200px_1fr] gap-6 p-6">
                                        <!-- Preview Image -->
                                        <div class="relative">
                                            <img src="{{ Storage::url($slide->image_path) }}" alt="{{ $slide->title }}"
                                                 class="w-full h-52 object-cover rounded-3xl border border-[#EDE0D0] shadow-inner">
                                            @if($slide->is_active)
                                                <div class="absolute top-4 left-4 px-4 py-1 bg-emerald-500 text-white text-xs font-semibold rounded-3xl shadow">AKTIF</div>
                                            @endif
                                        </div>
                                        
                                        <!-- Form Edit -->
                                        <div class="space-y-5">
                                            <form method="POST" action="{{ route('admin.landing.hero.update', $slide) }}" enctype="multipart/form-data" class="space-y-5">
                                                @csrf
                                                @method('PUT')
                                                
                                                <div class="grid md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">EYEBROW</label>
                                                        <input type="text" name="eyebrow" value="{{ $slide->eyebrow }}"
                                                               class="w-full px-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                                               placeholder="Judul kecil">
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">JUDUL BESAR</label>
                                                        <input type="text" name="title" value="{{ $slide->title }}" required
                                                               class="w-full px-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all"
                                                               placeholder="Judul besar">
                                                    </div>
                                                </div>
                                                
                                                <div>
                                                    <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">SUBJUDUL</label>
                                                    <textarea name="subtitle" rows="2"
                                                              class="w-full px-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all resize-y"
                                                              placeholder="Deskripsi singkat...">{{ $slide->subtitle }}</textarea>
                                                </div>
                                                
                                                <div class="grid md:grid-cols-3 gap-4 items-end">
                                                    <div>
                                                        <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">URUTAN</label>
                                                        <input type="number" name="sort_order" min="1" value="{{ $slide->sort_order }}" required
                                                               class="w-full px-5 py-3.5 rounded-3xl border border-[#E1D3C5] bg-white focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                                    </div>
                                                    
                                                    <label class="inline-flex items-center gap-3 text-sm text-[#7A5B3A] md:col-span-1">
                                                        <input type="checkbox" name="is_active" value="1" {{ $slide->is_active ? 'checked' : '' }}
                                                               class="w-5 h-5 rounded-xl border-[#E1D3C5] text-[#D4A017]">
                                                        <span class="font-medium">Tampilkan di Hero</span>
                                                    </label>
                                                    
                                                    <div>
                                                        <label class="text-xs font-medium text-[#7A5B3A] tracking-widest block mb-2">GANTI FOTO</label>
                                                        <input type="file" name="image" accept="image/*"
                                                               class="block w-full text-sm text-[#6f5134] file:mr-4 file:px-6 file:py-3 file:rounded-3xl file:border-0 file:bg-[#FAF6F0] file:text-[#3F2B1B] file:font-medium">
                                                    </div>
                                                </div>
                                                
                                                <div class="flex gap-3 pt-2">
                                                    <button type="submit"
                                                            class="flex-1 py-3.5 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold hover:brightness-110 transition-all">
                                                        Update Slide
                                                    </button>
                                                    
                                                    <button type="button"
                                                            @click="showDelete = true; deleteUrl='{{ route('admin.landing.hero.destroy', $slide) }}'; deleteTitle=@js($slide->title)"
                                                            class="px-8 py-3.5 rounded-3xl bg-white border-2 border-red-300 text-red-600 hover:bg-red-50 hover:border-red-400 transition-all font-semibold">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-20 border-2 border-dashed border-[#E1D3C5] rounded-3xl">
                                    <i class="fa-solid fa-images text-6xl text-[#D4A017]/30 mb-4"></i>
                                    <p class="text-[#8B7359] font-medium">Belum ada slide hero</p>
                                    <p class="text-sm text-[#7A5B3A] mt-1">Tambahkan slide pertama di panel kiri</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delete Confirmation Modal - Premium Look --}}
        <div x-show="showDelete"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.outside="showDelete = false"
                 class="bg-white rounded-3xl shadow-2xl border border-[#EDE0D0] max-w-md w-full overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation text-red-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-display text-2xl font-bold text-[#3F2B1B]">Hapus Slide?</h3>
                            <p class="text-[#7A5B3A]">Tindakan ini tidak dapat dibatalkan.</p>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 border border-red-200 rounded-3xl p-5 mb-8">
                        <p class="text-red-700 text-sm flex items-start gap-3">
                            <i class="fa-solid fa-circle-exclamation mt-1"></i>
                            <span>Anda akan menghapus slide: <span class="font-semibold" x-text="deleteTitle"></span></span>
                        </p>
                    </div>
                    
                    <div class="flex justify-end gap-4">
                        <button @click="showDelete = false"
                                class="px-8 py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] font-medium hover:bg-[#FAF6F0] transition-all">
                            Batal
                        </button>
                        <form :action="deleteUrl" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold hover:brightness-110 transition-all shadow-lg">
                                <i class="fa-solid fa-trash-can"></i>
                                Ya, Hapus Slide
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>