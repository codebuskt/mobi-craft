<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class StorefrontMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Identify tenant from subdomain/custom domain
        $tenant = $this->identifyTenant($request);

        if (! $tenant) {
            abort(404, 'Store not found');
        }

        // Check if tenant is active and has storefront enabled
        if (! $tenant->is_active || ! $tenant->shopProfile?->is_setup_complete) {
            abort(503, 'Store is currently unavailable');
        }

        // Set current tenant
        Tenant::setCurrentTenant($tenant);
        app()->instance('tenant', $tenant);
        View::share('currentTenant', $tenant);

        // Share shop profile with views
        View::share('shopProfile', $tenant->shopProfile);

        // Initialize customer session if logged in
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();

            // Verify customer belongs to current tenant
            if ($customer->tenant_id !== $tenant->id) {
                Auth::guard('customer')->logout();
            } else {
                View::share('currentCustomer', $customer);
                $customer->updateQuietly(['last_login_at' => now()]);
            }
        }

        // Share cart with views
        View::share('cart', $this->getCart());

        // Set locale from tenant or session
        $locale = Session::get('locale', $tenant->shopProfile?->locale ?? 'en');
        app()->setLocale($locale);
        View::share('currentLocale', $locale);

        return $next($request);
    }

    /**
     * Identify the tenant from the request.
     */
    protected function identifyTenant(Request $request): ?Tenant
    {
        $host = $request->getHost();
        $domain = $request->getHttpHost();

        // Remove port if present
        if (str_contains($domain, ':')) {
            $domain = substr($domain, 0, strpos($domain, ':'));
        }

        // Check for tenant by custom domain first
        if ($tenant = Tenant::where('domain', $domain)->where('status', 'active')->first()) {
            return $tenant;
        }

        // Check for tenant by subdomain
        if (str_contains($host, '.') && ! str_contains($host, 'localhost')) {
            $parts = explode('.', $host);
            if (count($parts) > 2) {
                $subdomain = $parts[0];
                if ($tenant = Tenant::where('slug', $subdomain)->where('status', 'active')->first()) {
                    return $tenant;
                }
            }
        }

        // For local development without subdomains, check for tenant in session
        if ($tenantId = Session::get('storefront_tenant_id')) {
            return Tenant::where('id', $tenantId)->where('status', 'active')->first();
        }

        // Local development fallback: if accessing via localhost, try to find a demo tenant
        // This allows testing without subdomain configuration
        if (app()->environment('local') && (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1'))) {
            // Try to find a tenant marked as demo or the first active tenant
            return Tenant::where('status', 'active')->first();
        }

        return null;
    }

    /**
     * Get the current cart from session.
     */
    protected function getCart(): array
    {
        return Session::get('storefront_cart', [
            'items' => [],
            'coupon' => null,
            'notes' => '',
        ]);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Clear tenant context
        Tenant::setCurrentTenant(null);
        app()->forgetInstance('tenant');
    }
}