@php use Illuminate\Support\Str; @endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-[#8B7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-box text-[#D4A017]"></i>
                    Katalog Layanan
                </p>
                <h2 class="text-4xl font-display font-bold tracking-tighter text-[#3F2B1B] mt-1">
                    Paket: <span class="bg-gradient-to-r from-[#D4A017] to-[#E07A5F] bg-clip-text text-transparent">{{ $category->name }}</span>
                </h2>
            </div>
            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <a href="{{ route('admin.catalog') }}"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:shadow-md transition-all">
                    Kembali ke Kategori
                </a>
                <a href="{{ route('admin.catalog.packages.create', $category) }}"
                   class="inline-flex w-full sm:w-auto items-center justify-center gap-3 px-8 py-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-lg shadow-[#D4A017]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Paket Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Session Messages --}}
            @if (session('status'))
                <div class="flex items-center gap-3 p-5 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center gap-3 p-5 rounded-3xl bg-red-50 border border-red-200 text-red-700">
                    <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Packages Grid --}}
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($packages as $pkg)
                    <div class="relative group" x-data="{ confirmDelete: false }">
                        <div class="h-full bg-white/85 backdrop-blur-sm rounded-3xl border border-[#EDE0D0] shadow-xl hover:shadow-2xl transition-all duration-300 overflow-hidden">
                            
                            <!-- Package Image -->
                            @if($pkg->overview_image)
                                <div class="relative h-56 w-full overflow-hidden">
                                    <img src="{{ Storage::url($pkg->overview_image) }}" 
                                         alt="{{ $pkg->name }}" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/45 via-black/10 to-transparent"></div>
                                    <div class="absolute bottom-4 left-4 flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-full bg-white/90 text-[#5C432C] text-xs font-semibold shadow">
                                            {{ $category->name }}
                                        </span>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium shadow
                                            {{ $pkg->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </div>
                            @else
                                <div class="relative h-56 w-full bg-gradient-to-br from-[#FAF6F0] via-[#F7EFE5] to-[#F4EDE4] flex items-center justify-center">
                                    <i class="fa-solid fa-image text-[#8B7359] text-6xl opacity-60"></i>
                                    <div class="absolute top-4 right-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium
                                            {{ $pkg->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            <div class="p-7 flex h-[calc(100%-14rem)] flex-col">
                                <!-- Name -->
                                <div class="mb-4">
                                    <p class="text-[11px] uppercase tracking-[0.18em] text-[#8B7359] mb-2">Ringkasan Paket</p>
                                    <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] leading-tight line-clamp-2">{{ $pkg->name }}</h3>
                                </div>

                                <!-- Price -->
                                <div class="mb-5">
                                    <p class="text-sm text-[#8B7359] mb-1">Harga Paket</p>
                                    <p class="text-4xl font-bold text-[#D4A017] leading-none">
                                        Rp {{ number_format($pkg->price) }}
                                    </p>
                                </div>

                                <!-- Description -->
                                @if($pkg->description)
                                    <p class="text-sm text-[#7A5B3A] line-clamp-3 mb-5 leading-relaxed">{{ $pkg->description }}</p>
                                @endif

                                <div class="space-y-5 mb-6">
                                    <!-- Quick Meta -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FCF7F1] px-4 py-3">
                                            <p class="text-[11px] uppercase tracking-[0.18em] text-[#8B7359] mb-1">Kapasitas</p>
                                            <p class="text-sm font-semibold text-[#3F2B1B]">
                                                {{ $pkg->max_people ? 'Maks. '.$pkg->max_people.' orang' : 'Fleksibel' }}
                                            </p>
                                        </div>
                                        <div class="rounded-2xl border border-[#EDE0D0] bg-[#FCF7F1] px-4 py-3">
                                            <p class="text-[11px] uppercase tracking-[0.18em] text-[#8B7359] mb-1">Add-on</p>
                                            <p class="text-sm font-semibold text-[#3F2B1B]">
                                                {{ is_countable($pkg->addons ?? null) ? count($pkg->addons) : 0 }} opsi
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Quick Features -->
                                    @if($pkg->features && count($pkg->features) > 0)
                                        <div>
                                            <h4 class="text-sm font-medium text-[#8B7359] uppercase tracking-wider mb-3 flex items-center gap-2">
                                                <i class="fa-solid fa-star text-[#D4A017]"></i>
                                                Yang Didapat
                                            </h4>
                                            <div class="space-y-2">
                                                @foreach(array_slice($pkg->features, 0, 3) as $feature)
                                                    <div class="flex items-start gap-2 text-xs text-[#5C432C]">
                                                        <i class="fa-solid fa-circle-check text-[#D4A017] mt-0.5"></i>
                                                        <span>{{ Str::limit($feature, 52) }}</span>
                                                    </div>
                                                @endforeach
                                                @if(count($pkg->features) > 3)
                                                    <p class="text-xs text-[#D4A017] font-medium">+{{ count($pkg->features) - 3 }} fitur lainnya</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Quick Add-ons -->
                                    @if($pkg->addons && count($pkg->addons) > 0)
                                        <div>
                                            <h4 class="text-sm font-medium text-[#8B7359] uppercase tracking-wider mb-3 flex items-center gap-2">
                                                <i class="fa-solid fa-plus-circle text-[#D4A017]"></i>
                                                Add-on Tersedia
                                            </h4>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach(array_slice($pkg->addons, 0, 2) as $addon)
                                                    @php
                                                        $addonLabel = is_array($addon) ? ($addon['label'] ?? '-') : $addon;
                                                        $addonPrice = is_array($addon) ? (int) ($addon['price'] ?? 0) : 0;
                                                        $addonUnit = is_array($addon) ? trim((string) ($addon['unit'] ?? '')) : '';
                                                    @endphp
                                                    <span class="px-3 py-1.5 rounded-full bg-[#F1E5D8] border border-[#E3D5C4] text-[#6B4A2D] text-xs">
                                                        {{ Str::limit($addonLabel, 18) }}
                                                        @if($addonPrice > 0)
                                                            · Rp {{ number_format($addonPrice, 0, ',', '.') }}
                                                        @endif
                                                        @if($addonUnit !== '')
                                                            / {{ $addonUnit }}
                                                        @endif
                                                    </span>
                                                @endforeach
                                                @if(count($pkg->addons) > 2)
                                                    <span class="px-3 py-1.5 rounded-full bg-white border border-[#E3D5C4] text-[#8B7359] text-xs">
                                                        +{{ count($pkg->addons) - 2 }} add-on
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-auto flex items-center gap-3 pt-6 border-t border-[#EDE0D0]">
                                    <a href="{{ route('admin.packages.show', $pkg) }}"
                                       class="flex-1 text-center py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all text-sm font-medium">
                                        Detail
                                    </a>
                                    <a href="{{ route('admin.packages.edit', $pkg) }}"
                                       class="flex-1 text-center py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all text-sm font-medium">
                                        Edit
                                    </a>
                                    <button type="button"
                                            @click="confirmDelete = true"
                                            class="flex-1 py-3 rounded-3xl border border-red-200 text-red-600 hover:bg-red-50 transition-all text-sm font-medium">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Confirmation Modal -->
                        <div x-show="confirmDelete" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60" x-transition.opacity>
                            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8" @click.stop>
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                                        <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xl font-semibold text-[#3F2B1B]">Hapus Paket?</h4>
                                        <p class="text-[#7A5B3A] mt-2">
                                            Paket <span class="font-medium">"{{ $pkg->name }}"</span> akan dihapus secara permanen. 
                                            Tindakan ini tidak dapat dibatalkan.
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-8 flex justify-end gap-3">
                                    <button type="button"
                                            @click="confirmDelete = false"
                                            class="px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white transition-all">
                                        Batal
                                    </button>
                                    <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="px-6 py-3 rounded-3xl bg-red-600 text-white hover:bg-red-700 transition-all">
                                            <i class="fa-solid fa-trash-can mr-2"></i>
                                            Hapus Paket
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20">
                        <div class="bg-white rounded-3xl border border-[#EDE0D0] p-16 text-center">
                            <i class="fa-solid fa-box-open text-7xl text-[#8B7359] mb-6 opacity-40"></i>
                            <p class="text-2xl font-medium text-[#3F2B1B] mb-2">Belum ada paket dalam kategori ini</p>
                            <p class="text-[#7A5B3A] mb-8">Klik tombol di atas untuk menambahkan paket baru</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
