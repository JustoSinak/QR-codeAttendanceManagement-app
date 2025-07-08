<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Static method to log activities
    public static function logActivity($userType, $userId, $action, $description = null, $entityType = null, $entityId = null, $oldValues = null, $newValues = null, $ipAddress = null, $userAgent = null)
    {
        return self::create([
            'user_type' => $userType,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress ?: request()->ip(),
            'user_agent' => $userAgent ?: request()->userAgent(),
            'description' => $description,
        ]);
    }

    // Get user name for display
    public function getUserNameAttribute()
    {
        if ($this->user_type === 'admin') {
            $admin = Admin::find($this->user_id);
            return $admin ? $admin->adminname : 'Unknown Admin';
        } elseif ($this->user_type === 'employee') {
            $employee = Employee::find($this->user_id);
            return $employee ? $employee->emp_name : 'Unknown Employee';
        } elseif ($this->user_type === 'user') {
            $user = User::find($this->user_id);
            return $user ? $user->username : 'Unknown User';
        }
        return 'Unknown User';
    }
}
