<?php

namespace App\Http\Resources\Payments;

use App\Http\Resources\Customers\CustomerResource;
use App\Http\Resources\Invoices\InvoiceResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Payment
 */
class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'invoice_id' => $this->invoice_id,
            'customer_id' => $this->customer_id,
            'provider' => $this->provider,
            'provider_payment_id' => $this->provider_payment_id,
            'amount_cents' => $this->amount_cents,
            'amount' => number_format($this->amount_cents / 100, 2, '.', ''),
            'currency' => $this->currency,
            'status' => $this->status,
            'failure_reason' => $this->failure_reason,
            'paid_at' => $this->paid_at?->toISOString(),
            'failed_at' => $this->failed_at?->toISOString(),
            'refunded_at' => $this->refunded_at?->toISOString(),
            'metadata' => $this->metadata ?? [],
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
