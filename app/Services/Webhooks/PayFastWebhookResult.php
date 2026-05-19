<?php

namespace App\Services\Webhooks;

use App\Models\WebhookEvent;

class PayFastWebhookResult
{
    public function __construct(
        public readonly WebhookEvent $event,
        public readonly bool $duplicate = false
    ) {}
}
