<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => \Illuminate\Support\Str::slug(fake()->unique()->words(2, true)),
            'description' => fake()->sentence(),
            'is_system' => false,
            'is_tenant_scoped' => true,
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    public function tenantScoped(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_tenant_scoped' => true,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'description' => 'Super administrator with full system access',
            'is_system' => true,
            'is_tenant_scoped' => false,
        ]);
    }

    public function tenantAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Tenant Admin',
            'slug' => 'tenant_admin',
            'description' => 'Tenant administrator with full tenant access',
            'is_system' => false,
            'is_tenant_scoped' => true,
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Manager with management permissions',
            'is_system' => false,
            'is_tenant_scoped' => true,
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Staff',
            'slug' => 'staff',
            'description' => 'General staff member',
            'is_system' => false,
            'is_tenant_scoped' => true,
        ]);
    }

    public function technician(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Technician',
            'slug' => 'technician',
            'description' => 'Repair technician',
            'is_system' => false,
            'is_tenant_scoped' => true,
        ]);
    }

    public function cashier(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Cashier',
            'slug' => 'cashier',
            'description' => 'POS cashier',
            'is_system' => false,
            'is_tenant_scoped' => true,
        ]);
    }

    public function salesPerson(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Sales Person',
            'slug' => 'sales_person',
            'description' => 'Sales representative',
            'is_system' => false,
            'is_tenant_scoped' => true,
        ]);
    }

    public function accountant(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Accountant',
            'slug' => 'accountant',
            'description' => 'Finance and accounting',
            'is_system' => false,
            'is_tenant_scoped' => true,
        ]);
    }

    public function inventoryManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Inventory Manager',
            'slug' => 'inventory_manager',
            'description' => 'Inventory management',
            'is_system' => false,
            'is_tenant_scoped' => true,
        ]);
    }

    public function withPermissions(array $permissions): static
    {
        return $this->afterCreating(function (Role $role) use ($permissions) {
            $permissionModels = Permission::whereIn('slug', $permissions)->get();
            $role->permissions()->sync($permissionModels->pluck('id'));
        });
    }
}