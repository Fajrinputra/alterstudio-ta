<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] flex items-center gap-2">
                    <i class="fa-solid fa-store text-[#D4A017]"></i>
                    Katalog Layanan
                </p>
                <h2 class="font-display font-bold text-4xl tracking-tighter text-[#3F2B1B]">
                    Kategori & Paket Foto
                </h2>
            </div>
            @if(auth()->check() && (auth()->user()->role === \App\Enums\Role::ADMIN || auth()->user()->role === \App\Enums\Role::MANAGER))
                <a href="{{ route('admin.catalog.create') }}" 
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Kategori
                </a>
            @endif
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 space-y-12">
        @forelse($categories as $category)
            <div class="bg-white rounded-3xl border border-[#EDE0D0] shadow-xl overflow-hidden"
                 x-data="{ editCategory: {{ (string) ((string) old('category_id') === (string) $category->id ? 'true' : 'false') }}, confirmDeleteCategory: false }">
                
                <!-- Category Header -->
                <div class="px-8 py-7 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                    <div class="flex items-start justify-between gap-6">
                        <div class="flex items-start gap-5">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10 flex items-center justify-center flex-shrink-0">
                                <i class="fa-solid fa-layer-group text-[#D4A017] text-3xl"></i>
                            </div>
                            <div>
                                <h3 class="font-display text-3xl font-semibold text-[#3F2B1B]">{{ $category->name }}</h3>
                                @if($category->description)
                                    <p class="text-[#7A5B3A] mt-2 leading-relaxed">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                        
                        @if(auth()->check() && (auth()->user()->role === \App\Enums\Role::ADMIN || auth()->user()->role === \App\Enums\Role::MANAGER))
                            <div class="flex gap-3">
                                <button type="button"
                                        @click="editCategory = true"
                                        class="px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all flex items-center gap-2">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Edit Kategori
                                </button>
                                <a href="{{ route('admin.catalog.packages', $category) }}" 
                                   class="px-6 py-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold flex items-center gap-2">
                                    <i class="fa-solid fa-box"></i>
                                    Kelola Paket
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Packages Grid -->
                <div class="p-8">
                    @if($category->packages->count() > 0)
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($category->packages as $pkg)
                                <div class="group relative bg-white border border-[#EDE0D0] rounded-3xl overflow-hidden hover:shadow-2xl hover:-translate-y-2 transition-all duration-300">
                                    
                                    <!-- Popular Badge -->
                                    @if($pkg->is_popular ?? false)
                                        <div class="absolute -top-3 -right-3 bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white text-xs px-5 py-1.5 rounded-3xl shadow-lg z-10 flex items-center gap-1">
                                            <i class="fa-solid fa-star"></i>
                                            Populer
                                        </div>
                                    @endif

                                    <!-- Image -->
                                    @if($pkg->overview_image)
                                        <div class="h-52 w-full overflow-hidden">
                                            <img src="{{ Storage::url($pkg->overview_image) }}" 
                                                 alt="{{ $pkg->name }}" 
                                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        </div>
                                    @else
                                        <div class="h-52 w-full bg-gradient-to-br from-[#FAF6F0] to-[#F4EDE4] flex items-center justify-center">
                                            <i class="fa-solid fa-image text-[#8B7359] text-5xl"></i>
                                        </div>
                                    @endif

                                    <div class="p-6">
                                        <h4 class="font-display font-semibold text-xl text-[#3F2B1B] line-clamp-2 mb-2">{{ $pkg->name }}</h4>
                                        <p class="text-3xl font-bold text-[#D4A017] mb-4">Rp {{ number_format($pkg->price) }}</p>
                                        
                                        <p class="text-sm text-[#7A5B3A] line-clamp-3 mb-6">{{ $pkg->description }}</p>

                                        <!-- Quick Features -->
                                        @if($pkg->features && count($pkg->features) > 0)
                                            <div class="space-y-2 mb-6">
                                                @foreach(array_slice($pkg->features, 0, 3) as $feature)
                                                    <div class="flex items-start gap-2 text-xs text-[#5C432C]">
                                                        <i class="fa-solid fa-circle-check text-[#D4A017] mt-0.5"></i>
                                                        <span>{{ Str::limit($feature, 35) }}</span>
                                                    </div>
                                                @endforeach
                                                @if(count($pkg->features) > 3)
                                                    <p class="text-xs text-[#D4A017]">+{{ count($pkg->features) - 3 }} fitur lainnya</p>
                                                @endif
                                            </div>
                                        @endif

                                        <!-- Action -->
                                        <div class="flex items-center justify-between pt-4 border-t border-[#EDE0D0]">
                                            <a href="{{ route('catalog.package.show', $pkg) }}" 
                                               class="text-[#D4A017] hover:text-[#E07A5F] font-medium flex items-center gap-1 text-sm">
                                                Lihat Detail
                                            </a>
                                            
                                            @if(auth()->check() && auth()->user()->role === \App\Enums\Role::CLIENT)
                                                <a href="{{ route('bookings.create', ['package_id' => $pkg->id]) }}" 
                                                   class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white text-sm font-semibold hover:brightness-110 transition-all">
                                                    Pesan
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16">
                            <i class="fa-solid fa-box-open text-6xl text-[#8B7359] mb-6 opacity-40"></i>
                            <p class="text-[#7A5B3A]">Belum ada paket dalam kategori ini.</p>
                            @if(auth()->check() && (auth()->user()->role === \App\Enums\Role::ADMIN || auth()->user()->role === \App\Enums\Role::MANAGER))
                                <a href="{{ route('admin.catalog.packages', $category) }}" 
                                   class="inline-block mt-6 px-8 py-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-medium">
                                    Tambah Paket Baru
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                @if(auth()->check() && (auth()->user()->role === \App\Enums\Role::ADMIN || auth()->user()->role === \App\Enums\Role::MANAGER))
                    <div x-show="editCategory" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" x-transition.opacity>
                        <div class="w-full max-w-xl rounded-3xl bg-white p-8 shadow-2xl" @click.stop>
                            <div class="flex items-start justify-between gap-4 mb-6">
                                <div>
                                    <p class="text-sm text-[#8B7359] flex items-center gap-2">
                                        <i class="fa-solid fa-pen-to-square text-[#D4A017]"></i>
                                        Edit Kategori
                                    </p>
                                    <h4 class="text-2xl font-display font-bold text-[#3F2B1B] mt-1">{{ $category->name }}</h4>
                                </div>
                                <button type="button"
                                        @click="editCategory = false"
                                        class="w-10 h-10 rounded-2xl border border-[#E1D3C5] text-[#5C432C] hover:bg-[#FAF6F0] transition-all">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <form method="POST" action="{{ url('/admin/categories/'.$category->id) }}" class="space-y-5">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="category_id" value="{{ $category->id }}">

                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-[#6F5134]">Nama Kategori</label>
                                    <input name="name"
                                           value="{{ old('name', $category->name) }}"
                                           required
                                           class="w-full px-4 py-3 rounded-2xl border border-[#D7C5B2] bg-[#FDF8F2] text-[#4A301F] focus:border-[#D4A017] focus:ring-[#D4A017]">
                                    @if((string) old('category_id') === (string) $category->id)
                                        @error('name')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-[#6F5134]">Deskripsi</label>
                                    <textarea name="description"
                                              rows="4"
                                              class="w-full px-4 py-3 rounded-2xl border border-[#D7C5B2] bg-[#FDF8F2] text-[#4A301F] focus:border-[#D4A017] focus:ring-[#D4A017]">{{ old('description', $category->description) }}</textarea>
                                    @if((string) old('category_id') === (string) $category->id)
                                        @error('description')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </div>

                                <div class="flex items-center justify-between gap-3 pt-4 border-t border-[#EDE0D0]">
                                    <button type="button"
                                            @click="confirmDeleteCategory = true"
                                            class="px-6 py-3 rounded-3xl border border-red-200 text-red-600 hover:bg-red-50 transition-all">
                                        <i class="fa-solid fa-trash-can mr-2"></i>
                                        Hapus Kategori
                                    </button>

                                    <div class="flex items-center gap-3">
                                    <button type="button"
                                            @click="editCategory = false"
                                            class="px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all">
                                        Batal
                                    </button>
                                    <button type="submit"
                                            class="px-6 py-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg hover:shadow-xl transition-all">
                                        <i class="fa-solid fa-floppy-disk mr-2"></i>
                                        Simpan Perubahan
                                    </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div x-show="confirmDeleteCategory" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60" x-transition.opacity>
                        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8" @click.stop>
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-xl font-semibold text-[#3F2B1B]">Hapus Kategori?</h4>
                                    <p class="text-[#7A5B3A] mt-2">
                                        Kategori <span class="font-medium">"{{ $category->name }}"</span> hanya bisa dihapus jika tidak memiliki paket.
                                    </p>
                                </div>
                            </div>
                            <div class="mt-8 flex justify-end gap-3">
                                <button type="button"
                                        @click="confirmDeleteCategory = false"
                                        class="px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all">
                                    Batal
                                </button>
                                <form method="POST" action="{{ url('/admin/categories/'.$category->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-6 py-3 rounded-3xl bg-red-600 text-white hover:bg-red-700 transition-all">
                                        <i class="fa-solid fa-trash-can mr-2"></i>
                                        Hapus Kategori
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-20 bg-white rounded-3xl border border-[#EDE0D0] shadow-xl">
                <i class="fa-solid fa-store-slash text-6xl text-[#8B7359] mb-6 opacity-40"></i>
                <p class="text-[#3F2B1B] text-xl mb-2">Belum ada kategori layanan</p>
                <p class="text-[#7A5B3A] mb-8">Silakan tambahkan kategori terlebih dahulu</p>
                @if(auth()->check() && (auth()->user()->role === \App\Enums\Role::ADMIN || auth()->user()->role === \App\Enums\Role::MANAGER))
                    <a href="{{ route('admin.catalog.create') }}" 
                       class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold">
                        <i class="fa-solid fa-plus"></i>
                        Buat Kategori Baru
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</x-app-layout>
