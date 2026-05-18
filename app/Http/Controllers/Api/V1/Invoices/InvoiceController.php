<?php

namespace App\Http\Controllers\Api\V1\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Services\Invoices\InvoiceService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Invoice::class);

        return InvoiceResource::collection($this->invoiceService->listForTenant());
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        Gate::authorize('view', $invoice);

        return new InvoiceResource($this->invoiceService->findForTenant($invoice));
    }
}
