<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Seed demo subscription plans.
     */
    public function run(): void
    {
        $account = Account::where('name', 'Acme LaunchBill Demo')->first();

        if (! $account) {
            return;
        }

        collect($this->plans())->each(function (array $plan) use ($account): void {
            Plan::updateOrCreate(
                [
                    'account_id' => $account->id,
                    'slug' => $plan['slug'],
                ],
                [
                    ...$plan,
                    'account_id' => $account->id,
                ],
            );
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function plans(): array
    {
        return [
            [
                'name' => 'Launch Starter',
                'slug' => 'launch-starter',
                'description' => 'Starter billing for small teams validating a SaaS offer.',
                'price_cents' => 9900,
                'currency' => 'ZAR',
                'billing_interval' => 'monthly',
                'trial_days' => 7,
                'features' => ['Customer portal', 'Manual invoices', 'Email support'],
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Growth Billing',
                'slug' => 'growth-billing',
                'description' => 'Automated subscriptions, billing reminders, and team workflows.',
                'price_cents' => 24900,
                'currency' => 'ZAR',
                'billing_interval' => 'monthly',
                'trial_days' => 14,
                'features' => ['Subscription billing', 'Payment reminders', 'Team roles'],
                'is_active' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'Scale Operations',
                'slug' => 'scale-operations',
                'description' => 'Advanced billing controls for growing SaaS operations.',
                'price_cents' => 79900,
                'currency' => 'ZAR',
                'billing_interval' => 'monthly',
                'trial_days' => 14,
                'features' => ['AI billing summaries', 'Audit exports', 'Priority support'],
                'is_active' => true,
                'sort_order' => 30,
            ],
        ];
    }
}
