@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-building text-[#D4A017]"></i>
                    Cabang Studio
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Daftar <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Cabang</span>
                </h2>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.locations.create') }}"
                   class="inline-flex items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#D4A017]/25 transition-all hover:-translate-y-0.5 hover:shadow-xl">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Tambah Cabang
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <x-stat-card label="Total Cabang" :value="$locations->count()" />
                <x-stat-card label="Cabang Aktif" :value="$locations->where('is_active', true)->count()" color="emerald" />
                <x-stat-card label="Total Ruangan" :value="$locations->sum('rooms_count')" color="amber" />
            </section>

            <section class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($locations as $loc)
                    <article class="bg-white border border-[#EDE0D0] rounded-3xl shadow-xl overflow-hidden hover:-translate-y-1 hover:shadow-2xl transition-all">
                        <div class="aspect-[16/10] bg-[#F4EDE4] overflow-hidden">
                            @if($loc->photo_path)
                                <img src="{{ Storage::url($loc->photo_path) }}" alt="{{ $loc->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="h-full w-full flex items-center justify-center text-[#D4A017]/40">
                                    <i class="fa-solid fa-building text-6xl"></i>
                                </div>
                            @endif
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-display text-2xl font-semibold text-[#3F2B1B]">{{ $loc->name }}</h3>
                                    <p class="mt-1 text-sm text-[#7A5B3A] line-clamp-2">{{ $loc->address ?? 'Alamat belum diisi' }}</p>
                                </div>
                                <span class="shrink-0 rounded-3xl px-4 py-1.5 text-xs font-semibold {{ $loc->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-2xl bg-[#FAF6F0] px-4 py-3">
                                    <p class="text-xs uppercase tracking-wide text-[#8B7359]">Ruangan</p>
                                    <p class="mt-1 font-semibold text-[#3F2B1B]">{{ $loc->rooms_count }}</p>
                                </div>
                                <div class="rounded-2xl bg-[#FAF6F0] px-4 py-3">
                                    <p class="text-xs uppercase tracking-wide text-[#8B7359]">Foto Lokasi</p>
                                    <p class="mt-1 font-semibold text-[#3F2B1B]">{{ count($loc->photo_gallery ?? []) }}</p>
                                </div>
                            </div>

                            <a href="{{ route('admin.locations.show', $loc) }}"
                               class="inline-flex w-full items-center justify-center gap-3 rounded-3xl border border-[#E1D3C5] px-5 py-3 font-semibold text-[#5C432C] hover:bg-[#FAF6F0] hover:border-[#D4A017] transition-all">
                                <i class="fa-solid fa-folder-open text-[#D4A017]"></i>
                                Detail Cabang
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 text-center py-20 bg-white/80 border border-[#EDE0D0] rounded-3xl shadow-xl">
                        <i class="fa-solid fa-store-slash text-6xl text-[#D4A017]/30"></i>
                        <p class="mt-4 text-[#3F2B1B] font-medium">Belum ada cabang studio.</p>
                    </div>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
