<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'phone' => fake()->unique()->phoneNumber(),
            'address' => fake()->optional()->streetAddress(),
            'avatar' => null,
            'status' => true,
            'tenant_id' => null, // Will be set for tenant users
            'role_id' => null, // Will be set based on role
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => null,
            'role_id' => Role::where('slug', 'super_admin')->first()?->id ?? Role::factory()->superAdmin(),
        ]);
    }

    public function tenantAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
            'role_id' => Role::where('slug', 'tenant_admin')->first()?->id ?? Role::factory()->tenantAdmin(),
        ]);
    }

    public function manager(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
            'role_id' => Role::where('slug', 'manager')->first()?->id ?? Role::factory()->manager(),
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
            'role_id' => Role::where('slug', 'staff')->first()?->id ?? Role::factory()->staff(),
        ]);
    }

    public function technician(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
            'role_id' => Role::where('slug', 'technician')->first()?->id ?? Role::factory()->technician(),
        ]);
    }

    public function cashier(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
            'role_id' => Role::where('slug', 'cashier')->first()?->id ?? Role::factory()->cashier(),
        ]);
    }

    public function salesPerson(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => Tenant::factory(),
            'role_id' => Role::where('slug', 'sales_person')->first()?->id ?? Role::factory()->salesPerson(),
        ]);
    }

    public function withTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
        ]);
    }

    public function withRole(Role $role): static
    {
        return $this->state(fn (array $attributes) => [
            'role_id' => $role->id,
        ]);
    }
}