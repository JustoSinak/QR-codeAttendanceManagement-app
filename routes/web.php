<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\UserAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Main application routes
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/employee-info', function () {
    return view('employee-info');
})->name('employee.info');

// New User Authentication Routes
Route::prefix('auth')->name('auth.')->group(function () {
    Route::middleware(['guest', 'security:login'])->group(function () {
        Route::get('/login', [UserAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [UserAuthController::class, 'login']);
    });

    Route::middleware(['auth', 'session.timeout'])->group(function () {
        Route::post('/logout', [UserAuthController::class, 'logout'])->name('logout');
        Route::get('/change-password', [UserAuthController::class, 'showChangePasswordForm'])->name('password.change');
        Route::post('/change-password', [UserAuthController::class, 'changePassword']);
        Route::get('/profile', [UserAuthController::class, 'showProfile'])->name('profile');
        Route::post('/profile', [UserAuthController::class, 'updateProfile']);
    });
});

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest routes (not authenticated)
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Authenticated admin routes
    Route::middleware(['admin.auth', 'session.timeout', 'security'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/admins', [DashboardController::class, 'admins'])->name('admins');
        Route::get('/employees-list', [DashboardController::class, 'employees'])->name('employees.list');

        // Employee Management
        Route::resource('employees', EmployeeController::class)->middleware('permission:manage_employees');

        // QR Code Management
        Route::prefix('employees')->name('employees.')->middleware('permission:manage_employees')->group(function () {
            Route::post('/{id}/generate-qr', [EmployeeController::class, 'generateQRCode'])->name('generate-qr');
            Route::post('/{id}/regenerate-qr', [EmployeeController::class, 'regenerateQRCode'])->name('regenerate-qr');
            Route::get('/{id}/download-qr', [EmployeeController::class, 'downloadQRCode'])->name('download-qr');
            Route::post('/{id}/printable-qr', [EmployeeController::class, 'generatePrintableQRCode'])->name('printable-qr');
            Route::post('/bulk-generate-qr', [EmployeeController::class, 'bulkGenerateQRCodes'])->name('bulk-generate-qr');
            Route::get('/qr-dashboard', [EmployeeController::class, 'qrCodeDashboard'])->name('qr-dashboard');
        });

        // User Management (Admin Only)
        Route::prefix('users')->name('users.')->middleware('permission:manage_system')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
            Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{id}/unlock', [UserController::class, 'unlock'])->name('unlock');
            Route::get('/{id}/activity-log', [UserController::class, 'activityLog'])->name('activity-log');
        });

        // Attendance Management
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('index');
            Route::get('/scanner', [AttendanceController::class, 'scanner'])->name('scanner');
            Route::post('/scan', [AttendanceController::class, 'processQRScan'])->middleware('security:qr_scan')->name('scan');
            Route::get('/report', [AttendanceController::class, 'report'])->name('report');
            Route::get('/daily', [AttendanceController::class, 'dailyReport'])->name('daily');
        });

        // Leave Management
        Route::prefix('leave')->name('leave.')->group(function () {
            Route::get('/', [LeaveController::class, 'index'])->name('index');
            Route::get('/create', [LeaveController::class, 'create'])->middleware('permission:manage_employees')->name('create');
            Route::post('/', [LeaveController::class, 'store'])->middleware('permission:manage_employees')->name('store');
            Route::get('/{id}', [LeaveController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [LeaveController::class, 'edit'])->middleware('permission:manage_employees')->name('edit');
            Route::put('/{id}', [LeaveController::class, 'update'])->middleware('permission:manage_employees')->name('update');
            Route::delete('/{id}', [LeaveController::class, 'destroy'])->middleware('permission:manage_employees')->name('destroy');
            Route::post('/{id}/approve', [LeaveController::class, 'approve'])->middleware('permission:approve_leave')->name('approve');
            Route::post('/{id}/reject', [LeaveController::class, 'reject'])->middleware('permission:approve_leave')->name('reject');
            Route::get('/statistics/overview', [LeaveController::class, 'statistics'])->name('statistics');
        });

        // Reports & Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
            Route::get('/daily', [\App\Http\Controllers\Admin\ReportController::class, 'dailyReport'])->name('daily');
            Route::get('/weekly', [\App\Http\Controllers\Admin\ReportController::class, 'weeklyReport'])->name('weekly');
            Route::get('/monthly', [\App\Http\Controllers\Admin\ReportController::class, 'monthlyReport'])->name('monthly');
            Route::get('/overtime', [\App\Http\Controllers\Admin\ReportController::class, 'overtimeReport'])->name('overtime');
            Route::get('/analytics', [\App\Http\Controllers\Admin\ReportController::class, 'performanceAnalytics'])->name('analytics');
            Route::get('/export', [\App\Http\Controllers\Admin\ReportController::class, 'exportReport'])->name('export');
        });
    });
});

// Original Laravel routes (keep for now)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
