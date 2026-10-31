@extends('storefront.layouts.app')

@section('title', __('storefront.checkout.title') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Progress Steps -->
        <div class="hidden lg:block mb-8">
            <div class="flex items-center">
                @php
                    $steps = [
                        ['key' => 'address', 'label' => __('storefront.checkout.step_address'), 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                        ['key' => 'shipping', 'label' => __('storefront.checkout.step_shipping'), 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                        ['key' => 'payment', 'label' => __('storefront.checkout.step_payment'), 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                        ['key' => 'review', 'label' => __('storefront.checkout.step_review'), 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ];
                    $currentStepIndex = array_search($currentStep, array_column($steps, 'key'));
                @endphp
                @foreach($steps as $index => $step)
                    <div class="relative flex-1">
                        <div class="flex flex-col items-center">
                            <div class="relative flex items-center justify-center w-10 h-10 rounded-full border-2
                                {{ $index < $currentStepIndex ? 'bg-primary-600 border-primary-600' : ($index === $currentStepIndex ? 'bg-primary-600 border-primary-600' : 'bg-white border-gray-300') }}
                                {{ $index > $currentStepIndex ? 'text-gray-400' : 'text-white' }}">
                                @if($index < $currentStepIndex)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                @else
                                    <span class="font-semibold text-sm">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <span class="mt-2 text-sm font-medium text-center
                                {{ $index <= $currentStepIndex ? 'text-gray-900' : 'text-gray-400' }}">
                                {{ $step['label'] }}
                            </span>
                        </div>
                        @if($index < count($steps) - 1)
                            <div class="absolute top-5 left-1/2 w-full h-0.5
                                {{ $index < $currentStepIndex ? 'bg-primary-600' : 'bg-gray-200' }}"
                                style="transform: translateX(-50%);"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Mobile Steps -->
        <div class="lg:hidden mb-6">
            <div class="overflow-x-auto pb-4">
                <div class="flex gap-4 min-w-max">
                    @foreach($steps as $index => $step)
                        <div class="flex flex-col items-center flex-shrink-0">
                            <div class="relative flex items-center justify-center w-10 h-10 rounded-full border-2
                                {{ $index < $currentStepIndex ? 'bg-primary-600 border-primary-600' : ($index === $currentStepIndex ? 'bg-primary-600 border-primary-600' : 'bg-white border-gray-300') }}
                                {{ $index > $currentStepIndex ? 'text-gray-400' : 'text-white' }}">
                                @if($index < $currentStepIndex)
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                @else
                                    <span class="font-semibold text-sm">{{ $index + 1 }}</span>
                                @endif
                            </div>
                            <span class="mt-1 text-xs font-medium text-center whitespace-nowrap
                                {{ $index <= $currentStepIndex ? 'text-gray-900' : 'text-gray-400' }}">
                                {{ $step['label'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-8">
                <form wire:submit.prevent="submitStep" class="space-y-6">
                    @if(session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Step: Address -->
                    @if($currentStep === 'address')
                        <div class="bg-white rounded-xl border border-gray-100 p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('storefront.checkout.step_address') }}</h2>

                            @if(auth()->guard('customer')->check() && auth()->guard('customer')->user()->addresses->count() > 0)
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">{{ __('storefront.checkout.select_saved_address') }}</label>
                                    <div class="space-y-2">
                                        @foreach(auth()->guard('customer')->user()->addresses as $address)
                                            <label class="flex items-start gap-3 p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-primary-300 transition-colors">
                                                <input type="radio" name="saved_address_id" value="{{ $address->id }}" class="mt-1 w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500" wire:model="selectedAddressId">
                                                <div class="flex-1">
                                                    <p class="font-medium text-gray-900">{{ $address->name }}, {{ $address->phone }}</p>
                                                    <p class="text-sm text-gray-600 mt-1">{{ $address->formatted_address }}</p>
                                                    @if($address->is_default)
                                                        <span class="inline-block mt-1 px-2 py-0.5 bg-primary-100 text-primary-700 text-xs rounded-full">{{ __('storefront.checkout.default') }}</span>
                                                    @endif
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <hr class="my-6">
                            @endif

                            <!-- Billing Address -->
                            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('storefront.checkout.billing_address') }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label for="billing_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.name') }} <span class="text-red-500">*</span></label>
                                    <input type="text" id="billing_name" wire:model="form.billing_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                    @error('form.billing_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="billing_phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.phone') }} <span class="text-red-500">*</span></label>
                                    <input type="tel" id="billing_phone" wire:model="form.billing_phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                    @error('form.billing_phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="billing_email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.email') }} <span class="text-red-500">*</span></label>
                                    <input type="email" id="billing_email" wire:model="form.billing_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                    @error('form.billing_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="billing_country" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.country') }} <span class="text-red-500">*</span></label>
                                    <select id="billing_country" wire:model="form.billing_country" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                        <option value="">{{ __('storefront.checkout.select_country') }}</option>
                                        <option value="BD">Bangladesh</option>
                                    </select>
                                    @error('form.billing_country') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="billing_address" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.address') }} <span class="text-red-500">*</span></label>
                                <textarea id="billing_address" wire:model="form.billing_address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required></textarea>
                                @error('form.billing_address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="billing_city" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.city') }} <span class="text-red-500">*</span></label>
                                    <input type="text" id="billing_city" wire:model="form.billing_city" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                </div>
                                <div>
                                    <label for="billing_state" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.state') }} <span class="text-red-500">*</span></label>
                                    <input type="text" id="billing_state" wire:model="form.billing_state" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                </div>
                                <div>
                                    <label for="billing_postal_code" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.postal_code') }} <span class="text-red-500">*</span></label>
                                    <input type="text" id="billing_postal_code" wire:model="form.billing_postal_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                </div>
                            </div>

                            <!-- Save address checkbox -->
                            @if(auth()->guard('customer')->check())
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="form.save_address" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="text-sm text-gray-700">{{ __('storefront.checkout.save_address') }}</span>
                                </label>
                            @endif
                        </div>

                    @elseif($currentStep === 'shipping')
                        <!-- Step: Shipping -->
                        <div class="bg-white rounded-xl border border-gray-100 p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('storefront.checkout.step_shipping') }}</h2>

                            <p class="text-gray-600 mb-6">{{ __('storefront.checkout.shipping_desc') }}</p>

                            @if($shippingMethods && $shippingMethods->count() > 0)
                                <div class="space-y-3">
                                    @foreach($shippingMethods as $method)
                                        <label class="flex items-start gap-4 p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-primary-300 transition-colors
                                            {{ $method['id'] === $selectedShippingMethod ? 'border-primary-500 bg-primary-50' : '' }}">
                                            <input type="radio" name="shipping_method" value="{{ $method['id'] }}" class="mt-1 w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500" wire:model="selectedShippingMethod">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <p class="font-medium text-gray-900">{{ $method['name'] }}</p>
                                                    <p class="font-semibold text-primary-600">
                                                        @if($method['price'] > 0)
                                                            {{ shopProfile->formatCurrency($method['price']) }}
                                                        @else
                                                            {{ __('storefront.checkout.free') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <p class="text-sm text-gray-500 mt-1">{{ $method['description'] }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $method['estimated_days'] }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500">
                                    {{ __('storefront.checkout.no_shipping_methods') }}
                                </div>
                            @endif
                        </div>

                    @elseif($currentStep === 'payment')
                        <!-- Step: Payment -->
                        <div class="bg-white rounded-xl border border-gray-100 p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('storefront.checkout.step_payment') }}</h2>

                            <p class="text-gray-600 mb-6">{{ __('storefront.checkout.payment_desc') }}</p>

                            <div class="space-y-3">
                                @foreach($paymentMethods as $method)
                                    <label class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg cursor-pointer hover:border-primary-300 transition-colors
                                        {{ $method['id'] === $selectedPaymentMethod ? 'border-primary-500 bg-primary-50' : '' }}">
                                        <input type="radio" name="payment_method" value="{{ $method['id'] }}" class="w-4 h-4 text-primary-600 border-gray-300 focus:ring-primary-500" wire:model="selectedPaymentMethod">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <span class="text-2xl">{{ $method['icon'] }}</span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $method['name'] }}</p>
                                                @if(isset($method['description']))
                                                    <p class="text-sm text-gray-500">{{ $method['description'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            @if($selectedPaymentMethod === 'cod')
                                <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                    <div class="flex items-start gap-3">
                                        <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <div>
                                            <p class="font-medium text-amber-800">{{ __('storefront.checkout.cod_notice_title') }}</p>
                                            <p class="text-sm text-amber-700 mt-1">{{ __('storefront.checkout.cod_notice') }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                    @elseif($currentStep === 'review')
                        <!-- Step: Review -->
                        <div class="bg-white rounded-xl border border-gray-100 p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('storefront.checkout.step_review') }}</h2>

                            <!-- Order Items -->
                            <div class="mb-6">
                                <h3 class="font-medium text-gray-900 mb-3">{{ __('storefront.checkout.order_items') }}</h3>
                                <div class="space-y-3 divide-y divide-gray-100">
                                    @foreach($cart['items'] as $item)
                                        @if(isset($item['product']) && $item['product'])
                                            <div class="flex gap-3 py-3">
                                                <a href="{{ route('storefront.products.show', $item['product']->slug) }}" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-white">
                                                    <img src="{{ $item['product']->thumbnail ? asset('storage/'.$item['product']->thumbnail) : asset('images/placeholder-product.svg') }}" alt="{{ $item['product']->name }}" class="w-full h-full object-cover">
                                                </a>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="font-medium text-gray-900 truncate">{{ $item['product']->name }}</h4>
                                                    <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }}</p>
                                                </div>
                                                <p class="font-semibold text-gray-900 whitespace-nowrap">{{ shopProfile->formatCurrency(($item['price'] ?? $item['product']->getEffectiveSalePriceAttribute()) * $item['quantity']) }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Addresses Summary -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ __('storefront.checkout.billing_address') }}</h4>
                                    <p class="text-sm text-gray-600">{{ $form['billing_name'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $form['billing_phone'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $form['billing_address'] }}, {{ $form['billing_city'] }}, {{ $form['billing_state'] }} {{ $form['billing_postal_code'] }}</p>
                                </div>
                                <div class="p-4 bg-gray-50 rounded-lg">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ __('storefront.checkout.shipping_method') }}</h4>
                                    @php $selectedMethod = collect($shippingMethods)->firstWhere('id', $selectedShippingMethod); @endphp
                                    <p class="text-sm text-gray-600">{{ $selectedMethod['name'] ?? '' }}</p>
                                    <p class="text-sm text-gray-600">{{ $selectedMethod['estimated_days'] ?? '' }}</p>
                                </div>
                            </div>

                            <!-- Payment Method Summary -->
                            <div class="p-4 bg-gray-50 rounded-lg mb-6">
                                <h4 class="font-medium text-gray-900 mb-2">{{ __('storefront.checkout.payment_method') }}</h4>
                                @php $selectedPayment = collect($paymentMethods)->firstWhere('id', $selectedPaymentMethod); @endphp
                                <p class="text-sm text-gray-600">{{ $selectedPayment['name'] ?? '' }}</p>
                            </div>

                            <!-- Terms & Conditions -->
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="form.agree_terms" class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" required>
                                <div class="text-sm text-gray-700">
                                    {{ __('storefront.checkout.agree_terms', ['terms_url' => '#', 'privacy_url' => '#']) }}
                                </div>
                            </label>
                            @error('form.agree_terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <!-- Navigation Buttons -->
                    <div class="flex flex-col sm:flex-row sm:justify-between gap-4 pt-4 border-t">
                        @if($currentStep !== 'address')
                            <button type="button" wire:click="previousStep" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                                {{ __('storefront.checkout.back') }}
                            </button>
                        @else
                            <div></div>
                        @endif

                        @if($currentStep !== 'review')
                            <button type="submit" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                                {{ __('storefront.checkout.continue') }}
                                <svg class="w-5 h-5 ml-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                            </button>
                        @else
                            <button type="submit" wire:click="placeOrder" class="px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors flex items-center justify-center gap-2"
                                :disabled="placingOrder">
                                @if($placingOrder)
                                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    {{ __('storefront.checkout.placing_order') }}
                                @else
                                    {{ __('storefront.checkout.place_order') }}
                                @endif
                            </button>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="lg:col-span-4">
                @if(!empty($cart['items']))
                    <div class="bg-white rounded-xl border border-gray-100 p-6 sticky top-24">
                        <h2 class="text-lg font-semibold text-gray-900 mb-6">{{ __('storefront.checkout.order_summary') }}</h2>

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

                            <div class="border-t pt-4 flex justify-between text-lg font-bold">
                                <span>{{ __('storefront.cart.total') }}</span>
                                <span>{{ shopProfile->formatCurrency($totals['grand_total']) }}</span>
                            </div>
                        </div>

                        <p class="mt-4 text-xs text-gray-500 text-center">{{ __('storefront.checkout.summary_note') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection