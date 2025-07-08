<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'emp_name',
        'gender',
        'emp_mail',
        'emp_number',
        'department',
        'qr_code_path',
        'profile_photo',
        'position',
        'hire_date',
        'employee_status',
        'address',
        'emergency_contact',
        'emergency_phone',
        'salary',
        'work_schedule',
    ];

    // Relationships
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'id');
    }

    public function credentials()
    {
        return $this->hasOne(EmployeeCredential::class, 'id', 'id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'id');
    }

    // Generate employee ID
    public static function generateEmployeeId($name, $department)
    {
        $year = date('Y');
        $deptPrefix = strtoupper(substr($department, 0, 3));
        $random = rand(100, 999);
        $nameInitial = strtoupper(substr($name, 0, 1));
        return $year . $deptPrefix . $random . $nameInitial;
    }

    // Get QR code URL
    public function getQrCodeUrlAttribute()
    {
        return $this->qr_code_path ? asset('storage/' . $this->qr_code_path) : null;
    }
}
