<?php

namespace App\Services\Audit;

use App\Models\Account;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $action,
        ?Account $account = null,
        ?User $user = null,
        ?Model $subject = null,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'account_id' => $account?->getKey(),
            'user_id' => $user?->getKey(),
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'metadata' => $this->sanitizeMetadata($metadata),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function sanitizeMetadata(array $metadata): array
    {
        $blockedKeys = [
            'authorization',
            'card_number',
            'cvv',
            'password',
            'password_confirmation',
            'secret',
            'token',
        ];

        return collect($metadata)
            ->mapWithKeys(function (mixed $value, int|string $key) use ($blockedKeys): array {
                if (in_array(strtolower((string) $key), $blockedKeys, true)) {
                    return [$key => '[redacted]'];
                }

                if (is_array($value)) {
                    return [$key => $this->sanitizeMetadata($value)];
                }

                return [$key => $value];
            })
            ->all();
    }
}
