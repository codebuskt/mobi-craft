<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the storefront home page.
     */
    public function index(): View
    {
        $tenant = app('tenant');
        $shopProfile = $tenant->shopProfile;

        // Featured products
        $featuredProducts = Product::currentTenant()
            ->active()
            ->posVisible()
            ->where('is_published', true)
            ->where('is_featured', true)
            ->with(['category', 'brand', 'thumbnail'])
            ->latest()
            ->take(8)
            ->get();

        // On sale products
        $onSaleProducts = Product::currentTenant()
            ->active()
            ->posVisible()
            ->where('is_published', true)
            ->whereNotNull('special_price')
            ->where('special_price_start', '<=', now())
            ->where('special_price_end', '>=', now())
            ->with(['category', 'brand', 'thumbnail'])
            ->latest()
            ->take(8)
            ->get();

        // New arrivals
        $newArrivals = Product::currentTenant()
            ->active()
            ->posVisible()
            ->where('is_published', true)
            ->with(['category', 'brand', 'thumbnail'])
            ->latest('published_at')
            ->take(8)
            ->get();

        // Root categories for navigation
        $categories = Category::currentTenant()
            ->active()
            ->where('is_storefront_visible', true)
            ->root()
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        // Banners/Promotions from shop profile settings
        $banners = $shopProfile?->getSetting('homepage_banners', []);

        // Featured categories
        $featuredCategoryIds = $shopProfile?->getSetting('featured_category_ids', []);
        $featuredCategories = Category::currentTenant()
            ->active()
            ->where('is_storefront_visible', true)
            ->whereIn('id', $featuredCategoryIds)
            ->withCount(['products' => fn ($q) => $q->where('is_published', true)->where('status', 'active')])
            ->get();

        return view('storefront.home', compact(
            'tenant',
            'shopProfile',
            'featuredProducts',
            'onSaleProducts',
            'newArrivals',
            'categories',
            'banners',
            'featuredCategories'
        ));
    }

    /**
     * Display the contact page.
     */
    public function contact(): View
    {
        $tenant = app('tenant');
        $shopProfile = $tenant->shopProfile;

        return view('storefront.contact', compact('tenant', 'shopProfile'));
    }
}