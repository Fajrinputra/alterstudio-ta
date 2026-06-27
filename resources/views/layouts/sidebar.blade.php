@php
    use App\Enums\Role;
    use Illuminate\Support\Facades\Storage;
    $user = Auth::user();
    $role = $user?->role;
    $roleValue = $role instanceof Role ? $role->value : $role;
    $roleLabel = $roleValue ? ucfirst(strtolower($roleValue)) : '';
    $menu = [
        ['label' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'fa-solid fa-house', 'active' => ['dashboard']],
    ];
    if ($user?->isRole(Role::ADMIN, Role::MANAGER)) {
        $menu[] = ['label' => 'Pemesanan', 'href' => url('/admin/bookings'), 'icon' => 'fa-solid fa-receipt', 'active' => ['admin/bookings*', 'projects/*']];
        $menu[] = ['label' => 'Katalog', 'href' => url('/admin/catalog'), 'icon' => 'fa-solid fa-box-open', 'active' => ['admin/catalog*', 'catalog']];
        if ($user?->isRole(Role::MANAGER)) {
            $menu[] = ['label' => 'Hero Landing', 'href' => route('manager.landing.hero'), 'icon' => 'fa-solid fa-images', 'active' => ['manager/landing/hero*']];
        }
    }
    if ($user?->isRole(Role::OWNER)) {
        $menu[] = ['label' => 'Kelola Pengguna', 'href' => route('admin.users.index'), 'icon' => 'fa-solid fa-users', 'active' => ['admin/users*']];
        $menu[] = ['label' => 'Cabang', 'href' => url('/admin/locations/manage'), 'icon' => 'fa-solid fa-building', 'active' => ['admin/locations*']];
    }
    if ($user?->isRole(Role::MANAGER, Role::OWNER)) {
        $menu[] = ['label' => 'Laporan', 'href' => route('reports.index'), 'icon' => 'fa-solid fa-chart-column', 'active' => ['reports*']];
    }
    if ($user?->isRole(Role::PHOTOGRAPHER, Role::EDITOR, Role::ADMIN)) {
        $menu[] = ['label' => 'Jadwal', 'href' => url('/admin/schedules'), 'icon' => 'fa-solid fa-calendar-days', 'active' => ['admin/schedules*']];
    }
    if ($user?->isRole(Role::CLIENT)) {
        $menu[] = ['label' => 'Pemesanan', 'href' => url('/bookings'), 'icon' => 'fa-solid fa-receipt', 'active' => ['bookings*', 'projects/*']];
        $menu[] = ['label' => 'Katalog', 'href' => route('catalog.public'), 'icon' => 'fa-solid fa-box-open', 'active' => ['catalog']];
    }
@endphp

