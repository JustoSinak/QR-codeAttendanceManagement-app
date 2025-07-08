<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use App\Models\Employee;
use App\Models\AuditLog;
use Carbon\Carbon;

class QRCodeService
{
    /**
     * Generate QR code for employee with encrypted data
     */
    public function generateEmployeeQRCode(Employee $employee, $size = 300, $format = 'png')
    {
        try {
            // Create encrypted payload with employee data
            $payload = $this->createEncryptedPayload($employee);
            
            // Generate QR code with high error correction level
            $qrCode = QrCode::format($format)
                           ->size($size)
                           ->errorCorrection('H') // High error correction
                           ->margin(2)
                           ->generate($payload);

            // Save QR code to storage
            $filename = $this->generateQRCodeFilename($employee);
            $path = "qr_codes/{$filename}";
            
            Storage::disk('public')->put($path, $qrCode);

            // Update employee record with QR code path and hash
            $employee->update([
                'qr_code_path' => $path,
                'qr_code_hash' => hash('sha256', $payload),
            ]);

            // Log QR code generation
            AuditLog::logActivity(
                'admin',
                auth('admin')->id() ?? 'system',
                'qr_code_generated',
                "Generated QR code for employee: {$employee->emp_name}",
                'employee',
                $employee->id
            );

            return [
                'success' => true,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'filename' => $filename,
                'hash' => hash('sha256', $payload)
            ];

        } catch (\Exception $e) {
            // Log error
            AuditLog::logActivity(
                'admin',
                auth('admin')->id() ?? 'system',
                'qr_code_generation_failed',
                "Failed to generate QR code for employee: {$employee->emp_name}. Error: {$e->getMessage()}",
                'employee',
                $employee->id
            );

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create encrypted payload for QR code
     */
    private function createEncryptedPayload(Employee $employee)
    {
        // Create data structure with employee information
        $data = [
            'employee_id' => $employee->id,
            'name' => $employee->emp_name,
            'email' => $employee->emp_mail,
            'department' => $employee->department,
            'generated_at' => now()->toISOString(),
            'expires_at' => now()->addYear()->toISOString(), // QR codes expire after 1 year
            'version' => '1.0',
            'checksum' => null
        ];

        // Add checksum for data integrity
        $data['checksum'] = hash('sha256', json_encode($data));

        // Encrypt the data
        $encryptedData = Crypt::encrypt($data);

        // Create final payload with metadata
        $payload = [
            'type' => 'employee_attendance',
            'data' => $encryptedData,
            'timestamp' => now()->timestamp
        ];

        return base64_encode(json_encode($payload));
    }

    /**
     * Decrypt and validate QR code data
     */
    public function decryptQRCodeData($qrData)
    {
        try {
            // Decode base64 payload
            $payload = json_decode(base64_decode($qrData), true);

            if (!$payload || !isset($payload['type'], $payload['data'])) {
                throw new \Exception('Invalid QR code format');
            }

            if ($payload['type'] !== 'employee_attendance') {
                throw new \Exception('Invalid QR code type');
            }

            // Decrypt the employee data
            $employeeData = Crypt::decrypt($payload['data']);

            // Validate data structure
            if (!isset($employeeData['employee_id'], $employeeData['checksum'])) {
                throw new \Exception('Invalid employee data structure');
            }

            // Verify checksum
            $checksum = $employeeData['checksum'];
            unset($employeeData['checksum']);
            $calculatedChecksum = hash('sha256', json_encode($employeeData));

            if ($checksum !== $calculatedChecksum) {
                throw new \Exception('Data integrity check failed');
            }

            // Check expiration
            if (isset($employeeData['expires_at'])) {
                $expiresAt = Carbon::parse($employeeData['expires_at']);
                if ($expiresAt->isPast()) {
                    throw new \Exception('QR code has expired');
                }
            }

            // Verify employee exists and is active
            $employee = Employee::where('id', $employeeData['employee_id'])
                              ->where('employee_status', 'active')
                              ->first();

            if (!$employee) {
                throw new \Exception('Employee not found or inactive');
            }

            return [
                'success' => true,
                'employee' => $employee,
                'data' => $employeeData
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate QR code filename
     */
    private function generateQRCodeFilename(Employee $employee)
    {
        $timestamp = now()->format('Ymd_His');
        $sanitizedName = preg_replace('/[^a-zA-Z0-9]/', '_', $employee->emp_name);
        return "{$employee->id}_{$sanitizedName}_{$timestamp}.png";
    }

    /**
     * Generate high-resolution QR code for printing (300 DPI)
     */
    public function generatePrintableQRCode(Employee $employee)
    {
        // Calculate size for 300 DPI (assuming 2 inch QR code)
        $size = 600; // 2 inches * 300 DPI
        
        return $this->generateEmployeeQRCode($employee, $size, 'png');
    }

    /**
     * Generate QR code with custom branding
     */
    public function generateBrandedQRCode(Employee $employee, $logoPath = null)
    {
        try {
            $payload = $this->createEncryptedPayload($employee);
            
            $qrCode = QrCode::format('png')
                           ->size(300)
                           ->errorCorrection('H')
                           ->margin(2);

            // Add logo if provided
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                $qrCode->merge($logoPath, 0.3, true);
            }

            $generatedQR = $qrCode->generate($payload);

            // Save with branding suffix
            $filename = str_replace('.png', '_branded.png', $this->generateQRCodeFilename($employee));
            $path = "qr_codes/{$filename}";
            
            Storage::disk('public')->put($path, $generatedQR);

            return [
                'success' => true,
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'filename' => $filename
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Regenerate QR code for employee
     */
    public function regenerateQRCode(Employee $employee)
    {
        // Delete old QR code if exists
        if ($employee->qr_code_path && Storage::disk('public')->exists($employee->qr_code_path)) {
            Storage::disk('public')->delete($employee->qr_code_path);
        }

        // Generate new QR code
        return $this->generateEmployeeQRCode($employee);
    }

    /**
     * Validate QR code hash
     */
    public function validateQRCodeHash(Employee $employee, $qrData)
    {
        $hash = hash('sha256', $qrData);
        return $employee->qr_code_hash === $hash;
    }

    /**
     * Get QR code statistics
     */
    public function getQRCodeStatistics()
    {
        $totalEmployees = Employee::count();
        $employeesWithQR = Employee::whereNotNull('qr_code_path')->count();
        $employeesWithoutQR = $totalEmployees - $employeesWithQR;

        return [
            'total_employees' => $totalEmployees,
            'employees_with_qr' => $employeesWithQR,
            'employees_without_qr' => $employeesWithoutQR,
            'qr_coverage_percentage' => $totalEmployees > 0 ? round(($employeesWithQR / $totalEmployees) * 100, 2) : 0
        ];
    }

    /**
     * Bulk generate QR codes for all employees
     */
    public function bulkGenerateQRCodes()
    {
        $employees = Employee::where('employee_status', 'active')
                           ->whereNull('qr_code_path')
                           ->get();

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($employees as $employee) {
            $result = $this->generateEmployeeQRCode($employee);
            
            if ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
                $results['errors'][] = [
                    'employee' => $employee->emp_name,
                    'error' => $result['error']
                ];
            }
        }

        return $results;
    }
}
