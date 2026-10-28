# MobiCraft - Database Schema

## Overview

This document describes the database schema for MobiCraft, including table structures, relationships, and key design decisions.

## Core Tables

### tenants
```sql
- id (BIGINT, PK)
- name (VARCHAR 255)
- slug (VARCHAR 255, UNIQUE)      -- Subdomain identifier
- domain (VARCHAR 255, UNIQUE, NULL)  -- Custom domain
- logo (VARCHAR 255, NULL)
- settings (JSON, NULL)           -- Business profile: address, phone, email, currency, timezone
- status (ENUM: active, suspended, trial, expired)
- trial_ends_at (TIMESTAMP, NULL)
- subscription_ends_at (TIMESTAMP, NULL)
- plan_id (BIGINT, FK → subscription_plans.id, NULL)
- created_at, updated_at
- deleted_at (SOFT DELETES)
```

### subscription_plans
```sql
- id (BIGINT, PK)
- name (VARCHAR 255)
- slug (VARCHAR 255, UNIQUE)
- description (TEXT, NULL)
- monthly_price (DECIMAL 10,2)
- yearly_price (DECIMAL 10,2)
- trial_days (INT)
- features (JSON, NULL)   -- ['customers', 'products', 'repairs', ...]
- limits (JSON, NULL)     -- {customers: 100, products: 50, storage: '5GB', repairs_per_month: 10}
- is_active (BOOLEAN)
- sort_order (INT)
- created_at, updated_at
```

### users
```sql
- id (BIGINT, PK)
- tenant_id (BIGINT, FK → tenants.id, NULL)  -- NULL = super admin
- role_id (BIGINT, FK → roles.id, NULL)
- name (VARCHAR 255)
- email (VARCHAR 255, UNIQUE)
- email_verified_at (TIMESTAMP, NULL)
- password (VARCHAR 255)
- phone (VARCHAR 20, NULL)
- address (TEXT, NULL)
- avatar (VARCHAR 255, NULL)
- status (BOOLEAN, DEFAULT true)
- remember_token (VARCHAR 100, NULL)
- created_at, updated_at
```

### roles
```sql
- id (BIGINT, PK)
- name (VARCHAR 255)
- slug (VARCHAR 255, UNIQUE)
- description (TEXT, NULL)
- is_system (BOOLEAN)          -- System roles cannot be deleted
- is_tenant_scoped (BOOLEAN)   -- true = tenant role, false = super admin role
- created_at, updated_at
```

### permissions
```sql
- id (BIGINT, PK)
- name (VARCHAR 255)           -- Display name
- slug (VARCHAR 255, UNIQUE)   -- e.g., customers.view, sales.create
- group (VARCHAR 255, NULL)    -- Module grouping: customers, sales, inventory
- description (TEXT, NULL)
- created_at, updated_at
```

### role_permission
```sql
- role_id (BIGINT, FK → roles.id, PK)
- permission_id (BIGINT, FK → permissions.id, PK)
-- NO timestamps (withTimestamps(false) on relationships)
```

## Business Module Tables (Tenant-Scoped)

All business tables include:
- `tenant_id` (BIGINT, FK → tenants.id) - NOT NULL
- Composite unique indexes where appropriate

### customers
```sql
- id, tenant_id
- code (VARCHAR 50)           -- Customer code (unique per tenant)
- name (VARCHAR 255)
- phone (VARCHAR 20)
- email (VARCHAR 255, NULL)
- address (TEXT, NULL)
- notes (TEXT, NULL)
- status (ENUM: active, inactive)
- created_at, updated_at, deleted_at
- UNIQUE(tenant_id, code)
- UNIQUE(tenant_id, phone)
```

### products
```sql
- id, tenant_id
- category_id (FK → product_categories.id)
- brand_id (FK → brands.id, NULL)
- sku (VARCHAR 100)           -- Unique per tenant
- barcode (VARCHAR 100, NULL) -- Unique per tenant
- name (VARCHAR 255)
- description (TEXT, NULL)
- type (ENUM: phone, accessory, spare_part, service)
- cost_price (DECIMAL 12,2)
- selling_price (DECIMAL 12,2)
- tax_rate (DECIMAL 5,2, DEFAULT 0)
- reorder_level (INT, DEFAULT 0)
- image (VARCHAR 255, NULL)
- status (ENUM: active, inactive, discontinued)
- created_at, updated_at, deleted_at
- UNIQUE(tenant_id, sku)
- UNIQUE(tenant_id, barcode) WHERE barcode IS NOT NULL
```

