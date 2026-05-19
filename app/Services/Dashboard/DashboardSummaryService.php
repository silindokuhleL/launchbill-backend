<?php

namespace App\Services\Dashboard;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Tenancy\TenantContext;
use Symfony\Component\HttpFoundation\Response;

class DashboardSummaryService
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $account = $this->tenantAccount();

        $totalRevenueCents = $this->paymentSum($account, 'succeeded');
        $pendingRevenueCents = $this->paymentSum($account, 'pending');
        $failedRevenueCents = $this->paymentSum($account, 'failed');
        $outstandingInvoiceCents = $this->outstandingInvoiceCents($account);
        $activeMrrCents = $this->activeMrrCents($account);

        return [
            'account' => [
                'id' => $account->id,
                'name' => $account->name,
                'billing_email' => $account->billing_email,
            ],
            'revenue' => [
                'currency' => 'ZAR',
                'total_revenue_cents' => $totalRevenueCents,
                'total_revenue' => $this->money($totalRevenueCents),
                'pending_revenue_cents' => $pendingRevenueCents,
                'pending_revenue' => $this->money($pendingRevenueCents),
                'failed_revenue_cents' => $failedRevenueCents,
                'failed_revenue' => $this->money($failedRevenueCents),
                'outstanding_invoice_cents' => $outstandingInvoiceCents,
                'outstanding_invoice' => $this->money($outstandingInvoiceCents),
                'active_mrr_cents' => $activeMrrCents,
                'active_mrr' => $this->money($activeMrrCents),
            ],
            'customers' => [
                'total' => $this->customerCount($account),
                'active' => $this->customerCount($account, 'active'),
                'inactive' => $this->customerCount($account, 'inactive'),
            ],
            'plans' => [
                'total' => $this->planCount($account),
                'active' => $this->planCount($account, true),
                'archived' => $this->planCount($account, false),
            ],
            'subscriptions' => [
                'total' => $this->subscriptionCount($account),
                'active' => $this->subscriptionCount($account, 'active'),
                'trialing' => $this->subscriptionCount($account, 'trialing'),
                'paused' => $this->subscriptionCount($account, 'paused'),
                'past_due' => $this->subscriptionCount($account, 'past_due'),
                'canceled' => $this->subscriptionCount($account, 'canceled'),
            ],
            'invoices' => [
                'total' => $this->invoiceCount($account),
                'paid' => $this->invoiceCount($account, 'paid'),
                'open' => $this->invoiceCount($account, 'open'),
                'overdue' => $this->invoiceCount($account, 'overdue'),
                'draft' => $this->invoiceCount($account, 'draft'),
                'void' => $this->invoiceCount($account, 'void'),
            ],
            'payments' => [
                'total' => $this->paymentCount($account),
                'succeeded' => $this->paymentCount($account, 'succeeded'),
                'pending' => $this->paymentCount($account, 'pending'),
                'failed' => $this->paymentCount($account, 'failed'),
                'refunded' => $this->paymentCount($account, 'refunded'),
            ],
        ];
    }

    private function tenantAccount(): Account
    {
        $account = $this->tenantContext->account();

        abort_unless($account, Response::HTTP_UNPROCESSABLE_ENTITY, 'Select an account before viewing dashboard metrics.');

        return $account;
    }

    private function paymentSum(Account $account, string $status): int
    {
        return (int) Payment::query()
            ->whereBelongsTo($account)
            ->where('status', $status)
            ->sum('amount_cents');
    }

    private function outstandingInvoiceCents(Account $account): int
    {
        return (int) Invoice::query()
            ->whereBelongsTo($account)
            ->whereIn('status', ['open', 'overdue'])
            ->get()
            ->sum(fn (Invoice $invoice): int => max(0, $invoice->amount_due_cents - $invoice->amount_paid_cents));
    }

    private function activeMrrCents(Account $account): int
    {
        return (int) Subscription::query()
            ->whereBelongsTo($account)
            ->where('status', 'active')
            ->get()
            ->sum(fn (Subscription $subscription): int => $subscription->unit_price_cents * $subscription->quantity);
    }

    private function customerCount(Account $account, ?string $status = null): int
    {
        return (int) Customer::query()
            ->whereBelongsTo($account)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->count();
    }

    private function planCount(Account $account, ?bool $active = null): int
    {
        return (int) Plan::query()
            ->whereBelongsTo($account)
            ->when($active !== null, fn ($query) => $query->where('is_active', $active))
            ->count();
    }

    private function subscriptionCount(Account $account, ?string $status = null): int
    {
        return (int) Subscription::query()
            ->whereBelongsTo($account)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->count();
    }

    private function invoiceCount(Account $account, ?string $status = null): int
    {
        return (int) Invoice::query()
            ->whereBelongsTo($account)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->count();
    }

    private function paymentCount(Account $account, ?string $status = null): int
    {
        return (int) Payment::query()
            ->whereBelongsTo($account)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->count();
    }

    private function money(int $amountCents): string
    {
        return number_format($amountCents / 100, 2, '.', '');
    }
}
