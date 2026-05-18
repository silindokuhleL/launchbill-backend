<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;
use App\Services\Tenancy\TenantContext;

class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageSubscriptions($user);
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $this->canManageSubscriptions($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageSubscriptions($user);
    }

    public function cancel(User $user, Subscription $subscription): bool
    {
        return $this->view($user, $subscription);
    }

    public function resume(User $user, Subscription $subscription): bool
    {
        return $this->view($user, $subscription);
    }

    private function canManageSubscriptions(User $user): bool
    {
        return $user->can('subscriptions.manage') && (bool) app(TenantContext::class)->accountId();
    }
}
