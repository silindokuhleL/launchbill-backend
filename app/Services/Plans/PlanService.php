<?php

namespace App\Services\Plans;

use App\Models\Account;
use App\Models\Plan;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PlanService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext
    ) {}

    /**
     * @return LengthAwarePaginator<int, Plan>
     */
    public function listForTenant(): LengthAwarePaginator
    {
        $account = $this->tenantAccount();

        return Plan::query()
            ->whereBelongsTo($account)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);
    }

    public function create(array $payload, User $user): Plan
    {
        $account = $this->tenantAccount();
        $plan = Plan::create($this->payloadForStorage($payload, $account));

        $this->auditLogger->record(
            action: 'plans.created',
            account: $account,
            user: $user,
            subject: $plan,
            metadata: [
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price_cents' => $plan->price_cents,
            ],
        );

        return $plan;
    }

    public function findForTenant(Plan $plan): Plan
    {
        $account = $this->tenantAccount();

        abort_unless($plan->account_id === $account->id, Response::HTTP_NOT_FOUND);

        return $plan;
    }

    public function update(Plan $plan, array $payload, User $user): Plan
    {
        $account = $this->tenantAccount();
        $plan = $this->findForTenant($plan);
        $plan->update($this->payloadForStorage($payload, $account, $plan));

        $this->auditLogger->record(
            action: 'plans.updated',
            account: $account,
            user: $user,
            subject: $plan,
            metadata: [
                'name' => $plan->name,
                'slug' => $plan->slug,
                'is_active' => $plan->is_active,
            ],
        );

        return $plan->refresh();
    }

    public function delete(Plan $plan, User $user): void
    {
        $account = $this->tenantAccount();
        $plan = $this->findForTenant($plan);

        $this->auditLogger->record(
            action: 'plans.archived',
            account: $account,
            user: $user,
            subject: $plan,
            metadata: [
                'name' => $plan->name,
                'slug' => $plan->slug,
            ],
        );

        $plan->delete();
    }

    private function tenantAccount(): Account
    {
        $account = $this->tenantContext->account();

        abort_unless($account, Response::HTTP_UNPROCESSABLE_ENTITY, 'Select an account before managing plans.');

        return $account;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function payloadForStorage(array $payload, Account $account, ?Plan $plan = null): array
    {
        $name = (string) ($payload['name'] ?? $plan?->name);
        $slug = (string) ($payload['slug'] ?? Str::slug($name));

        return [
            ...$payload,
            'account_id' => $account->id,
            'slug' => $this->uniqueSlug($account, $slug, $plan),
            'currency' => strtoupper((string) ($payload['currency'] ?? $plan?->currency ?? 'ZAR')),
            'trial_days' => $payload['trial_days'] ?? $plan?->trial_days ?? 0,
            'features' => array_values($payload['features'] ?? $plan?->features ?? []),
            'is_active' => $payload['is_active'] ?? $plan?->is_active ?? true,
            'sort_order' => $payload['sort_order'] ?? $plan?->sort_order ?? 0,
        ];
    }

    private function uniqueSlug(Account $account, string $slug, ?Plan $plan = null): string
    {
        $baseSlug = Str::slug($slug) ?: 'plan';
        $nextSlug = $baseSlug;
        $suffix = 2;

        while (
            Plan::query()
                ->whereBelongsTo($account)
                ->where('slug', $nextSlug)
                ->when($plan, fn ($query) => $query->whereKeyNot($plan->getKey()))
                ->exists()
        ) {
            $nextSlug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $nextSlug;
    }
}
