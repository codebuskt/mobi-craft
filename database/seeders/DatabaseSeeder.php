<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $this->seedSubscriptionPlans();
        $this->seedSystemSuperAdmin();
        $this->seedDefaultShopProfile();
    }

    /**
     * Seed roles and permissions.
     */
    protected function seedRolesAndPermissions(): void
    {
        // Define roles with their permissions
        $roles = [
            'super_admin' => [
                'description' => 'SaaS Super Admin - Full platform access',
                'permissions' => [
                    'tenants.manage',
                    'users.manage',
                    'roles.manage',
                    'subscription-plans.manage',
                    'customers.manage',
                    'products.manage',
                    'inventory.manage',
                    'purchases.manage',
                    'sales.manage',
                    'repairs.manage',
                    'suppliers.manage',
                    'expenses.manage',
                    'payroll.manage',
                    'reports.view',
                    'reports.export',
                    'settings.manage',
                    'notifications.manage',
                ],
            ],
            'shop_owner' => [
                'description' => 'Shop Owner - Full business access',
                'permissions' => [
                    'customers.manage',
                    'products.manage',
                    'inventory.manage',
                    'sales.manage',
                    'repairs.manage',
                    'suppliers.manage',
                    'expenses.manage',
                    'payments.view',
                    'reports.view',
                    'reports.export',
                    'settings.view',
                ],
            ],
            'shop_manager' => [
                'description' => 'Shop Manager - Manage daily operations',
                'permissions' => [
                    'customers.view',
                    'products.view',
                    'inventory.view',
                    'sales.view',
                    'repairs.manage',
                    'suppliers.view',
                    'expenses.view',
                    'reports.view',
                ],
            ],
            'cashier' => [
                'description' => 'Cashier / Sales Staff - POS and sales',
                'permissions' => [
                    'sales.create',
                    'products.search',
                    'inventory.view',
                    'customers.search',
                    'payments.receive',
                    'receipts.print',
                    'sales.view',
                ],
            ],
            'technician' => [
                'description' => 'Technician - Repair management',
                'permissions' => [
                    'repairs.create',
                    'repairs.manage',
                    'repairs.view',
                    'parts.view',
                    'products.search',
                    'reports.view',
                    'diagnostics.create',
                ],
            ],
            'inventory_manager' => [
                'description' => 'Inventory Manager - Stock management',
                'permissions' => [
                    'inventory.manage',
                    'products.manage',
                    'suppliers.manage',
                    'purchases.create',
                    'purchases.view',
                    'stock.adjust',
                    'stock.view',
                    'reports.view',
                ],
            ],
            'accountant' => [
                'description' => 'Accountant - Financial records',
                'permissions' => [
                    'expenses.view',
                    'expenses.manage',
                    'payments.view',
                    'reports.view',
                    'reports.financial',
                    'sales.view',
                    'suppliers.view',
                    'dues.view',
                    'dues.manage',
                ],
            ],
        ];

        foreach ($roles as $slug => $data) {
            $role = Role::firstOrCreate([
                'slug' => $slug,
            ], [
                'name' => $data['description'],
                'description' => $data['description'],
                'is_system' => true,
                'is_tenant_scoped' => ($slug !== 'super_admin'),
            ]);

            foreach ($data['permissions'] as $permissionSlug) {
                $permission = Permission::firstOrCreate([
                    'slug' => $permissionSlug,
                ], [
                    'name' => ucwords(str_replace(['-', '_'], ' ', $permissionSlug)),
                    'group' => $this->getPermissionGroup($permissionSlug),
                ]);

                // Directly insert into pivot table to avoid Eloquent timestamp issues
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                ]);
            }
        }
    }

    /**
     * Get permission group from slug.
     */
    protected function getPermissionGroup(string $slug): string
    {
        $parts = explode('.', $slug);
        $groups = [
            'tenants' => 'Tenancy',
            'users' => 'Users',
            'roles' => 'Roles',
            'subscription-plans' => 'Subscriptions',
            'customers' => 'Customers',
            'products' => 'Products',
            'inventory' => 'Inventory',
            'purchases' => 'Purchasing',
            'sales' => 'Sales',
            'repairs' => 'Repairs',
            'suppliers' => 'Suppliers',
            'expenses' => 'Expenses',
            'payments' => 'Payments',
            'reports' => 'Reports',
            'settings' => 'Settings',
            'notifications' => 'Notifications',
            'dashboards' => 'Dashboards',
        ];

        return $groups[$parts[0]] ?? 'General';
    }

    /**
     * Seed subscription plans.
     */
    protected function seedSubscriptionPlans(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Entry level plan for new mobile repair businesses',
                'monthly_price' => 29.99,
                'yearly_price' => 299.99,
                'trial_days' => 14,
                'features' => ['customers', 'products', 'inventory', 'pos', 'basic-repairs'],
                'limits' => [
                    'customers' => 100,
                    'products' => 50,
                    'storage' => '5GB',
                    'repairs_per_month' => 10,
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'Growing business with advanced features',
                'monthly_price' => 79.99,
                'yearly_price' => 799.99,
                'trial_days' => 30,
                'features' => ['customers', 'products', 'inventory', 'pos', 'repairs', 'suppliers', 'dues', 'reports'],
                'limits' => [
                    'customers' => 1000,
                    'products' => 500,
                    'storage' => '20GB',
                    'repairs_per_month' => 100,
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Large operation with all features and support',
                'monthly_price' => 199.99,
                'yearly_price' => 1999.99,
                'trial_days' => 30,
                'features' => ['customers', 'products', 'inventory', 'pos', 'repairs', 'suppliers', 'dues', 'payroll', 'reports', 'api'],
                'limits' => [
                    'customers' => 10000,
                    'products' => 5000,
                    'storage' => '100GB',
                    'repairs_per_month' => -1, // Unlimited
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::firstOrCreate([
                'slug' => $planData['slug'],
            ], $planData);
        }
    }

    /**
     * Seed system super admin user.
     */
    protected function seedSystemSuperAdmin(): void
    {
        // Create a tenant for the super admin context
        $tenant = Tenant::firstOrCreate([
            'name' => 'MSTechSoft Platform',
            'slug' => 'super-admin',
            'domain' => 'admin.mobicraft.test',
            'status' => 'active',
        ]);

        // Create super admin user
        $superAdmin = User::firstOrCreate([
            'email' => 'super-admin@mobicraft.test',
        ], [
            'name' => 'Super Admin',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'phone' => '+8801700000000',
            'tenant_id' => $tenant->id,
            'status' => true,
        ]);

        // Assign super admin role with all permissions
        $superAdminRole = Role::where('slug', 'super_admin')->firstOrFail();
        $superAdmin->role()->associate($superAdminRole);
        $superAdmin->save();

        // Grant all permissions to the role
        foreach (['tenants.manage', 'users.manage', 'roles.manage', 'subscription-plans.manage',
                    'customers.manage', 'products.manage', 'inventory.manage', 'purchases.manage',
                    'sales.manage', 'repairs.manage', 'suppliers.manage', 'expenses.manage',
                    'payroll.manage', 'reports.view', 'reports.export', 'settings.manage',
                    'notifications.manage'] as $permissionSlug) {
            $permission = Permission::firstOrCreate([
                'slug' => $permissionSlug,
                'name' => ucwords(str_replace(['-', '_'], ' ', $permissionSlug)),
                'group' => $this->getPermissionGroup($permissionSlug),
            ]);

            DB::table('role_permission')->insertOrIgnore([
                'role_id' => $superAdminRole->id,
                'permission_id' => $permission->id,
            ]);
        }

        // Log the seeded data
        $this->command->info("Seeded super admin: {$superAdmin->email}");
        $this->command->info("Seeded tenant: {$tenant->name} ({$tenant->slug})");
        $this->command->info("Seeded roles: " . Role::count());
        $this->command->info("Seeded permissions: " . Permission::count());
        $this->command->info("Seeded subscription plans: " . SubscriptionPlan::count());
    }

    /**
     * Seed default shop profile for the super admin tenant.
     */
    protected function seedDefaultShopProfile(): void
    {
        $tenant = Tenant::where('slug', 'super-admin')->first();

        if ($tenant && ! $tenant->shopProfile) {
            $tenant->shopProfile()->create([
                'business_name' => 'MSTechSoft Platform',
                'address' => 'Dhaka, Bangladesh',
                'city' => 'Dhaka',
                'state' => 'Dhaka',
                'postal_code' => '1000',
                'country' => 'Bangladesh',
                'phone' => '+8801700000000',
                'email' => 'support@mstechsoft.com',
                'website' => 'https://mstechsoft.com',
                'currency' => 'BDT',
                'currency_symbol' => '৳',
                'timezone' => 'Asia/Dhaka',
                'locale' => 'en',
                'date_format' => 'd/m/Y',
                'time_format' => 'H:i',
                'default_tax_rate' => 15.00,
                'tax_label' => 'VAT',
                'tax_inclusive' => false,
                'payment_terms_days' => 0,
                'rounding_precision' => 0.50,
                'invoice_prefix' => 'INV',
                'repair_ticket_prefix' => 'REP',
                'purchase_order_prefix' => 'PO',
                'quotation_prefix' => 'QUO',
                'enable_barcode_scanner' => true,
                'enable_customer_display' => false,
                'auto_print_receipt' => false,
                'receipt_printer_type' => 'thermal',
                'receipt_width' => 80,
                'enable_imei_tracking' => true,
                'default_warranty_days' => 30,
                'require_customer_approval' => true,
                'enable_quality_check' => true,
                'allow_negative_stock' => false,
                'track_serial_numbers' => true,
                'enable_low_stock_alerts' => true,
                'low_stock_threshold' => 5,
                'email_notifications' => true,
                'sms_notifications' => false,
                'invoice_terms' => 'Payment due within stated terms. Late payments may incur interest.',
                'repair_terms' => 'Repairs are performed based on customer approval. Warranty covers parts and labor for the specified period.',
                'warranty_terms' => 'Warranty does not cover physical damage, liquid damage, or unauthorized repairs.',
                'is_setup_complete' => true,
                'setup_completed_at' => now(),
            ]);

            $this->command->info("Seeded default shop profile for: {$tenant->name}");
        }
    }
}