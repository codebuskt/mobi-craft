<?php

namespace Database\Factories;

use App\Models\CustomerPayment;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerPaymentFactory extends Factory
{
    protected $model = CustomerPayment::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 50000);

        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => Customer::factory(),
            'sale_id' => fake()->optional(0.5)->fn(fn() => Sale::factory()),
            'amount' => $amount,
            'payment_method' => fake()->randomElement(['cash', 'card', 'mobile_banking', 'bank_transfer', 'cheque']),
            'payment_reference' => fake()->optional()->regexify('TXN[0-9]{10}'),
            'notes' => fake()->optional()->sentence(),
            'received_by' => User::factory(),
            'payment_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'allocated_amount' => 0,
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

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
            'tenant_id' => $customer->tenant_id,
        ]);
    }
}