<aside class="hidden lg:flex w-56 shrink-0 bg-gradient-to-b from-[#FAF6F0] to-[#F4EDE4] border-r border-[#EDE0D0] fixed inset-y-0 left-0 z-40 shadow-lg">
    <div class="flex flex-col w-full p-3 space-y-4 h-full overflow-y-auto">
        <!-- Logo & User Info -->
        <div class="relative">
            <div class="absolute -top-3 -left-3 w-16 h-16 bg-[#D4A017]/20 rounded-full blur-2xl"></div>
            <div class="relative rounded-2xl border border-[#D4A017]/35 bg-white/60 p-3 shadow-md shadow-[#6B4A2D]/10 ring-1 ring-[#E07A5F]/15 backdrop-blur-xl">
                @php
                    $avatarUrl = $user?->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null;
                @endphp
                <div class="flex items-center gap-3">
                    <div class="relative shrink-0">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Foto profil {{ $user?->name }}" class="h-11 w-11 rounded-2xl border-2 border-white object-cover shadow-lg shadow-[#6B4A2D]/15">
                        @else
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] text-[#D4A017] shadow-sm">
                                <i class="fa-solid fa-user text-base"></i>
                            </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full ring-2 ring-white shadow-md"></div>
                    </div>
                    <div class="min-w-0 text-left">
                        <p class="font-display text-lg font-bold leading-tight tracking-tight text-[#3F2B1B] whitespace-nowrap">Alter Studio</p>
                        <span class="mt-2 block h-0.5 w-14 rounded-full bg-gradient-to-r from-[#D4A017] to-[#E07A5F]"></span>
                        <p class="mt-2 flex items-center gap-1.5 text-[10px] uppercase tracking-widest text-[#8B7359]">
                        <span class="inline-block w-2 h-2 rounded-full bg-[#D4A017]"></span>
                        {{ $roleLabel !== '' ? $roleLabel : ucfirst(strtolower($role?->value ?? '')) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-1">
            @foreach($menu as $item)
                @php
                    $activePatterns = $item['active'] ?? [];
                    $isActive = collect($activePatterns)->contains(fn ($pattern) => request()->is($pattern) || request()->routeIs($pattern));
                    $linkHost = parse_url($item['href'], PHP_URL_HOST);
                    $currentHost = request()->getHost();
                    $isExternal = $linkHost && !in_array($linkHost, [$currentHost, '127.0.0.1', 'localhost'], true);
                @endphp
                <a href="{{ $item['href'] }}"
                   @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
                   class="group flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300
                          {{ $isActive 
                              ? 'bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white shadow-lg shadow-[#D4A017]/40' 
                              : 'text-[#5C432C] hover:bg-white hover:shadow-md hover:text-[#D4A017]' }}">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center">
                        <i class="{{ $item['icon'] }} w-5 text-center text-sm {{ $isActive ? 'text-white' : 'text-[#8B7359] group-hover:text-[#D4A017]' }}"></i>
                    </span>
                    <span class="flex-1 text-left">{{ $item['label'] }}</span>
                    @if($isActive)
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-white shadow"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <!-- Logout -->
        <div class="pt-3 border-t border-[#EDE0D0]">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-[#E1D3C5] text-sm text-[#5C432C] hover:bg-red-500 hover:text-white hover:border-red-500 transition-all group">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center">
                        <i class="fa-solid fa-arrow-right-from-bracket w-6 text-center text-sm transition-transform group-hover:rotate-180"></i>
                    </span>
                    <span class="flex-1 text-left font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Mobile Sidebar -->
<div class="lg:hidden" x-cloak>
    <div x-show="mobileSidebar" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm" @click="mobileSidebar = false"></div>
    <aside x-show="mobileSidebar"
           x-transition:enter="transform transition ease-out duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transform transition ease-in duration-200"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 w-72 max-w-[88vw] bg-gradient-to-b from-[#FAF6F0] to-[#F4EDE4] border-r border-[#EDE0D0] shadow-2xl overflow-y-auto">
        <div class="flex flex-col w-full p-4 space-y-5 h-full">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-display text-xl font-bold text-[#3F2B1B]">Alter Studio</p>
                    <span class="mt-1 block h-0.5 w-14 rounded-full bg-gradient-to-r from-[#D4A017] to-[#E07A5F]"></span>
                </div>
                <button @click="mobileSidebar = false" class="w-9 h-9 flex items-center justify-center rounded-xl hover:bg-white/70 text-[#8B7359]">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Mobile User Info -->
            <div class="glass rounded-2xl p-4 border border-white/60">
                <div class="flex items-center gap-4">
                    @php
                        $avatarUrl = $user?->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null;
                    @endphp
                    <div class="relative">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="h-12 w-12 rounded-2xl border-2 border-white object-cover">
                        @else
                            <div class="h-12 w-12 rounded-2xl border border-[#E1D3C5] bg-[#FAF6F0] flex items-center justify-center text-[#D4A017]">
                                <i class="fa-solid fa-user"></i>
                            </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></div>
                    </div>
                    <div>
                        <p class="font-medium text-[#3F2B1B]">{{ $user?->name }}</p>
                        <p class="text-xs text-[#8B7359]">{{ $user?->email }}</p>
                        <p class="text-[10px] uppercase tracking-widest text-[#A18263]">{{ $roleLabel }}</p>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <nav class="flex-1 space-y-1">
                @foreach($menu as $item)
                    @php
                        $activePatterns = $item['active'] ?? [];
                        $isActive = collect($activePatterns)->contains(fn ($pattern) => request()->is($pattern) || request()->routeIs($pattern));
                        $linkHost = parse_url($item['href'], PHP_URL_HOST);
                        $currentHost = request()->getHost();
                        $isExternal = $linkHost && !in_array($linkHost, [$currentHost, '127.0.0.1', 'localhost'], true);
                    @endphp
                    <a href="{{ $item['href'] }}"
                       @if($isExternal) target="_blank" rel="noopener noreferrer" @endif
                       @click="mobileSidebar = false"
                       class="group flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all
                              {{ $isActive ? 'bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white shadow-lg' : 'text-[#5C432C] hover:bg-white hover:text-[#D4A017]' }}">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center">
                            <i class="{{ $item['icon'] }} w-6 text-center text-base {{ $isActive ? 'text-white' : 'text-[#8B7359]' }}"></i>
                        </span>
                        <span class="flex-1 text-left">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <!-- Mobile Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-[#E1D3C5] text-[#5C432C] hover:bg-red-500 hover:text-white transition-all">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center">
                        <i class="fa-solid fa-arrow-right-from-bracket w-7 text-center"></i>
                    </span>
                    <span class="flex-1 text-left font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </aside>
</div>
