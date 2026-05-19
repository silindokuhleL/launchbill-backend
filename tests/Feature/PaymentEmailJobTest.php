<?php

namespace Tests\Feature;

use App\Jobs\SendPaymentFailedEmail;
use App\Jobs\SendPaymentSucceededEmail;
use App\Mail\PaymentFailedMail;
use App\Mail\PaymentSucceededMail;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentEmailJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_succeeded_payment_email_job_sends_customer_mail(): void
    {
        $this->seed(DatabaseSeeder::class);
        Mail::fake();

        [$invoice, $payment] = $this->invoiceAndPayment('INV-2026-0001', 'pf_demo_northstar_001');

        (new SendPaymentSucceededEmail($payment, $invoice))->handle();

        Mail::assertSent(
            PaymentSucceededMail::class,
            fn (PaymentSucceededMail $mail): bool => $mail->hasTo('naledi@northstar.example')
                && $mail->invoice->is($invoice)
                && $mail->payment->is($payment)
        );
    }

    public function test_failed_payment_email_job_sends_customer_mail(): void
    {
        $this->seed(DatabaseSeeder::class);
        Mail::fake();

        [$invoice, $payment] = $this->invoiceAndPayment('INV-2026-0003', 'pf_demo_brightops_failed');

        (new SendPaymentFailedEmail($payment, $invoice))->handle();

        Mail::assertSent(
            PaymentFailedMail::class,
            fn (PaymentFailedMail $mail): bool => $mail->hasTo('aisha@brightops.example')
                && $mail->invoice->is($invoice)
                && $mail->payment->is($payment)
        );
    }

    /**
     * @return array{0: Invoice, 1: Payment}
     */
    private function invoiceAndPayment(string $invoiceNumber, string $providerPaymentId): array
    {
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $invoice = Invoice::whereBelongsTo($account)
            ->where('number', $invoiceNumber)
            ->firstOrFail();
        $payment = Payment::whereBelongsTo($account)
            ->where('provider_payment_id', $providerPaymentId)
            ->firstOrFail();

        return [$invoice, $payment];
    }
}
