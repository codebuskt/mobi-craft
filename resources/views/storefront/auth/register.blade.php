@extends('storefront.layouts.guest')

@section('title', __('storefront.auth.register_title') . ' - ' . $shopProfile->business_name)

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
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.auth.register_title') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('storefront.auth.register_desc') }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-8">
            <form method="POST" action="{{ route('storefront.account.register.post') }}" class="space-y-5">
                @csrf

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.name') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="name">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.email') }} <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="email">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.phone') }} <span class="text-red-500">*</span></label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="tel" placeholder="01XXXXXXXXX">
                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.password') }} <span class="text-red-500">*</span></label>
                        <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="new-password" minlength="8">
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.confirm_password') }} <span class="text-red-500">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="new-password">
                        @error('password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="agree_terms" value="1" class="mt-1 w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" required>
                        <div class="text-sm text-gray-700">
                            {{ __('storefront.auth.agree_terms', ['terms_url' => '#', 'privacy_url' => '#']) }}
                        </div>
                    </label>
                    @error('agree_terms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                    {{ __('storefront.auth.register') }}
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">{{ __('storefront.auth.has_account') }}</p>
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