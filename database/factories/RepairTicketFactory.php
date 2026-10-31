<?php

namespace Database\Factories;

use App\Models\RepairTicket;
use App\Models\Customer;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class RepairTicketFactory extends Factory
{
    protected $model = RepairTicket::class;

    public function definition(): array
    {
        $status = fake()->randomElement([
            'received', 'diagnosing', 'waiting_approval', 'waiting_parts',
            'in_repair', 'quality_check', 'ready_delivery', 'delivered',
            'cancelled', 'rejected'
        ]);

        $receivedDate = fake()->dateTimeBetween('-6 months', 'now');
        $estimatedCompletion = fake()->dateTimeBetween($receivedDate, (clone $receivedDate)->modify('+30 days'));
        $actualCompletion = in_array($status, ['delivered', 'ready_delivery', 'quality_check'])
            ? fake()->dateTimeBetween($receivedDate, $estimatedCompletion)
            : null;
        $deliveredDate = $status === 'delivered'
            ? fake()->dateTimeBetween($actualCompletion ?? $receivedDate, 'now')
            : null;

        $diagnosticFee = fake()->randomFloat(2, 0, 1000);
        $laborCharge = fake()->randomFloat(2, 500, 10000);
        $partsCost = fake()->randomFloat(2, 0, 15000);
        $totalCharged = $diagnosticFee + $laborCharge + $partsCost;
        $paidAmount = fake()->randomFloat(2, 0, $totalCharged);

        return [
            'tenant_id' => Tenant::factory(),
            'customer_id' => Customer::factory(),
            'assigned_technician_id' => fake()->optional(0.7)->fn(fn() => User::factory()),
            'created_by' => User::factory(),
            'ticket_number' => null, // Auto-generated in model boot
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'reported_issue' => fake()->sentence(10),
            'intake_notes' => fake()->optional()->paragraph(),
            'diagnostic_fee' => $diagnosticFee,
            'labor_charge' => $laborCharge,
            'parts_cost' => $partsCost,
            'total_estimated' => $totalCharged + fake()->randomFloat(2, 0, 5000),
            'total_charged' => $totalCharged,
            'paid_amount' => $paidAmount,
            'balance_amount' => $totalCharged - $paidAmount,
            'received_date' => $receivedDate,
            'estimated_completion_date' => $estimatedCompletion,
            'actual_completion_date' => $actualCompletion,
            'delivered_date' => $deliveredDate,
            'warranty_months' => fake()->randomElement([0, 1, 3, 6, 12]),
            'warranty_start_date' => $deliveredDate ? fake()->dateTimeBetween($deliveredDate, 'now') : null,
            'warranty_end_date' => $deliveredDate ? (clone $deliveredDate)->modify('+' . fake()->numberBetween(1, 12) . ' months') : null,
            'warranty_terms' => fake()->optional()->paragraph(),
            'sla_due_at' => fake()->optional()->dateTimeBetween($receivedDate, (clone $receivedDate)->modify('+7 days')),
            'sla_breached' => fake()->boolean(10),
        ];
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'received',
            'estimated_completion_date' => fake()->dateTimeBetween('now', '+14 days'),
        ]);
    }

    public function diagnosing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'diagnosing',
            'estimated_completion_date' => fake()->dateTimeBetween('now', '+14 days'),
        ]);
    }

    public function waitingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waiting_approval',
            'estimated_completion_date' => fake()->dateTimeBetween('now', '+14 days'),
        ]);
    }

    public function inRepair(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_repair',
            'estimated_completion_date' => fake()->dateTimeBetween('now', '+14 days'),
        ]);
    }

    public function readyDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ready_delivery',
            'actual_completion_date' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'actual_completion_date' => fake()->dateTimeBetween('-30 days', '-1 days'),
            'delivered_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'paid_amount' => $attributes['total_charged'],
            'balance_amount' => 0,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    public function withWarranty(): static
    {
        return $this->state(fn (array $attributes) => [
            'warranty_months' => fake()->randomElement([3, 6, 12]),
            'warranty_start_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'warranty_end_date' => fake()->dateTimeBetween('now', '+1 year'),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => fake()->randomElement(['received', 'diagnosing', 'waiting_approval', 'waiting_parts', 'in_repair']),
            'estimated_completion_date' => fake()->dateTimeBetween('-30 days', '-1 days'),
        ]);
    }

    public function underWarranty(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'delivered_date' => fake()->dateTimeBetween('-6 months', '-1 days'),
            'warranty_months' => 12,
            'warranty_start_date' => fake()->dateTimeBetween('-6 months', '-1 days'),
            'warranty_end_date' => fake()->dateTimeBetween('now', '+6 months'),
        ]);
    }

    public function fullyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_charged'],
            'balance_amount' => 0,
        ]);
    }

    public function partialPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_charged'] * fake()->randomFloat(2, 0.1, 0.9),
            'balance_amount' => $attributes['total_charged'] - ($attributes['total_charged'] * fake()->randomFloat(2, 0.1, 0.9)),
        ]);
    }
}