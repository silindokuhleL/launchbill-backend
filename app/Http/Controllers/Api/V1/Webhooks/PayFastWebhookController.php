<?php

namespace App\Http\Controllers\Api\V1\Webhooks;

use App\Http\Controllers\Controller;
use App\Http\Resources\Webhooks\WebhookEventResource;
use App\Services\Webhooks\PayFastWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PayFastWebhookController extends Controller
{
    public function __construct(
        private readonly PayFastWebhookService $webhookService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $result = $this->webhookService->handle($request->all());

        return (new WebhookEventResource($result->event, $result->duplicate))
            ->response()
            ->setStatusCode($result->duplicate ? Response::HTTP_OK : Response::HTTP_ACCEPTED);
    }
}
