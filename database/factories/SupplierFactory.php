<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'code' => null, // Auto-generated in model boot
            'name' => fake()->unique()->company(),
            'contact_person' => fake()->name(),
            'phone' => fake()->unique()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'website' => fake()->url(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'Bangladesh',
            'trade_license_number' => fake()->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            'vat_registration_number' => fake()->unique()->regexify('[0-9]{15}'),
            'tax_identification_number' => fake()->unique()->regexify('[0-9]{12}'),
            'bank_name' => fake()->randomElement([
                'Dutch-Bangla Bank Ltd', 'BRAC Bank Ltd', 'Eastern Bank Ltd',
                'Islami Bank Bangladesh Ltd', 'Pubali Bank Ltd', 'Sonali Bank Ltd'
            ]),
            'bank_account_name' => fake()->company(),
            'bank_account_number' => fake()->regexify('[0-9]{10,16}'),
            'bank_branch' => fake()->streetName() . ' Branch',
            'bank_routing_number' => fake()->regexify('[0-9]{9}'),
            'credit_limit' => fake()->randomElement([0, 50000, 100000, 500000, 1000000]),
            'outstanding_balance' => fake()->randomFloat(2, 0, 100000),
            'payment_terms_days' => fake()->randomElement([0, 7, 15, 30, 45, 60, 90]),
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive', 'blocked']),
            'type' => fake()->randomElement([
                'distributor', 'manufacturer', 'wholesaler', 'importer',
                'local_vendor', 'service_provider'
            ]),
            'lead_time_days' => fake()->numberBetween(1, 30),
            'discount_percentage' => fake()->randomFloat(2, 0, 15),
            'preferences' => [
                'preferred_contact' => fake()->randomElement(['email', 'phone', 'whatsapp']),
                'currency' => 'BDT',
                'delivery_method' => fake()->randomElement(['pickup', 'delivery', 'shipping']),
                'payment_method' => fake()->randomElement(['bank_transfer', 'cash', 'cheque', 'card']),
            ],
            'notes' => fake()->optional()->paragraph(),
            'total_orders' => fake()->numberBetween(0, 100),
            'total_purchase_value' => fake()->randomFloat(2, 0, 1000000),
            'on_time_delivery_rate' => fake()->randomFloat(2, 60, 100),
            'last_order_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
        ];
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

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'blocked',
        ]);
    }

    public function manufacturer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'manufacturer',
            'lead_time_days' => fake()->numberBetween(14, 45),
        ]);
    }

    public function distributor(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'distributor',
            'lead_time_days' => fake()->numberBetween(3, 14),
        ]);
    }

    public function wholesaler(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'wholesaler',
            'lead_time_days' => fake()->numberBetween(1, 7),
            'discount_percentage' => fake()->randomFloat(2, 5, 20),
        ]);
    }

    public function importer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'importer',
            'lead_time_days' => fake()->numberBetween(21, 60),
        ]);
    }

    public function localVendor(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'local_vendor',
            'lead_time_days' => fake()->numberBetween(1, 5),
        ]);
    }

    public function serviceProvider(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'service_provider',
            'lead_time_days' => fake()->numberBetween(1, 3),
        ]);
    }

    public function withCredit(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => fake()->numberBetween(100000, 5000000),
            'outstanding_balance' => fake()->randomFloat(2, 0, 1000000),
        ]);
    }

    public function preferred(): static
    {
        return $this->state(fn (array $attributes) => [
            'on_time_delivery_rate' => fake()->randomFloat(2, 90, 100),
            'discount_percentage' => fake()->randomFloat(2, 10, 20),
        ]);
    }

    public function highVolume(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_orders' => fake()->numberBetween(100, 500),
            'total_purchase_value' => fake()->randomFloat(2, 1000000, 50000000),
        ]);
    }
}