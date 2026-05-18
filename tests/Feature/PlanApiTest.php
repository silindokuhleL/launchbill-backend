<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_owner_can_list_seeded_plans(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson('/api/v1/plans')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.slug', 'launch-starter')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'account_id',
                        'name',
                        'slug',
                        'description',
                        'price_cents',
                        'price',
                        'currency',
                        'billing_interval',
                        'trial_days',
                        'features',
                        'is_active',
                        'sort_order',
                    ],
                ],
            ]);
    }

    public function test_account_owner_can_create_update_and_archive_plan(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $createdPlanId = $this->withAccount($account)
            ->postJson('/api/v1/plans', [
                'name' => 'Founder Plan',
                'description' => 'Early access subscription plan.',
                'price_cents' => 14900,
                'billing_interval' => 'monthly',
                'trial_days' => 14,
                'features' => ['Billing dashboard', 'Email notifications'],
                'sort_order' => 5,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Founder Plan')
            ->assertJsonPath('data.slug', 'founder-plan')
            ->assertJsonPath('data.price', '149.00')
            ->json('data.id');

        $this->assertDatabaseHas('plans', [
            'id' => $createdPlanId,
            'account_id' => $account->id,
            'slug' => 'founder-plan',
        ]);

        $this->withAccount($account)
            ->patchJson("/api/v1/plans/{$createdPlanId}", [
                'name' => 'Founder Plus',
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Founder Plus')
            ->assertJsonPath('data.slug', 'founder-plus')
            ->assertJsonPath('data.is_active', false);

        $this->withAccount($account)
            ->deleteJson("/api/v1/plans/{$createdPlanId}")
            ->assertNoContent();

        $this->assertSoftDeleted('plans', ['id' => $createdPlanId]);
        $this->assertSame(3, AuditLog::whereIn('action', [
            'plans.created',
            'plans.updated',
            'plans.archived',
        ])->count());
    }

    public function test_plan_slug_is_unique_per_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->postJson('/api/v1/plans', [
                'name' => 'Launch Starter',
                'price_cents' => 12900,
                'billing_interval' => 'monthly',
            ])
            ->assertCreated()
            ->assertJsonPath('data.slug', 'launch-starter-2');
    }

    public function test_users_without_plan_permission_cannot_manage_plans(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@launchbill.test')->firstOrFail();
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();

        Sanctum::actingAs($viewer);

        $this->withAccount($account)
            ->getJson('/api/v1/plans')
            ->assertForbidden();

        $this->withAccount($account)
            ->postJson('/api/v1/plans', [
                'name' => 'Forbidden Plan',
                'price_cents' => 1000,
                'billing_interval' => 'monthly',
            ])
            ->assertForbidden();
    }

    public function test_plan_routes_are_tenant_isolated(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();
        $otherAccount = Account::factory()->create();
        $otherPlan = Plan::factory()->create(['account_id' => $otherAccount->id]);

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson("/api/v1/plans/{$otherPlan->id}")
            ->assertNotFound();
    }

    public function test_plan_validation_rejects_invalid_payload(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->postJson('/api/v1/plans', [
                'name' => '',
                'price_cents' => -10,
                'billing_interval' => 'weekly',
                'features' => ['Valid feature', 123],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price_cents', 'billing_interval', 'features.1']);
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
