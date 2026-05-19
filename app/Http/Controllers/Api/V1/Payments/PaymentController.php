<?php

namespace App\Http\Controllers\Api\V1\Payments;

use App\Http\Controllers\Controller;
use App\Http\Resources\Payments\PaymentResource;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Payment::class);

        return PaymentResource::collection($this->paymentService->listForTenant());
    }

    public function show(Payment $payment): PaymentResource
    {
        Gate::authorize('view', $payment);

        return new PaymentResource($this->paymentService->findForTenant($payment));
    }
}
