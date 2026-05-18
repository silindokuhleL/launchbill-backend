<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_owner_can_list_seeded_invoices(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson('/api/v1/invoices')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'account_id',
                        'customer_id',
                        'subscription_id',
                        'provider_invoice_id',
                        'number',
                        'amount_due_cents',
                        'amount_due',
                        'amount_paid_cents',
                        'amount_paid',
                        'currency',
                        'status',
                        'issued_at',
                        'due_at',
                        'line_items',
                        'customer',
                        'subscription',
                    ],
                ],
            ]);
    }

    public function test_viewer_can_view_invoice_detail(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@launchbill.test')->firstOrFail();
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $invoice = Invoice::whereBelongsTo($account)
            ->where('number', 'INV-2026-0002')
            ->firstOrFail();

        Sanctum::actingAs($viewer);

        $this->withAccount($account)
            ->getJson("/api/v1/invoices/{$invoice->id}")
            ->assertOk()
            ->assertJsonPath('data.number', 'INV-2026-0002')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.amount_due_cents', 79900)
            ->assertJsonPath('data.customer.email', 'thabo@greenledger.example')
            ->assertJsonPath('data.subscription.provider_subscription_id', 'demo_sub_greenledger_scale');
    }

    public function test_users_without_invoice_permission_cannot_view_invoices(): void
    {
        $this->seed(DatabaseSeeder::class);
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $user = User::factory()->create(['email' => 'no-invoices@launchbill.test']);
        $account->users()->attach($user->id);

        Sanctum::actingAs($user);

        $this->withAccount($account)
            ->getJson('/api/v1/invoices')
            ->assertForbidden();
    }

    public function test_invoice_routes_are_tenant_isolated(): void
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
        $otherInvoice = Invoice::factory()->create([
            'account_id' => $otherAccount->id,
            'customer_id' => $otherCustomer->id,
            'subscription_id' => $otherSubscription->id,
        ]);

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson("/api/v1/invoices/{$otherInvoice->id}")
            ->assertNotFound();
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
