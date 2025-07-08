<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;

class SecurityMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $type = 'general'): Response
    {
        // Check rate limiting based on type
        switch ($type) {
            case 'login':
                return $this->handleLoginRateLimit($request, $next);
            case 'qr_scan':
                return $this->handleQRScanRateLimit($request, $next);
            case 'api':
                return $this->handleAPIRateLimit($request, $next);
            default:
                return $this->handleGeneralSecurity($request, $next);
        }
    }

    /**
     * Handle login rate limiting
     */
    private function handleLoginRateLimit(Request $request, Closure $next): Response
    {
        $key = 'login_attempts:' . $request->ip();
        $maxAttempts = config('security.rate_limiting.login_attempts.max_attempts', 5);
        $decayMinutes = config('security.rate_limiting.login_attempts.decay_minutes', 15);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            // Log suspicious activity
            AuditLog::logActivity(
                'system',
                'rate_limiter',
                'login_rate_limit_exceeded',
                "Too many login attempts from IP: {$request->ip()}",
                null,
                null,
                null,
                null,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'error' => 'Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.'
            ], 429);
        }

        $response = $next($request);

        // If login failed, increment attempts
        if ($request->isMethod('POST') && $response->getStatusCode() !== 200) {
            RateLimiter::hit($key, $decayMinutes * 60);
        } else {
            // Clear attempts on successful login
            RateLimiter::clear($key);
        }

        return $response;
    }

    /**
     * Handle QR scan rate limiting
     */
    private function handleQRScanRateLimit(Request $request, Closure $next): Response
    {
        $key = 'qr_scan:' . $request->ip();
        $maxAttempts = config('security.rate_limiting.qr_scans.per_minute', 10);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            // Log suspicious activity
            AuditLog::logActivity(
                'system',
                'rate_limiter',
                'qr_scan_rate_limit_exceeded',
                "Too many QR scan attempts from IP: {$request->ip()}",
                null,
                null,
                null,
                null,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'error' => 'Too many QR scan attempts. Please wait before trying again.'
            ], 429);
        }

        RateLimiter::hit($key, 60); // 1 minute window

        return $next($request);
    }

    /**
     * Handle API rate limiting
     */
    private function handleAPIRateLimit(Request $request, Closure $next): Response
    {
        $key = 'api_calls:' . $request->ip();
        $maxAttempts = config('security.rate_limiting.api_calls.per_minute', 60);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'error' => 'API rate limit exceeded. Please try again later.'
            ], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }

    /**
     * Handle general security checks
     */
    private function handleGeneralSecurity(Request $request, Closure $next): Response
    {
        // Check for suspicious patterns
        $this->checkSuspiciousActivity($request);

        // Check IP whitelist if enabled
        if (config('security.access_control.ip_whitelist_enabled')) {
            $this->checkIPWhitelist($request);
        }

        // Add security headers
        $response = $next($request);
        
        return $this->addSecurityHeaders($response);
    }

    /**
     * Check for suspicious activity patterns
     */
    private function checkSuspiciousActivity(Request $request): void
    {
        $suspiciousPatterns = [
            'sql injection' => ['union', 'select', 'drop', 'delete', 'insert', 'update'],
            'xss attempt' => ['<script', 'javascript:', 'onerror=', 'onload='],
            'path traversal' => ['../', '..\\', '/etc/passwd', '/windows/system32'],
        ];

        $requestData = json_encode($request->all());
        $userAgent = $request->userAgent();
        $uri = $request->getRequestUri();

        foreach ($suspiciousPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($requestData . $userAgent . $uri, $pattern) !== false) {
                    // Log suspicious activity
                    AuditLog::logActivity(
                        'system',
                        'security_monitor',
                        'suspicious_activity_detected',
                        "Suspicious activity detected: {$type} from IP: {$request->ip()}",
                        null,
                        null,
                        null,
                        ['pattern' => $pattern, 'type' => $type],
                        $request->ip(),
                        $request->userAgent()
                    );
                    break 2;
                }
            }
        }
    }

    /**
     * Check IP whitelist
     */
    private function checkIPWhitelist(Request $request): void
    {
        $whitelist = explode(',', config('security.access_control.ip_whitelist', ''));
        $clientIP = $request->ip();

        if (!empty($whitelist) && !in_array($clientIP, $whitelist)) {
            // Log unauthorized access attempt
            AuditLog::logActivity(
                'system',
                'security_monitor',
                'unauthorized_ip_access',
                "Access attempt from non-whitelisted IP: {$clientIP}",
                null,
                null,
                null,
                null,
                $request->ip(),
                $request->userAgent()
            );

            abort(403, 'Access denied from this IP address.');
        }
    }

    /**
     * Add security headers to response
     */
    private function addSecurityHeaders(Response $response): Response
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.bunny.net; font-src 'self' https://fonts.bunny.net; img-src 'self' data: https:; connect-src 'self';");
        
        return $response;
    }
}
