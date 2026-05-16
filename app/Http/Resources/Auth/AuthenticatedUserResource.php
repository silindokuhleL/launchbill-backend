<?php

namespace App\Http\Resources\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;

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
            'accounts' => $this->whenLoaded('accounts', fn () => $this->accountAccessSummary()),
            'global_roles' => $this->globalRoleNames(),
            'global_permissions' => $this->globalPermissionNames(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function accountAccessSummary(): Collection
    {
        $originalTeamId = app(PermissionRegistrar::class)->getPermissionsTeamId();

        try {
            return $this->accounts->map(function ($account): array {
                \setPermissionsTeamId($account->id);
                app(PermissionRegistrar::class)->forgetCachedPermissions();
                $this->unsetRelation('roles');
                $this->unsetRelation('permissions');

                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'billing_email' => $account->billing_email,
                    'status' => $account->status,
                    'is_owner' => (bool) ($account->pivot?->is_owner ?? false),
                    'roles' => $this->getRoleNames()->values(),
                    'permissions' => $this->getAllPermissions()->pluck('name')->sort()->values(),
                    'theme' => [
                        'primary_color' => $account->theme_primary_color,
                    ],
                ];
            });
        } finally {
            \setPermissionsTeamId($originalTeamId);
        }
    }

    private function globalRoleNames(): mixed
    {
        $originalTeamId = app(PermissionRegistrar::class)->getPermissionsTeamId();

        try {
            \setPermissionsTeamId(null);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->unsetRelation('roles');
            $this->unsetRelation('permissions');

            return $this->getRoleNames()->values();
        } finally {
            \setPermissionsTeamId($originalTeamId);
        }
    }

    private function globalPermissionNames(): mixed
    {
        $originalTeamId = app(PermissionRegistrar::class)->getPermissionsTeamId();

        try {
            \setPermissionsTeamId(null);
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->unsetRelation('roles');
            $this->unsetRelation('permissions');

            return $this->getAllPermissions()->pluck('name')->sort()->values();
        } finally {
            \setPermissionsTeamId($originalTeamId);
        }
    }
}
