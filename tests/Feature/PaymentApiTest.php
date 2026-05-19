<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_owner_can_list_seeded_payments(): void
    {
        $this->seed(DatabaseSeeder::class);
        [$owner, $account] = $this->ownerAndAccount();

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson('/api/v1/payments')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'account_id',
                        'invoice_id',
                        'customer_id',
                        'provider',
                        'provider_payment_id',
                        'amount_cents',
                        'amount',
                        'currency',
                        'status',
                        'failure_reason',
                        'paid_at',
                        'failed_at',
                        'refunded_at',
                        'customer',
                        'invoice',
                    ],
                ],
            ]);
    }

    public function test_viewer_can_view_payment_detail(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@launchbill.test')->firstOrFail();
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $payment = Payment::whereBelongsTo($account)
            ->where('provider_payment_id', 'pf_demo_brightops_failed')
            ->firstOrFail();

        Sanctum::actingAs($viewer);

        $this->withAccount($account)
            ->getJson("/api/v1/payments/{$payment->id}")
            ->assertOk()
            ->assertJsonPath('data.provider', 'payfast')
            ->assertJsonPath('data.provider_payment_id', 'pf_demo_brightops_failed')
            ->assertJsonPath('data.status', 'failed')
            ->assertJsonPath('data.failure_reason', 'Insufficient funds')
            ->assertJsonPath('data.amount_cents', 9900)
            ->assertJsonPath('data.customer.email', 'aisha@brightops.example')
            ->assertJsonPath('data.invoice.number', 'INV-2026-0003');
    }

    public function test_users_without_payment_permission_cannot_view_payments(): void
    {
        $this->seed(DatabaseSeeder::class);
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $user = User::factory()->create(['email' => 'no-payments@launchbill.test']);
        $account->users()->attach($user->id);

        Sanctum::actingAs($user);

        $this->withAccount($account)
            ->getJson('/api/v1/payments')
            ->assertForbidden();
    }

    public function test_payment_routes_are_tenant_isolated(): void
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
        $otherPayment = Payment::factory()->create([
            'account_id' => $otherAccount->id,
            'customer_id' => $otherCustomer->id,
            'invoice_id' => $otherInvoice->id,
        ]);

        Sanctum::actingAs($owner);

        $this->withAccount($account)
            ->getJson("/api/v1/payments/{$otherPayment->id}")
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
