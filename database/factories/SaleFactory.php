<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use App\Models\InventoryLocation;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $saleDate = fake()->dateTimeBetween('-6 months', 'now');
        $status = fake()->randomElement(['draft', 'pending', 'completed', 'cancelled', 'refunded']);

        $subtotal = fake()->randomFloat(2, 100, 200000);
        $discountPercentage = fake()->randomFloat(2, 0, 15);
        $discountAmount = $subtotal * ($discountPercentage / 100);
        $taxAmount = ($subtotal - $discountAmount) * 0.15; // 15% VAT
        $totalAmount = $subtotal - $discountAmount + $taxAmount;

        $paidAmount = $status === 'completed'
            ? fake()->randomFloat(2, $totalAmount * 0.5, $totalAmount)
            : ($status === 'refunded' ? $totalAmount : 0);
        $paymentStatus = $paidAmount >= $totalAmount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');

        if ($status === 'refunded') {
            $paymentStatus = 'refunded';
        }

        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => fake()->optional(0.7)->fn(fn() => Customer::factory()),
            'location_id' => InventoryLocation::factory(),
            'created_by' => User::factory(),
            'sale_number' => null, // Auto-generated in model boot
            'sale_date' => $saleDate,
            'status' => $status,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'discount_percentage' => $discountPercentage,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $totalAmount - $paidAmount,
            'payment_status' => $paymentStatus,
            'payment_method' => fake()->randomElement(['cash', 'card', 'mobile_banking', 'bank_transfer', 'mixed']),
            'payment_reference' => fake()->optional()->regexify('TXN[0-9]{10}'),
            'notes' => fake()->optional()->paragraph(),
            'terms_conditions' => fake()->optional()->sentence(),
            'completed_at' => in_array($status, ['completed', 'refunded'])
                ? fake()->dateTimeBetween($saleDate, 'now') : null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'paid_amount' => 0,
            'balance_amount' => $attributes['total_amount'],
            'payment_status' => 'unpaid',
            'completed_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_amount' => $attributes['total_amount'],
            'payment_status' => 'unpaid',
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_amount' => $attributes['total_amount'],
            'balance_amount' => 0,
            'payment_status' => 'paid',
            'completed_at' => fake()->dateTimeBetween($attributes['sale_date'] ?? now(), 'now'),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'paid_amount' => 0,
            'balance_amount' => 0,
            'payment_status' => 'unpaid',
            'completed_at' => null,
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'refunded',
            'paid_amount' => $attributes['total_amount'],
            'balance_amount' => 0,
            'payment_status' => 'refunded',
            'completed_at' => fake()->dateTimeBetween($attributes['sale_date'] ?? now(), 'now'),
        ]);
    }

    public function partiallyPaid(): static
    {
        $paid = $attributes['total_amount'] * fake()->randomFloat(2, 0.1, 0.9);
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_amount' => $paid,
            'balance_amount' => $attributes['total_amount'] - $paid,
            'payment_status' => 'partial',
            'completed_at' => fake()->dateTimeBetween($attributes['sale_date'] ?? now(), 'now'),
        ]);
    }

    public function walkIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => null,
        ]);
    }

    public function withCustomer(): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => Customer::factory(),
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_date' => now()->toDateString(),
        ]);
    }

    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_date' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_date' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => fake()->randomFloat(2, 50000, 500000),
            'total_amount' => fake()->randomFloat(2, 50000, 500000),
        ]);
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'cash',
        ]);
    }

    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'card',
        ]);
    }

    public function mobileBanking(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'mobile_banking',
        ]);
    }
}