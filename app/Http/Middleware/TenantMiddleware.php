<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Routes that should not have tenant middleware applied.
     */
    protected array $except = [
        '/',
        '/up',
        '/health',
        'sanctum/csrf-cookie',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if this route should not have tenant middleware
        if ($this->shouldSkip($request)) {
            return $next($request);
        }

        $tenant = $this->identifyTenant($request);

        if ($tenant) {
            Tenant::setCurrentTenant($tenant);

            // Bind tenant to container for easy access
            app()->instance('tenant', $tenant);

            // Share tenant with views
            view()->share('currentTenant', $tenant);
        }

        return $next($request);
    }

    /**
     * Determine if the request should skip tenant identification.
     */
    protected function shouldSkip(Request $request): bool
    {
        // Skip for public API routes (login, register, password reset, etc.)
        if ($request->is('api/*')) {
            // Check if this is a public auth route
            $publicRoutes = [
                'api/login',
                'api/register',
                'api/password/email',
                'api/password/reset',
                'api/password/update',
                'api/email/verification-notification',
                'api/email/verify',
            ];

            foreach ($publicRoutes as $route) {
                if ($request->is($route)) {
                    return true;
                }
            }
        }

        // Skip for web routes that should be public
        if (! $request->is('api/*')) {
            $publicRoutes = [
                'login',
                'register',
                'password/*',
                'email/*',
                'sanctum/csrf-cookie',
                'up',
                'health',
            ];

            foreach ($publicRoutes as $route) {
                if ($request->is($route)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Identify the tenant from the request.
     */
    protected function identifyTenant(Request $request): ?Tenant
    {
        // 1. Check for subdomain (e.g., shop1.mobicraft.test)
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

        // 2. Check for tenant header (for API requests)
        if ($tenantId = $request->header('X-Tenant-ID')) {
            if ($tenant = Tenant::where('id', $tenantId)->where('status', 'active')->first()) {
                return $tenant;
            }
        }

        // 3. Check for authenticated user's tenant
        if (Auth::check() && Auth::user()->tenant_id) {
            if ($tenant = Tenant::find(Auth::user()->tenant_id)) {
                return $tenant;
            }
        }

        // 4. Check session for tenant context
        if ($tenantId = $request->session()->get('tenant_id')) {
            if ($tenant = Tenant::find($tenantId)) {
                return $tenant;
            }
        }

        return null;
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
