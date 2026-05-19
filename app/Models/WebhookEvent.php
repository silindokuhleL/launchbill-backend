<?php

namespace App\Models;

use Database\Factories\WebhookEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'provider',
    'provider_event_id',
    'type',
    'payload',
    'status',
    'processed_at',
    'failed_at',
    'failure_reason',
])]
class WebhookEvent extends Model
{
    /** @use HasFactory<WebhookEventFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'failed_at' => 'datetime',
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
