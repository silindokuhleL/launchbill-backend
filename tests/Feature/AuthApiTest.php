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

        foreach ($this->expectedAccess() as $email => $expectedAccess) {
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
                            'global_permissions',
                        ],
                    ],
                ]);

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => $email,
                'password' => 'password',
                'device_name' => 'phpunit-access',
            ])->assertOk();

            $this->assertSame($expectedAccess['global_roles'], $response->json('data.user.global_roles'));
            $this->assertCount($expectedAccess['account_count'], $response->json('data.user.accounts'));

            if ($expectedAccess['account_count'] > 0) {
                $this->assertSame($expectedAccess['tenant_roles'], $response->json('data.user.accounts.0.roles'));
                $this->assertSame($expectedAccess['permission_count'], count($response->json('data.user.accounts.0.permissions')));
            } else {
                $this->assertSame($expectedAccess['permission_count'], count($response->json('data.user.global_permissions')));
            }
        }

        $this->assertSame(8, PersonalAccessToken::count());
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

    /**
     * @return array<string, array{global_roles: array<int, string>, tenant_roles: array<int, string>, account_count: int, permission_count: int}>
     */
    private function expectedAccess(): array
    {
        return [
            'superadmin@launchbill.test' => [
                'global_roles' => ['super_admin'],
                'tenant_roles' => [],
                'account_count' => 0,
                'permission_count' => 13,
            ],
            'owner@launchbill.test' => [
                'global_roles' => [],
                'tenant_roles' => ['account_owner'],
                'account_count' => 1,
                'permission_count' => 12,
            ],
            'billing@launchbill.test' => [
                'global_roles' => [],
                'tenant_roles' => ['billing_manager'],
                'account_count' => 1,
                'permission_count' => 7,
            ],
            'viewer@launchbill.test' => [
                'global_roles' => [],
                'tenant_roles' => ['viewer'],
                'account_count' => 1,
                'permission_count' => 4,
            ],
        ];
    }
}
