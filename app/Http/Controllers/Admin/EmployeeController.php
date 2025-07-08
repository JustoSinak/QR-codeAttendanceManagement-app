<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Services\QRCodeService;

class EmployeeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->middleware('admin.auth');
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::with('attendances')->paginate(10);
        return view('admin.employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'gender' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,emp_mail',
            'number' => 'required|integer',
            'department' => 'required|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:100',
            'emergency_phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ]);
        }

        try {
            $employeeId = Employee::generateEmployeeId($request->name, $request->department);

            // Handle profile photo upload
            $profilePhotoPath = null;
            if ($request->hasFile('profile_photo')) {
                $profilePhotoPath = $request->file('profile_photo')->store('employee_photos', 'public');
            }

            $employee = Employee::create([
                'id' => $employeeId,
                'emp_name' => $request->name,
                'gender' => $request->gender,
                'emp_mail' => $request->email,
                'emp_number' => $request->number,
                'department' => $request->department,
                'position' => $request->position,
                'hire_date' => $request->hire_date,
                'salary' => $request->salary,
                'address' => $request->address,
                'emergency_contact' => $request->emergency_contact,
                'emergency_phone' => $request->emergency_phone,
                'profile_photo' => $profilePhotoPath,
                'employee_status' => 'active',
            ]);

            // Log the activity
            \App\Models\AuditLog::logActivity(
                'admin',
                auth('admin')->id(),
                'employee_create',
                "Created new employee: {$employee->emp_name}",
                'employee',
                $employee->id,
                null,
                $employee->toArray()
            );

            // Generate QR code
            $qrResult = $this->qrCodeService->generateEmployeeQRCode($employee);

            return response()->json([
                'success' => true,
                'message' => 'Employee registered successfully!',
                'employee' => $employee,
                'qr_code' => $qrResult
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $employee = Employee::with('attendances')->findOrFail($id);
        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $employee = Employee::findOrFail($id);
        return view('admin.employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'gender' => 'required|string|max:100',
            'email' => 'required|email|unique:employees,emp_mail,' . $id,
            'number' => 'required|integer',
            'department' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $employee->update([
            'emp_name' => $request->name,
            'gender' => $request->gender,
            'emp_mail' => $request->email,
            'emp_number' => $request->number,
            'department' => $request->department,
        ]);

        return redirect()->route('admin.employees.index')
                        ->with('success', 'Employee updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);

        // Delete QR code file if exists
        if ($employee->qr_code_path && Storage::disk('public')->exists($employee->qr_code_path)) {
            Storage::disk('public')->delete($employee->qr_code_path);
        }

        $employee->delete();

        return redirect()->route('admin.employees.index')
                        ->with('success', 'Employee deleted successfully!');
    }

    /**
     * Generate QR code for employee
     */
    public function generateQRCode($id)
    {
        $employee = Employee::findOrFail($id);
        $result = $this->qrCodeService->generateEmployeeQRCode($employee);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'QR code generated successfully!',
                'qr_code_url' => $result['url'],
                'download_url' => route('admin.employees.download-qr', $id)
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate QR code: ' . $result['error']
            ]);
        }
    }

    /**
     * Regenerate QR code for employee
     */
    public function regenerateQRCode($id)
    {
        $employee = Employee::findOrFail($id);
        $result = $this->qrCodeService->regenerateQRCode($employee);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'QR code regenerated successfully!',
                'qr_code_url' => $result['url']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate QR code: ' . $result['error']
            ]);
        }
    }

    /**
     * Download QR code for employee
     */
    public function downloadQRCode($id)
    {
        $employee = Employee::findOrFail($id);

        if (!$employee->qr_code_path || !Storage::disk('public')->exists($employee->qr_code_path)) {
            return back()->with('error', 'QR code not found. Please generate QR code first.');
        }

        $filename = $employee->emp_name . '_QR_Code.png';
        return Storage::disk('public')->download($employee->qr_code_path, $filename);
    }

    /**
     * Generate printable QR code (300 DPI)
     */
    public function generatePrintableQRCode($id)
    {
        $employee = Employee::findOrFail($id);
        $result = $this->qrCodeService->generatePrintableQRCode($employee);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'High-resolution QR code generated successfully!',
                'qr_code_url' => $result['url']
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate printable QR code: ' . $result['error']
            ]);
        }
    }

    /**
     * Bulk generate QR codes for all employees
     */
    public function bulkGenerateQRCodes()
    {
        $results = $this->qrCodeService->bulkGenerateQRCodes();

        $message = "QR Code generation completed. Success: {$results['success']}, Failed: {$results['failed']}";

        if ($results['failed'] > 0) {
            $message .= " Errors: " . implode(', ', array_column($results['errors'], 'error'));
        }

        return back()->with('success', $message);
    }

    /**
     * QR Code management dashboard
     */
    public function qrCodeDashboard()
    {
        $statistics = $this->qrCodeService->getQRCodeStatistics();
        $employeesWithoutQR = Employee::whereNull('qr_code_path')
                                    ->where('employee_status', 'active')
                                    ->get();

        return view('admin.employees.qr-dashboard', compact('statistics', 'employeesWithoutQR'));
    }
}
