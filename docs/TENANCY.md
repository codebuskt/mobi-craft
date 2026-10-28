# MobiCraft - Multi-Tenancy Strategy

## Overview

MobiCraft implements multi-tenancy using the **shared database, shared tables** approach with a `tenant_id` column on all tenant-scoped tables.

## Tenant Identification Strategies

### 1. Subdomain (Primary)
```
shop1.mobicraft.test → Tenant where slug = 'shop1'
admin.mobicraft.test → Super admin tenant
```

### 2. Custom Domain (Enterprise)
```
shop.mystore.com → Tenant where domain = 'shop.mystore.com'
```

### 3. API Header (Mobile Apps)
```
X-Tenant-ID: 123
```

### 4. Authenticated User Context
```
User → tenant_id → Tenant
```

### 5. Session (Fallback)
```
session('tenant_id') → Tenant
```

## Implementation Components

### 1. Tenant Model (`App\Models\Tenant`)
- Holds static `$currentTenant` property
- Methods: `getCurrentTenant()`, `setCurrentTenant()`
- Relationships: `users()`, `plan()`

### 2. TenantScope (`App\Scopes\TenantScope`)
Global Eloquent scope that automatically adds `WHERE tenant_id = ?` to all queries on models with a `tenant_id` column.

**Applied via**: `TenantServiceProvider` boot method
```php
Model::addGlobalScope(new TenantScope());
```

### 3. TenantMiddleware (`App\Http\Middleware\TenantMiddleware`)
Runs on every web request to identify and set the current tenant.

**Registered in**: `bootstrap/app.php`
```php
$middleware->web(prepend: [
    \App\Http\Middleware\TenantMiddleware::class,
]);
```

### 4. TenantServiceProvider (`App\Providers\TenantServiceProvider`)
Registers global scope, tenant-aware queue, and notifications.

## Usage Patterns

### In Controllers
```php
// Current tenant (set by middleware)
$tenant = app('tenant'); // or Tenant::getCurrentTenant()

// Query within tenant scope
$customers = Customer::all(); // Auto-filtered by tenant_id

// Query without tenant scope (super admin only)
$customers = Customer::withoutTenantScope()->get();
```

### In Models
```php
// Add to any tenant-scoped model
protected $fillable = [..., 'tenant_id'];

// Or use trait
use App\Traits\HasTenantScope;
```

### In Policies
```php
public function view(User $user, Customer $customer)
{
    return $user->tenant_id === $customer->tenant_id;
}
```

### In Jobs (Queue)
```php
// Job automatically gets tenant context via middleware termination
// Or explicitly:
class ProcessSale implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        $tenant = app('tenant'); // Available if dispatched from web request
        // ...
    }
}
```

## Cross-Tenant Security

### Mandatory Tests
Every feature must pass these isolation tests:

```php
// tests/Feature/Tenancy/IsolationTest.php
test('tenant A cannot access tenant B customers', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();
    
    $customerA = Customer::factory()->for($tenantA)->create();
    
    actingAs($tenantA->users()->first());
    $this->getJson("/api/v1/customers/{$customerA->id}")->assertOk();
    
    actingAs($tenantB->users()->first());
    $this->getJson("/api/v1/customers/{$customerA->id}")->assertNotFound();
});
```

### Never Trust Client-Supplied Tenant ID
```php
// BAD - vulnerable to ID manipulation
$customer = Customer::find($request->input('customer_id'));

// GOOD - tenant scoped automatically
$customer = Customer::findOrFail($request->input('customer_id'));

// GOOD - explicit tenant check
$customer = Customer::where('tenant_id', auth()->user()->tenant_id)
    ->findOrFail($request->input('customer_id'));
```

## Tenant Onboarding Flow

```
1. Super Admin creates tenant via Filament
   → Tenant record created (status: trial)
   → Subscription plan assigned
   → Trial period starts

2. Tenant admin receives invitation
   → Sets up shop profile
   → Creates first users with roles

3. Tenant operates within plan limits
   → Feature flags enforced via SubscriptionPlan::hasFeature()
   → Limits enforced via SubscriptionPlan::getLimit()

4. Trial ends / subscription expires
   → Status changes to expired/suspended
   → Access restricted via middleware

5. Subscription renewed
   → Status → active
   → Full access restored
```

## Feature Flags & Limits

```php
// In controllers/services
if (! $tenant->plan->hasFeature('repairs')) {
    abort(403, 'Repair management not included in your plan');
}

if ($tenant->customers()->count() >= $tenant->plan->getLimit('customers')) {
    return response()->json(['error' => 'Customer limit reached'], 403);
}
```

## Super Admin vs Tenant Context

| Aspect | Super Admin | Tenant User |
|--------|-------------|-------------|
| `tenant_id` | `NULL` | Set |
| Panel | `/admin` | `/shop` (planned) |
| Resources | All tenants | Own tenant only |
| Global scopes | Bypassed with `withoutTenantScope()` | Always applied |
| Permissions | All (`super_admin` role) | Role-based |

## Testing Utilities

```php
// In TestCase or Pest helpers
function actingAsTenant(Tenant $tenant, User $user = null): TestResponse
{
    Tenant::setCurrentTenant($tenant);
    return $this->actingAs($user ?? $tenant->users()->first());
}

function withoutTenantScope(): void
{
    Tenant::setCurrentTenant(null);
}

// Usage
test('tenant isolation', function () {
    $tenantA = Tenant::factory()->hasUsers()->create();
    $tenantB = Tenant::factory()->hasUsers()->create();
    
    $customerA = Customer::factory()->for($tenantA)->create();
    
    actingAsTenant($tenantA)->getJson("/api/customers/{$customerA->id}")->assertOk();
    actingAsTenant($tenantB)->getJson("/api/customers/{$customerA->id}")->assertNotFound();
});
```

## Configuration

### Environment Variables
```env
# Tenant identification
TENANT_BASE_DOMAIN=mobicraft.test
TENANT_SUBDOMAIN_PREFIX=shop

# Feature flags
TENANT_CUSTOM_DOMAINS_ENABLED=true
TENANT_SUBDOMAINS_ENABLED=true
```

### Cache Considerations
```php
// ALWAYS include tenant in cache keys
Cache::remember("tenant.{$tenant->id}.customers.count", 300, fn () => 
    Customer::count()
);

// NEVER cache tenant-sensitive data without tenant context
// BAD: Cache::remember('customers.list', ...)
// GOOD: Cache::remember("tenant.{$tenant->id}.customers.list", ...)
```

## Migration from Other Strategies

If migrating from separate databases/schemas:
1. Export tenant data
2. Import into shared tables with `tenant_id`
3. Update foreign keys
4. Run isolation tests

## Future Considerations

- **Database-per-tenant**: For enterprise customers with strict isolation requirements
- **Schema-per-tenant**: If custom columns per tenant needed
- **Hybrid**: Most tenants shared, enterprise on separate DB