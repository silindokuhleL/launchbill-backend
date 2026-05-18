<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $account = Account::factory();
        $issuedAt = now()->subDays(fake()->numberBetween(1, 30));
        $amountDueCents = fake()->randomElement([9900, 24900, 49800, 79900]);
        $status = fake()->randomElement(['draft', 'open', 'paid', 'overdue']);

        return [
            'account_id' => $account,
            'customer_id' => Customer::factory()->for($account),
            'subscription_id' => Subscription::factory()->for($account),
            'provider_invoice_id' => fake()->optional()->bothify('inv_########'),
            'number' => 'INV-'.fake()->unique()->numerify('2026-####'),
            'amount_due_cents' => $amountDueCents,
            'amount_paid_cents' => $status === 'paid' ? $amountDueCents : 0,
            'currency' => 'ZAR',
            'status' => $status,
            'issued_at' => $issuedAt,
            'due_at' => (clone $issuedAt)->addDays(14),
            'paid_at' => $status === 'paid' ? (clone $issuedAt)->addDays(4) : null,
            'voided_at' => null,
            'line_items' => [
                [
                    'description' => 'Subscription billing',
                    'quantity' => 1,
                    'unit_price_cents' => $amountDueCents,
                    'amount_cents' => $amountDueCents,
                ],
            ],
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }
}
