<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\User;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TenantContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_select_an_account_they_belong_to(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_id' => $user->id]);
        $account->users()->attach($user->id, ['is_owner' => true]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me', [
            'X-Account-Id' => (string) $account->id,
        ])->assertOk();

        $this->assertNull(app(TenantContext::class)->account());
        $this->assertNull(\getPermissionsTeamId());
    }

    public function test_authenticated_user_cannot_select_another_account(): void
    {
        $user = User::factory()->create();
        $account = Account::factory()->create(['owner_id' => $user->id]);
        $otherAccount = Account::factory()->create();
        $account->users()->attach($user->id, ['is_owner' => true]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/me', [
            'X-Account-Id' => (string) $otherAccount->id,
        ])->assertForbidden();
    }
}
