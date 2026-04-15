<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $lastActivity = Session::get('last_activity', time());
            $timeoutDuration = config('session.lifetime') * 60; // Convert minutes to seconds
            
            // Check if session has timed out
            if (time() - $lastActivity > $timeoutDuration) {
                Auth::logout();
                Session::flush();
                Session::regenerate();
                
                // Log timeout for security audit
                logger()->info('Session timeout occurred', [
                    'user_id' => Auth::id(),
                    'last_activity' => date('Y-m-d H:i:s', $lastActivity),
                    'timeout_duration' => $timeoutDuration,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Session has expired due to inactivity. Please log in again.',
                        'redirect' => route('login')
                    ], 401);
                }
                
                return redirect()->route('login')
                    ->with('warning', 'Your session has expired due to inactivity. Please log in again.');
            }
            
            // Update last activity timestamp
            Session::put('last_activity', time());
        }
        
        return $next($request);
    }
}
