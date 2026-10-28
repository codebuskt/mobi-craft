<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\TenantServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    TenantServiceProvider::class,
];
