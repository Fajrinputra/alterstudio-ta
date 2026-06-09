@php
    $user = Auth::user();
    $sidebar = (bool) $user;
@endphp

<nav x-data="{ open: false }" 
     class="bg-white/90 backdrop-blur-2xl border-b border-[#EDE0D0] text-[#5C432C] sticky top-0 z-50 shadow-sm">
    <div class="max-w-[1360px] mx-auto px-4 sm:px-5 lg:px-6">
        <div class="flex justify-between items-center h-12">
            
            <!-- Left Section -->
            <div class="flex items-center gap-3">
                @if($sidebar)
                    <button @click="$dispatch('toggle-sidebar')"
                            class="lg:hidden p-2 rounded-xl hover:bg-[#FAF6F0] transition-all active:scale-95">
                        <i class="fa-solid fa-bars text-[#D4A017] text-lg"></i>
                    </button>
                @endif
                
                @if(!$sidebar)
                    <div class="flex flex-col leading-none">
                        <span class="font-display text-xl font-bold tracking-tight text-[#3F2B1B]">Alter Studio</span>
                        <span class="mt-1 h-0.5 w-12 rounded-full bg-gradient-to-r from-[#D4A017] to-[#E07A5F]"></span>
                    </div>
                @endif
            </div>

            <!-- Desktop User Menu -->
            @if($user)
                <div class="hidden sm:flex items-center gap-2">
                    <div class="text-right px-2 py-0.5">
                        <div class="text-sm font-semibold text-[#3F2B1B]">{{ $user->name }}</div>
                        <div class="text-xs text-[#8B7359]">{{ $user->email }}</div>
                    </div>
                    
                    <a href="{{ route('profile.edit') }}"
                       class="group flex items-center gap-2 px-3.5 py-2 rounded-xl border border-[#E1D3C5] hover:border-[#D4A017] hover:bg-white hover:shadow-md transition-all duration-300">
                        <i class="fa-solid fa-user text-[#D4A017]"></i>
                        <span class="hidden lg:inline text-sm font-medium">Profil</span>
                    </a>
                </div>
            @endif

            <!-- Mobile Menu Button -->
            <div class="sm:hidden">
                <button @click="open = !open"
                        class="p-2 rounded-xl hover:bg-[#FAF6F0] text-[#D4A017] transition-all active:scale-95">
                    <i class="fa-solid fa-bars text-lg" :class="{'hidden': open, 'block': !open}"></i>
                    <i class="fa-solid fa-xmark text-lg" :class="{'block': open, 'hidden': !open}"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.away="open = false"
         class="sm:hidden border-t border-[#EDE0D0] bg-white/95 backdrop-blur-xl">
        <div class="px-4 py-3 space-y-1 text-sm">
            @if($user)
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                                      class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-[#FAF6F0] transition-colors">
                    <i class="fa-solid fa-house text-[#D4A017]"></i>
                    <span>Dashboard</span>
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('profile.edit')"
                                      class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-[#FAF6F0] transition-colors">
                    <i class="fa-solid fa-user text-[#D4A017]"></i>
                    <span>Profil</span>
                </x-responsive-nav-link>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button onclick="event.preventDefault(); this.closest('form').submit();"
                            class="w-full flex items-center gap-3 py-3 px-4 rounded-xl text-left hover:bg-red-50 hover:text-red-600 transition-colors">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            @else
                <x-responsive-nav-link :href="url('/')" 
                                      class="flex items-center gap-3 py-3 px-4 rounded-xl hover:bg-[#FAF6F0]">
                    <i class="fa-solid fa-house text-[#D4A017]"></i>
                    Beranda
                </x-responsive-nav-link>
            @endif
        </div>
    </div>
</nav>
