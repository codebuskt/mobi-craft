<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\RepairTicket;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepairTrackingController extends Controller
{
    /**
     * Show repair tracking form.
     */
    public function showTrackForm(): View
    {
        return view('storefront.repairs.track');
    }

    /**
     * Track repair by ticket number and phone.
     */
    public function track(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'ticket_number' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
        ]);

        $phone = Customer::normalizePhone($request->phone);

        $repair = RepairTicket::where('tenant_id', app('tenant')->id)
            ->where('ticket_number', $request->ticket_number)
            ->whereHas('customer', function ($q) use ($phone) {
                $q->where('phone', $phone);
            })
            ->with(['customer', 'device', 'statusLogs.changedBy', 'parts.product', 'approvals'])
            ->first();

        if (! $repair) {
            return back()->withErrors([
                'ticket_number' => __('storefront.repairs.not_found'),
            ]);
        }

        return redirect()->route('storefront.repairs.status', $repair);
    }

    /**
     * Display repair status.
     */
    public function status(RepairTicket $repair): View
    {
        // Verify repair belongs to current tenant
        if ($repair->tenant_id !== app('tenant')->id) {
            abort(404);
        }

        $repair->load([
            'customer',
            'device',
            'statusLogs.changedBy',
            'parts.product',
            'approvals',
            'qualityCheck',
            'delivery',
        ]);

        // Get status timeline
        $timeline = $this->getRepairTimeline($repair);

        return view('storefront.repairs.status', compact('repair', 'timeline'));
    }

    /**
     * Get repair status timeline.
     */
    protected function getRepairTimeline(RepairTicket $repair): array
    {
        $timeline = [];
        $statuses = [
            'received' => ['label' => 'Received', 'icon' => 'inbox', 'description' => 'Device received at shop'],
            'diagnosing' => ['label' => 'Diagnosing', 'icon' => 'magnifying-glass', 'description' => 'Technician is diagnosing the issue'],
            'waiting_approval' => ['label' => 'Waiting Approval', 'icon' => 'clock', 'description' => 'Waiting for customer approval on estimate'],
            'waiting_parts' => ['label' => 'Waiting Parts', 'icon' => 'cube', 'description' => 'Waiting for replacement parts to arrive'],
            'in_repair' => ['label' => 'In Repair', 'icon' => 'wrench', 'description' => 'Repair work in progress'],
            'quality_check' => ['label' => 'Quality Check', 'icon' => 'clipboard-check', 'description' => 'Final quality check before delivery'],
            'ready_delivery' => ['label' => 'Ready for Delivery', 'icon' => 'truck', 'description' => 'Repair complete, ready for pickup/delivery'],
            'delivered' => ['label' => 'Delivered', 'icon' => 'check-circle', 'description' => 'Device delivered to customer'],
            'cancelled' => ['label' => 'Cancelled', 'icon' => 'x-circle', 'description' => 'Repair cancelled'],
            'rejected' => ['label' => 'Rejected', 'icon' => 'x-circle', 'description' => 'Customer rejected the repair'],
        ];

        $statusOrder = array_keys($statuses);
        $currentIndex = array_search($repair->status, $statusOrder);

        // Add status logs to timeline
        $logs = $repair->statusLogs->sortBy('created_at');

        foreach ($statusOrder as $index => $status) {
            $log = $logs->firstWhere('to_status', $status);

            $completed = $index <= $currentIndex;
            $current = $index === $currentIndex && ! in_array($repair->status, ['delivered', 'cancelled', 'rejected']);

            $timeline[] = [
                'status' => $status,
                'label' => $statuses[$status]['label'],
                'icon' => $statuses[$status]['icon'],
                'description' => $statuses[$status]['description'],
                'timestamp' => $log?->created_at ?? ($completed ? $repair->created_at : null),
                'completed' => $completed,
                'current' => $current,
                'notes' => $log?->notes,
            ];
        }

        return $timeline;
    }
}