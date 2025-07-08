<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    /**
     * Display a listing of leave requests
     */
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'approver']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by leave type
        if ($request->has('leave_type') && $request->leave_type !== '') {
            $query->where('leave_type', $request->leave_type);
        }

        // Filter by employee
        if ($request->has('employee_id') && $request->employee_id !== '') {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date !== '') {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date !== '') {
            $query->where('end_date', '<=', $request->end_date);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);
        $employees = Employee::where('employee_status', 'active')->get();

        return view('admin.leave.index', compact('leaveRequests', 'employees'));
    }

    /**
     * Show the form for creating a new leave request
     */
    public function create()
    {
        $employees = Employee::where('employee_status', 'active')->get();
        return view('admin.leave.create', compact('employees'));
    }

    /**
     * Store a newly created leave request
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:sick,vacation,personal,emergency',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'is_emergency' => 'boolean',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Calculate total days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            // Handle file attachments
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('leave_attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType()
                    ];
                }
            }

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $request->employee_id,
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'is_emergency' => $request->boolean('is_emergency'),
                'attachments' => $attachments,
                'status' => LeaveRequest::STATUS_PENDING,
            ]);

            // Log the activity
            AuditLog::logActivity(
                'admin',
                auth('admin')->id(),
                'leave_request_created',
                "Created leave request for employee {$leaveRequest->employee->emp_name}",
                'leave_request',
                $leaveRequest->id,
                null,
                $leaveRequest->toArray()
            );

            return redirect()->route('admin.leave.index')
                           ->with('success', 'Leave request created successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error creating leave request: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Display the specified leave request
     */
    public function show($id)
    {
        $leaveRequest = LeaveRequest::with(['employee', 'approver'])->findOrFail($id);
        return view('admin.leave.show', compact('leaveRequest'));
    }

    /**
     * Show the form for editing the specified leave request
     */
    public function edit($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        
        if (!$leaveRequest->isPending()) {
            return redirect()->route('admin.leave.index')
                           ->with('error', 'Only pending leave requests can be edited.');
        }

        $employees = Employee::where('employee_status', 'active')->get();
        return view('admin.leave.edit', compact('leaveRequest', 'employees'));
    }

    /**
     * Update the specified leave request
     */
    public function update(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if (!$leaveRequest->isPending()) {
            return redirect()->route('admin.leave.index')
                           ->with('error', 'Only pending leave requests can be updated.');
        }

        $validator = Validator::make($request->all(), [
            'leave_type' => 'required|in:sick,vacation,personal,emergency',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'is_emergency' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $oldValues = $leaveRequest->toArray();

            // Calculate total days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            $leaveRequest->update([
                'leave_type' => $request->leave_type,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'is_emergency' => $request->boolean('is_emergency'),
            ]);

            // Log the activity
            AuditLog::logActivity(
                'admin',
                auth('admin')->id(),
                'leave_request_updated',
                "Updated leave request for employee {$leaveRequest->employee->emp_name}",
                'leave_request',
                $leaveRequest->id,
                $oldValues,
                $leaveRequest->fresh()->toArray()
            );

            return redirect()->route('admin.leave.index')
                           ->with('success', 'Leave request updated successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error updating leave request: ' . $e->getMessage())
                        ->withInput();
        }
    }

    /**
     * Approve a leave request
     */
    public function approve(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if (!$leaveRequest->canBeApproved()) {
            return back()->with('error', 'This leave request cannot be approved.');
        }

        $leaveRequest->approve(auth('admin')->id(), $request->approval_notes);

        return back()->with('success', 'Leave request approved successfully!');
    }

    /**
     * Reject a leave request
     */
    public function reject(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Only pending leave requests can be rejected.');
        }

        $leaveRequest->reject(auth('admin')->id(), $request->approval_notes);

        return back()->with('success', 'Leave request rejected.');
    }

    /**
     * Remove the specified leave request
     */
    public function destroy($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if (!$leaveRequest->isPending()) {
            return back()->with('error', 'Only pending leave requests can be deleted.');
        }

        // Log the activity before deletion
        AuditLog::logActivity(
            'admin',
            auth('admin')->id(),
            'leave_request_deleted',
            "Deleted leave request for employee {$leaveRequest->employee->emp_name}",
            'leave_request',
            $leaveRequest->id,
            $leaveRequest->toArray(),
            null
        );

        $leaveRequest->delete();

        return redirect()->route('admin.leave.index')
                       ->with('success', 'Leave request deleted successfully!');
    }

    /**
     * Get leave statistics
     */
    public function statistics()
    {
        $stats = [
            'total_requests' => LeaveRequest::count(),
            'pending_requests' => LeaveRequest::pending()->count(),
            'approved_requests' => LeaveRequest::approved()->count(),
            'rejected_requests' => LeaveRequest::rejected()->count(),
            'emergency_requests' => LeaveRequest::where('is_emergency', true)->count(),
        ];

        $leaveTypeStats = LeaveRequest::selectRaw('leave_type, count(*) as count')
                                    ->groupBy('leave_type')
                                    ->get()
                                    ->pluck('count', 'leave_type');

        return view('admin.leave.statistics', compact('stats', 'leaveTypeStats'));
    }
}
