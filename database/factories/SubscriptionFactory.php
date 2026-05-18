<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $account = Account::factory();
        $startsAt = now()->subDays(fake()->numberBetween(1, 45));

        return [
            'account_id' => $account,
            'customer_id' => Customer::factory()->for($account),
            'plan_id' => Plan::factory()->for($account),
            'provider_subscription_id' => fake()->optional()->bothify('sub_########'),
            'status' => fake()->randomElement(['trialing', 'active', 'past_due']),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price_cents' => fake()->randomElement([9900, 24900, 79900]),
            'currency' => 'ZAR',
            'starts_at' => $startsAt,
            'trial_ends_at' => fake()->optional()->dateTimeBetween($startsAt, '+14 days'),
            'current_period_starts_at' => $startsAt,
            'current_period_ends_at' => (clone $startsAt)->addMonth(),
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }
}
