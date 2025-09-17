<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $health = [
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'service' => 'user-microservice',
            'version' => config('app.version', '1.0.0'),
        ];

        return response()->json($health);
    }

    public function ready(): JsonResponse
    {
        $checks = [];
        $allPassed = true;

        // Database check
        try {
            DB::connection()->getPdo();
            $checks['database'] = ['status' => 'ok'];
        } catch (\Exception $e) {
            $checks['database'] = ['status' => 'error', 'message' => $e->getMessage()];
            $allPassed = false;
        }

        // Cache check
        try {
            Cache::put('health_check', 'ok', 10);
            $cached = Cache::get('health_check');
            $checks['cache'] = ['status' => $cached === 'ok' ? 'ok' : 'error'];
        } catch (\Exception $e) {
            $checks['cache'] = ['status' => 'error', 'message' => $e->getMessage()];
            $allPassed = false;
        }

        $response = [
            'status' => $allPassed ? 'ready' : 'not_ready',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
        ];

        return response()->json($response, $allPassed ? 200 : 503);
    }
}