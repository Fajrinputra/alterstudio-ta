@php
    use App\Enums\Role;
    use Illuminate\Support\Facades\Storage;

    $user = Auth::user();
    $role = $user?->role;
    $menu = [
        ['label' => 'Dashboard', 'href' => route('dashboard'), 'icon' => '🏠'],
    ];

    if (in_array($role, [Role::ADMIN, Role::MANAGER])) {
        $menu[] = ['label' => 'Pemesanan', 'href' => url('/admin/bookings'), 'icon' => '🧾'];
        $menu[] = ['label' => 'Katalog', 'href' => url('/admin/catalog'), 'icon' => '📦'];
        $menu[] = ['label' => 'Kelola Pengguna', 'href' => route('admin.users.index'), 'icon' => '👥'];
        $menu[] = ['label' => 'Cabang', 'href' => url('/admin/locations/manage'), 'icon' => '🏢'];
        if ($role === Role::ADMIN) {
            $menu[] = ['label' => 'Hero Landing', 'href' => route('admin.landing.hero'), 'icon' => '🖼️'];
        }
    }

    if ($role === Role::MANAGER) {
        $menu[] = ['label' => 'Laporan', 'href' => route('payroll.index'), 'icon' => '📊'];
    }

    if (in_array($role, [Role::PHOTOGRAPHER, Role::EDITOR, Role::ADMIN])) {
        $menu[] = ['label' => 'Jadwal', 'href' => url('/admin/schedules'), 'icon' => '🗓️'];
    }

    if ($role === Role::CLIENT) {
        $menu[] = ['label' => 'Pemesanan', 'href' => url('/bookings'), 'icon' => '🧾'];
        $menu[] = ['label' => 'Katalog', 'href' => route('catalog.public'), 'icon' => '📦'];
    }
@endphp

<aside class="hidden lg:flex w-72 shrink-0 bg-gradient-to-b from-[#faf3eb] to-[#f3e7d9] border-r border-[#e3d5c4] fixed inset-y-0 left-0 z-40 shadow-2xl shadow-black/5">
    <div class="flex flex-col w-full p-5 space-y-6 h-full overflow-y-auto">
        <div class="relative">
            <div class="absolute -top-3 -left-3 w-20 h-20 bg-[#b58042]/10 rounded-full blur-2xl"></div>

            <div class="relative bg-white/60 backdrop-blur-sm rounded-2xl p-4 border border-[#e3d5c4] shadow-inner">
                <div class="flex items-center gap-4">
                    @php
                        $avatarUrl = $user?->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null;
                        $initial = $user ? strtoupper(mb_substr($user->name, 0, 1)) : 'A';
                    @endphp

                    <div class="relative">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="h-14 w-14 rounded-xl border-2 border-white object-cover shadow-lg">
                        @else
                            <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                {{ $initial }}
                            </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                    </div>

                    <div class="flex-1">
                        <p class="font-display font-bold text-lg text-[#3f2b1b] leading-tight">Alter Studio</p>
                        <p class="text-xs text-[#8b7359] flex items-center gap-1 mt-0.5">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#b58042]"></span>
                            {{ ucfirst(strtolower($role?->value ?? '')) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <nav class="flex-1 space-y-1">
            @foreach($menu as $item)
                @php
                    $isActive = request()->url() === $item['href'];
                @endphp
                <a href="{{ $item['href'] }}"
                   class="group flex items-center gap-4 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                          {{ $isActive
                              ? 'bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white shadow-md shadow-[#b58042]/30'
                              : 'text-[#6c4f32] hover:bg-white/80 hover:shadow-md hover:shadow-black/5' }}">
                    <span class="text-xl {{ $isActive ? 'text-white' : 'text-[#8b7359] group-hover:text-[#b58042]' }}">{{ $item['icon'] }}</span>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if($isActive)
                        <span class="w-1.5 h-1.5 bg-white rounded-full"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="pt-4 border-t border-[#e3d5c4] space-y-3">
            <div class="px-4 py-3 bg-white/50 rounded-xl">
                <p class="font-medium text-[#3f2b1b] flex items-center gap-2">
                    <i class="fa-solid fa-user text-[#b58042]"></i>
                    {{ $user?->name }}
                </p>
                <p class="text-xs text-[#8b7359] truncate mt-1">{{ $user?->email }}</p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-red-500 hover:text-white hover:border-red-500 transition-all duration-200 group">
                    <i class="fa-solid fa-circle-right group-hover:rotate-180 transition-transform duration-300"></i>
                    <span class="flex-1 text-left font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Mobile sidebar drawer --}}
<div class="lg:hidden" x-cloak>
    <div x-show="mobileSidebar" x-transition.opacity class="fixed inset-0 z-40 bg-black/40" @click="mobileSidebar = false"></div>

    <aside x-show="mobileSidebar"
           x-transition:enter="transform transition ease-out duration-200"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transform transition ease-in duration-150"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-[#faf3eb] to-[#f3e7d9] border-r border-[#e3d5c4] shadow-2xl shadow-black/10">
        <div class="flex flex-col w-full p-5 space-y-5 h-full overflow-y-auto">
            <div class="flex items-center justify-between">
                <p class="font-display font-bold text-lg text-[#3f2b1b]">Alter Studio</p>
                <button @click="mobileSidebar = false" class="w-9 h-9 rounded-lg hover:bg-white/80 text-[#6c4f32]">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="relative bg-white/60 backdrop-blur-sm rounded-2xl p-4 border border-[#e3d5c4] shadow-inner">
                <div class="flex items-center gap-4">
                    @php
                        $avatarUrl = $user?->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null;
                        $initial = $user ? strtoupper(mb_substr($user->name, 0, 1)) : 'A';
                    @endphp

                    <div class="relative">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="h-12 w-12 rounded-xl border-2 border-white object-cover shadow-lg">
                        @else
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                {{ $initial }}
                            </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-green-500 border-2 border-white rounded-full"></div>
                    </div>

                    <div class="flex-1">
                        <p class="font-semibold text-[#3f2b1b] leading-tight">{{ $user?->name }}</p>
                        <p class="text-xs text-[#8b7359] truncate mt-0.5">{{ $user?->email }}</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 space-y-1">
                @foreach($menu as $item)
                    @php
                        $isActive = request()->url() === $item['href'];
                    @endphp
                    <a href="{{ $item['href'] }}"
                       @click="mobileSidebar = false"
                       class="group flex items-center gap-4 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200
                              {{ $isActive
                                  ? 'bg-gradient-to-r from-[#b58042] to-[#8b5b2e] text-white shadow-md shadow-[#b58042]/30'
                                  : 'text-[#6c4f32] hover:bg-white/80 hover:shadow-md hover:shadow-black/5' }}">
                        <span class="text-xl {{ $isActive ? 'text-white' : 'text-[#8b7359] group-hover:text-[#b58042]' }}">{{ $item['icon'] }}</span>
                        <span class="flex-1">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-[#d7c5b2] text-[#5b422b] hover:bg-red-500 hover:text-white hover:border-red-500 transition-all duration-200 group">
                    <i class="fa-solid fa-circle-right group-hover:rotate-180 transition-transform duration-300"></i>
                    <span class="flex-1 text-left font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </aside>
</div>
