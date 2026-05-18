<?php

namespace App\Services\Subscriptions;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantContext $tenantContext
    ) {}

    /**
     * @return LengthAwarePaginator<int, Subscription>
     */
    public function listForTenant(): LengthAwarePaginator
    {
        $account = $this->tenantAccount();

        return Subscription::query()
            ->with(['customer', 'plan'])
            ->whereBelongsTo($account)
            ->orderByRaw("case status when 'past_due' then 0 when 'trialing' then 1 when 'active' then 2 when 'paused' then 3 else 4 end")
            ->latest('created_at')
            ->paginate(20);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $user): Subscription
    {
        $account = $this->tenantAccount();
        $plan = Plan::query()
            ->whereBelongsTo($account)
            ->findOrFail($payload['plan_id']);
        Customer::query()
            ->whereBelongsTo($account)
            ->findOrFail($payload['customer_id']);

        $subscription = Subscription::create($this->payloadForStorage($payload, $account, $plan));

        $this->auditLogger->record(
            action: 'subscriptions.created',
            account: $account,
            user: $user,
            subject: $subscription,
            metadata: [
                'customer_id' => $subscription->customer_id,
                'plan_id' => $subscription->plan_id,
                'status' => $subscription->status,
            ],
        );

        return $subscription->load(['customer', 'plan']);
    }

    public function findForTenant(Subscription $subscription): Subscription
    {
        $account = $this->tenantAccount();

        abort_unless($subscription->account_id === $account->id, Response::HTTP_NOT_FOUND);

        return $subscription->load(['customer', 'plan']);
    }

    public function cancel(Subscription $subscription, User $user): Subscription
    {
        $account = $this->tenantAccount();
        $subscription = $this->findForTenant($subscription);
        $now = now();

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => $subscription->canceled_at ?? $now,
            'ended_at' => $subscription->ended_at ?? $now,
        ]);

        $this->auditLogger->record(
            action: 'subscriptions.canceled',
            account: $account,
            user: $user,
            subject: $subscription,
            metadata: [
                'customer_id' => $subscription->customer_id,
                'plan_id' => $subscription->plan_id,
            ],
        );

        return $subscription->refresh()->load(['customer', 'plan']);
    }

    public function resume(Subscription $subscription, User $user): Subscription
    {
        $account = $this->tenantAccount();
        $subscription = $this->findForTenant($subscription);
        $periodStartsAt = now();
        $periodEndsAt = $this->periodEndFor($subscription->plan, $periodStartsAt);

        $subscription->update([
            'status' => 'active',
            'canceled_at' => null,
            'ended_at' => null,
            'current_period_starts_at' => $periodStartsAt,
            'current_period_ends_at' => $periodEndsAt,
        ]);

        $this->auditLogger->record(
            action: 'subscriptions.resumed',
            account: $account,
            user: $user,
            subject: $subscription,
            metadata: [
                'customer_id' => $subscription->customer_id,
                'plan_id' => $subscription->plan_id,
            ],
        );

        return $subscription->refresh()->load(['customer', 'plan']);
    }

    private function tenantAccount(): Account
    {
        $account = $this->tenantContext->account();

        abort_unless($account, Response::HTTP_UNPROCESSABLE_ENTITY, 'Select an account before managing subscriptions.');

        return $account;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function payloadForStorage(array $payload, Account $account, Plan $plan): array
    {
        $startsAt = $this->carbonOrNow($payload['starts_at'] ?? null);
        $currentPeriodStartsAt = $this->carbonOrNow($payload['current_period_starts_at'] ?? $startsAt);
        $currentPeriodEndsAt = $payload['current_period_ends_at'] ?? $this->periodEndFor($plan, $currentPeriodStartsAt);
        $trialEndsAt = isset($payload['trial_ends_at']) ? Carbon::parse($payload['trial_ends_at']) : null;

        return [
            ...$payload,
            'account_id' => $account->id,
            'status' => $payload['status'] ?? ($trialEndsAt?->isFuture() ? 'trialing' : 'active'),
            'quantity' => $payload['quantity'] ?? 1,
            'unit_price_cents' => $payload['unit_price_cents'] ?? $plan->price_cents,
            'currency' => strtoupper((string) ($payload['currency'] ?? $plan->currency)),
            'starts_at' => $startsAt,
            'trial_ends_at' => $trialEndsAt,
            'current_period_starts_at' => $currentPeriodStartsAt,
            'current_period_ends_at' => $currentPeriodEndsAt,
            'metadata' => $payload['metadata'] ?? [],
        ];
    }

    private function carbonOrNow(mixed $value): Carbon
    {
        return $value ? Carbon::parse($value) : now();
    }

    private function periodEndFor(Plan $plan, Carbon $periodStartsAt): Carbon
    {
        return match ($plan->billing_interval) {
            'yearly' => $periodStartsAt->copy()->addYear(),
            default => $periodStartsAt->copy()->addMonth(),
        };
    }
}
