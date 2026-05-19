<?php

namespace App\Http\Resources\Webhooks;

use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WebhookEvent
 */
class WebhookEventResource extends JsonResource
{
    public function __construct($resource, private readonly bool $duplicate = false)
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'provider_event_id' => $this->provider_event_id,
            'type' => $this->type,
            'status' => $this->status,
            'duplicate' => $this->duplicate,
            'processed_at' => $this->processed_at?->toISOString(),
            'failed_at' => $this->failed_at?->toISOString(),
            'failure_reason' => $this->failure_reason,
        ];
    }
}
