@extends('storefront.layouts.app')

@section('title', __('storefront.cart.title') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.cart.title') }}</h1>
            <p class="text-gray-500 mt-1">{{ count($cart['items'] ?? []) }} {{ __('storefront.cart.items_in_cart') }}</p>
        </div>

        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-8">
                @if(empty($cart['items']))
                    <!-- Empty Cart -->
                    <div class="bg-white rounded-xl border border-gray-100 p-12 text-center">
                        <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <h2 class="mt-6 text-xl font-semibold text-gray-900">{{ __('storefront.cart.empty') }}</h2>
                        <p class="mt-2 text-gray-500">{{ __('storefront.cart.empty_desc') }}</p>
                        <a href="{{ route('storefront.products.index') }}" class="mt-6 inline-block bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                            {{ __('storefront.cart.continue_shopping') }}
                        </a>
                    </div>
                @else
                    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('storefront.cart.items') }}</h2>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach($cart['items'] as $item)
                                @if(isset($item['product']) && $item['product'])
                                    <div class="p-6 flex gap-4">
                                        <!-- Product Image -->
                                        <a href="{{ route('storefront.products.show', $item['product']->slug) }}" class="flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden bg-white">
                                            <img src="{{ $item['product']->thumbnail ? asset('storage/'.$item['product']->thumbnail) : asset('images/placeholder-product.svg') }}" alt="{{ $item['product']->name }}" class="w-full h-full object-cover">
                                        </a>

                                        <!-- Product Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <h3 class="font-medium text-gray-900 truncate">
                                                        <a href="{{ route('storefront.products.show', $item['product']->slug) }}" class="hover:text-primary-600">{{ $item['product']->name }}</a>
                                                    </h3>
                                                    @if($item['product']->brand)
                                                        <p class="text-sm text-gray-500">{{ $item['product']->brand->name }}</p>
                                                    @endif
                                                    @if($item['product']->isOnSpecial())
                                                        <p class="text-sm text-red-500 line-through">{{ shopProfile->formatCurrency($item['product']->sale_price) }}</p>
                                                    @endif
                                                    <p class="text-lg font-semibold text-primary-600 mt-1">{{ shopProfile->formatCurrency($item['price'] ?? $item['product']->getEffectiveSalePriceAttribute()) }}</p>
                                                </div>

                                                <!-- Quantity Controls -->
                                                <div class="flex items-center gap-2 ml-4">
                                                    <button wire:click="updateCartItem('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-100" aria-label="Decrease quantity">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                                    </button>
                                                    <input type="number" value="{{ $item['quantity'] }}" min="1" max="{{ $item['product']->available_quantity }}" class="w-16 text-center border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" wire:model.debounce.500ms="cartItems.{{ $item['id'] }}.quantity" wire:change="updateCartItem('{{ $item['id'] }}', $event.target.value)">
                                                    <button wire:click="updateCartItem('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})" class="w-10 h-10 flex items-center justify-center border border-gray-300 rounded-lg hover:bg-gray-100" aria-label="Increase quantity">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- Line Total -->
                                            <div class="mt-4 flex items-center justify-between">
                                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                                    <span>{{ __('storefront.cart.subtotal') }}</span>
                                                    <span class="font-semibold text-gray-900">{{ shopProfile->formatCurrency(($item['price'] ?? $item['product']->getEffectiveSalePriceAttribute()) * $item['quantity']) }}</span>
                                                </div>
                                                <button wire:click="removeFromCart('{{ $item['id'] }}')" class="text-red-500 hover:text-red-700 text-sm font-medium flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    {{ __('storefront.cart.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <!-- Cart Actions -->
                        <div class="p-6 border-t flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <a href="{{ route('storefront.products.index') }}" class="text-primary-600 hover:underline font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                {{ __('storefront.cart.continue_shopping') }}
                            </a>
                            <div class="flex items-center gap-3">
                                <button wire:click="clearCart" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                    {{ __('storefront.cart.clear') }}
                                </button>
                                <a href="{{ route('storefront.checkout.index') }}" class="px-6 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                                    {{ __('storefront.cart.proceed_to_checkout') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-4">
                @if(!empty($cart['items']))
                    <div class="bg-white rounded-xl border border-gray-100 p-6 sticky top-24">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">{{ __('storefront.cart.order_summary') }}</h2>

                        <div class="space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('storefront.cart.subtotal') }}</span>
                                <span class="font-medium text-gray-900">{{ shopProfile->formatCurrency($totals['subtotal']) }}</span>
                            </div>

                            @if($totals['tax_amount'] > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ shopProfile->tax_label ?? 'VAT' }} ({{ shopProfile->default_tax_rate }}%)</span>
                                    <span class="font-medium text-gray-900">{{ shopProfile->formatCurrency($totals['tax_amount']) }}</span>
                                </div>
                            @endif

                            @if($totals['shipping'] > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('storefront.cart.shipping') }}</span>
                                    <span class="font-medium text-gray-900">{{ shopProfile->formatCurrency($totals['shipping']) }}</span>
                                </div>
                            @else
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('storefront.cart.shipping') }}</span>
                                    <span class="font-medium text-green-600">{{ __('storefront.cart.free') }}</span>
                                </div>
                            @endif

                            @if($totals['discount'] > 0)
                                <div class="flex justify-between text-sm text-green-600">
                                    <span>{{ __('storefront.cart.discount') }}</span>
                                    <span class="font-medium">-{{ shopProfile->formatCurrency($totals['discount']) }}</span>
                                </div>
                            @endif

                            @if($cart['coupon'])
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">{{ __('storefront.cart.coupon_applied', ['code' => $cart['coupon']['code']]) }}</span>
                                    <button wire:click="removeCoupon" class="text-red-500 hover:text-red-700 text-xs">
                                        {{ __('storefront.cart.remove_coupon') }}
                                    </button>
                                </div>
                            @else
                                <!-- Coupon Input -->
                                <div class="border-t pt-4">
                                    <form wire:submit.prevent="applyCoupon" class="flex gap-2">
                                        <input type="text" wire:model="couponCode" placeholder="{{ __('storefront.cart.coupon_placeholder') }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors">
                                            {{ __('storefront.cart.apply') }}
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <div class="border-t pt-4 flex justify-between text-lg font-bold">
                                <span>{{ __('storefront.cart.total') }}</span>
                                <span>{{ shopProfile->formatCurrency($totals['grand_total']) }}</span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <a href="{{ route('storefront.checkout.index') }}" class="w-full mt-6 bg-primary-600 text-white py-3 px-6 rounded-lg font-semibold text-lg hover:bg-primary-700 transition-colors text-center block">
                            {{ __('storefront.cart.proceed_to_checkout') }}
                        </a>

                        <!-- Trust Signals -->
                        <div class="mt-6 pt-6 border-t space-y-3 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ __('storefront.cart.secure_checkout') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <span>{{ __('storefront.cart.warranty') }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <span>{{ __('storefront.cart.easy_returns') }}</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection