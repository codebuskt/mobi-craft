<?php

namespace Database\Factories;

use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderItemFactory extends Factory
{
    protected $model = PurchaseOrderItem::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 100);
        $unitPrice = fake()->randomFloat(2, 50, 30000);
        $discountPercentage = fake()->randomFloat(2, 0, 15);
        $discountAmount = $quantity * $unitPrice * ($discountPercentage / 100);
        $taxRate = fake()->randomElement([0, 5, 15, 20]);
        $taxable = ($quantity * $unitPrice) - $discountAmount;
        $taxAmount = $taxable * ($taxRate / 100);
        $lineTotal = $taxable + $taxAmount;

        return [
            'tenant_id' => Tenant::factory(),
            'purchase_order_id' => PurchaseOrder::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount > 0 ? $discountAmount : 0,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'line_total' => $lineTotal,
            'received_quantity' => 0,
            'pending_quantity' => $quantity,
            'notes' => fake()->optional()->sentence(),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'received_quantity' => $attributes['quantity'],
            'pending_quantity' => 0,
        ]);
    }

    public function partiallyReceived(): static
    {
        $received = $attributes['quantity'] * fake()->randomFloat(2, 0.1, 0.9);
        return $this->state(fn (array $attributes) => [
            'received_quantity' => $received,
            'pending_quantity' => $attributes['quantity'] - $received,
        ]);
    }

    public function notReceived(): static
    {
        return $this->state(fn (array $attributes) => [
            'received_quantity' => 0,
            'pending_quantity' => $attributes['quantity'],
        ]);
    }

    public function noDiscount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_percentage' => 0,
            'discount_amount' => 0,
        ]);
    }

    public function taxExempt(): static
    {
        return $this->state(fn (array $attributes) => [
            'tax_rate' => 0,
            'tax_amount' => 0,
        ]);
    }
}