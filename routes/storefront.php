<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\OrderController;
use App\Http\Controllers\Storefront\RepairTrackingController;
use App\Http\Controllers\Storefront\CustomerAuthController;
use App\Http\Controllers\Storefront\CustomerAccountController;

/*
|--------------------------------------------------------------------------
| Storefront Routes
|--------------------------------------------------------------------------
|
| Public-facing customer storefront routes for tenant subdomains.
| All routes are protected by StorefrontMiddleware which handles
| tenant resolution and customer authentication.
|
*/

Route::middleware(['web', 'storefront'])->group(function () {
    // Language switching
    Route::get('/locale/{locale}', function (string $locale) {
        if (in_array($locale, ['en', 'bn'])) {
            session(['locale' => $locale]);
        }
        return redirect()->back();
    })->name('storefront.locale');

    // Home page
    Route::get('/', [HomeController::class, 'index'])->name('storefront.home');

    // Product catalog
    Route::prefix('products')->name('storefront.products.')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        Route::get('/featured', [ProductController::class, 'featured'])->name('featured');
        Route::get('/on-sale', [ProductController::class, 'onSale'])->name('on-sale');
        Route::get('/{product:slug}', [ProductController::class, 'show'])->name('show');
    });

    // Categories
    Route::prefix('categories')->name('storefront.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/{category:slug}', [CategoryController::class, 'show'])->name('show');
    });

    // Cart
    Route::prefix('cart')->name('storefront.cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::patch('/{id}', [CartController::class, 'update'])->name('update');
        Route::delete('/{id}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
        Route::get('/count', [CartController::class, 'count'])->name('count');
        Route::get('/drawer', [CartController::class, 'drawer'])->name('drawer');
        Route::post('/coupon', [CartController::class, 'applyCoupon'])->name('coupon.apply');
        Route::delete('/coupon', [CartController::class, 'removeCoupon'])->name('coupon.remove');
    });

    // Checkout
    Route::prefix('checkout')->name('storefront.checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/address', [CheckoutController::class, 'storeAddress'])->name('address.store');
        Route::post('/shipping', [CheckoutController::class, 'storeShipping'])->name('shipping.store');
        Route::post('/payment', [CheckoutController::class, 'storePayment'])->name('payment.store');
        Route::post('/place-order', [CheckoutController::class, 'placeOrder'])->name('place-order');
        Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    });

    // Customer Authentication
    Route::prefix('account')->name('storefront.account.')->group(function () {
        // Guest routes
        Route::middleware('guest:customer')->group(function () {
            Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
            Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.post');
            Route::get('/register', [CustomerAuthController::class, 'showRegistrationForm'])->name('register');
            Route::post('/register', [CustomerAuthController::class, 'register'])->name('register.post');
            Route::get('/password/reset', [CustomerAuthController::class, 'showLinkRequestForm'])->name('password.request');
            Route::post('/password/email', [CustomerAuthController::class, 'sendResetLinkEmail'])->name('password.email');
            Route::get('/password/reset/{token}', [CustomerAuthController::class, 'showResetForm'])->name('password.reset');
            Route::post('/password/reset', [CustomerAuthController::class, 'reset'])->name('password.update');
        });

        // Authenticated customer routes
        Route::middleware('auth:customer')->group(function () {
            Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

            Route::get('/', [CustomerAccountController::class, 'index'])->name('index');
            Route::get('/profile', [CustomerAccountController::class, 'profile'])->name('profile');
            Route::patch('/profile', [CustomerAccountController::class, 'updateProfile'])->name('profile.update');
            Route::patch('/password', [CustomerAccountController::class, 'updatePassword'])->name('password.change');

            Route::get('/orders', [CustomerAccountController::class, 'orders'])->name('orders');
            Route::get('/orders/{order}', [CustomerAccountController::class, 'showOrder'])->name('orders.show');
            Route::post('/orders/{order}/reorder', [CustomerAccountController::class, 'reorder'])->name('orders.reorder');

            Route::get('/repairs', [CustomerAccountController::class, 'repairs'])->name('repairs');
            Route::get('/repairs/{repair}', [CustomerAccountController::class, 'showRepair'])->name('repairs.show');

            Route::get('/addresses', [CustomerAccountController::class, 'addresses'])->name('addresses');
            Route::post('/addresses', [CustomerAccountController::class, 'storeAddress'])->name('addresses.store');
            Route::patch('/addresses/{address}', [CustomerAccountController::class, 'updateAddress'])->name('addresses.update');
            Route::delete('/addresses/{address}', [CustomerAccountController::class, 'destroyAddress'])->name('addresses.destroy');
        });
    });

    // Order history (accessible without login via order number + email/phone)
    Route::prefix('orders')->name('storefront.orders.')->group(function () {
        Route::get('/lookup', [OrderController::class, 'lookup'])->name('lookup');
        Route::post('/lookup', [OrderController::class, 'find'])->name('find');
        Route::get('/{order}/track', [OrderController::class, 'track'])->name('track');
    });

    // Repair tracking (public - no auth required)
    Route::prefix('repairs')->name('storefront.repairs.')->group(function () {
        Route::get('/track', [RepairTrackingController::class, 'showTrackForm'])->name('track');
        Route::post('/track', [RepairTrackingController::class, 'track'])->name('track.post');
        Route::get('/{repairTicket}/status', [RepairTrackingController::class, 'status'])->name('status');
    });

    // Contact page
    Route::get('/contact', [HomeController::class, 'contact'])->name('storefront.contact');

    // API endpoints for AJAX (cart drawer, etc.)
    Route::prefix('api')->name('storefront.api.')->group(function () {
        Route::get('/cart', [CartController::class, 'apiCart'])->name('cart');
        Route::post('/cart/add', [CartController::class, 'apiAdd'])->name('cart.add');
        Route::patch('/cart/{id}', [CartController::class, 'apiUpdate'])->name('cart.update');
        Route::delete('/cart/{id}', [CartController::class, 'apiRemove'])->name('cart.remove');
    });
});