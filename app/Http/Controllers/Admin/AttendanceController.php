<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\QRCodeService;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->middleware('admin.auth');
        $this->qrCodeService = $qrCodeService;
    }

    public function index()
    {
        $attendances = Attendance::with('employee')
            ->orderBy('date', 'desc')
            ->orderBy('time_in', 'desc')
            ->paginate(20);

        return view('admin.attendance.index', compact('attendances'));
    }

    public function scanner()
    {
        return view('admin.attendance.scanner');
    }

    public function processQRScan(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'photo' => 'nullable|string', // Base64 encoded photo
            'location' => 'nullable|array',
            'device_info' => 'nullable|array'
        ]);

        $qrData = $request->text;

        // Decrypt and validate QR code data
        $qrResult = $this->qrCodeService->decryptQRCodeData($qrData);

        if (!$qrResult['success']) {
            // Log failed attempt
            \App\Models\AuditLog::logActivity(
                'admin',
                auth('admin')->id(),
                'attendance_scan_failed',
                "Failed QR scan attempt: {$qrResult['error']}"
            );

            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code: ' . $qrResult['error']
            ]);
        }

        $employee = $qrResult['employee'];
        $employeeName = $employee->emp_name;

        // Handle photo upload if provided
        $photoPath = null;
        if ($request->photo) {
            $photoPath = $this->saveBase64Photo($request->photo, $employee->id);
        }

        // Check if employee already has attendance today
        $todayAttendance = Attendance::getTodayAttendance($employeeName);

        if ($todayAttendance) {
            if ($todayAttendance->status == 0) {
                // Check out
                $checkOutTime = now();
                $checkInTime = \Carbon\Carbon::parse($todayAttendance->date . ' ' . $todayAttendance->time_in);
                $workHours = $checkOutTime->diffInHours($checkInTime);
                $overtimeHours = max(0, $workHours - 8); // Assuming 8-hour work day

                $todayAttendance->update([
                    'time_out' => $checkOutTime->format('H:i:s'),
                    'status' => 1,
                    'photo_out' => $photoPath,
                    'location_out' => $request->location ? json_encode($request->location) : null,
                    'overtime_hours' => $overtimeHours,
                    'device_info' => $request->device_info ? json_encode($request->device_info) : null
                ]);

                // Log checkout
                \App\Models\AuditLog::logActivity(
                    'employee',
                    $employee->id,
                    'attendance_checkout',
                    "Employee {$employeeName} checked out at {$checkOutTime->format('H:i:s')}",
                    'attendance',
                    $todayAttendance->id
                );

                return response()->json([
                    'success' => true,
                    'message' => "Goodbye {$employeeName}! You have been checked out. Work hours: {$workHours}h" . ($overtimeHours > 0 ? ", Overtime: {$overtimeHours}h" : ""),
                    'action' => 'checkout',
                    'work_hours' => $workHours,
                    'overtime_hours' => $overtimeHours
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "{$employeeName} has already completed attendance for today."
                ]);
            }
        } else {
            // Check in
            $attendanceId = Attendance::generateAttendanceId($employee->id);

            $attendance = Attendance::create([
                'id' => $attendanceId,
                'emp_name' => $employeeName,
                'date' => today(),
                'time_in' => now()->format('H:i:s'),
                'status' => 0,
                'employee_id' => $employee->id,
                'photo_in' => $photoPath,
                'location_in' => $request->location ? json_encode($request->location) : null,
                'device_info' => $request->device_info ? json_encode($request->device_info) : null
            ]);

            // Log checkin
            \App\Models\AuditLog::logActivity(
                'employee',
                $employee->id,
                'attendance_checkin',
                "Employee {$employeeName} checked in at " . now()->format('H:i:s'),
                'attendance',
                $attendance->id
            );

            return response()->json([
                'success' => true,
                'message' => "Hello {$employeeName}! Your attendance has been recorded. Thank you!",
                'action' => 'checkin'
            ]);
        }
    }

    private function saveBase64Photo($base64Photo, $employeeId)
    {
        try {
            // Remove data:image/jpeg;base64, prefix if present
            $photoData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Photo);
            $photoData = base64_decode($photoData);

            // Generate unique filename
            $filename = 'attendance_' . $employeeId . '_' . time() . '.jpg';
            $path = 'attendance_photos/' . $filename;

            // Save to storage
            \Storage::disk('public')->put($path, $photoData);

            return $path;
        } catch (\Exception $e) {
            \Log::error('Failed to save attendance photo: ' . $e->getMessage());
            return null;
        }
    }

    public function report()
    {
        $attendances = Attendance::with('employee')
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.attendance.report', compact('attendances'));
    }

    public function dailyReport(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        $attendances = Attendance::with('employee')
            ->whereDate('date', $date)
            ->orderBy('time_in')
            ->get();

        return view('admin.attendance.daily', compact('attendances', 'date'));
    }
}
