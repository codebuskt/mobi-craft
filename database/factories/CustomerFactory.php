<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $name = "$firstName $lastName";

        return [
            'tenant_id' => Tenant::factory(),
            'code' => null, // Auto-generated in model boot
            'name' => $name,
            'phone' => Customer::normalizePhone(fake()->unique()->phoneNumber()),
            'email' => fake()->unique()->safeEmail(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'Bangladesh',
            'type' => fake()->randomElement(['walk_in', 'regular', 'corporate', 'wholesale']),
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive', 'blocked']), // Weighted
            'credit_limit' => fake()->randomElement([0, 5000, 10000, 25000, 50000, 100000]),
            'outstanding_balance' => fake()->randomFloat(2, 0, 10000),
            'payment_terms_days' => fake()->randomElement([0, 7, 15, 30, 45, 60]),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'national_id' => fake()->unique()->regexify('[0-9]{10,17}'),
            'tags' => fake()->randomElements(['vip', 'frequent', 'wholesale', 'repair', 'corporate', 'new'], fake()->numberBetween(0, 3)),
            'notes' => fake()->optional()->paragraph(),
            'preferences' => [
                'preferred_contact' => fake()->randomElement(['phone', 'email', 'sms', 'whatsapp']),
                'language' => fake()->randomElement(['en', 'bn']),
                'notification_opt_in' => fake()->boolean(80),
            ],
            'source' => fake()->randomElement(['walk_in', 'referral', 'website', 'social_media', 'advertisement', 'existing_customer']),
            'referred_by' => fake()->optional()->regexify('REF[0-9]{6}'),
            'last_purchase_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'last_repair_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
            'total_purchases' => fake()->numberBetween(0, 50),
            'total_repairs' => fake()->numberBetween(0, 20),
            'lifetime_value' => fake()->randomFloat(2, 0, 50000),
            // Storefront auth fields
            'password' => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'email_verified_at' => fake()->boolean(70) ? now()->subDays(fake()->numberBetween(1, 365)) : null,
            'remember_token' => fake()->optional()->sha256(),
            'last_login_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'storefront_preferences' => [
                'theme' => fake()->randomElement(['light', 'dark', 'system']),
                'currency' => 'BDT',
                'language' => 'en',
                'notifications' => [
                    'order_updates' => fake()->boolean(90),
                    'repair_updates' => fake()->boolean(90),
                    'promotions' => fake()->boolean(50),
                    'newsletter' => fake()->boolean(30),
                ],
            ],
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

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now()->subDays(fake()->numberBetween(1, 365)),
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function walkIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'walk_in',
        ]);
    }

    public function regular(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'regular',
        ]);
    }

    public function corporate(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'corporate',
            'credit_limit' => fake()->numberBetween(50000, 500000),
        ]);
    }

    public function wholesale(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'wholesale',
            'credit_limit' => fake()->numberBetween(100000, 1000000),
        ]);
    }

    public function withCredit(): static
    {
        return $this->state(fn (array $attributes) => [
            'credit_limit' => fake()->numberBetween(10000, 100000),
            'outstanding_balance' => fake()->randomFloat(2, 0, 50000),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'outstanding_balance' => fake()->numberBetween(50000, 200000),
            'credit_limit' => fake()->numberBetween(10000, 100000),
        ]);
    }

    public function forStorefront(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'email_verified_at' => now()->subDays(fake()->numberBetween(1, 365)),
        ]);
    }

    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => fake()->randomElement(['corporate', 'wholesale', 'regular']),
            'credit_limit' => fake()->numberBetween(100000, 1000000),
            'total_purchases' => fake()->numberBetween(20, 100),
            'total_repairs' => fake()->numberBetween(10, 50),
            'lifetime_value' => fake()->randomFloat(2, 100000, 1000000),
        ]);
    }
}