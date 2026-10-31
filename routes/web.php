<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

// Storefront routes for public customer-facing site
// These use a separate middleware stack with tenant resolution
require __DIR__.'/storefront.php';
