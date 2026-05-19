<p>Hello {{ $invoice->customer->name }},</p>

<p>We received your payment for invoice {{ $invoice->number }}.</p>

<p>
    Amount paid:
    {{ $payment->currency }} {{ number_format($payment->amount_cents / 100, 2, '.', ' ') }}
</p>

<p>Thank you for keeping your LaunchBill subscription up to date.</p>
