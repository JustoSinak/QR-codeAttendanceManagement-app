<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index()
    {
        return view('admin.reports.index');
    }

    public function dailyReport(Request $request)
    {
        $date = $request->get('date', today()->format('Y-m-d'));

        $attendances = Attendance::with('employee')
            ->whereDate('date', $date)
            ->orderBy('time_in')
            ->get();

        $stats = [
            'total_employees' => Employee::where('employee_status', 'active')->count(),
            'present_today' => $attendances->count(),
            'on_time' => $attendances->where('time_in', '<=', '09:00:00')->count(),
            'late_arrivals' => $attendances->where('time_in', '>', '09:00:00')->count(),
            'total_overtime' => $attendances->sum('overtime_hours'),
            'checked_out' => $attendances->where('status', 1)->count(),
            'still_in' => $attendances->where('status', 0)->count(),
        ];

        return view('admin.reports.daily', compact('attendances', 'date', 'stats'));
    }

    public function weeklyReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfWeek()->format('Y-m-d'));

        $attendances = Attendance::with('employee')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->orderBy('time_in')
            ->get();

        $employeeStats = $attendances->groupBy('employee_id')->map(function ($employeeAttendances) {
            $employee = $employeeAttendances->first()->employee;
            return [
                'employee' => $employee,
                'total_days' => $employeeAttendances->count(),
                'total_hours' => $employeeAttendances->sum(function ($att) {
                    if ($att->time_out) {
                        $timeIn = Carbon::parse($att->date . ' ' . $att->time_in);
                        $timeOut = Carbon::parse($att->date . ' ' . $att->time_out);
                        return $timeOut->diffInHours($timeIn);
                    }
                    return 0;
                }),
                'total_overtime' => $employeeAttendances->sum('overtime_hours'),
                'late_days' => $employeeAttendances->where('time_in', '>', '09:00:00')->count(),
                'on_time_days' => $employeeAttendances->where('time_in', '<=', '09:00:00')->count(),
            ];
        });

        return view('admin.reports.weekly', compact('employeeStats', 'startDate', 'endDate'));
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = Carbon::parse($month . '-01')->endOfMonth();

        $attendances = Attendance::with('employee')
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $monthlyStats = [
            'total_working_days' => $startDate->diffInWeekdays($endDate) + 1,
            'total_attendances' => $attendances->count(),
            'total_overtime_hours' => $attendances->sum('overtime_hours'),
            'average_daily_attendance' => round($attendances->count() / $startDate->diffInDays($endDate), 2),
            'punctuality_rate' => $attendances->count() > 0 ?
                round(($attendances->where('time_in', '<=', '09:00:00')->count() / $attendances->count()) * 100, 2) : 0,
        ];

        $employeeMonthlyStats = $attendances->groupBy('employee_id')->map(function ($employeeAttendances) use ($startDate, $endDate) {
            $employee = $employeeAttendances->first()->employee;
            $workingDays = $startDate->diffInWeekdays($endDate) + 1;
            $attendedDays = $employeeAttendances->count();

            return [
                'employee' => $employee,
                'attended_days' => $attendedDays,
                'working_days' => $workingDays,
                'attendance_rate' => round(($attendedDays / $workingDays) * 100, 2),
                'total_hours' => $employeeAttendances->sum(function ($att) {
                    if ($att->time_out) {
                        $timeIn = Carbon::parse($att->date . ' ' . $att->time_in);
                        $timeOut = Carbon::parse($att->date . ' ' . $att->time_out);
                        return $timeOut->diffInHours($timeIn);
                    }
                    return 0;
                }),
                'total_overtime' => $employeeAttendances->sum('overtime_hours'),
                'late_days' => $employeeAttendances->where('time_in', '>', '09:00:00')->count(),
                'punctuality_rate' => $attendedDays > 0 ?
                    round(($employeeAttendances->where('time_in', '<=', '09:00:00')->count() / $attendedDays) * 100, 2) : 0,
            ];
        });

        return view('admin.reports.monthly', compact('employeeMonthlyStats', 'monthlyStats', 'month'));
    }

    public function overtimeReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $overtimeData = Attendance::with('employee')
            ->whereBetween('date', [$startDate, $endDate])
            ->where('overtime_hours', '>', 0)
            ->orderBy('overtime_hours', 'desc')
            ->get();

        $overtimeStats = [
            'total_overtime_hours' => $overtimeData->sum('overtime_hours'),
            'total_overtime_cost' => $overtimeData->sum(function ($att) {
                $hourlyRate = $att->employee->salary ? ($att->employee->salary / 160) : 0; // Assuming 160 hours/month
                return $att->overtime_hours * $hourlyRate * 1.5; // 1.5x overtime rate
            }),
            'employees_with_overtime' => $overtimeData->unique('employee_id')->count(),
            'average_overtime_per_employee' => $overtimeData->count() > 0 ?
                round($overtimeData->sum('overtime_hours') / $overtimeData->unique('employee_id')->count(), 2) : 0,
        ];

        return view('admin.reports.overtime', compact('overtimeData', 'overtimeStats', 'startDate', 'endDate'));
    }

    public function performanceAnalytics(Request $request)
    {
        $period = $request->get('period', 'month'); // week, month, quarter, year

        switch ($period) {
            case 'week':
                $startDate = now()->startOfWeek();
                $endDate = now()->endOfWeek();
                break;
            case 'quarter':
                $startDate = now()->startOfQuarter();
                $endDate = now()->endOfQuarter();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $endDate = now()->endOfYear();
                break;
            default: // month
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
        }

        $analytics = [
            'attendance_trends' => $this->getAttendanceTrends($startDate, $endDate),
            'punctuality_trends' => $this->getPunctualityTrends($startDate, $endDate),
            'department_performance' => $this->getDepartmentPerformance($startDate, $endDate),
            'top_performers' => $this->getTopPerformers($startDate, $endDate),
            'attendance_patterns' => $this->getAttendancePatterns($startDate, $endDate),
        ];

        return view('admin.reports.analytics', compact('analytics', 'period', 'startDate', 'endDate'));
    }

    private function getAttendanceTrends($startDate, $endDate)
    {
        return Attendance::selectRaw('DATE(date) as date, COUNT(*) as count')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getPunctualityTrends($startDate, $endDate)
    {
        return Attendance::selectRaw('DATE(date) as date,
                                     COUNT(*) as total,
                                     SUM(CASE WHEN time_in <= "09:00:00" THEN 1 ELSE 0 END) as on_time,
                                     SUM(CASE WHEN time_in > "09:00:00" THEN 1 ELSE 0 END) as late')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getDepartmentPerformance($startDate, $endDate)
    {
        return DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->select('employees.department',
                     DB::raw('COUNT(*) as total_attendances'),
                     DB::raw('AVG(CASE WHEN attendances.time_in <= "09:00:00" THEN 1 ELSE 0 END) * 100 as punctuality_rate'),
                     DB::raw('SUM(attendances.overtime_hours) as total_overtime'))
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->groupBy('employees.department')
            ->get();
    }

    private function getTopPerformers($startDate, $endDate)
    {
        return DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->select('employees.emp_name',
                     'employees.department',
                     DB::raw('COUNT(*) as attendance_days'),
                     DB::raw('AVG(CASE WHEN attendances.time_in <= "09:00:00" THEN 1 ELSE 0 END) * 100 as punctuality_rate'),
                     DB::raw('SUM(attendances.overtime_hours) as total_overtime'))
            ->whereBetween('attendances.date', [$startDate, $endDate])
            ->groupBy('employees.id', 'employees.emp_name', 'employees.department')
            ->orderByDesc('punctuality_rate')
            ->orderByDesc('attendance_days')
            ->limit(10)
            ->get();
    }

    private function getAttendancePatterns($startDate, $endDate)
    {
        return Attendance::selectRaw('DAYOFWEEK(date) as day_of_week,
                                     COUNT(*) as count,
                                     AVG(CASE WHEN time_in <= "09:00:00" THEN 1 ELSE 0 END) * 100 as punctuality_rate')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get();
    }

    public function exportReport(Request $request)
    {
        $type = $request->get('type', 'daily');
        $format = $request->get('format', 'pdf'); // pdf, excel, csv

        // Implementation for report export
        // This would generate and download the report in the specified format

        return response()->json([
            'success' => true,
            'message' => 'Report export functionality will be implemented based on requirements',
            'type' => $type,
            'format' => $format
        ]);
    }
}
