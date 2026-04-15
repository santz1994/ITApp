<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only modify JSON responses
        if ($request->expectsJson() || $request->is('api/*')) {
            // Add API headers
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('X-API-Version', '1.0');
            $response->headers->set('X-Powered-By', 'Laravel IT Asset Management API');
            
            // Add CORS headers for API endpoints
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                $response->headers->set('Access-Control-Allow-Origin', '*');
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            }
        }

        return $response;
    }
}
