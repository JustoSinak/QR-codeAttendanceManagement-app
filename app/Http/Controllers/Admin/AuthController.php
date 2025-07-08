<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('id', $request->admin_id)->first();

        if ($admin && Hash::check($request->password, $admin->adminpassword)) {
            Auth::guard('admin')->login($admin);

            // Log successful login
            \App\Models\AuditLog::logActivity(
                'admin',
                $admin->id,
                'login',
                "Admin {$admin->adminname} logged in successfully"
            );

            return redirect()->route('admin.dashboard')->with('success', 'Welcome back, ' . $admin->adminname . '!');
        }

        // Log failed login attempt
        \App\Models\AuditLog::logActivity(
            'admin',
            $request->admin_id,
            'login_failed',
            "Failed login attempt for admin ID: {$request->admin_id}"
        );

        return back()->withErrors([
            'admin_id' => 'Invalid admin ID or password.',
        ])->withInput($request->only('admin_id'));
    }

    public function logout(Request $request)
    {
        $admin = auth('admin')->user();

        // Log logout
        if ($admin) {
            \App\Models\AuditLog::logActivity(
                'admin',
                $admin->id,
                'logout',
                "Admin {$admin->adminname} logged out"
            );
        }

        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been logged out successfully.');
    }
}
