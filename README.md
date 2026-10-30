# MobiCraft

**Production-Ready Multi-Tenant SaaS for Mobile Retail & Repair Businesses**

> **মোবাইল ব্যবসার ডিজিটাল সঙ্গী** — আপনার মোবাইল ব্যবসা, আরও গোছানো।

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-3.x-007BFF?logo=filament&logoColor=white)](https://filamentphp.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6?logo=livewire&logoColor=white)](https://livewire.laravel.com)
[![Pest](https://img.shields.io/badge/Pest-3.x-25A36A?logo=pestphp&logoColor=white)](https://pestphp.com)
[![Tailwind](https://img.shields.io/badge/Tailwind-3.x-38B2AC?logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)](https://mysql.com)

---

## Overview

MobiCraft is a comprehensive SaaS platform designed for mobile phone shops, repair centers, and accessories retailers. Built with a modular monolith architecture, it provides complete business management capabilities while ensuring strict multi-tenant data isolation.

### Key Features

- **🏢 Multi-Tenant SaaS** - Isolated data per business with subscription management
- **📱 POS System** - Fast, keyboard-friendly point of sale with barcode support
- **🔧 Repair Management** - Complete ticket workflow from intake to delivery
- **📦 Inventory** - Stock movement ledger with real-time tracking
- **👥 Customer & Supplier CRM** - Full lifecycle management
- **💰 Financial** - Expenses, payroll, dues, payments, reporting
- **👨‍💼 Staff & Roles** - Granular permission system
- **📊 Dashboards** - Business intelligence and analytics
- **🌐 REST API** - Versioned API for mobile app integration
- **🌙 Dark Mode** - Full light/dark theme support
- **🌐 Bangla/English** - Bilingual UI support
- **📱 Used Device Management** - IMEI/serial tracking, purchase/inspection, refurbishment, margin reporting
- **💳 SaaS Billing** - Subscription plans, trial management, invoice generation, feature limits enforcement

---

## Tech Stack

| Category | Technology |
|----------|------------|
| **Backend** | PHP 8.3+, Laravel 11.x |
| **Admin Panel** | Filament 3.x |
| **Reactive UI** | Livewire 3.x, Alpine.js |
| **Styling** | Tailwind CSS |
| **Database** | MySQL 8.0+ / SQLite (dev) |
| **Testing** | Pest PHP |
| **API Auth** | Laravel Sanctum |
| **Code Style** | Laravel Pint |

---

## Quick Start

### Prerequisites
- PHP 8.3+
- Composer 2.x
- Node.js 20+ & npm
- MySQL 8.0+ (or SQLite for local)

### Installation

```bash
# Clone and enter directory
git clone <repository-url> mobi-craft
cd mobi-craft

# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
# Edit .env with database credentials

# Generate app key
php artisan key:generate

# Database setup
php artisan migrate --seed

# Build assets
npm run build

# Start server
php artisan serve
```

### Default Login (After Seeding)
- **Super Admin**: `super-admin@mobicraft.test` / `password`
- **Admin Panel**: `http://localhost:8000/admin`

---

## Project Structure

```
mobi-craft/
├── app/
│   ├── Actions/          # Business operations
│   ├── DTOs/             # Data transfer objects
│   ├── Filament/         # Admin/Shop/Staff panels
│   ├── Http/             # Controllers, Requests, Middleware
│   ├── Models/           # Eloquent models
│   ├── Policies/         # Authorization
│   ├── Scopes/           # Eloquent global scopes
│   ├── Services/         # Complex services
│   └── Traits/           # Reusable traits
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── docs/                 # Documentation
│   ├── ARCHITECTURE.md
│   ├── DATABASE.md
│   ├── DEVELOPMENT.md
│   ├── TENANCY.md
│   └── ...
├── resources/
│   ├── css/, js/         # Frontend assets
│   └── views/            # Blade templates
├── routes/
│   ├── api.php           # REST API
│   ├── web.php           # Web routes
│   └── console.php       # Artisan commands
├── tests/
│   ├── Feature/          # Feature tests
│   ├── Unit/             # Unit tests
│   └── Pest.php          # Pest config
└── vendor/
```

---

## Documentation

| Document | Description |
|----------|-------------|
| [Architecture](docs/ARCHITECTURE.md) | System architecture, modules, tech decisions |
| [Database](docs/DATABASE.md) | Complete schema, tables, relationships |
| [Tenancy](docs/TENANCY.md) | Multi-tenant strategy, implementation, testing |
| [Development](docs/DEVELOPMENT.md) | Setup, workflow, patterns, testing guide |
| [Security](docs/SECURITY.md) | Security model, authorization, best practices |
| [API](docs/API.md) | REST API reference (OpenAPI planned) |
| [Deployment](docs/DEPLOYMENT.md) | Production deployment guide |
| [Testing](docs/TESTING.md) | Testing strategy, patterns, CI/CD |

---

## Multi-Tenancy

MobiCraft uses **shared database, shared tables** with `tenant_id` column:

- **Identification**: Subdomain, custom domain, API header, user context
- **Isolation**: Global scopes + middleware + policies + automated tests
- **Security**: Never trust client-supplied tenant IDs

```php
// Automatic tenant scoping
$customers = Customer::all(); // Only current tenant's customers

// Super admin bypass
$customers = Customer::withoutTenantScope()->get();
```

---

## Roles & Permissions

| Role | Scope | Key Permissions |
|------|-------|-----------------|
| **Super Admin** | Platform | Tenant management, platform config |
| **Shop Owner** | Tenant | Full business access |
| **Shop Manager** | Tenant | Daily operations |
| **Cashier** | Tenant | POS, sales, receipts |
| **Technician** | Tenant | Repair tickets, diagnostics |
| **Inventory Manager** | Tenant | Stock, purchasing |
| **Accountant** | Tenant | Financials, reports |

---

## Development

### Commands
```bash
# Testing
php artisan test              # Run tests
php artisan test --parallel   # Parallel execution
php artisan test --coverage   # With coverage

# Code style
php artisan pint              # Fix style
php artisan pint --test       # Check style

# Database
php artisan migrate:fresh --seed  # Reset & seed
php artisan make:migration ...    # New migration

# Filament
php artisan make:filament-resource ...
```

### Conventional Commits
```
feat(tenant): add custom domain support
fix(sales): correct tax calculation on POS
refactor(repairs): simplify status transitions
```

---

## API Endpoints (v1)

```
POST   /api/v1/auth/login
POST   /api/v1/auth/register
POST   /api/v1/auth/logout

GET    /api/v1/customers
POST   /api/v1/customers
GET    /api/v1/customers/{id}
PUT    /api/v1/customers/{id}
DELETE /api/v1/customers/{id}

GET    /api/v1/products
POST   /api/v1/products
...

GET    /api/v1/sales
POST   /api/v1/sales
...

GET    /api/v1/repairs
POST   /api/v1/repairs
...

GET    /api/v1/inventory
...

GET    /api/v1/reports/...
```

Full OpenAPI spec available at `/api/documentation` (when implemented).

---

## Roadmap

- [x] **Phase 0** - Foundation (Auth, Tenancy, Roles, Admin Panel)
- [x] **Phase 1** - MVP Business Operations (Customers, Products, POS, Sales)
- [x] **Phase 2** - Repair Management (Tickets, Workflow, Parts)
- [x] **Phase 3** - Financial & Staff (Payroll, Advanced Reports)
- [x] **Phase 4** - Used Devices (IMEI Tracking, Refurbishment)
- [x] **Phase 5** - SaaS Commercialization (Billing, Payments)
- [ ] **Phase 6** - Customer Storefront (Public Shop)
- [ ] **Phase 7** - Expansion (Flutter App, AI, Multi-branch)

---

## License

Copyright © 2024 MSTechSoft. All rights reserved.

MobiCraft is proprietary software developed by MSTechSoft.

---

## Support

- **Email**: support@mstechsoft.com
- **Documentation**: See `docs/` directory
- **Issues**: GitHub Issues

---

**Built with ❤️ by MSTechSoft for mobile businesses in Bangladesh and beyond.**