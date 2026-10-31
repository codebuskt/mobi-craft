@extends('storefront.layouts.app')

@section('title', __('storefront.orders.lookup_title') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-xl border border-gray-100 p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">{{ __('storefront.orders.lookup_title') }}</h1>
                <p class="text-gray-500 mt-2">{{ __('storefront.orders.lookup_desc') }}</p>
            </div>

            <form wire:submit.prevent="lookupOrder" class="space-y-4">
                @if($lookupError)
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        {{ $lookupError }}
                    </div>
                @endif

                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.orders.order_number') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="order_number" wire:model="orderNumber" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="off">
                    @error('orderNumber') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.orders.phone') }} <span class="text-red-500">*</span></label>
                    <input type="tel" id="phone" wire:model="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="tel">
                    @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-primary-700 transition-colors"
                    :disabled="lookingUp">
                    @if($lookingUp)
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('storefront.orders.looking_up') }}
                    @else
                        {{ __('storefront.orders.lookup') }}
                    @endif
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">{{ __('storefront.orders.login_instead') }}</p>
                <a href="{{ route('storefront.account.login') }}" class="text-primary-600 hover:underline font-medium">{{ __('storefront.orders.login') }}</a>
            </div>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>{{ __('storefront.orders.need_help') }}</p>
            <a href="{{ route('storefront.contact') }}" class="text-primary-600 hover:underline">{{ __('storefront.orders.contact_support') }}</a>
        </div>
    </div>
</div>
@endsection