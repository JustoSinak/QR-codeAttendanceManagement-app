<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['admin.auth', 'permission:manage_system']);
    }

    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,hr_manager,supervisor',
            'is_active' => 'boolean',
            'force_password_change' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validate password strength
        if (!User::validatePassword($request->password)) {
            return back()->withErrors([
                'password' => 'Password must contain at least 8 characters including uppercase, lowercase, numbers, and special characters.'
            ])->withInput();
        }

        try {
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => $request->boolean('is_active', true),
                'force_password_change' => $request->boolean('force_password_change', true),
                'password_changed_at' => now(),
            ]);

            // Log the activity
            AuditLog::logActivity(
                'admin',
                auth('admin')->id(),
                'user_created',
                "Created new user: {$user->username} with role: {$user->role}",
                'user',
                $user->id,
                null,
                $user->toArray()
            );

            return redirect()->route('admin.users.index')
                           ->with('success', 'User created successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error creating user: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $recentActivities = AuditLog::where('user_type', 'user')
                                  ->where('user_id', $id)
                                  ->orderBy('created_at', 'desc')
                                  ->limit(20)
                                  ->get();

        return view('admin.users.show', compact('user', 'recentActivities'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'email' => 'required|email|max:100|unique:users,email,' . $id,
            'role' => 'required|in:admin,hr_manager,supervisor',
            'is_active' => 'boolean',
            'force_password_change' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $oldValues = $user->toArray();

            $user->update([
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->role,
                'is_active' => $request->boolean('is_active'),
                'force_password_change' => $request->boolean('force_password_change'),
            ]);

            // Log the activity
            AuditLog::logActivity(
                'admin',
                auth('admin')->id(),
                'user_updated',
                "Updated user: {$user->username}",
                'user',
                $user->id,
                $oldValues,
                $user->fresh()->toArray()
            );

            return redirect()->route('admin.users.index')
                           ->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating user: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Validate password strength
        if (!User::validatePassword($request->password)) {
            return back()->withErrors([
                'password' => 'Password must contain at least 8 characters including uppercase, lowercase, numbers, and special characters.'
            ]);
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
                'password_changed_at' => now(),
                'force_password_change' => true,
                'failed_login_attempts' => 0,
                'locked_until' => null,
            ]);

            // Log the activity
            AuditLog::logActivity(
                'admin',
                auth('admin')->id(),
                'password_reset',
                "Reset password for user: {$user->username}",
                'user',
                $user->id
            );

            return back()->with('success', 'Password reset successfully! User will be required to change password on next login.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error resetting password: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        $oldStatus = $user->is_active;
        $user->update(['is_active' => !$user->is_active]);

        $action = $user->is_active ? 'activated' : 'deactivated';

        // Log the activity
        AuditLog::logActivity(
            'admin',
            auth('admin')->id(),
            'user_status_changed',
            "User {$user->username} {$action}",
            'user',
            $user->id,
            ['is_active' => $oldStatus],
            ['is_active' => $user->is_active]
        );

        return back()->with('success', "User {$action} successfully!");
    }

    /**
     * Unlock user account
     */
    public function unlock($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);

        // Log the activity
        AuditLog::logActivity(
            'admin',
            auth('admin')->id(),
            'user_unlocked',
            "Unlocked user account: {$user->username}",
            'user',
            $user->id
        );

        return back()->with('success', 'User account unlocked successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deletion of the last admin
        if ($user->isAdmin() && User::where('role', User::ROLE_ADMIN)->count() <= 1) {
            return back()->with('error', 'Cannot delete the last admin user.');
        }

        // Log the activity before deletion
        AuditLog::logActivity(
            'admin',
            auth('admin')->id(),
            'user_deleted',
            "Deleted user: {$user->username}",
            'user',
            $user->id,
            $user->toArray(),
            null
        );

        $user->delete();

        return redirect()->route('admin.users.index')
                       ->with('success', 'User deleted successfully!');
    }

    /**
     * Show user activity log
     */
    public function activityLog($id)
    {
        $user = User::findOrFail($id);
        $activities = AuditLog::where('user_type', 'user')
                            ->where('user_id', $id)
                            ->orderBy('created_at', 'desc')
                            ->paginate(20);

        return view('admin.users.activity-log', compact('user', 'activities'));
    }
}
