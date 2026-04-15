<?php

namespace App\Http\Middleware;

use App\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Routes that should be excluded from automatic audit logging.
     *
     * @var array
     */
    protected $excludedRoutes = [
        'audit-logs*',
        'api/audit-logs*',
        '_debugbar*',
        'telescope*',
    ];

    /**
     * Actions that should be logged (HTTP methods).
     *
     * @var array
     */
    protected $loggableActions = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Execute the request
        $response = $next($request);

        // Only log if user is authenticated
        if (!auth()->check()) {
            return $response;
        }

        // Only log specific HTTP methods
        if (!in_array($request->method(), $this->loggableActions)) {
            return $response;
        }

        // Skip excluded routes
        if ($this->shouldExclude($request)) {
            return $response;
        }

        // Only log successful responses (2xx status codes)
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return $response;
        }

        try {
            $this->logRequest($request, $response);
        } catch (\Exception $e) {
            // Don't fail the request if audit logging fails
            Log::error('Audit log middleware error: ' . $e->getMessage());
        }

        return $response;
    }

    /**
     * Check if the request should be excluded from logging.
     *
     * @param Request $request
     * @return bool
     */
    protected function shouldExclude(Request $request): bool
    {
        foreach ($this->excludedRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the request.
     *
     * @param Request $request
     * @param Response $response
     * @return void
     */
    protected function logRequest(Request $request, Response $response): void
    {
        $action = $this->determineAction($request);
        $description = $this->generateDescription($request, $action);

        // Get request data excluding sensitive fields
        $requestData = $this->sanitizeRequestData($request->all());

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => null, // Will be set by model observers for specific models
            'model_id' => null,
            'old_values' => null,
            'new_values' => !empty($requestData) ? json_encode($requestData) : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'description' => $description,
            'event_type' => 'system',
        ]);
    }

    /**
     * Determine the action from the request.
     *
     * @param Request $request
     * @return string
     */
    protected function determineAction(Request $request): string
    {
        $method = strtolower($request->method());
        
        $actionMap = [
            'post' => 'create',
            'put' => 'update',
            'patch' => 'update',
            'delete' => 'delete',
        ];

        return $actionMap[$method] ?? $method;
    }

    /**
     * Generate a description for the request.
     *
     * @param Request $request
     * @param string $action
     * @return string
     */
    protected function generateDescription(Request $request, string $action): string
    {
        $routeName = $request->route() ? $request->route()->getName() : null;
        $path = $request->path();

        if ($routeName) {
            return ucfirst($action) . " action performed on route: {$routeName}";
        }

        return ucfirst($action) . " action performed on: {$path}";
    }

    /**
     * Sanitize request data by removing sensitive fields.
     *
     * @param array $data
     * @return array
     */
    protected function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'token',
            'api_token',
            'secret',
            'api_secret',
            'card_number',
            'cvv',
            'ssn',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }
}
