<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditEvents;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'account_id',
    'customer_id',
    'subscription_id',
    'provider_invoice_id',
    'number',
    'amount_due_cents',
    'amount_paid_cents',
    'currency',
    'status',
    'issued_at',
    'due_at',
    'paid_at',
    'voided_at',
    'line_items',
    'metadata',
])]
class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, RecordsAuditEvents, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount_due_cents' => 'integer',
            'amount_paid_cents' => 'integer',
            'due_at' => 'datetime',
            'issued_at' => 'datetime',
            'line_items' => 'array',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
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
     * @return BelongsTo<Subscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
