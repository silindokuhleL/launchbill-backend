<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $account = Account::factory();
        $invoice = Invoice::factory()->for($account);
        $status = fake()->randomElement(['pending', 'succeeded', 'failed', 'refunded']);

        return [
            'account_id' => $account,
            'invoice_id' => $invoice,
            'customer_id' => Customer::factory()->for($account),
            'provider' => 'payfast',
            'provider_payment_id' => fake()->optional()->bothify('pf_########'),
            'amount_cents' => fake()->randomElement([9900, 24900, 49800, 79900]),
            'currency' => 'ZAR',
            'status' => $status,
            'failure_reason' => $status === 'failed' ? fake()->sentence() : null,
            'paid_at' => $status === 'succeeded' ? now()->subDays(fake()->numberBetween(1, 20)) : null,
            'failed_at' => $status === 'failed' ? now()->subDays(fake()->numberBetween(1, 20)) : null,
            'refunded_at' => $status === 'refunded' ? now()->subDays(fake()->numberBetween(1, 20)) : null,
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }
}
