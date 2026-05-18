<?php

namespace App\Policies;

use App\Models\Plan;
use App\Models\User;
use App\Services\Tenancy\TenantContext;

class PlanPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManagePlans($user);
    }

    public function view(User $user, Plan $plan): bool
    {
        return $this->canManagePlans($user);
    }

    public function create(User $user): bool
    {
        return $this->canManagePlans($user);
    }

    public function update(User $user, Plan $plan): bool
    {
        return $this->view($user, $plan);
    }

    public function delete(User $user, Plan $plan): bool
    {
        return $this->view($user, $plan);
    }

    private function canManagePlans(User $user): bool
    {
        return $user->can('plans.manage') && (bool) app(TenantContext::class)->accountId();
    }
}
