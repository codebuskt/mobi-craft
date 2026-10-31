<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\InventoryLocation;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $orderDate = fake()->dateTimeBetween('-6 months', 'now');
        $expectedDelivery = fake()->dateTimeBetween($orderDate, (clone $orderDate)->modify('+30 days'));
        $status = fake()->randomElement([
            'draft', 'pending_approval', 'approved', 'ordered',
            'partial_received', 'received', 'cancelled', 'rejected'
        ]);

        $subtotal = fake()->randomFloat(2, 1000, 500000);
        $discountPercentage = fake()->randomFloat(2, 0, 10);
        $discountAmount = $subtotal * ($discountPercentage / 100);
        $taxAmount = ($subtotal - $discountAmount) * 0.15; // 15% VAT
        $shippingCost = fake()->randomFloat(2, 0, 5000);
        $otherCharges = fake()->randomFloat(2, 0, 2000);
        $totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingCost + $otherCharges;

        $paidAmount = in_array($status, ['received', 'partial_received'])
            ? fake()->randomFloat(2, 0, $totalAmount)
            : 0;
        $paymentStatus = $paidAmount >= $totalAmount ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid');

        return [
            'tenant_id' => Tenant::factory(),
            'supplier_id' => Supplier::factory(),
            'location_id' => InventoryLocation::factory(),
            'created_by' => User::factory(),
            'approved_by' => in_array($status, ['approved', 'ordered', 'partial_received', 'received'])
                ? User::factory() : null,
            'po_number' => null, // Auto-generated in model boot
            'order_date' => $orderDate,
            'expected_delivery_date' => $expectedDelivery,
            'status' => $status,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'discount_percentage' => $discountPercentage,
            'tax_amount' => $taxAmount,
            'shipping_cost' => $shippingCost,
            'other_charges' => $otherCharges,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'balance_amount' => $totalAmount - $paidAmount,
            'payment_status' => $paymentStatus,
            'payment_method' => fake()->randomElement(['bank_transfer', 'cash', 'cheque', 'card', 'mobile_banking']),
            'payment_reference' => fake()->optional()->regexify('TXN[0-9]{10}'),
            'notes' => fake()->optional()->paragraph(),
            'supplier_notes' => fake()->optional()->sentence(),
            'terms_conditions' => fake()->optional()->paragraph(),
            'attachments' => null,
            'approved_at' => in_array($status, ['approved', 'ordered', 'partial_received', 'received'])
                ? fake()->dateTimeBetween($orderDate, $expectedDelivery) : null,
            'ordered_at' => in_array($status, ['ordered', 'partial_received', 'received'])
                ? fake()->dateTimeBetween($orderDate, $expectedDelivery) : null,
            'received_at' => $status === 'received' ? fake()->dateTimeBetween($expectedDelivery, 'now') : null,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
            'ordered_at' => null,
            'received_at' => null,
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_approval',
            'approved_by' => null,
            'approved_at' => null,
            'ordered_at' => null,
            'received_at' => null,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => fake()->dateTimeBetween($attributes['order_date'] ?? now(), now()),
            'ordered_at' => null,
            'received_at' => null,
        ]);
    }

    public function ordered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ordered',
            'approved_by' => User::factory(),
            'approved_at' => fake()->dateTimeBetween($attributes['order_date'] ?? now(), now()),
            'ordered_at' => fake()->dateTimeBetween($attributes['order_date'] ?? now(), now()),
            'received_at' => null,
        ]);
    }

    public function partialReceived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'partial_received',
            'approved_by' => User::factory(),
            'approved_at' => fake()->dateTimeBetween($attributes['order_date'] ?? now(), now()),
            'ordered_at' => fake()->dateTimeBetween($attributes['order_date'] ?? now(), now()),
            'received_at' => fake()->dateTimeBetween($attributes['expected_delivery_date'] ?? now(), now()),
        ]);
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'received',
            'approved_by' => User::factory(),
            'approved_at' => fake()->dateTimeBetween($attributes['order_date'] ?? now(), now()),
            'ordered_at' => fake()->dateTimeBetween($attributes['order_date'] ?? now(), now()),
            'received_at' => fake()->dateTimeBetween($attributes['expected_delivery_date'] ?? now(), now()),
            'paid_amount' => $attributes['total_amount'],
            'balance_amount' => 0,
            'payment_status' => 'paid',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function fullyPaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_amount'],
            'balance_amount' => 0,
            'payment_status' => 'paid',
        ]);
    }

    public function partialPaid(): static
    {
        $paid = $attributes['total_amount'] * fake()->randomFloat(2, 0.1, 0.9);
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $paid,
            'balance_amount' => $attributes['total_amount'] - $paid,
            'payment_status' => 'partial',
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => fake()->randomElement(['ordered', 'partial_received']),
            'expected_delivery_date' => fake()->dateTimeBetween('-30 days', '-1 days'),
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'subtotal' => fake()->randomFloat(2, 500000, 5000000),
            'total_amount' => fake()->randomFloat(2, 500000, 5000000),
        ]);
    }
}