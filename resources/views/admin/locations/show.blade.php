@php
    use Illuminate\Support\Facades\Storage;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-[#D4A017]"></i>
                    Detail Cabang
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">{{ $studioLocation->name }}</h2>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.locations.manage') }}" class="inline-flex items-center justify-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all">
                    <i class="fa-solid fa-arrow-left"></i>
                    Daftar Cabang
                </a>
                <a href="{{ route('admin.locations.edit', $studioLocation) }}" class="inline-flex items-center justify-center gap-3 px-7 py-3 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Cabang
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]" x-data="{ showDeleteLocation: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            @if (session('status'))
                <div class="flex items-center gap-3 p-5 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-700 shadow-sm">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif

            <section class="grid lg:grid-cols-[minmax(0,1.35fr)_minmax(320px,0.65fr)] gap-8">
                <div class="bg-white border border-[#EDE0D0] rounded-3xl shadow-xl overflow-hidden">
                    @if(count($studioLocation->photo_gallery ?? []))
                        <div class="grid sm:grid-cols-2 gap-3 p-4 bg-[#FAF6F0]">
                            @foreach($studioLocation->photo_gallery as $photo)
                                <img src="{{ Storage::url($photo) }}" alt="{{ $studioLocation->name }}" class="aspect-[16/10] w-full rounded-3xl object-cover border border-[#EDE0D0]">
                            @endforeach
                        </div>
                    @else
                        <div class="aspect-[16/7] flex items-center justify-center bg-[#F4EDE4] text-[#D4A017]/40">
                            <i class="fa-solid fa-building text-7xl"></i>
                        </div>
                    @endif

                    <div class="p-8 space-y-6">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-3xl px-5 py-2 text-sm font-semibold {{ $studioLocation->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $studioLocation->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                            <span class="rounded-3xl px-5 py-2 text-sm font-semibold bg-[#FAF6F0] text-[#5C432C] border border-[#EDE0D0]">
                                {{ $studioLocation->rooms->count() }} Ruangan
                            </span>
                        </div>

                        <div>
                            <h3 class="font-display text-3xl font-semibold text-[#3F2B1B]">Informasi Cabang</h3>
                            @if($studioLocation->description)
                                <p class="mt-3 text-[#7A5B3A] leading-relaxed">{{ $studioLocation->description }}</p>
                            @endif
                        </div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-5">
                                <p class="text-xs uppercase tracking-widest text-[#8B7359]">Alamat</p>
                                <p class="mt-2 text-[#3F2B1B]">{{ $studioLocation->address ?? '-' }}</p>
                            </div>
                            <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-5">
                                <p class="text-xs uppercase tracking-widest text-[#8B7359]">Google Maps</p>
                                @if($studioLocation->map_url)
                                    <a href="{{ $studioLocation->map_url }}" target="_blank" class="mt-2 inline-flex items-center gap-2 font-semibold text-[#D4A017] hover:text-[#E07A5F]">
                                        Buka Maps <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                    </a>
                                @else
                                    <p class="mt-2 text-[#3F2B1B]">-</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="space-y-6">
                    <div class="bg-white border border-[#EDE0D0] rounded-3xl shadow-xl p-6">
                        <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3">
                            <i class="fa-solid fa-door-open text-[#D4A017]"></i>
                            Ruangan Studio
                        </h3>
                        <div class="mt-5 space-y-4">
                            @forelse($studioLocation->rooms as $room)
                                <div class="rounded-3xl border border-[#EDE0D0] bg-[#FAF6F0] p-5">
                                    @if($room->photo_path)
                                        <img src="{{ Storage::url($room->photo_path) }}" alt="{{ $room->name }}" class="mb-4 aspect-[16/10] w-full rounded-3xl object-cover border border-[#EDE0D0]">
                                    @else
                                        <div class="mb-4 aspect-[16/10] w-full rounded-3xl border border-[#EDE0D0] bg-white flex items-center justify-center text-[#D4A017]/40">
                                            <i class="fa-solid fa-door-open text-4xl"></i>
                                        </div>
                                    @endif
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h4 class="font-semibold text-[#3F2B1B]">{{ $room->name }}</h4>
                                            <p class="mt-1 text-sm text-[#7A5B3A]">{{ $room->description ?? '-' }}</p>
                                        </div>
                                        <span class="rounded-3xl px-3 py-1 text-xs font-semibold {{ $room->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $room->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-[#8B7359]">Belum ada ruangan.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white border border-red-100 rounded-3xl shadow-xl p-6">
                        <h3 class="font-display text-2xl font-semibold text-[#3F2B1B] flex items-center gap-3">
                            <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                            Area Berbahaya
                        </h3>
                        <p class="mt-3 text-sm text-[#7A5B3A]">Cabang yang dihapus tidak dapat dikembalikan dari halaman ini.</p>
                        <button type="button"
                                @click="showDeleteLocation = true"
                                class="mt-5 w-full inline-flex items-center justify-center gap-3 rounded-3xl border border-red-200 bg-red-50 px-6 py-3 font-semibold text-red-600 transition-all hover:border-red-300 hover:bg-red-100">
                            <i class="fa-solid fa-trash-can"></i>
                            Hapus Cabang
                        </button>
                    </div>
                </aside>
            </section>

            <template x-teleport="body">
            <div x-show="showDeleteLocation"
                 x-cloak
                 class="fixed inset-0 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md" style="z-index: 99999;"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="showDeleteLocation = false">
                <div @click.outside="showDeleteLocation = false"
                     class="bg-white rounded-3xl shadow-2xl border border-[#EDE0D0] max-w-md w-full overflow-hidden"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    <div class="p-8">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-100">
                                <i class="fa-solid fa-triangle-exclamation text-3xl text-red-600"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-display text-2xl font-bold text-[#3F2B1B]">Hapus Cabang?</h3>
                                <p class="mt-1 text-sm text-[#7A5B3A]">Tindakan ini tidak dapat dibatalkan.</p>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-red-200 bg-red-50 p-5 mb-8">
                            <p class="flex items-start gap-3 text-sm text-red-700">
                                <i class="fa-solid fa-circle-exclamation mt-1"></i>
                                <span>Cabang <span class="font-semibold">{{ $studioLocation->name }}</span> akan dihapus. Jika masih memiliki data pemesanan terkait, sistem dapat menolak penghapusan.</span>
                            </p>
                        </div>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <button type="button"
                                    @click="showDeleteLocation = false"
                                    class="inline-flex items-center justify-center rounded-3xl border border-[#E1D3C5] px-7 py-3.5 font-medium text-[#5C432C] transition-all hover:bg-[#FAF6F0]">
                                Batal
                            </button>
                            <form method="POST" action="{{ route('admin.locations.destroy', $studioLocation) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex w-full items-center justify-center gap-3 rounded-3xl bg-gradient-to-r from-red-600 to-red-700 px-7 py-3.5 font-semibold text-white transition-all hover:brightness-110">
                                    <i class="fa-solid fa-trash-can"></i>
                                    Hapus Permanen
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            </template>
        </div>
    </div>
</x-app-layout>
