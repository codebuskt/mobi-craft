# MobiCraft - Architecture Overview

## System Architecture

MobiCraft follows a **Modular Monolith** architecture with clear API-first boundaries between modules. The application is a single deployable Laravel application with clearly separated business modules.

## Module Structure

```
app/
├── Domain/
│   ├── Tenancy/          # Multi-tenant infrastructure
│   ├── Identity/         # Users, roles, permissions
│   ├── Subscriptions/    # Plans, billing, trials
│   ├── Customers/        # Customer management
│   ├── Suppliers/        # Supplier management
│   ├── Catalog/          # Products, categories, brands
│   ├── Inventory/        # Stock movements, adjustments
│   ├── Purchasing/       # Purchase orders, receiving
│   ├── Sales/            # POS, invoicing, payments
│   ├── Repairs/          # Repair tickets, workflow
│   ├── UsedDevices/      # IMEI-tracked devices
│   ├── Expenses/         # Business expenses
│   ├── Staff/            # Employee management
│   ├── Payroll/          # Salary, advances, deductions
│   ├── Accounting/       # Financial records, reports
│   ├── Notifications/    # In-app, email, SMS
│   ├── Reporting/        # Business intelligence
│   ├── Audit/            # Activity logs, audit trail
│   └── AI/               # Future AI integration
├── Application/
│   ├── Actions/          # Business operation actions
│   ├── DTOs/             # Data transfer objects
│   ├── Services/         # Application services
│   └── Queries/          # Read models, projections
├── Infrastructure/
│   ├── Persistence/      # Eloquent models, repositories
│   ├── Integrations/     # External APIs, services
│   ├── AI/               # AI provider adapters
│   └── Notifications/    # Notification channels
└── Filament/
    ├── Admin/            # SaaS Super Admin panel
    ├── Shop/             # Tenant admin panel
    └── Staff/            # Staff/Technician panel
```

## Multi-Tenant Architecture

### Tenant Model
- **Strategy**: Shared database, shared tables with `tenant_id` column
- **Isolation**: Global scopes + middleware + policies + tests
- **Identification**: Subdomain, custom domain, header, authenticated user context

### Core Tables
| Table | Tenant Scoped | Description |
|-------|---------------|-------------|
| `tenants` | No | Tenant registry |
| `subscription_plans` | No | SaaS plan definitions |
| `users` | Yes | User accounts |
| `roles` | Mixed | System + tenant roles |
| `permissions` | No | Global permissions |
| `role_permission` | No | Role-permission pivot |

### Tenant Resolution Flow
```
Request → TenantMiddleware → Identify Tenant → Set Current Tenant → Global Scope Applied
```

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.3+, Laravel 11.x |
| Admin Panel | Filament 3.x |
| Reactive UI | Livewire 3.x, Alpine.js |
| Styling | Tailwind CSS |
| Database | MySQL 8.0+ |
| Testing | Pest PHP |
| API Auth | Laravel Sanctum |
| Queue/Jobs | Database (Redis optional) |

## Security Model

### Role Hierarchy
1. **Super Admin** (SaaS level) - `is_tenant_scoped = false`
2. **Shop Owner** - Full tenant access
3. **Shop Manager** - Operations management
4. **Cashier** - POS & sales
5. **Technician** - Repairs
6. **Inventory Manager** - Stock & purchasing
7. **Accountant** - Financial records

### Authorization
- Explicit permission checks via `User::hasPermission()`
- Policy classes for complex authorization
- Middleware enforcement on all routes
- Cross-tenant isolation tests

## API Architecture

### Versioning
- URL-based: `/api/v1/...`
- Standard REST conventions
- Consistent JSON responses

### Authentication
- Sanctum tokens for API clients
- Session auth for Filament panels
- Tenant-aware token validation

## Database Design Principles

- Use foreign keys for referential integrity
- DECIMAL for all monetary values (10,2)
- Timestamps on all tables
- Soft deletes for business records
- Composite unique indexes for tenant-scoped data
- Stock movement ledger pattern (not direct quantity updates)

## Development Standards

- Code style: Laravel Pint
- Static analysis: PHPStan (planned)
- Testing: Pest with parallel execution
- Documentation: Architecture Decision Records (ADRs)
- Git workflow: Feature branches, PR reviews

## Deployment Requirements

- PHP 8.3+
- MySQL 8.0+
- Redis (optional, for cache/queues)
- SSL/HTTPS
- Queue workers for background jobs
- Scheduler for cron tasks
- Backup strategy for database/files