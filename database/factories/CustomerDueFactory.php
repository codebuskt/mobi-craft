<?php

namespace Database\Factories;

use App\Models\CustomerDue;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerDueFactory extends Factory
{
    protected $model = CustomerDue::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 500, 100000);
        $paidAmount = fake()->randomFloat(2, 0, $amount * 0.8);
        $dueDate = fake()->dateTimeBetween('now', '+90 days');

        $status = 'pending';
        if ($paidAmount >= $amount) {
            $status = 'paid';
        } elseif ($paidAmount > 0) {
            $status = 'partial';
        } elseif ($dueDate < now()) {
            $status = 'overdue';
        }

        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => Customer::factory(),
            'sale_id' => fake()->optional(0.7)->fn(fn() => Sale::factory()),
            'amount' => $amount,
            'due_date' => $dueDate,
            'status' => $status,
            'notes' => fake()->optional()->sentence(),
            'paid_amount' => $paidAmount,
            'balance_amount' => $amount - $paidAmount,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'paid_amount' => 0,
            'balance_amount' => $attributes['amount'],
            'due_date' => fake()->dateTimeBetween('now', '+30 days'),
        ]);
    }

    public function partial(): static
    {
        $paid = $attributes['amount'] * fake()->randomFloat(2, 0.1, 0.9);
        return $this->state(fn (array $attributes) => [
            'status' => 'partial',
            'paid_amount' => $paid,
            'balance_amount' => $attributes['amount'] - $paid,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_amount' => $attributes['amount'],
            'balance_amount' => 0,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => fake()->dateTimeBetween('-60 days', '-1 days'),
            'paid_amount' => 0,
            'balance_amount' => $attributes['amount'],
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => fake()->randomFloat(2, 100000, 1000000),
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