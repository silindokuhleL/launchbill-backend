<p>Hello {{ $invoice->customer->name }},</p>

<p>Your payment for invoice {{ $invoice->number }} was not completed.</p>

<p>
    Amount:
    {{ $payment->currency }} {{ number_format($payment->amount_cents / 100, 2, '.', ' ') }}
</p>

<p>Reason: {{ $payment->failure_reason ?? 'Payment was not completed by PayFast.' }}</p>

<p>Please update the payment method or retry the payment so your subscription can stay active.</p>
