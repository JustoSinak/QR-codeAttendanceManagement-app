<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
        'password_changed_at',
        'force_password_change',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'password_changed_at' => 'datetime',
        'force_password_change' => 'boolean',
    ];

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_HR_MANAGER = 'hr_manager';
    const ROLE_SUPERVISOR = 'supervisor';

    // Role permissions
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isHRManager()
    {
        return $this->role === self::ROLE_HR_MANAGER;
    }

    public function isSupervisor()
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    public function canManageEmployees()
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_HR_MANAGER]);
    }

    public function canApproveLeave()
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_HR_MANAGER, self::ROLE_SUPERVISOR]);
    }

    public function canViewReports()
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_HR_MANAGER, self::ROLE_SUPERVISOR]);
    }

    public function canManageSystem()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    // Account security methods
    public function isLocked()
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function incrementFailedLogins()
    {
        $this->increment('failed_login_attempts');

        if ($this->failed_login_attempts >= 5) {
            $this->update(['locked_until' => now()->addMinutes(5)]);
        }
    }

    public function resetFailedLogins()
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip()
        ]);
    }



    // Relationships
    public function approvedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    // Password validation using policy service
    public static function validatePassword($password, $user = null)
    {
        $passwordService = app(\App\Services\PasswordPolicyService::class);
        $validation = $passwordService->validatePassword($password, $user);

        return $validation['valid'];
    }

    // Get password validation errors
    public static function getPasswordValidationErrors($password, $user = null)
    {
        $passwordService = app(\App\Services\PasswordPolicyService::class);
        $validation = $passwordService->validatePassword($password, $user);

        return $validation['errors'];
    }

    // Check if password change is needed
    public function needsPasswordChange()
    {
        $passwordService = app(\App\Services\PasswordPolicyService::class);
        return $passwordService->needsPasswordChange($this);
    }
}
