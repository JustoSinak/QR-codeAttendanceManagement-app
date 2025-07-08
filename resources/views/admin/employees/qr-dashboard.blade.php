@extends('layouts.admin')

@section('title', 'QR Code Management Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-qrcode me-2"></i>
                        QR Code Management Dashboard
                    </h3>
                    <div class="btn-group">
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Employees
                        </a>
                        <form method="POST" action="{{ route('admin.employees.bulk-generate-qr') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Generate QR codes for all employees without QR codes?')">
                                <i class="fas fa-magic me-2"></i>Bulk Generate QR Codes
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $statistics['total_employees'] }}</h4>
                                            <p class="mb-0">Total Employees</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $statistics['employees_with_qr'] }}</h4>
                                            <p class="mb-0">With QR Codes</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-qrcode fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $statistics['employees_without_qr'] }}</h4>
                                            <p class="mb-0">Without QR Codes</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $statistics['qr_coverage_percentage'] }}%</h4>
                                            <p class="mb-0">QR Coverage</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-chart-pie fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">QR Code Generation Progress</h5>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $statistics['qr_coverage_percentage'] }}%"
                                             aria-valuenow="{{ $statistics['qr_coverage_percentage'] }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            {{ $statistics['qr_coverage_percentage'] }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        {{ $statistics['employees_with_qr'] }} out of {{ $statistics['total_employees'] }} employees have QR codes
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employees Without QR Codes -->
                    @if($employeesWithoutQR->count() > 0)
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                            Employees Without QR Codes ({{ $employeesWithoutQR->count() }})
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Employee ID</th>
                                                        <th>Name</th>
                                                        <th>Department</th>
                                                        <th>Email</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($employeesWithoutQR as $employee)
                                                        <tr>
                                                            <td>{{ $employee->id }}</td>
                                                            <td>
                                                                <div class="d-flex align-items-center">
                                                                    @if($employee->profile_photo)
                                                                        <img src="{{ asset('storage/' . $employee->profile_photo) }}" 
                                                                             class="rounded-circle me-2" width="32" height="32" alt="Profile">
                                                                    @else
                                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                                             style="width: 32px; height: 32px;">
                                                                            <span class="text-white fw-bold">{{ substr($employee->emp_name, 0, 1) }}</span>
                                                                        </div>
                                                                    @endif
                                                                    {{ $employee->emp_name }}
                                                                </div>
                                                            </td>
                                                            <td>{{ $employee->department }}</td>
                                                            <td>{{ $employee->emp_mail }}</td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-primary" 
                                                                        onclick="generateQRCode('{{ $employee->id }}')">
                                                                    <i class="fas fa-qrcode me-1"></i>Generate QR
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Excellent!</strong> All active employees have QR codes generated.
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- QR Code Security Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        QR Code Security Features
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>AES-256 Encryption</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Data Integrity Verification</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Expiration Date Control</li>
                                                <li><i class="fas fa-check text-success me-2"></i>High Error Correction Level</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check text-success me-2"></i>300 DPI Print Quality</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Tamper Detection</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Employee Status Validation</li>
                                                <li><i class="fas fa-check text-success me-2"></i>Audit Trail Logging</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function generateQRCode(employeeId) {
    // Show loading state
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
    button.disabled = true;

    $.ajax({
        url: `/admin/employees/${employeeId}/generate-qr`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Show success message
                showAlert('success', response.message);
                
                // Remove the row from the table
                button.closest('tr').fadeOut(500, function() {
                    $(this).remove();
                    
                    // Update statistics
                    updateStatistics();
                });
            } else {
                showAlert('danger', response.message);
                
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            }
        },
        error: function(xhr) {
            showAlert('danger', 'An error occurred while generating QR code.');
            
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
        }
    });
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.card-body').prepend(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

function updateStatistics() {
    // Reload the page to update statistics
    setTimeout(function() {
        location.reload();
    }, 1000);
}
</script>
@endpush
