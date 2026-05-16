<?php

namespace App\Services\Tenancy;

use App\Models\Account;
use App\Models\User;

class TenantContext
{
    private ?Account $account = null;

    public function set(Account $account): void
    {
        $this->account = $account;
    }

    public function account(): ?Account
    {
        return $this->account;
    }

    public function accountId(): ?int
    {
        return $this->account?->id;
    }

    public function clear(): void
    {
        $this->account = null;
    }

    public function userBelongsToAccount(User $user, Account $account): bool
    {
        return $user->accounts()
            ->whereKey($account->getKey())
            ->exists();
    }
}
