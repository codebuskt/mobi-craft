<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'bn' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? $shopProfile->business_name ?? 'MobiCraft Store' }}</title>

    <!-- Meta tags -->
    <meta name="description" content="{{ $description ?? $shopProfile->getSetting('meta_description', '') }}">
    <meta name="keywords" content="{{ $keywords ?? $shopProfile->getSetting('meta_keywords', '') }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $title ?? $shopProfile->business_name }}">
    <meta property="og:description" content="{{ $description ?? $shopProfile->getSetting('meta_description', '') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    @if($shopProfile->logo)
        <meta property="og:image" content="{{ asset('storage/'.$shopProfile->logo) }}">
    @endif

    <!-- Favicon -->
    @if($shopProfile->favicon)
        <link rel="icon" href="{{ asset('storage/'.$shopProfile->favicon) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if(app()->getLocale() === 'bn')
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @else
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif

    <!-- Tailwind CSS -->
    @vite(['resources/css/storefront.css', 'resources/js/storefront.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900" x-data="{ mobileMenuOpen: false, cartDrawerOpen: false, searchOpen: false }">
    <!-- Header -->
    @include('storefront.partials.header')

    <!-- Mobile Menu Overlay -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="mobileMenuOpen = false"></div>

    <!-- Cart Drawer -->
    <div x-show="cartDrawerOpen" x-transition:enter="transition ease-in-out duration-300" x-transition:enter-start="transform translate-x-full opacity-0" x-transition:enter-end="transform translate-x-0 opacity-100" x-transition:leave="transition ease-in-out duration-300" x-transition:leave-start="transform translate-x-0 opacity-100" x-transition:leave-end="transform translate-x-full opacity-0" class="fixed inset-y-0 right-0 w-full max-w-sm bg-white shadow-xl z-50 lg:w-96" @click.outside="cartDrawerOpen = false" role="dialog" aria-modal="true" aria-label="Shopping Cart">
        <div class="flex h-full flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h2 class="text-lg font-semibold">{{ __('storefront.cart.title') }}</h2>
                <button @click="cartDrawerOpen = false" class="text-gray-400 hover:text-gray-600" aria-label="Close cart">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4" id="cart-drawer-content">
                @include('storefront.partials.cart-drawer')
            </div>

            <div class="p-4 border-t bg-gray-50" id="cart-drawer-footer">
                @if(session('cart') && session('cart.items') && count(session('cart.items')) > 0)
                    <a href="{{ route('storefront.cart.index') }}" class="block w-full text-center py-3 px-4 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                        {{ __('storefront.cart.view_cart') }}
                    </a>
                    <a href="{{ route('storefront.checkout.index') }}" class="block w-full text-center mt-2 py-3 px-4 border border-primary-600 text-primary-600 font-medium rounded-lg hover:bg-primary-50 transition-colors">
                        {{ __('storefront.cart.checkout') }}
                    </a>
                @else
                    <p class="text-center text-gray-500 py-4">{{ __('storefront.cart.empty') }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div x-show="searchOpen" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-40 flex items-start justify-center pt-20" @click.outside="searchOpen = false" @keydown.escape.window="searchOpen = false">
        <div class="w-full max-w-2xl mx-4 bg-white rounded-xl shadow-xl overflow-hidden" @click.outside="searchOpen = false">
            <div class="p-4 border-b flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()" placeholder="{{ __('storefront.search.placeholder') }}" class="flex-1 bg-transparent border-none focus:outline-none text-lg" autofocus>
                <button @click="searchOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="max-h-96 overflow-y-auto" x-show="searchResults.length > 0" x-transition>
                <div class="p-4 divide-y">
                    <template x-for="product in searchResults" :key="product.id">
                        <a :href="product.url" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition-colors">
                            <img :src="product.thumbnail" :alt="product.name" class="w-16 h-16 object-cover rounded-lg">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-sm truncate" x-text="product.name"></p>
                                <p class="text-primary-600 font-semibold text-sm" x-text="product.price_formatted"></p>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
            <div class="p-4 border-t" x-show="searchResults.length === 0 && searchQuery.length > 2" x-transition>
                <p class="text-center text-gray-500">{{ __('storefront.search.no_results') }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @include('storefront.partials.footer')

    <!-- Scripts -->
    <script>
        // Global storefront data
        window.storefront = {
            tenantId: {{ app('tenant')->id ?? 'null' }},
            locale: '{{ app()->getLocale() }}',
            currencySymbol: '{{ $shopProfile->currency_symbol ?? '৳' }}',
            csrfToken: '{{ csrf_token() }}',
            routes: {
                cartAdd: '{{ route('storefront.cart.add') }}',
                cartUpdate: '{{ route('storefront.cart.update', ':id') }}',
                cartRemove: '{{ route('storefront.cart.remove', ':id') }}',
                cartCount: '{{ route('storefront.cart.count') }}',
                cartDrawer: '{{ route('storefront.cart.drawer') }}',
            }
        };
    </script>

    @stack('scripts')
</body>
</html>