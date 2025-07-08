<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\AuditLog;

class UserAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.user-login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::where('username', $request->username)->first();

        // Check if user exists
        if (!$user) {
            AuditLog::logActivity(
                'user',
                $request->username,
                'login_failed',
                "Failed login attempt for unknown username: {$request->username}",
                null,
                null,
                null,
                null,
                $request->ip(),
                $request->userAgent()
            );

            return back()->withErrors([
                'username' => 'Invalid username or password.',
            ])->withInput($request->only('username'));
        }

        // Check if account is active
        if (!$user->is_active) {
            AuditLog::logActivity(
                'user',
                $user->id,
                'login_failed',
                "Login attempt on inactive account: {$user->username}",
                null,
                null,
                null,
                null,
                $request->ip(),
                $request->userAgent()
            );

            return back()->withErrors([
                'username' => 'Your account has been deactivated. Please contact administrator.',
            ])->withInput($request->only('username'));
        }

        // Check if account is locked
        if ($user->isLocked()) {
            AuditLog::logActivity(
                'user',
                $user->id,
                'login_failed',
                "Login attempt on locked account: {$user->username}",
                null,
                null,
                null,
                null,
                $request->ip(),
                $request->userAgent()
            );

            return back()->withErrors([
                'username' => 'Your account is temporarily locked due to multiple failed login attempts. Please try again later.',
            ])->withInput($request->only('username'));
        }

        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            $user->incrementFailedLogins();

            AuditLog::logActivity(
                'user',
                $user->id,
                'login_failed',
                "Failed login attempt for user: {$user->username}",
                null,
                null,
                null,
                null,
                $request->ip(),
                $request->userAgent()
            );

            return back()->withErrors([
                'username' => 'Invalid username or password.',
            ])->withInput($request->only('username'));
        }

        // Successful login
        Auth::login($user);
        $user->resetFailedLogins();

        // Log successful login
        AuditLog::logActivity(
            'user',
            $user->id,
            'login',
            "User {$user->username} logged in successfully",
            null,
            null,
            null,
            null,
            $request->ip(),
            $request->userAgent()
        );

        // Check if password change is required
        if ($user->needsPasswordChange()) {
            return redirect()->route('password.change')
                           ->with('warning', 'You must change your password before continuing.');
        }

        // Redirect based on role
        $redirectRoute = match($user->role) {
            User::ROLE_ADMIN => 'admin.dashboard',
            User::ROLE_HR_MANAGER => 'admin.dashboard',
            User::ROLE_SUPERVISOR => 'admin.dashboard',
            default => 'dashboard'
        };

        return redirect()->route($redirectRoute)
                        ->with('success', 'Welcome back, ' . $user->username . '!');
    }

    public function logout(Request $request)
    {
        $user = auth()->user();

        // Log logout
        if ($user) {
            AuditLog::logActivity(
                'user',
                $user->id,
                'logout',
                "User {$user->username} logged out",
                null,
                null,
                null,
                null,
                $request->ip(),
                $request->userAgent()
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Validate new password strength
        if (!User::validatePassword($request->password)) {
            return back()->withErrors([
                'password' => 'Password must contain at least 8 characters including uppercase, lowercase, numbers, and special characters.'
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
            'force_password_change' => false,
        ]);

        // Log password change
        AuditLog::logActivity(
            'user',
            $user->id,
            'password_changed',
            "User {$user->username} changed password",
            null,
            null,
            null,
            null,
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->route('admin.dashboard')
                        ->with('success', 'Password changed successfully!');
    }

    public function showProfile()
    {
        return view('auth.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $oldValues = $user->toArray();

        $user->update([
            'email' => $request->email,
        ]);

        // Log profile update
        AuditLog::logActivity(
            'user',
            $user->id,
            'profile_updated',
            "User {$user->username} updated profile",
            'user',
            $user->id,
            $oldValues,
            $user->fresh()->toArray(),
            $request->ip(),
            $request->userAgent()
        );

        return back()->with('success', 'Profile updated successfully!');
    }
}
