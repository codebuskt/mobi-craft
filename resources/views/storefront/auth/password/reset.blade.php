@extends('storefront.layouts.guest')

@section('title', __('storefront.auth.reset_password') . ' - ' . $shopProfile->business_name)

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
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.auth.reset_password') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('storefront.auth.reset_password_desc') }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-8">
            <form wire:submit.prevent="resetPassword" class="space-y-6">
                @if($error)
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        {{ $error }}
                    </div>
                @endif

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.email') }} <span class="text-red-500">*</span></label>
                    <input type="email" id="email" wire:model="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="email">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.new_password') }} <span class="text-red-500">*</span></label>
                    <input type="password" id="password" wire:model="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="new-password" minlength="8">
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.confirm_password') }} <span class="text-red-500">*</span></label>
                    <input type="password" id="password_confirmation" wire:model="password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="new-password">
                    @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-primary-700 transition-colors"
                    :disabled="resetting">
                    @if($resetting)
                        <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('storefront.auth.resetting') }}
                    @else
                        {{ __('storefront.auth.reset_password') }}
                    @endif
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">{{ __('storefront.auth.back_to_login') }}</p>
                <a href="{{ route('storefront.account.login') }}" class="text-primary-600 hover:underline font-medium">{{ __('storefront.auth.login') }}</a>
            </div>
        </div>

        <div class="mt-6 text-center text-sm text-gray-500">
            <p>{{ __('storefront.auth.need_help') }}</p>
            <a href="{{ route('storefront.contact') }}" class="text-primary-600 hover:underline">{{ __('storefront.auth.contact_support') }}</a>
        </div>
    </div>
</div>
@endsection