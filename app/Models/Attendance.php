<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'emp_name',
        'date',
        'time_in',
        'time_out',
        'status',
        'employee_id',
        'photo_in',
        'photo_out',
        'location_in',
        'location_out',
        'device_info',
        'overtime_hours',
        'break_time',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime:H:i:s',
        'time_out' => 'datetime:H:i:s',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    // Generate attendance ID
    public static function generateAttendanceId($employeeId)
    {
        $date = date('Ymd');
        $time = date('His');
        return $employeeId . '_' . $date . '_' . $time;
    }

    // Check if employee is currently checked in
    public static function isCheckedIn($employeeName)
    {
        return self::where('emp_name', $employeeName)
                   ->where('status', 0)
                   ->whereDate('date', today())
                   ->exists();
    }

    // Get today's attendance for an employee
    public static function getTodayAttendance($employeeName)
    {
        return self::where('emp_name', $employeeName)
                   ->whereDate('date', today())
                   ->first();
    }

    // Check if employee is on approved leave
    public function isEmployeeOnLeave($employeeId, $date = null)
    {
        $date = $date ?: today();

        return LeaveRequest::where('employee_id', $employeeId)
                          ->where('status', LeaveRequest::STATUS_APPROVED)
                          ->where('start_date', '<=', $date)
                          ->where('end_date', '>=', $date)
                          ->exists();
    }

    // Get attendance status including leave
    public static function getAttendanceStatus($employeeId, $date = null)
    {
        $date = $date ?: today();
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return 'unknown';
        }

        // Check if on approved leave
        $onLeave = LeaveRequest::where('employee_id', $employeeId)
                              ->where('status', LeaveRequest::STATUS_APPROVED)
                              ->where('start_date', '<=', $date)
                              ->where('end_date', '>=', $date)
                              ->first();

        if ($onLeave) {
            return 'on_leave';
        }

        // Check attendance record
        $attendance = self::where('employee_id', $employeeId)
                         ->whereDate('date', $date)
                         ->first();

        if (!$attendance) {
            return 'absent';
        }

        if ($attendance->status == 0) {
            return 'present'; // Checked in
        } else {
            return 'completed'; // Checked out
        }
    }
}
