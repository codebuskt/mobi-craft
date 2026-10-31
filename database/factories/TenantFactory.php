<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'domain' => null,
            'logo' => null,
            'settings' => [
                'theme' => 'light',
                'language' => 'en',
                'currency' => 'BDT',
            ],
            'status' => 'active',
            'trial_ends_at' => now()->addDays(14),
            'subscription_ends_at' => now()->addMonth(),
            'plan_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'subscription_ends_at' => now()->subDays(5),
        ]);
    }

    public function withPlan(int $planId): static
    {
        return $this->state(fn (array $attributes) => [
            'plan_id' => $planId,
            'subscription_ends_at' => now()->addMonth(),
        ]);
    }
}