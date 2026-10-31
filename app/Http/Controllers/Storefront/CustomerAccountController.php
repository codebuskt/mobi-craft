<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Sale;
use App\Models\RepairTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    /**
     * Display customer dashboard.
     */
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();

        // Recent orders
        $recentOrders = Sale::where('tenant_id', app('tenant')->id)
            ->where('customer_id', $customer->id)
            ->with(['items.product', 'payments'])
            ->latest()
            ->take(5)
            ->get();

        // Recent repairs
        $recentRepairs = RepairTicket::where('tenant_id', app('tenant')->id)
            ->where('customer_id', $customer->id)
            ->with(['device'])
            ->latest()
            ->take(5)
            ->get();

        // Stats
        $stats = [
            'total_orders' => Sale::where('tenant_id', app('tenant')->id)->where('customer_id', $customer->id)->count(),
            'total_spent' => Sale::where('tenant_id', app('tenant')->id)->where('customer_id', $customer->id)->sum('total_amount'),
            'pending_repairs' => RepairTicket::where('tenant_id', app('tenant')->id)
                ->where('customer_id', $customer->id)
                ->whereNotIn('status', ['delivered', 'cancelled', 'rejected'])
                ->count(),
            'outstanding_balance' => $customer->outstanding_balance,
        ];

        return view('storefront.account.index', compact('customer', 'recentOrders', 'recentRepairs', 'stats'));
    }

    /**
     * Display customer profile.
     */
    public function profile(): View
    {
        $customer = Auth::guard('customer')->user();
        return view('storefront.account.profile', compact('customer'));
    }

    /**
     * Update customer profile.
     */
    public function updateProfile(Request $request): \Illuminate\Http\RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:customers,email,' . $customer->id . ',id,tenant_id,' . app('tenant')->id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
        ]);

        $customer->update([
            'name' => $request->name,
            'phone' => Customer::normalizePhone($request->phone),
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country ?? 'Bangladesh',
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ]);

        // If email changed, mark as unverified
        if ($customer->wasChanged('email')) {
            $customer->update(['email_verified_at' => null]);
            // Send verification email
        }

        return back()->with('success', __('storefront.account.profile_updated'));
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): \Illuminate\Http\RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors([
                'current_password' => __('storefront.account.current_password_incorrect'),
            ]);
        }

        $customer->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', __('storefront.account.password_updated'));
    }

    /**
     * Display customer orders.
     */
    public function orders(Request $request): View
    {
        $customer = Auth::guard('customer')->user();

        $orders = Sale::where('tenant_id', app('tenant')->id)
            ->where('customer_id', $customer->id)
            ->with(['items.product', 'payments'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('storefront.account.orders', compact('orders'));
    }

    /**
     * Display single order.
     */
    public function showOrder(Sale $order): View
    {
        $customer = Auth::guard('customer')->user();

        if ($order->tenant_id !== app('tenant')->id || $order->customer_id !== $customer->id) {
            abort(404);
        }

        $order->load(['items.product', 'customer', 'payments', 'statusLogs']);
        $timeline = $this->getOrderTimeline($order);

        return view('storefront.account.order-detail', compact('order', 'timeline'));
    }

    /**
     * Display customer repairs.
     */
    public function repairs(Request $request): View
    {
        $customer = Auth::guard('customer')->user();

        $query = RepairTicket::where('tenant_id', app('tenant')->id)
            ->where('customer_id', $customer->id)
            ->with(['device', 'statusLogs']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $repairs = $query->latest()->paginate(10)->withQueryString();

        return view('storefront.account.repairs', compact('repairs'));
    }

    /**
     * Display single repair.
     */
    public function showRepair(RepairTicket $repair): View
    {
        $customer = Auth::guard('customer')->user();

        if ($repair->tenant_id !== app('tenant')->id || $repair->customer_id !== $customer->id) {
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

        $timeline = $this->getRepairTimeline($repair);

        return view('storefront.account.repair-detail', compact('repair', 'timeline'));
    }

    /**
     * Display customer addresses.
     */
    public function addresses(): View
    {
        $customer = Auth::guard('customer')->user();

        $addresses = CustomerContact::where('customer_id', $customer->id)
            ->orderByDesc('is_primary')
            ->get();

        return view('storefront.account.addresses', compact('customer', 'addresses'));
    }

    /**
     * Store new address.
     */
    public function storeAddress(Request $request): \Illuminate\Http\RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'type' => 'required|in:shipping,billing,both',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_primary' => 'sometimes|boolean',
        ]);

        // If setting as primary, unset other primary addresses of same type
        if ($request->boolean('is_primary')) {
            CustomerContact::where('customer_id', $customer->id)
                ->where('type', $request->type)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        CustomerContact::create([
            'customer_id' => $customer->id,
            'type' => $request->type,
            'name' => $request->name,
            'phone' => Customer::normalizePhone($request->phone),
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'is_primary' => $request->boolean('is_primary', false),
        ]);

        return back()->with('success', __('storefront.account.address_added'));
    }

    /**
     * Update address.
     */
    public function updateAddress(Request $request, CustomerContact $address): \Illuminate\Http\RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if ($address->customer_id !== $customer->id) {
            abort(403);
        }

        $request->validate([
            'type' => 'required|in:shipping,billing,both',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_primary' => 'sometimes|boolean',
        ]);

        // If setting as primary, unset other primary addresses of same type
        if ($request->boolean('is_primary')) {
            CustomerContact::where('customer_id', $customer->id)
                ->where('type', $request->type)
                ->where('is_primary', true)
                ->where('id', '!=', $address->id)
                ->update(['is_primary' => false]);
        }

        $address->update([
            'type' => $request->type,
            'name' => $request->name,
            'phone' => Customer::normalizePhone($request->phone),
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'is_primary' => $request->boolean('is_primary', false),
        ]);

        return back()->with('success', __('storefront.account.address_updated'));
    }

    /**
     * Delete address.
     */
    public function destroyAddress(CustomerContact $address): \Illuminate\Http\RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if ($address->customer_id !== $customer->id) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', __('storefront.account.address_deleted'));
    }

    /**
     * Get order status timeline.
     */
    protected function getOrderTimeline(Sale $order): array
    {
        $timeline = [];

        $timeline[] = [
            'status' => 'placed',
            'label' => __('storefront.orders.timeline.placed'),
            'description' => __('storefront.orders.timeline.placed_desc'),
            'timestamp' => $order->created_at,
            'completed' => true,
            'icon' => 'shopping-bag',
        ];

        if ($order->payment_status === 'paid' || $order->payment_status === 'partial') {
            $timeline[] = [
                'status' => 'paid',
                'label' => __('storefront.orders.timeline.paid'),
                'description' => __('storefront.orders.timeline.paid_desc'),
                'timestamp' => $order->payments()->latest()->first()?->received_at ?? $order->created_at,
                'completed' => true,
                'icon' => 'credit-card',
            ];
        } else {
            $timeline[] = [
                'status' => 'payment_pending',
                'label' => __('storefront.orders.timeline.payment_pending'),
                'description' => __('storefront.orders.timeline.payment_pending_desc'),
                'timestamp' => null,
                'completed' => false,
                'current' => true,
                'icon' => 'clock',
            ];
        }

        $timeline[] = [
            'status' => 'processing',
            'label' => __('storefront.orders.timeline.processing'),
            'description' => __('storefront.orders.timeline.processing_desc'),
            'timestamp' => null,
            'completed' => in_array($order->payment_status, ['paid', 'partial']),
            'current' => $order->payment_status === 'due',
            'icon' => 'cog-6-tooth',
        ];

        $timeline[] = [
            'status' => 'shipped',
            'label' => __('storefront.orders.timeline.shipped'),
            'description' => __('storefront.orders.timeline.shipped_desc'),
            'timestamp' => null,
            'completed' => false,
            'icon' => 'truck',
        ];

        $timeline[] = [
            'status' => 'delivered',
            'label' => __('storefront.orders.timeline.delivered'),
            'description' => __('storefront.orders.timeline.delivered_desc'),
            'timestamp' => null,
            'completed' => false,
            'icon' => 'check-circle',
        ];

        return $timeline;
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