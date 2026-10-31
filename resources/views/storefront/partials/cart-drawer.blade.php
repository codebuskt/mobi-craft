@php
    $cartItems = $cart['items'] ?? [];
    $totals = $totals ?? ['subtotal' => 0, 'tax_amount' => 0, 'shipping' => 0, 'discount' => 0, 'grand_total' => 0];
@endphp

@if(empty($cartItems))
    <div class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        <p class="mt-4 text-gray-500">{{ __('storefront.cart.empty') }}</p>
        <a href="{{ route('storefront.products.index') }}" class="mt-4 inline-block text-primary-600 hover:underline">{{ __('storefront.cart.continue_shopping') }}</a>
    </div>
@else
    <div class="space-y-3 max-h-96 overflow-y-auto">
        @foreach($cartItems as $item)
            @if(isset($item['product']) && $item['product'])
                <div class="flex gap-3">
                    <a href="{{ route('storefront.products.show', $item['product']->slug) }}" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-white">
                        <img src="{{ $item['product']->thumbnail ? asset('storage/'.$item['product']->thumbnail) : asset('images/placeholder-product.svg') }}" alt="{{ $item['product']->name }}" class="w-full h-full object-cover">
                    </a>

                    <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-gray-900 truncate">{{ $item['product']->name }}</h4>
                        @if($item['product']->isOnSpecial())
                            <p class="text-sm text-red-500 line-through">{{ shopProfile->formatCurrency($item['product']->sale_price) }}</p>
                        @endif
                        <p class="text-sm text-primary-600 font-semibold">{{ shopProfile->formatCurrency($item['price'] ?? $item['product']->getEffectiveSalePriceAttribute()) }}</p>

                        <div class="flex items-center gap-2 mt-2">
                            <button wire:click="updateCartItem('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})" class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100" aria-label="Decrease quantity">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </button>
                            <span class="w-8 text-center font-medium">{{ $item['quantity'] }}</span>
                            <button wire:click="updateCartItem('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})" class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100" aria-label="Increase quantity">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                            <button wire:click="removeFromCart('{{ $item['id'] }}')" class="ml-auto text-gray-400 hover:text-red-500" aria-label="Remove">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Cart Summary -->
    <div class="border-t pt-4 space-y-2">
        <div class="flex justify-between text-sm">
            <span>{{ __('storefront.cart.subtotal') }}</span>
            <span class="font-medium">{{ shopProfile->formatCurrency($totals['subtotal']) }}</span>
        </div>

        @if($totals['tax_amount'] > 0)
            <div class="flex justify-between text-sm">
                <span>{{ shopProfile->tax_label ?? 'VAT' }} ({{ shopProfile->default_tax_rate }}%)</span>
                <span class="font-medium">{{ shopProfile->formatCurrency($totals['tax_amount']) }}</span>
            </div>
        @endif

        @if($totals['shipping'] > 0)
            <div class="flex justify-between text-sm">
                <span>{{ __('storefront.cart.shipping') }}</span>
                <span class="font-medium">{{ shopProfile->formatCurrency($totals['shipping']) }}</span>
            </div>
        @endif

        @if($totals['discount'] > 0)
            <div class="flex justify-between text-sm text-green-600">
                <span>{{ __('storefront.cart.discount') }}</span>
                <span class="font-medium">-{{ shopProfile->formatCurrency($totals['discount']) }}</span>
            </div>
        @endif

        <div class="border-t pt-2 flex justify-between text-lg font-bold">
            <span>{{ __('storefront.cart.total') }}</span>
            <span>{{ shopProfile->formatCurrency($totals['grand_total']) }}</span>
        </div>
    </div>
@endif