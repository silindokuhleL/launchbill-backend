<?php

namespace App\Services\Notifications;

use App\Mail\PaymentFailedMail;
use App\Mail\PaymentSucceededMail;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Mail;

class BillingNotificationService
{
    public function paymentSucceeded(Payment $payment, Invoice $invoice): void
    {
        $invoice->loadMissing('customer');

        $email = $invoice->customer?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new PaymentSucceededMail($payment, $invoice));
    }

    public function paymentFailed(Payment $payment, Invoice $invoice): void
    {
        $invoice->loadMissing('customer');

        $email = $invoice->customer?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new PaymentFailedMail($payment, $invoice));
    }
}