### inventory_movements (Stock Ledger)
```sql
- id, tenant_id
- product_id (FK → products.id)
- type (ENUM: purchase, sale, return, adjustment, repair_usage, transfer, damage)
- quantity (INT)              -- Positive = in, Negative = out
- unit_cost (DECIMAL 12,2)    -- Cost at time of movement
- reference_type (VARCHAR)    -- e.g., App\Models\Sale
- reference_id (BIGINT)       -- Polymorphic reference
- reason (VARCHAR 255, NULL)
- performed_by (FK → users.id)
- created_at
- INDEX(tenant_id, product_id, created_at)
```

### sales
```sql
- id, tenant_id
- invoice_number (VARCHAR 50) -- Unique per tenant
- customer_id (FK → customers.id, NULL) -- Walk-in if NULL
- user_id (FK → users.id)     -- Cashier
- subtotal (DECIMAL 12,2)
- discount_amount (DECIMAL 12,2, DEFAULT 0)
- tax_amount (DECIMAL 12,2, DEFAULT 0)
- total_amount (DECIMAL 12,2)
- paid_amount (DECIMAL 12,2)
- due_amount (DECIMAL 12,2, DEFAULT 0)
- payment_status (ENUM: paid, partial, due)
- payment_method (ENUM: cash, card, mobile_banking, mixed)
- notes (TEXT, NULL)
- created_at, updated_at, deleted_at
- UNIQUE(tenant_id, invoice_number)
```

### sale_items
```sql
- id, tenant_id
- sale_id (FK → sales.id)
- product_id (FK → products.id)
- quantity (INT)
- unit_price (DECIMAL 12,2)   -- Price at time of sale
- unit_cost (DECIMAL 12,2)    -- Cost at time of sale
- discount (DECIMAL 12,2, DEFAULT 0)
- tax (DECIMAL 12,2, DEFAULT 0)
- total (DECIMAL 12,2)
```

### repairs
```sql
- id, tenant_id
- ticket_number (VARCHAR 50)  -- Unique per tenant
- customer_id (FK → customers.id)
- device_brand (VARCHAR 100)
- device_model (VARCHAR 100)
- imei (VARCHAR 50, NULL)
- serial_number (VARCHAR 100, NULL)
- condition_at_intake (TEXT)
- reported_issue (TEXT)
- accessories_received (TEXT, NULL)
- intake_notes (TEXT, NULL)
- intake_photos (JSON, NULL)
- assigned_technician_id (FK → users.id, NULL)
- diagnosis (TEXT, NULL)
- estimated_cost (DECIMAL 12,2, NULL)
- customer_approved (BOOLEAN, NULL)
- approved_at (TIMESTAMP, NULL)
- status (ENUM: received, diagnosing, waiting_approval, waiting_parts, in_repair, quality_check, ready_delivery, delivered, cancelled, rejected)
- labor_charge (DECIMAL 12,2, DEFAULT 0)
- parts_cost (DECIMAL 12,2, DEFAULT 0)
- total_cost (DECIMAL 12,2, DEFAULT 0)
- paid_amount (DECIMAL 12,2, DEFAULT 0)
- warranty_days (INT, DEFAULT 0)
- warranty_until (DATE, NULL)
- delivered_at (TIMESTAMP, NULL)
- created_at, updated_at, deleted_at
- UNIQUE(tenant_id, ticket_number)
- UNIQUE(tenant_id, imei) WHERE imei IS NOT NULL
```

### repair_status_logs
```sql
- id, tenant_id
- repair_id (FK → repairs.id)
- from_status (VARCHAR 50)
- to_status (VARCHAR 50)
- notes (TEXT, NULL)
- changed_by (FK → users.id)
- created_at
- INDEX(tenant_id, repair_id, created_at)
```

### repair_parts
```sql
- id, tenant_id
- repair_id (FK → repairs.id)
- product_id (FK → products.id)
- quantity (INT)
- unit_cost (DECIMAL 12,2)
- total_cost (DECIMAL 12,2)
- status (ENUM: required, ordered, received, used, returned)
- created_at, updated_at
```

### suppliers
```sql
- id, tenant_id
- name (VARCHAR 255)
- contact_person (VARCHAR 255, NULL)
- phone (VARCHAR 20, NULL)
- email (VARCHAR 255, NULL)
- address (TEXT, NULL)
- notes (TEXT, NULL)
- status (ENUM: active, inactive)
- created_at, updated_at, deleted_at
```

