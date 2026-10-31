@props(['product', 'variant' => 'default'])

@php
    $price = $product->getEffectiveSalePriceAttribute();
    $originalPrice = $product->sale_price;
    $onSale = $product->isOnSpecial();
    $inStock = $product->available_quantity > 0;
    $lowStock = $product->isLowStock();
    $thumbnail = $product->thumbnail ? asset('storage/'.$product->thumbnail) : asset('images/placeholder-product.svg');
@endphp

@if($variant === 'grid')
    <!-- Grid View Product Card -->
    <article class="group bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-lg hover:border-primary-200 transition-all duration-300 flex flex-col h-full">
        <!-- Badges -->
        <div class="absolute top-3 left-3 right-3 flex flex-wrap gap-1.5 z-10 pointer-events-none">
            @if($onSale)
                <span class="bg-red-500 text-white text-xs font-medium px-2 py-1 rounded-full">
                    -{{ round(100 - ($price / $originalPrice * 100)) }}%
                </span>
            @endif
            @if($product->is_featured)
                <span class="bg-amber-500 text-white text-xs font-medium px-2 py-1 rounded-full">
                    {{ __('storefront.products.featured') }}
                </span>
            @endif
            @if(!$inStock)
                <span class="bg-gray-500 text-white text-xs font-medium px-2 py-1 rounded-full">
                    {{ __('storefront.products.out_of_stock') }}
                </span>
            @elseif($lowStock)
                <span class="bg-yellow-500 text-white text-xs font-medium px-2 py-1 rounded-full">
                    {{ __('storefront.products.low_stock') }}
                </span>
            @endif
        </div>

        <!-- Image -->
        <div class="relative aspect-square bg-gray-50 overflow-hidden">
            <a href="{{ route('storefront.products.show', $product->slug) }}" class="block">
                <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
            </a>

            <!-- Quick Add to Cart -->
            @if($inStock)
            <div class="absolute bottom-3 left-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-y-2 group-hover:translate-y-0">
                <button wire:click="addToCart({{ $product->id }})" class="w-full bg-primary-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-primary-700 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    {{ __('storefront.products.add_to_cart') }}
                </button>
            </div>
            @endif

            <!-- Wishlist/Compare -->
            <div class="absolute top-3 right-3 flex flex-col gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <button class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow-md hover:bg-white transition-colors" aria-label="Add to wishlist">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </button>
                <button class="bg-white/90 backdrop-blur-sm p-2 rounded-full shadow-md hover:bg-white transition-colors" aria-label="Compare">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 flex flex-col flex-1">
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $product->category->name ?? '' }}</p>
                @if($product->brand)
                    <p class="text-xs text-gray-400 whitespace-nowrap">{{ $product->brand->name }}</p>
                @endif
            </div>

            <h3 class="font-medium text-gray-900 line-clamp-2 mb-2 group-hover:text-primary-600 transition-colors">
                <a href="{{ route('storefront.products.show', $product->slug) }}">{{ $product->name }}</a>
            </h3>

            @if($product->short_description)
                <p class="text-sm text-gray-500 line-clamp-2 mb-3 flex-1">{{ $product->short_description }}</p>
            @else
                <div class="flex-1"></div>
            @endif

            <!-- Price -->
            <div class="flex items-baseline gap-2">
                <span class="text-lg font-bold text-gray-900">{{ shopProfile->formatCurrency($price) }}</span>
                @if($onSale)
                    <span class="text-sm text-gray-400 line-through">{{ shopProfile->formatCurrency($originalPrice) }}</span>
                @endif
            </div>
        </div>
    </article>

@elseif($variant === 'list')
    <!-- List View Product Card -->
    <article class="group bg-white rounded-xl border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-300 flex flex-col sm:flex-row">
        <div class="relative sm:w-48 flex-shrink-0 aspect-square sm:aspect-auto">
            <a href="{{ route('storefront.products.show', $product->slug) }}">
                <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            </a>
            @if($onSale)
                <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-medium px-2 py-1 rounded">
                    -{{ round(100 - ($price / $originalPrice * 100)) }}%
                </span>
            @endif
        </div>

        <div class="p-4 flex flex-col flex-1">
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $product->category->name ?? '' }}</p>
                @if($product->brand)
                    <p class="text-xs text-gray-400">{{ $product->brand->name }}</p>
                @endif
            </div>

            <h3 class="font-medium text-gray-900 mb-2 group-hover:text-primary-600 transition-colors">
                <a href="{{ route('storefront.products.show', $product->slug) }}">{{ $product->name }}</a>
            </h3>

            @if($product->short_description)
                <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ $product->short_description }}</p>
            @endif

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-auto pt-3 border-t">
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-bold text-gray-900">{{ shopProfile->formatCurrency($price) }}</span>
                    @if($onSale)
                        <span class="text-sm text-gray-400 line-through">{{ shopProfile->formatCurrency($originalPrice) }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @if($inStock)
                        <button wire:click="addToCart({{ $product->id }})" class="bg-primary-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            {{ __('storefront.products.add_to_cart') }}
                        </button>
                    @else
                        <button disabled class="bg-gray-200 text-gray-500 px-4 py-2 rounded-lg font-medium cursor-not-allowed flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            {{ __('storefront.products.out_of_stock') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </article>

@elseif($variant === 'drawer')
    <!-- Cart Drawer Item -->
    <div class="flex gap-3 p-3 bg-gray-50 rounded-lg">
        <a href="{{ route('storefront.products.show', $product->slug) }}" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-white">
            <img src="{{ $thumbnail }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
        </a>

        <div class="flex-1 min-w-0">
            <h4 class="font-medium text-gray-900 truncate">{{ $product->name }}</h4>
            <p class="text-sm text-primary-600 font-semibold">{{ shopProfile->formatCurrency($price) }}</p>

            <div class="flex items-center gap-2 mt-2">
                <button wire:click="updateCartItem('{{ $product->cart_item_id }}', {{ $product->quantity - 1 }})" class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100" aria-label="Decrease quantity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                </button>
                <span class="w-8 text-center font-medium">{{ $product->quantity }}</span>
                <button wire:click="updateCartItem('{{ $product->cart_item_id }}', {{ $product->quantity + 1 }})" class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100" aria-label="Increase quantity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
                <button wire:click="removeFromCart('{{ $product->cart_item_id }}')" class="ml-auto text-gray-400 hover:text-red-500" aria-label="Remove">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
    </div>

@endif