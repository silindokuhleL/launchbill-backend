<?php

namespace App\Http\Resources\Subscriptions;

use App\Http\Resources\Customers\CustomerResource;
use App\Http\Resources\Plans\PlanResource;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subscription
 */
class SubscriptionResource extends JsonResource
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
            'plan_id' => $this->plan_id,
            'provider_subscription_id' => $this->provider_subscription_id,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'unit_price_cents' => $this->unit_price_cents,
            'currency' => $this->currency,
            'starts_at' => $this->starts_at?->toISOString(),
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'current_period_starts_at' => $this->current_period_starts_at?->toISOString(),
            'current_period_ends_at' => $this->current_period_ends_at?->toISOString(),
            'canceled_at' => $this->canceled_at?->toISOString(),
            'ended_at' => $this->ended_at?->toISOString(),
            'metadata' => $this->metadata ?? [],
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
