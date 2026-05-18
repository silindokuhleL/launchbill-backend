<?php

namespace App\Http\Controllers\Api\V1\Plans;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plans\StorePlanRequest;
use App\Http\Requests\Plans\UpdatePlanRequest;
use App\Http\Resources\Plans\PlanResource;
use App\Models\Plan;
use App\Services\Plans\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PlanController extends Controller
{
    public function __construct(
        private readonly PlanService $planService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Plan::class);

        return PlanResource::collection($this->planService->listForTenant());
    }

    public function store(StorePlanRequest $request): PlanResource
    {
        return new PlanResource(
            $this->planService->create($request->validated(), $request->user())
        );
    }

    public function show(Plan $plan): PlanResource
    {
        Gate::authorize('view', $plan);

        return new PlanResource($this->planService->findForTenant($plan));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): PlanResource
    {
        return new PlanResource(
            $this->planService->update($plan, $request->validated(), $request->user())
        );
    }

    public function destroy(Request $request, Plan $plan): JsonResponse
    {
        Gate::authorize('delete', $plan);

        $this->planService->delete($plan, $request->user());

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
