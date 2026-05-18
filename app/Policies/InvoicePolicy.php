<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Services\Tenancy\TenantContext;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewInvoices($user);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $this->canViewInvoices($user);
    }

    private function canViewInvoices(User $user): bool
    {
        return $user->can('invoices.view') && (bool) app(TenantContext::class)->accountId();
    }
}
