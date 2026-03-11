@php
    $user = Auth::user();
    $sidebar = $user && $user->role !== \App\Enums\Role::CLIENT;
@endphp

<nav x-data="{ open: false }" class="bg-white/80 backdrop-blur-xl border-b border-[#e3d5c4]/50 text-[#5b422b] sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            {{-- Left section dengan hamburger menu --}}
            <div class="flex items-center gap-2">
                @if($sidebar)
                    <button @click="$dispatch('toggle-sidebar')" class="lg:hidden p-2.5 rounded-lg hover:bg-[#eee0d1] transition-colors">
                        <i class="fa-solid fa-bars text-[#6c4f32] text-lg"></i>
                    </button>
                @endif
                
                @if(!$sidebar)
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-[#b58042] to-[#8b5b2e] rounded-xl blur-sm opacity-50"></div>
                            <div class="relative h-9 w-9 rounded-xl bg-gradient-to-br from-[#b58042] to-[#8b5b2e] flex items-center justify-center text-white font-bold">
                                AS
                            </div>
                        </div>
                        <span class="font-display font-semibold text-lg text-[#3f2b1b]">Alter Studio</span>
                    </div>
                @endif
            </div>

            {{-- Desktop User Menu --}}
            <div class="hidden sm:flex sm:items-center sm:space-x-4">
                <div class="text-right px-3 py-1.5 rounded-lg bg-[#fcf7f1]">
                    <div class="text-sm font-semibold text-[#3f2b1b]">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-[#8b7359]">{{ Auth::user()->email }}</div>
                </div>
                
                <a href="{{ route('profile.edit') }}" 
                   class="group relative px-5 py-2 rounded-full border border-[#d7c5b2] text-[#5b422b] hover:bg-white hover:shadow-md transition-all duration-200 overflow-hidden">
                    <span class="relative z-10 flex items-center gap-2">
                        <i class="fa-solid fa-user"></i>
                        <span class="hidden lg:inline">Profil</span>
                    </span>
                    <span class="absolute inset-0 bg-gradient-to-r from-[#b58042]/0 to-[#8b5b2e]/0 group-hover:from-[#b58042]/5 group-hover:to-[#8b5b2e]/5 transition-all"></span>
                </a>
            </div>

            {{-- Mobile Menu Button --}}
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" 
                        class="inline-flex items-center justify-center p-2.5 rounded-lg text-[#6c4f32] hover:text-[#3f2b1b] hover:bg-[#eee0d1] focus:outline-none transition-colors">
                    <i class="fa-solid fa-bars text-xl" :class="{'hidden': open, 'block': !open}"></i>
                    <i class="fa-solid fa-xmark text-xl" :class="{'block': open, 'hidden': !open}"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu Dropdown dengan animasi smooth --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.away="open = false"
         class="sm:hidden border-t border-[#e3d5c4] bg-white/95 backdrop-blur-md">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" 
                                  class="flex items-center gap-3 py-2.5 px-4 rounded-lg hover:bg-[#eee0d1]">
                <i class="fa-solid fa-chart-pie w-5 text-[#b58042]"></i>
                Dashboard
            </x-responsive-nav-link>
            
            <x-responsive-nav-link :href="route('profile.edit')" 
                                  class="flex items-center gap-3 py-2.5 px-4 rounded-lg hover:bg-[#eee0d1]">
                <i class="fa-solid fa-user w-5 text-[#b58042]"></i>
                Profil
            </x-responsive-nav-link>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();"
                        class="flex items-center gap-3 py-2.5 px-4 rounded-lg hover:bg-red-50 hover:text-red-600">
                    <i class="fa-solid fa-circle-right w-5"></i>
                    Keluar
                </x-responsive-nav-link>
            </form>
        </div>
    </div>
</nav>