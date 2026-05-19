<?php

namespace App\Jobs;

use App\Mail\PaymentSucceededMail;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendPaymentSucceededEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly Payment $payment,
        public readonly Invoice $invoice
    ) {}

    public function handle(): void
    {
        $this->invoice->loadMissing('customer');

        $email = $this->invoice->customer?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->send(new PaymentSucceededMail($this->payment, $this->invoice));
    }
}
