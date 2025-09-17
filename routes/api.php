<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\MetricsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check endpoint
Route::get('/health', [HealthController::class, 'check'])->name('health.check');
Route::get('/ready', [HealthController::class, 'ready'])->name('health.ready');

// Metrics endpoint for monitoring
Route::get('/metrics', [MetricsController::class, 'index'])->name('metrics');

// API v1 routes
Route::group(['prefix' => 'v1', 'as' => 'api.v1.'], function () {

    // Authentication routes
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum')->name('refresh');
        Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum')->name('me');
    });

    // Protected user routes
    Route::group(['prefix' => 'users', 'as' => 'users.', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});