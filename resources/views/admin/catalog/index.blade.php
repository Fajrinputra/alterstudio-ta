<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-[#b58042]"></i>
                    Katalog Layanan
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Daftar <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">Kategori</span>
                </h2>
            </div>
            <a href="{{ route('admin.catalog.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <i class="fa-solid fa-plus"></i>
                Tambah Katalog
            </a>
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

            {{-- Tabel Kategori --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/10 to-[#8b5b2e]/10 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl shadow-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-[#faf3eb] to-white border-b border-[#e3d5c4]">
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Kategori</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Deskripsi</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Jumlah Paket</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e4d6]">
                                @forelse($categories as $cat)
                                    <tr class="hover:bg-white/80 transition-colors" x-data="{ confirmDelete: false }">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#b58042]/20 to-[#8b5b2e]/20 flex items-center justify-center">
                                                    <i class="fa-solid fa-folder text-[#b58042] text-sm"></i>
                                                </div>
                                                <span class="font-medium text-[#3f2b1b]">{{ $cat->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-[#6f5134] max-w-xs truncate">{{ $cat->description }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-[#f1e5d8] text-[#5b422b]">
                                                {{ $cat->packages_count }} Paket
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('admin.catalog.packages', $cat) }}" 
                                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-[#d7c5b2] text-xs text-[#5b422b] hover:bg-white/80 transition-colors">
                                                    <i class="fa-solid fa-eye"></i>
                                                    Lihat Paket
                                                </a>
                                                
                                                <button type="button"
                                                        @click="confirmDelete = true"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-red-200 text-xs text-red-600 hover:bg-red-50 transition-colors">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                    Hapus
                                                </button>
                                            </div>

                                            <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50" x-transition.opacity>
                                                <div class="absolute inset-0 bg-black/45" @click="confirmDelete = false"></div>
                                                <div class="relative h-full w-full flex items-center justify-center p-4">
                                                    <div class="w-full max-w-md rounded-2xl bg-white border border-[#e3d5c4] shadow-2xl p-5 text-left" @click.stop>
                                                        <div class="flex items-start gap-3">
                                                            <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center">
                                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                            </div>
                                                            <div>
                                                                <h4 class="text-lg font-semibold text-[#3f2b1b]">Hapus Kategori?</h4>
                                                                <p class="text-sm text-[#6f5134] mt-1">
                                                                    Kategori <span class="font-medium">{{ $cat->name }}</span> hanya bisa dihapus jika tidak memiliki paket.
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="mt-5 flex justify-end gap-2">
                                                            <button type="button"
                                                                    @click="confirmDelete = false"
                                                                    class="px-4 py-2 rounded-lg border border-[#d7c5b2] text-[#5b422b] hover:bg-[#f7efe5] transition-colors">
                                                                Batal
                                                            </button>
                                                            <form method="POST" action="/admin/categories/{{ $cat->id }}">
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
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center">
                                                <div class="w-16 h-16 mb-3 rounded-full bg-[#f0e4d6] flex items-center justify-center">
                                                    <i class="fa-solid fa-folder-open text-2xl text-[#8b7359]"></i>
                                                </div>
                                                <p class="text-[#6f5134] font-medium">Belum ada kategori</p>
                                                <p class="text-sm text-[#8b7359] mt-1">Klik "Tambah Katalog" untuk membuat kategori baru</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
