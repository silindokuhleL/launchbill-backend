<?php

namespace App\Services\Webhooks;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\WebhookEvent;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayFastWebhookService
{
    public function __construct(
        private readonly PayFastSignatureVerifier $signatureVerifier,
        private readonly AuditLogger $auditLogger
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): PayFastWebhookResult
    {
        if (! $this->signatureVerifier->isValid($payload)) {
            throw ValidationException::withMessages([
                'signature' => 'The PayFast signature is invalid.',
            ]);
        }

        $providerEventId = $this->providerEventId($payload);
        $existingEvent = WebhookEvent::query()
            ->where('provider', 'payfast')
            ->where('provider_event_id', $providerEventId)
            ->first();

        if ($existingEvent) {
            return new PayFastWebhookResult($existingEvent, true);
        }

        return DB::transaction(function () use ($payload, $providerEventId): PayFastWebhookResult {
            $event = WebhookEvent::create([
                'provider' => 'payfast',
                'provider_event_id' => $providerEventId,
                'type' => $this->eventType($payload),
                'payload' => $this->safePayload($payload),
                'status' => 'received',
            ]);

            $invoice = $this->findInvoice($payload);

            if (! $invoice) {
                $event->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => 'Matching invoice was not found.',
                ]);

                return new PayFastWebhookResult($event);
            }

            $payment = $this->upsertPayment($invoice, $payload, $providerEventId);
            $this->syncInvoice($invoice, $payment);

            $event->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            $this->auditLogger->record(
                action: 'webhooks.payfast.processed',
                account: $invoice->account,
                subject: $payment,
                metadata: [
                    'provider_event_id' => $providerEventId,
                    'invoice_number' => $invoice->number,
                    'payment_status' => $payment->status,
                ],
            );

            return new PayFastWebhookResult($event->refresh());
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function upsertPayment(Invoice $invoice, array $payload, string $providerEventId): Payment
    {
        $status = $this->paymentStatus($payload);
        $timestamp = now();

        return Payment::updateOrCreate(
            [
                'account_id' => $invoice->account_id,
                'provider_payment_id' => $this->stringValue($payload, 'pf_payment_id') ?: $providerEventId,
            ],
            [
                'account_id' => $invoice->account_id,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'provider' => 'payfast',
                'provider_payment_id' => $this->stringValue($payload, 'pf_payment_id') ?: $providerEventId,
                'amount_cents' => $this->amountCents($payload),
                'currency' => 'ZAR',
                'status' => $status,
                'failure_reason' => $status === 'failed' ? $this->failureReason($payload) : null,
                'paid_at' => $status === 'succeeded' ? $timestamp : null,
                'failed_at' => $status === 'failed' ? $timestamp : null,
                'refunded_at' => $status === 'refunded' ? $timestamp : null,
                'metadata' => [
                    'source' => 'payfast_itn',
                    'payment_status' => $this->stringValue($payload, 'payment_status'),
                ],
            ],
        );
    }

    private function syncInvoice(Invoice $invoice, Payment $payment): void
    {
        if ($payment->status === 'succeeded') {
            $invoice->update([
                'amount_paid_cents' => $payment->amount_cents,
                'status' => 'paid',
                'paid_at' => $payment->paid_at ?? now(),
            ]);

            return;
        }

        if ($payment->status === 'failed' && $invoice->status === 'paid') {
            return;
        }

        if ($payment->status === 'failed') {
            $invoice->update([
                'amount_paid_cents' => 0,
                'paid_at' => null,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function findInvoice(array $payload): ?Invoice
    {
        $invoiceNumber = $this->stringValue($payload, 'm_payment_id');

        if ($invoiceNumber === null) {
            return null;
        }

        $query = Invoice::query()
            ->with('account')
            ->where('number', $invoiceNumber);

        $accountId = $this->stringValue($payload, 'custom_int1') ?: $this->stringValue($payload, 'custom_str1');

        if ($accountId !== null && ctype_digit($accountId)) {
            $query->where('account_id', (int) $accountId);
        }

        return $query->first();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function providerEventId(array $payload): string
    {
        return $this->stringValue($payload, 'pf_payment_id')
            ?: $this->stringValue($payload, 'm_payment_id')
            ?: md5(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function eventType(array $payload): string
    {
        return 'payment.'.strtolower($this->stringValue($payload, 'payment_status') ?: 'unknown');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function paymentStatus(array $payload): string
    {
        return match (strtoupper($this->stringValue($payload, 'payment_status') ?: '')) {
            'COMPLETE' => 'succeeded',
            'FAILED', 'CANCELLED' => 'failed',
            'REFUNDED' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function amountCents(array $payload): int
    {
        $amount = str_replace(',', '.', $this->stringValue($payload, 'amount_gross') ?: '0');

        return (int) round(((float) $amount) * 100);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function failureReason(array $payload): string
    {
        return $this->stringValue($payload, 'reason')
            ?: $this->stringValue($payload, 'failure_reason')
            ?: 'Payment was not completed by PayFast.';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function safePayload(array $payload): array
    {
        return collect($payload)
            ->except(['merchant_key', 'signature'])
            ->all();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function stringValue(array $payload, string $key): ?string
    {
        if (! array_key_exists($key, $payload)) {
            return null;
        }

        $value = trim((string) $payload[$key]);

        return $value === '' ? null : $value;
    }
}
