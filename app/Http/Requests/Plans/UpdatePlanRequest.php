<?php

namespace App\Http\Requests\Plans;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('update', $this->route('plan'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'slug' => ['sometimes', 'nullable', 'string', 'alpha_dash:ascii', 'max:140'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'price_cents' => ['sometimes', 'required', 'integer', 'min:0', 'max:100000000'],
            'currency' => ['sometimes', 'nullable', 'string', Rule::in(['ZAR'])],
            'billing_interval' => ['sometimes', 'required', 'string', Rule::in(['monthly', 'yearly'])],
            'trial_days' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:365'],
            'features' => ['sometimes', 'nullable', 'array', 'max:20'],
            'features.*' => ['required', 'string', 'max:160'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
