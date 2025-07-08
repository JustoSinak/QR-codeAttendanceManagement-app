<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'approval_notes',
        'approved_at',
        'attachments',
        'is_emergency',
        'deduction_amount',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'attachments' => 'array',
        'is_emergency' => 'boolean',
        'deduction_amount' => 'decimal:2',
    ];

    // Leave type constants
    const TYPE_SICK = 'sick';
    const TYPE_VACATION = 'vacation';
    const TYPE_PERSONAL = 'personal';
    const TYPE_EMERGENCY = 'emergency';

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeByEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
    }

    // Mutators and Accessors
    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = Carbon::parse($value)->format('Y-m-d');
        $this->calculateTotalDays();
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = Carbon::parse($value)->format('Y-m-d');
        $this->calculateTotalDays();
    }

    // Helper methods
    public function calculateTotalDays()
    {
        if ($this->start_date && $this->end_date) {
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);
            $this->attributes['total_days'] = $start->diffInDays($end) + 1;
        }
    }

    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected()
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canBeApproved()
    {
        return $this->isPending() && $this->start_date->isFuture();
    }

    public function approve($approverId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $approverId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        // Log the approval
        AuditLog::logActivity(
            'user',
            $approverId,
            'leave_approved',
            "Approved leave request for employee {$this->employee->emp_name}",
            'leave_request',
            $this->id
        );
    }

    public function reject($approverId, $notes = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $approverId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        // Log the rejection
        AuditLog::logActivity(
            'user',
            $approverId,
            'leave_rejected',
            "Rejected leave request for employee {$this->employee->emp_name}",
            'leave_request',
            $this->id
        );
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary'
        };
    }

    public function getLeaveTypeDisplayAttribute()
    {
        return match($this->leave_type) {
            self::TYPE_SICK => 'Sick Leave',
            self::TYPE_VACATION => 'Vacation Leave',
            self::TYPE_PERSONAL => 'Personal Leave',
            self::TYPE_EMERGENCY => 'Emergency Leave',
            default => ucfirst($this->leave_type)
        };
    }

    // Check for overlapping leave requests
    public function hasOverlappingLeave()
    {
        return self::where('employee_id', $this->employee_id)
                   ->where('id', '!=', $this->id)
                   ->where('status', self::STATUS_APPROVED)
                   ->where(function($query) {
                       $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                             ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                             ->orWhere(function($q) {
                                 $q->where('start_date', '<=', $this->start_date)
                                   ->where('end_date', '>=', $this->end_date);
                             });
                   })
                   ->exists();
    }

    // Calculate leave balance impact
    public function calculateLeaveBalance()
    {
        // This would integrate with a leave balance system
        // For now, return basic calculation
        return [
            'days_requested' => $this->total_days,
            'leave_type' => $this->leave_type,
            'deduction_amount' => $this->deduction_amount,
        ];
    }
}
