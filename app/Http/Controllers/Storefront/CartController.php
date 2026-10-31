<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display the cart page.
     */
    public function index(): \Illuminate\View\View
    {
        $cart = $this->getCartWithProducts();
        $totals = $this->calculateTotals($cart);

        return view('storefront.cart.index', compact('cart', 'totals'));
    }

    /**
     * Add product to cart.
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'sometimes|integer|min:1|max:999',
        ]);

        $product = Product::currentTenant()
            ->where('is_published', true)
            ->where('status', 'active')
            ->where('is_pos_visible', true)
            ->findOrFail($request->product_id);

        $quantity = $request->integer('quantity', 1);

        // Check stock
        $available = $product->available_quantity;
        $currentInCart = $this->getCartItemQuantity($product->id);

        if ($currentInCart + $quantity > $available) {
            return response()->json([
                'success' => false,
                'message' => __('storefront.cart.insufficient_stock', ['available' => $available - $currentInCart]),
            ], 422);
        }

        $cart = Session::get('storefront_cart', ['items' => [], 'coupon' => null, 'notes' => '']);

        $existingKey = $this->findCartItemKey($cart, $product->id);

        if ($existingKey !== null) {
            $cart['items'][$existingKey]['quantity'] += $quantity;
        } else {
            $cart['items'][] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->getEffectiveSalePriceAttribute(),
                'added_at' => now()->toISOString(),
            ];
        }

        Session::put('storefront_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => __('storefront.cart.added', ['name' => $product->name]),
            'cart_count' => $this->getCartCount($cart),
            'cart_total' => $this->calculateTotals($this->hydrateCart($cart))['grand_total'],
        ]);
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:999',
        ]);

        $cart = Session::get('storefront_cart', ['items' => [], 'coupon' => null, 'notes' => '']);
        $key = $this->findCartItemKeyById($cart, $id);

        if ($key === null) {
            return response()->json([
                'success' => false,
                'message' => __('storefront.cart.item_not_found'),
            ], 404);
        }

        $quantity = $request->integer('quantity');

        if ($quantity === 0) {
            unset($cart['items'][$key]);
            $cart['items'] = array_values($cart['items']);
        } else {
            $product = Product::find($cart['items'][$key]['product_id']);

            if ($product && $quantity > $product->available_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => __('storefront.cart.insufficient_stock', ['available' => $product->available_quantity]),
                ], 422);
            }

            $cart['items'][$key]['quantity'] = $quantity;
        }

        Session::put('storefront_cart', $cart);

        $hydratedCart = $this->hydrateCart($cart);
        $totals = $this->calculateTotals($hydratedCart);

        return response()->json([
            'success' => true,
            'message' => $quantity === 0 ? __('storefront.cart.removed') : __('storefront.cart.updated'),
            'cart_count' => $this->getCartCount($cart),
            'item_total' => $product ? $product->getEffectiveSalePriceAttribute() * $quantity : 0,
            'cart_total' => $totals['grand_total'],
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function remove(string $id): JsonResponse
    {
        $cart = Session::get('storefront_cart', ['items' => [], 'coupon' => null, 'notes' => '']);
        $key = $this->findCartItemKeyById($cart, $id);

        if ($key === null) {
            return response()->json([
                'success' => false,
                'message' => __('storefront.cart.item_not_found'),
            ], 404);
        }

        unset($cart['items'][$key]);
        $cart['items'] = array_values($cart['items']);
        Session::put('storefront_cart', $cart);

        $hydratedCart = $this->hydrateCart($cart);
        $totals = $this->calculateTotals($hydratedCart);

        return response()->json([
            'success' => true,
            'message' => __('storefront.cart.removed'),
            'cart_count' => $this->getCartCount($cart),
            'cart_total' => $totals['grand_total'],
        ]);
    }

    /**
     * Clear entire cart.
     */
    public function clear(): JsonResponse
    {
        Session::forget('storefront_cart');

        return response()->json([
            'success' => true,
            'message' => __('storefront.cart.cleared'),
            'cart_count' => 0,
            'cart_total' => 0,
        ]);
    }

    /**
     * Get cart item count.
     */
    public function count(): JsonResponse
    {
        $cart = Session::get('storefront_cart', ['items' => []]);

        return response()->json([
            'count' => $this->getCartCount($cart),
        ]);
    }

    /**
     * Get cart drawer partial.
     */
    public function drawer(): \Illuminate\View\View
    {
        $cart = $this->getCartWithProducts();
        $totals = $this->calculateTotals($cart);

        return view('storefront.partials.cart-drawer', compact('cart', 'totals'));
    }

    /**
     * Apply coupon code.
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        // TODO: Implement coupon validation logic
        // For now, just return not implemented
        return response()->json([
            'success' => false,
            'message' => __('storefront.cart.coupon_not_implemented'),
        ], 501);
    }

    /**
     * Remove coupon.
     */
    public function removeCoupon(): JsonResponse
    {
        $cart = Session::get('storefront_cart', ['items' => [], 'coupon' => null, 'notes' => '']);
        $cart['coupon'] = null;
        Session::put('storefront_cart', $cart);

        return response()->json([
            'success' => true,
            'message' => __('storefront.cart.coupon_removed'),
        ]);
    }

    /**
     * API: Get full cart data.
     */
    public function apiCart(): JsonResponse
    {
        $cart = $this->getCartWithProducts();
        $totals = $this->calculateTotals($cart);

        return response()->json([
            'items' => $cart['items'],
            'coupon' => $cart['coupon'],
            'notes' => $cart['notes'],
            'totals' => $totals,
            'count' => $this->getCartCount($cart),
        ]);
    }

    /**
     * API: Add to cart.
     */
    public function apiAdd(Request $request): JsonResponse
    {
        return $this->add($request);
    }

    /**
     * API: Update cart item.
     */
    public function apiUpdate(Request $request, string $id): JsonResponse
    {
        return $this->update($request, $id);
    }

    /**
     * API: Remove from cart.
     */
    public function apiRemove(string $id): JsonResponse
    {
        return $this->remove($id);
    }

    /**
     * Get cart with product details hydrated.
     */
    protected function getCartWithProducts(): array
    {
        $cart = Session::get('storefront_cart', ['items' => [], 'coupon' => null, 'notes' => '']);
        return $this->hydrateCart($cart);
    }

    /**
     * Hydrate cart items with product models.
     */
    protected function hydrateCart(array $cart): array
    {
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
                // Product no longer available
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

        // TODO: Add shipping calculation
        $shipping = 0;

        // TODO: Apply coupon discount
        $discount = 0;

        $grandTotal = $subtotal + $taxAmount + $shipping - $discount;

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'shipping' => round($shipping, 2),
            'discount' => round($discount, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    /**
     * Get total item count in cart.
     */
    protected function getCartCount(array $cart): int
    {
        return collect($cart['items'])->sum('quantity');
    }

    /**
     * Get quantity of a product currently in cart.
     */
    protected function getCartItemQuantity(int $productId): int
    {
        $cart = Session::get('storefront_cart', ['items' => []]);
        $key = $this->findCartItemKey($cart, $productId);

        return $key !== null ? $cart['items'][$key]['quantity'] : 0;
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

    /**
     * Find cart item key by cart item ID.
     */
    protected function findCartItemKeyById(array $cart, string $id): ?int
    {
        foreach ($cart['items'] as $key => $item) {
            if ($item['id'] === $id) {
                return $key;
            }
        }
        return null;
    }
}