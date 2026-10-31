<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of products with filters.
     */
    public function index(Request $request): View
    {
        $query = Product::currentTenant()
            ->active()
            ->posVisible()
            ->where('is_published', true)
            ->with(['category', 'brand', 'thumbnail']);

        // Category filter
        if ($request->filled('category')) {
            $category = Category::currentTenant()->where('slug', $request->category)->first();
            if ($category) {
                $categoryIds = $category->children()->pluck('id')->push($category->id);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Brand filter
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('sale_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('sale_price', '<=', $request->max_price);
        }

        // Stock filter
        if ($request->boolean('in_stock_only')) {
            $query->where('stock_quantity', '>', 0);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('sale_price', 'asc'),
            'price_desc' => $query->orderBy('sale_price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'popular' => $query->orderBy('total_sales', 'desc'), // Would need a column or join
            default => $query->latest('published_at'),
        };

        $products = $query->paginate(12)->withQueryString();

        // Get filter options
        $categories = Category::currentTenant()
            ->active()
            ->where('is_storefront_visible', true)
            ->withCount(['products' => fn ($q) => $q->where('is_published', true)->where('status', 'active')])
            ->get();

        $brands = Brand::currentTenant()
            ->whereHas('products', fn ($q) => $q->where('is_published', true)->where('status', 'active'))
            ->orderBy('name')
            ->get();

        return view('storefront.products.index', compact(
            'products',
            'categories',
            'brands',
            'sort'
        ));
    }

    /**
     * Search products.
     */
    public function search(Request $request): View
    {
        $query = $request->get('q', '');

        $products = Product::currentTenant()
            ->active()
            ->posVisible()
            ->where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%")
                  ->orWhere('short_description', 'like', "%{$query}%");
            })
            ->with(['category', 'brand', 'thumbnail'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('storefront.products.search', compact('products', 'query'));
    }

    /**
     * Display featured products.
     */
    public function featured(Request $request): View
    {
        $query = Product::currentTenant()
            ->active()
            ->posVisible()
            ->where('is_published', true)
            ->where('is_featured', true)
            ->with(['category', 'brand', 'thumbnail']);

        // Category filter
        if ($request->filled('category')) {
            $category = Category::currentTenant()->where('slug', $request->category)->first();
            if ($category) {
                $categoryIds = $category->children()->pluck('id')->push($category->id);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Brand filter
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('sale_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('sale_price', '<=', $request->max_price);
        }

        // Stock filter
        if ($request->boolean('in_stock_only')) {
            $query->where('stock_quantity', '>', 0);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('sale_price', 'asc'),
            'price_desc' => $query->orderBy('sale_price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->latest('published_at'),
        };

        $products = $query->paginate(12)->withQueryString();

        // Get filter options
        $categories = Category::currentTenant()
            ->active()
            ->where('is_storefront_visible', true)
            ->withCount(['products' => fn ($q) => $q->where('is_published', true)->where('status', 'active')->where('is_featured', true)])
            ->get();

        $brands = Brand::currentTenant()
            ->whereHas('products', fn ($q) => $q->where('is_published', true)->where('status', 'active')->where('is_featured', true))
            ->orderBy('name')
            ->get();

        return view('storefront.products.featured', compact(
            'products',
            'categories',
            'brands',
            'sort'
        ));
    }

    /**
     * Display on-sale products.
     */
    public function onSale(Request $request): View
    {
        $query = Product::currentTenant()
            ->active()
            ->posVisible()
            ->where('is_published', true)
            ->whereNotNull('special_price')
            ->where('special_price_start', '<=', now())
            ->where('special_price_end', '>=', now())
            ->with(['category', 'brand', 'thumbnail']);

        // Category filter
        if ($request->filled('category')) {
            $category = Category::currentTenant()->where('slug', $request->category)->first();
            if ($category) {
                $categoryIds = $category->children()->pluck('id')->push($category->id);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        // Brand filter
        if ($request->filled('brand')) {
            $query->where('brand_id', $request->brand);
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('sale_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('sale_price', '<=', $request->max_price);
        }

        // Stock filter
        if ($request->boolean('in_stock_only')) {
            $query->where('stock_quantity', '>', 0);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('sale_price', 'asc'),
            'price_desc' => $query->orderBy('sale_price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'discount' => $query->orderByRaw('((sale_price - special_price) / sale_price * 100) DESC'),
            default => $query->latest('published_at'),
        };

        $products = $query->paginate(12)->withQueryString();

        // Get filter options
        $categories = Category::currentTenant()
            ->active()
            ->where('is_storefront_visible', true)
            ->withCount(['products' => fn ($q) => $q->where('is_published', true)->where('status', 'active')->whereNotNull('special_price')->where('special_price_start', '<=', now())->where('special_price_end', '>=', now())])
            ->get();

        $brands = Brand::currentTenant()
            ->whereHas('products', fn ($q) => $q->where('is_published', true)->where('status', 'active')->whereNotNull('special_price')->where('special_price_start', '<=', now())->where('special_price_end', '>=', now()))
            ->orderBy('name')
            ->get();

        return view('storefront.products.on-sale', compact(
            'products',
            'categories',
            'brands',
            'sort'
        ));
    }

    /**
     * Display a single product.
     */
    public function show(Product $product): View
    {
        // Verify product belongs to current tenant and is visible
        if ($product->tenant_id !== app('tenant')->id || ! $product->is_published || $product->status !== 'active') {
            abort(404);
        }

        $product->load(['category', 'brand', 'images']);

        // Related products (same category, excluding current)
        $relatedProducts = Product::currentTenant()
            ->active()
            ->posVisible()
            ->where('is_published', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'brand', 'thumbnail'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        // Recently viewed products (from session)
        $recentlyViewed = $this->getRecentlyViewed($product->id);

        return view('storefront.products.show', compact(
            'product',
            'relatedProducts',
            'recentlyViewed'
        ));
    }

    /**
     * Get recently viewed products from session.
     */
    protected function getRecentlyViewed(int $currentProductId): \Illuminate\Support\Collection
    {
        $recentIds = session('recently_viewed_products', []);

        // Add current product to front
        $recentIds = array_unique(array_merge([$currentProductId], $recentIds));

        // Keep only last 10
        $recentIds = array_slice($recentIds, 0, 10);

        session(['recently_viewed_products' => $recentIds]);

        // Get products excluding current
        $ids = array_filter($recentIds, fn ($id) => $id !== $currentProductId);

        if (empty($ids)) {
            return collect();
        }

        return Product::currentTenant()
            ->whereIn('id', $ids)
            ->with(['category', 'thumbnail'])
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $recentIds));
    }
}