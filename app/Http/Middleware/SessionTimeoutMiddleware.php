<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $timeoutMinutes = config('security.session_config.timeout_minutes', 30);
            $lastActivity = Session::get('last_activity', time());
            $currentTime = time();
            
            // Check if session has timed out
            if (($currentTime - $lastActivity) > ($timeoutMinutes * 60)) {
                $user = Auth::user();
                
                // Log session timeout
                AuditLog::logActivity(
                    'user',
                    $user->id,
                    'session_timeout',
                    "Session timed out for user: {$user->username}",
                    null,
                    null,
                    null,
                    null,
                    $request->ip(),
                    $request->userAgent()
                );
                
                // Logout user
                Auth::logout();
                Session::invalidate();
                Session::regenerateToken();
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'Session has expired due to inactivity. Please login again.',
                        'redirect' => route('auth.login')
                    ], 401);
                }
                
                return redirect()->route('auth.login')
                                ->with('warning', 'Your session has expired due to inactivity. Please login again.');
            }
            
            // Update last activity time
            Session::put('last_activity', $currentTime);
            
            // Check for concurrent sessions
            $this->checkConcurrentSessions($request);
        }
        
        return $next($request);
    }
    
    /**
     * Check for concurrent sessions
     */
    private function checkConcurrentSessions(Request $request): void
    {
        $maxSessions = config('security.access_control.max_concurrent_sessions', 3);
        $user = Auth::user();
        
        if (!$user) {
            return;
        }
        
        $sessionKey = 'user_sessions:' . $user->id;
        $currentSessionId = Session::getId();
        $sessions = Session::get($sessionKey, []);
        
        // Add current session if not exists
        if (!in_array($currentSessionId, $sessions)) {
            $sessions[] = $currentSessionId;
        }
        
        // Remove expired sessions (this is a simplified check)
        $sessions = array_slice($sessions, -$maxSessions);
        
        // If too many sessions, log and potentially terminate oldest
        if (count($sessions) > $maxSessions) {
            AuditLog::logActivity(
                'user',
                $user->id,
                'concurrent_session_limit_exceeded',
                "User {$user->username} exceeded concurrent session limit",
                null,
                null,
                null,
                ['session_count' => count($sessions), 'max_allowed' => $maxSessions],
                $request->ip(),
                $request->userAgent()
            );
            
            // Keep only the most recent sessions
            $sessions = array_slice($sessions, -$maxSessions);
        }
        
        Session::put($sessionKey, $sessions);
    }
}
