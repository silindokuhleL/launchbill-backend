<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'dashboard.view',
        'plans.manage',
        'customers.manage',
        'subscriptions.manage',
        'invoices.view',
        'payments.view',
        'team.manage',
        'roles.manage',
        'audit.view',
        'theme.manage',
        'ai.billing_summary',
        'ai.payment_failure_draft',
        'ai.admin_activity_insight',
    ];

    /**
     * Seed global permissions and global roles.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        \setPermissionsTeamId(null);

        collect($this->permissions)
            ->each(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

        Role::findOrCreate('super_admin', 'web')
            ->syncPermissions(Permission::whereIn('name', $this->permissions)->get());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
