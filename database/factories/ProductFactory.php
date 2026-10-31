<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $costPrice = fake()->randomFloat(2, 500, 50000);
        $salePrice = $costPrice * fake()->randomFloat(2, 1.2, 2.5);
        $wholesalePrice = $costPrice * fake()->randomFloat(2, 1.1, 1.4);
        $specialPrice = fake()->boolean(20) ? $salePrice * fake()->randomFloat(2, 0.7, 0.9) : null;

        return [
            'tenant_id' => Tenant::factory(),
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'sku' => fake()->unique()->regexify('[A-Z]{3}[0-9]{6}'),
            'barcode' => fake()->unique()->ean13(),
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'type' => fake()->randomElement(['product', 'service', 'combo']),
            'status' => fake()->randomElement(['active', 'inactive', 'discontinued']),
            'cost_price' => $costPrice,
            'sale_price' => $salePrice,
            'wholesale_price' => $wholesalePrice,
            'special_price' => $specialPrice,
            'special_price_start' => $specialPrice ? now()->subDays(fake()->numberBetween(1, 30)) : null,
            'special_price_end' => $specialPrice ? now()->addDays(fake()->numberBetween(7, 60)) : null,
            'tax_rate' => fake()->randomElement([0, 5, 15, 20]),
            'tax_inclusive' => false,
            'stock_quantity' => fake()->randomFloat(2, 0, 100),
            'reserved_quantity' => fake()->randomFloat(2, 0, 10),
            'available_quantity' => 0, // Will be calculated
            'reorder_level' => fake()->randomFloat(2, 5, 20),
            'reorder_quantity' => fake()->randomFloat(2, 10, 50),
            'max_stock_level' => fake()->randomFloat(2, 100, 500),
            'unit' => fake()->randomElement(['pcs', 'box', 'set', 'pack']),
            'weight' => fake()->randomFloat(3, 0.05, 2),
            'dimensions' => json_encode([
                'length' => fake()->randomFloat(2, 5, 50),
                'width' => fake()->randomFloat(2, 3, 30),
                'height' => fake()->randomFloat(2, 1, 20),
            ]),
            'is_serialized' => fake()->boolean(30),
            'track_imei' => fake()->boolean(10),
            'warranty_months' => fake()->randomElement([0, 6, 12, 24]),
            'warranty_type' => fake()->randomElement(['manufacturer', 'shop', 'extended']),
            'images' => null,
            'thumbnail' => null,
            'short_description' => fake()->sentence(10),
            'description' => fake()->paragraphs(3, true),
            'specifications' => json_encode([
                'color' => fake()->safeColorName(),
                'material' => fake()->randomElement(['Plastic', 'Metal', 'Glass', 'Leather']),
                'features' => fake()->words(fake()->numberBetween(3, 8)),
            ]),
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
            'is_pos_visible' => true,
            'is_published' => true,
            'is_featured' => fake()->boolean(20),
            'sort_order' => fake()->numberBetween(1, 100),
            'preferred_supplier_id' => null,
            'lead_time_days' => fake()->numberBetween(1, 30),
            'attributes' => json_encode([
                'color' => fake()->safeColorName(),
                'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'Universal']),
            ]),
            'settings' => json_encode([
                'allow_backorder' => fake()->boolean(),
                'show_stock' => fake()->boolean(80),
            ]),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Product $product) {
            $product->available_quantity = max(0, $product->stock_quantity - $product->reserved_quantity);
        });
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'special_price' => $attributes['sale_price'] * fake()->randomFloat(2, 0.7, 0.9),
            'special_price_start' => now()->subDays(fake()->numberBetween(1, 30)),
            'special_price_end' => now()->addDays(fake()->numberBetween(7, 60)),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
            'reserved_quantity' => 0,
            'available_quantity' => 0,
        ]);
    }

    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => fake()->randomFloat(2, 1, 5),
            'reorder_level' => 5,
        ]);
    }

    public function serialized(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_serialized' => true,
            'track_imei' => fake()->boolean(50),
        ]);
    }

    public function forStorefront(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'is_pos_visible' => true,
            'status' => 'active',
        ]);
    }
}