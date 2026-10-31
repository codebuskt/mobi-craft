<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'parent_id' => null,
            'name' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->paragraph(),
            'icon' => fake()->randomElement([
                'mobile', 'laptop', 'tablet', 'headphone', 'charger', 'case', 'screen', 'battery', 'cable', 'speaker'
            ]),
            'image' => null,
            'sort_order' => fake()->numberBetween(1, 100),
            'is_active' => true,
            'is_service' => false,
            'is_storefront_visible' => true,
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_service' => true,
        ]);
    }

    public function storefrontVisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_storefront_visible' => true,
        ]);
    }

    public function child(Category $parent = null): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent?->id ?? Category::factory(),
        ]);
    }

    public function root(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }
}