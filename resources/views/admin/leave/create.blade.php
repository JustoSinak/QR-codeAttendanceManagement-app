@extends('layouts.admin')

@section('title', 'Create Leave Request')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>
                        Create New Leave Request
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.leave.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.leave.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
                                    <select name="employee_id" id="employee_id" class="form-select" required>
                                        <option value="">Select Employee</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->emp_name }} - {{ $employee->department }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leave_type" class="form-label">Leave Type <span class="text-danger">*</span></label>
                                    <select name="leave_type" id="leave_type" class="form-select" required>
                                        <option value="">Select Leave Type</option>
                                        <option value="sick" {{ old('leave_type') == 'sick' ? 'selected' : '' }}>Sick Leave</option>
                                        <option value="vacation" {{ old('leave_type') == 'vacation' ? 'selected' : '' }}>Vacation Leave</option>
                                        <option value="personal" {{ old('leave_type') == 'personal' ? 'selected' : '' }}>Personal Leave</option>
                                        <option value="emergency" {{ old('leave_type') == 'emergency' ? 'selected' : '' }}>Emergency Leave</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" 
                                           value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" 
                                           value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                                    <textarea name="reason" id="reason" class="form-control" rows="4" 
                                              placeholder="Please provide a detailed reason for the leave request..." required>{{ old('reason') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_emergency" id="is_emergency" class="form-check-input" 
                                               value="1" {{ old('is_emergency') ? 'checked' : '' }}>
                                        <label for="is_emergency" class="form-check-label">
                                            <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                            This is an emergency leave request
                                        </label>
                                    </div>
                                    <small class="text-muted">Emergency requests may be processed with priority</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="attachments" class="form-label">Attachments (Optional)</label>
                                    <input type="file" name="attachments[]" id="attachments" class="form-control" 
                                           multiple accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="text-muted">
                                        Supported formats: PDF, JPG, PNG. Max size: 2MB per file.
                                        <br>For sick leave, please attach medical certificate if available.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle me-2"></i>Leave Request Information:</h6>
                                    <ul class="mb-0">
                                        <li>Leave requests must be submitted at least 24 hours in advance (except emergency)</li>
                                        <li>All leave requests require approval from HR Manager or Supervisor</li>
                                        <li>Medical certificates are required for sick leave exceeding 3 days</li>
                                        <li>Vacation leave should be planned and approved in advance</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.leave.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Leave Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    // Update end date minimum when start date changes
    startDateInput.addEventListener('change', function() {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    });
    
    // Calculate and display total days
    function calculateDays() {
        if (startDateInput.value && endDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            const timeDiff = endDate.getTime() - startDate.getTime();
            const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
            
            // Display total days
            let daysDisplay = document.getElementById('days-display');
            if (!daysDisplay) {
                daysDisplay = document.createElement('small');
                daysDisplay.id = 'days-display';
                daysDisplay.className = 'text-info fw-bold';
                endDateInput.parentNode.appendChild(daysDisplay);
            }
            daysDisplay.textContent = `Total days: ${dayDiff}`;
        }
    }
    
    startDateInput.addEventListener('change', calculateDays);
    endDateInput.addEventListener('change', calculateDays);
    
    // File upload preview
    const attachmentsInput = document.getElementById('attachments');
    attachmentsInput.addEventListener('change', function() {
        const files = this.files;
        let preview = document.getElementById('file-preview');
        
        if (!preview) {
            preview = document.createElement('div');
            preview.id = 'file-preview';
            preview.className = 'mt-2';
            this.parentNode.appendChild(preview);
        }
        
        preview.innerHTML = '';
        
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileDiv = document.createElement('div');
            fileDiv.className = 'badge bg-light text-dark me-2 mb-1';
            fileDiv.innerHTML = `<i class="fas fa-file me-1"></i>${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
            preview.appendChild(fileDiv);
        }
    });
});
</script>
@endpush
