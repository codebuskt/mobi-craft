<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display all categories.
     */
    public function index(): View
    {
        $categories = Category::currentTenant()
            ->active()
            ->where('is_storefront_visible', true)
            ->root()
            ->with(['children' => fn ($q) => $q->active()->where('is_storefront_visible', true)->withCount('products')])
            ->withCount(['products' => fn ($q) => $q->where('is_published', true)->where('status', 'active')])
            ->orderBy('sort_order')
            ->get();

        return view('storefront.categories.index', compact('categories'));
    }

    /**
     * Display a single category with products.
     */
    public function show(Category $category): View
    {
        // Verify category belongs to current tenant and is visible
        if ($category->tenant_id !== app('tenant')->id || ! $category->is_active || ! $category->is_storefront_visible) {
            abort(404);
        }

        // Get all descendant category IDs
        $categoryIds = $this->getDescendantIds($category);

        $products = Product::currentTenant()
            ->active()
            ->posVisible()
            ->where('is_published', true)
            ->whereIn('category_id', $categoryIds)
            ->with(['category', 'brand', 'thumbnail'])
            ->latest()
            ->paginate(12);

        // Sibling categories for navigation
        $siblings = Category::currentTenant()
            ->active()
            ->where('is_storefront_visible', true)
            ->where('parent_id', $category->parent_id)
            ->orderBy('sort_order')
            ->get();

        // Breadcrumbs
        $breadcrumbs = $this->getBreadcrumbs($category);

        return view('storefront.categories.show', compact(
            'category',
            'products',
            'siblings',
            'breadcrumbs'
        ));
    }

    /**
     * Get all descendant category IDs including self.
     */
    protected function getDescendantIds(Category $category): array
    {
        $ids = [$category->id];

        $children = $category->children()->get();

        foreach ($children as $child) {
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }

        return $ids;
    }

    /**
     * Get breadcrumb trail for category.
     */
    protected function getBreadcrumbs(Category $category): array
    {
        $breadcrumbs = [['name' => $category->name, 'url' => null]];
        $parent = $category->parent;

        while ($parent) {
            array_unshift($breadcrumbs, [
                'name' => $parent->name,
                'url' => route('storefront.categories.show', $parent->slug)
            ]);
            $parent = $parent->parent;
        }

        // Add "All Categories" at the beginning
        array_unshift($breadcrumbs, [
            'name' => __('storefront.categories'),
            'url' => route('storefront.categories.index')
        ]);

        return $breadcrumbs;
    }
}