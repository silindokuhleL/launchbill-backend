<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Seed demo invoices for the billing catalog.
     */
    public function run(): void
    {
        $account = Account::where('name', 'Acme LaunchBill Demo')->first();

        if (! $account) {
            return;
        }

        collect($this->invoices())->each(function (array $invoice) use ($account): void {
            $subscription = Subscription::query()
                ->whereBelongsTo($account)
                ->where('provider_subscription_id', $invoice['provider_subscription_id'])
                ->first();

            if (! $subscription) {
                return;
            }

            Invoice::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'number' => $invoice['number'],
                ],
                [
                    'account_id' => $account->id,
                    'customer_id' => $subscription->customer_id,
                    'subscription_id' => $subscription->id,
                    'provider_invoice_id' => $invoice['provider_invoice_id'],
                    'number' => $invoice['number'],
                    'amount_due_cents' => $invoice['amount_due_cents'],
                    'amount_paid_cents' => $invoice['amount_paid_cents'],
                    'currency' => 'ZAR',
                    'status' => $invoice['status'],
                    'issued_at' => $invoice['issued_at'],
                    'due_at' => $invoice['due_at'],
                    'paid_at' => $invoice['paid_at'],
                    'voided_at' => null,
                    'line_items' => $invoice['line_items'],
                    'metadata' => $invoice['metadata'],
                ],
            );
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function invoices(): array
    {
        return [
            [
                'provider_subscription_id' => 'demo_sub_northstar_growth',
                'provider_invoice_id' => 'demo_inv_northstar_001',
                'number' => 'INV-2026-0001',
                'amount_due_cents' => 49800,
                'amount_paid_cents' => 49800,
                'status' => 'paid',
                'issued_at' => now()->subDays(20),
                'due_at' => now()->subDays(6),
                'paid_at' => now()->subDays(9),
                'line_items' => [
                    [
                        'description' => 'Growth Billing x 2',
                        'quantity' => 2,
                        'unit_price_cents' => 24900,
                        'amount_cents' => 49800,
                    ],
                ],
                'metadata' => ['source' => 'demo'],
            ],
            [
                'provider_subscription_id' => 'demo_sub_greenledger_scale',
                'provider_invoice_id' => 'demo_inv_greenledger_001',
                'number' => 'INV-2026-0002',
                'amount_due_cents' => 79900,
                'amount_paid_cents' => 0,
                'status' => 'open',
                'issued_at' => now()->subDays(5),
                'due_at' => now()->addDays(9),
                'paid_at' => null,
                'line_items' => [
                    [
                        'description' => 'Scale Operations',
                        'quantity' => 1,
                        'unit_price_cents' => 79900,
                        'amount_cents' => 79900,
                    ],
                ],
                'metadata' => ['source' => 'demo', 'follow_up' => true],
            ],
            [
                'provider_subscription_id' => 'demo_sub_brightops_paused',
                'provider_invoice_id' => 'demo_inv_brightops_001',
                'number' => 'INV-2026-0003',
                'amount_due_cents' => 9900,
                'amount_paid_cents' => 0,
                'status' => 'overdue',
                'issued_at' => now()->subDays(25),
                'due_at' => now()->subDays(11),
                'paid_at' => null,
                'line_items' => [
                    [
                        'description' => 'Launch Starter',
                        'quantity' => 1,
                        'unit_price_cents' => 9900,
                        'amount_cents' => 9900,
                    ],
                ],
                'metadata' => ['source' => 'demo', 'risk' => 'needs reminder'],
            ],
        ];
    }
}
