<?php

namespace App\Services\Webhooks;

class PayFastSignatureVerifier
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function isValid(array $payload): bool
    {
        $submittedSignature = strtolower((string) ($payload['signature'] ?? ''));

        if ($submittedSignature === '') {
            return false;
        }

        return hash_equals($submittedSignature, $this->signatureFor($payload));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function signatureFor(array $payload): string
    {
        $data = collect($payload)
            ->except('signature')
            ->filter(fn (mixed $value): bool => $value !== null && trim((string) $value) !== '')
            ->map(fn (mixed $value, string $key): string => $key.'='.urlencode(trim((string) $value)))
            ->implode('&');

        $passphrase = config('services.payfast.passphrase');

        if (is_string($passphrase) && trim($passphrase) !== '') {
            $data .= '&passphrase='.urlencode(trim($passphrase));
        }

        return md5($data);
    }
}
