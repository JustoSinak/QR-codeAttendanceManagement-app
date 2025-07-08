@extends('layouts.admin')

@section('title', 'Add Employee')
@section('page-title', 'Add New Employee')

@push('styles')
<style>
    .form-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        align-items: start;
    }

    .form-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .qr-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        text-align: center;
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #333;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e1e5e9;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e1e5e9;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .form-select:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-submit {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
    }

    .qr-display {
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        margin: 2rem 0;
    }

    .qr-placeholder {
        text-align: center;
        color: #6c757d;
    }

    .qr-placeholder i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #dee2e6;
    }

    .download-btn {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .download-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        color: white;
    }

    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .success-message {
        color: #28a745;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    @media (max-width: 768px) {
        .form-container {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
    }
</style>
@endpush

@section('content')
<div class="form-container">
    <div class="form-card">
        <h3 class="mb-4">
            <i class="fas fa-user-plus me-2 text-primary"></i>
            Employee Information
        </h3>
        
        <form id="employeeForm">
            @csrf
            
            <div class="form-group">
                <label for="name" class="form-label">
                    <i class="fas fa-user me-1"></i>
                    Employee Name
                </label>
                <input type="text" id="name" name="name" class="form-control" required>
                <div class="error-message" id="name-error"></div>
            </div>

            <div class="form-group">
                <label for="gender" class="form-label">
                    <i class="fas fa-venus-mars me-1"></i>
                    Gender
                </label>
                <select id="gender" name="gender" class="form-select" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
                <div class="error-message" id="gender-error"></div>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope me-1"></i>
                    Email Address
                </label>
                <input type="email" id="email" name="email" class="form-control" required>
                <div class="error-message" id="email-error"></div>
            </div>

            <div class="form-group">
                <label for="number" class="form-label">
                    <i class="fas fa-phone me-1"></i>
                    Phone Number
                </label>
                <input type="number" id="number" name="number" class="form-control" min="0" max="699999999" required>
                <div class="error-message" id="number-error"></div>
            </div>

            <div class="form-group">
                <label for="department" class="form-label">
                    <i class="fas fa-building me-1"></i>
                    Department
                </label>
                <input type="text" id="department" name="department" class="form-control" required>
                <div class="error-message" id="department-error"></div>
            </div>

            <div class="form-group">
                <label for="position" class="form-label">
                    <i class="fas fa-briefcase me-1"></i>
                    Position/Job Title
                </label>
                <input type="text" id="position" name="position" class="form-control">
                <div class="error-message" id="position-error"></div>
            </div>

            <div class="form-group">
                <label for="hire_date" class="form-label">
                    <i class="fas fa-calendar me-1"></i>
                    Hire Date
                </label>
                <input type="date" id="hire_date" name="hire_date" class="form-control">
                <div class="error-message" id="hire_date-error"></div>
            </div>

            <div class="form-group">
                <label for="salary" class="form-label">
                    <i class="fas fa-money-bill me-1"></i>
                    Salary (CFA)
                </label>
                <input type="number" id="salary" name="salary" class="form-control" step="0.01" min="0">
                <div class="error-message" id="salary-error"></div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    Address
                </label>
                <textarea id="address" name="address" class="form-control" rows="3"></textarea>
                <div class="error-message" id="address-error"></div>
            </div>

            <div class="form-group">
                <label for="emergency_contact" class="form-label">
                    <i class="fas fa-user-friends me-1"></i>
                    Emergency Contact Name
                </label>
                <input type="text" id="emergency_contact" name="emergency_contact" class="form-control">
                <div class="error-message" id="emergency_contact-error"></div>
            </div>

            <div class="form-group">
                <label for="emergency_phone" class="form-label">
                    <i class="fas fa-phone-alt me-1"></i>
                    Emergency Contact Phone
                </label>
                <input type="tel" id="emergency_phone" name="emergency_phone" class="form-control">
                <div class="error-message" id="emergency_phone-error"></div>
            </div>

            <div class="form-group">
                <label for="profile_photo" class="form-label">
                    <i class="fas fa-camera me-1"></i>
                    Profile Photo
                </label>
                <input type="file" id="profile_photo" name="profile_photo" class="form-control" accept="image/*">
                <div class="error-message" id="profile_photo-error"></div>
                <small class="text-muted">Upload a profile photo (JPG, PNG, max 2MB)</small>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus me-2"></i>
                Add Employee & Generate QR
            </button>
        </form>
    </div>

    <div class="qr-card">
        <h3 class="mb-4">
            <i class="fas fa-qrcode me-2 text-success"></i>
            QR Code
        </h3>
        
        <div class="qr-display" id="qrDisplay">
            <div class="qr-placeholder">
                <i class="fas fa-qrcode"></i>
                <p>QR Code will appear here after adding employee</p>
            </div>
        </div>
        
        <div id="downloadSection" style="display: none;">
            <a href="#" id="downloadLink" class="download-btn">
                <i class="fas fa-download me-2"></i>
                Download QR Code
            </a>
        </div>
        
        <div id="responseMessage" class="mt-3"></div>
    </div>
</div>
@endsection

@push('scripts')
<!-- QR Code Library -->
<script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>

<script>
$(document).ready(function() {
    let qrCode = null;
    
    $('#employeeForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous errors
        $('.error-message').text('');
        $('.form-control, .form-select').removeClass('is-invalid');
        
        // Show loading state
        const submitBtn = $('.btn-submit');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.employees.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('#responseMessage').html(
                        '<div class="alert alert-success">' +
                        '<i class="fas fa-check-circle me-2"></i>' +
                        response.message +
                        '</div>'
                    );
                    
                    // Generate QR Code
                    generateQRCode(response.employee);
                    
                    // Reset form
                    $('#employeeForm')[0].reset();
                    
                    // Redirect after 3 seconds
                    setTimeout(function() {
                        window.location.href = '{{ route("admin.employees.index") }}';
                    }, 3000);
                } else {
                    // Show error message
                    $('#responseMessage').html(
                        '<div class="alert alert-danger">' +
                        '<i class="fas fa-exclamation-circle me-2"></i>' +
                        response.message +
                        '</div>'
                    );
                    
                    // Show field errors
                    if (response.errors) {
                        $.each(response.errors, function(field, messages) {
                            $('#' + field + '-error').text(messages[0]);
                            $('#' + field).addClass('is-invalid');
                        });
                    }
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while adding the employee.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                $('#responseMessage').html(
                    '<div class="alert alert-danger">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>' +
                    errorMessage +
                    '</div>'
                );
            },
            complete: function() {
                // Restore button
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    function generateQRCode(employeeData) {
        // Clear previous QR code
        $('#qrDisplay').empty();

        if (employeeData.qr_code && employeeData.qr_code.success) {
            // Display the generated QR code
            const qrImage = $('<img>')
                .attr('src', employeeData.qr_code.url)
                .attr('alt', 'Employee QR Code')
                .css({
                    'width': '256px',
                    'height': '256px',
                    'border': '2px solid #ddd',
                    'border-radius': '8px'
                });

            $('#qrDisplay').append(qrImage);

            // Setup download functionality
            $('#downloadLink').attr('href', employeeData.qr_code.url);
            $('#downloadLink').attr('download', employeeData.emp_name + '_QR_Code.png');
            $('#downloadSection').show();

            // Show success message
            $('#responseMessage').html(`
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    QR Code generated successfully! The QR code contains encrypted employee data for secure attendance tracking.
                </div>
            `);
        } else {
            // Show error message
            $('#responseMessage').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Failed to generate QR code. Please try again.
                </div>
            `);
        }
    }
});
</script>
@endpush
