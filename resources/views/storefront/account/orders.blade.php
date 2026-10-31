@extends('storefront.layouts.app')

@section('title', __('storefront.account.orders') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Sidebar Navigation -->
            <aside class="lg:col-span-3">
                @include('storefront.partials.account-nav', ['active' => 'orders'])
            </aside>

            <!-- Orders Content -->
            <div class="lg:col-span-9">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('storefront.account.orders') }}</h1>
                </div>

                <!-- Filter Tabs -->
                <div class="mb-6 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Order filters">
                        @php
                            $filters = [
                                'all' => __('storefront.account.all_orders'),
                                'pending' => __('storefront.account.pending'),
                                'confirmed' => __('storefront.account.confirmed'),
                                'processing' => __('storefront.account.processing'),
                                'shipped' => __('storefront.account.shipped'),
                                'delivered' => __('storefront.account.delivered'),
                                'cancelled' => __('storefront.account.cancelled'),
                            ];
                        @endphp
                        @foreach($filters as $key => $label)
                            <a href="{{ route('storefront.account.orders', ['status' => $key]) }}" class="border-b-2 py-4 px-1 text-sm font-medium {{ request('status', 'all') === $key ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} transition-colors">
                                {{ $label }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <!-- Orders List -->
                @if($orders->count() > 0)
                    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                        <div class="divide-y divide-gray-100">
                            @foreach($orders as $order)
                                <div class="p-6 flex flex-col sm:flex-row sm:items-center gap-4">
                                    <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between flex-wrap gap-2 mb-1">
                                            <p class="font-medium text-gray-900">{{ $order->order_number }}</p>
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
                                        <div class="flex items-center gap-4 text-sm text-gray-500 flex-wrap">
                                            <span>{{ $order->created_at->format('d M Y') }}</span>
                                            <span>{{ shopProfile->formatCurrency($order->grand_total) }}</span>
                                            <span>{{ $order->items_count }} {{ __('storefront.account.items') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('storefront.orders.show', $order->order_number) }}" class="px-4 py-2 text-primary-600 hover:text-primary-700 font-medium text-sm">
                                            {{ __('storefront.account.view') }}
                                        </a>
                                        @if($order->canReorder())
                                            <a href="{{ route('storefront.checkout.index', ['reorder' => $order->order_number]) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-colors">
                                                {{ __('storefront.account.reorder') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="p-6 border-t">
                            {{ $orders->links('pagination::tailwind') }}
                        </div>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('storefront.account.no_orders') }}</h3>
                        <p class="mt-2 text-gray-500">{{ __('storefront.account.no_orders_desc') }}</p>
                        <a href="{{ route('storefront.products.index') }}" class="mt-6 inline-block bg-primary-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                            {{ __('storefront.account.start_shopping') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection