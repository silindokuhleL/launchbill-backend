<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Seed demo subscriptions for the billing catalog.
     */
    public function run(): void
    {
        $account = Account::where('name', 'Acme LaunchBill Demo')->first();

        if (! $account) {
            return;
        }

        collect($this->subscriptions())->each(function (array $subscription) use ($account): void {
            $customer = Customer::whereBelongsTo($account)
                ->where('email', $subscription['customer_email'])
                ->first();
            $plan = Plan::whereBelongsTo($account)
                ->where('slug', $subscription['plan_slug'])
                ->first();

            if (! $customer || ! $plan) {
                return;
            }

            Subscription::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'provider_subscription_id' => $subscription['provider_subscription_id'],
                ],
                [
                    'account_id' => $account->id,
                    'customer_id' => $customer->id,
                    'plan_id' => $plan->id,
                    'provider_subscription_id' => $subscription['provider_subscription_id'],
                    'status' => $subscription['status'],
                    'quantity' => $subscription['quantity'],
                    'unit_price_cents' => $plan->price_cents,
                    'currency' => $plan->currency,
                    'starts_at' => $subscription['starts_at'],
                    'trial_ends_at' => $subscription['trial_ends_at'],
                    'current_period_starts_at' => $subscription['current_period_starts_at'],
                    'current_period_ends_at' => $subscription['current_period_ends_at'],
                    'canceled_at' => $subscription['canceled_at'],
                    'ended_at' => $subscription['ended_at'],
                    'metadata' => $subscription['metadata'],
                ],
            );
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function subscriptions(): array
    {
        return [
            [
                'customer_email' => 'naledi@northstar.example',
                'plan_slug' => 'growth-billing',
                'provider_subscription_id' => 'demo_sub_northstar_growth',
                'status' => 'active',
                'quantity' => 2,
                'starts_at' => now()->subDays(24),
                'trial_ends_at' => now()->subDays(10),
                'current_period_starts_at' => now()->subDays(10),
                'current_period_ends_at' => now()->addDays(20),
                'canceled_at' => null,
                'ended_at' => null,
                'metadata' => ['source' => 'demo', 'sales_owner' => 'Account Owner'],
            ],
            [
                'customer_email' => 'thabo@greenledger.example',
                'plan_slug' => 'scale-operations',
                'provider_subscription_id' => 'demo_sub_greenledger_scale',
                'status' => 'trialing',
                'quantity' => 1,
                'starts_at' => now()->subDays(3),
                'trial_ends_at' => now()->addDays(11),
                'current_period_starts_at' => now()->subDays(3),
                'current_period_ends_at' => now()->addDays(27),
                'canceled_at' => null,
                'ended_at' => null,
                'metadata' => ['source' => 'demo', 'needs_invoice_grouping' => true],
            ],
            [
                'customer_email' => 'aisha@brightops.example',
                'plan_slug' => 'launch-starter',
                'provider_subscription_id' => 'demo_sub_brightops_paused',
                'status' => 'paused',
                'quantity' => 1,
                'starts_at' => now()->subDays(60),
                'trial_ends_at' => null,
                'current_period_starts_at' => now()->subDays(30),
                'current_period_ends_at' => now()->addDay(),
                'canceled_at' => null,
                'ended_at' => null,
                'metadata' => ['source' => 'demo', 'pause_reason' => 'reviewing plan options'],
            ],
        ];
    }
}
