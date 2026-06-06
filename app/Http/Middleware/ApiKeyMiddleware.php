<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        
        if ($apiKey !== env('ESP32_API_KEY')) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized - Invalid API Key'
            ], 401);
        }
        
        return $next($request);
    }
}