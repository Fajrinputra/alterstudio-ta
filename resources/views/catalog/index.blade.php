<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#7a5b3a] flex items-center gap-2">
                    <i class="fa-solid fa-store text-[#b58042]"></i>
                    Katalog Layanan
                </p>
                <h2 class="font-display font-bold text-3xl text-[#3f2b1b]">Kategori & Paket</h2>
            </div>
            @if(auth()->user()->role === \App\Enums\Role::ADMIN || auth()->user()->role === \App\Enums\Role::MANAGER)
                <a href="{{ route('admin.catalog.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#b58042] text-white hover:bg-[#9b6a34] transition-all shadow-md">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Kategori
                </a>
            @endif
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        @forelse($categories as $category)
            <div class="bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all">
                {{-- Category Header --}}
                <div class="px-6 py-5 border-b border-[#e3d5c4] bg-gradient-to-r from-[#faf3eb] to-white">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#b58042]/20 to-[#8b5b2e]/20 flex items-center justify-center">
                                <i class="fa-solid fa-layer-group text-[#b58042] text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-display text-2xl text-[#3f2b1b]">{{ $category->name }}</h3>
                                @if($category->description)
                                    <p class="text-sm text-[#6f5134] mt-1">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                        @if(auth()->user()->role === \App\Enums\Role::ADMIN || auth()->user()->role === \App\Enums\Role::MANAGER)
                            <div class="flex gap-2">
                                <a href="{{ route('admin.catalog.edit', $category) }}" class="px-3 py-2 rounded-lg text-sm bg-[#f0e4d6] text-[#6c4f32] hover:bg-[#e3d5c4] transition-colors">
                                    <i class="fa-solid fa-pen-to-square mr-1"></i>
                                    Edit
                                </a>
                                <a href="{{ route('admin.catalog.packages', $category) }}" class="px-3 py-2 rounded-lg bg-[#b58042] text-white hover:bg-[#9b6a34] transition-colors">
                                    <i class="fa-solid fa-box mr-1"></i>
                                    Kelola Paket
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Packages Grid --}}
                <div class="p-6">
                    @if($category->packages->count() > 0)
                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                            @foreach($category->packages as $pkg)
                                <div class="group relative border border-[#e3d5c4] rounded-xl bg-white p-5 hover:shadow-lg hover:-translate-y-1 transition-all duration-200">
                                    {{-- Popular Badge --}}
                                    @if($pkg->is_popular ?? false)
                                        <div class="absolute -top-2 -right-2 bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white text-xs px-3 py-1 rounded-full shadow-lg">
                                            <i class="fa-solid fa-star mr-1"></i>Populer
                                        </div>
                                    @endif

                                    {{-- Package Image --}}
                                    @if($pkg->overview_image)
                                        <div class="h-32 w-full rounded-lg overflow-hidden mb-3">
                                            <img src="{{ Storage::url($pkg->overview_image) }}" alt="{{ $pkg->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        </div>
                                    @else
                                        <div class="h-32 w-full rounded-lg bg-gradient-to-br from-[#f0e4d6] to-[#e3d5c4] mb-3 flex items-center justify-center">
                                            <i class="fa-solid fa-image text-[#8b7359] text-3xl"></i>
                                        </div>
                                    @endif

                                    {{-- Package Info --}}
                                    <h4 class="font-display font-semibold text-lg text-[#3f2b1b] mb-1">{{ $pkg->name }}</h4>
                                    <p class="text-xl font-bold text-[#b58042] mb-2">Rp {{ number_format($pkg->price) }}</p>
                                    <p class="text-sm text-[#6f5134] line-clamp-2 mb-3">{{ $pkg->description }}</p>

                                    {{-- Quick Features --}}
                                    @if($pkg->features && count($pkg->features) > 0)
                                        <div class="space-y-1 mb-3">
                                            @foreach(array_slice($pkg->features, 0, 2) as $feature)
                                                <p class="text-xs text-[#7a5b3a] flex items-center gap-1">
                                                    <i class="fa-solid fa-circle-check text-[#b58042] text-[10px]"></i>
                                                    {{ Str::limit($feature, 30) }}
                                                </p>
                                            @endforeach
                                            @if(count($pkg->features) > 2)
                                                <p class="text-xs text-[#b58042]">+{{ count($pkg->features) - 2 }} fitur lainnya</p>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Action Buttons --}}
                                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-[#f0e4d6]">
                                        <a href="{{ route('catalog.package.show', $pkg) }}" class="text-sm text-[#b58042] hover:text-[#8b5b2e] flex items-center gap-1">
                                            Detail <i class="fa-solid fa-arrow-right text-xs"></i>
                                        </a>
                                        @if(auth()->user()->role === \App\Enums\Role::CLIENT)
                                            <a href="{{ route('bookings.create', ['package_id' => $pkg->id]) }}" class="px-3 py-1.5 rounded-lg bg-[#b58042] text-white text-xs hover:bg-[#9b6a34] transition-colors">
                                                Pesan
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-[#7a5b3a]">
                            <i class="fa-solid fa-box-open text-4xl mb-3 opacity-50"></i>
                            <p>Belum ada paket dalam kategori ini.</p>
                            @if(auth()->user()->role === \App\Enums\Role::ADMIN || auth()->user()->role === \App\Enums\Role::MANAGER)
                                <a href="{{ route('admin.catalog.packages', $category) }}" class="inline-block mt-3 px-4 py-2 rounded-lg bg-[#b58042] text-white text-sm">
                                    Tambah Paket
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white/80 backdrop-blur-sm border border-[#e3d5c4] rounded-2xl">
                <i class="fa-solid fa-store-slash text-5xl text-[#8b7359] mb-4 opacity-50"></i>
                <p class="text-[#6f5134] mb-2">Belum ada kategori layanan.</p>
                @if(auth()->user()->role === \App\Enums\Role::ADMIN || auth()->user()->role === \App\Enums\Role::MANAGER)
                    <a href="{{ route('admin.catalog.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#b58042] text-white">
                        <i class="fa-solid fa-plus"></i>
                        Buat Kategori Baru
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</x-app-layout>