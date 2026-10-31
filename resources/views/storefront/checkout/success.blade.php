@extends('storefront.layouts.app')

@section('title', __('storefront.checkout.success_title') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-xl border border-gray-100 p-8 sm:p-12 text-center">
            <!-- Success Icon -->
            <div class="w-24 h-24 mx-auto mb-6 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('storefront.checkout.success_title') }}</h1>
            <p class="text-gray-600 mb-6">{{ __('storefront.checkout.success_desc') }}</p>

            <!-- Order Details -->
            <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-900">{{ __('storefront.checkout.order_number') }}</h2>
                    <span class="font-mono text-xl font-bold text-primary-600">{{ $order->order_number }}</span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">{{ __('storefront.checkout.order_date') }}</p>
                        <p class="font-medium text-gray-900">{{ $order->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('storefront.checkout.order_status') }}</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($order->status === 'pending')
                                bg-yellow-100 text-yellow-800
                            @elseif($order->status === 'confirmed')
                                bg-blue-100 text-blue-800
                            @elseif($order->status === 'processing')
                                bg-purple-100 text-purple-800
                            @elseif($order->status === 'shipped')
                                bg-indigo-100 text-indigo-800
                            @elseif($order->status === 'delivered')
                                bg-green-100 text-green-800
                            @elseif($order->status === 'cancelled')
                                bg-red-100 text-red-800
                            @else
                                bg-gray-100 text-gray-800
                            @endif
                        ">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('storefront.checkout.payment_status') }}</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($order->payment_status === 'paid')
                                bg-green-100 text-green-800
                            @elseif($order->payment_status === 'partial')
                                bg-yellow-100 text-yellow-800
                            @else
                                bg-red-100 text-red-800
                            @endif
                        ">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('storefront.checkout.payment_method') }}</p>
                        <p class="font-medium text-gray-900 capitalize">{{ $order->payment_method }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-gray-500">{{ __('storefront.checkout.order_total') }}</p>
                        <p class="font-bold text-xl text-gray-900">{{ shopProfile->formatCurrency($order->grand_total) }}</p>
                    </div>
                </div>
            </div>

            <!-- Email Notice -->
            @if($order->customer_email)
                <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg text-left">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <p class="font-medium text-blue-800">{{ __('storefront.checkout.email_sent') }}</p>
                            <p class="text-sm text-blue-700 mt-1">{{ __('storefront.checkout.email_sent_desc', ['email' => $order->customer_email]) }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('storefront.orders.show', $order->order_number) }}" class="w-full sm:w-auto px-6 py-3 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                    {{ __('storefront.checkout.view_order') }}
                </a>
                <a href="{{ route('storefront.products.index') }}" class="w-full sm:w-auto px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                    {{ __('storefront.checkout.continue_shopping') }}
                </a>
            </div>

            <!-- What's Next -->
            <div class="mt-10 pt-8 border-t">
                <h3 class="font-semibold text-gray-900 mb-4">{{ __('storefront.checkout.whats_next') }}</h3>
                <ul class="space-y-3 text-left text-sm text-gray-600">
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-primary-600 font-bold text-xs">1</span>
                        </div>
                        <span>{{ __('storefront.checkout.next_1') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-primary-600 font-bold text-xs">2</span>
                        </div>
                        <span>{{ __('storefront.checkout.next_2') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-primary-600 font-bold text-xs">3</span>
                        </div>
                        <span>{{ __('storefront.checkout.next_3') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Support Info -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>{{ __('storefront.checkout.need_help') }}</p>
            <a href="{{ route('storefront.contact') }}" class="text-primary-600 hover:underline">{{ __('storefront.checkout.contact_support') }}</a>
        </div>
    </div>
</div>
@endsection