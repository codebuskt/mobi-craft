<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'bn' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? $shopProfile->business_name ?? 'MobiCraft Store' }}</title>

    <!-- Meta tags -->
    <meta name="description" content="{{ $description ?? shopProfile->getSetting('meta_description', '') }}">
    <meta name="keywords" content="{{ $keywords ?? shopProfile->getSetting('meta_keywords', '') }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $title ?? shopProfile->business_name }}">
    <meta property="og:description" content="{{ $description ?? shopProfile->getSetting('meta_description', '') }}">
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
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <!-- Main Content -->
    <main class="min-h-screen">
        {{ $slot }}
    </main>

    <!-- Footer -->
    @include('storefront.partials.footer')

    <!-- Scripts -->
    <script>
        // Global storefront data
        window.storefront = {
            tenantId: {{ app('tenant')->id ?? 'null' }},
            locale: '{{ app()->getLocale() }}',
            currencySymbol: '{{ shopProfile->currency_symbol ?? '৳' }}',
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