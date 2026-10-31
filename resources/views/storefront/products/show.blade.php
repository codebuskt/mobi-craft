@extends('storefront.layouts.app')

@section('title', $product->name . ' - ' . $shopProfile->business_name)

@section('description', $product->short_description ?? $product->description)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumbs -->
        <nav class="mb-6" aria-label="Breadcrumb">
            <ol class="flex items-center gap-2 text-sm text-gray-500">
                <li><a href="{{ route('storefront.home') }}" class="hover:text-primary-600">{{ __('storefront.header.home') }}</a></li>
                <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></li>
                <li><a href="{{ route('storefront.products.index') }}" class="hover:text-primary-600">{{ __('storefront.products.title') }}</a></li>
                <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></li>
                <li><a href="{{ route('storefront.categories.show', $product->category->slug) }}" class="hover:text-primary-600">{{ $product->category->name }}</a></li>
                <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg></li>
                <li class="text-gray-900 font-medium truncate max-w-xs">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Product Gallery -->
            <div class="lg:col-span-7">
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <!-- Main Image -->
                    <div class="aspect-square bg-gray-50 relative">
                        @if($product->images && count($product->images) > 0)
                            <div x-data="{ currentImage: 0 }" class="h-full">
                                <img :src="images[currentImage]" alt="{{ $product->name }}" class="w-full h-full object-contain p-8" x-transition>
                                @if(count($product->images) > 1)
                                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                                        @foreach($product->images as $index => $image)
                                            <button @click="currentImage = {{ $index }}" class="w-2 h-2 rounded-full bg-white/50 hover:bg-white transition-colors" :class="{ 'bg-white': currentImage === {{ $index }} }" aria-label="Image {{ $index + 1 }}"></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <img src="{{ $product->thumbnail ? asset('storage/'.$product->thumbnail) : asset('images/placeholder-product.svg') }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-8">
                        @endif
                    </div>

                    <!-- Thumbnails -->
                    @if($product->images && count($product->images) > 1)
                        <div class="p-4 border-t flex gap-2 overflow-x-auto">
                            @foreach($product->images as $index => $image)
                                <button @click="currentImage = {{ $index }}" class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 {{ $index === 0 ? 'border-primary-500' : 'border-transparent' }} hover:border-primary-300 transition-colors" aria-label="View image {{ $index + 1 }}">
                                    <img src="{{ asset('storage/'.$image) }}" alt="{{ $product->name }} {{ $index + 1 }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Product Badges -->
                <div class="mt-4 flex flex-wrap gap-2">
                    @if($product->isOnSpecial())
                        <span class="px-3 py-1 bg-red-100 text-red-700 text-sm font-medium rounded-full">{{ __('storefront.products.on_sale') }}</span>
                    @endif
                    @if($product->is_featured)
                        <span class="px-3 py-1 bg-amber-100 text-amber-700 text-sm font-medium rounded-full">{{ __('storefront.products.featured') }}</span>
                    @endif
                    @if($product->is_serialized)
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">{{ __('storefront.products.serialized') }}</span>
                    @endif
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded-full">{{ $product->type_label }}</span>
                </div>
            </div>

            <!-- Product Info -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-xl border border-gray-100 p-6 sticky top-24">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            @if($product->brand)
                                <p class="text-sm text-primary-600 font-medium">{{ $product->brand->name }}</p>
                            @endif
                            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $product->name }}</h1>
                        </div>
                        @if($product->isOnSpecial())
                            <span class="px-3 py-1 bg-red-100 text-red-700 text-sm font-medium rounded-full flex-shrink-0">{{ __('storefront.products.on_sale') }}</span>
                        @endif
                    </div>

                    <!-- SKU -->
                    <p class="text-sm text-gray-500 mb-4">{{ __('storefront.products.sku') }}: <span class="font-mono">{{ $product->sku }}</span></p>

                    <!-- Rating -->
                    @if($product->average_rating > 0)
                        <div class="flex items-center gap-2 mb-4">
                            <div class="flex items-center text-amber-500">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= $product->average_rating ? 'fill-current' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.969-.57-.567-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endfor
                            </div>
                            <span class="text-sm text-gray-600">{{ $product->average_rating }} ({{ $product->reviews_count }} {{ __('storefront.products.reviews') }})</span>
                        </div>
                    @endif

                    <!-- Price -->
                    <div class="mb-6">
                        @if($product->isOnSpecial())
                            <div class="flex items-baseline gap-3 mb-2">
                                <span class="text-3xl font-bold text-gray-900">{{ shopProfile->formatCurrency($product->getEffectiveSalePriceAttribute()) }}</span>
                                <span class="text-xl text-gray-400 line-through">{{ shopProfile->formatCurrency($product->sale_price) }}</span>
                                <span class="px-2 py-1 bg-red-100 text-red-700 text-sm font-medium rounded-full">
                                    -{{ round(100 - ($product->getEffectiveSalePriceAttribute() / $product->sale_price * 100)) }}%
                                </span>
                            </div>
                            @if($product->special_price_end)
                                <p class="text-sm text-gray-500">{{ __('storefront.products.offer_ends', ['date' => $product->special_price_end->format('d M Y')]) }}</p>
                            @endif
                        @else
                            <span class="text-3xl font-bold text-gray-900">{{ shopProfile->formatCurrency($product->getEffectiveSalePriceAttribute()) }}</span>
                        @endif

                        @if($product->tax_inclusive)
                            <p class="text-sm text-gray-500 mt-1">{{ __('storefront.products.tax_inclusive') }}</p>
                        @elseif($product->tax_rate > 0)
                            <p class="text-sm text-gray-500 mt-1">{{ __('storefront.products.tax_exclusive', ['rate' => $product->tax_rate]) }}</p>
                        @endif
                    </div>

                    <!-- Stock Status -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        @if($product->available_quantity > 0)
                            @if($product->isLowStock())
                                <div class="flex items-center gap-2 text-yellow-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span class="font-medium">{{ __('storefront.products.low_stock_alert', ['count' => $product->available_quantity]) }}</span>
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-green-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="font-medium">{{ __('storefront.products.in_stock', ['count' => $product->available_quantity]) }}</span>
                                </div>
                            @endif
                        @else
                            <div class="flex items-center gap-2 text-red-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <span class="font-medium">{{ __('storefront.products.out_of_stock') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Short Description -->
                    @if($product->short_description)
                        <div class="mb-6 text-gray-600">{{ $product->short_description }}</div>
                    @endif

                    <!-- Add to Cart Form -->
                    @if($product->canSell())
                        <form wire:submit.prevent="addToCart({{ $product->id }})" class="space-y-4">
                            <!-- Quantity Selector -->
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">{{ __('storefront.products.quantity') }}</label>
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="decrementQuantity" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-100" aria-label="Decrease quantity">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                    </button>
                                    <input type="number" id="quantity" wire:model="quantity" min="1" max="{{ $product->available_quantity }}" class="w-20 h-10 text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" value="1">
                                    <button type="button" wire:click="incrementQuantity" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-100" aria-label="Increase quantity">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                    <span class="text-sm text-gray-500 ml-2">({{ __('storefront.products.max_available', ['count' => $product->available_quantity]) }})</span>
                                </div>
                            </div>

                            <!-- Add to Cart Button -->
                            <button type="submit" class="w-full bg-primary-600 text-white py-3 px-6 rounded-lg font-semibold text-lg hover:bg-primary-700 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                {{ __('storefront.products.add_to_cart') }}
                            </button>

                            <!-- Buy Now Button -->
                            <button type="button" wire:click="buyNow({{ $product->id }})" class="w-full border-2 border-primary-600 text-primary-600 py-3 px-6 rounded-lg font-semibold text-lg hover:bg-primary-50 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h8a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h3"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 10v6m0 0l3-3m-3 3l-3-3"></path></svg>
                                {{ __('storefront.products.buy_now') }}
                            </button>
                        </form>
                    @else
                        <div class="space-y-3">
                            <button disabled class="w-full bg-gray-200 text-gray-500 py-3 px-6 rounded-lg font-semibold text-lg cursor-not-allowed flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                {{ __('storefront.products.out_of_stock') }}
                            </button>

                            <button disabled class="w-full border-2 border-gray-200 text-gray-400 py-3 px-6 rounded-lg font-semibold text-lg cursor-not-allowed flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h8a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h3"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 10v6m0 0l3-3m-3 3l-3-3"></path></svg>
                                {{ __('storefront.products.buy_now') }}
                            </button>
                        </div>
                    @endif

                    <!-- Wishlist/Share -->
                    <div class="mt-6 pt-6 border-t flex items-center gap-4">
                        <button class="flex items-center gap-2 text-gray-500 hover:text-red-500 transition-colors" aria-label="Add to wishlist">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            <span class="text-sm">{{ __('storefront.products.add_to_wishlist') }}</span>
                        </button>
                        <button class="flex items-center gap-2 text-gray-500 hover:text-primary-600 transition-colors" aria-label="Share product">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                            <span class="text-sm">{{ __('storefront.products.share') }}</span>
                        </button>
                    </div>

                    <!-- Trust Signals -->
                    <div class="mt-6 pt-6 border-t space-y-3 text-sm text-gray-600">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ __('storefront.products.genuine_products') }}</span>
                        </div>
                        @if($product->warranty_months > 0)
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <span>{{ __('storefront.products.warranty_months', ['months' => $product->warranty_months]) }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            <span>{{ __('storefront.products.easy_returns') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Section -->
        <div class="mt-12">
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-100" x-data="{ activeTab: 'description' }">
                    <nav class="flex -mb-px" aria-label="Product tabs">
                        <button @click="activeTab = 'description'" :class="activeTab === 'description' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 border-b-2 font-medium text-sm transition-colors">{{ __('storefront.products.description') }}</button>
                        @if($product->specifications && count($product->specifications) > 0)
                            <button @click="activeTab = 'specifications'" :class="activeTab === 'specifications' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 border-b-2 font-medium text-sm transition-colors">{{ __('storefront.products.specifications') }}</button>
                        @endif
                        <button @click="activeTab = 'reviews'" :class="activeTab === 'reviews' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-6 py-4 border-b-2 font-medium text-sm transition-colors">{{ __('storefront.products.reviews') }} ({{ $product->reviews_count ?? 0 }})</button>
                    </nav>
                </div>

                <!-- Tab Panels -->
                <div class="p-6">
                    <!-- Description -->
                    <div x-show="activeTab === 'description'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
                        <div class="prose prose-gray max-w-none">
                            @if($product->description)
                                {!! $product->description !!}
                            @else
                                <p class="text-gray-500">{{ __('storefront.products.no_description') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Specifications -->
                    <div x-show="activeTab === 'specifications'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
                        @if($product->specifications && count($product->specifications) > 0)
                            <dl class="divide-y divide-gray-100">
                                @foreach($product->specifications as $key => $value)
                                    <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                        <dt class="font-medium text-gray-900">{{ $key }}</dt>
                                        <dd class="text-gray-600">{{ $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @else
                            <p class="text-gray-500">{{ __('storefront.products.no_specifications') }}</p>
                        @endif
                    </div>

                    <!-- Reviews -->
                    <div x-show="activeTab === 'reviews'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h16M8 12l-5.5 5.5M8 12l-5.5-5.5"></path></svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('storefront.products.no_reviews') }}</h3>
                            <p class="mt-2 text-gray-500">{{ __('storefront.products.be_first_review') }}</p>
                            <button class="mt-4 text-primary-600 hover:underline font-medium">{{ __('storefront.products.write_review') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts && $relatedProducts->count() > 0)
            <section class="mt-16">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">{{ __('storefront.products.related_products') }}</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $product)
                        @include('storefront.partials.product-card', ['product' => $product, 'variant' => 'grid'])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize image gallery
        const images = @json($product->images ?? []);
        if (images.length > 0) {
            Alpine.store('productGallery', {
                currentImage: 0,
                images: images.map(img => '/storage/' + img),
                setImage(index) {
                    this.currentImage = index;
                }
            });
        }
    });
</script>
@endpush