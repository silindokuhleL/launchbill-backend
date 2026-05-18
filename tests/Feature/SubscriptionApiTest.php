<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_owner_can_list_seeded_subscriptions(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson('/api/v1/subscriptions')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'account_id',
                        'customer_id',
                        'plan_id',
                        'provider_subscription_id',
                        'status',
                        'quantity',
                        'unit_price_cents',
                        'currency',
                        'current_period_starts_at',
                        'current_period_ends_at',
                        'customer',
                        'plan',
                    ],
                ],
            ]);
    }

    public function test_billing_manager_can_create_cancel_and_resume_subscription(): void
    {
        $this->seed(DatabaseSeeder::class);
        $billingManager = User::where('email', 'billing@launchbill.test')->firstOrFail();
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $customer = Customer::whereBelongsTo($account)->where('email', 'naledi@northstar.example')->firstOrFail();
        $plan = Plan::whereBelongsTo($account)->where('slug', 'launch-starter')->firstOrFail();

        Sanctum::actingAs($billingManager);

        $createdSubscriptionId = $this->withAccount($account)
            ->postJson('/api/v1/subscriptions', [
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'provider_subscription_id' => 'sub_browser_verified',
                'quantity' => 3,
                'trial_ends_at' => now()->addDays(7)->toISOString(),
                'metadata' => [
                    'source' => 'phpunit',
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.customer_id', $customer->id)
            ->assertJsonPath('data.plan_id', $plan->id)
            ->assertJsonPath('data.status', 'trialing')
            ->assertJsonPath('data.quantity', 3)
            ->assertJsonPath('data.unit_price_cents', $plan->price_cents)
            ->assertJsonPath('data.customer.email', 'naledi@northstar.example')
            ->assertJsonPath('data.plan.slug', 'launch-starter')
            ->json('data.id');

        $this->assertDatabaseHas('subscriptions', [
            'id' => $createdSubscriptionId,
            'account_id' => $account->id,
            'provider_subscription_id' => 'sub_browser_verified',
            'status' => 'trialing',
        ]);

        $this->withAccount($account)
            ->postJson("/api/v1/subscriptions/{$createdSubscriptionId}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'canceled')
            ->assertJsonPath('data.ended_at', fn (?string $endedAt) => filled($endedAt));

        $this->withAccount($account)
            ->postJson("/api/v1/subscriptions/{$createdSubscriptionId}/resume")
            ->assertOk()
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.canceled_at', null)
            ->assertJsonPath('data.ended_at', null);

        $this->assertSame(3, AuditLog::whereIn('action', [
            'subscriptions.created',
            'subscriptions.canceled',
            'subscriptions.resumed',
        ])->count());
    }

    public function test_users_without_subscription_permission_cannot_manage_subscriptions(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@launchbill.test')->firstOrFail();
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $subscription = Subscription::whereBelongsTo($account)->firstOrFail();

        Sanctum::actingAs($viewer);

        $this->withAccount($account)
            ->getJson('/api/v1/subscriptions')
            ->assertForbidden();

        $this->withAccount($account)
            ->postJson("/api/v1/subscriptions/{$subscription->id}/cancel")
            ->assertForbidden();
    }

    public function test_subscription_routes_are_tenant_isolated(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();
        $otherAccount = Account::factory()->create();
        $otherCustomer = Customer::factory()->create(['account_id' => $otherAccount->id]);
        $otherPlan = Plan::factory()->create(['account_id' => $otherAccount->id]);
        $otherSubscription = Subscription::factory()->create([
            'account_id' => $otherAccount->id,
            'customer_id' => $otherCustomer->id,
            'plan_id' => $otherPlan->id,
        ]);

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson("/api/v1/subscriptions/{$otherSubscription->id}")
            ->assertNotFound();
    }

    public function test_subscription_validation_rejects_invalid_payload(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();
        $otherAccount = Account::factory()->create();
        $otherCustomer = Customer::factory()->create(['account_id' => $otherAccount->id]);
        $otherPlan = Plan::factory()->create(['account_id' => $otherAccount->id]);

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->postJson('/api/v1/subscriptions', [
                'customer_id' => $otherCustomer->id,
                'plan_id' => $otherPlan->id,
                'status' => 'unknown',
                'quantity' => 0,
                'currency' => 'RAND',
                'current_period_starts_at' => now()->toISOString(),
                'current_period_ends_at' => now()->subDay()->toISOString(),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'customer_id',
                'plan_id',
                'status',
                'quantity',
                'currency',
                'current_period_ends_at',
            ]);
    }

    public function test_inactive_plan_cannot_be_used_for_new_subscription(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();
        $customer = Customer::whereBelongsTo($account)->firstOrFail();
        $inactivePlan = Plan::factory()->create([
            'account_id' => $account->id,
            'is_active' => false,
        ]);

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->postJson('/api/v1/subscriptions', [
                'customer_id' => $customer->id,
                'plan_id' => $inactivePlan->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('plan_id');
    }

    /**
     * @return array{0: User, 1: Account}
     */
    private function ownerAndAccount(): array
    {
        return [
            User::where('email', 'owner@launchbill.test')->firstOrFail(),
            Account::where('name', 'Acme LaunchBill Demo')->firstOrFail(),
        ];
    }

    private function withAccount(Account $account): self
    {
        return $this->withHeader('X-Account-Id', (string) $account->id);
    }
}
