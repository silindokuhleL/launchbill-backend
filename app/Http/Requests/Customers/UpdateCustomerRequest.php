<?php

namespace App\Http\Requests\Customers;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('customer'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $accountId = app(TenantContext::class)->accountId();
        $customerId = $this->route('customer')?->getKey();

        return [
            'name' => ['sometimes', 'required', 'string', 'max:160'],
            'email' => ['sometimes', 'required', 'email:rfc', 'max:190', Rule::unique('customers', 'email')->where('account_id', $accountId)->ignore($customerId)],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:160'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:60'],
            'provider_customer_id' => ['sometimes', 'nullable', 'string', 'max:190', Rule::unique('customers', 'provider_customer_id')->where('account_id', $accountId)->ignore($customerId)],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(['active', 'inactive'])],
            'billing_address' => ['sometimes', 'nullable', 'array'],
            'billing_address.line1' => ['nullable', 'string', 'max:190'],
            'billing_address.city' => ['nullable', 'string', 'max:120'],
            'billing_address.region' => ['nullable', 'string', 'max:120'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:40'],
            'billing_address.country' => ['nullable', 'string', 'size:2'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
