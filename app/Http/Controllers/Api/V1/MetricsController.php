<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MetricsController extends Controller
{
    public function index(): Response
    {
        $metrics = [];

        // Application metrics
        $metrics[] = '# HELP app_users_total Total number of users';
        $metrics[] = '# TYPE app_users_total counter';
        $metrics[] = 'app_users_total ' . \App\Models\User::count();

        // Database metrics
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $dbLatency = (microtime(true) - $start) * 1000;

            $metrics[] = '# HELP db_connection_latency_ms Database connection latency in milliseconds';
            $metrics[] = '# TYPE db_connection_latency_ms gauge';
            $metrics[] = 'db_connection_latency_ms ' . round($dbLatency, 2);
        } catch (\Exception $e) {
            $metrics[] = 'db_connection_latency_ms -1';
        }

        // Cache metrics
        try {
            $start = microtime(true);
            Cache::put('metrics_test', 'ok', 1);
            Cache::get('metrics_test');
            $cacheLatency = (microtime(true) - $start) * 1000;

            $metrics[] = '# HELP cache_latency_ms Cache latency in milliseconds';
            $metrics[] = '# TYPE cache_latency_ms gauge';
            $metrics[] = 'cache_latency_ms ' . round($cacheLatency, 2);
        } catch (\Exception $e) {
            $metrics[] = 'cache_latency_ms -1';
        }

        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        $metrics[] = '# HELP php_memory_usage_bytes PHP memory usage in bytes';
        $metrics[] = '# TYPE php_memory_usage_bytes gauge';
        $metrics[] = 'php_memory_usage_bytes ' . $memoryUsage;

        $metrics[] = '# HELP php_memory_peak_bytes PHP peak memory usage in bytes';
        $metrics[] = '# TYPE php_memory_peak_bytes gauge';
        $metrics[] = 'php_memory_peak_bytes ' . $memoryPeak;

        return response(implode("\n", $metrics), 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8'
        ]);
    }
}