<?php

namespace App\Services\Payments;

use App\Models\Account;
use App\Models\Payment;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class PaymentService
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    /**
     * @return LengthAwarePaginator<int, Payment>
     */
    public function listForTenant(): LengthAwarePaginator
    {
        $account = $this->tenantAccount();

        return Payment::query()
            ->with(['customer', 'invoice'])
            ->whereBelongsTo($account)
            ->orderByRaw("case status when 'failed' then 0 when 'pending' then 1 when 'succeeded' then 2 when 'refunded' then 3 else 4 end")
            ->latest('created_at')
            ->paginate(20);
    }

    public function findForTenant(Payment $payment): Payment
    {
        $account = $this->tenantAccount();

        abort_unless($payment->account_id === $account->id, Response::HTTP_NOT_FOUND);

        return $payment->load(['customer', 'invoice']);
    }

    private function tenantAccount(): Account
    {
        $account = $this->tenantContext->account();

        abort_unless($account, Response::HTTP_UNPROCESSABLE_ENTITY, 'Select an account before viewing payments.');

        return $account;
    }
}
