<?php

namespace App\Http\Requests\Subscriptions;

use App\Models\Customer;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Subscription::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $accountId = app(TenantContext::class)->accountId();

        return [
            'customer_id' => [
                'required',
                'integer',
                Rule::exists(Customer::class, 'id')
                    ->where('account_id', $accountId)
                    ->whereNull('deleted_at'),
            ],
            'plan_id' => [
                'required',
                'integer',
                Rule::exists(Plan::class, 'id')
                    ->where('account_id', $accountId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at'),
            ],
            'provider_subscription_id' => ['nullable', 'string', 'max:190', Rule::unique('subscriptions', 'provider_subscription_id')->where('account_id', $accountId)],
            'status' => ['nullable', 'string', Rule::in(['trialing', 'active', 'past_due', 'paused'])],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'unit_price_cents' => ['nullable', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'starts_at' => ['nullable', 'date'],
            'trial_ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'current_period_starts_at' => ['nullable', 'date'],
            'current_period_ends_at' => ['nullable', 'date', 'after:current_period_starts_at'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
