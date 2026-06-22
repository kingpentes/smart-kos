<nav class="bg-white/80 backdrop-blur-xl border-b border-slate-200/60 shadow-sm z-30 shrink-0 sticky top-0 transition-all duration-300" x-data="{ mobileMenuOpen: false, profileDropdownOpen: false }" @click.outside="mobileMenuOpen = false">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center hover:opacity-90 transition-opacity" aria-label="SMART KOST">
                    <img src="{{ asset('assets/iconjpeg.jpeg') }}" alt="SMART KOST Logo" class="h-14 w-auto object-contain rounded">
                </a>
                
                <!-- Desktop Navigation (Left side, next to logo) -->
                <div class="hidden lg:block ml-10">
                    <div class="flex items-baseline space-x-4">
                        <a href="{{ route('boarding-houses.search') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('boarding-houses.search') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Cari Kos</a>
                        
                        @auth
                            @php $role = auth()->user()->role->value ?? ''; @endphp
                            @if($role === 'tenant')
                                <a href="{{ route('tenant.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('tenant.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Dashboard</a>
                                <a href="{{ route('tenant.bookings.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('tenant.bookings.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Riwayat Booking</a>
                                <a href="{{ route('subscriptions.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('subscriptions.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Langganan AI</a>
                            @elseif($role === 'owner')
                                <a href="{{ route('owner.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('owner.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Dashboard</a>
                                <a href="{{ route('owner.listings.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('owner.listings.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Kelola Kos</a>
                                <a href="{{ route('owner.bookings.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('owner.bookings.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Booking Masuk</a>
                                <a href="{{ route('subscriptions.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('subscriptions.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Langganan AI</a>
                            @elseif($role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Dashboard</a>
                                <a href="{{ route('admin.listings.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.listings.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Review Listing</a>
                                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Kelola Pengguna</a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
            
            <!-- Right side (Profile / Login / Notifications) -->
            <div class="hidden lg:flex items-center ml-4 space-x-3">
                @auth
                    @php $role = auth()->user()->role->value ?? ''; @endphp
                    
                    <!-- Notifications Dropdown -->
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" type="button" class="relative p-2 text-slate-500 hover:text-blue-600 focus:outline-none transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                    {{ auth()->user()->unreadNotifications->count() > 9 ? '9+' : auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>
                        
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg py-1 border border-slate-100 z-50"
                             style="display: none;">
                            <div class="px-4 py-2 border-b border-slate-100 flex justify-between items-center">
                                <span class="font-bold text-slate-800 text-sm">Notifikasi</span>
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Tandai semua dibaca</button>
                                    </form>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                                    <div class="px-4 py-3 border-b border-slate-50 hover:bg-slate-50 transition-colors {{ $notification->read_at ? 'opacity-60' : 'bg-blue-50/30' }}">
                                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="block w-full text-left">
                                            @csrf
                                            <button type="submit" class="w-full text-left">
                                                <p class="text-sm font-semibold text-slate-800">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                                                <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $notification->data['message'] ?? '' }}</p>
                                                <p class="text-[10px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-slate-500">
                                        Tidak ada notifikasi.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Profile menu -->
                    <div class="relative">
                        <div>
                            <button type="button" class="relative flex max-w-xs items-center rounded-full bg-blue-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" @click="profileDropdownOpen = !profileDropdownOpen" @click.outside="profileDropdownOpen = false">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-800 font-bold overflow-hidden shadow-sm hover:ring-2 hover:ring-blue-200 transition-all">
                                    @if(auth()->user()->google_avatar)
                                        <img src="{{ auth()->user()->google_avatar }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                                    @else
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    @endif
                                </div>
                            </button>
                        </div>
                        <div x-show="profileDropdownOpen" 
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-xl bg-white py-2 shadow-xl ring-1 ring-black/5 focus:outline-none border border-slate-100"
                                style="display: none;">
                            <div class="px-4 py-2.5 text-xs text-slate-500 border-b border-slate-100 mb-1">
                                <div class="font-bold text-slate-900 truncate">{{ auth()->user()->name }}</div>
                                <div class="truncate opacity-70">{{ auth()->user()->email }}</div>
                            </div>
                            
                            <!-- Role Based Quick Links in Dropdown to save Navbar space -->
                            @if($role === 'tenant')
                                <a href="{{ route('tenant.invoices.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">Tagihan Saya</a>
                                <a href="{{ route('tenant.complaints.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors border-b border-slate-50">Keluhan</a>
                            @elseif($role === 'owner')
                                <a href="{{ route('owner.invoices.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">Tagihan & Piutang</a>
                                <a href="{{ route('owner.complaints.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">Daftar Keluhan</a>
                                <a href="{{ route('owner.reports.financial') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors border-b border-slate-50">Analisa Keuangan</a>
                            @endif

                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">Pengaturan Profil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 hover:text-rose-700 font-medium transition-colors">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}" class="rounded-md px-3 py-2 font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700">Login</a>
                        <x-ui.button href="{{ route('register') }}" class="px-3 py-2 text-sm shadow-md shadow-blue-500/20">Register</x-ui.button>
                    </div>
                @endauth
            </div>
            
            <!-- Mobile menu button -->
            <div class="-mr-2 flex lg:hidden items-center">
                <button type="button" class="relative inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" @click="mobileMenuOpen = !mobileMenuOpen">
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">Open main menu</span>
                    <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" x-show="!mobileMenuOpen">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" x-show="mobileMenuOpen" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="lg:hidden border-t border-blue-50 bg-white" x-show="mobileMenuOpen" style="display: none;">
        <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3 shadow-inner">
            <a href="{{ route('boarding-houses.search') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('boarding-houses.search') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Cari Kos Baru</a>
            @auth
                @php $role = auth()->user()->role->value ?? ''; @endphp
                @if($role === 'tenant')
                    <a href="{{ route('tenant.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('tenant.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Dashboard</a>
                    <a href="{{ route('tenant.bookings.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('tenant.bookings.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Riwayat Booking</a>
                    <a href="{{ route('tenant.invoices.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('tenant.invoices.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Tagihan</a>
                    <a href="{{ route('subscriptions.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('subscriptions.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Langganan AI</a>
                    <a href="{{ route('tenant.complaints.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('tenant.complaints.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Keluhan Saya</a>
                @elseif($role === 'owner')
                    <a href="{{ route('owner.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('owner.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Dashboard</a>
                    <a href="{{ route('owner.listings.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('owner.listings.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Kelola Kos</a>
                    <a href="{{ route('owner.bookings.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('owner.bookings.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Booking Masuk</a>
                    <a href="{{ route('owner.invoices.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('owner.invoices.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Tagihan</a>
                    <a href="{{ route('subscriptions.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('subscriptions.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Langganan AI</a>
                    <a href="{{ route('owner.complaints.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('owner.complaints.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Keluhan</a>
                    <a href="{{ route('owner.reports.financial') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('owner.reports.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Laporan Keuangan</a>
                @elseif($role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Dashboard</a>
                    <a href="{{ route('admin.listings.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('admin.listings.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Review Listing</a>
                    <a href="{{ route('admin.users.index') }}" class="block rounded-md px-3 py-2 text-base font-medium {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700' }}">Kelola Pengguna</a>
                @endif
            @endauth
        </div>
        
        @auth
        <div class="border-t border-blue-100 pb-3 pt-4">
            <div class="flex items-center px-5">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-800 font-bold overflow-hidden shadow-sm">
                        @if(auth()->user()->google_avatar)
                            <img src="{{ auth()->user()->google_avatar }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                        @else
                            {{ substr(auth()->user()->name, 0, 1) }}
                        @endif
                    </div>
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium leading-none text-slate-900">{{ auth()->user()->name }}</div>
                    <div class="text-sm font-medium leading-none text-slate-500 mt-1">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1 px-2">
                <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 border border-slate-100 mb-2 bg-white">Pengaturan Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left rounded-md px-3 py-2 text-base font-medium text-red-600 hover:bg-red-50 hover:text-red-700 border border-red-50">Logout</button>
                </form>
            </div>
        </div>
        @else
        <div class="border-t border-blue-100 pb-3 pt-4 px-5 flex flex-col gap-2">
            <a href="{{ route('login') }}" class="block text-center rounded-md px-3 py-2 text-base font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-700 border border-slate-200">Login</a>
            <x-ui.button href="{{ route('register') }}" class="block text-center px-3 py-2 text-base">Register</x-ui.button>
        </div>
        @endauth
    </div>
</nav>