### purchases
```sql
- id, tenant_id
- supplier_id (FK → suppliers.id)
- reference_number (VARCHAR 100)
- total_cost (DECIMAL 12,2)
- paid_amount (DECIMAL 12,2, DEFAULT 0)
- due_amount (DECIMAL 12,2, DEFAULT 0)
- payment_status (ENUM: paid, partial, due)
- received_at (TIMESTAMP, NULL)
- notes (TEXT, NULL)
- created_at, updated_at, deleted_at
```

### purchase_items
```sql
- id, tenant_id
- purchase_id (FK → purchases.id)
- product_id (FK → products.id)
- quantity_ordered (INT)
- quantity_received (INT, DEFAULT 0)
- unit_cost (DECIMAL 12,2)
- total_cost (DECIMAL 12,2)
```

### expenses
```sql
- id, tenant_id
- category_id (FK → expense_categories.id)
- amount (DECIMAL 12,2)
- expense_date (DATE)
- payment_method (ENUM: cash, card, mobile_banking, bank_transfer)
- reference (VARCHAR 100, NULL)
- description (TEXT, NULL)
- attachment (VARCHAR 255, NULL)
- created_by (FK → users.id)
- created_at, updated_at, deleted_at
```

### expense_categories
```sql
- id, tenant_id
- name (VARCHAR 100)
- description (TEXT, NULL)
- is_active (BOOLEAN, DEFAULT true)
- created_at, updated_at
```

### staff
```sql
- id, tenant_id
- user_id (FK → users.id, UNIQUE)
- employee_id (VARCHAR 50)     -- Unique per tenant
- position (VARCHAR 100)
- department (VARCHAR 100, NULL)
- hire_date (DATE)
- employment_status (ENUM: active, on_leave, terminated)
- salary_type (ENUM: monthly, hourly, daily)
- base_salary (DECIMAL 12,2)
- bank_account (VARCHAR 100, NULL)
- emergency_contact (VARCHAR 255, NULL)
- notes (TEXT, NULL)
- created_at, updated_at, deleted_at
- UNIQUE(tenant_id, employee_id)
```

### payroll_periods
```sql
- id, tenant_id
- name (VARCHAR 100)           -- e.g., "January 2024"
- start_date (DATE)
- end_date (DATE)
- status (ENUM: draft, processing, approved, paid)
- processed_by (FK → users.id, NULL)
- processed_at (TIMESTAMP, NULL)
- created_at, updated_at
```

### payroll_records
```sql
- id, tenant_id
- payroll_period_id (FK → payroll_periods.id)
- staff_id (FK → staff.id)
- base_salary (DECIMAL 12,2)
- overtime_hours (DECIMAL 5,2, DEFAULT 0)
- overtime_amount (DECIMAL 12,2, DEFAULT 0)
- allowances (DECIMAL 12,2, DEFAULT 0)
- deductions (DECIMAL 12,2, DEFAULT 0)
- advance_deduction (DECIMAL 12,2, DEFAULT 0)
- net_salary (DECIMAL 12,2)
- status (ENUM: draft, approved, paid)
- paid_at (TIMESTAMP, NULL)
- payment_method (VARCHAR 50, NULL)
- payment_reference (VARCHAR 100, NULL)
- created_at, updated_at
```

## Index Strategy

### Performance Indexes
- All FK columns indexed
- Composite indexes for common queries:
  - `(tenant_id, created_at)` on transactional tables
  - `(tenant_id, status)` on status-filtered tables
  - `(tenant_id, customer_id)` on customer-related tables
- Unique indexes for business keys (per tenant)

### Full-Text Search (MySQL)
- `customers`: name, phone, email
- `products`: name, sku, barcode
- `repairs`: ticket_number, imei, customer phone

## Data Integrity Rules

1. **Tenant Isolation**: Every tenant-scoped query MUST include `tenant_id`
2. **Soft Deletes**: Business records use soft deletes
3. **Stock Consistency**: Use `inventory_movements` ledger; never direct quantity updates
4. **Financial Precision**: All monetary values use `DECIMAL(12,2)`
5. **Referential Integrity**: Foreign keys with `RESTRICT` or `CASCADE` as appropriate
6. **Audit Trail**: Critical changes logged in `audit_logs` table (planned)

## Migration Naming Convention

```
YYYY_MM_DD_HHMMSS_create_{table_name}_table
YYYY_MM_DD_HHMMSS_add_{column}_to_{table}_table
YYYY_MM_DD_HHMMSS_update_{table}_table
```

## Seeding

```bash
# Development
php artisan db:seed

# Specific seeder
php artisan db:seed --class=DatabaseSeeder
```