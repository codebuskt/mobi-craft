@extends('storefront.layouts.app')

@section('title', $shopProfile->business_name)

@section('content')
<!-- Hero Section -->
@if($banners && count($banners) > 0)
    <div class="relative">
        <div class="overflow-hidden">
            <div class="flex" x-data="{ currentSlide: 0 }" x-init="setInterval(() => currentSlide = (currentSlide + 1) % {{ count($banners) }}, 5000)">
                @foreach($banners as $index => $banner)
                    <div class="w-full flex-shrink-0 transition-transform duration-500" :class="{ 'translate-x-0': currentSlide === {{ $index }}, '-translate-x-full': currentSlide !== {{ $index }} }">
                        @if(isset($banner['image']))
                            <img src="{{ asset('storage/'.$banner['image']) }}" alt="{{ $banner['title'] ?? '' }}" class="w-full h-64 md:h-80 lg:h-96 object-cover">
                        @else
                            <div class="w-full h-64 md:h-80 lg:h-96 bg-gradient-to-r from-primary-600 to-primary-800 flex items-center justify-center">
                                <div class="text-center text-white px-8">
                                    <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4">{{ $banner['title'] ?? '' }}</h1>
                                    <p class="text-lg md:text-xl mb-6 opacity-90">{{ $banner['subtitle'] ?? '' }}</p>
                                    @if(isset($banner['button_text']) && isset($banner['button_url']))
                                        <a href="{{ $banner['button_url'] }}" class="inline-block bg-white text-primary-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">{{ $banner['button_text'] }}</a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Indicators -->
        @if(count($banners) > 1)
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                @foreach($banners as $index => $banner)
                    <button @click="currentSlide = {{ $index }}" class="w-2 h-2 rounded-full bg-white/50 hover:bg-white transition-colors" :class="{ 'bg-white': currentSlide === {{ $index }} }" aria-label="Slide {{ $index + 1 }}"></button>
                @endforeach
            </div>
        @endif
    </div>
@else
    <!-- Default Hero -->
    <section class="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 text-white py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    {{ $shopProfile->getSetting('hero_title', 'Welcome to ' . $shopProfile->business_name) }}
                </h1>
                <p class="text-lg md:text-xl mb-8 opacity-90 max-w-2xl">
                    {{ $shopProfile->getSetting('hero_subtitle', 'Your trusted destination for mobile phones, accessories, and expert repair services.') }}
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('storefront.products.index') }}" class="bg-white text-primary-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-colors">
                        {{ __('storefront.home.shop_now') }}
                    </a>
                    <a href="{{ route('storefront.repairs.track') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold text-lg hover:bg-white/10 transition-colors">
                        {{ __('storefront.home.track_repair') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
@endif

<!-- Featured Categories -->
@if($featuredCategories && $featuredCategories->count() > 0)
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-900">{{ __('storefront.home.featured_categories') }}</h2>
                <a href="{{ route('storefront.categories.index') }}" class="text-primary-600 hover:underline text-sm font-medium">{{ __('storefront.home.view_all') }}</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($featuredCategories as $category)
                    <a href="{{ route('storefront.categories.show', $category->slug) }}" class="group relative aspect-square rounded-xl overflow-hidden bg-gray-100">
                        @if($category->image)
                            <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-black/40 group-hover:bg-black/50 transition-colors duration-300 flex items-end p-4">
                            <div class="w-full">
                                <h3 class="text-white font-semibold text-lg">{{ $category->name }}</h3>
                                <p class="text-white/80 text-sm">{{ $category->products_count ?? 0 }} {{ __('storefront.home.products') }}</p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- On Sale Products -->
@if($onSaleProducts && $onSaleProducts->count() > 0)
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                    {{ __('storefront.home.on_sale') }}
                </h2>
                <a href="{{ route('storefront.products.on-sale') }}" class="text-primary-600 hover:underline text-sm font-medium">{{ __('storefront.home.view_all') }}</a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($onSaleProducts as $product)
                    @include('storefront.partials.product-card', ['product' => $product, 'variant' => 'grid'])
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- Featured Products -->
@if($featuredProducts && $featuredProducts->count() > 0)
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    {{ __('storefront.home.featured_products') }}
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    @include('storefront.partials.product-card', ['product' => $product, 'variant' => 'grid'])
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- New Arrivals -->
@if($newArrivals && $newArrivals->count() > 0)
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    {{ __('storefront.home.new_arrivals') }}
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($newArrivals as $product)
                    @include('storefront.partials.product-card', ['product' => $product, 'variant' => 'grid'])
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- Features/Trust Signals -->
<section class="py-12 bg-white border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
            <div class="p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-primary-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ __('storefront.home.free_shipping') }}</h3>
                <p class="text-sm text-gray-500">{{ __('storefront.home.free_shipping_desc') }}</p>
            </div>

            <div class="p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ __('storefront.home.secure_payment') }}</h3>
                <p class="text-sm text-gray-500">{{ __('storefront.home.secure_payment_desc') }}</p>
            </div>

            <div class="p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-amber-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ __('storefront.home.warranty') }}</h3>
                <p class="text-sm text-gray-500">{{ __('storefront.home.warranty_desc') }}</p>
            </div>

            <div class="p-6">
                <div class="w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ __('storefront.home.support') }}</h3>
                <p class="text-sm text-gray-500">{{ __('storefront.home.support_desc') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Signup -->
@if($shopProfile->getSetting('enable_newsletter', false))
    <section class="py-12 bg-primary-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">{{ __('storefront.home.newsletter_title') }}</h2>
            <p class="text-white/80 mb-6 max-w-2xl mx-auto">{{ __('storefront.home.newsletter_desc') }}</p>
            <form class="max-w-md mx-auto flex gap-2" action="#" method="POST">
                <input type="email" name="email" placeholder="{{ __('storefront.home.email_placeholder') }}" required class="flex-1 px-4 py-3 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-white">
                <button type="submit" class="bg-white text-primary-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors">{{ __('storefront.home.subscribe') }}</button>
            </form>
        </div>
    </section>
@endif
@endsection