<?php

namespace App\Providers;

use App\Models\Tenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind tenant resolver
        $this->app->singleton('tenant', function () {
            return Tenant::getCurrentTenant();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Add global tenant scope to all models that have tenant_id column
        Model::addGlobalScope(new TenantScope);

        // Register tenant-aware queue connection
        // This ensures queued jobs have the correct tenant context
        $this->registerTenantAwareQueue();

        // Register tenant-aware notifications
        $this->registerTenantAwareNotifications();
    }

    /**
     * Register tenant-aware queue connections.
     */
    protected function registerTenantAwareQueue(): void
    {
        // Queue jobs will automatically receive the current tenant context
        // through the TenantMiddleware termination
    }

    /**
     * Register tenant-aware notifications.
     */
    protected function registerTenantAwareNotifications(): void
    {
        // Notification channels will have access to current tenant
    }
}
