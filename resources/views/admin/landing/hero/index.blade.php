<div>
    <x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-image text-[#D4A017]"></i>
                    Pengelolaan Landing Page
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Kelola <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Slide Hero</span>
                </h2>
            </div>
            @if($slides->count() < 10)
                <a href="{{ route('manager.landing.hero.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Slide
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]" x-data="{ showDelete: false, deleteUrl: '', deleteTitle: '' }">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-2xl px-5 py-4">
                    <i class="fa-solid fa-circle-check text-green-500"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-5 py-4">
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Kuota info --}}
            <div class="flex items-center justify-between bg-white/80 border border-[#EDE0D0] rounded-2xl px-6 py-4">
                <p class="text-sm text-[#7A5B3A]">
                    <span class="font-semibold text-[#3F2B1B]">{{ $slides->count() }}</span> / 10 slide hero
                </p>
                <p class="text-xs text-[#8B7359]">Nomor urut ditentukan otomatis saat slide ditambahkan</p>
            </div>

            @forelse($slides as $slide)
                <div class="group bg-white border border-[#EDE0D0] rounded-3xl overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="grid sm:grid-cols-[260px_minmax(0,1fr)]">
                        {{-- Foto --}}
                        <div class="relative bg-[#F5EFE6] min-h-[180px]">
                            @if($slide->image_path)
                                <img src="{{ Storage::url($slide->image_path) }}"
                                     alt="{{ $slide->title }}"
                                     class="w-full h-full min-h-[180px] object-cover">
                            @else
                                <div class="w-full min-h-[180px] flex items-center justify-center">
                                    <i class="fa-solid fa-image text-4xl text-[#D4A017]/30"></i>
                                </div>
                            @endif
                            <div class="absolute top-3 left-3 w-9 h-9 rounded-xl bg-black/60 backdrop-blur-md flex items-center justify-center text-white font-bold text-sm">
                                {{ $slide->sort_order }}
                            </div>
                            <div class="absolute top-3 right-3">
                                @if($slide->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-green-500 text-white px-3 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-gray-500 text-white px-3 py-1 rounded-full">
                                        Nonaktif
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Info + Aksi --}}
                        <div class="p-6 flex flex-col justify-between gap-4">
                            <div class="space-y-2">
                                @if($slide->eyebrow)
                                    <p class="text-xs font-semibold tracking-widest text-[#D4A017] uppercase">{{ $slide->eyebrow }}</p>
                                @endif
                                <h3 class="font-display text-xl font-semibold text-[#3F2B1B]">{{ $slide->title }}</h3>
                                @if($slide->subtitle)
                                    <p class="text-sm text-[#7A5B3A] line-clamp-2">{{ $slide->subtitle }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 pt-4 border-t border-[#EDE0D0]">
                                <a href="{{ route('manager.landing.hero.edit', $slide) }}"
                                   class="flex-1 py-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold text-sm text-center hover:brightness-110 transition-all">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Slide
                                </a>
                                <button type="button"
                                        @click="showDelete=true; deleteUrl='{{ route('manager.landing.hero.destroy', $slide) }}'; deleteTitle=@js($slide->title)"
                                        class="px-5 py-3 rounded-2xl bg-white border-2 border-red-200 text-red-600 hover:bg-red-50 hover:border-red-400 transition-all font-semibold text-sm">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-24 border-2 border-dashed border-[#E1D3C5] rounded-3xl bg-white/50">
                    <i class="fa-solid fa-images text-6xl text-[#D4A017]/30 mb-5"></i>
                    <p class="text-[#8B7359] font-medium text-lg">Belum ada slide hero</p>
                    <p class="text-sm text-[#7A5B3A] mt-1 mb-6">Tambahkan slide pertama untuk halaman utama</p>
                    <a href="{{ route('manager.landing.hero.create') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold hover:brightness-110 transition-all">
                        <i class="fa-solid fa-plus"></i> Tambah Slide Pertama
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Delete Modal --}}
        <div x-show="showDelete" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div @click.outside="showDelete=false"
                 class="bg-white rounded-3xl shadow-2xl border border-[#EDE0D0] max-w-md w-full p-8"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-red-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-display text-xl font-bold text-[#3F2B1B]">Hapus Slide?</h3>
                        <p class="text-sm text-[#7A5B3A]">Foto dan data slide akan dihapus permanen.</p>
                    </div>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-6">
                    <p class="text-red-700 text-sm"><span class="font-semibold" x-text="deleteTitle"></span></p>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="showDelete=false"
                            class="px-6 py-3 rounded-2xl border border-[#E1D3C5] text-[#5C432C] hover:bg-[#FAF6F0] transition-all">
                        Batal
                    </button>
                    <form :action="deleteUrl" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-6 py-3 rounded-2xl bg-red-600 text-white font-semibold hover:bg-red-700 transition-all">
                            <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
</div>
