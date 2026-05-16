<?php

use App\Http\Controllers\Api\V1\SystemStatusController;
use App\Http\Middleware\ResolveTenantContext;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/status', SystemStatusController::class);
});

Route::middleware(['auth:sanctum', ResolveTenantContext::class])->prefix('v1')->group(function (): void {
    Route::get('/me', fn () => request()->user());
});
