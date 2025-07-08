<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin.auth');
    }

    public function index()
    {
        $totalEmployees = Employee::count();
        $totalAdmins = Admin::count();
        $todayAttendance = Attendance::whereDate('date', today())->count();
        $checkedInToday = Attendance::whereDate('date', today())->where('status', 0)->count();

        // Recent attendance records
        $recentAttendance = Attendance::with('employee')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly attendance statistics
        $monthlyStats = Attendance::selectRaw('DATE(date) as date, COUNT(*) as count')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.dashboard', compact(
            'totalEmployees',
            'totalAdmins',
            'todayAttendance',
            'checkedInToday',
            'recentAttendance',
            'monthlyStats'
        ));
    }

    public function admins()
    {
        $admins = Admin::all();
        return view('admin.admins', compact('admins'));
    }

    public function employees()
    {
        $employees = Employee::with('attendances')->get();
        return view('admin.employees', compact('employees'));
    }
}
