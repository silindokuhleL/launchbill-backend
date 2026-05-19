<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\Webhooks\PayFastSignatureVerifier;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayFastWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.payfast.passphrase' => 'launchbill-secret']);
    }

    public function test_payfast_webhook_rejects_invalid_signature(): void
    {
        $this->seed(DatabaseSeeder::class);

        $payload = $this->payload([
            'pf_payment_id' => 'pf_invalid_signature',
            'signature' => 'invalid',
        ]);

        $this->postJson('/api/v1/webhooks/payfast', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('signature');

        $this->assertDatabaseMissing('webhook_events', [
            'provider' => 'payfast',
            'provider_event_id' => 'pf_invalid_signature',
        ]);
    }

    public function test_payfast_webhook_stores_event_and_marks_invoice_paid(): void
    {
        $this->seed(DatabaseSeeder::class);
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();

        $payload = $this->signedPayload([
            'custom_int1' => (string) $account->id,
            'm_payment_id' => 'INV-2026-0002',
            'pf_payment_id' => 'pf_test_greenledger_paid',
            'payment_status' => 'COMPLETE',
            'amount_gross' => '799.00',
        ]);

        $this->postJson('/api/v1/webhooks/payfast', $payload)
            ->assertAccepted()
            ->assertJsonPath('data.provider', 'payfast')
            ->assertJsonPath('data.provider_event_id', 'pf_test_greenledger_paid')
            ->assertJsonPath('data.type', 'payment.complete')
            ->assertJsonPath('data.status', 'processed')
            ->assertJsonPath('data.duplicate', false);

        $invoice = Invoice::where('number', 'INV-2026-0002')->firstOrFail();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame(79900, $invoice->amount_paid_cents);
        $this->assertNotNull($invoice->paid_at);

        $this->assertDatabaseHas('payments', [
            'account_id' => $account->id,
            'invoice_id' => $invoice->id,
            'provider' => 'payfast',
            'provider_payment_id' => 'pf_test_greenledger_paid',
            'amount_cents' => 79900,
            'status' => 'succeeded',
        ]);

        $event = WebhookEvent::where('provider_event_id', 'pf_test_greenledger_paid')->firstOrFail();
        $this->assertSame('processed', $event->status);
        $this->assertArrayNotHasKey('signature', $event->payload);
        $this->assertArrayNotHasKey('merchant_key', $event->payload);
    }

    public function test_duplicate_payfast_webhook_returns_existing_event_without_reprocessing(): void
    {
        $this->seed(DatabaseSeeder::class);
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();

        $payload = $this->signedPayload([
            'custom_int1' => (string) $account->id,
            'm_payment_id' => 'INV-2026-0002',
            'pf_payment_id' => 'pf_test_duplicate_paid',
            'payment_status' => 'COMPLETE',
            'amount_gross' => '799.00',
        ]);

        $this->postJson('/api/v1/webhooks/payfast', $payload)->assertAccepted();

        $this->postJson('/api/v1/webhooks/payfast', $payload)
            ->assertOk()
            ->assertJsonPath('data.provider_event_id', 'pf_test_duplicate_paid')
            ->assertJsonPath('data.status', 'processed')
            ->assertJsonPath('data.duplicate', true);

        $this->assertSame(1, WebhookEvent::where('provider_event_id', 'pf_test_duplicate_paid')->count());
        $this->assertSame(1, Payment::where('provider_payment_id', 'pf_test_duplicate_paid')->count());
    }

    public function test_failed_payfast_webhook_stores_failure_reason(): void
    {
        $this->seed(DatabaseSeeder::class);
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();

        $payload = $this->signedPayload([
            'custom_int1' => (string) $account->id,
            'm_payment_id' => 'INV-2026-0003',
            'pf_payment_id' => 'pf_test_brightops_failed',
            'payment_status' => 'FAILED',
            'amount_gross' => '99.00',
            'reason' => 'Insufficient funds',
        ]);

        $this->postJson('/api/v1/webhooks/payfast', $payload)
            ->assertAccepted()
            ->assertJsonPath('data.provider_event_id', 'pf_test_brightops_failed')
            ->assertJsonPath('data.status', 'processed');

        $this->assertDatabaseHas('payments', [
            'provider_payment_id' => 'pf_test_brightops_failed',
            'amount_cents' => 9900,
            'status' => 'failed',
            'failure_reason' => 'Insufficient funds',
        ]);

        $invoice = Invoice::where('number', 'INV-2026-0003')->firstOrFail();

        $this->assertSame('overdue', $invoice->status);
        $this->assertSame(0, $invoice->amount_paid_cents);
        $this->assertNull($invoice->paid_at);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function signedPayload(array $overrides = []): array
    {
        $payload = $this->payload($overrides);
        $payload['signature'] = app(PayFastSignatureVerifier::class)->signatureFor($payload);

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'merchant_id' => '10000100',
            'merchant_key' => '46f0cd694581a',
            'm_payment_id' => 'INV-2026-0002',
            'pf_payment_id' => 'pf_test_payment',
            'payment_status' => 'COMPLETE',
            'amount_gross' => '799.00',
        ], $overrides);
    }
}
