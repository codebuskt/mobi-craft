<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\InventoryMovement;
use App\Models\CustomerDue;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /**
     * Display checkout page.
     */
    public function index(): \Illuminate\View\View
    {
        $cart = $this->getCartWithProducts();

        if (empty($cart['items'])) {
            return redirect()->route('storefront.cart.index')
                ->with('error', __('storefront.checkout.cart_empty'));
        }

        $totals = $this->calculateTotals($cart);
        $customer = Auth::guard('customer')->user();

        // Pre-fill address if logged in
        $address = null;
        if ($customer) {
            $address = [
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'city' => $customer->city,
                'state' => $customer->state,
                'postal_code' => $customer->postal_code,
                'country' => $customer->country ?? 'Bangladesh',
            ];
        }

        return view('storefront.checkout.index', compact('cart', 'totals', 'customer', 'address'));
    }

    /**
     * Store shipping/billing address.
     */
    public function storeAddress(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'same_as_billing' => 'sometimes|boolean',
        ]);

        $addressData = $request->only([
            'name', 'phone', 'email', 'address', 'city',
            'state', 'postal_code', 'country'
        ]);

        Session::put('checkout_address', $addressData);
        Session::put('checkout_same_as_billing', $request->boolean('same_as_billing', true));

        return redirect()->route('storefront.checkout.index')
            ->with('success', __('storefront.checkout.address_saved'));
    }

    /**
     * Store shipping method (if multiple options).
     */
    public function storeShipping(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'shipping_method' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
        ]);

        Session::put('checkout_shipping', [
            'method' => $request->shipping_method,
            'cost' => $request->shipping_cost,
        ]);

        return redirect()->route('storefront.checkout.index')
            ->with('success', __('storefront.checkout.shipping_saved'));
    }

    /**
     * Store payment method.
     */
    public function storePayment(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'payment_method' => 'required|in:cash,card,mobile_banking,bank_transfer',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        Session::put('checkout_payment', [
            'method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
        ]);

        return redirect()->route('storefront.checkout.index')
            ->with('success', __('storefront.checkout.payment_saved'));
    }

    /**
     * Place the order.
     */
    public function placeOrder(Request $request): \Illuminate\Http\RedirectResponse
    {
        $cart = $this->getCartWithProducts();

        if (empty($cart['items'])) {
            return redirect()->route('storefront.cart.index')
                ->with('error', __('storefront.checkout.cart_empty'));
        }

        // Validate required checkout data
        $address = Session::get('checkout_address');
        $payment = Session::get('checkout_payment');

        if (! $address) {
            return redirect()->route('storefront.checkout.index')
                ->with('error', __('storefront.checkout.address_required'));
        }

        if (! $payment) {
            return redirect()->route('storefront.checkout.index')
                ->with('error', __('storefront.checkout.payment_required'));
        }

        try {
            $order = $this->createOrder($cart, $address, $payment);

            // Clear cart and checkout session
            Session::forget('storefront_cart');
            Session::forget('checkout_address');
            Session::forget('checkout_same_as_billing');
            Session::forget('checkout_shipping');
            Session::forget('checkout_payment');

            return redirect()->route('storefront.checkout.success', $order)
                ->with('success', __('storefront.checkout.order_placed', ['number' => $order->invoice_number]));

        } catch (\Exception $e) {
            \Log::error('Storefront checkout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('storefront.checkout.index')
                ->with('error', __('storefront.checkout.failed'));
        }
    }

    /**
     * Display order success page.
     */
    public function success(Sale $order): \Illuminate\View\View
    {
        // Verify order belongs to current tenant
        if ($order->tenant_id !== app('tenant')->id) {
            abort(404);
        }

        $order->load(['items.product', 'customer', 'payments']);

        return view('storefront.checkout.success', compact('order'));
    }

    /**
     * Create the order (Sale record) with all related data.
     */
    protected function createOrder(array $cart, array $address, array $payment): Sale
    {
        $tenant = app('tenant');
        $shopProfile = $tenant->shopProfile;
        $customer = Auth::guard('customer')->user();

        // Calculate totals
        $totals = $this->calculateTotals($cart);
        $shipping = Session::get('checkout_shipping.cost', 0);
        $totals['shipping'] = $shipping;
        $totals['grand_total'] = $totals['subtotal'] + $totals['tax_amount'] + $shipping - $totals['discount'];

        return DB::transaction(function () use ($tenant, $shopProfile, $customer, $cart, $address, $payment, $totals) {
            // Create or get customer
            if (! $customer) {
                $customer = $this->createGuestCustomer($address, $tenant->id);
            }

            // Generate invoice number
            $invoiceNumber = $shopProfile->getNextInvoiceNumber();

            // Create sale record
            $sale = Sale::create([
                'tenant_id' => $tenant->id,
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customer->id,
                'user_id' => null, // No staff user for storefront orders
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['grand_total'],
                'paid_amount' => $payment['method'] === 'cash' ? 0 : $totals['grand_total'],
                'due_amount' => $payment['method'] === 'cash' ? $totals['grand_total'] : 0,
                'payment_status' => $payment['method'] === 'cash' ? 'due' : 'paid',
                'payment_method' => $payment['method'],
                'notes' => $cart['notes'] ?? null,
                'shipping_address' => json_encode($address),
                'billing_address' => json_encode(Session::get('checkout_same_as_billing', true) ? $address : (Session::get('checkout_billing_address') ?? $address)),
            ]);

            // Create sale items and inventory movements
            foreach ($cart['items'] as $item) {
                if (! isset($item['product'])) {
                    continue; // Skip unavailable products
                }

                $product = $item['product'];
                $price = $product->getEffectiveSalePriceAttribute();
                $taxRate = $product->tax_rate ?? $shopProfile->default_tax_rate ?? 0;
                $taxAmount = $price * $item['quantity'] * ($taxRate / 100);
                $total = ($price * $item['quantity']) + $taxAmount;

                $saleItem = SaleItem::create([
                    'tenant_id' => $tenant->id,
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    'unit_cost' => $product->cost_price,
                    'discount' => 0,
                    'tax' => $taxAmount,
                    'total' => $total,
                ]);

                // Create inventory movement (reduce stock)
                InventoryMovement::create([
                    'tenant_id' => $tenant->id,
                    'product_id' => $product->id,
                    'type' => 'sale',
                    'quantity' => -$item['quantity'],
                    'unit_cost' => $product->cost_price,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'reason' => 'Storefront order: ' . $invoiceNumber,
                    'performed_by' => null,
                ]);
            }

            // Create customer due if payment is due
            if ($sale->due_amount > 0) {
                CustomerDue::create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'sale_id' => $sale->id,
                    'amount' => $sale->due_amount,
                    'due_date' => now()->addDays($shopProfile->payment_terms_days ?? 0),
                    'status' => 'pending',
                ]);
            }

            // Record payment if paid
            if ($sale->paid_amount > 0) {
                $sale->payments()->create([
                    'tenant_id' => $tenant->id,
                    'customer_id' => $customer->id,
                    'sale_id' => $sale->id,
                    'amount' => $sale->paid_amount,
                    'payment_method' => $payment['method'],
                    'transaction_id' => $payment['transaction_id'],
                    'received_at' => now(),
                    'notes' => 'Storefront payment',
                ]);
            }

            return $sale;
        });
    }

    /**
     * Create customer from guest checkout.
     */
    protected function createGuestCustomer(array $address, int $tenantId): Customer
    {
        // Normalize phone
        $phone = Customer::normalizePhone($address['phone']);

        // Check if customer exists
        $existing = Customer::where('tenant_id', $tenantId)
            ->where('phone', $phone)
            ->first();

        if ($existing) {
            return $existing;
        }

        return Customer::create([
            'tenant_id' => $tenantId,
            'name' => $address['name'],
            'phone' => $phone,
            'email' => $address['email'],
            'address' => $address['address'],
            'city' => $address['city'],
            'state' => $address['state'],
            'postal_code' => $address['postal_code'],
            'country' => $address['country'],
            'type' => 'walk_in',
            'status' => 'active',
            'credit_limit' => 0,
            'outstanding_balance' => 0,
            'payment_terms_days' => 0,
            'source' => 'storefront',
        ]);
    }

    /**
     * Get cart with product details hydrated.
     */
    protected function getCartWithProducts(): array
    {
        $cart = Session::get('storefront_cart', ['items' => [], 'coupon' => null, 'notes' => '']);

        if (empty($cart['items'])) {
            return $cart;
        }

        $productIds = collect($cart['items'])->pluck('product_id')->unique()->toArray();

        $products = Product::currentTenant()
            ->whereIn('id', $productIds)
            ->with(['category', 'brand', 'thumbnail'])
            ->get()
            ->keyBy('id');

        foreach ($cart['items'] as &$item) {
            $product = $products->get($item['product_id']);

            if ($product) {
                $item['product'] = $product;
                $item['price'] = $product->getEffectiveSalePriceAttribute();
                $item['available'] = $product->available_quantity;
                $item['subtotal'] = $item['price'] * $item['quantity'];
            } else {
                $item['product'] = null;
                $item['subtotal'] = $item['price'] * $item['quantity'];
            }
        }

        return $cart;
    }

    /**
     * Calculate cart totals.
     */
    protected function calculateTotals(array $cart): array
    {
        $shopProfile = app('tenant')->shopProfile;
        $taxRate = $shopProfile?->default_tax_rate ?? 0;
        $taxInclusive = $shopProfile?->tax_inclusive ?? false;

        $subtotal = 0;
        $taxAmount = 0;

        foreach ($cart['items'] as $item) {
            if (isset($item['subtotal'])) {
                $subtotal += $item['subtotal'];
            } elseif (isset($item['product'])) {
                $price = $item['product']->getEffectiveSalePriceAttribute();
                $subtotal += $price * $item['quantity'];
            } else {
                $subtotal += $item['price'] * $item['quantity'];
            }
        }

        if (! $taxInclusive && $taxRate > 0) {
            $taxAmount = $subtotal * ($taxRate / 100);
        }

        // TODO: Add coupon discount logic
        $discount = 0;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'shipping' => 0,
            'discount' => round($discount, 2),
            'grand_total' => round($subtotal + $taxAmount - $discount, 2),
        ];
    }
}