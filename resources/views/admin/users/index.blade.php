@php
    use App\Enums\Role;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-[#8B7359] tracking-[1.5px] uppercase font-medium flex items-center gap-2">
                    <i class="fa-solid fa-users text-[#D4A017]"></i>
                    Kelola Pengguna
                </p>
                <h2 class="font-display text-4xl md:text-5xl font-semibold tracking-[-1px] text-[#3F2B1B] mt-1">
                    Pengguna & <span class="font-medium bg-gradient-to-r from-[#D4A017] via-[#E07A5F] to-[#D4A017] bg-clip-text text-transparent">Akses</span>
                </h2>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex w-full sm:w-auto items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#D4A017]/25 transition-all hover:-translate-y-0.5 hover:shadow-xl">
                <i class="fa-solid fa-plus text-xs"></i>
                Tambah Pengguna Baru
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-[#FAF6F0]" x-data="{ showDelete: false, deleteUrl: '', deleteName: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Filter Section --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-[#D4A017]/10 via-[#E07A5F]/10 rounded-3xl blur-2xl"></div>
                <div class="relative glass border border-[#EDE0D0] rounded-3xl p-8 shadow-xl backdrop-blur-2xl">
                    <form method="GET" class="flex flex-wrap items-end gap-6">
                        <div class="min-w-[260px] flex-1">
                            <label class="block text-xs font-medium text-[#7A5B3A] tracking-widest mb-2 flex items-center gap-2">
                                <i class="fa-solid fa-filter text-[#D4A017]"></i>
                                Filter Akses Akun
                            </label>
                            <select name="role_filter" 
                                    class="w-full px-6 py-4 rounded-3xl border border-[#E1D3C5] bg-white/70 backdrop-blur-md text-[#3F2B1B] focus:border-[#D4A017] focus:ring-2 focus:ring-[#D4A017]/20 transition-all">
                                <option value="">Semua Akun</option>
                                <option value="photographer" @selected(($roleFilter ?? null) === 'photographer')>Akun Fotografer</option>
                                <option value="editor" @selected(($roleFilter ?? null) === 'editor')>Akun Editor</option>
                            </select>
                        </div>
                        
                        <div class="flex w-full sm:w-auto gap-3">
                            <button class="h-14 px-8 rounded-3xl bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white font-semibold shadow-xl hover:shadow-2xl hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3">
                                <i class="fa-solid fa-filter"></i>
                                Terapkan Filter
                            </button>
                            @if(!empty($roleFilter))
                                <a href="{{ route('admin.users.index') }}" 
                                   class="h-14 px-8 rounded-3xl border border-[#E1D3C5] text-[#5C432C] hover:bg-white hover:border-[#D4A017] transition-all flex items-center justify-center gap-3">
                                    <i class="fa-solid fa-rotate-left"></i>
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Users Table --}}
            <div class="relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017]/5 to-[#E07A5F]/5 rounded-3xl blur-3xl"></div>
                <div class="relative bg-white/80 backdrop-blur-2xl border border-[#EDE0D0] rounded-3xl shadow-2xl overflow-hidden">
                    
                    <div class="px-8 py-6 border-b border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                        <h3 class="font-display text-2xl font-bold text-[#3F2B1B] flex items-center gap-3">
                            <i class="fa-solid fa-list text-[#D4A017]"></i>
                            Daftar Pengguna
                        </h3>
                    </div>
                    
                    <div class="space-y-4 p-4 lg:hidden">
                        @foreach($users as $user)
                            @php
                                $primaryRole = $user->role instanceof Role ? $user->role->value : $user->role;
                                $isOwner = $user->role === \App\Enums\Role::OWNER;
                            @endphp
                            <article class="rounded-3xl border border-[#EDE0D0] bg-white px-4 py-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10">
                                            <span class="text-sm font-semibold text-[#3F2B1B]">{{ substr($user->name, 0, 1) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="truncate text-sm font-semibold text-[#3F2B1B]">{{ $user->name }}</h4>
                                            <p class="truncate text-sm text-[#7A5B3A]">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <span class="inline-flex rounded-3xl bg-[#F4EDE4] px-3 py-1 text-[11px] font-medium text-[#5C432C]">
                                        {{ ucfirst($primaryRole) }}
                                    </span>
                                </div>

                                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-2xl bg-[#FAF6F0] px-3 py-2.5">
                                        <dt class="text-[11px] uppercase tracking-wide text-[#8B7359]">No. HP</dt>
                                        <dd class="mt-1 font-medium text-[#3F2B1B]">{{ $user->no_hp ?? '-' }}</dd>
                                    </div>
                                    <div class="rounded-2xl bg-[#FAF6F0] px-3 py-2.5">
                                        <dt class="text-[11px] uppercase tracking-wide text-[#8B7359]">Status</dt>
                                        <dd class="mt-2">
                                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}">
                                                @csrf
                                                <select name="is_active" onchange="this.form.submit()"
                                                        @disabled($isOwner)
                                                        class="w-full rounded-2xl border px-3 py-2 text-sm font-medium transition-all {{ $user->is_active ? 'border-emerald-200 bg-emerald-100 text-emerald-700' : 'border-red-200 bg-red-100 text-red-700' }}">
                                                    <option value="1" @selected($user->is_active)>Aktif</option>
                                                    <option value="0" @selected(!$user->is_active)>Nonaktif</option>
                                                </select>
                                                @if($isOwner)
                                                    <p class="mt-1 text-[10px] leading-4 text-[#8B7359]">Akun Owner wajib selalu aktif.</p>
                                                @endif
                                            </form>
                                        </dd>
                                    </div>
                                </dl>

                                <div class="mt-4 flex items-center gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="inline-flex items-center gap-2 rounded-3xl border border-[#E1D3C5] px-3 py-2 text-[11px] font-medium text-[#5C432C] transition hover:border-[#D4A017] hover:bg-white">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Edit
                                    </a>
                                    @if(!$isOwner)
                                        <button @click="showDelete=true; deleteUrl='{{ route('admin.users.destroy',$user) }}'; deleteName='{{ addslashes($user->name) }}'"
                                                class="inline-flex items-center gap-2 rounded-3xl border border-red-200 px-3 py-2 text-[11px] font-medium text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                            <i class="fa-solid fa-trash-can"></i>
                                            Hapus
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="hidden overflow-x-auto lg:block">
                        <table class="min-w-full table-fixed">
                            <colgroup>
                                <col class="w-[32%]">
                                <col class="w-[18%]">
                                <col class="w-[18%]">
                                <col class="w-[15%]">
                                <col class="w-[17%]">
                            </colgroup>
                            <thead>
                                <tr class="bg-gradient-to-r from-[#FAF6F0] to-white border-b border-[#EDE0D0]">
                                    <th class="px-3 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Nama</th>
                                    <th class="px-3 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Kontak</th>
                                    <th class="px-3 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Role</th>
                                    <th class="px-3 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Status</th>
                                    <th class="px-3 py-4 text-center text-xs font-semibold tracking-widest text-[#3F2B1B] uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EDE0D0]">
                                @foreach($users as $user)
                                    @php
                                        $primaryRole = $user->role instanceof Role ? $user->role->value : $user->role;
                                        $isOwner = $user->role === \App\Enums\Role::OWNER;
                                    @endphp
                                    <tr class="hover:bg-[#FAF6F0] transition-all duration-300">
                                        <td class="px-3 py-4 align-middle text-left">
                                            <div class="flex items-center justify-start gap-3">
                                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#D4A017]/10 to-[#E07A5F]/10">
                                                    <span class="text-sm font-semibold text-[#3F2B1B]">{{ substr($user->name, 0, 1) }}</span>
                                                </div>
                                                <div class="min-w-0 text-left">
                                                    <p class="truncate text-sm font-semibold text-[#3F2B1B]">{{ $user->name }}</p>
                                                    <p class="truncate text-xs text-[#8B7359]">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 align-middle text-center text-sm text-[#7A5B3A]">
                                            {{ $user->no_hp ?? '-' }}
                                        </td>
                                        <td class="px-3 py-4 align-middle text-center">
                                            <span class="inline-flex min-h-[34px] items-center rounded-3xl bg-[#F4EDE4] px-3 py-1.5 text-[11px] font-medium text-[#5C432C]">
                                                {{ ucfirst($primaryRole) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 align-middle text-center">
                                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="mx-auto w-fit">
                                                @csrf
                                                <select name="is_active" onchange="this.form.submit()"
                                                        @disabled($isOwner)
                                                        class="rounded-3xl border px-4 py-2 text-[11px] font-medium transition-all {{ $user->is_active ? 'border-emerald-200 bg-emerald-100 text-emerald-700' : 'border-red-200 bg-red-100 text-red-700' }}">
                                                    <option value="1" @selected($user->is_active)>Aktif</option>
                                                    <option value="0" @selected(!$user->is_active)>Nonaktif</option>
                                                </select>
                                                @if($isOwner)
                                                    <p class="mt-1 text-[10px] leading-4 text-[#8B7359]">Akun Owner wajib selalu aktif.</p>
                                                @endif
                                            </form>
                                        </td>
                                        <td class="px-3 py-4 align-middle text-center">
                                            <div class="flex flex-wrap items-center justify-center gap-2">
                                                <a href="{{ route('admin.users.edit', $user) }}"
                                                   class="inline-flex items-center gap-2 rounded-3xl border border-[#E1D3C5] px-3 py-2 text-[11px] font-medium text-[#5C432C] transition hover:border-[#D4A017] hover:bg-white">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                    Edit
                                                </a>
                                                @if(!$isOwner)
                                                    <button @click="showDelete=true; deleteUrl='{{ route('admin.users.destroy',$user) }}'; deleteName='{{ addslashes($user->name) }}'"
                                                            class="inline-flex items-center gap-2 rounded-3xl border border-red-200 px-3 py-2 text-[11px] font-medium text-red-600 transition hover:border-red-300 hover:bg-red-50">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                        Hapus
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="px-8 py-6 border-t border-[#EDE0D0] bg-gradient-to-r from-[#FAF6F0] to-white">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Delete Confirmation Modal --}}
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
                        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center">
                            <i class="fa-solid fa-triangle-exclamation text-red-600 text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-display text-2xl font-bold text-[#3F2B1B]">Hapus Pengguna?</h3>
                            <p class="text-[#7A5B3A]">Tindakan ini tidak dapat dibatalkan</p>
                        </div>
                    </div>
                    
                    <div class="bg-red-50 border border-red-200 rounded-3xl p-6 mb-8">
                        <p class="text-red-700 text-sm flex items-start gap-3">
                            <i class="fa-solid fa-circle-exclamation mt-1"></i>
                            <span>Anda akan menghapus pengguna <span class="font-semibold" x-text="deleteName"></span>. Semua data terkait akan ikut terhapus.</span>
                        </p>
                    </div>
                    
                    <div class="flex justify-end gap-4">
                        <button @click="showDelete=false"
                                class="px-8 py-4 rounded-3xl border border-[#E1D3C5] text-[#5C432C] font-medium hover:bg-[#FAF6F0] transition-all">
                            Batal
                        </button>
                        <form :action="deleteUrl" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex items-center gap-3 px-8 py-4 rounded-3xl bg-gradient-to-r from-red-600 to-red-700 text-white font-semibold hover:brightness-110 transition-all">
                                <i class="fa-solid fa-trash-can"></i>
                                Hapus Permanen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
