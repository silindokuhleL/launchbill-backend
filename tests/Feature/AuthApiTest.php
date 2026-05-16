<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_users_can_login_with_their_demo_roles(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach ([
            'superadmin@launchbill.test',
            'owner@launchbill.test',
            'billing@launchbill.test',
            'viewer@launchbill.test',
        ] as $email) {
            $this->postJson('/api/v1/auth/login', [
                'email' => $email,
                'password' => 'password',
                'device_name' => 'phpunit',
            ])
                ->assertOk()
                ->assertJsonPath('data.token_type', 'Bearer')
                ->assertJsonPath('data.user.email', $email)
                ->assertJsonStructure([
                    'data' => [
                        'token',
                        'token_type',
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'accounts',
                            'global_roles',
                        ],
                    ],
                ]);
        }

        $this->assertSame(4, PersonalAccessToken::count());
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'owner@launchbill.test',
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_authenticated_user_can_view_their_profile(): void
    {
        $this->seed(DatabaseSeeder::class);
        $token = $this->loginAs('owner@launchbill.test');

        $this->withToken($token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'owner@launchbill.test')
            ->assertJsonCount(1, 'data.accounts');
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $this->seed(DatabaseSeeder::class);
        $token = $this->loginAs('viewer@launchbill.test');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertNoContent();

        $this->assertSame(0, PersonalAccessToken::count());
    }

    public function test_registration_creates_owner_user_account_and_token(): void
    {
        Role::findOrCreate('account_owner', 'web');

        $this->postJson('/api/v1/auth/register', [
            'name' => 'New LaunchBill Owner',
            'email' => 'new-owner@launchbill.test',
            'password' => 'launchbill123',
            'password_confirmation' => 'launchbill123',
            'account_name' => 'New LaunchBill Account',
            'billing_email' => 'billing@new-launchbill.test',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.email', 'new-owner@launchbill.test')
            ->assertJsonPath('data.user.accounts.0.name', 'New LaunchBill Account');

        $account = Account::where('name', 'New LaunchBill Account')->firstOrFail();
        $user = User::where('email', 'new-owner@launchbill.test')->firstOrFail();

        $this->assertTrue($account->users()->whereKey($user->id)->wherePivot('is_owner', true)->exists());

        \setPermissionsTeamId($account->id);
        $this->assertTrue($user->hasRole('account_owner'));
        \setPermissionsTeamId(null);
    }

    private function loginAs(string $email): string
    {
        return $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'password',
            'device_name' => 'phpunit',
        ])->json('data.token');
    }
}
