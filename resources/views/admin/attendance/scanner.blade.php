@extends('layouts.admin')

@section('title', 'QR Scanner')
@section('page-title', 'QR Code Scanner')

@push('styles')
<style>
    .scanner-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        align-items: start;
    }

    .scanner-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .results-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    #preview {
        width: 100%;
        max-width: 400px;
        height: 300px;
        border-radius: 10px;
        border: 2px solid #dee2e6;
        background: #f8f9fa;
    }

    .scan-input {
        width: 100%;
        padding: 1rem;
        border: 2px solid #e1e5e9;
        border-radius: 10px;
        font-size: 1.1rem;
        margin: 1rem 0;
        background: #f8f9fa;
        text-align: center;
        font-weight: 600;
    }

    .scan-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .attendance-table {
        max-height: 400px;
        overflow-y: auto;
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .status-checkin {
        background: #d4edda;
        color: #155724;
    }

    .status-checkout {
        background: #f8d7da;
        color: #721c24;
    }

    .scanner-status {
        text-align: center;
        padding: 1rem;
        margin: 1rem 0;
        border-radius: 10px;
        font-weight: 600;
    }

    .scanner-ready {
        background: #d4edda;
        color: #155724;
        border: 2px solid #c3e6cb;
    }

    .scanner-error {
        background: #f8d7da;
        color: #721c24;
        border: 2px solid #f5c6cb;
    }

    .message-display {
        padding: 1rem;
        margin: 1rem 0;
        border-radius: 10px;
        text-align: center;
        font-weight: 600;
        display: none;
    }

    .message-success {
        background: #d4edda;
        color: #155724;
        border: 2px solid #c3e6cb;
    }

    .message-error {
        background: #f8d7da;
        color: #721c24;
        border: 2px solid #f5c6cb;
    }

    @media (max-width: 768px) {
        .scanner-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="scanner-container">
    <div class="scanner-card">
        <h4 class="mb-4">
            <i class="fas fa-camera me-2 text-primary"></i>
            Camera Scanner
        </h4>
        
        <div class="text-center">
            <video id="preview" autoplay></video>
            
            <div id="scannerStatus" class="scanner-status scanner-ready">
                <i class="fas fa-camera me-2"></i>
                Scanner Ready - Point camera at QR code
            </div>
            
            <form id="scanForm">
                @csrf
                <input type="text" id="scanInput" name="text" class="scan-input" 
                       placeholder="Scanned QR code will appear here" readonly>
            </form>
            
            <div id="messageDisplay" class="message-display"></div>

            <!-- Photo capture canvas (hidden) -->
            <canvas id="photoCanvas" style="display: none;"></canvas>

            <!-- Photo preview -->
            <div id="photoPreview" style="display: none; margin-top: 1rem;">
                <h6>Captured Photo:</h6>
                <img id="capturedPhoto" style="max-width: 200px; border-radius: 10px; border: 2px solid #dee2e6;">
            </div>
        </div>
    </div>

    <div class="results-card">
        <h4 class="mb-4">
            <i class="fas fa-list me-2 text-success"></i>
            Today's Attendance
        </h4>
        
        <div class="attendance-table">
            <table class="table table-hover" id="attendanceTable">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="attendanceBody">
                    <!-- Attendance records will be loaded here -->
                </tbody>
            </table>
        </div>
        
        <div class="text-center mt-3">
            <button class="btn btn-primary" onclick="loadTodayAttendance()">
                <i class="fas fa-refresh me-2"></i>
                Refresh Data
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- QR Scanner Libraries -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<script>
$(document).ready(function() {
    let video = document.getElementById('preview');
    let canvas = document.createElement('canvas');
    let context = canvas.getContext('2d');
    let scanning = false;
    
    // Initialize camera
    initializeCamera();
    
    // Load today's attendance
    loadTodayAttendance();
    
    function initializeCamera() {
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment',
                width: { ideal: 640 },
                height: { ideal: 480 }
            } 
        })
        .then(function(stream) {
            video.srcObject = stream;
            video.play();
            
            updateScannerStatus('ready', 'Scanner Ready - Point camera at QR code');
            
            // Start scanning
            video.addEventListener('loadedmetadata', function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                startScanning();
            });
        })
        .catch(function(err) {
            console.error('Camera error:', err);
            updateScannerStatus('error', 'Camera access denied or not available');
        });
    }
    
    function startScanning() {
        if (scanning) return;
        scanning = true;
        
        function scan() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                
                if (code) {
                    handleQRCode(code.data);
                    return; // Stop scanning temporarily
                }
            }
            
            if (scanning) {
                requestAnimationFrame(scan);
            }
        }
        
        scan();
    }
    
    function handleQRCode(qrData) {
        scanning = false; // Pause scanning

        $('#scanInput').val(qrData);
        updateScannerStatus('processing', 'Capturing photo and processing attendance...');

        // Capture photo
        const photoData = capturePhoto();

        // Play shutter sound
        playShutterSound();

        // Process attendance with photo
        $.ajax({
            url: '{{ route("admin.attendance.scan") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                text: qrData,
                photo: photoData,
                location: getCurrentLocation(),
                device_info: getDeviceInfo()
            },
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.message);
                    loadTodayAttendance(); // Refresh attendance table
                } else {
                    showMessage('error', response.message);
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while processing attendance.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showMessage('error', errorMessage);
            },
            complete: function() {
                // Resume scanning after 3 seconds
                setTimeout(function() {
                    $('#scanInput').val('');
                    $('#photoPreview').hide();
                    updateScannerStatus('ready', 'Scanner Ready - Point camera at QR code');
                    scanning = true;
                    startScanning();
                }, 3000);
            }
        });
    }

    function capturePhoto() {
        const canvas = document.getElementById('photoCanvas');
        const context = canvas.getContext('2d');

        // Set canvas size to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        // Draw current video frame to canvas
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Get image data as base64
        const photoData = canvas.toDataURL('image/jpeg', 0.8);

        // Show preview
        $('#capturedPhoto').attr('src', photoData);
        $('#photoPreview').show();

        return photoData;
    }

    function getCurrentLocation() {
        // Get geolocation if available
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                return {
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude,
                    accuracy: position.coords.accuracy
                };
            });
        }
        return null;
    }

    function getDeviceInfo() {
        return {
            userAgent: navigator.userAgent,
            platform: navigator.platform,
            language: navigator.language,
            screen: {
                width: screen.width,
                height: screen.height
            },
            timestamp: new Date().toISOString()
        };
    }
    
    function updateScannerStatus(type, message) {
        const statusDiv = $('#scannerStatus');
        statusDiv.removeClass('scanner-ready scanner-error scanner-processing');
        
        if (type === 'ready') {
            statusDiv.addClass('scanner-ready');
            statusDiv.html('<i class="fas fa-camera me-2"></i>' + message);
        } else if (type === 'error') {
            statusDiv.addClass('scanner-error');
            statusDiv.html('<i class="fas fa-exclamation-triangle me-2"></i>' + message);
        } else if (type === 'processing') {
            statusDiv.addClass('scanner-processing');
            statusDiv.html('<i class="fas fa-spinner fa-spin me-2"></i>' + message);
        }
    }
    
    function showMessage(type, message) {
        const messageDiv = $('#messageDisplay');
        messageDiv.removeClass('message-success message-error');
        
        if (type === 'success') {
            messageDiv.addClass('message-success');
            messageDiv.html('<i class="fas fa-check-circle me-2"></i>' + message);
        } else {
            messageDiv.addClass('message-error');
            messageDiv.html('<i class="fas fa-exclamation-circle me-2"></i>' + message);
        }
        
        messageDiv.show();
        
        // Hide message after 5 seconds
        setTimeout(function() {
            messageDiv.hide();
        }, 5000);
    }
    
    function playShutterSound() {
        try {
            const audio = new Audio('{{ asset("sounds/shutter.mp3") }}');
            audio.play().catch(function(e) {
                console.log('Could not play shutter sound:', e);
            });
        } catch (e) {
            console.log('Shutter sound not available:', e);
        }
    }
    
    // Load today's attendance data
    window.loadTodayAttendance = function() {
        $.ajax({
            url: '{{ route("admin.attendance.daily") }}',
            method: 'GET',
            data: {
                date: new Date().toISOString().split('T')[0]
            },
            success: function(response) {
                // This would need to be implemented to return JSON data
                // For now, we'll just show a placeholder
                updateAttendanceTable([]);
            },
            error: function() {
                console.log('Could not load attendance data');
            }
        });
    };
    
    function updateAttendanceTable(attendances) {
        const tbody = $('#attendanceBody');
        tbody.empty();
        
        if (attendances.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                        No attendance records for today
                    </td>
                </tr>
            `);
            return;
        }
        
        attendances.forEach(function(attendance) {
            const timeOut = attendance.time_out ? attendance.time_out : '--';
            const status = attendance.status == 0 ? 
                '<span class="status-badge status-checkin">Checked In</span>' :
                '<span class="status-badge status-checkout">Checked Out</span>';
                
            tbody.append(`
                <tr>
                    <td><strong>${attendance.emp_name}</strong></td>
                    <td><span class="badge bg-success">${attendance.time_in}</span></td>
                    <td><span class="badge bg-${attendance.time_out ? 'danger' : 'warning'}">${timeOut}</span></td>
                    <td>${status}</td>
                </tr>
            `);
        });
    }
});
</script>
@endpush
