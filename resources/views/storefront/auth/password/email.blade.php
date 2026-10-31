@extends('storefront.layouts.guest')

@section('title', __('storefront.auth.forgot_password') . ' - ' . $shopProfile->business_name)

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
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.auth.forgot_password') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('storefront.auth.forgot_password_desc') }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 p-8">
            <form method="POST" action="{{ route('storefront.account.password.email') }}" class="space-y-6">
                @csrf

                @if(session('status'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.email') }} <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="email">
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                    {{ __('storefront.auth.send_reset_link') }}
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