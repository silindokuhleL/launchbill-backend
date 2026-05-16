<?php

namespace App\Models\Concerns;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Audit\AuditLogger;

trait RecordsAuditEvents
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function recordAuditEvent(
        string $action,
        array $metadata = [],
        ?Account $account = null,
        ?User $user = null
    ): AuditLog {
        return app(AuditLogger::class)->record(
            action: $action,
            account: $account,
            user: $user,
            subject: $this,
            metadata: $metadata
        );
    }
}
