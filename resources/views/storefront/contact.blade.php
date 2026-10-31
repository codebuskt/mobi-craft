@extends('storefront.layouts.app')

@section('title', __('storefront.contact.title') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl border border-gray-100 p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.contact.title') }}</h1>
                <p class="mt-2 text-gray-600">{{ __('storefront.contact.desc') }}</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.contact.info') }}</h3>
                    <div class="space-y-4">
                        @if($shopProfile->phone)
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">{{ __('storefront.contact.phone') }}</p>
                                    <a href="tel:{{ $shopProfile->phone }}" class="text-gray-900 hover:text-primary-600">{{ $shopProfile->phone }}</a>
                                </div>
                            </div>
                        @endif
                        @if($shopProfile->email)
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">{{ __('storefront.contact.email') }}</p>
                                    <a href="mailto:{{ $shopProfile->email }}" class="text-gray-900 hover:text-primary-600">{{ $shopProfile->email }}</a>
                                </div>
                            </div>
                        @endif
                        @if($shopProfile->address)
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">{{ __('storefront.contact.address') }}</p>
                                    <address class="text-gray-900 not-italic">{{ $shopProfile->full_address }}</address>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.contact.form_title') }}</h3>
                    <form class="space-y-4" action="#" method="POST">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.contact.name') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.contact.email') }} <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.contact.phone') }}</label>
                            <input type="tel" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.contact.subject') }} <span class="text-red-500">*</span></label>
                            <select id="subject" name="subject" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="">{{ __('storefront.contact.select_subject') }}</option>
                                <option value="general">{{ __('storefront.contact.general') }}</option>
                                <option value="support">{{ __('storefront.contact.support') }}</option>
                                <option value="sales">{{ __('storefront.contact.sales') }}</option>
                                <option value="repairs">{{ __('storefront.contact.repairs') }}</option>
                                <option value="complaints">{{ __('storefront.contact.complaints') }}</option>
                                <option value="other">{{ __('storefront.contact.other') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">{{ __('storefront.contact.message') }} <span class="text-red-500">*</span></label>
                            <textarea id="message" name="message" rows="5" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-primary-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-primary-700 transition-colors">{{ __('storefront.contact.submit') }}</button>
                    </form>
                </div>
            </div>

            <div class="pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.contact.faq') }}</h3>
                <div class="space-y-3">
                    <details class="group">
                        <summary class="flex items-center justify-between cursor-pointer p-4 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-900">{{ __('storefront.contact.faq1_q') }}</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>
                        <div class="p-4 text-gray-600">{{ __('storefront.contact.faq1_a') }}</div>
                    </details>
                    <details class="group">
                        <summary class="flex items-center justify-between cursor-pointer p-4 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-900">{{ __('storefront.contact.faq2_q') }}</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>
                        <div class="p-4 text-gray-600">{{ __('storefront.contact.faq2_a') }}</div>
                    </details>
                    <details class="group">
                        <summary class="flex items-center justify-between cursor-pointer p-4 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-900">{{ __('storefront.contact.faq3_q') }}</span>
                            <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </summary>
                        <div class="p-4 text-gray-600">{{ __('storefront.contact.faq3_a') }}</div>
                    </details>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection