# MobiCraft - Development Guide

## Getting Started

### Prerequisites
- PHP 8.3+
- Composer 2.x
- Node.js 20+ & npm
- MySQL 8.0+ (or SQLite for local dev)
- Git

### Installation

```bash
# Clone repository
git clone <repo-url> mobi-craft
cd mobi-craft

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Environment setup
cp .env.example .env
# Edit .env with your database credentials

# Generate app key
php artisan key:generate

# Run migrations and seeders
php artisan migrate --seed

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

### Environment Variables

```env
APP_NAME=MobiCraft
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mobicraft
DB_USERNAME=root
DB_PASSWORD=

# Tenant
TENANT_BASE_DOMAIN=mobicraft.test
TENANT_SUBDOMAIN_PREFIX=shop

# Mail (for Breeze auth)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@mobicraft.test
MAIL_FROM_NAME="MobiCraft"

# Queue
QUEUE_CONNECTION=database

# Cache
CACHE_STORE=database
```

### Local Development Domains

Add to `/etc/hosts` (Linux/Mac) or `C:\Windows\System32\drivers\etc\hosts` (Windows):
```
127.0.0.1   mobicraft.test
127.0.0.1   admin.mobicraft.test
127.0.0.1   shop1.mobicraft.test
127.0.0.1   shop2.mobicraft.test
```

Then run:
```bash
php artisan serve --host=mobicraft.test --port=80
```

Or use Laravel Valet / Herd for automatic subdomain handling.

## Development Workflow

### Branch Strategy
```
main                    # Production-ready
├── develop             # Integration branch
├── feature/xxx         # New features
├── fix/xxx             # Bug fixes
├── refactor/xxx        # Code improvements
└── release/x.x.x       # Release preparation
```

### Commit Convention
```
type(scope): description

Types: feat, fix, refactor, docs, test, chore, style, perf
Scopes: tenant, auth, sales, repairs, inventory, ui, api, db

Examples:
feat(tenant): add custom domain support
fix(sales): correct tax calculation on POS
refactor(repairs): simplify status transitions
```

### Pull Request Process
1. Create feature branch from `develop`
2. Implement changes with tests
3. Run `php artisan test` and `php artisan pint`
4. Update documentation if needed
5. Create PR to `develop`
6. Code review required
7. Merge after approval

## Code Standards

### PHP (Laravel Pint)
```bash
# Check style
php artisan pint --test

# Fix style
php artisan pint
```

### Testing (Pest)
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/Tenancy/IsolationTest.php

# Parallel execution
php artisan test --parallel
```

### Architecture Rules
1. **No business logic in controllers** → Use Actions/Services
2. **No tenant_id in requests** → Use global scope + policies
3. **No direct model manipulation in HTTP layer** → Use FormRequests + Actions
4. **Financial calculations** → Use `Decimal`/`Money` objects, never float
5. **Database transactions** → Required for multi-table operations

### File Organization
```
app/
├── Actions/              # Single-purpose business operations
│   ├── Tenant/
│   ├── Customer/
│   ├── Sale/
│   └── Repair/
├── DTOs/                 # Data transfer objects
├── Services/             # Complex business logic
├── Queries/              # Read models, reports
├── Filament/
│   ├── Admin/            # Super admin resources
│   ├── Shop/             # Tenant admin resources
│   └── Staff/            # Staff panel resources
├── Http/
│   ├── Controllers/      # Thin controllers
│   ├── Requests/         # Form requests
│   ├── Resources/        # API resources
│   └── Middleware/       # Custom middleware
├── Models/               # Eloquent models
├── Policies/             # Authorization
├── Scopes/               # Eloquent scopes
└── Traits/               # Reusable model traits
```

## Key Development Patterns

### Creating a New Module

1. **Create migration**
```bash
php artisan make:migration create_products_table --create=products
```

2. **Create model**
```bash
php artisan make:model Product -m
```

3. **Add tenant_id to migration** (see DATABASE.md)

4. **Register in TenantScope** (automatic if tenant_id column exists)

5. **Create Filament resource**
```bash
php artisan make:filament-resource Product --generate
```

6. **Create policy**
```bash
php artisan make:policy ProductPolicy --model=Product
```

7. **Write tests**
```bash
php artisan make:test ProductTest --feature
```

### Action Pattern
```php
// app/Actions/Sale/CompleteSale.php
class CompleteSale
{
    public function execute(SaleData $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::create([
                'tenant_id' => $data->tenantId,
                'customer_id' => $data->customerId,
                'user_id' => $data->userId,
                'total_amount' => $data->calculateTotal(),
                // ...
            ]);
            
            foreach ($data->items as $item) {
                $sale->items()->create([...]);
                
                // Stock movement
                InventoryMovement::create([
                    'tenant_id' => $data->tenantId,
                    'product_id' => $item->productId,
                    'type' => 'sale',
                    'quantity' => -$item->quantity,
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                ]);
            }
            
            return $sale->load('items.product');
        });
    }
}
```

### DTO Pattern
```php
// app/DTOs/SaleData.php
readonly class SaleData
{
    public function __construct(
        public int $tenantId,
        public ?int $customerId,
        public int $userId,
        public array $items, // [['product_id' => 1, 'quantity' => 2, 'price' => 100]]
        public string $paymentMethod,
        public ?string $notes = null,
    ) {}
    
    public function calculateTotal(): float
    {
        return collect($this->items)->sum(fn ($item) => $item['price'] * $item['quantity']);
    }
}
```

### API Resource Pattern
```php
// app/Http/Resources/SaleResource.php
class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'total_amount' => (string) $this->total_amount,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
```

## Testing Guidelines

### Test Categories
| Category | Location | Purpose |
|----------|----------|---------|
| Unit | `tests/Unit/` | Business logic, calculations |
| Feature | `tests/Feature/` | HTTP endpoints, workflows |
| Authorization | `tests/Feature/Auth/` | Policies, gates |
| Tenancy | `tests/Feature/Tenancy/` | Cross-tenant isolation |
| API | `tests/Feature/Api/` | REST endpoints |

### Test Helpers
```php
// tests/Pest.php or bootstrap
use App\Models\{Tenant, User, Customer};

function createTenant(array $attributes = []): Tenant
{
    return Tenant::factory()->create($attributes);
}

function createCustomer(Tenant $tenant, array $attributes = []): Customer
{
    return Customer::factory()->for($tenant)->create($attributes);
}

function actingAsTenant(Tenant $tenant, User $user = null): TestResponse
{
    return test()->actingAs($user ?? $tenant->users()->first());
}
```

### Example Tests
```php
// tests/Feature/Sales/SaleTest.php
test('sale creates inventory movements', function () {
    $tenant = createTenant();
    $product = Product::factory()->for($tenant)->create(['stock' => 100]);
    $customer = createCustomer($tenant);
    $user = $tenant->users()->first();
    
    actingAsTenant($tenant, $user)
        ->postJson('/api/v1/sales', [
            'customer_id' => $customer->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'price' => 100],
            ],
            'payment_method' => 'cash',
        ])
        ->assertCreated()
        ->assertJsonPath('total_amount', '500.00');
    
    expect($product->fresh()->stock)->toBe(95);
    expect(InventoryMovement::where('product_id', $product->id)->where('type', 'sale')->exists())->toBeTrue();
});

// Cross-tenant isolation test
test('tenant A cannot see tenant B sales', function () {
    $tenantA = createTenant()->hasUsers()->create();
    $tenantB = createTenant()->hasUsers()->create();
    
    $saleA = Sale::factory()->for($tenantA)->create();
    
    actingAsTenant($tenantA)
        ->getJson("/api/v1/sales/{$saleA->id}")
        ->assertOk();
    
    actingAsTenant($tenantB)
        ->getJson("/api/v1/sales/{$saleA->id}")
        ->assertNotFound();
});
```

## Debugging

### Telescope (Development)
```bash
# Install
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate

# Access at /telescope
```

### Logs
```bash
# View logs
tail -f storage/logs/laravel.log

# Clear logs
php artisan log:clear
```

### Common Commands
```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# Queue worker
php artisan queue:work --tries=3

# Scheduler
php artisan schedule:work
```

## Deployment Checklist

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` set
- [ ] HTTPS configured
- [ ] Queue workers running
- [ ] Scheduler cron configured
- [ ] Database backups scheduled
- [ ] File storage configured (S3/local)
- [ ] Mail driver configured
- [ ] Error tracking (Sentry/Flare)
- [ ] Health checks endpoint (`/up`)
- [ ] `php artisan optimize` run