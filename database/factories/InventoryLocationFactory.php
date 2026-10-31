<?php

namespace Database\Factories;

use App\Models\InventoryLocation;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryLocationFactory extends Factory
{
    protected $model = InventoryLocation::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'parent_id' => null,
            'name' => fake()->unique()->words(2, true),
            'code' => fake()->unique()->regexify('LOC[0-9]{4}'),
            'slug' => \Illuminate\Support\Str::slug(fake()->unique()->words(2, true)),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement([
                'warehouse', 'shop_floor', 'repair_bay', 'showroom', 'storage', 'transit'
            ]),
            'manager_id' => fake()->optional(0.3)->fn(fn() => User::factory()),
            'address' => fake()->optional()->streetAddress(),
            'is_active' => true,
            'is_default' => false,
            'sort_order' => fake()->numberBetween(1, 100),
            'settings' => [
                'temperature_controlled' => fake()->boolean(20),
                'security_level' => fake()->randomElement(['basic', 'standard', 'high']),
                'capacity' => fake()->optional()->numberBetween(100, 10000),
            ],
        ];
    }

    public function warehouse(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'warehouse',
            'name' => 'Main Warehouse',
            'code' => 'WH001',
        ]);
    }

    public function shopFloor(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'shop_floor',
            'name' => 'Shop Floor',
            'code' => 'SF001',
        ]);
    }

    public function repairBay(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'repair_bay',
            'name' => 'Repair Bay',
            'code' => 'RB001',
        ]);
    }

    public function showroom(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'showroom',
            'name' => 'Showroom',
            'code' => 'SR001',
        ]);
    }

    public function storage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'storage',
            'name' => 'Storage',
            'code' => 'ST001',
        ]);
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function child(InventoryLocation $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent?->id ?? InventoryLocation::factory(),
        ]);
    }

    public function withManager(): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => User::factory(),
        ]);
    }
}