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

class DashboardSummaryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_can_view_dashboard_summary_for_selected_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@launchbill.test')->firstOrFail();
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();

        Sanctum::actingAs($viewer);

        $this->withAccount($account)
            ->getJson('/api/v1/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('data.account.id', $account->id)
            ->assertJsonPath('data.account.name', 'Acme LaunchBill Demo')
            ->assertJsonPath('data.revenue.currency', 'ZAR')
            ->assertJsonPath('data.revenue.total_revenue_cents', 49800)
            ->assertJsonPath('data.revenue.total_revenue', '498.00')
            ->assertJsonPath('data.revenue.pending_revenue_cents', 79900)
            ->assertJsonPath('data.revenue.failed_revenue_cents', 9900)
            ->assertJsonPath('data.revenue.outstanding_invoice_cents', 89800)
            ->assertJsonPath('data.revenue.active_mrr_cents', 49800)
            ->assertJsonPath('data.customers.total', 3)
            ->assertJsonPath('data.customers.active', 2)
            ->assertJsonPath('data.customers.inactive', 1)
            ->assertJsonPath('data.plans.total', 3)
            ->assertJsonPath('data.plans.active', 3)
            ->assertJsonPath('data.plans.archived', 0)
            ->assertJsonPath('data.subscriptions.total', 3)
            ->assertJsonPath('data.subscriptions.active', 1)
            ->assertJsonPath('data.subscriptions.trialing', 1)
            ->assertJsonPath('data.subscriptions.paused', 1)
            ->assertJsonPath('data.invoices.total', 3)
            ->assertJsonPath('data.invoices.paid', 1)
            ->assertJsonPath('data.invoices.open', 1)
            ->assertJsonPath('data.invoices.overdue', 1)
            ->assertJsonPath('data.payments.total', 3)
            ->assertJsonPath('data.payments.succeeded', 1)
            ->assertJsonPath('data.payments.pending', 1)
            ->assertJsonPath('data.payments.failed', 1)
            ->assertJsonPath('data.payments.refunded', 0);
    }

    public function test_dashboard_summary_is_scoped_to_selected_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@launchbill.test')->firstOrFail();
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $this->seedOtherAccountMetrics();

        Sanctum::actingAs($viewer);

        $this->withAccount($account)
            ->getJson('/api/v1/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('data.revenue.total_revenue_cents', 49800)
            ->assertJsonPath('data.customers.total', 3)
            ->assertJsonPath('data.payments.succeeded', 1);
    }

    public function test_users_without_dashboard_permission_cannot_view_summary(): void
    {
        $this->seed(DatabaseSeeder::class);
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $user = User::factory()->create(['email' => 'no-dashboard@launchbill.test']);
        $account->users()->attach($user->id);

        Sanctum::actingAs($user);

        $this->withAccount($account)
            ->getJson('/api/v1/dashboard/summary')
            ->assertForbidden();
    }

    public function test_dashboard_summary_requires_selected_account(): void
    {
        $this->seed(DatabaseSeeder::class);
        $viewer = User::where('email', 'viewer@launchbill.test')->firstOrFail();

        Sanctum::actingAs($viewer);

        $this->getJson('/api/v1/dashboard/summary')
            ->assertUnprocessable();
    }

    private function seedOtherAccountMetrics(): void
    {
        $otherAccount = Account::factory()->create(['name' => 'Other Dashboard Account']);
        $customer = Customer::factory()->create([
            'account_id' => $otherAccount->id,
            'status' => 'active',
        ]);
        $plan = Plan::factory()->create([
            'account_id' => $otherAccount->id,
            'price_cents' => 150000,
            'is_active' => true,
        ]);
        $subscription = Subscription::factory()->create([
            'account_id' => $otherAccount->id,
            'customer_id' => $customer->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'quantity' => 2,
            'unit_price_cents' => 150000,
        ]);
        $invoice = Invoice::factory()->create([
            'account_id' => $otherAccount->id,
            'customer_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'amount_due_cents' => 300000,
            'amount_paid_cents' => 300000,
            'status' => 'paid',
        ]);

        Payment::factory()->create([
            'account_id' => $otherAccount->id,
            'customer_id' => $customer->id,
            'invoice_id' => $invoice->id,
            'amount_cents' => 300000,
            'status' => 'succeeded',
        ]);
    }

    private function withAccount(Account $account): self
    {
        return $this->withHeader('X-Account-Id', (string) $account->id);
    }
}
