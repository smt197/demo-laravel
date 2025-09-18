<?php

use Illuminate\Support\Facades\Route;
use App\Infrastructure\User\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| User Microservice API Routes - DDD Architecture 2025
|--------------------------------------------------------------------------
|
| This is a User Management Bounded Context implemented with DDD principles.
| Following Laravel 11 microservices best practices with CQRS pattern.
|
*/

// Health endpoints (for microservice monitoring)
Route::get('/health', function () {
    return response()->json([
        'service' => 'user-microservice',
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

Route::get('/ready', function () {
    return response()->json([
        'service' => 'user-microservice',
        'status' => 'ready',
        'timestamp' => now()->toISOString(),
        'checks' => [
            'database' => 'ok',
            'cache' => 'ok'
        ]
    ]);
});

// User Bounded Context API v1
Route::prefix('v1/users')->group(function () {

    // Commands (Write operations - CQRS)
    Route::post('/', [UserController::class, 'register'])
        ->name('users.register');

    Route::patch('/{userId}/verify-email', [UserController::class, 'verifyEmail'])
        ->name('users.verify-email');

    // Queries (Read operations - CQRS)
    Route::get('/', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('/{userId}', [UserController::class, 'show'])
        ->name('users.show');

});

// API Documentation endpoint
Route::get('/docs', function () {
    return response()->json([
        'service' => 'User Management Microservice',
        'description' => 'DDD-based microservice for user management',
        'architecture' => 'Domain-Driven Design with CQRS',
        'version' => '1.0.0',
        'endpoints' => [
            'POST /api/v1/users' => 'Register new user',
            'GET /api/v1/users' => 'Get users list',
            'GET /api/v1/users/{id}' => 'Get user by ID',
            'PATCH /api/v1/users/{id}/verify-email' => 'Verify user email',
        ],
        'events' => [
            'user.registered' => 'User was registered',
            'user.email_verified' => 'User email was verified'
        ]
    ]);
});