<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_global_and_tenant_roles(): void
    {
        $this->seed(DatabaseSeeder::class);

        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $owner = User::where('email', 'owner@launchbill.test')->firstOrFail();
        $superAdmin = User::where('email', 'superadmin@launchbill.test')->firstOrFail();

        \setPermissionsTeamId(null);

        $this->assertTrue($superAdmin->hasRole('super_admin'));
        $this->assertDatabaseHas('roles', [
            'name' => 'super_admin',
            'account_id' => null,
        ]);

        \setPermissionsTeamId($account->id);

        $this->assertTrue($owner->hasRole('account_owner'));
        $this->assertDatabaseHas('roles', [
            'name' => 'account_owner',
            'account_id' => $account->id,
        ]);
    }

    public function test_it_seeds_demo_account_memberships(): void
    {
        $this->seed(DatabaseSeeder::class);

        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();

        $this->assertCount(3, $account->users);
        $this->assertTrue($account->users()->where('email', 'owner@launchbill.test')->wherePivot('is_owner', true)->exists());
        $this->assertTrue(Role::where('name', 'billing_manager')->where('account_id', $account->id)->exists());
        $this->assertTrue(Role::where('name', 'viewer')->where('account_id', $account->id)->exists());
    }
}
