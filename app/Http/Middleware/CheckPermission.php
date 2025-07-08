<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Check specific permissions based on the permission parameter
        $hasPermission = match($permission) {
            'manage_employees' => $user->canManageEmployees(),
            'approve_leave' => $user->canApproveLeave(),
            'view_reports' => $user->canViewReports(),
            'manage_system' => $user->canManageSystem(),
            'admin_only' => $user->isAdmin(),
            'hr_manager' => $user->isHRManager() || $user->isAdmin(),
            'supervisor' => $user->isSupervisor() || $user->isHRManager() || $user->isAdmin(),
            default => false
        };

        if (!$hasPermission) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Insufficient permissions'], 403);
            }
            
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
