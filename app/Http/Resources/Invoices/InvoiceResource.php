<?php

namespace App\Http\Resources\Invoices;

use App\Http\Resources\Customers\CustomerResource;
use App\Http\Resources\Subscriptions\SubscriptionResource;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Invoice
 */
class InvoiceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'customer_id' => $this->customer_id,
            'subscription_id' => $this->subscription_id,
            'provider_invoice_id' => $this->provider_invoice_id,
            'number' => $this->number,
            'amount_due_cents' => $this->amount_due_cents,
            'amount_due' => number_format($this->amount_due_cents / 100, 2, '.', ''),
            'amount_paid_cents' => $this->amount_paid_cents,
            'amount_paid' => number_format($this->amount_paid_cents / 100, 2, '.', ''),
            'currency' => $this->currency,
            'status' => $this->status,
            'issued_at' => $this->issued_at?->toISOString(),
            'due_at' => $this->due_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'voided_at' => $this->voided_at?->toISOString(),
            'line_items' => $this->line_items ?? [],
            'metadata' => $this->metadata ?? [],
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'subscription' => new SubscriptionResource($this->whenLoaded('subscription')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
