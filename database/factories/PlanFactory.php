<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'account_id' => Account::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price_cents' => fake()->randomElement([9900, 19900, 49900, 99900]),
            'currency' => 'ZAR',
            'billing_interval' => fake()->randomElement(['monthly', 'yearly']),
            'trial_days' => fake()->randomElement([0, 7, 14]),
            'features' => fake()->randomElements([
                'Customer portal',
                'Subscription billing',
                'Invoice automation',
                'Payment reminders',
                'AI billing summaries',
            ], 3),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
