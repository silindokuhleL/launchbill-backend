<?php

namespace App\Http\Middleware;

use App\Models\Account;
use App\Services\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $accountId = $request->header('X-Account-Id');
        $user = $request->user();

        if (! $accountId || ! $user) {
            return $next($request);
        }

        $account = Account::findOrFail($accountId);
        $tenantContext = app(TenantContext::class);

        abort_unless($tenantContext->userBelongsToAccount($user, $account), Response::HTTP_FORBIDDEN);

        $tenantContext->set($account);
        \setPermissionsTeamId($account->id);

        try {
            return $next($request);
        } finally {
            $tenantContext->clear();
            \setPermissionsTeamId(null);
        }
    }
}
