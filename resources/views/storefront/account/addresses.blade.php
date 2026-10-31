@extends('storefront.layouts.app')

@section('title', __('storefront.account.addresses') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Sidebar Navigation -->
            <aside class="lg:col-span-3">
                @include('storefront.partials.account-nav', ['active' => 'addresses'])
            </aside>

            <!-- Addresses Content -->
            <div class="lg:col-span-9">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('storefront.account.addresses') }}</h1>
                    <button wire:click="showAddAddressModal" class="px-4 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('storefront.account.add_address') }}
                    </button>
                </div>

                <!-- Addresses List -->
                @if($addresses->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($addresses as $address)
                            <div class="bg-white rounded-xl border border-gray-100 p-6 relative">
                                @if($address->is_default)
                                    <span class="absolute top-4 right-4 px-2 py-0.5 bg-primary-100 text-primary-700 text-xs rounded-full font-medium">{{ __('storefront.account.default') }}</span>
                                @endif

                                <div class="mb-4">
                                    <h3 class="font-semibold text-gray-900">{{ $address->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $address->phone }}</p>
                                </div>

                                <address class="text-gray-600 not-italic space-y-1 mb-4">
                                    <p>{{ $address->address_line_1 }}</p>
                                    @if($address->address_line_2)
                                        <p>{{ $address->address_line_2 }}</p>
                                    @endif
                                    <p>{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
                                    <p>{{ $address->country }}</p>
                                </address>

                                <div class="flex items-center gap-2 pt-4 border-t">
                                    <button wire:click="editAddress({{ $address->id }})" class="flex-1 px-3 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-colors">
                                        {{ __('storefront.account.edit') }}
                                    </button>
                                    <form wire:submit.prevent="setDefaultAddress({{ $address->id }})">
                                        <button type="submit" class="flex-1 px-3 py-2 border border-primary-600 text-primary-600 rounded-lg font-medium text-sm hover:bg-primary-50 transition-colors">
                                            {{ __('storefront.account.set_default') }}
                                        </button>
                                    </form>
                                    <form wire:submit.prevent="deleteAddress({{ $address->id }})" class="flex-1">
                                        <button type="submit" class="w-full px-3 py-2 border border-red-300 text-red-600 rounded-lg font-medium text-sm hover:bg-red-50 transition-colors">
                                            {{ __('storefront.account.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('storefront.account.no_addresses') }}</h3>
                        <p class="mt-2 text-gray-500">{{ __('storefront.account.no_addresses_desc') }}</p>
                        <button wire:click="showAddAddressModal" class="mt-6 inline-block bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                            {{ __('storefront.account.add_address') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Address Modal -->
@if($showAddressModal)
<div class="fixed inset-0 z-50 overflow-y-auto" x-data="{}" x-show="showAddressModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeAddressModal"></div>

        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-show="showAddressModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900">{{ $editingAddress ? __('storefront.account.edit_address') : __('storefront.account.add_address') }}</h2>
                <button @click="closeAddressModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form wire:submit.prevent="saveAddress" class="space-y-4">
                <div>
                    <label for="address_name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.account.address_name') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="address_name" wire:model="addressForm.name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                    @error('addressForm.name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address_phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.auth.phone') }} <span class="text-red-500">*</span></label>
                    <input type="tel" id="address_phone" wire:model="addressForm.phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                    @error('addressForm.phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address_line_1" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.account.address_line_1') }} <span class="text-red-500">*</span></label>
                    <input type="text" id="address_line_1" wire:model="addressForm.address_line_1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                    @error('addressForm.address_line_1') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="address_line_2" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.account.address_line_2') }}</label>
                    <input type="text" id="address_line_2" wire:model="addressForm.address_line_2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.city') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="city" wire:model="addressForm.city" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.state') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="state" wire:model="addressForm.state" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.postal_code') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="postal_code" wire:model="addressForm.postal_code" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.checkout.country') }} <span class="text-red-500">*</span></label>
                        <select id="country" wire:model="addressForm.country" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                            <option value="BD">Bangladesh</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_default" wire:model="addressForm.is_default" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                    <label for="is_default" class="text-sm text-gray-700">{{ __('storefront.account.set_as_default') }}</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" @click="closeAddressModal" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        {{ __('storefront.account.cancel') }}
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors"
                        :disabled="savingAddress">
                        @if($savingAddress)
                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            {{ __('storefront.account.saving') }}
                        @else
                            {{ $editingAddress ? __('storefront.account.update') : __('storefront.account.save') }}
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection