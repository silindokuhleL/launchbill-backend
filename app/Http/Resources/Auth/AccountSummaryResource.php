<?php

namespace App\Http\Resources\Auth;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Account
 */
class AccountSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'billing_email' => $this->billing_email,
            'status' => $this->status,
            'is_owner' => (bool) ($this->pivot?->is_owner ?? false),
            'theme' => [
                'primary_color' => $this->theme_primary_color,
            ],
        ];
    }
}
