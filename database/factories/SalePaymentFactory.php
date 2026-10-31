<?php

namespace Database\Factories;

use App\Models\SalePayment;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalePaymentFactory extends Factory
{
    protected $model = SalePayment::class;

    public function definition(): array
    {
        $sale = Sale::factory();
        $amount = fake()->randomFloat(2, 100, 50000);

        return [
            'tenant_id' => Tenant::factory(),
            'sale_id' => $sale,
            'customer_id' => $sale->customer_id ?? Customer::factory(),
            'amount' => $amount,
            'payment_method' => fake()->randomElement(['cash', 'card', 'mobile_banking', 'bank_transfer', 'cheque', 'credit']),
            'payment_reference' => fake()->optional()->regexify('TXN[0-9]{10}'),
            'notes' => fake()->optional()->sentence(),
            'received_by' => User::factory(),
            'payment_date' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
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

    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'bank_transfer',
        ]);
    }

    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => 'credit',
        ]);
    }

    public function forSale(Sale $sale): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_id' => $sale->id,
            'customer_id' => $sale->customer_id,
            'tenant_id' => $sale->tenant_id,
        ]);
    }
}