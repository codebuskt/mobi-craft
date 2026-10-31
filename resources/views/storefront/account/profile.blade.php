@extends('storefront.layouts.app')

@section('title', __('storefront.account.profile') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Sidebar Navigation -->
            <aside class="lg:col-span-3">
                @include('storefront.partials.account-nav', ['active' => 'profile'])
            </aside>

            <!-- Profile Content -->
            <div class="lg:col-span-9">
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('storefront.account.profile') }}</h1>

                    <form wire:submit.prevent="updateProfile" class="space-y-6 max-w-2xl">
                        @if(session('success'))
                            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if($error)
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                                {{ $error }}
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.name') }} <span class="text-red-500">*</span></label>
                                <input type="text" id="name" wire:model="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.email') }} <span class="text-red-500">*</span></label>
                                <input type="email" id="email" wire:model="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.phone') }} <span class="text-red-500">*</span></label>
                                <input type="tel" id="phone" wire:model="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Language Preference -->
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.account.language') }}</label>
                            <select id="language" wire:model="language" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="en">{{ __('storefront.lang.english') }}</option>
                                <option value="bn">{{ __('storefront.lang.bangla') }}</option>
                            </select>
                        </div>

                        <div class="pt-4 border-t">
                            <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors"
                                :disabled="saving">
                                @if($saving)
                                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    {{ __('storefront.account.saving') }}
                                @else
                                    {{ __('storefront.account.save_changes') }}
                                @endif
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Section -->
                <div class="mt-6 bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">{{ __('storefront.account.change_password') }}</h2>

                    <form wire:submit.prevent="changePassword" class="space-y-5 max-w-2xl">
                        @if(session('password_success'))
                            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                                {{ session('password_success') }}
                            </div>
                        @endif

                        @if($passwordError)
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                                {{ $passwordError }}
                            </div>
                        @endif

                        <div>
                            <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.account.current_password') }} <span class="text-red-500">*</span></label>
                            <input type="password" id="current_password" wire:model="current_password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="current-password">
                            @error('current_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.account.new_password') }} <span class="text-red-500">*</span></label>
                            <input type="password" id="new_password" wire:model="new_password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="new-password" minlength="8">
                            @error('new_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.account.confirm_new_password') }} <span class="text-red-500">*</span></label>
                            <input type="password" id="new_password_confirmation" wire:model="new_password_confirmation" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required autocomplete="new-password">
                            @error('new_password_confirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors"
                            :disabled="changingPassword">
                            @if($changingPassword)
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ __('storefront.account.updating') }}
                            @else
                                {{ __('storefront.account.update_password') }}
                            @endif
                        </button>
                    </form>
                </div>

                <!-- Delete Account -->
                <div class="mt-6 bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">{{ __('storefront.account.delete_account') }}</h2>
                    <p class="text-gray-600 mb-4">{{ __('storefront.account.delete_account_desc') }}</p>
                    <button wire:click="confirmDeleteAccount" class="px-6 py-2 border border-red-300 text-red-600 rounded-lg font-medium hover:bg-red-50 transition-colors">
                        {{ __('storefront.account.delete_account') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection