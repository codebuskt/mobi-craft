@extends('storefront.layouts.app')

@section('title', __('storefront.account.repairs') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Sidebar Navigation -->
            <aside class="lg:col-span-3">
                @include('storefront.partials.account-nav', ['active' => 'repairs'])
            </aside>

            <!-- Repairs Content -->
            <div class="lg:col-span-9">
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('storefront.account.repairs') }}</h1>
                </div>

                <!-- Filter Tabs -->
                <div class="mb-6 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Repair filters">
                        @php
                            $filters = [
                                'all' => __('storefront.account.all_repairs'),
                                'received' => __('storefront.repairs.status_received'),
                                'diagnosed' => __('storefront.repairs.status_diagnosed'),
                                'awaiting_parts' => __('storefront.repairs.status_awaiting_parts'),
                                'in_repair' => __('storefront.repairs.status_in_repair'),
                                'quality_check' => __('storefront.repairs.status_quality_check'),
                                'ready_for_pickup' => __('storefront.repairs.status_ready_for_pickup'),
                                'completed' => __('storefront.repairs.status_completed'),
                                'cancelled' => __('storefront.repairs.status_cancelled'),
                            ];
                        @endphp
                        @foreach($filters as $key => $label)
                            <a href="{{ route('storefront.account.repairs', ['status' => $key]) }}" class="border-b-2 py-4 px-1 text-sm font-medium whitespace-nowrap {{ request('status', 'all') === $key ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} transition-colors">
                                {{ $label }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <!-- Repairs List -->
                @if($repairs->count() > 0)
                    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                        <div class="divide-y divide-gray-100">
                            @foreach($repairs as $ticket)
                                <div class="p-6 flex flex-col sm:flex-row sm:items-center gap-4">
                                    <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between flex-wrap gap-2 mb-1">
                                            <p class="font-medium text-gray-900">{{ $ticket->ticket_number }}</p>
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
                                        </div>
                                        <div class="flex items-center gap-4 text-sm text-gray-500 flex-wrap">
                                            <span>{{ $ticket->brand }} {{ $ticket->model }}</span>
                                            <span>{{ $ticket->created_at->format('d M Y') }}</span>
                                            @if($ticket->estimated_completion_date)
                                                <span class="{{ $ticket->estimated_completion_date->isPast() ? 'text-red-600' : 'text-amber-600' }}">{{ __('storefront.repairs.est_completion', ['date' => $ticket->estimated_completion_date->format('d M Y')]) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('storefront.repairs.status', $ticket->ticket_number) }}" class="px-4 py-2 text-primary-600 hover:text-primary-700 font-medium text-sm">
                                            {{ __('storefront.account.view') }}
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="p-6 border-t">
                            {{ $repairs->links('pagination::tailwind') }}
                        </div>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('storefront.account.no_repairs') }}</h3>
                        <p class="mt-2 text-gray-500">{{ __('storefront.account.no_repairs_desc') }}</p>
                        <a href="{{ route('storefront.contact') }}" class="mt-6 inline-block text-primary-600 hover:underline">{{ __('storefront.account.contact_for_repair') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection