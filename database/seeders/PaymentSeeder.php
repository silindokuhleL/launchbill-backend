<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Seed demo payments for the billing catalog.
     */
    public function run(): void
    {
        $account = Account::where('name', 'Acme LaunchBill Demo')->first();

        if (! $account) {
            return;
        }

        collect($this->payments())->each(function (array $payment) use ($account): void {
            $invoice = Invoice::query()
                ->whereBelongsTo($account)
                ->where('number', $payment['invoice_number'])
                ->first();

            if (! $invoice) {
                return;
            }

            Payment::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'provider_payment_id' => $payment['provider_payment_id'],
                ],
                [
                    'account_id' => $account->id,
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'provider' => $payment['provider'],
                    'provider_payment_id' => $payment['provider_payment_id'],
                    'amount_cents' => $payment['amount_cents'],
                    'currency' => 'ZAR',
                    'status' => $payment['status'],
                    'failure_reason' => $payment['failure_reason'],
                    'paid_at' => $payment['paid_at'],
                    'failed_at' => $payment['failed_at'],
                    'refunded_at' => $payment['refunded_at'],
                    'metadata' => $payment['metadata'],
                ],
            );
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function payments(): array
    {
        return [
            [
                'invoice_number' => 'INV-2026-0001',
                'provider' => 'payfast',
                'provider_payment_id' => 'pf_demo_northstar_001',
                'amount_cents' => 49800,
                'status' => 'succeeded',
                'failure_reason' => null,
                'paid_at' => now()->subDays(9),
                'failed_at' => null,
                'refunded_at' => null,
                'metadata' => ['source' => 'demo', 'method' => 'card'],
            ],
            [
                'invoice_number' => 'INV-2026-0002',
                'provider' => 'payfast',
                'provider_payment_id' => 'pf_demo_greenledger_pending',
                'amount_cents' => 79900,
                'status' => 'pending',
                'failure_reason' => null,
                'paid_at' => null,
                'failed_at' => null,
                'refunded_at' => null,
                'metadata' => ['source' => 'demo', 'method' => 'eft'],
            ],
            [
                'invoice_number' => 'INV-2026-0003',
                'provider' => 'payfast',
                'provider_payment_id' => 'pf_demo_brightops_failed',
                'amount_cents' => 9900,
                'status' => 'failed',
                'failure_reason' => 'Insufficient funds',
                'paid_at' => null,
                'failed_at' => now()->subDays(10),
                'refunded_at' => null,
                'metadata' => ['source' => 'demo', 'retry_recommended' => true],
            ],
        ];
    }
}
