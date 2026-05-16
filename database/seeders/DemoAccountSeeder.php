<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DemoAccountSeeder extends Seeder
{
    /**
     * Seed a demo account, account users, and tenant-scoped roles.
     */
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'name' => 'LaunchBill Super Admin',
            'email' => 'superadmin@launchbill.test',
        ]);

        \setPermissionsTeamId(null);
        $superAdmin->assignRole('super_admin');

        $owner = User::factory()->create([
            'name' => 'LaunchBill Account Owner',
            'email' => 'owner@launchbill.test',
        ]);

        $billingManager = User::factory()->create([
            'name' => 'LaunchBill Billing Manager',
            'email' => 'billing@launchbill.test',
        ]);

        $viewer = User::factory()->create([
            'name' => 'LaunchBill Viewer',
            'email' => 'viewer@launchbill.test',
        ]);

        $account = Account::factory()->create([
            'name' => 'Acme LaunchBill Demo',
            'owner_id' => $owner->id,
            'billing_email' => 'billing@acme-launchbill.test',
        ]);

        $account->users()->attach($owner->id, ['is_owner' => true]);
        $account->users()->attach($billingManager->id);
        $account->users()->attach($viewer->id);

        $this->seedTenantRoles($account, $owner, $billingManager, $viewer);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedTenantRoles(Account $account, User $owner, User $billingManager, User $viewer): void
    {
        \setPermissionsTeamId($account->id);

        Role::findOrCreate('account_owner', 'web')
            ->syncPermissions($this->permissions([
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
            ]));

        Role::findOrCreate('billing_manager', 'web')
            ->syncPermissions($this->permissions([
                'dashboard.view',
                'customers.manage',
                'subscriptions.manage',
                'invoices.view',
                'payments.view',
                'ai.billing_summary',
                'ai.payment_failure_draft',
            ]));

        Role::findOrCreate('viewer', 'web')
            ->syncPermissions($this->permissions([
                'dashboard.view',
                'invoices.view',
                'payments.view',
                'ai.billing_summary',
            ]));

        $owner->assignRole('account_owner');
        $billingManager->assignRole('billing_manager');
        $viewer->assignRole('viewer');
    }

    /**
     * @param  array<int, string>  $names
     */
    private function permissions(array $names): mixed
    {
        return Permission::whereIn('name', $names)->get();
    }
}
