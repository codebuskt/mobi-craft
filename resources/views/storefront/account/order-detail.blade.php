@extends('storefront.layouts.app')

@section('title', $order->order_number . ' - ' . __('storefront.account.order_details') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Sidebar Navigation -->
            <aside class="lg:col-span-3">
                @include('storefront.partials.account-nav', ['active' => 'orders'])
            </aside>

            <!-- Order Detail Content -->
            <div class="lg:col-span-9 space-y-6">
                <!-- Back Link -->
                <a href="{{ route('storefront.account.orders') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-primary-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    {{ __('storefront.account.back_to_orders') }}
                </a>

                <!-- Order Header -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1>
                            <p class="text-gray-500 mt-1">{{ __('storefront.orders.placed_on', ['date' => $order->created_at->format('d M Y, h:i A')]) }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
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
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
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
                    </div>

                    <!-- Progress Timeline -->
                    <div class="border-t pt-6">
                        @php
                            $statuses = [
                                ['key' => 'pending', 'label' => __('storefront.orders.status_pending')],
                                ['key' => 'confirmed', 'label' => __('storefront.orders.status_confirmed')],
                                ['key' => 'processing', 'label' => __('storefront.orders.status_processing')],
                                ['key' => 'shipped', 'label' => __('storefront.orders.status_shipped')],
                                ['key' => 'delivered', 'label' => __('storefront.orders.status_delivered')],
                            ];
                            $currentIndex = array_search($order->status, array_column($statuses, 'key'));
                        @endphp

                        <div class="relative">
                            <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200" style="top: -1.5rem;"></div>

                            @foreach($statuses as $index => $status)
                                <div class="relative flex items-start gap-4 mb-6 last:mb-0">
                                    <div class="relative flex-shrink-0">
                                        <div class="w-4 h-4 rounded-full border-3 flex-shrink-0
                                            {{ $index <= $currentIndex ? 'bg-primary-600 border-primary-600' : 'bg-white border-gray-300' }}
                                            {{ $index > $currentIndex ? 'text-gray-400' : 'text-white' }}">
                                            @if($index < $currentIndex)
                                                <svg class="w-3 h-3 mx-auto my-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <p class="font-medium text-gray-900 {{ $index > $currentIndex ? 'text-gray-400' : '' }}">{{ $status['label'] }}</p>
                                        @if(isset($order->status_history[$status['key']]))
                                            <p class="text-sm text-gray-500 mt-1">{{ $order->status_history[$status['key']]->format('d M Y, h:i A') }}</p>
                                        @elseif($index < $currentIndex)
                                            <p class="text-sm text-gray-500 mt-1">{{ __('storefront.orders.completed') }}</p>
                                        @else
                                            <p class="text-sm text-gray-400 mt-1">{{ __('storefront.orders.pending') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.orders.items') }}</h2>

                    <div class="space-y-4">
                        @foreach($order->items as $item)
                            <div class="flex gap-4">
                                @if($item->product && $item->product->thumbnail)
                                    <a href="{{ route('storefront.products.show', $item->product->slug) }}" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-white">
                                        <img src="{{ asset('storage/'.$item->product->thumbnail) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                    </a>
                                @else
                                    <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-gray-100"></div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-gray-900 truncate">{{ $item->product_name }}</h3>
                                    @if($item->product_variant_name)
                                        <p class="text-sm text-gray-500">{{ $item->product_variant_name }}</p>
                                    @endif
                                    <p class="text-sm text-gray-500">{{ __('storefront.orders.qty', ['qty' => $item->quantity]) }}</p>
                                </div>

                                <p class="font-semibold text-gray-900 whitespace-nowrap">{{ shopProfile->formatCurrency($item->total_price) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Addresses & Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.orders.billing_address') }}</h2>
                        <address class="text-gray-600 not-italic space-y-1">
                            <p class="font-medium">{{ $order->billing_name }}</p>
                            <p>{{ $order->billing_phone }}</p>
                            <p>{{ $order->billing_email }}</p>
                            <p>{{ $order->billing_address }}</p>
                            <p>{{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_postal_code }}</p>
                            <p>{{ $order->billing_country }}</p>
                        </address>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.orders.shipping_address') }}</h2>
                        @if($order->shipping_address_same_as_billing)
                            <p class="text-gray-500">{{ __('storefront.orders.same_as_billing') }}</p>
                        @else
                            <address class="text-gray-600 not-italic space-y-1">
                                <p class="font-medium">{{ $order->shipping_name }}</p>
                                <p>{{ $order->shipping_phone }}</p>
                                <p>{{ $order->shipping_address }}</p>
                                <p>{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}</p>
                                <p>{{ $order->shipping_country }}</p>
                            </address>
                        @endif
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.orders.order_summary') }}</h2>

                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ __('storefront.cart.subtotal') }}</span>
                            <span class="font-medium text-gray-900">{{ shopProfile->formatCurrency($order->subtotal) }}</span>
                        </div>

                        @if($order->tax_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ shopProfile->tax_label ?? 'VAT' }} ({{ $order->tax_rate }}%)</span>
                                <span class="font-medium text-gray-900">{{ shopProfile->formatCurrency($order->tax_amount) }}</span>
                            </div>
                        @endif

                        @if($order->shipping_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('storefront.cart.shipping') }}</span>
                                <span class="font-medium text-gray-900">{{ shopProfile->formatCurrency($order->shipping_amount) }}</span>
                            </div>
                        @else
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ __('storefront.cart.shipping') }}</span>
                                <span class="font-medium text-green-600">{{ __('storefront.cart.free') }}</span>
                            </div>
                        @endif

                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-sm text-green-600">
                                <span>{{ __('storefront.cart.discount') }}</span>
                                <span class="font-medium">-{{ shopProfile->formatCurrency($order->discount_amount) }}</span>
                            </div>
                        @endif

                        <div class="border-t pt-4 flex justify-between text-lg font-bold">
                            <span>{{ __('storefront.cart.total') }}</span>
                            <span>{{ shopProfile->formatCurrency($order->grand_total) }}</span>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t">
                        <h3 class="font-medium text-gray-900 mb-3">{{ __('storefront.orders.payment_info') }}</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('storefront.orders.payment_method') }}</dt>
                                <dd class="font-medium text-gray-900 capitalize">{{ $order->payment_method }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('storefront.orders.payment_status') }}</dt>
                                <dd class="font-medium
                                    @if($order->payment_status === 'paid')
                                        text-green-700
                                    @elseif($order->payment_status === 'partial')
                                        text-yellow-700
                                    @else
                                        text-red-700
                                    @endif
                                ">
                                    {{ ucfirst($order->payment_status) }}
                                </dd>
                            </div>
                            @if($order->payment_transaction_id)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">{{ __('storefront.orders.transaction_id') }}</dt>
                                    <dd class="font-medium text-gray-900 font-mono">{{ $order->payment_transaction_id }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-xl border border-gray-100 p-6 flex flex-wrap gap-4">
                    @if($order->canReorder())
                        <a href="{{ route('storefront.checkout.index', ['reorder' => $order->order_number]) }}" class="px-6 py-2 border border-primary-600 text-primary-600 rounded-lg font-medium hover:bg-primary-50 transition-colors">
                            {{ __('storefront.orders.reorder') }}
                        </a>
                    @endif
                    @if($order->invoice_pdf_path)
                        <a href="{{ route('storefront.orders.invoice', $order->order_number) }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                            {{ __('storefront.orders.download_invoice') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection