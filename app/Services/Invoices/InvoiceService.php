<?php

namespace App\Services\Invoices;

use App\Models\Account;
use App\Models\Invoice;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class InvoiceService
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    /**
     * @return LengthAwarePaginator<int, Invoice>
     */
    public function listForTenant(): LengthAwarePaginator
    {
        $account = $this->tenantAccount();

        return Invoice::query()
            ->with(['customer', 'subscription.plan'])
            ->whereBelongsTo($account)
            ->orderByRaw("case status when 'overdue' then 0 when 'open' then 1 when 'draft' then 2 when 'paid' then 3 when 'void' then 4 else 5 end")
            ->latest('issued_at')
            ->paginate(20);
    }

    public function findForTenant(Invoice $invoice): Invoice
    {
        $account = $this->tenantAccount();

        abort_unless($invoice->account_id === $account->id, Response::HTTP_NOT_FOUND);

        return $invoice->load(['customer', 'subscription.plan']);
    }

    private function tenantAccount(): Account
    {
        $account = $this->tenantContext->account();

        abort_unless($account, Response::HTTP_UNPROCESSABLE_ENTITY, 'Select an account before viewing invoices.');

        return $account;
    }
}
