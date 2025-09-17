<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiLogging
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $requestId = uniqid();

        // Log incoming request
        Log::info('API Request', [
            'request_id' => $requestId,
            'method' => $request->method(),
            'uri' => $request->getRequestUri(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
            'timestamp' => now()->toISOString(),
        ]);

        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // Log response
        Log::info('API Response', [
            'request_id' => $requestId,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'response_size' => strlen($response->getContent()),
            'timestamp' => now()->toISOString(),
        ]);

        // Add request ID to response headers
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}