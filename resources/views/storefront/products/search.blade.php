@extends('storefront.layouts.app')

@section('title', __('storefront.products.search_results', ['query' => $query]) . ' - ' . $shopProfile->business_name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('storefront.products.search_results', ['query' => $query]) }}</h1>
            <p class="text-gray-500 mt-1">{{ $products->total() }} {{ __('storefront.products.found') }}</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Sidebar Filters -->
            <aside class="lg:w-64 flex-shrink-0">
                <div class="bg-white rounded-xl border border-gray-100 p-6 sticky top-24">
                    <!-- Categories -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('storefront.products.categories') }}</h3>
                        <ul class="space-y-2">
                            @foreach($categories as $category)
                                <li>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="category" value="{{ $category->slug }}" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" {{ request('category') === $category->slug ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700">{{ $category->name }}</span>
                                        <span class="text-xs text-gray-400 ml-auto">({{ $category->products_count }})</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Brands -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('storefront.products.brands') }}</h3>
                        <ul class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($brands as $brand)
                                <li>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="brand" value="{{ $brand->id }}" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                        <span class="text-sm text-gray-700">{{ $brand->name }}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-gray-900 mb-4">{{ __('storefront.products.price_range') }}</h3>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="price_range" value="0-5000" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="text-sm text-gray-700">{{ __('storefront.products.under_5k') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="price_range" value="5000-15000" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="text-sm text-gray-700">{{ __('storefront.products.5k_15k') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="price_range" value="15000-30000" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="text-sm text-gray-700">{{ __('storefront.products.15k_30k') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="price_range" value="30000-999999" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                <span class="text-sm text-gray-700">{{ __('storefront.products.over_30k') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Stock Filter -->
                    <div class="mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="in_stock_only" value="1" class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500" {{ request('in_stock_only') ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700">{{ __('storefront.products.in_stock_only') }}</span>
                        </label>
                    </div>

                    <!-- Clear Filters -->
                    @if(request()->hasAny(['category', 'brand', 'price_range', 'min_price', 'max_price', 'in_stock_only', 'sort']))
                        <a href="{{ route('storefront.products.search', ['q' => $query]) }}" class="w-full text-center text-primary-600 hover:underline text-sm font-medium">{{ __('storefront.products.clear_filters') }}</a>
                    @endif
                </div>
            </aside>

            <!-- Products Grid -->
            <div class="flex-1">
                <!-- Toolbar -->
                <div class="bg-white rounded-xl border border-gray-100 p-4 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <!-- View Toggle -->
                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                            <button type="button" class="px-3 py-2 text-gray-500 hover:text-gray-700 hover:bg-gray-50" aria-label="Grid view">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                            </button>
                            <button type="button" class="px-3 py-2 text-primary-600 bg-primary-50 border-l border-gray-200" aria-label="List view">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            </button>
                        </div>

                        <!-- Sort -->
                        <select id="sort-select" class="px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent" onchange="window.location.href = this.value">
                            <option value="{{ route('storefront.products.search', array_merge(['q' => $query], request()->query(), ['sort' => 'newest'])) }}" {{ $sort === 'newest' ? 'selected' : '' }}>{{ __('storefront.products.sort_newest') }}</option>
                            <option value="{{ route('storefront.products.search', array_merge(['q' => $query], request()->query(), ['sort' => 'price_asc'])) }}" {{ $sort === 'price_asc' ? 'selected' : '' }}>{{ __('storefront.products.sort_price_asc') }}</option>
                            <option value="{{ route('storefront.products.search', array_merge(['q' => $query], request()->query(), ['sort' => 'price_desc'])) }}" {{ $sort === 'price_desc' ? 'selected' : '' }}>{{ __('storefront.products.sort_price_desc') }}</option>
                            <option value="{{ route('storefront.products.search', array_merge(['q' => $query], request()->query(), ['sort' => 'name_asc'])) }}" {{ $sort === 'name_asc' ? 'selected' : '' }}>{{ __('storefront.products.sort_name_asc') }}</option>
                            <option value="{{ route('storefront.products.search', array_merge(['q' => $query], request()->query(), ['sort' => 'name_desc'])) }}" {{ $sort === 'name_desc' ? 'selected' : '' }}>{{ __('storefront.products.sort_name_desc') }}</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">{{ $products->total() }} {{ __('storefront.products.results') }}</span>
                    </div>
                </div>

                <!-- Products Grid -->
                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="products-grid">
                        @foreach($products as $product)
                            @include('storefront.partials.product-card', ['product' => $product, 'variant' => 'grid'])
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-8 flex justify-center">
                        {{ $products->links('pagination::tailwind') }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('storefront.products.no_search_results', ['query' => $query]) }}</h3>
                        <p class="mt-2 text-gray-500">{{ __('storefront.products.no_search_results_desc') }}</p>
                        <a href="{{ route('storefront.products.index') }}" class="mt-6 inline-block text-primary-600 hover:underline">{{ __('storefront.products.browse_all_products') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection