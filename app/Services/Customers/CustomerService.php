<?php

namespace App\Services\Customers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class CustomerService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext
    ) {}

    /**
     * @return LengthAwarePaginator<int, Customer>
     */
    public function listForTenant(): LengthAwarePaginator
    {
        $account = $this->tenantAccount();

        return Customer::query()
            ->whereBelongsTo($account)
            ->orderBy('name')
            ->paginate(20);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $user): Customer
    {
        $account = $this->tenantAccount();
        $customer = Customer::create($this->payloadForStorage($payload, $account));

        $this->auditLogger->record(
            action: 'customers.created',
            account: $account,
            user: $user,
            subject: $customer,
            metadata: [
                'name' => $customer->name,
                'email' => $customer->email,
            ],
        );

        return $customer;
    }

    public function findForTenant(Customer $customer): Customer
    {
        $account = $this->tenantAccount();

        abort_unless($customer->account_id === $account->id, Response::HTTP_NOT_FOUND);

        return $customer;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(Customer $customer, array $payload, User $user): Customer
    {
        $account = $this->tenantAccount();
        $customer = $this->findForTenant($customer);
        $customer->update($this->payloadForStorage($payload, $account, $customer));

        $this->auditLogger->record(
            action: 'customers.updated',
            account: $account,
            user: $user,
            subject: $customer,
            metadata: [
                'name' => $customer->name,
                'email' => $customer->email,
                'status' => $customer->status,
            ],
        );

        return $customer->refresh();
    }

    public function delete(Customer $customer, User $user): void
    {
        $account = $this->tenantAccount();
        $customer = $this->findForTenant($customer);

        $this->auditLogger->record(
            action: 'customers.archived',
            account: $account,
            user: $user,
            subject: $customer,
            metadata: [
                'name' => $customer->name,
                'email' => $customer->email,
            ],
        );

        $customer->delete();
    }

    private function tenantAccount(): Account
    {
        $account = $this->tenantContext->account();

        abort_unless($account, Response::HTTP_UNPROCESSABLE_ENTITY, 'Select an account before managing customers.');

        return $account;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function payloadForStorage(array $payload, Account $account, ?Customer $customer = null): array
    {
        return [
            ...$payload,
            'account_id' => $account->id,
            'email' => strtolower((string) ($payload['email'] ?? $customer?->email)),
            'status' => $payload['status'] ?? $customer?->status ?? 'active',
            'billing_address' => $payload['billing_address'] ?? $customer?->billing_address ?? [],
        ];
    }
}
