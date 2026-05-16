<?php

namespace App\Http\Resources\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class AuthenticatedUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'accounts' => AccountSummaryResource::collection($this->whenLoaded('accounts')),
            'global_roles' => $this->when(
                $this->relationLoaded('roles'),
                fn () => $this->roles->whereNull(config('permission.column_names.team_foreign_key'))->pluck('name')->values()
            ),
        ];
    }
}
