<?php

namespace App\Http\Controllers\Api\V1\Subscriptions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\StoreSubscriptionRequest;
use App\Http\Resources\Subscriptions\SubscriptionResource;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Subscription::class);

        return SubscriptionResource::collection($this->subscriptionService->listForTenant());
    }

    public function store(StoreSubscriptionRequest $request): SubscriptionResource
    {
        return new SubscriptionResource(
            $this->subscriptionService->create($request->validated(), $request->user())
        );
    }

    public function show(Subscription $subscription): SubscriptionResource
    {
        Gate::authorize('view', $subscription);

        return new SubscriptionResource($this->subscriptionService->findForTenant($subscription));
    }

    public function cancel(Request $request, Subscription $subscription): SubscriptionResource
    {
        Gate::authorize('cancel', $subscription);

        return new SubscriptionResource(
            $this->subscriptionService->cancel($subscription, $request->user())
        );
    }

    public function resume(Request $request, Subscription $subscription): SubscriptionResource
    {
        Gate::authorize('resume', $subscription);

        return new SubscriptionResource(
            $this->subscriptionService->resume($subscription, $request->user())
        );
    }
}
