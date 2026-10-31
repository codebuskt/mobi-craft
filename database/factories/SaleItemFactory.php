<?php

namespace Database\Factories;

use App\Models\SaleItem;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 10);
        $unitPrice = fake()->randomFloat(2, 100, 50000);
        $discountPercentage = fake()->randomFloat(2, 0, 20);
        $discountAmount = $quantity * $unitPrice * ($discountPercentage / 100);
        $taxRate = fake()->randomElement([0, 5, 15, 20]);
        $taxable = ($quantity * $unitPrice) - $discountAmount;
        $taxAmount = $taxable * ($taxRate / 100);
        $lineTotal = $taxable + $taxAmount;

        return [
            'tenant_id' => Tenant::factory(),
            'sale_id' => Sale::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount > 0 ? $discountAmount : 0,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'line_total' => $lineTotal,
            'notes' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function noDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => 0,
            'discount_amount' => 0,
        ]);
    }

    public function withDiscount(): static
    {
        $discountPercentage = fake()->randomFloat(2, 5, 30);
        $quantity = $attributes['quantity'] ?? fake()->randomFloat(2, 1, 10);
        $unitPrice = $attributes['unit_price'] ?? fake()->randomFloat(2, 100, 50000);
        $discountAmount = $quantity * $unitPrice * ($discountPercentage / 100);

        return $this->state(fn (array $attributes) => [
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
        ]);
    }

    public function taxExempt(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_rate' => 0,
            'tax_amount' => 0,
        ]);
    }

    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->service(),
            'quantity' => 1,
            'unit_price' => fake()->randomFloat(2, 500, 10000),
        ]);
    }

    public function highQuantity(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->randomFloat(2, 10, 100),
        ]);
    }
}