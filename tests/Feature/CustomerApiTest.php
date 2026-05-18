<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_owner_can_list_seeded_customers(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson('/api/v1/customers')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.email', 'aisha@brightops.example')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'account_id',
                        'name',
                        'email',
                        'company_name',
                        'phone',
                        'provider_customer_id',
                        'status',
                        'billing_address',
                        'notes',
                    ],
                ],
            ]);
    }

    public function test_billing_manager_can_create_update_and_archive_customer(): void
    {
        $this->seed(DatabaseSeeder::class);
        $billingManager = User::where('email', 'billing@launchbill.test')->firstOrFail();
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();

        Sanctum::actingAs($billingManager);

        $createdCustomerId = $this->withAccount($account)
            ->postJson('/api/v1/customers', [
                'name' => 'Browser Ventures',
                'email' => 'HELLO@BROWSER-VENTURES.EXAMPLE',
                'company_name' => 'Browser Ventures',
                'phone' => '+27 82 222 0000',
                'billing_address' => [
                    'line1' => '1 Test Avenue',
                    'city' => 'Pretoria',
                    'region' => 'Gauteng',
                    'postal_code' => '0002',
                    'country' => 'ZA',
                ],
                'notes' => 'Created from PHPUnit.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Browser Ventures')
            ->assertJsonPath('data.email', 'hello@browser-ventures.example')
            ->assertJsonPath('data.billing_address.city', 'Pretoria')
            ->json('data.id');

        $this->assertDatabaseHas('customers', [
            'id' => $createdCustomerId,
            'account_id' => $account->id,
            'email' => 'hello@browser-ventures.example',
        ]);

        $this->withAccount($account)
            ->patchJson("/api/v1/customers/{$createdCustomerId}", [
                'name' => 'Browser Ventures Plus',
                'status' => 'inactive',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Browser Ventures Plus')
            ->assertJsonPath('data.status', 'inactive');

        $this->withAccount($account)
            ->deleteJson("/api/v1/customers/{$createdCustomerId}")
            ->assertNoContent();

        $this->assertSoftDeleted('customers', ['id' => $createdCustomerId]);
        $this->assertSame(3, AuditLog::whereIn('action', [
            'customers.created',
            'customers.updated',
            'customers.archived',
        ])->count());
    }

    public function test_duplicate_customer_email_is_rejected_per_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->postJson('/api/v1/customers', [
                'name' => 'Duplicate Northstar',
                'email' => 'naledi@northstar.example',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_users_without_customer_permission_cannot_manage_customers(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@launchbill.test')->firstOrFail();
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();

        Sanctum::actingAs($viewer);

        $this->withAccount($account)
            ->getJson('/api/v1/customers')
            ->assertForbidden();

        $this->withAccount($account)
            ->postJson('/api/v1/customers', [
                'name' => 'Forbidden Customer',
                'email' => 'forbidden@example.test',
            ])
            ->assertForbidden();
    }

    public function test_customer_routes_are_tenant_isolated(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();
        $otherAccount = Account::factory()->create();
        $otherCustomer = Customer::factory()->create(['account_id' => $otherAccount->id]);

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson("/api/v1/customers/{$otherCustomer->id}")
            ->assertNotFound();
    }

    public function test_customer_validation_rejects_invalid_payload(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->postJson('/api/v1/customers', [
                'name' => '',
                'email' => 'not-an-email',
                'status' => 'paused',
                'billing_address' => [
                    'country' => 'South Africa',
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'status', 'billing_address.country']);
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
