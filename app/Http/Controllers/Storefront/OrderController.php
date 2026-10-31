<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Show order lookup form for guests.
     */
    public function lookup(): View
    {
        return view('storefront.orders.lookup');
    }

    /**
     * Find order by invoice number and email/phone.
     */
    public function find(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'invoice_number' => 'required|string',
            'contact' => 'required|string', // email or phone
        ]);

        $order = Sale::where('tenant_id', app('tenant')->id)
            ->where('invoice_number', $request->invoice_number)
            ->whereHas('customer', function ($q) use ($request) {
                $q->where('email', $request->contact)
                  ->orWhere('phone', Customer::normalizePhone($request->contact));
            })
            ->first();

        if (! $order) {
            return back()->withErrors([
                'invoice_number' => __('storefront.orders.not_found'),
            ]);
        }

        return redirect()->route('storefront.orders.track', $order);
    }

    /**
     * Track order status (public).
     */
    public function track(Sale $order): View
    {
        // Verify order belongs to current tenant
        if ($order->tenant_id !== app('tenant')->id) {
            abort(404);
        }

        $order->load(['items.product', 'customer', 'payments', 'statusLogs']);

        // Get order status timeline
        $timeline = $this->getOrderTimeline($order);

        return view('storefront.orders.track', compact('order', 'timeline'));
    }

    /**
     * Display authenticated customer's order history.
     */
    public function index(Request $request): View
    {
        $customer = Auth::guard('customer')->user();

        $orders = Sale::where('tenant_id', app('tenant')->id)
            ->where('customer_id', $customer->id)
            ->with(['items.product', 'payments'])
            ->latest()
            ->paginate(10);

        return view('storefront.account.orders', compact('orders'));
    }

    /**
     * Display single order detail for authenticated customer.
     */
    public function show(Sale $order): View
    {
        $customer = Auth::guard('customer')->user();

        // Verify order belongs to current customer and tenant
        if ($order->tenant_id !== app('tenant')->id || $order->customer_id !== $customer->id) {
            abort(404);
        }

        $order->load(['items.product', 'customer', 'payments', 'statusLogs']);
        $timeline = $this->getOrderTimeline($order);

        return view('storefront.account.order-detail', compact('order', 'timeline'));
    }

    /**
     * Reorder - add all items from previous order to cart.
     */
    public function reorder(Request $request, Sale $order): \Illuminate\Http\RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if ($order->tenant_id !== app('tenant')->id || $order->customer_id !== $customer->id) {
            abort(404);
        }

        $order->load('items.product');

        $cart = session('storefront_cart', ['items' => [], 'coupon' => null, 'notes' => '']);
        $added = 0;

        foreach ($order->items as $item) {
            if (! $item->product || ! $item->product->canSell()) {
                continue;
            }

            $available = $item->product->available_quantity;
            $currentInCart = $this->getCartItemQuantity($cart, $item->product_id);
            $quantity = min($item->quantity, $available - $currentInCart);

            if ($quantity <= 0) {
                continue;
            }

            $existingKey = $this->findCartItemKey($cart, $item->product_id);

            if ($existingKey !== null) {
                $cart['items'][$existingKey]['quantity'] += $quantity;
            } else {
                $cart['items'][] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'product_id' => $item->product_id,
                    'quantity' => $quantity,
                    'price' => $item->product->getEffectiveSalePriceAttribute(),
                    'added_at' => now()->toISOString(),
                ];
            }
            $added++;
        }

        session(['storefront_cart' => $cart]);

        if ($added > 0) {
            return redirect()->route('storefront.cart.index')
                ->with('success', __('storefront.orders.reorder_success', ['count' => $added]));
        }

        return redirect()->route('storefront.cart.index')
            ->with('error', __('storefront.orders.reorder_failed'));
    }

    /**
     * Get order status timeline.
     */
    protected function getOrderTimeline(Sale $order): array
    {
        $timeline = [];

        // Order placed
        $timeline[] = [
            'status' => 'placed',
            'label' => __('storefront.orders.timeline.placed'),
            'description' => __('storefront.orders.timeline.placed_desc'),
            'timestamp' => $order->created_at,
            'completed' => true,
            'icon' => 'shopping-bag',
        ];

        // Payment
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

        // Processing
        $timeline[] = [
            'status' => 'processing',
            'label' => __('storefront.orders.timeline.processing'),
            'description' => __('storefront.orders.timeline.processing_desc'),
            'timestamp' => null,
            'completed' => in_array($order->payment_status, ['paid', 'partial']),
            'current' => $order->payment_status === 'due',
            'icon' => 'cog-6-tooth',
        ];

        // Shipped (if applicable)
        $timeline[] = [
            'status' => 'shipped',
            'label' => __('storefront.orders.timeline.shipped'),
            'description' => __('storefront.orders.timeline.shipped_desc'),
            'timestamp' => null,
            'completed' => false,
            'icon' => 'truck',
        ];

        // Delivered
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
     * Get quantity of a product currently in cart.
     */
    protected function getCartItemQuantity(array $cart, int $productId): int
    {
        foreach ($cart['items'] as $item) {
            if ($item['product_id'] === $productId) {
                return $item['quantity'];
            }
        }
        return 0;
    }

    /**
     * Find cart item key by product ID.
     */
    protected function findCartItemKey(array $cart, int $productId): ?int
    {
        foreach ($cart['items'] as $key => $item) {
            if ($item['product_id'] === $productId) {
                return $key;
            }
        }
        return null;
    }
}