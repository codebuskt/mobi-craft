@extends('storefront.layouts.app')

@section('title', $ticket->ticket_number . ' - ' . __('storefront.repairs.track_title') . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Back Link -->
        <a href="{{ route('storefront.repairs.track') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-primary-600 mb-6">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            {{ __('storefront.repairs.back_to_track') }}
        </a>

        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Repair Details -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Ticket Header -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->ticket_number }}</h1>
                            <p class="text-gray-500 mt-1">{{ __('storefront.repairs.created_on', ['date' => $ticket->created_at->format('d M Y, h:i A')]) }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
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
                    </div>

                    <!-- Progress Timeline -->
                    <div class="border-t pt-6">
                        @php
                            $statuses = [
                                ['key' => 'received', 'label' => __('storefront.repairs.status_received'), 'desc' => __('storefront.repairs.status_received_desc')],
                                ['key' => 'diagnosed', 'label' => __('storefront.repairs.status_diagnosed'), 'desc' => __('storefront.repairs.status_diagnosed_desc')],
                                ['key' => 'awaiting_parts', 'label' => __('storefront.repairs.status_awaiting_parts'), 'desc' => __('storefront.repairs.status_awaiting_parts_desc')],
                                ['key' => 'in_repair', 'label' => __('storefront.repairs.status_in_repair'), 'desc' => __('storefront.repairs.status_in_repair_desc')],
                                ['key' => 'quality_check', 'label' => __('storefront.repairs.status_quality_check'), 'desc' => __('storefront.repairs.status_quality_check_desc')],
                                ['key' => 'ready_for_pickup', 'label' => __('storefront.repairs.status_ready_for_pickup'), 'desc' => __('storefront.repairs.status_ready_for_pickup_desc')],
                                ['key' => 'completed', 'label' => __('storefront.repairs.status_completed'), 'desc' => __('storefront.repairs.status_completed_desc')],
                            ];
                            $currentIndex = array_search($ticket->status, array_column($statuses, 'key'));
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
                                        <p class="text-sm text-gray-500 mt-0.5">{{ $status['desc'] }}</p>
                                        @if(isset($ticket->status_history[$status['key']]))
                                            <p class="text-sm text-primary-600 mt-1">{{ __('storefront.repairs.completed_on', ['date' => $ticket->status_history[$status['key']]->format('d M Y, h:i A')]) }}</p>
                                        @elseif($index < $currentIndex)
                                            <p class="text-sm text-gray-500 mt-1">{{ __('storefront.repairs.completed') }}</p>
                                        @else
                                            <p class="text-sm text-gray-400 mt-1">{{ __('storefront.repairs.pending') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Device Info -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        {{ __('storefront.repairs.device_info') }}
                    </h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">{{ __('storefront.repairs.device_type') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $ticket->device_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('storefront.repairs.brand') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $ticket->brand }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">{{ __('storefront.repairs.model') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $ticket->model }}</dd>
                        </div>
                        @if($ticket->serial_number)
                            <div>
                                <dt class="text-gray-500">{{ __('storefront.repairs.serial_number') }}</dt>
                                <dd class="font-medium text-gray-900 font-mono">{{ $ticket->serial_number }}</dd>
                            </div>
                        @endif
                        @if($ticket->imei)
                            <div>
                                <dt class="text-gray-500">{{ __('storefront.repairs.imei') }}</dt>
                                <dd class="font-medium text-gray-900 font-mono">{{ $ticket->imei }}</dd>
                            </div>
                        @endif
                        @if($ticket->color)
                            <div>
                                <dt class="text-gray-500">{{ __('storefront.repairs.color') }}</dt>
                                <dd class="font-medium text-gray-900">{{ $ticket->color }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Issue Description -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ __('storefront.repairs.issue_description') }}
                    </h2>
                    <p class="text-gray-600 whitespace-pre-wrap">{{ $ticket->issue_description }}</p>

                    @if($ticket->accessories)
                        <div class="mt-4 pt-4 border-t">
                            <h3 class="font-medium text-gray-900 mb-2">{{ __('storefront.repairs.accessories') }}</h3>
                            <p class="text-gray-600">{{ $ticket->accessories }}</p>
                        </div>
                    @endif
                </div>

                <!-- Notes/Timeline -->
                @if($ticket->notes && $ticket->notes->count() > 0)
                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            {{ __('storefront.repairs.updates') }}
                        </h2>
                        <div class="space-y-4">
                            @foreach($ticket->notes as $note)
                                <div class="flex gap-3 pb-4 border-b last:border-0 last:pb-0">
                                    <div class="w-2 h-2 bg-primary-500 rounded-full mt-2.5 flex-shrink-0"></div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <p class="font-medium text-gray-900">{{ $note->title }}</p>
                                            <span class="text-sm text-gray-400">{{ $note->created_at->format('d M Y, h:i A') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">{{ $note->content }}</p>
                                        @if($note->author_name)
                                            <p class="text-xs text-gray-400 mt-1">{{ $note->author_name }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Estimated Completion -->
                @if($ticket->estimated_completion_date)
                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900">{{ __('storefront.repairs.estimated_completion') }}</h3>
                                <p class="text-gray-600 mt-1">{{ $ticket->estimated_completion_date->format('d M Y') }}</p>
                                @if($ticket->estimated_completion_date->isPast())
                                    <p class="text-sm text-red-600 mt-1">{{ __('storefront.repairs.overdue') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Contact Info -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.repairs.contact_info') }}</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">{{ __('storefront.repairs.customer_name') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $ticket->customer_name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">{{ __('storefront.repairs.phone') }}</dt>
                            <dd class="font-medium text-gray-900">{{ $ticket->customer_phone }}</dd>
                        </div>
                        @if($ticket->customer_email)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('storefront.repairs.email') }}</dt>
                                <dd class="font-medium text-gray-900">{{ $ticket->customer_email }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <!-- Assigned Technician -->
                @if($ticket->assigned_technician)
                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.repairs.assigned_technician') }}</h2>
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-primary-100 flex items-center justify-center">
                                <span class="text-primary-600 font-bold text-lg">{{ strtoupper($ticket->assigned_technician->name[0]) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $ticket->assigned_technician->name }}</p>
                                <p class="text-sm text-gray-500">{{ $ticket->assigned_technician->email }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Cost Summary -->
                @if($ticket->estimated_cost > 0 || $ticket->final_cost > 0)
                    <div class="bg-white rounded-xl border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.repairs.cost_summary') }}</h2>
                        <dl class="space-y-2 text-sm">
                            @if($ticket->estimated_cost > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">{{ __('storefront.repairs.estimated_cost') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ shopProfile->formatCurrency($ticket->estimated_cost) }}</dd>
                                </div>
                            @endif
                            @if($ticket->final_cost > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">{{ __('storefront.repairs.final_cost') }}</dt>
                                    <dd class="font-medium text-gray-900">{{ shopProfile->formatCurrency($ticket->final_cost) }}</dd>
                                </div>
                            @endif
                            @if($ticket->paid_amount > 0)
                                <div class="flex justify-between text-green-700">
                                    <dt class="text-gray-600">{{ __('storefront.repairs.paid_amount') }}</dt>
                                    <dd class="font-medium">{{ shopProfile->formatCurrency($ticket->paid_amount) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">{{ __('storefront.repairs.balance_due') }}</dt>
                                    <dd class="font-bold text-gray-900">{{ shopProfile->formatCurrency($ticket->final_cost - $ticket->paid_amount) }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif

                <!-- Actions -->
                <div class="bg-white rounded-xl border border-gray-100 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('storefront.repairs.actions') }}</h2>
                    <div class="space-y-3">
                        @if($ticket->status === 'ready_for_pickup' || $ticket->status === 'completed')
                            <a href="#" class="w-full text-center px-4 py-2 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors block">
                                {{ __('storefront.repairs.schedule_pickup') }}
                            </a>
                        @endif
                        <button class="w-full text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                            {{ __('storefront.repairs.contact_shop') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection