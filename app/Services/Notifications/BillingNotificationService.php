<?php

namespace App\Services\Notifications;

use App\Jobs\SendPaymentFailedEmail;
use App\Jobs\SendPaymentSucceededEmail;
use App\Models\Invoice;
use App\Models\Payment;

class BillingNotificationService
{
    public function paymentSucceeded(Payment $payment, Invoice $invoice): void
    {
        SendPaymentSucceededEmail::dispatch($payment, $invoice);
    }

    public function paymentFailed(Payment $payment, Invoice $invoice): void
    {
        SendPaymentFailedEmail::dispatch($payment, $invoice);
    }
}
