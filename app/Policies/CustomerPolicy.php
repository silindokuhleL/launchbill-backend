<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use App\Services\Tenancy\TenantContext;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManageCustomers($user);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $this->canManageCustomers($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageCustomers($user);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->view($user, $customer);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->view($user, $customer);
    }

    private function canManageCustomers(User $user): bool
    {
        return $user->can('customers.manage') && (bool) app(TenantContext::class)->accountId();
    }
}
