@extends('storefront.layouts.app')

@section('title', __('storefront.account.dashboard') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.account.welcome', ['name' => auth()->guard('customer')->user()->name]) }}</h1>
            <p class="text-gray-500 mt-1">{{ __('storefront.account.welcome_desc') }}</p>
        </div>

        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Sidebar Navigation -->
            <aside class="lg:col-span-3">
                <div class="bg-white rounded-xl border border-gray-100 p-6 sticky top-24">
                    <!-- Profile Summary -->
                    <div class="mb-6 pb-6 border-b">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-primary-600 font-bold text-xl">{{ strtoupper(auth()->guard('customer')->user()->name[0]) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ auth()->guard('customer')->user()->name }}</p>
                                <p class="text-sm text-gray-500">{{ auth()->guard('customer')->user()->email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Menu -->
                    <nav class="space-y-1">
                        <a href="{{ route('storefront.account.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary-50 text-primary-700 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            {{ __('storefront.account.dashboard') }}
                        </a>
                        <a href="{{ route('storefront.account.orders') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            {{ __('storefront.account.orders') }}
                            @if($pendingOrdersCount > 0)
                                <span class="ml-auto px-2 py-0.5 bg-primary-100 text-primary-700 text-xs rounded-full">{{ $pendingOrdersCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('storefront.account.repairs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ __('storefront.account.repairs') }}
                        </a>
                        <a href="{{ route('storefront.account.profile') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            {{ __('storefront.account.profile') }}
                        </a>
                        <a href="{{ route('storefront.account.addresses') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ __('storefront.account.addresses') }}
                        </a>
                    </nav>

                    <!-- Logout -->
                    <div class="mt-6 pt-6 border-t">
                        <form wire:submit.prevent="logout">
                            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                {{ __('storefront.auth.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Dashboard Content -->
            <div class="lg:col-span-9 space-y-8">
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('storefront.account.total_orders') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('storefront.account.completed_orders') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $completedOrders }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('storefront.account.pending_orders') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $pendingOrders }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">{{ __('storefront.account.total_repairs') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $totalRepairs }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('storefront.account.recent_orders') }}</h2>
                        <a href="{{ route('storefront.account.orders') }}" class="text-sm text-primary-600 hover:underline">{{ __('storefront.account.view_all') }}</a>
                    </div>
                    @if($recentOrders->count() > 0)
                        <div class="divide-y divide-gray-100">
                            @foreach($recentOrders as $order)
                                <div class="p-6 flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ $order->order_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $order->created_at->format('d M Y') }} · {{ $shopProfile->formatCurrency($order->grand_total) }}</p>
                                    </div>
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
                                    <a href="{{ route('storefront.orders.show', $order->order_number) }}" class="text-sm text-primary-600 hover:underline">{{ __('storefront.account.view') }}</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <p class="mt-2 text-gray-500">{{ __('storefront.account.no_orders') }}</p>
                            <a href="{{ route('storefront.products.index') }}" class="mt-4 inline-block text-primary-600 hover:underline">{{ __('storefront.account.start_shopping') }}</a>
                        </div>
                    @endif
                </div>

                <!-- Recent Repairs -->
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('storefront.account.recent_repairs') }}</h2>
                        <a href="{{ route('storefront.account.repairs') }}" class="text-sm text-primary-600 hover:underline">{{ __('storefront.account.view_all') }}</a>
                    </div>
                    @if($recentRepairs->count() > 0)
                        <div class="divide-y divide-gray-100">
                            @foreach($recentRepairs as $ticket)
                                <div class="p-6 flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate">{{ $ticket->ticket_number }}</p>
                                        <p class="text-sm text-gray-500">{{ $ticket->brand }} {{ $ticket->model }} · {{ $ticket->created_at->format('d M Y') }}</p>
                                    </div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($ticket->status === 'received')
                                            bg-gray-100 text-gray-800
                                        @elseif($ticket->status === 'diagnosed')
                                            bg-blue-100 text-blue-800
                                        @elseif($ticket->status === 'awaiting_parts')
                                            bg-yellow-100 text-yellow-800
                                        @elseif($ticket->status === 'in_repair')
                                            bg-orange-100 text-orange-800
                                        @elseif($ticket->status === 'quality_check')
                                            bg-purple-100 text-purple-800
                                        @elseif($ticket->status === 'ready_for_pickup')
                                            bg-green-100 text-green-800
                                        @elseif($ticket->status === 'completed')
                                            bg-emerald-100 text-emerald-800
                                        @elseif($ticket->status === 'cancelled')
                                            bg-red-100 text-red-800
                                        @else
                                            bg-gray-100 text-gray-800
                                        @endif
                                    ">
                                        {{ $ticket->status_label }}
                                    </span>
                                    <a href="{{ route('storefront.repairs.status', $ticket->ticket_number) }}" class="text-sm text-primary-600 hover:underline">{{ __('storefront.account.view') }}</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <p class="mt-2 text-gray-500">{{ __('storefront.account.no_repairs') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection