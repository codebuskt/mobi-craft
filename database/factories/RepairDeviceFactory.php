<?php

namespace Database\Factories;

use App\Models\RepairDevice;
use App\Models\RepairTicket;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RepairDeviceFactory extends Factory
{
    protected $model = RepairDevice::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'repair_ticket_id' => RepairTicket::factory(),
            'product_id' => Product::factory()->forStorefront(),
            'brand' => fake()->company(),
            'model' => fake()->unique()->words(2, true),
            'imei' => fake()->optional()->regexify('[0-9]{15,17}'),
            'serial_number' => fake()->optional()->regexify('SN-[A-Z0-9]{10}'),
            'color' => fake()->randomElement(['Black', 'Silver', 'Gold', 'Blue', 'Red', 'White', 'Gray']),
            'condition' => fake()->randomElement(['new', 'excellent', 'good', 'fair', 'poor']),
            'reported_issue' => fake()->sentence(8),
            'diagnosis_notes' => fake()->optional()->paragraph(),
            'repair_notes' => fake()->optional()->paragraph(),
            'quality_check_notes' => fake()->optional()->paragraph(),
            'accessories_received' => fake()->boolean(60),
            'intake_photos' => null,
            'diagnosis_photos' => null,
            'repair_photos' => null,
            'delivery_photos' => null,
            'device_status' => fake()->randomElement(['received', 'diagnosed', 'repaired', 'ready', 'delivered']),
        ];
    }

    public function newDevice(): static
    {
        return $this->state(fn (array $attributes) => [
            'condition' => 'new',
            'device_status' => 'received',
        ]);
    }

    public function diagnosed(): static
    {
        return $this->state(fn (array $attributes) => [
            'diagnosis_notes' => fake()->paragraph(),
            'device_status' => 'diagnosed',
        ]);
    }

    public function repaired(): static
    {
        return $this->state(fn (array $attributes) => [
            'repair_notes' => fake()->paragraph(),
            'device_status' => 'repaired',
        ]);
    }

    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_status' => 'ready',
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_photos' => fake()->optional()->imageUrl(),
            'device_status' => 'delivered',
        ]);
    }

    public function withProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public defunct(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_status' => 'rejected',
        ]);
    }
}