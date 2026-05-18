<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditEvents;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'account_id',
    'customer_id',
    'plan_id',
    'provider_subscription_id',
    'status',
    'quantity',
    'unit_price_cents',
    'currency',
    'starts_at',
    'trial_ends_at',
    'current_period_starts_at',
    'current_period_ends_at',
    'canceled_at',
    'ended_at',
    'metadata',
])]
class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory, RecordsAuditEvents, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'canceled_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'current_period_starts_at' => 'datetime',
            'ended_at' => 'datetime',
            'metadata' => 'array',
            'quantity' => 'integer',
            'starts_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'unit_price_cents' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Account, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
