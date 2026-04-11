@php
    $user = Auth::user();
    $sidebar = (bool) $user;
@endphp

<nav x-data="{ open: false }" 
     class="bg-white/90 backdrop-blur-2xl border-b border-[#EDE0D0] text-[#5C432C] sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            
            <!-- Left Section -->
            <div class="flex items-center gap-3">
                @if($sidebar)
                    <button @click="$dispatch('toggle-sidebar')" 
                            class="lg:hidden p-3 rounded-2xl hover:bg-[#FAF6F0] transition-all active:scale-95">
                        <i class="fa-solid fa-bars text-[#D4A017] text-2xl"></i>
                    </button>
                @endif
                
                @if(!$sidebar)
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-br from-[#D4A017] to-[#E07A5F] rounded-2xl blur-xl opacity-40"></div>
                            <div class="relative h-10 w-10 rounded-2xl bg-gradient-to-br from-[#D4A017] to-[#E07A5F] flex items-center justify-center text-white font-black text-2xl shadow-inner">
                                A
                            </div>
                        </div>
                        <span class="font-display font-semibold text-2xl tracking-tighter text-[#3F2B1B]">Alter Studio</span>
                    </div>
                @endif
            </div>

            <!-- Desktop User Menu -->
            @if($user)
                <div class="hidden sm:flex items-center gap-5">
                    <div class="text-right bg-white border border-[#EDE0D0] px-5 py-2.5 rounded-3xl">
                        <div class="text-sm font-semibold text-[#3F2B1B]">{{ $user->name }}</div>
                        <div class="text-xs text-[#8B7359]">{{ $user->email }}</div>
                    </div>
                    
                    <a href="{{ route('profile.edit') }}"
                       class="group flex items-center gap-3 px-6 py-3 rounded-3xl border border-[#E1D3C5] hover:border-[#D4A017] hover:bg-white hover:shadow-md transition-all duration-300">
                        <i class="fa-solid fa-user text-[#D4A017]"></i>
                        <span class="hidden lg:inline font-medium">Profil</span>
                    </a>
                </div>
            @endif

            <!-- Mobile Menu Button -->
            <div class="sm:hidden">
                <button @click="open = !open"
                        class="p-3 rounded-2xl hover:bg-[#FAF6F0] text-[#D4A017] transition-all active:scale-95">
                    <i class="fa-solid fa-bars text-2xl" :class="{'hidden': open, 'block': !open}"></i>
                    <i class="fa-solid fa-xmark text-2xl" :class="{'block': open, 'hidden': !open}"></i>
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
        <div class="px-4 py-5 space-y-1">
            @if($user)
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                                      class="flex items-center gap-3 py-3.5 px-5 rounded-2xl hover:bg-[#FAF6F0] transition-colors">
                    <i class="fa-solid fa-house text-[#D4A017]"></i>
                    <span>Dashboard</span>
                </x-responsive-nav-link>
                
                <x-responsive-nav-link :href="route('profile.edit')"
                                      class="flex items-center gap-3 py-3.5 px-5 rounded-2xl hover:bg-[#FAF6F0] transition-colors">
                    <i class="fa-solid fa-user text-[#D4A017]"></i>
                    <span>Profil</span>
                </x-responsive-nav-link>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button onclick="event.preventDefault(); this.closest('form').submit();"
                            class="w-full flex items-center gap-3 py-3.5 px-5 rounded-2xl text-left hover:bg-red-50 hover:text-red-600 transition-colors">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            @else
                <x-responsive-nav-link :href="url('/')" 
                                      class="flex items-center gap-3 py-3.5 px-5 rounded-2xl hover:bg-[#FAF6F0]">
                    <i class="fa-solid fa-house text-[#D4A017]"></i>
                    Beranda
                </x-responsive-nav-link>
            @endif
        </div>
    </div>
</nav>