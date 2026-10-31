<header class="sticky top-0 z-30 bg-white border-b border-gray-200">
    <!-- Top Bar -->
    @if($shopProfile->getSetting('show_top_bar', true))
    <div class="bg-primary-600 text-white py-1.5 hidden sm:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between text-sm">
            <div class="flex items-center gap-4">
                @if($shopProfile->phone)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        {{ $shopProfile->phone }}
                    </span>
                @endif
                @if($shopProfile->email)
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        {{ $shopProfile->email }}
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <!-- Language Switcher -->
                <div class="relative">
                    <button @click="langOpen = !langOpen" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-white/10 transition-colors" aria-label="Language">
                        <span class="text-sm font-medium">{{ app()->getLocale() === 'bn' ? 'বাংলা' : 'English' }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="langOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-1 w-32 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5" style="display: none;">
                        <a href="{{ route('storefront.locale', 'en') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">English</a>
                        <a href="{{ route('storefront.locale', 'bn') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">বাংলা</a>
                    </div>
                </div>

                <!-- Cart Link -->
                <a href="{{ route('storefront.cart.index') }}" class="relative flex items-center gap-1 px-2 py-1 rounded hover:bg-white/10 transition-colors" @click.prevent="cartDrawerOpen = true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span class="text-sm font-medium">{{ __('storefront.header.cart') }}</span>
                    <span x-data="{ count: 0 }" x-init="fetch('{{ route('storefront.cart.count') }}').then(r => r.json()).then(d => count = d.count)" class="absolute -top-1 -right-1 w-5 h-5 bg-white text-primary-600 text-xs font-bold rounded-full flex items-center justify-center" x-text="count" x-show="count > 0"></span>
                </a>

                <!-- Auth Links -->
                @auth('customer')
                    <div class="relative">
                        <button @click="userMenuOpen = !userMenuOpen" class="flex items-center gap-1 px-2 py-1 rounded hover:bg-white/10 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="text-sm font-medium">{{ auth()->guard('customer')->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="userMenuOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5" style="display: none;">
                            <a href="{{ route('storefront.account.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('storefront.header.my_account') }}</a>
                            <a href="{{ route('storefront.account.orders') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('storefront.header.orders') }}</a>
                            <a href="{{ route('storefront.account.repairs') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('storefront.header.repairs') }}</a>
                            <hr class="my-1">
                            <form action="{{ route('storefront.account.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">{{ __('storefront.header.logout') }}</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('storefront.account.login') }}" class="px-2 py-1 text-sm font-medium hover:underline">{{ __('storefront.header.login') }}</a>
                    <a href="{{ route('storefront.account.register') }}" class="px-3 py-1.5 bg-white text-primary-600 text-sm font-medium rounded hover:bg-gray-100 transition-colors">{{ __('storefront.header.register') }}</a>
                @endauth
            </div>
        </div>
    </div>
    @endif

    <!-- Main Header -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('storefront.home') }}" class="flex items-center gap-2" aria-label="{{ $shopProfile->business_name }}">
                    @if($shopProfile->logo)
                        <img src="{{ asset('storage/'.$shopProfile->logo) }}" alt="{{ $shopProfile->business_name }}" class="h-10 w-auto">
                    @else
                        <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-xl">{{ strtoupper(substr($shopProfile->business_name ?? 'M', 0, 1)) }}</span>
                        </div>
                    @endif
                    <span class="hidden lg:block text-xl font-bold text-gray-900">{{ $shopProfile->business_name }}</span>
                </a>
            </div>

            <!-- Search Bar -->
            <div class="hidden lg:flex flex-1 max-w-2xl mx-8">
                <div class="relative w-full">
                    <button @click="searchOpen = true" class="w-full px-4 py-2.5 bg-gray-100 rounded-lg text-left focus:outline-none hover:bg-gray-200 transition-colors" aria-label="Search products">
                        <div class="flex items-center gap-2 text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <span>{{ __('storefront.search.placeholder') }}</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Mobile Actions -->
            <div class="flex items-center gap-2 lg:hidden">
                <button @click="searchOpen = true" class="p-2 text-gray-500 hover:text-gray-700" aria-label="Search">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </button>
                <a href="{{ route('storefront.cart.index') }}" @click.prevent="cartDrawerOpen = true" class="relative p-2 text-gray-500 hover:text-gray-700" aria-label="Cart">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span x-data="{ count: 0 }" x-init="fetch('{{ route('storefront.cart.count') }}').then(r => r.json()).then(d => count = d.count)" class="absolute -top-1 -right-1 w-5 h-5 bg-primary-600 text-white text-xs font-bold rounded-full flex items-center justify-center" x-text="count" x-show="count > 0"></span>
                </a>
                <button @click="mobileMenuOpen = true" class="p-2 text-gray-500 hover:text-gray-700" aria-label="Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="hidden lg:block border-t border-gray-200" aria-label="Main navigation">
            <div class="flex items-center justify-between h-12">
                <div class="flex items-center gap-1 overflow-x-auto pb-2">
                    <a href="{{ route('storefront.home') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors whitespace-nowrap">{{ __('storefront.header.home') }}</a>
                    <a href="{{ route('storefront.products.index') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors whitespace-nowrap">{{ __('storefront.header.products') }}</a>
                    <a href="{{ route('storefront.categories.index') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors whitespace-nowrap">{{ __('storefront.header.categories') }}</a>
                    <a href="{{ route('storefront.products.featured') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors whitespace-nowrap">{{ __('storefront.header.featured') }}</a>
                    <a href="{{ route('storefront.products.on-sale') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors whitespace-nowrap">{{ __('storefront.header.on_sale') }}</a>
                    @if($shopProfile->getSetting('enable_repair_tracking', true))
                        <a href="{{ route('storefront.repairs.track') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors whitespace-nowrap">{{ __('storefront.header.track_repair') }}</a>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @auth('customer')
                        <a href="{{ route('storefront.account.orders') }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors whitespace-nowrap">{{ __('storefront.header.my_orders') }}</a>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Mobile Navigation -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-in-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2" class="lg:hidden border-t border-gray-200 bg-white" style="display: none;">
            <div class="px-4 py-4 space-y-2">
                <a href="{{ route('storefront.home') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.home') }}</a>
                <a href="{{ route('storefront.products.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.products') }}</a>
                <a href="{{ route('storefront.categories.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.categories') }}</a>
                <a href="{{ route('storefront.products.featured') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.featured') }}</a>
                <a href="{{ route('storefront.products.on-sale') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.on_sale') }}</a>
                @if($shopProfile->getSetting('enable_repair_tracking', true))
                    <a href="{{ route('storefront.repairs.track') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.track_repair') }}</a>
                @endif
                @auth('customer')
                    <hr class="my-2">
                    <a href="{{ route('storefront.account.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.my_account') }}</a>
                    <a href="{{ route('storefront.account.orders') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.orders') }}</a>
                    <a href="{{ route('storefront.account.repairs') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.repairs') }}</a>
                    <form action="{{ route('storefront.account.logout') }}" method="POST" @submit="mobileMenuOpen = false">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 text-base font-medium text-red-600 hover:bg-red-50 rounded-md">{{ __('storefront.header.logout') }}</button>
                    </form>
                @else
                    <hr class="my-2">
                    <a href="{{ route('storefront.account.login') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md">{{ __('storefront.header.login') }}</a>
                    <a href="{{ route('storefront.account.register') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-base font-medium text-primary-600 hover:bg-primary-50 rounded-md">{{ __('storefront.header.register') }}</a>
                @endauth
            </div>
        </div>
    </div>
</header>