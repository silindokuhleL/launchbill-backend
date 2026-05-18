<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Customers\CustomerController;
use App\Http\Controllers\Api\V1\Invoices\InvoiceController;
use App\Http\Controllers\Api\V1\Plans\PlanController;
use App\Http\Controllers\Api\V1\Subscriptions\SubscriptionController;
use App\Http\Controllers\Api\V1\SystemStatusController;
use App\Http\Middleware\ResolveTenantContext;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/status', SystemStatusController::class);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware(['auth:sanctum', ResolveTenantContext::class])->prefix('v1')->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::apiResource('plans', PlanController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('subscriptions', SubscriptionController::class)->only(['index', 'store', 'show']);
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel']);
    Route::post('/subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume']);
    Route::apiResource('invoices', InvoiceController::class)->only(['index', 'show']);
});
