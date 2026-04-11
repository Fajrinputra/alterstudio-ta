@php
    use App\Enums\Role;
    use Illuminate\Support\Facades\Storage;
    $user = Auth::user();
    $role = $user?->role;
    $effectiveRoles = $user?->effectiveRoles() ?? [];
    $roleLabel = collect($effectiveRoles)
        ->map(fn ($item) => ucfirst(strtolower($item)))
        ->join(' / ');
    $menu = [
        ['label' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'fa-solid fa-house', 'active' => ['dashboard']],
    ];
    if ($user?->isRole(Role::ADMIN, Role::MANAGER)) {
        $menu[] = ['label' => 'Pemesanan', 'href' => url('/admin/bookings'), 'icon' => 'fa-solid fa-receipt', 'active' => ['admin/bookings*', 'projects/*']];
        $menu[] = ['label' => 'Katalog', 'href' => url('/admin/catalog'), 'icon' => 'fa-solid fa-box-open', 'active' => ['admin/catalog*', 'catalog']];
        if ($user?->isRole(Role::ADMIN)) {
            $menu[] = ['label' => 'Hero Landing', 'href' => route('admin.landing.hero'), 'icon' => 'fa-solid fa-images', 'active' => ['admin/landing/hero*']];
        }
    }
    if ($user?->isRole(Role::MANAGER)) {
        $menu[] = ['label' => 'Kelola Pengguna', 'href' => route('admin.users.index'), 'icon' => 'fa-solid fa-users', 'active' => ['admin/users*']];
        $menu[] = ['label' => 'Cabang', 'href' => url('/admin/locations/manage'), 'icon' => 'fa-solid fa-building', 'active' => ['admin/locations*']];
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

<aside class="hidden lg:flex w-72 shrink-0 bg-gradient-to-b from-[#FAF6F0] to-[#F4EDE4] border-r border-[#EDE0D0] fixed inset-y-0 left-0 z-40 shadow-2xl">
    <div class="flex flex-col w-full p-6 space-y-8 h-full overflow-y-auto">
        <!-- Logo & User Info -->
        <div class="relative">
            <div class="absolute -top-4 -left-4 w-24 h-24 bg-[#D4A017]/10 rounded-full blur-3xl"></div>
            <div class="relative glass rounded-3xl p-5 border border-white/60 shadow-inner">
                <div class="flex items-center gap-4">
                    @php
                        $avatarUrl = $user?->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null;
                        $initial = $user ? strtoupper(mb_substr($user->name, 0, 1)) : 'A';
                    @endphp
                    <div class="relative">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="h-14 w-14 rounded-2xl border-2 border-white object-cover shadow-lg">
                        @else
                            <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white font-black text-2xl shadow-inner">
                                {{ $initial }}
                            </div>
                        @endif
                        <div class="absolute -bottom-0.5 -right-0.5 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full ring-2 ring-white/70"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-display text-2xl font-semibold tracking-tight text-[#3F2B1B]">Alter Studio</p>
                        <p class="text-xs uppercase tracking-widest text-[#8B7359] flex items-center gap-2 mt-1">
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
                   class="group flex items-center gap-4 px-5 py-4 rounded-2xl text-sm font-medium transition-all duration-300
                          {{ $isActive 
                              ? 'bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white shadow-lg shadow-[#D4A017]/40' 
                              : 'text-[#5C432C] hover:bg-white hover:shadow-md hover:text-[#D4A017]' }}">
                    <i class="{{ $item['icon'] }} text-xl {{ $isActive ? 'text-white' : 'text-[#8B7359] group-hover:text-[#D4A017]' }}"></i>
                    <span class="flex-1">{{ $item['label'] }}</span>
                    @if($isActive)
                        <span class="w-2 h-2 bg-white rounded-full shadow"></span>
                    @endif
                </a>
            @endforeach
        </nav>

        <!-- Logout -->
        <div class="pt-6 border-t border-[#EDE0D0]">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-4 px-5 py-4 rounded-2xl border border-[#E1D3C5] text-[#5C432C] hover:bg-red-500 hover:text-white hover:border-red-500 transition-all group">
                    <i class="fa-solid fa-arrow-right-from-bracket group-hover:rotate-180 transition-transform"></i>
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
           class="fixed inset-y-0 left-0 z-50 w-80 bg-gradient-to-b from-[#FAF6F0] to-[#F4EDE4] border-r border-[#EDE0D0] shadow-2xl overflow-y-auto">
        <div class="flex flex-col w-full p-6 space-y-8 h-full">
            <div class="flex items-center justify-between">
                <p class="font-display text-2xl font-semibold text-[#3F2B1B]">Alter Studio</p>
                <button @click="mobileSidebar = false" class="w-10 h-10 flex items-center justify-center rounded-2xl hover:bg-white/70 text-[#8B7359]">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>

            <!-- Mobile User Info -->
            <div class="glass rounded-3xl p-5 border border-white/60">
                <div class="flex items-center gap-4">
                    @php
                        $avatarUrl = $user?->avatar_path ? Storage::disk('public')->url($user->avatar_path) : null;
                        $initial = $user ? strtoupper(mb_substr($user->name, 0, 1)) : 'A';
                    @endphp
                    <div class="relative">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="Avatar" class="h-12 w-12 rounded-2xl border-2 border-white object-cover">
                        @else
                            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white font-bold text-xl">
                                {{ $initial }}
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
                       class="group flex items-center gap-4 px-5 py-4 rounded-2xl text-sm font-medium transition-all
                              {{ $isActive ? 'bg-gradient-to-r from-[#D4A017] to-[#E07A5F] text-white shadow-lg' : 'text-[#5C432C] hover:bg-white hover:text-[#D4A017]' }}">
                        <i class="{{ $item['icon'] }} text-xl {{ $isActive ? 'text-white' : 'text-[#8B7359]' }}"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <!-- Mobile Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-4 px-5 py-4 rounded-2xl border border-[#E1D3C5] text-[#5C432C] hover:bg-red-500 hover:text-white transition-all">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    <span class="flex-1 text-left font-medium">Keluar</span>
                </button>
            </form>
        </div>
    </aside>
</div>