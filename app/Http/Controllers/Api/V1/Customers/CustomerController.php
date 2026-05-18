<?php

namespace App\Http\Controllers\Api\V1\Customers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Http\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Services\Customers\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Customer::class);

        return CustomerResource::collection($this->customerService->listForTenant());
    }

    public function store(StoreCustomerRequest $request): CustomerResource
    {
        return new CustomerResource(
            $this->customerService->create($request->validated(), $request->user())
        );
    }

    public function show(Customer $customer): CustomerResource
    {
        Gate::authorize('view', $customer);

        return new CustomerResource($this->customerService->findForTenant($customer));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        return new CustomerResource(
            $this->customerService->update($customer, $request->validated(), $request->user())
        );
    }

    public function destroy(Request $request, Customer $customer): JsonResponse
    {
        Gate::authorize('delete', $customer);

        $this->customerService->delete($customer, $request->user());

        return response()->json(status: Response::HTTP_NO_CONTENT);
    }
}
