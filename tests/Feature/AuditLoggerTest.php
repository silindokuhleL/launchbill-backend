<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_sanitized_audit_logs(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();

        $auditLog = app(AuditLogger::class)->record(
            action: 'billing.plan_created',
            account: $account,
            user: $user,
            subject: $account,
            metadata: [
                'plan' => 'Growth',
                'password' => 'do-not-store',
                'nested' => [
                    'token' => 'secret-token',
                ],
            ],
        );

        $this->assertInstanceOf(AuditLog::class, $auditLog);
        $this->assertSame($account->id, $auditLog->account_id);
        $this->assertSame($user->id, $auditLog->user_id);
        $this->assertSame('billing.plan_created', $auditLog->action);
        $this->assertSame('Growth', $auditLog->metadata['plan']);
        $this->assertSame('[redacted]', $auditLog->metadata['password']);
        $this->assertSame('[redacted]', $auditLog->metadata['nested']['token']);
    }

    public function test_models_can_record_audit_events_through_the_trait(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();

        $auditLog = $account->recordAuditEvent(
            action: 'tenant.theme_updated',
            metadata: ['color' => '#0f5132'],
            account: $account,
            user: $user,
        );

        $this->assertSame($account->getMorphClass(), $auditLog->subject_type);
        $this->assertSame($account->id, $auditLog->subject_id);
        $this->assertSame('#0f5132', $auditLog->metadata['color']);
    }

    public function test_database_seeders_create_demo_audit_history(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'tenant.demo_seeded',
        ]);
    }
}
