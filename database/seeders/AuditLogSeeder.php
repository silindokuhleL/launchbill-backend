<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    /**
     * Seed demo audit history for the sample tenant.
     */
    public function run(): void
    {
        $account = Account::where('name', 'Acme LaunchBill Demo')->firstOrFail();
        $owner = User::where('email', 'owner@launchbill.test')->firstOrFail();

        app(AuditLogger::class)->record(
            action: 'tenant.demo_seeded',
            account: $account,
            user: $owner,
            subject: $account,
            metadata: [
                'source' => 'database_seeder',
                'note' => 'Initial tenant audit trail created for local testing.',
            ],
        );
    }
}
