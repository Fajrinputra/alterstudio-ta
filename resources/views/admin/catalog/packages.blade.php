@php use Illuminate\Support\Str; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-box text-[#b58042]"></i>
                    Katalog Layanan
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Paket: <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">{{ $category->name }}</span>
                </h2>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.catalog') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
                <a href="{{ route('admin.catalog.packages.create', $category) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Paket
                </a>
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
            @if (session('error'))
                <div class="flex items-center gap-3 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
                    <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Grid Paket --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($packages as $pkg)
                    <div class="relative group" x-data="{ confirmDelete: false }">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/10 to-[#8b5b2e]/10 rounded-2xl blur-xl"></div>
                        <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all">
                            
                            {{-- Header dengan status --}}
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#b58042]/20 to-[#8b5b2e]/20 flex items-center justify-center">
                                        <i class="fa-solid fa-cube text-[#b58042] text-sm"></i>
                                    </div>
                                    <h3 class="font-display text-lg text-[#3f2b1b]">{{ $pkg->name }}</h3>
                                </div>
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $pkg->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            {{-- Harga --}}
                            <p class="text-2xl font-light text-[#b58042] mb-3">
                                Rp {{ number_format($pkg->price) }}
                            </p>

                            {{-- Deskripsi singkat --}}
                            @if($pkg->description)
                                <p class="text-sm text-[#6f5134] mb-3 line-clamp-2">{{ $pkg->description }}</p>
                            @endif

                            {{-- Features preview --}}
                            @if($pkg->features && count($pkg->features) > 0)
                                <div class="space-y-1 mb-3">
                                    @foreach(array_slice($pkg->features, 0, 3) as $feature)
                                        <p class="text-xs text-[#5b422b] flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check text-[#b58042] text-[10px]"></i>
                                            {{ Str::limit($feature, 40) }}
                                        </p>
                                    @endforeach
                                    @if(count($pkg->features) > 3)
                                        <p class="text-xs text-[#b58042]">+{{ count($pkg->features) - 3 }} fitur lainnya</p>
                                    @endif
                                </div>
                            @endif

                            {{-- Max People --}}
                            @if($pkg->max_people)
                                <p class="text-xs text-[#8b7359] flex items-center gap-1 mb-3">
                                    <i class="fa-solid fa-user"></i>
                                    Max {{ $pkg->max_people }} orang
                                </p>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-2 pt-3 border-t border-[#e3d5c4]">
                                <a href="{{ route('admin.packages.show', $pkg) }}" 
                                   class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg border border-[#d7c5b2] text-xs text-[#5b422b] hover:bg-white/80 transition-colors">
                                    <i class="fa-solid fa-eye"></i>
                                    Detail
                                </a>
                                <a href="{{ route('admin.packages.edit', $pkg) }}" 
                                   class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg border border-[#d7c5b2] text-xs text-[#5b422b] hover:bg-white/80 transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Edit
                                </a>
                                <button type="button"
                                        @click="confirmDelete = true"
                                        class="flex-1 w-full inline-flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg border border-red-200 text-xs text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="fa-solid fa-trash-can"></i>
                                    Hapus
                                </button>
                            </div>

                            <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50" x-transition.opacity>
                                <div class="absolute inset-0 bg-black/45" @click="confirmDelete = false"></div>
                                <div class="relative h-full w-full flex items-center justify-center p-4">
                                    <div class="w-full max-w-md rounded-2xl bg-white border border-[#e3d5c4] shadow-2xl p-5" @click.stop>
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                            </div>
                                            <div>
                                                <h4 class="text-lg font-semibold text-[#3f2b1b]">Hapus Paket?</h4>
                                                <p class="text-sm text-[#6f5134] mt-1">
                                                    Paket <span class="font-medium">{{ $pkg->name }}</span> akan dihapus permanen. Lanjutkan?
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mt-5 flex justify-end gap-2">
                                            <button type="button"
                                                    @click="confirmDelete = false"
                                                    class="px-4 py-2 rounded-lg border border-[#d7c5b2] text-[#5b422b] hover:bg-[#f7efe5] transition-colors">
                                                Batal
                                            </button>
                                            <form method="POST" action="/admin/packages/{{ $pkg->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
                                                    <i class="fa-solid fa-trash-can mr-1"></i>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="relative group">
                            <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/10 to-[#8b5b2e]/10 rounded-2xl blur-xl"></div>
                            <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl p-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 mb-3 rounded-full bg-[#f0e4d6] flex items-center justify-center">
                                        <i class="fa-solid fa-box-open text-2xl text-[#8b7359]"></i>
                                    </div>
                                    <p class="text-[#6f5134] font-medium">Belum ada paket</p>
                                    <p class="text-sm text-[#8b7359] mt-1">Klik "Tambah Paket" untuk membuat paket baru</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
