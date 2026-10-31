@extends('storefront.layouts.guest')

@section('title', __('storefront.auth.login_title') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <a href="{{ route('storefront.home') }}" class="inline-block mb-6">
                @if($shopProfile->logo)
                    <img src="{{ asset('storage/'.$shopProfile->logo) }}" alt="{{ $shopProfile->business_name }}" class="h-12 mx-auto">
                @else
                    <div class="w-16 h-16 mx-auto rounded-xl bg-primary-600 flex items-center justify-center">
                        <span class="text-2xl font-bold text-white">{{ strtoupper($shopProfile->business_name[0]) }}</span>
                    </div>
                @endif
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.auth.login_title') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('storefront.auth.login_desc') }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-8">
            <form wire:submit.prevent="login" class="space-y-6">
                @if($loginError)
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        {{ $loginError }}
                    </div>
                @endif

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.email') }} <span class="text-red-500">*</span></label>
                    <input type="email" id="email" wire:model="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="email">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">{{ __('storefront.auth.password') }} <span class="text-red-500">*</span></label>
                        <a href="{{ route('storefront.account.password.request') }}" class="text-sm text-primary-600 hover:underline">{{ __('storefront.auth.forgot_password') }}</a>
                    </div>
                    <input type="password" id="password" wire:model="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="current-password">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="remember" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                        <span class="text-sm text-gray-700">{{ __('storefront.auth.remember_me') }}</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-primary-700 transition-colors"
                    :disabled="loggingIn">
                    @if($loggingIn)
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('storefront.auth.logging_in') }}
                    @else
                        {{ __('storefront.auth.login') }}
                    @endif
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">{{ __('storefront.auth.no_account') }}</p>
                <a href="{{ route('storefront.account.register') }}" class="text-primary-600 hover:underline font-medium">{{ __('storefront.auth.register') }}</a>
            </div>

            @if(shopProfile->getSetting('enable_guest_checkout', true))
                <div class="mt-6 pt-6 border-t text-center">
                    <p class="text-sm text-gray-500">{{ __('storefront.auth.guest_checkout') }}</p>
                    <a href="{{ route('storefront.checkout.index') }}" class="text-primary-600 hover:underline font-medium">{{ __('storefront.auth.continue_as_guest') }}</a>
                </div>
            @endif
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>{{ __('storefront.auth.need_help') }}</p>
            <a href="{{ route('storefront.contact') }}" class="text-primary-600 hover:underline">{{ __('storefront.auth.contact_support') }}</a>
        </div>
    </div>
</div>
@endsection