<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'company_name' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'provider_customer_id' => fake()->optional()->bothify('cus_########'),
            'status' => 'active',
            'billing_address' => [
                'line1' => fake()->streetAddress(),
                'city' => fake()->city(),
                'region' => fake()->state(),
                'postal_code' => fake()->postcode(),
                'country' => 'ZA',
            ],
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
