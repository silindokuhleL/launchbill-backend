<?php

namespace App\Http\Requests\Customers;

use App\Models\Customer;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Customer::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $accountId = app(TenantContext::class)->accountId();

        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:190', Rule::unique('customers', 'email')->where('account_id', $accountId)],
            'company_name' => ['nullable', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'provider_customer_id' => ['nullable', 'string', 'max:190', Rule::unique('customers', 'provider_customer_id')->where('account_id', $accountId)],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'billing_address' => ['nullable', 'array'],
            'billing_address.line1' => ['nullable', 'string', 'max:190'],
            'billing_address.city' => ['nullable', 'string', 'max:120'],
            'billing_address.region' => ['nullable', 'string', 'max:120'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:40'],
            'billing_address.country' => ['nullable', 'string', 'size:2'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
