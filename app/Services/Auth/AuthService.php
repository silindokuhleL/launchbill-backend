<?php

namespace App\Services\Auth;

use App\Models\Account;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function __construct(
        private readonly AuditLogger $auditLogger
    ) {}

    /**
     * @param  array{email: string, password: string, device_name?: string|null}  $payload
     * @return array{token: string, user: User}
     */
    public function login(array $payload): array
    {
        $user = User::query()
            ->where('email', $payload['email'])
            ->first();

        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($payload['device_name'] ?? 'launchbill-api')->plainTextToken;

        $this->auditLogger->record(
            action: 'auth.login',
            user: $user,
            metadata: ['email' => $user->email],
        );

        return [
            'token' => $token,
            'user' => $this->loadAuthenticatedUser($user),
        ];
    }

    /**
     * @param  array{name: string, email: string, password: string, account_name: string, billing_email?: string|null, device_name?: string|null}  $payload
     * @return array{token: string, user: User}
     */
    public function register(array $payload): array
    {
        return DB::transaction(function () use ($payload): array {
            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => $payload['password'],
            ]);

            $account = Account::create([
                'name' => $payload['account_name'],
                'owner_id' => $user->id,
                'billing_email' => $payload['billing_email'] ?? $payload['email'],
            ]);

            $account->users()->attach($user->id, ['is_owner' => true]);

            \setPermissionsTeamId($account->id);
            Role::findOrCreate('account_owner', 'web');
            $user->assignRole('account_owner');
            \setPermissionsTeamId(null);

            $token = $user->createToken($payload['device_name'] ?? 'launchbill-api')->plainTextToken;

            $this->auditLogger->record(
                action: 'auth.register',
                account: $account,
                user: $user,
                subject: $account,
                metadata: ['email' => $user->email],
            );

            return [
                'token' => $token,
                'user' => $this->loadAuthenticatedUser($user),
            ];
        });
    }

    public function loadAuthenticatedUser(User $user): User
    {
        return $user->load([
            'accounts' => fn ($query) => $query->orderBy('accounts.name'),
            'roles',
        ]);
    }
}
