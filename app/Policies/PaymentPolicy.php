<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Services\Tenancy\TenantContext;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewPayments($user);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $this->canViewPayments($user);
    }

    private function canViewPayments(User $user): bool
    {
        return $user->can('payments.view') && (bool) app(TenantContext::class)->accountId();
    }
}
