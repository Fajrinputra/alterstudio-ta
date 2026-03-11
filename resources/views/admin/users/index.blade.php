@php
    use App\Enums\Role;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-[#8b7359] tracking-wide flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#b58042]"></i>
                    Kelola Pengguna
                </p>
                <h2 class="text-3xl font-light tracking-tight text-[#3f2b1b] mt-1">
                    Pengguna & <span class="font-medium bg-gradient-to-r from-[#b58042] to-[#8b5b2e] bg-clip-text text-transparent">Akses</span>
                </h2>
            </div>
            <a href="{{ route('admin.users.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white font-semibold shadow-lg shadow-[#b58042]/30 hover:shadow-xl hover:-translate-y-0.5 transition-all">
                <i class="fa-solid fa-plus"></i>
                Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showDelete: false, deleteUrl: '', deleteName: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Session Status --}}
            @if (session('user_status'))
                <div class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span class="text-sm font-medium">{{ session('user_status') }}</span>
                </div>
            @endif

            {{-- Tabel Pengguna --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#b58042]/10 to-[#8b5b2e]/10 rounded-2xl blur-xl"></div>
                <div class="relative bg-white/70 backdrop-blur-xl border border-[#e3d5c4] rounded-2xl shadow-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#e3d5c4] bg-gradient-to-r from-[#faf3eb] to-white">
                        <h3 class="text-xl font-light text-[#3f2b1b] flex items-center gap-2">
                            <i class="fa-solid fa-list text-[#b58042]"></i>
                            Daftar Pengguna
                        </h3>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-[#faf3eb] to-white border-b border-[#e3d5c4]">
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Nama</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Email</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">No. HP</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Role</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Status</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-[#3f2b1b]">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e4d6]">
                                @foreach($users as $user)
                                    <tr class="hover:bg-white/80 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#b58042]/20 to-[#8b5b2e]/20 flex items-center justify-center">
                                                    <span class="text-xs font-semibold text-[#5b422b]">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                                <span class="font-medium text-[#3f2b1b]">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-[#6f5134]">{{ $user->email }}</td>
                                        <td class="px-6 py-4 text-[#6f5134]">{{ $user->no_hp ?? '-' }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1.5 rounded-full text-xs font-medium bg-[#f1e5d8] text-[#5b422b]">
                                                {{ $user->role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline">
                                                @csrf
                                                <button class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                                                    {{ $user->is_active 
                                                        ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' 
                                                        : 'bg-red-100 text-red-700 hover:bg-red-200' }}">
                                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-2">
                                            <a href="{{ route('admin.users.edit', $user) }}" 
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-[#d7c5b2] text-xs text-[#5b422b] hover:bg-white/80 transition-colors">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                                Edit
                                            </a>
                                            
                                            @if($user->role !== \App\Enums\Role::MANAGER)
                                                <button @click="showDelete=true; deleteUrl='{{ route('admin.users.destroy',$user) }}'; deleteName='{{ $user->name }}'"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-red-200 text-xs text-red-600 hover:bg-red-50 transition-colors">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                    Hapus
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="px-6 py-4 border-t border-[#e3d5c4] bg-gradient-to-r from-[#faf3eb] to-white">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Hapus --}}
        <div x-show="showDelete" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.outside="showDelete = false" 
                 class="bg-white rounded-2xl shadow-2xl border border-[#e3d5c4] max-w-md w-full p-6"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                        <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-display text-xl text-[#3f2b1b] font-bold">Hapus Pengguna</h3>
                        <p class="text-sm text-[#6f5134]">Tindakan ini permanen</p>
                    </div>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                    <p class="text-sm text-red-700 flex items-start gap-2">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <span>Anda akan menghapus pengguna <span class="font-semibold" x-text="deleteName"></span>. Semua data terkait akan ikut terhapus.</span>
                    </p>
                </div>

                <div class="flex justify-end gap-3">
                    <button @click="showDelete=false" 
                            class="px-5 py-2.5 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-[#fcf7f1] transition-colors">
                        Batal
                    </button>
                    <form :action="deleteUrl" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold shadow-lg shadow-red-600/30 hover:shadow-xl transition-all">
                            <i class="fa-solid fa-trash-can"></i>
                            Hapus Permanen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
